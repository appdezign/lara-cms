<?php

namespace Lara\Admin\Http\Controllers\Block;

use Lara\Admin\Http\Controllers\Base\BaseController;

use Lara\Common\Models\Slider;

class SlidersController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function make(): Slider
	{
		return Slider::create();
	}

}
