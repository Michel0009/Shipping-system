<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Cache;

Schedule::call(function () {

    app(\App\Repositories\UserRepository::class)->delete_unverified_users();
})->hourly();

Schedule::call(function () {
    app(\App\Services\UserService::class)->process_expired_bans();
})->daily();

Schedule::call(function () {

    $driverRepository = app(\App\Repositories\DriverRepository::class);

    $drivers = $driverRepository->get_all_available_drivers();

    foreach ($drivers as $driver) {
        $key = "location_driver_{$driver->id}";

        if (!Cache::has($key)) {
            $driver->availability = false;
            $driverRepository->save($driver);
        }
    }

})->everyThirtyMinutes();

Schedule::call(function () {

    $shipmentRepository = app(\App\Repositories\ShipmentRepository::class);
    $driverRepository = app(\App\Repositories\DriverRepository::class);

    $shipments = $shipmentRepository->get_expired_shipments(); 

    foreach ($shipments as $shipment) {

        $shipment->status = 'غير مستلمة';
        $shipmentRepository->save($shipment);

        if ($shipment->driver_id) {
            $driver = $driverRepository->find_driver($shipment->driver_id);

            if ($driver) {
                $driver->continuous_successful_shipments = 0;
                $driverRepository->save($driver);
            }
        }
    }

})->everyThirtyMinutes();

// Schedule::call(function () {
//     $shipmentRepository = app(\App\Repositories\ShipmentRepository::class);
//     $driverRepository = app(\App\Repositories\DriverRepository::class);
//     app(\App\Repositories\UserRepository::class)->freeze_accounts();
// })->monthlyOn(1, '03:00');