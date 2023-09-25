<?php

namespace Lara\Admin\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class FormRowBadgeComponent extends Component
{
	/**
	 * @var string
	 */
	public $labelfield;

	/**
	 * @var string
	 */
	public $labeltext;

	/**
	 * @var string
	 */
	public $badgeplacement;

	/**
	 * @var string
	 */
	public $badgetitle;

	/**
	 * @var string
	 */
	public $badgecontent;

	/**
	 * FormRowBadgeComponent constructor.
	 *
	 * @param string $labelfield
	 * @param string $labeltext
	 * @param string $badgeplacement
	 * @param string $badgetitle
	 * @param string $badgecontent
	 * @return void
	 */
    public function __construct(string $labelfield, string $labeltext, string $badgeplacement, string $badgetitle, string $badgecontent)
    {

        $this->labelfield = $labelfield;
        $this->labeltext = $labeltext;
        $this->badgeplacement = $badgeplacement;
        $this->badgetitle = $badgetitle;
        $this->badgecontent = $badgecontent;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('lara-admin::components.formrowbadge');
    }
}
