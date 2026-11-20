<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LeftFilterAirline extends Component
{
    public $lFilterAirl,$selected;

    public function __construct($lFilterAirl,$selected=null)
    {
        $this->lFilterAirl       =   $lFilterAirl;
        $this->selected          =   $selected;
    }
    
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.left-filter-airline');
    }
}
