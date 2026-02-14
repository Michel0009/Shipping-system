<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['reporter_id','reported_id','type','description'];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }


    public function reported_user()
    {
        return $this->belongsTo(User::class, 'reported_id');
    }
}
