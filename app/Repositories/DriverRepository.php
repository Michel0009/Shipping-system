<?php

namespace App\Repositories;

use App\Models\Badge;
use App\Models\Coefficient;
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
    public function save(Driver $driver): bool
    {
        return $driver->save();
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

    public function get_badge($driver)
    {
        return Badge::where('id', $driver->badge_id)->select('level','name','text')->first();
    }

    public function get_available_drivers($shipment)
    {
        return $this->driver->with([
            'user',
            'car.vehicle_type',
            'reviews',
            'badge',
            'governorates'
        ])
        ->where('availability', true)
        ->get()
        ->filter(function ($driver) use ($shipment) {

            $govs = $driver->governorates->pluck('id')->toArray();
            if (
                !in_array($shipment['start_governorate_id'], $govs) ||
                !in_array($shipment['end_governorate_id'], $govs)
            ) {
                return false;
            }

            $type = $driver->car->vehicle_type;

            return
                $shipment['weight'] >= $type->min_weight &&
                $shipment['weight'] <= $type->max_weight &&
                $shipment['length'] >= $type->min_length &&
                $shipment['length'] <= $type->max_length &&
                $shipment['width'] >= $type->min_width &&
                $shipment['width'] <= $type->max_width &&
                $shipment['height'] >= $type->min_height &&
                $shipment['height'] <= $type->max_height;
        });
    }

    public function get_coefficients()
    {
        return Coefficient::pluck('value', 'name');
    }

    public function find_driver($id)
    {
        return $this->driver->where('id', $id)->first();
    }
}
