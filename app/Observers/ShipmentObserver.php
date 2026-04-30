<?php

namespace App\Observers;

use App\Models\Shipment;
use Illuminate\Support\Facades\Cache;

class ShipmentObserver
{

    public function created(Shipment $shipment): void
    {
        $this->clearCache($shipment);
    }

    /**
     * Handle the Shipment "updated" event.
     */
    public function updated(Shipment $shipment): void
    {
        Cache::forget("driver_{$shipment->driver_id}_shipments");
        $this->clearCache($shipment);
    }

    private function clearCache(Shipment $shipment): void
    {
        Cache::tags(['shipments_all'])->flush();
        Cache::tags(['shipments_user_' . $shipment->user_id])->flush();
        Cache::tags(['shipments_driver_' . $shipment->driver_id])->flush();

        if ($shipment->insurance) {
            Cache::tags(['shipments_insured'])->flush();
        }
    }

}
