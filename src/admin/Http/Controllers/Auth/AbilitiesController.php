<?php

namespace Lara\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Lara\Admin\Http\Traits\LaraAdminHelpers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Userability;
use Silber\Bouncer\Database\Ability;

use Auth;
use Session;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Carbon\Carbon;

class AbilitiesController extends Controller
{

	use LaraAdminHelpers;

	/**
	 * @var string
	 */
	protected $modelClass = Ability::class;

	/**
	 * @var string
	 */
	protected $laraModelClass = Userability::class;

	/**
	 * @var string|null
	 */
	protected $routename = '';

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
			$this->entity = $this->getLaraEntity($this->routename, $this->laraModelClass);

			// get agent
			$agent = new Agent();
			$this->ismobile = $agent->isMobile();

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
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function index(Request $request)
	{

		// check if user has administrator role
		$this->checkAdminAccess();

		// get filters
		$this->data->filters = $this->getIndexFilters($this->entity, $request, ['entity_key']);

		// get params
		$this->data->params = $this->getIndexParams($this->entity, $request, $this->data->filters);

		// get objects
		if ($this->data->filters->autofilter) {

			$collection = new $this->modelClass;

			foreach ($this->data->filters->autofilters as $column => $value) {
				$collection = $collection->where($column, $value);
			}

			$collection = $collection->orderBy('entity_key', 'asc')->orderBy('name', 'asc');
			if ($this->data->params->paginate) {
				$this->data->objects = $collection->paginate($this->data->params->perpage);
				$this->checkPagination($this->data->objects->count(), $this->entity);
			} else {
				$this->data->objects = $collection->get();
			}

		} else {

			$collection = $this->modelClass::orderBy('entity_key', 'asc')->orderBy('name', 'asc');
			if ($this->data->params->paginate) {
				$this->data->objects = $collection->paginate($this->data->params->perpage);
				$this->checkPagination($this->data->objects->count(), $this->entity);
			} else {
				$this->data->objects = $collection->get();
			}

		}

		$this->data->availableEntities = $this->modelClass::distinct('entity_key')->orderBy('entity_key', 'asc')->pluck('entity_key', 'entity_key');

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
	 * @return Application|Factory|View
	 */
	public function create()
	{

		// check if user has administrator role
		$this->checkAdminAccess();
		$this->data->object = new $this->modelClass;

		$this->data->crudlist = config('lara-admin.abilities');

		$this->data->entitytypes = Entity::all()->pluck('entity_key', 'entity_model_class');

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

		// check if user has administrator role
		$this->checkAdminAccess();

		// set friendly names
		$entityKey = strtolower(class_basename($request->entity_type));
		$entityTitle = ucfirst($entityKey) . ' ' . ucfirst($request->name);
		$request->merge(['title' => $entityTitle]);
		$request->merge(['entity_key' => $entityKey]);

		$check = $this->modelClass::where('entity_key', $entityKey)
			->where('name', $request->name)
			->first();

		if ($check) {

			flash('Error: duplicate ability')->error();

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

		} else {

			// save model (force mass assignment)
			$object = $this->modelClass::forceCreate($request->only('name', 'title', 'entity_type', 'entity_key'));

			flash(ucfirst($this->entity->getEntityKey()) . ' created successfully')->success();

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $object->id]);

		}

	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return void
	 */
	public function show(int $id)
	{

		// check if user has administrator role
		$this->checkAdminAccess();

		dd($id);

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 * @return Application|Factory|View
	 */
	public function edit(int $id)
	{

		// check if user has administrator role
		$this->checkAdminAccess();

		// get record
		$this->data->object = $this->modelClass::findOrFail($id);

		// lock record
		$this->lockRecord($id);

		$this->data->crudlist = config('lara-admin.abilities');

		$this->data->entitytypes = Entity::all()->pluck('entity_key', 'entity_model_class');

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

		// check if user has administrator role
		$this->checkAdminAccess();

		// get record
		$object = $this->modelClass::findOrFail($id);

		// set friendly names
		$entityKey = strtolower(class_basename($request->entity_type));
		$entityTitle = ucfirst($entityKey) . ' ' . ucfirst($request->name);
		$request->merge(['title' => $entityTitle]);
		$request->merge(['entity_key' => $entityKey]);

		// force mass assignment
		$this->modelClass::unguard();

		// save model
		$object->update($request->only('name', 'title', 'entity_type', 'entity_key'));

		// prevent mass assignment
		$this->modelClass::reguard();

		flash(ucfirst($this->entity->getEntityKey()) . ' saved successfully')->success();

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $id]);

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function destroy(int $id)
	{

		// check if user has administrator role
		$this->checkAdminAccess();

		// get record
		$object = $this->modelClass::findOrFail($id);

		$object->delete();

		flash(ucfirst($this->entity->getEntityKey()) . ' deleted successfully')->success();

		// get last page (pagination) for redirect
		$lastpage = $this->getLastPage($this->entity->getEntityKey());

		// redirect
		if ($lastpage) {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['page' => $lastpage]);
		} else {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
		}

	}

	/**
	 * @param int $id
	 * @return void
	 */
	public function lockRecord(int $id)
	{

		// check if user has administrator role
		$this->checkAdminAccess();

		// get record
		$object = $this->modelClass::findOrFail($id);

		$object->locked_at = Carbon::now();
		$object->locked_by = Auth::user()->id;
		$object->save();

	}

	/**
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function unlock(int $id)
	{

		// check if user has administrator role
		$this->checkAdminAccess();

		// get record
		$object = $this->modelClass::findOrFail($id);

		// unlock record
		$object->locked_at = null;
		$object->locked_by = null;
		$object->save();

		// get last page (pagination) for redirect
		$lastpage = $this->getLastPage($this->entity->getEntityKey());

		// redirect
		if ($lastpage) {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['page' => $lastpage]);
		} else {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
		}

	}
}
