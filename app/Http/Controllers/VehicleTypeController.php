<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleTypeFormRequest;
use App\Services\VehicleTypeService;

class VehicleTypeController extends Controller
{
    protected VehicleTypeService $vehicleTypeService;

    public function __construct(VehicleTypeService $vehicleTypeService)
    {
        $this->vehicleTypeService = $vehicleTypeService;
    }

    public function create_vehicle_type(VehicleTypeFormRequest $request)
    {
        $this->vehicleTypeService->create_vehicle_type($request->validated());

        return response()->json([
            'message' => 'تم إصافة نوع مركبة جديد بنجاح',
        ]);
    }

    public function update_vehicle_type(VehicleTypeFormRequest $request, $id)
    {
        $this->vehicleTypeService->update_vehicle_type($request->validated(), $id);

        return response()->json([
            'message' => 'تم تعديل نوع المركبة بنجاح',
        ]);
    }

    public function vehicle_types()
    {
        return response()->json(
            $this->vehicleTypeService->get_vehicle_types()
        );
    }

    public function create_coefficient(VehicleTypeFormRequest $request)
    {
        $this->vehicleTypeService->create_coefficient($request->validated());

        return response()->json([
            'message' => 'تم إضافة معامل سعر جديد بنجاح',
        ]);
    }

    public function update_coefficient(VehicleTypeFormRequest $request)
    {
        $this->vehicleTypeService->update_coefficient($request->validated());

        return response()->json([
            'message' => 'تم تعديل قيمة المعامل بنجاح',
        ]);
    }

    public function get_coefficients()
    {
        return response()->json(
            $this->vehicleTypeService->get_coefficients()
        );
    }
}
