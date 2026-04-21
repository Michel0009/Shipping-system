<?php

namespace App\Observers;

use App\Models\Unconvicted_paper;
use Illuminate\Support\Facades\Cache;

class UnconvictedPaperObserver
{
    /**
     * Handle the Unconvicted_paper "created" event.
     */
    public function created(Unconvicted_paper $unconvicted_paper): void
    {
        Cache::forget("driver_{$unconvicted_paper->driver_id}_docs");
    }
    /**
     * Handle the Unconvicted_paper "deleted" event.
     */
    public function deleted(Unconvicted_paper $unconvicted_paper): void
    {
        Cache::forget("driver_{$unconvicted_paper->driver_id}_docs");
    }
}
