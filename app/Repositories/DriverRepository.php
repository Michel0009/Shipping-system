<?php

namespace App\Repositories;

use App\Models\Driver;
use App\Models\Governorate;
use App\Models\License;
use App\Models\Unconvicted_paper;

class DriverRepository
{
    protected $driver;
    protected $license;
    protected $uncovicted_paper;
    public function __construct(Driver $driver, License $license, Unconvicted_paper $unconvicted_paper)
    {
        $this->driver = $driver;
        $this->license=$license;
        $this->uncovicted_paper=$unconvicted_paper;
    }
    public function create(array $data): Driver
    {
        return $this->driver->create($data);
    }
    public function create_license(array $data): License
    {
        return $this->license->create($data);
    }
    public function create_unconvicted_paper(array $data): Unconvicted_paper
    {
        return $this->uncovicted_paper->create($data);
    }
    public function attach_governorates($driver,array $governorateIds){
        return $driver->governorates()->sync($governorateIds);
    }

    public function find_by_user_ID($id)
    {
        return $this->driver->where('user_id', $id)->first();
    }

    public function get_governorates()
    {
        return Governorate::query()->select('id', 'name')->get();
    }

    public function get_driver_governorates($driver)
    {
        return $driver->governorates()->get();
    }

    public function attach_governorate($driver,$governorateId)
    {
        return $driver->governorates()->syncWithoutDetaching([$governorateId]);
    }

    public function detach_governorate($driver,$governorateId)
    {
        return $driver->governorates()->detach($governorateId);
    }
}
