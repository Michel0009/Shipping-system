<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = ['driver_id','license_file'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
