<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FareleaderFlightBookingDetail extends Component
{
    public $key,$fields,$results;

    public function __construct($key,$fields,$results)
    {
        $this->key              =   $key;
        $this->fields           =   $fields;
        $this->results          =   $results;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.fareleader-flight-booking-detail');
    }
}
