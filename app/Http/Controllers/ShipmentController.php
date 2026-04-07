<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShipmentFormRequest;
use App\Services\ShipmentService;
use Illuminate\Http\JsonResponse;


class ShipmentController extends Controller
{

    protected ShipmentService $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    public function create_shipment(ShipmentFormRequest $request): JsonResponse
    {
        $result = $this->shipmentService->create_shipment($request->validated());

        return response()->json([
            'message' => 'تم إنشاء الشحنة بنجاح، الرجاء اختيار السائق لشحنتك.',
            'data' => $result
        ]);
    }

    public function get_shipment()
    {
        $data = $this->shipmentService->get_shipment_request();

        return response()->json([
            'data' => $data
        ]);
    }

    public function delete_shipment()
    {
        $this->shipmentService->delete_shipment_request();

        return response()->json([
            'message' => 'تم حذف طلب الشحنة بنجاح'
        ]);
    }

    public function update_shipment(ShipmentFormRequest $request)
    {
        $result = $this->shipmentService->update_shipment_request($request->validated());

        return response()->json([
            'message' => 'تم تعديل طلب الشحنة بنجاح',
            'data' => $result
        ]);
    }

    public function extend_shipment()
    {
        return response()->json(
            $this->shipmentService->extend_shipment_request()
        );
    }

    public function send_to_driver(ShipmentFormRequest $request)
    {
        $result = $this->shipmentService->send_to_driver($request->validated());

        return response()->json([
            'message' => $result,
        ]);
    }

    public function respond_to_request(ShipmentFormRequest $request)
    {
        $result = $this->shipmentService->respond_to_request($request->validated());

        return response()->json([
            'message' => $result
        ]);
    } 
  
}
