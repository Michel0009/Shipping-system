<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'user_Register']);
Route::post('/sendEmail', [AuthController::class, 'send_email']);
Route::post('/emailVerification', [AuthController::class, 'verification']);
Route::post('/newPasswordVerification', [AuthController::class, 'new_password_verification']);
Route::post('/reSetPassword', [AuthController::class, 'reset_password']);

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {

  Route::get('/logout', [AuthController::class, 'logout']);
  Route::get('/governorates', [DriverController::class, 'governorates']);
  Route::get('/profile', [UserController::class, 'get_profile']);

  // Client Routes
  Route::middleware('role:client')->group(function () {
    Route::post('/shipment/create', [ShipmentController::class, 'create_shipment']);
    Route::get('/shipmentRequest', [ShipmentController::class, 'get_shipment']);
    Route::delete('/shipmentRequest', [ShipmentController::class, 'delete_shipment']);
    Route::put('/shipmentRequest', [ShipmentController::class, 'update_shipment']);
    
    Route::post('/review', [ReviewController::class, 'create_review']);
  });

  // Driver Routes
  Route::middleware('role:driver')->group(function () {
    Route::post('/governorate/attach', [DriverController::class, 'attach_governorate']);
    Route::post('/governorate/detatch', [DriverController::class, 'detatch_governorate']);

    Route::get('/reviews', [ReviewController::class, 'get_driver_reviews']);

  });

  // Admin Routes
  Route::middleware('role:admin')->group(function () {

  });

  // Employee-Admin Routes
  Route::middleware('role:employee,admin')->group(function () {
        Route::post('/createDriver', [UserController::class, 'create_driver']);
  });

  // Client-Driver Routes
  Route::middleware('role:client,driver')->group(function () {
        Route::post('/report', [ReportController::class, 'report']);
  });

});
