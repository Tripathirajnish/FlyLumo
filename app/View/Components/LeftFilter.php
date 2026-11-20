<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LeftFilter extends Component
{
    public $lFilter;
    
    public function __construct($lFilter)
    {
        $this->lFilter       =   $lFilter;
    }

    public function render(): View|Closure|string
    {
        return view('components.left-filter');
    }
}
