<?php

namespace App\Providers;

use App\Models\Car;
use App\Models\Car_paper;
use App\Models\Driver;
use App\Models\Review;
use App\Models\Shipment;
use App\Models\User;
use App\Observers\CarObserver;
use App\Observers\CarPaperObserver;
use App\Observers\DriverObserver;
use App\Observers\ReviewObserver;
use App\Observers\ShipmentObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Driver::observe(DriverObserver::class);
        User::observe(UserObserver::class);
        Car::observe(CarObserver::class);
        Car_paper::observe(CarPaperObserver::class);
        Review::observe(ReviewObserver::class);
        Shipment::observe(ShipmentObserver::class);
    }
}
