<?php

namespace Lara\Admin\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class FormRowReqComponent extends Component
{

	/**
	 * @var string
	 */
	public $emessage;

    /**
     * Create a new component instance.
     *
     * @param string $emessage
     * @return void
     */
    public function __construct(string $emessage)
    {
	    $this->emessage = $emessage;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('lara-admin::components.formrowreq');
    }
}
