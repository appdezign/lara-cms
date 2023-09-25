<?php

namespace Lara\Admin\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Lara\Admin\Http\Traits\LaraAdminHelpers;
use Lara\Admin\Http\Traits\LaraBuilder;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Entitygroup;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

class EntityController extends Controller
{

	use LaraAdminHelpers;
	use LaraBuilder;

	/**
	 * @var string
	 */
	protected $modelClass = Entity::class;

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
	protected $isbuilder = false;


	/**
	 * @var bool
	 */
	protected $isformbuilder = false;

	/**
	 * @var bool
	 */
	protected $ismobile;

	public function __construct()
	{

		$this->isbuilder = true;

		// restrict the Entity Builder to administrators
		$this->middleware(function ($request, $next) {
			if (!Auth::user()->isAn('administrator')) {
				return response()->view('lara-admin::errors.405', [], 405);
			}

			return $next($request);
		});

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

			$this->middleware(function ($request, $next) {
				view()->share('isbuilder', $this->isbuilder);
				view()->share('entity', $this->entity);
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

		$this->data->force = $this->getRequestParam($request, 'force');

		// get filter
		$defaultEntityGroup = Entitygroup::keyIs('entity')->first();
		$filtergroup = $this->getRequestParam($request, 'egroup', $defaultEntityGroup->id, 'builder');
		$this->data->filtergroup = $filtergroup;

		// get filtered objects
		$this->data->objects = Entity::when(is_numeric($filtergroup), function ($query) use ($filtergroup) {
			return $query->where('group_id', $filtergroup);
		})->orderby('menu_position', 'asc')
			->get();

		// get entity groups
		$this->data->entityGroups = Entitygroup::keyIsNot('form')->pluck('title', 'id')->toArray();

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

		$this->data->menuParents = $this->builderGetAdminMenuGroups($this->isformbuilder);

		$this->data->entityGroups = Entitygroup::keyIs('entity')->pluck('title', 'id')->toArray();

		$this->data->defaultGroup = Entitygroup::keyIs('entity')->first();

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
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{

		$entity_key = str_slug($request->input('entity_key')); // page, blog

		// check if entitykey already exists
		$check = Entity::where('entity_key', $entity_key)->first();

		if (empty($check)) {

			$object = $this->builderCreateNewEntity($request, $entity_key);

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.edit',
				['id' => $object->id]);

		} else {

			flash('Error: Entity key already exists')->error();

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.index');
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

		dd($id);

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param Request $request
	 * @param int $id
	 * @return Application|Factory|RedirectResponse|View
	 */
	public function edit(Request $request, int $id)
	{

		// force ability to update entity
		$this->data->force = $this->getRequestParam($request, 'force');

		// get record
		$this->data->object = Entity::findOrFail($id);

		// purge soft deleted records
		$purge = $request->get('purge');
		if($purge == 'trash') {

			$modelClass = $this->data->object->entity_model_class;
			$modelClass::onlyTrashed()->forceDelete();

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.edit', ['id' => $id]);
		}

		// get object count
		$entityModelClass = $this->data->object->getEntityModelClass();
		$this->data->modelCount = $entityModelClass::count();

		// avoid errors if model does not use soft deletes
		try {
			$this->data->trashCount = $entityModelClass::onlyTrashed()->count();
			$this->data->totalCount = $entityModelClass::withTrashed()->count();
		} catch (\Exception $e) {
			$this->data->trashCount = 0;
			$this->data->totalCount = $this->data->modelCount;
		}

		// get fields for this entity
		if($this->isformbuilder) {
			$this->data->customcolumns = $this->data->object->customcolumns
				->sortBy([['fieldhook', 'asc'],['position', 'asc']]);
		} else {
			$this->data->customcolumns = $this->data->object->customcolumns;
		}

		// sort manually (before, between, after)
		$before = array();
		$between = array();
		$after = array();
		foreach($this->data->customcolumns as $custcol) {
			$varname = $custcol->fieldhook;
			$$varname[] = $custcol;
		}
		$this->data->customcolumns = collect(array_merge($before, $between, $after));

		// relations
		$this->data->relationTypes = config('lara-admin.relationTypes');

		// get views
		$this->data->entityViews = $this->data->object->views()->get();

		// get active methods
		$this->data->activeMethods = $this->data->entityViews->unique('method')->pluck('method')->toArray();

		// get available methods, based on entity group
		$this->data->availableMethods = $this->builderGetAvailableMethods($this->data->activeMethods, $this->isformbuilder);

		// get current relations
		$this->data->relations = $this->data->object->relations()->with('relatedEntity')->get();

		// get IDs from current related entities
		$this->data->activeRelations = $this->data->relations->pluck('id')->toArray();

		// get available entities for creating new relations
		$this->data->relatableEntities = $this->builderGetRelatableEntities($this->data->activeRelations);

		// get field list
		$this->data->fieldList = $this->data->customcolumns->pluck('fieldtitle', 'fieldname')->toArray();

		// lock record
		$this->data->object->lockRecord();

		// get sort columns
		$this->data->sortfields = $this->builderGetSortFields($this->data->object);

		// get all available admin menu groups
		$this->data->menuParents = $this->builderGetAdminMenuGroups($this->isformbuilder);

		// get all entity groups
		if ($this->isformbuilder) {
			$this->data->entityGroups = Entitygroup::keyIs('form')->pluck('title', 'id')->toArray();
		} else {
			$this->data->entityGroups = Entitygroup::keyIsNot('form')->pluck('title', 'id')->toArray();
		}

		// get all available field types
		$this->data->fieldTypes = $this->builderGetFieldTypes($this->isformbuilder);

		// get available hooks (after, between, before)
		$this->data->fieldHooks = config('lara-admin.fieldHooks');
		$this->data->formFieldHooks = config('lara-admin.formFieldHooks');

		// get available field states (enabled, enabledif)
		$this->data->fieldStates = config('lara-admin.fieldStates');

		// get entity view types (single, list, grid, etc)
		$this->data->entityViewTypes = $this->builderGetEntityViewTypes($this->isformbuilder);

		// get view tag options
		$this->data->entityViewShowTags = config('lara-admin.entityViewShowTags');

		// check locks
		if($this->data->customcolumns->count() > config('lara-admin.builder_custom_columns_max')) {
			$this->data->maxColumnsReached = true;
			$this->data->entityLocked = true;
		} else {
			$this->data->maxColumnsReached = false;
			if($this->data->totalCount == 0 || $this->data->force === true ) {
				$this->data->entityLocked = false;
			} else {
				$this->data->entityLocked = true;
			}
		}

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
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function update(Request $request, int $id)
	{

		$this->data->force = $this->getRequestParam($request, 'force', false);

		// get record
		$object = Entity::findOrFail($id);

		// save object to database
		$object->update($request->all());

		// process options
		$this->builderProcessColumns($request, $object);

		// process settings
		$this->builderProcessObjectRelations($request, $object);

		// process settings
		$this->builderProcessPanels($request, $object);

		// process fields
		$this->builderProcessCustomColumns($request, $object, $this->data->force);

		// process views
		$this->builderProcessViews($request, $object);

		// process related
		$this->builderProcessRelated($request, $object, $this->data->force);

		// check health
		$this->builderCheckEntityHealth($object->entity_key, true);

		// clear Cache
		Artisan::call('cache:clear');

		// refresh Route cache
		$request->session()->put('routecacheclear', true);

		flash('Saved successfully')->success();

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.edit', ['id' => $id]);

	}

	/**
	 * @param Request $request
	 * @param int $id
	 * @return Application|Factory|View
	 */
	public function reorder(Request $request, int $id)
	{

		$this->data->force = $this->getRequestParam($request, 'force');

		// get record
		$this->data->object = Entity::findOrFail($id);

		// get fields
		if($this->isformbuilder) {
			$this->data->customcolumns = $this->data->object->customcolumns
				->sortBy([['fieldhook', 'asc'],['position', 'asc']]);
		} else {
			$this->data->customcolumns = $this->data->object->customcolumns;
		}
		$this->data->fieldList = $this->data->customcolumns->pluck('fieldtitle', 'fieldname')->toArray();

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
	 * Remove the specified resource from storage.
	 *
	 * @param Request $request
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function destroy(Request $request, int $id)
	{

		// get entity object
		$object = Entity::findOrFail($id);

		// destroy object, and all relations
		$this->builderDestroyEntity($request, $object);

		// refresh Route cache
		$request->session()->put('routecacheclear', true);

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.index');

	}

	/**
	 * Perform batch operation
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function batch(Request $request)
	{

		if ($request->submitValue == 'saveall') {

			$objectIDs = $request->input('objcts');

			$objectCount = sizeof($objectIDs);

			foreach ($objectIDs as $objectID) {
				$entity = Entity::find($objectID);
				if ($entity) {
					$this->builderCheckEntityHealth($entity->entity_key, true);
				}
			}

			// flash message
			flash($objectCount . ' entities checked successfully')->success();

		}

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.index');

	}

	/**
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function unlock(int $id)
	{

		// get record
		$object = Entity::findOrFail($id);

		// unlock record
		$object->unlockRecord();

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.index',
			['force' => 'false']);

	}

	/**
	 * @return RedirectResponse
	 */
	public function export()
	{

		$this->builderExportTablesToIseed();

		flash('Database exported successfully')->success();

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.index');

	}

}
