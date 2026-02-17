<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'user_Register']);
Route::post('/sendEmail', [AuthController::class, 'send_email']);
Route::post('/emailVerification', [AuthController::class, 'verification']);
Route::post('/newPasswordVerification', [AuthController::class, 'new_password_verification']);
Route::post('/reSetPassword', [AuthController::class, 'reset_password']);

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {

  Route::get('/logout', [AuthController::class, 'logout']);
  
  // Client Routes
  Route::middleware('role:client')->group(function () {

  });

  // Driver Routes
  Route::middleware('role:driver')->group(function () {

  });

  // Admin Routes
  Route::middleware('role:admin')->group(function () {

  });

  // Employee-Admin Routes
  Route::middleware('role:employee,admin')->group(function () {

  });
  
});
