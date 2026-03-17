<?php

namespace App\Http\Controllers;


use App\Http\Requests\DriverFormRequest;
use App\Services\DriverService;

class DriverController extends Controller
{
    protected $driverService;


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
}
