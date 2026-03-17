<?php

namespace App\Http\Controllers;

use App\Services\DriverService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    public function change_driver_availability(){
        $result = $this->driverService->change_driver_availability();
        return response()->json($result, 200);
    }
}
