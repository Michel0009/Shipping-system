<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        Cache::forget("driver_{$review->driver_id}_avg_rate");
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        Cache::forget("driver_{$review->driver_id}_avg_rate");
    }
}
