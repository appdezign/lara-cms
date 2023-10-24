<?php

namespace Lara\Admin\Http\Controllers\Builder;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;

use Illuminate\View\View;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminDbTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Lara\Admin\Http\Traits\AdminBuilderTrait;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Entitygroup;
use Lara\Common\Models\Form;

class FormController extends EntityController
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminDbTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminViewTrait;

	use AdminBuilderTrait;

	/**
	 * @var string
	 */
	protected $modelClass = Form::class;

	public function __construct()
	{
		parent::__construct();

		$this->isbuilder = true;
		$this->isformbuilder = true;
	}

	/**
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function index(Request $request)
	{

		$this->data->force = $this->getRequestParam($request, 'force');

		// get filter
		$filtergroup = Entitygroup::keyIs('form')->first()->id;
		$this->data->filtergroup = $filtergroup;

		// get filtered objects
		$this->data->objects = Entity::when(is_numeric($filtergroup), function ($query) use ($filtergroup) {
			return $query->where('group_id', $filtergroup);
		})
			->with('columns')
			->with('objectrelations')
			->orderby('menu_position', 'asc')
			->get();

		// get entity groups
		$this->data->entityGroups = Entitygroup::keyIs('form')->pluck('title', 'id')->toArray();

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data'          => $this->data,
			'isformbuilder' => $this->isformbuilder,
		]);

	}

	/**
	 * @return Application|Factory|View
	 */
	public function create()
	{

		$this->data->object = new Entity;

		$this->data->menuParents = $this->builderGetAdminMenuGroups(true);

		$this->data->entityGroups = Entitygroup::keyIs('form')->pluck('title', 'id')->toArray();

		$this->data->defaultGroup = Entitygroup::keyIs('form')->first();

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data'          => $this->data,
			'isformbuilder' => $this->isformbuilder,
		]);

	}

}
