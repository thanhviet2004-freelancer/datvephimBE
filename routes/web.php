<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['name' => 'CineGo API', 'version' => 'v1', 'movies' => '/api/v1/movies']));
