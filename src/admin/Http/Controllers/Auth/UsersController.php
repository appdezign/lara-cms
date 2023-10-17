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
use Illuminate\Support\Facades\Route;

use Lara\Common\Models\User;
use Silber\Bouncer\Database\Role;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

class UsersController extends Controller
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
	protected $modelClass = \Lara\Common\Models\User::class;

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

			$this->routename = Route::current()->getName();
			$this->entity = $this->getLaraEntity($this->routename, $this->modelClass);

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

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		$this->data->showarchive = $this->getRequestParam($request, 'archive', 'false', $this->entity->getEntityKey());

		// get user/group level of loggedin user
		$mylevel = $this->getUserLevel(Auth::user());

		// get filters
		$this->data->filters = $this->getIndexFilters($this->entity, $request);

		// get params
		$this->data->params = $this->getIndexParams($this->entity, $request, $this->data->filters);

		if ($this->data->filters->search) {

			// get objects
			$keywords = $this->data->filters->keywords;

			$collection = $this->modelClass::isWeb()
				->where(function ($q) use ($keywords) {
					foreach ($keywords as $value) {
						$q->orWhere('name', 'like', "%{$value}%");
					}
				});

			if($this->data->showarchive) {
				$collection = $collection->onlyTrashed();
			} else {
				$collection = $collection->with('roles');
			}

			$collection = $collection->orderBy('name', 'asc');


			$this->data->objects = $collection->get();

		} else {

			// get all objects
			$collection = $this->modelClass::isWeb();

			if($this->data->showarchive) {
				$collection = $collection->onlyTrashed();
			} else {
				$collection = $collection->with('roles');
			}

			$collection = $collection->orderBy('name', 'asc');

			if ($this->data->params->paginate) {
				$this->data->objects = $collection->paginate($this->data->params->perpage);
				$this->checkPagination($this->data->objects->count(), $this->entity);
			} else {
				$this->data->objects = $collection->get();
			}

		}

		foreach ($this->data->objects as $userobject) {
			$userobject->userlevel = $this->getUserLevel($userobject);
		}

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data'    => $this->data,
			'mylevel' => $mylevel,
		]);

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Application|Factory|View
	 */
	public function create()
	{

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

		// get user/group level of loggedin user
		$mylevel = $this->getUserLevel(Auth::user());

		$this->data->object = new $this->modelClass;

		// get all roles
		$this->data->roles = Role::where('level', '<=', $mylevel)->orderBy('level', 'desc')->get();

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data'    => $this->data,
			'mylevel' => $mylevel,
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

		$validatedData = $request->validate([
			'username' => 'required|unique:lara_auth_users,username',
			'email' => 'required|unique:lara_auth_users,email',
		]);

		// check if the new username already exists
		$newUsername = $request->input('username');

		$check = User::where('username', $newUsername)->first();

		if (!$check) {

			$request['type'] = 'web';

			// create object
			$object = $this->modelClass::create($request->except('role_name'));

			// save role
			$roles = array();
			$roles[] = $request['role_name'];
			foreach ($roles as $role) {
				$object->assign($role);
			}

			Bouncer::refresh();

			flash(ucfirst($this->entity->getEntityKey()) . ' created successfully')->success();

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $object->id]);

		} else {

			flash(_lanq('lara-admin::user.message.user_already_exists'))->error();

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
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

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

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

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get user/group level of loggedin user
		$mylevel = $this->getUserLevel(Auth::user());

		// get record
		$this->data->object = User::findOrFail($id);

		// lock record
		$this->data->object->lockRecord();

		// get user role
		$this->data->objectrole = $this->data->object->roles()->value('name');

		// get all roles
		$this->data->roles = Role::where('level', '<=', $mylevel)->orderBy('level', 'desc')->get();

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data'    => $this->data,
			'mylevel' => $mylevel,
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

		$validatedData = $request->validate([
			'email' => 'required|unique:lara_auth_users,email,' . $id,
		]);

		$object = $this->modelClass::findOrFail($id);

		if ($request->input('_password') != '') {
			$object->password = $request->input('_password');
		}

		// save object
		$object->update($request->all());

		// update profile
		$this->saveUserProfile($request, $object);


		/**
		 * Save role.
		 * We have a many-to-many relationship (user to role),
		 * but for now we allow only one role per user.
		 * The form uses radio buttons instead of checkboxes.*
		 */

		$newRoles = (array)$request['_role_name'];
		foreach ($object->roles as $role) {
			$object->retract($role);
		}
		foreach ($newRoles as $newRole) {
			$object->assign($newRole);
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

		// check if user has access to method
		$this->authorizer('delete', $this->modelClass);

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
