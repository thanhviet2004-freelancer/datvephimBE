<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CinemaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim((string) $request->query('q', ''));
        $movies = Movie::query()->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))->get();

        return response()->json(['data' => $movies]);
    }

    public function movie(Movie $movie): JsonResponse
    {
        $showtimes = $movie->showtimes()->where('starts_at', '>', now())->orderBy('starts_at')->get();

        return response()->json(['data' => $movie->toArray() + ['showtimes' => $showtimes]]);
    }

    public function seats(Showtime $showtime): JsonResponse
    {
        abort_if($showtime->starts_at->isPast(), 404);
        $occupied = BookingSeat::where('showtime_id', $showtime->id)->pluck('seat')->all();

        return response()->json(['data' => $showtime->load('movie')->toArray() + ['occupied' => $occupied]]);
    }

    public function store(Request $request, Showtime $showtime): JsonResponse
    {
        abort_if($showtime->starts_at->isPast(), 404);
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^0[0-9]{9}$/'],
            'seats' => ['required', 'array', 'min:1', 'max:8'],
            'seats.*' => ['required', 'string', 'distinct', 'regex:/^[A-F][1-8]$/'],
        ], [
            'customer_name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email chưa đúng định dạng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại gồm 10 chữ số, bắt đầu bằng 0.',
            'seats.required' => 'Vui lòng chọn ít nhất một ghế.',
            'seats.max' => 'Mỗi đơn được đặt tối đa 8 ghế.',
            'seats.*.regex' => 'Ghế không hợp lệ.',
            'seats.*.distinct' => 'Không được chọn ghế trùng nhau.',
        ]);

        try {
            $booking = DB::transaction(function () use ($showtime, $data) {
                $booking = Booking::create([
                    'code' => (string) Str::uuid(),
                    'showtime_id' => $showtime->id,
                    'customer_name' => $data['customer_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'total' => count($data['seats']) * $showtime->price,
                ]);
                foreach ($data['seats'] as $seat) {
                    $booking->seats()->create(['showtime_id' => $showtime->id, 'seat' => $seat]);
                }

                return $booking;
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['seats' => 'Một ghế vừa được người khác đặt. Vui lòng chọn ghế khác.']);
        }

        return (new BookingResource($booking->load(['showtime.movie', 'seats'])))->response()->setStatusCode(201)->header('Cache-Control', 'no-store');
    }

    public function lookup(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'uuid'], 'email' => ['required', 'email']]);
        $booking = Booking::with(['showtime.movie', 'seats'])->where('code', $data['code'])->where('email', $data['email'])->first();
        if (! $booking) {
            throw ValidationException::withMessages(['code' => 'Không tìm thấy vé khớp mã đặt vé và email.']);
        }

        return (new BookingResource($booking))->response()->header('Cache-Control', 'no-store');
    }
}
