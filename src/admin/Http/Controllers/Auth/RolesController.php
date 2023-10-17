<?php

namespace Lara\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Silber\Bouncer\Database\Ability;
use Lara\Common\Models\Entity;

use Session;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Carbon\Carbon;

use Bouncer;

class RolesController extends Controller
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
	protected $modelClass = \Silber\Bouncer\Database\Role::class;

	/**
	 * @var string
	 */
	protected $laraModelClass = \Lara\Common\Models\Userrole::class;

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

		$this->purgeAbilities();

		// get filters
		$this->data->filters = $this->getIndexFilters($this->entity, $request);

		// get params
		$this->data->params = $this->getIndexParams($this->entity, $request, $this->data->filters);

		// get all objects
		$collection = $this->modelClass::with('users')->orderBy('level', 'desc');
		if ($this->data->params->paginate) {
			$this->data->objects = $collection->paginate($this->data->params->perpage);
			$this->checkPagination($this->data->objects->count(), $this->entity);
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
	 * @return Application|Factory|View
	 */
	public function create()
	{

		// check if user has administrator role
		$this->checkAdminAccess();

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

		// check if user has administrator role
		$this->checkAdminAccess();

		// save model
		$object = $this->modelClass::create($request->all());

		flash(ucfirst($this->entity->getEntityKey()) . ' created successfully')->success();

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $object->id]);

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

		// get all abilities
		$this->data->abilities = Ability::orderBy('entity_type', 'asc')->orderBy('name', 'asc')->get();

		// get entities by group
		$this->data->entities = array();
		$ents = Entity::with('egroup')
			->orderBy('group_id')
			->orderBy('menu_position')
			->get();
		foreach ($ents as $ent) {
			if(!in_array($ent->entity_key, ['role', 'ability'])) {
				$this->data->entities[$ent->egroup->key][] = $ent->entity_key;
			}
		}

		// get current abilities
		$this->data->entkeys = Ability::distinct('entity_key')->pluck('entity_key');
		$this->data->objectabilities = array();
		foreach ($this->data->entkeys as $entkey) {
			$i = 0;
			foreach ($this->data->object->abilities as $ability) {
				if ($entkey == $ability->entity_key) {
					$this->data->objectabilities[] = $entkey . '_' . $ability->name;
					$i++;
				}
			}
			if ($i == 4) {
				$this->data->objectabilities[] = $entkey . '_all';
			}
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

		$userrole = $this->laraModelClass::findOrFail($id);

		$request->merge(['level' => intval($request->input('level'))]);

		// save model
		$userrole->update($request->except(['abilities']));

		// save abilities
		$object = $this->modelClass::findOrFail($id);

		$objectabilities = $object->getAbilities();
		if (count($objectabilities)) {
			foreach ($objectabilities as $objectAbility) {
				$object->disallow($objectAbility->name, $objectAbility->entity_type);
			}
		}
		$newAbilities = $request['abilities'];

		$entities = Entity::pluck('entity_model_class', 'entity_key')->toArray();

		if ($newAbilities) {
			foreach ($newAbilities as $newAbility) {
				list($newMod, $newAb) = explode('_', $newAbility);

				// fix model name
				if ($newMod == 'role') {
					$fullmodel = 'Userrole';
				} elseif ($newMod == 'ability') {
					$fullmodel = 'Userability';
				} else {
					// get model from Entity
					$fullmodel = $entities[$newMod];

				}

				$object->allow($newAb, $fullmodel);
			}
		}

		Bouncer::refresh();

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

		if ($object->users->count() == 0) {
			$object->delete();
			flash(ucfirst($this->entity->getEntityKey()) . ' deleted successfully')->success();
		} else {
			flash('Error: this role still has users')->error();
		}

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

	/**
	 * @return bool
	 */
	private function purgeAbilities()
	{

		$entities = Entity::whereNotIn('entity_key', ['role', 'ability'])->pluck('entity_key')->toArray();

		$abilities = Ability::distinct('entity_key')->whereNotNull('entity_key')->pluck('entity_key');

		foreach ($abilities as $ability) {
			if (!in_array($ability, $entities)) {
				DB::table(config('lara-common.database.auth.abilities'))->where('entity_key', $ability)->delete();
			}
		}

		return true;

	}

}
