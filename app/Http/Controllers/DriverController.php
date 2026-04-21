<?php

namespace App\Http\Controllers;


use App\Http\Requests\DriverFormRequest;
use App\Services\DriverService;

class DriverController extends Controller
{
    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    public function change_driver_availability()
    {
        $result = $this->driverService->change_driver_availability();
        return response()->json($result, 200);
    }
    public function governorates()
    {
        return response()->json(
            $this->driverService->get_governorates()
        );
    }

    public function attach_governorate(DriverFormRequest $request)
    {
        $gov_id = $request->validated()['gov_id'];

        $this->driverService->attach_governorate($gov_id);

        return response()->json([
            'message' => 'تم تعديل خط النقل بنجاح'
        ]);
    }

    public function detatch_governorate(DriverFormRequest $request)
    {
        $gov_id = $request->validated()['gov_id'];

        $this->driverService->detach_governorate($gov_id);

        return response()->json([
            'message' => 'تم تعديل خط النقل بنجاح'
        ]);
    }

    public function available_drivers()
    {
        $drivers = $this->driverService->get_available_drivers();

        return response()->json([
            'data' => $drivers
        ]);
    }

    public function get_driver_details($id)
    {
        $driver = $this->driverService->get_driver_details($id);

        return response()->json($driver);
    }

    public function get_driver_image($id)
    {
        return $this->driverService->get_driver_image($id);
    }

    public function count_continuous_successful_shipments()
    {
        $count = $this->driverService->count_continuous_successful_shipments();

        return response()->json([
            'count' => $count
        ]);
    }

    public function set_driver_location(DriverFormRequest $request)
    {
        $this->driverService->set_driver_location($request->validated());

        return response()->json([
            'message' => 'تم تحديث موقع السائق'
        ]);
    }
}
