<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FlightListPrice extends Component
{
    public $key,$code,$searchguid,$results;

    public function __construct($key,$code,$searchguid,$results)
    {
        $this->key              =   $key;
        $this->code             =   $code;
        $this->searchguid       =   $searchguid;
        $this->results          =   $results;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.flight-list-price');
    }
}
