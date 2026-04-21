<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->clearSpecificDriverCache($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->clearAllCache($user);
    }
    protected function clearSpecificDriverCache(User $user)
    {

        if ($user->driver) {
            $this->clearListCache();
            Cache::forget("driver_{$user->driver->id}_user");
        }

    }

    protected function clearListCache()
    {
        Cache::tags(['driver_list'])->flush();
    }
    protected function clearAllCache($user){
        if ($user->driver) {
            $driverId = $user->driver->id;
            Cache::forget("driver_{$driverId}_user");
            Cache::forget("driver_{$driverId}_driver");
            Cache::forget("driver_{$driverId}_gov");
            Cache::forget("driver_{$driverId}_avg_rate");
            Cache::forget("driver_{$driverId}_car");
            Cache::forget("driver_{$driverId}_docs");
            Cache::forget("driver_{$driverId}_badge");
            Cache::forget("driver_{$driverId}_shipments");
            $this->clearListCache();
        }

    }
}
