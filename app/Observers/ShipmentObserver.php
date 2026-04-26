<?php

namespace App\Observers;

use App\Models\Shipment;
use Illuminate\Support\Facades\Cache;

class ShipmentObserver
{
    /**
     * Handle the Shipment "updated" event.
     */
    public function updated(Shipment $shipment): void
    {
        Cache::forget("driver_{$shipment->driver_id}_shipments");
    }

}
