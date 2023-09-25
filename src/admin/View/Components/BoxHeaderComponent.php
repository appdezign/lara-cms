<?php

namespace Lara\Admin\View\Components;

use Illuminate\View\Component;

class BoxHeaderComponent extends Component
{

	/**
	 * @var string
	 */
	public $collapseid;
	public $cstate;

	/**
	 * BoxHeaderComponent constructor.
	 *
	 * @param string $collapseid
	 * @param string $cstate
	 * @return void
	 */
    public function __construct(string $collapseid, string $cstate)
    {
        $this->cstate = $cstate;
        $this->collapseid = $collapseid;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('lara-admin::components.boxheader');
    }
}
