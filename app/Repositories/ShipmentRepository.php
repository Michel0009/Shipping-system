<?php

namespace App\Repositories;

use App\Models\Shipment;

class ShipmentRepository
{
    protected $shipment;
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function create(array $request, $shipmentNumber, $pin, $qrPin)
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
            'qr_pin' => $qrPin,
            'status' => 'جارية',
            'success' => false,
            'delivery_deadline' => now()->addDay(),
        ]);

        $shipment->governorates()->attach([
            $shipmentData['start_governorate_id'] => ['start_end' => 'start']
        ]);
        $shipment->governorates()->attach([
            $shipmentData['end_governorate_id'] => ['start_end' => 'end']
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

    public function get_shipments_for_driver($driver_id)
    {
        $shipments = $this->shipment->where('driver_id', $driver_id)->with('governorates')->latest()->paginate(10);
        return $this->transform_shipments($shipments);
    }

    public function get_shipments_for_user($user_id)
    {
        $shipments = $this->shipment->where('user_id', $user_id)->with('governorates')->latest()->paginate(10);
        return $this->transform_shipments($shipments);
    }

    public function get_shipments()
    {
        $shipments = $this->shipment->with('governorates')->latest()->paginate(10);
        return $this->transform_shipments($shipments);
    }

    public function get_shipments_with_insurance()
    {
        $shipments = $this->shipment->where('insurance', true)->with('governorates')->latest()->paginate(10);
        return $this->transform_shipments($shipments);
    }

    private function transform_shipments($shipments)
    {
        return $shipments->through(function ($shipment) {

            $start = $shipment->governorates
                ->where('pivot.start_end', 'start')
                ->first();

            $end = $shipment->governorates
                ->where('pivot.start_end', 'end')
                ->first();

            return [
                'id' => $shipment->id,
                'user_id' => $shipment->user_id,
                'driver_id' => $shipment->driver_id,
                'shipment_number' => $shipment->shipment_number,
                'width' => $shipment->width,
                'height' => $shipment->height,
                'length' => $shipment->length,
                'weight' => $shipment->weight,
                'object' => $shipment->object,
                'insurance' => $shipment->insurance,
                'price' => $shipment->price,
                'status' => $shipment->status,
                'success' => $shipment->success,
                'start_governorate' => $start?->name,
                'end_governorate' => $end?->name,
            ];
        });
    }
}
