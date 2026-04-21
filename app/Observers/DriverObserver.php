<?php

namespace App\Observers;

use App\Models\Driver;
use Illuminate\Support\Facades\Cache;

class DriverObserver
{
    /**
     * Handle the Driver "created" event.
     */
    public function created(Driver $driver): void
    {
        $this->clearListCache();
    }

    /**
     * Handle the Driver "updated" event.
     */
    public function updated(Driver $driver): void
    {
        $this->clearSpecificDriverCache($driver);
    }
    protected function clearSpecificDriverCache(Driver $driver)
    {
        $this->clearListCache();
        Cache::forget("driver_{$driver->id}_driver");
    }

    protected function clearListCache()
    {
        Cache::tags(['driver_list'])->flush();
    }
}
