<?php

namespace App\Http\Controllers;


use App\Http\Requests\DriverFormRequest;
use App\Services\DriverService;
use Exception;

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
    public function get_drivers()
    {
        $drivers = $this->driverService->get_drivers();
        return response()->json($drivers);
    }
    public function get_driver_details_for_admin(DriverFormRequest $request, $id)
    {
        $driverId = $request->id;
        $driver = $this->driverService->get_driver_details_for_admin($driverId);
        return response()->json($driver);
    }
    public function search_for_driver(DriverFormRequest $request)
    {
        $validated = $request->validated();
        $driver = $this->driverService->search_for_driver($validated);
        return response()->json($driver);
    }
    public function update_driver(DriverFormRequest $request, $id)
    {
        $validated = $request->validated();
        $result = $this->driverService->update_driver($id, $validated);
        return response()->json(['message' => $result['message']], $result['code']);
    }
}
