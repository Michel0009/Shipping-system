<?php

namespace App\Services;

use App\Repositories\DriverRepository;
use Illuminate\Support\Facades\Auth;

class DriverService
{
    protected DriverRepository $driverRepository;
    public function __construct(DriverRepository $driverRepository)
    {
        $this->driverRepository = $driverRepository;
    }

    public function change_driver_availability()
    {
        $user = Auth::user();
        $driver = $this->driverRepository->findByUserId($user->id);
        $driver->availability = !$driver->availability;
        $driver->save();
        return [
            'message' => 'تم تغيير الحالة بنجاح',
        ];
    }
}
