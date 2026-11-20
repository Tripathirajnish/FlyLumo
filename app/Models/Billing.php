<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;

    function country()
    {
        return $this->belongsTo(Country::class);
    }

    function state()
    {
        return $this->belongsTo(State::class);
    }

}
