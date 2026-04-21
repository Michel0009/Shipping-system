<?php

namespace App\Repositories;

use App\Models\Shipment;
use App\Models\GovernorateShipment;


class ShipmentRepository
{
    protected $shipment;
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function create(array $request, $shipmentNumber, $pin)
    {
        $shipmentData = $request['shipment'];

        $shipment = Shipment::create([
            'user_id' => $request['user_id'],
            'driver_id' => $request['driver_id'],
            'shipment_number' => $shipmentNumber,
            'weight' => $shipmentData['weight'],
            'height' => $shipmentData['height'],
            'width' => $shipmentData['width'],
            'length' => $shipmentData['length'],
            'object' => $shipmentData['object'],
            'insurance' => $shipmentData['insurance'],
            'start_position_lat' => $shipmentData['start_position_lat'],
            'start_position_lng' => $shipmentData['start_position_lng'],
            'end_position_lat' => $shipmentData['end_position_lat'],
            'end_position_lng' => $shipmentData['end_position_lng'],
            'price' => $request['price'],
            'pin' => $pin,
            'status' => 'جارية',
            'success' => false,
            'delivery_deadline' => now()->addDay(),
        ]);

        $shipment->governorates()->attach([
            $shipmentData['start_governorate_id'] => ['start_end' => 'start'],
            $shipmentData['end_governorate_id'] => ['start_end' => 'end'],
        ]);

        return $shipment;
    }

    public function find_shipment($id)
    {
        return $this->shipment->where('id', $id)->first();
    }

    public function save(Shipment $shipment): bool
    {
        return $shipment->save();
    }
    public function get_shipments_by_driver_id($driver_id)
    {
        return $this->shipment->where('driver_id', $driver_id)->get();
    }
}
