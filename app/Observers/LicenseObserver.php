<?php

namespace App\Observers;

use App\Models\License;
use Illuminate\Support\Facades\Cache;

class LicenseObserver
{
    /**
     * Handle the License "created" event.
     */
    public function created(License $license): void
    {
        Cache::forget("driver_{$license->driver_id}_docs");
    }
    /**
     * Handle the License "deleted" event.
     */
    public function deleted(License $license): void
    {
        Cache::forget("driver_{$license->driver_id}_docs");
    }
}
