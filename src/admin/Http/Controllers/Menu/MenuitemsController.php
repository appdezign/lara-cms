<?php

namespace Lara\Admin\Http\Controllers\Menu;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Lara\Common\Models\EntityView;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Page;

use App\Http\Controllers\Controller;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Lara\Admin\Http\Traits\AdminMenuTrait;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

use Log;

class MenuitemsController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
	use AdminViewTrait;

	use AdminMenuTrait;

	/**
	 * @var string
	 */
	protected $modelClass = Menuitem::class;

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

		// get content language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get menu group
		$mainMenuID = $this->getMainMenuId();
		$this->data->menu_id = $this->getRequestParam($request, 'menu', $mainMenuID, $this->entity->getEntityKey());

		// get menu types
		$this->data->menutypes = config('lara-front.front_menu.front_menu_types');

		// get all menu groups (for dropdown)
		$this->data->menus = Menu::pluck('title', 'id')->toArray();

		// get menu tree
		$this->data->tree = $this->getMenuTree($this->clanguage, $this->data->menu_id);

		// get all menu items that can have children
		$this->data->parents = $this->getMenuParents($this->clanguage, $this->data->menu_id);

		// get pages
		$this->data->pages = Page::langIs($this->clanguage)->groupIs('page')->pluck('title', 'id')->toArray();
		$this->data->pages = array_add($this->data->pages, 'new', '[ - ' . _lanq('lara-admin::' . $this->entity->getEntityKey() . '.message.create_new_page') . ' - ]');

		// get entities
		$this->data->entviews = EntityView::isPublished()
			->where('type', '!=', '_form')
			->where('method', '!=', 'show')
			->pluck('title', 'id')
			->toArray();

		// get forms
		$this->data->entformviews = EntityView::isPublished()
			->where('type', '_form')
			->pluck('title', 'id')
			->toArray();

		// get tags
		$this->data->tags = [];

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
	 * @return void
	 */
	public function create()
	{

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

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

		$new_id = $this->storeMenuItem($this->entity, $request);

		if ($new_id) {
			$this->rebuildMenuRoutes($new_id);
		}

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

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
	 * @return void
	 */
	public function edit(int $id)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		dd($id);

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @return void
	 */
	public function update(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		dd($request);

	}

	/**
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function batch(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		$menuItemId = $request->input('menu_item_id');

		$this->updateMenuItem($this->entity, $request, $menuItemId);

		$this->rebuildMenuRoutes($menuItemId, true);

		flash('Saved successfully')->success();

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function reorder(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get content language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get menu
		$this->data->menu = $this->getRequestParam($request, 'menu', 1, $this->entity->getEntityKey());

		$menuobject = Menu::find($this->data->menu);
		if ($menuobject) {
			$this->data->menutitle = $menuobject->title;
		} else {
			$this->data->menutitle = null;
		}

		// get Menu Tree
		$this->data->tree = $this->getMenuTree($this->clanguage, $this->data->menu);

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * Save the new order, using the Nested Set pattern.
	 *
	 * @param Request $request
	 * @return false|JsonResponse
	 */
	public function saveReorder(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		$newtree = $request->get('tree');

		$result = $this->checkMaxChildren($newtree, config('lara-admin.menu.max_children'));

		if(!$result->isvalid) {
			$parent = Menuitem::where('id', $result->id)->first();
			return response()->json([
				'status' => false,
				'error' => true,
				'message' => 'Folder has too many children: ' . $parent->title,
			], 401);
		}

		$rootId = $newtree[0]['id'];
		$data = $newtree[0]['children'];
		$root = Menuitem::find($rootId);

		if ($root) {

			// mass assignment (kalnoy/nestedset)
			Menuitem::rebuildSubtree($root, $data);

			// add depth to db
			$scope = ['menu_id', 'language'];
			$this->addDepthToNestedSet($root, $scope);

		} else {
			return false;
		}

		// rebuild routes
		$this->rebuildMenuRoutes($rootId);

		flash('Saved successfully')->success();

		return response()->json(['status' => 'Hooray']);

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
		$node = Menuitem::findOrFail($id);

		// get arrtiubutes, so we can rebuild the menu and sync pages
		$parent_id = $node->parent_id;

		// delete menu item
		$node->delete();

		// rebuild menu routes and sync pages
		$this->rebuildMenuRoutes($parent_id);

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
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function unlock(int $id)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get record
		$object = Menuitem::findOrFail($id);

		// unlock record
		$object->unlockRecord();

		// get last page (pagination) for redirect
		$lastpage = $this->getLastPage($this->entity->getEntityKey());

		// redirect
		if (isset($lastpage) && is_numeric($lastpage) && $lastpage > 1) {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['page' => $lastpage]);
		} else {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
		}

	}

	private function checkMaxChildren($tree, $limit)
	{
		foreach($tree as $root) {

			$max = $this->makeNewObject();

			$max->id = null;
			$max->childcount = 0;

			foreach($root['children'] as $node) {
				$check = $this->checkNode($node, $max);
				if($check->childcount > $max->childcount) {
					$max = $check;
				}
			}
		}

		if($max->childcount > $limit) {
			$max->isvalid = false;
		} else {
			$max->isvalid = true;
		}

		return $max;
	}

	private function checkNode($node, $max) {
		if(array_key_exists('children', $node)) {
			if(sizeof($node['children']) > $max->childcount) {
				$max->id = $node['id'];
				$max->childcount = sizeof($node['children']);
			}
			foreach($node['children'] as $child) {
				$max = $this->checkNode($child, $max);
			}
		}
		return $max;
	}
}
