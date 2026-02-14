<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unconvicted_paper extends Model
{
    protected $fillable = ['driver_id', 'uncovicted_file'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
