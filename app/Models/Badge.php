<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = ['level', 'name', 'text', 'continuous_successful_shipments_condition', 'successful_shipments_percentage_condition', 'continuous_failed_shipments_condition'];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }
}
