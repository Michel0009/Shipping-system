<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'user_id',
        'driver_id',
        'shipment_number',
        'width',
        'height',
        'length',
        'weight',
        'object',
        'insurance',
        'start_position_lat',
        'start_position_lng',
        'end_position_lat',
        'end_position_lng',
        'price',
        'pin',
        'status',
        'success'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    public function governorates()
    {
        return $this->belongsToMany(Governorate::class)->withPivot('start_end');
    }
}
