<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LeftFilterDptStop extends Component
{
    public $lFilterDptStop,$selected;

    public function __construct($lFilterDptStop,$selected=null)
    {
        $this->lFilterDptStop       =   $lFilterDptStop;
        $this->selected             =   $selected;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.left-filter-dpt-stop');
    }
}
