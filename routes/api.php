<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleTypeController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'user_Register']);
Route::post('/sendEmail', [AuthController::class, 'send_email']);
Route::post('/emailVerification', [AuthController::class, 'verification']);
Route::post('/newPasswordVerification', [AuthController::class, 'new_password_verification']);
Route::post('/reSetPassword', [AuthController::class, 'reset_password']);

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/refreshToken', [AuthController::class, 'refresh']);

Route::middleware('auth:sanctum')->group(function () {


    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/governorates', [DriverController::class, 'governorates']);
    Route::get('/profile', [UserController::class, 'get_profile']);
    Route::get('/vehicleTypes', [VehicleTypeController::class, 'vehicle_types']);
    Route::get('/driverImage/{id}', [DriverController::class, 'get_driver_image']);

    // Client Routes
    Route::middleware('role:client')->group(function () {
        Route::post('/shipment/create', [ShipmentController::class, 'create_shipment']);
        Route::get('/shipmentRequest', [ShipmentController::class, 'get_shipment']);
        Route::delete('/shipmentRequest', [ShipmentController::class, 'delete_shipment']);
        Route::put('/shipmentRequest', [ShipmentController::class, 'update_shipment']);
        Route::get('/availableDrivers', [DriverController::class, 'available_drivers']);
        Route::get('/driver/{id}', [DriverController::class, 'get_driver_details']);
        Route::get('/shipment/extend', [ShipmentController::class, 'extend_shipment']);
        Route::post('/shipment/send-to-driver', [ShipmentController::class, 'send_to_driver']);
        Route::post('/review', [ReviewController::class, 'create_review']);
    });

    // Driver Routes
    Route::middleware('role:driver')->group(function () {
        Route::get('/changeDriverAvailability', [DriverController::class, 'change_driver_availability']);
        Route::post('/governorate/attach', [DriverController::class, 'attach_governorate']);
        Route::post('/governorate/detatch', [DriverController::class, 'detatch_governorate']);

        Route::get('/reviews', [ReviewController::class, 'get_driver_reviews']);


        Route::post('/shipment/respond', [ShipmentController::class, 'respond_to_request']);
        Route::post('/shipment/confirm-pickup', [ShipmentController::class, 'confirm_pickup']);
        Route::post('/shipment/confirm-delivery', [ShipmentController::class, 'confirm_delivery']);

        Route::get('/countContinuousSuccessfulShipments', [DriverController::class, 'count_continuous_successful_shipments']);
        Route::post('/driver/setLocation', [DriverController::class, 'set_driver_location']);
    });

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        Route::post('/vehicleType/create', [VehicleTypeController::class, 'create_vehicle_type']);
        Route::put('/vehicleType/update/{id}', [VehicleTypeController::class, 'update_vehicle_type']);

        Route::post('/coefficient/create', [VehicleTypeController::class, 'create_coefficient']);
        Route::put('/coefficient/update', [VehicleTypeController::class, 'update_coefficient']);
        Route::get('/coefficients', [VehicleTypeController::class, 'get_coefficients']);
        Route::get('/subAdmins', [UserController::class, 'get_sub_admins']);
        Route::post('/subAdmin/create', [UserController::class, 'add_sub_admin']);
        Route::put('/subAdmin/update/{id}', [UserController::class, 'update_sub_admin']);
    });

    // Employee-Admin Routes
    Route::middleware('role:employee,admin')->group(function () {
        Route::post('/createDriver', [UserController::class, 'create_driver']);
        Route::get('/getDrivers', [DriverController::class, 'get_drivers']);
        Route::get('/driverDetails/{id}', [DriverController::class, 'get_driver_details_for_admin']);
        Route::post('/searchForDriver', [DriverController::class, 'search_for_driver']);
        Route::put('/editDriver/{id}', [DriverController::class, 'update_driver']);
    });

    // Client-Driver Routes
    Route::middleware('role:client,driver')->group(function () {
        Route::post('/report', [ReportController::class, 'report']);
        Route::put('/editProfile', [UserController::class, 'edit_profile']);

        Route::get('/notifications', [NotificationController::class, 'get_all_notifications']);
        Route::get('/newNotifications/count', [NotificationController::class, 'new_notifications_count']);
        Route::post('/saveDeviceToken', [NotificationController::class, 'save_device_token']);
    });
});
