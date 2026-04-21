<?php

namespace App\Observers;

use App\Models\Car;
use Illuminate\Support\Facades\Cache;

class CarObserver
{
    /**
     * Handle the Car "updated" event.
     */
    public function updated(Car $car): void
    {
        Cache::forget("driver_{$car->driver_id}_car");
        Cache::tags(['driver_list'])->flush();
    }
}
