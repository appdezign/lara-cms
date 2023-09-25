<?php

namespace Lara\Admin\Http\Controllers\Menu;

use Lara\Admin\Http\Controllers\Base\BaseController;

use Lara\Common\Models\Menu;

class MenusController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function make(): Menu
	{
		return Menu::create();
	}

}
