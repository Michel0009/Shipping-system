<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'user_id',
        'badge_id',
        'father_name',
        'mother_name',
        'mother_last_name',
        'birth_date',
        'birth_place',
        'national_number',
        'governorate',
        'city',
        'neighborhood',
        'gender',
        'additional_phone_number',
        'personal_picture',
        'nationality',
        'continuous_successful_shipments'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
    public function car()
    {
        return $this->hasOne(Car::class);
    }
    public function license()
    {
        return $this->hasOne(License::class);
    }
    public function unconvicted_paper()
    {
        return $this->hasOne(Unconvicted_paper::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function governorates()
    {
        return $this->belongsToMany(Governorate::class);
    }
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
    public function posts()
    {
        return $this->belongsToMany(Post::class)->withPivot('date', 'price');
    }
    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }
}
