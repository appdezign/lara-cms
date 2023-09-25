<?php

namespace Lara\Admin\Http\Controllers\Menu;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Lara\Admin\Http\Traits\LaraAdminHelpers;
use Lara\Admin\Http\Traits\LaraMenu;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Lara\Common\Models\Page;
use Lara\Common\Models\Seo;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

class SeosController extends Controller
{

	use LaraAdminHelpers;
	use LaraMenu;

	/**
	 * @var string
	 */
	protected $modelClass = Seo::class;

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
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function index(Request $request)
	{

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		$this->data->force = $this->getRequestParam($request, 'force', false, $this->entity->getEntityKey());

		$mainMenuID = $this->getMainMenuId();

		$tree = $this->getMenuTree($this->clanguage, $mainMenuID);

		if ($tree) {
			foreach ($tree as $node) {
				$this->getMenuObject($node, $this->clanguage);
			}
		}

		$this->data->tree = $tree;

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param Request $request
	 * @return void
	 */
	public function create(Request $request)
	{

		dd($request);

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return void
	 */
	public function store(Request $request)
	{

		dd($request);

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
	 * @return Application|Factory|View
	 */
	public function edit(Request $request, int $id)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get record
		$this->data->object = Page::with('seo')->findOrFail($id);

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

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get record
		$object = Page::findOrFail($id);

		// save object to database
		$object->seo()->update($request->only('seo_title', 'seo_description', 'seo_keywords'));

		// flash message
		$this->flashMessage($this->entity->getEntityKey(), 'save_success', 'success');

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $id]);

	}

	/**
	 * Perform batch operation
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function batch(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		$language = $request->input('language');

		$objects = Page::langIs($language)->get();

		foreach ($objects as $object) {

			if ($object->seo) {

				$seo = $object->seo;
				$seo->seo_title = $object->title;
				$seo->seo_description = '';
				$seo->seo_keywords = '';

				$seo->save();

			} else {

				$object->seo()->create([
					'seo_title'       => $object->title,
					'seo_description' => '',
					'seo_keywords'    => '',
				]);

			}

		}

		// flash message
		flash('All SEO data has been reset')->success();

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

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

		// get last page (pagination) for redirect
		$lastpage = $this->getLastPage($this->entity->getEntityKey());

		// redirect
		if ($lastpage && is_numeric($lastpage) && $lastpage > 1) {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['page' => $lastpage]);
		} else {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
		}

	}

	/**
	 * @param object $node
	 * @param string $language
	 * @return bool
	 */
	public function getMenuObject(object $node, string $language)
	{

		if ($node->type == 'page') {

			// get page object for this menu item
			$object = Page::with('seo')->find($node->object_id);

			if (!empty($object)) {
				$node->object = $object;
			}

		} elseif ($node->type == 'entity') {

			// get module page for this menu item
			$object = Page::langIs($language)->groupIs('module')->where('slug', $node->entity->entity_key . '-index-module-' . $language)->first();
			if (!empty($object)) {
				$node->object = $object;
			}

		} elseif ($node->type == 'form') {

			// get module page object for this menu item
			$object = Page::langIs($language)->groupIs('module')->where('slug', $node->entity->entity_key . '-form-module-' . $language)->first();
			if (!empty($object)) {
				$node->object = $object;
			}

		}

		if (!$node->isLeaf()) {
			foreach ($node->children as $child) {
				$this->getMenuObject($child, $language);
			}
		}

		return true;
	}

}
