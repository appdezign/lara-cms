<?php

namespace Lara\Front\Http\Controllers\Api\Blocks;

use Lara\Front\Http\Controllers\Api\Base\BaseApiController;

use Illuminate\Http\Request;

use Lara\Common\Models\Cta;

class CtasController extends BaseApiController
{

	public function __construct() {
		parent::__construct();
	}

	protected function make(): Cta {
		return Cta::create();
	}

}
