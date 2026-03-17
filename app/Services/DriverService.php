<?php

namespace App\Services;

use App\Repositories\DriverRepository;
use Illuminate\Support\Facades\Auth;

class DriverService
{

    protected $driverRepository;

    public function __construct(DriverRepository $driverRepository)
    {
        $this->driverRepository = $driverRepository;
    }
    public function change_driver_availability()
    {
        $user = Auth::user();
        $driver = $this->driverRepository->find_by_user_ID($user->id);
        $driver->availability = !$driver->availability;
        $driver->save();
        return [
            'message' => 'تم تغيير الحالة بنجاح',
        ];
    }

    private function get_auth_driver()
    {
        $user = Auth::user();
        return $this->driverRepository->find_by_user_ID($user->id);
    }

    public function get_driver_governorates()
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->get_driver_governorates($driver);
    }

    public function get_governorates()
    {
        return $this->driverRepository->get_governorates();
    }

    public function attach_governorate($govId)
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->attach_governorate($driver, $govId);
    }

    public function detach_governorate($govId)
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->detach_governorate($driver, $govId);
    }
}
