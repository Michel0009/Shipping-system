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
        $this->clearCache();
    }

    /**
     * Handle the Driver "updated" event.
     */
    public function updated(Driver $driver): void
    {
        $this->clearCache();
    }
    protected function clearCache()
    {
        Cache::tags(['driver_list'])->flush();
    }
}
