<?php

namespace Lara\Admin\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use Bouncer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;
use Lara\Common\Models\Redirect;
use LaravelLocalization;

class RedirectsController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
	use AdminViewTrait;

	/**
	 * @var string
	 */
	protected $modelClass = Redirect::class;

	/**
	 * @var string
	 */
	protected $clanguage;

	/**
	 * @var string|null
	 */
	protected $routename;

	/**
	 * @var object
	 */
	protected $entity;

	/**
	 * @var object
	 */
	protected $data;

	/**
	 * @var bool
	 */
	protected $ismobile;

	/**
	 * @var bool
	 */
	protected $isbuilder = false;

	public function __construct()
	{

		// create an empty Laravel object to hold all the data
		$this->data = $this->makeNewObject();

		if (!App::runningInConsole()) {

			// get route name
			$this->routename = Route::current()->getName();

			// get entity
			$this->entity = $this->getLaraEntity($this->routename, $this->modelClass);

			// get agent
			$agent = new Agent();
			$this->ismobile = $agent->isMobile();

			// share data with all views, see: https://goo.gl/Aqxquw
			$this->middleware(function ($request, $next) {
				view()->share('isbuilder', $this->isbuilder);
				view()->share('entity', $this->entity);
				view()->share('clanguage', $this->getContentLanguage($request, $this->entity));
				view()->share('ismobile', $this->ismobile);

				return $next($request);
			});

		}

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function index(Request $request)
	{
		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		// get content language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get filters
		$this->data->filters = $this->getIndexFilters($this->entity, $request);

		// get params
		$this->data->params = $this->getIndexParams($this->entity, $request, $this->data->filters);

		// get objects
		$collection = $this->modelClass::langIs($this->clanguage);
		if ($this->data->params->paginate) {
			$this->data->objects = $collection->paginate($this->data->params->perpage);
		} else {
			$this->data->objects = $collection->get();
		}

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function create(Request $request)
	{

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

		// create empty object
		$this->data->object = new $this->modelClass;

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

		$request->merge(['title' => $request->input('redirectfrom')]);

		// save model
		$object = $this->modelClass::create($request->all());

		// flash message
		$this->flashMessage($this->entity->entity_key, 'save_success', 'success');

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $object->id]);

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param Request $request
	 * @param int $id
	 * @return Application|Factory|View
	 */
	public function edit(Request $request, int $id)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get record
		$this->data->object = $this->modelClass::findOrFail($id);

		// lock record
		$this->data->object->lockRecord();

		// get status
		$this->data->status = $this->getObjectStatus($this->entity, $this->data->object);

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function update(Request $request, int $id)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get record
		$object = $this->modelClass::findOrFail($id);

		$request->merge(['title' => $request->input('redirectfrom')]);

		// save object to database
		$object->update($request->all());

		// flash message
		$this->flashMessage($this->entity->entity_key, 'save_success', 'success');

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $id]);

	}

	/**
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function destroy(int $id)
	{

		// check if user has access to method
		$this->authorizer('delete', $this->modelClass);

		// get record
		$object = $this->modelClass::findOrFail($id);

		// delete object
		$object->delete();

		// flash message
		$this->flashMessage($this->entity->entity_key, 'delete_success', 'succes', 1);

		// get last page (pagination) for redirect
		$lastpage = $this->getLastPage($this->entity->entity_key);

		// redirect
		if ($lastpage && is_numeric($lastpage) && $lastpage > 1) {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['page' => $lastpage]);
		} else {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
		}

	}

	/**
	 * Unlock the specified resource
	 *
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function unlock(int $id)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get record
		$object = $this->modelClass::findOrFail($id);

		// unlock record
		$object->unlockRecord();

		// redirect Module Page back to associated Entity
		$this->redirectModulePageToEntity($object, $this->entity);

		// get last page (pagination) for redirect
		$lastpage = $this->getLastPage($this->entity->getEntityKey());

		// redirect
		if ($lastpage && is_numeric($lastpage) && $lastpage > 1) {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['page' => $lastpage]);
		} else {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
		}

	}

}
