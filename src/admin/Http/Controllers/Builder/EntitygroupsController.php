<?php

namespace Lara\Admin\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Lara\Admin\Http\Traits\LaraAdminHelpers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Lara\Common\Models\Entitygroup;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

class EntitygroupsController extends Controller
{

	use LaraAdminHelpers;

	/**
	 * @var string
	 */
	protected $modelClass = Entitygroup::class;

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
	protected $ismobile;

	public function __construct()
	{

		$this->isbuilder = true;

		// create an empty Laravel object to hold all the data
		$this->data = $this->makeNewObject();

		// restrict the Builder to administrators
		$this->middleware(function ($request, $next) {
			if (!Auth::user()->isAn('administrator')) {
				return response()->view('lara-admin::errors.405', [], 405);
			}

			return $next($request);
		});

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
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function index(Request $request)
	{

		// get filters
		$this->data->filters = $this->getIndexFilters($this->entity, $request);

		// get params
		$this->data->params = $this->getIndexParams($this->entity, $request, $this->data->filters);

		// get objects
		$this->data->objects = $this->getObjects($this->entity, $request, $this->data->params, $this->data->filters);

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

		$this->data->object = new Entitygroup;

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

		// save model
		$object = Entitygroup::create($request->all());

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.edit',
			['id' => $object->id]);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return Application|Factory|View
	 */
	public function show(int $id)
	{

		// get record
		$this->data->object = Entitygroup::findOrFail($id);

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

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

		// get record
		$this->data->object = Entitygroup::findOrFail($id);

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
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function update(Request $request, int $id)
	{

		// get record
		$object = Entitygroup::findOrFail($id);

		// reset slug
		if ($request->has('_slug_reset') && $request->input('_slug_reset') == 1) {
			$object->key = null;
		}

		// save object to database
		$object->update($request->all());

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.edit', ['id' => $id]);

	}

	/**
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function reorder(Request $request)
	{

		// model is not sortable, redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.index');

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function destroy(int $id)
	{

		// get record
		$object = Entitygroup::findOrFail($id);

		$object->delete();

		flash(ucfirst($this->entity->entity_key) . ' deleted successfully')->success();

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
		$object = Entitygroup::findOrFail($id);

		// unlock record
		$object->unlockRecord();

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->entity_key . '.index');

	}

}

