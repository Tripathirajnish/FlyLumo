<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FlightListReturn extends Component
{
    public $results;

    public function __construct($results)
    {
        $this->results          =   $results;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.flight-list-return');
    }
}
