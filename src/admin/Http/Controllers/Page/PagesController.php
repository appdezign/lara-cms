<?php

namespace Lara\Admin\Http\Controllers\Page;

use Lara\Admin\Http\Controllers\Base\BaseController;

use Illuminate\Http\Request;

use Lara\Common\Models\Page;

class PagesController extends BaseController
{

	public function __construct() {
		parent::__construct();
	}

	protected function make(): Page {
		return Page::create();
	}

}
