<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    //protected $fillable = ['name','code','city_id','state_id','country_id'];

    public function city()
    {
        return $this->belongsTo(City::class,'city_id');
    }

    function country()
    {
        return $this->belongsTo(Country::class);
    }

}
