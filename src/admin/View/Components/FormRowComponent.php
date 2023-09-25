<?php

namespace Lara\Admin\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class FormRowComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('lara-admin::components.formrow');
    }
}
