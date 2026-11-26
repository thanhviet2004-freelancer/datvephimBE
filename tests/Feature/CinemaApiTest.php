<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Showtime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CinemaApiTest extends TestCase
{
    use RefreshDatabase;

    private function showtime(): Showtime
    {
        $this->seed();

        return Showtime::where('starts_at', '>', now())->firstOrFail();
    }

    private function customer(array $overrides = []): array
    {
        return array_merge(['customer_name' => 'Khách Demo', 'email' => 'demo@example.com', 'phone' => '0901234567', 'seats' => ['A1', 'A2']], $overrides);
    }

    public function test_browsing_booking_and_lookup_work_through_json_api(): void
    {
        $show = $this->showtime();
        $this->getJson('/api/v1/movies')->assertOk()->assertJsonCount(4, 'data');
        $this->getJson('/api/v1/movies/'.$show->movie_id)->assertOk()->assertJsonPath('data.title', $show->movie->title)->assertJsonStructure(['data' => ['showtimes']]);
        $this->getJson('/api/v1/showtimes/'.$show->id)->assertOk()->assertJsonPath('data.occupied', []);
        $response = $this->postJson('/api/v1/showtimes/'.$show->id.'/bookings', $this->customer(['total' => 1]));
        $response->assertCreated()->assertJsonPath('data.total', 2 * $show->price)->assertJsonPath('data.seats', ['A1', 'A2'])->assertJsonMissingPath('data.email')->assertJsonMissingPath('data.phone');
        $code = $response->json('data.code');
        $this->getJson('/api/v1/showtimes/'.$show->id)->assertJsonPath('data.occupied', ['A1', 'A2']);
        $this->postJson('/api/v1/bookings/lookup', ['code' => $code, 'email' => 'demo@example.com'])->assertOk()->assertJsonPath('data.code', $code);
    }

    public function test_conflict_rolls_back_every_seat_and_booking(): void
    {
        $show = $this->showtime();
        $this->postJson('/api/v1/showtimes/'.$show->id.'/bookings', $this->customer())->assertCreated();
        $this->postJson('/api/v1/showtimes/'.$show->id.'/bookings', $this->customer(['seats' => ['B1', 'A1']]))->assertUnprocessable()->assertJsonValidationErrors('seats');
        $this->assertSame(1, Booking::count());
        $this->assertSame(2, BookingSeat::count());
        $this->assertDatabaseMissing('booking_seats', ['seat' => 'B1']);
    }

    public function test_invalid_seats_and_contact_details_are_rejected(): void
    {
        $show = $this->showtime();
        foreach ([[], ['Z9'], ['A1', 'A1'], 'invalid', ['A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'B1']] as $seats) {
            $this->postJson('/api/v1/showtimes/'.$show->id.'/bookings', $this->customer(['seats' => $seats]))->assertUnprocessable();
        }
        $this->postJson('/api/v1/showtimes/'.$show->id.'/bookings', $this->customer(['email' => 'bad', 'phone' => '123', 'customer_name' => '']))->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone', 'customer_name']);
        $this->assertSame(0, Booking::count());
    }

    public function test_past_showtime_cannot_be_booked(): void
    {
        $show = $this->showtime();
        $show->update(['starts_at' => now()->subDay()]);
        $this->getJson('/api/v1/showtimes/'.$show->id)->assertNotFound();
        $this->postJson('/api/v1/showtimes/'.$show->id.'/bookings', $this->customer())->assertNotFound();
        $this->assertSame(0, Booking::count());
    }

    public function test_lookup_requires_matching_email_and_no_public_ticket_endpoint_exists(): void
    {
        $show = $this->showtime();
        $response = $this->postJson('/api/v1/showtimes/'.$show->id.'/bookings', $this->customer());
        $code = $response->json('data.code');
        $this->postJson('/api/v1/bookings/lookup', ['code' => $code, 'email' => 'wrong@example.com'])->assertUnprocessable();
        $this->getJson('/ve/'.$code)->assertNotFound();
        $this->getJson('/api/v1/bookings/'.$code)->assertNotFound();
    }

    public function test_search_and_seed_can_repeat_without_duplicates(): void
    {
        $this->seed();
        $count = Showtime::count();
        $this->seed();
        $this->assertSame($count, Showtime::count());
        $this->getJson('/api/v1/movies?q='.urlencode('Mùa Hè'))->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Mùa Hè Năm Ấy');
        $this->getJson('/api/v1/movies?q=unknown')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/movies?q[]=bad')->assertUnprocessable();
    }

    public function test_cors_allows_configured_frontend_only(): void
    {
        $headers = ['Origin' => 'http://127.0.0.1:8080', 'Access-Control-Request-Method' => 'POST', 'Access-Control-Request-Headers' => 'content-type'];
        $this->options('/api/v1/bookings/lookup', [], $headers)->assertSuccessful()->assertHeader('Access-Control-Allow-Origin', 'http://127.0.0.1:8080');
        $headers['Origin'] = 'https://untrusted.example';
        $this->options('/api/v1/bookings/lookup', [], $headers)->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_lookup_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->postJson('/api/v1/bookings/lookup', [])->assertUnprocessable();
        }
        $this->postJson('/api/v1/bookings/lookup', [])->assertStatus(429);
    }
}
