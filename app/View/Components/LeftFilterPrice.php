<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LeftFilterPrice extends Component
{
    public $lFilter;

    public function __construct($lFilter)
    {
        $this->lFilter     =   $lFilter;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.left-filter-price');
    }
}
