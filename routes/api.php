<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route::post('/register', [AuthController::class, 'user_Register']);
// Route::get('/sendEmail/{email}', [AuthController::class, 'send_email']);
// Route::post('/verification/{email}', [AuthController::class, 'verification']);
// Route::post('/reSetPassword', [AuthController::class, 'reset_password']);

// Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {

//   Route::get('/logout', [AuthController::class, 'logout']);
  // Route::post('/Refresh', [AuthController::class, 'refresh']);
  
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
