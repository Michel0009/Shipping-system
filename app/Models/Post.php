<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'weight',
        'height',
        'width',
        'length',
        'object',
        'insurance',
        'start_position_lat',
        'start_position_lng',
        'end_position_lat',
        'end_position_lng',
        'start_location_details',
        'end_location_details',
        'max_price',
        'min_price',
        'last_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function drivers()
    {
        return $this->belongsToMany(Driver::class)->withPivot('date','price');
    }
    public function governorates()
    {
        return $this->belongsToMany(Governorate::class)->withPivot('start_end');
    }
}
