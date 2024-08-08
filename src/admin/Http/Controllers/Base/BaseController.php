<?php

namespace Lara\Admin\Http\Controllers\Base;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminDbTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminMediaTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminRelatedTrait;
use Lara\Admin\Http\Traits\AdminTagTrait;
use Lara\Admin\Http\Traits\AdminThemeTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Lara\Admin\Http\Traits\AdminBuilderTrait;
use Lara\Admin\Http\Traits\AdminSyncTrait;

use Illuminate\Http\Request;
use Lara\Admin\Http\Requests\UpdateObjectRequest;
use Lara\Admin\Http\Requests\StoreObjectRequest;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Lara\Common\Models\User;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use ReflectionClass;

use Bouncer;
use ReflectionException;

use Carbon\Carbon;

use Lara\Common\Models\Entitygroup;
use Lara\Common\Models\Entity;
use Illuminate\Support\Facades\File;

class BaseController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminDbTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminMediaTrait;
	use AdminObjectTrait;
	use AdminRelatedTrait;
	use AdminTagTrait;
	use AdminThemeTrait;
	use AdminViewTrait;

	use AdminBuilderTrait;
	use AdminSyncTrait;

	/**
	 * @var string
	 */
	protected $modelClass;

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

		// get model class from child controller
		$this->modelClass = $this->determineModelClass();

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

		// unlock abandoned objects
		$this->unlockAbandonedObjects($this->modelClass);

		// check orphaned files on disk
		$this->syncFilesArchive($this->entity);
		$this->syncVideoFilesArchive($this->entity);

		// check image positions
		$this->checkEntityImagePosition($this->entity);

		// get filters
		$this->data->filters = $this->getIndexFilters($this->entity, $request);

		// get params
		$this->data->params = $this->getIndexParams($this->entity, $request, $this->data->filters);

		// get objects
		$this->data->objects = $this->getEntityObjects($this->entity, $request, $this->data->params, $this->data->filters);

		// get tags for filtering
		$this->data->tags = $this->getAllEntityTags($this->clanguage, $this->entity);

		// get module page
		$this->data->modulepage = $this->getModulePage($this->clanguage, $this->entity, 'index');

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
	 * @return Application|Factory|View
	 */
	public function create(Request $request)
	{

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

		// create empty object
		$this->data->object = new $this->modelClass;

		// get templates for Dynamic Widgets
		if($this->entity->getEntityKey() == 'larawidget') {
			$this->data->widgetTemplates = $this->getBladeTemplates($this->data->object, true);
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
	 * Store a newly created resource in storage.
	 *
	 * @param StoreObjectRequest $request
	 * @return RedirectResponse
	 */
	public function store(StoreObjectRequest $request)
	{

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

		// save model
		$object = $this->modelClass::create($request->all());

		// add language to slug
		$this->updateLanguageSlug($this->entity, $object);

		// clear the cache (depending on entity)
		$this->clearCacheOnSave($this->entity);

		// flash message
		$this->flashMessage($this->entity->getEntityKey(), 'save_success', 'success');

		// redirect to edit page
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $object->id]);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param Request $request
	 * @param int $id
	 * @return Application|Factory|View
	 */
	public function show(Request $request, int $id)
	{

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		// get record
		if ($this->entity->getEgroup() == 'form') {
			// show spam records
			$this->data->object = $this->modelClass::withTrashed()->findOrFail($id);
		} else {
			$this->data->object = $this->modelClass::findOrFail($id);
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

		$this->data->tab = $this->getRequestParam($request, 'tab', null, $this->entity->getEntityKey(), true);

		// get language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get record
		$this->data->object = $this->modelClass::findOrFail($id);

		$this->checkObjectLanguage($this->entity, $id, $this->clanguage, $this->data->object->language);

		// get templates for Dynamic Widgets
		if($this->entity->getEntityKey() == 'larawidget') {
			$this->data->widgetTemplates = $this->getBladeTemplates($this->data->object);
		}

		// check Image Positions
		if ($this->entity->hasImages()) {
			if ($this->checkImagePosition($this->entity, $this->data->object)) {
				// reload model
				$this->data->object = $this->modelClass::findOrFail($id);
			}
		}

		// lock record
		$this->data->object->lockRecord();

		// layout
		if ($this->entity->hasLayout()) {
			$this->data->layoutoptions = $this->getFullLayout();
			$this->data->objectlayout = $this->getObjectLayout($this->data->object);
		}

		// get tags
		if ($this->entity->hasTags()) {
			$this->data->objecttags = $this->data->object->tags()->allRelatedIds()->toArray();
			$this->data->tags = $this->getAllEntityTags($this->clanguage, $this->entity);
		} else {
			$this->data->objecttags = null;
			$this->data->tags = null;
		}

		// get all authors
		$this->data->authors = User::isWeb()->get()->pluck('name', 'id');

		// Check if the author is a (soft) deleted user, or an API user
		// If so, add it to the array
		if (!array_key_exists($this->data->object->user_id, $this->data->authors->toArray())) {
			$thisUser = User::where('id', $this->data->object->user_id)->withTrashed()->first();
			if ($thisUser) {
				if ($thisUser->type = 'api') {
					$this->data->authors[$thisUser->id] = $thisUser->name . ' (API)';
				} else {
					$this->data->authors[$thisUser->id] = $thisUser->name . ' (deleted)';

				}
			}
		}

		// get related objects from other models
		$this->data->related = $this->getRelated($this->entity->getEntityKey(), $id);

		// get relatable objects from other models
		$this->data->relatables = $this->getRelatable($request, $this->entity, $id);
		// $this->data->relatables = [];

		// get status
		$this->data->status = $this->getObjectStatus($this->entity, $this->data->object);

		// redirect between pages and widgets
		if ($this->entity->getEntityKey() == 'larawidget') {
			$this->data->onpages = $this->data->object->onpages()->allRelatedIds()->toArray();
			$this->data->returnpage = $this->getWidgetReturnPage($request, $this->entity->getEntityKey(), $id);
		}
		if ($this->entity->getEntityKey() == 'page' && $request->has('fromwidget')) {
			session()->forget('larawidget');
		}

		// get related Module for ModulePages
		if ($this->entity->getEntityKey() == 'page' && $this->data->object->cgroup == 'module') {
			$this->data->modulePageModule = $this->getModulePageModule($this->data->object);
		}

		// get language versions
		$this->data->langversions = $this->getLanguageVersions($this->entity, $this->data->object);

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
	 * @param UpdateObjectRequest $request
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function update(UpdateObjectRequest $request, int $id)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get record
		$object = $this->modelClass::findOrFail($id);

		// update slug
		if ($request->has('_new_slug') && $request->input('_new_slug') != $object->slug) {
			$object->slug = $request->input('_new_slug');
		}

		// reset slug
		if ($request->has('_slug_reset') && $request->input('_slug_reset') == 1) {
			$object->slug = null;
		}

		// update publish from
		$currentState = $object->publish;
		$currentTimestamp = Carbon::parse($object->publish_from)->format('H:i:s');
		if ($request->input('publish') == 1 && $currentState == 0 && $currentTimestamp == '00:00:00') {
			$request->merge(['publish_from' => Carbon::now()]);
		}

		// save object to database
		$object->update($request->all());

		// add language to slug
		$this->updateLanguageSlug($this->entity, $object);

		// save tags
		$this->saveObjectTags($request, $this->entity, $object);

		// save relatables
		$this->saveRelated($request, $this->entity, $id);

		// save layout
		$this->saveLayout($request, $this->entity, $object);

		// save images
		$this->saveMedia($request, $this->entity, $object);

		// save videos
		$this->saveVideo($request, $this->entity, $object);

		// save videofiles
		$this->saveVideoFile($request, $this->entity, $object);

		// save files
		$this->saveFile($request, $this->entity, $object);

		// save widgets
		$this->saveLaraWidgets($request, $this->entity, $object);

		// save sync
		$this->saveSync($request, $this->entity, $object);

		// save seo
		$this->saveSeo($request, $this->entity, $object);

		// save opengraph
		$this->saveOpengraph($request, $this->entity, $object);

		// save geo location
		$this->saveGeo($this->entity, $object);

		// save onpages
		if ($this->entity->getEntityKey() == 'larawidget') {
			$object->onpages()->sync($request->input('_pages_array'));
		}

		// clear the cache if a block is updated
		$this->clearCacheOnSave($this->entity);

		if ($request->has('_widget_id')) {
			$widget_id = $request->input('_widget_id');
			if (is_numeric($widget_id)) {
				return redirect()->route($this->entity->getPrefix() . '.larawidget.edit', ['id' => $widget_id, 'returnpage' => $id, 'returnwidget' => $widget_id]);
			}
		}

		// flash message
		$this->flashMessage($this->entity->getEntityKey(), 'save_success', 'success');

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $id]);

	}

	/**
	 * Reorder the specified resource
	 *
	 * @param Request $request
	 * @return Application|Factory|RedirectResponse|View
	 */
	public function reorder(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		if ($this->entity->isSortable()) {

			// get language
			$this->clanguage = $this->getContentLanguage($request, $this->entity);

			// get tags
			// kalnoy/nestedset
			$this->data->tags = $this->getAllEntityTags($this->clanguage, $this->entity);

			// get filters
			$this->data->filters = $this->getIndexFilters($this->entity, $request);

			// get params
			$this->data->params = $this->getIndexParams($this->entity, $request, $this->data->filters);

			// get all objects
			$this->data->objects = $this->getSortedObjects($this->clanguage, $this->entity, $request, $this->data->tags);

			// get view file and partials
			$this->data->partials = $this->getPartials($this->entity);
			$viewfile = $this->getViewFile($this->entity);

			// pass all variables to the view
			return view($viewfile, [
				'data' => $this->data,
			]);

		} else {

			// model is not sortable, redirect
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

		}

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
		if ($this->entity->getEgroup() == 'form') {
			$object = $this->modelClass::withTrashed()->findOrFail($id);
		} else {
			$object = $this->modelClass::findOrFail($id);
		}

		if ($this->isObjectInMenu($this->entity->id, $object->id)) {

			flash(_lanq('lara-admin::menu.message.object_still_in_menu'))->error();

		} else {

			// delete object and all related items
			$force = ($this->entity->getEgroup() == 'form' && $object->deleted_at) ? true : false;
			$this->deleteEntityObject($this->entity, $object, $force);

			// flash message
			$this->flashMessage($this->entity->getEntityKey(), 'delete_success', 'succes', 1);

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
	 * Perform batch operation
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function batch(Request $request)
	{

		if ($request->submitValue == 'batchdelete') {

			// check if user has access to method
			$this->authorizer('delete', $this->modelClass);

			// delete selected objects
			$objectCount = $this->batchDeleteObjects($this->entity, $request->input('objcts'));

			// flash message
			$this->flashMessage($this->entity->getEntityKey(), 'delete_success', 'success', $objectCount);

		}

		if ($request->submitValue == 'batchpublish') {

			// check if user has access to method
			$this->authorizer('update', $this->modelClass);

			// publish selected objects
			$objectCount = $this->batchPublishObjects($this->entity, $request->input('objcts'));

			// flash message
			$this->flashMessage($this->entity->getEntityKey(), 'publish_success', 'success', $objectCount);

		}

		if ($request->submitValue == 'batchunpublish') {

			// check if user has access to method
			$this->authorizer('update', $this->modelClass);

			// publish selected objects
			$objectCount = $this->batchUnPublishObjects($this->entity, $request->input('objcts'));

			// flash message
			$this->flashMessage($this->entity->getEntityKey(), 'unpublish_success', 'success', $objectCount);

		}

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
		if ($this->entity->getEgroup() == 'form') {
			$object = $this->modelClass::withTrashed()->findOrFail($id);
		} else {
			$object = $this->modelClass::findOrFail($id);
		}

		// unlock record
		$object->unlockRecord();

		// redirect Module Page back to associated Entity
		$this->redirectModulePageToEntity($object, $this->entity);

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
	 * Determine the model class name of the child controller
	 *
	 * @return string
	 * @throws ReflectionException
	 */
	protected function determineModelClass(): string
	{
		return (new ReflectionClass($this))
			->getMethod('make')
			->getReturnType()
			->getName();
	}

}
