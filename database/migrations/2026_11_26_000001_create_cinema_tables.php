<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('genre');
            $table->unsignedSmallInteger('duration');
            $table->string('rating');
            $table->string('art');
            $table->text('description');
            $table->timestamps();
        });
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->string('room');
            $table->dateTime('starts_at');
            $table->unsignedInteger('price');
            $table->timestamps();
            $table->unique(['room', 'starts_at']);
        });
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->foreignId('showtime_id')->constrained();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone', 20);
            $table->unsignedInteger('total');
            $table->timestamps();
        });
        Schema::create('booking_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('showtime_id')->constrained();
            $table->string('seat', 3);
            $table->timestamps();
            $table->unique(['showtime_id', 'seat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_seats');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('showtimes');
        Schema::dropIfExists('movies');
    }
};
