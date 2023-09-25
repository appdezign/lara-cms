<?php

namespace Lara\Admin\Http\Controllers\Tools;

use Lara\Admin\Http\Controllers\Base\BaseController;

use Lara\Admin\Http\Traits\LaraAdminHelpers;


use Illuminate\Http\Request;

use Lara\Common\Models\Setting;

class SettingsController extends BaseController {

	use LaraAdminHelpers;

	public function __construct() {
		parent::__construct();
	}

	protected function make(): Setting {
		return Setting::create();
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

		// check if the company GEO coordionates are valid
		$this->checkGeoSettings();

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		return view($viewfile, [
			'data' => $this->data,
		]);

	}


}
