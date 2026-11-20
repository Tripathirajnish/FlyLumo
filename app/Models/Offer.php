<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    function flighttype()
    {
        return $this->belongsTo(Master::class,'master_flighttype_id');
    }

    function flightstop()
    {
        return $this->belongsTo(Master::class,'master_flightstop_id');
    }

}
