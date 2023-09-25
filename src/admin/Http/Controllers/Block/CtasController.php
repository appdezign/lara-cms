<?php

namespace Lara\Admin\Http\Controllers\Block;

use Lara\Admin\Http\Controllers\Base\BaseController;

use Lara\Common\Models\Cta;

class CtasController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function make(): Cta
	{
		return Cta::create();
	}

}
