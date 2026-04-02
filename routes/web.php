<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Support\Facades\Cache;

Route::get('/test/set-driver-location/{id}', function ($id) {
    Cache::put("location_driver_{$id}", [
        'lat' => 33.4988,
        'lng' => 36.3236
    ], now()->addHours(1));

    return response()->json([
        'message' => "تم تخزين موقع السائق {$id}"
    ]);
});

Route::get('/test/get-driver-location/{id}', function ($id) {
    return Cache::get("location_driver_{$id}");
});

