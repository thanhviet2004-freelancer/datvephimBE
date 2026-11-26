<?php

use App\Http\Controllers\CinemaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/movies', [CinemaController::class, 'index']);
    Route::get('/movies/{movie}', [CinemaController::class, 'movie']);
    Route::get('/showtimes/{showtime}', [CinemaController::class, 'seats']);
    Route::post('/showtimes/{showtime}/bookings', [CinemaController::class, 'store'])->middleware('throttle:20,1');
    Route::post('/bookings/lookup', [CinemaController::class, 'lookup'])->middleware('throttle:10,1');
});
