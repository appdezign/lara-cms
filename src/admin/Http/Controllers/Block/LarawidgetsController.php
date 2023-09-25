<?php

namespace Lara\Admin\Http\Controllers\Block;

use Lara\Admin\Http\Controllers\Base\BaseController;

use Lara\Common\Models\Larawidget;

class LarawidgetsController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function make(): Larawidget
	{
		return Larawidget::create();
	}

}
