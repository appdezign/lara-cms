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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use Lara\Common\Models\User;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

class ProfileController extends Controller
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
	protected $modelClass = User::class;

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
	 * Show the form for editing the specified resource.
	 *
	 * @return Application|Factory|View
	 */
	public function edit()
	{

		$id = Auth::user()->id;

		// get record
		$this->data->object = User::findOrFail($id);

		// lock record
		$this->data->object->lockRecord();

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
	 * @return RedirectResponse
	 */
	public function update(Request $request)
	{

		$id = Auth::user()->id;

		$object = $this->modelClass::findOrFail($id);

		if ($request->input('_password') != '') {
			$object->password = $request->input('_password');
		}

		// save object
		$object->update($request->all());

		// update profile
		$this->saveUserProfile($request, $object);

		flash(ucfirst($this->entity->getEntityKey()) . ' saved successfully')->success();

		return redirect()->route('admin.dashboard.index');

	}

	/**
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function unlock(int $id)
	{

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
