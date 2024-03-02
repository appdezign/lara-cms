<?php

namespace Lara\Admin\Http\Controllers\Seo;

use Illuminate\Http\Request;
use Lara\Admin\Http\Controllers\Base\BaseController;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;
use Lara\Common\Models\Headertag;

class HeadertagsController extends BaseController
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
	use AdminViewTrait;

	public function __construct()
	{
		parent::__construct();
	}

	protected function make(): Headertag
	{
		return Headertag::create();
	}

	public function index(Request $request)
	{

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get filters
		$this->data->filters = $this->getIndexFilters($this->entity, $request);

		// get params
		$this->data->params = $this->getIndexParams($this->entity, $request, $this->data->filters);

		// get objects
		$this->data->objects = $this->getEntityObjects($this->entity, $request, $this->data->params, $this->data->filters);

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

}
