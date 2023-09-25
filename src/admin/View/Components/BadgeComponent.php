<?php

namespace Lara\Admin\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class BadgeComponent extends Component
{

	/**
	 * @var string
	 */
	public $placement;

    /**
     * Create a new component instance.
     *
	 * @param string $placement
     * @return void
	 */
    public function __construct(string $placement)
    {
	    $this->placement = $placement;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('lara-admin::components.badge');
    }
}
