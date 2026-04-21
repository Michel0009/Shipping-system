<?php

namespace App\Observers;

use App\Models\Car_paper;
use Illuminate\Support\Facades\Cache;

class CarPaperObserver
{
    /**
     * Handle the Car_paper "created" event.
     */
    public function created(Car_paper $car_paper): void
    {
        Cache::forget("driver_{$car_paper->car->driver_id}_docs");
    }
    /**
     * Handle the Car_paper "deleted" event.
     */
    public function deleted(Car_paper $car_paper): void
    {
        Cache::forget("driver_{$car_paper->car->driver_id}_docs");
    }
}
