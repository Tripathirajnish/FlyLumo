<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    function credit()
    {
        return $this->belongsTo(Credit::class);
    }

    function billing()
    {
        return $this->belongsTo(Billing::class);
    }

}
