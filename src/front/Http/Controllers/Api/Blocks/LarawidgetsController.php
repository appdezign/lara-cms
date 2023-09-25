<?php

namespace Lara\Front\Http\Controllers\Api\Blocks;

use Lara\Front\Http\Controllers\Api\Base\BaseApiController;

use Illuminate\Http\Request;

use Lara\Common\Models\Larawidget;

class LarawidgetsController extends BaseApiController
{

	public function __construct() {
		parent::__construct();
	}

	protected function make(): Larawidget {
		return Larawidget::create();
	}

}
