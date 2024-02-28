<?php

namespace Lara\Admin\Http\Controllers\Tag;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Lara\Common\Models\EntityView;
use Lara\Common\Models\Tag;

use App\Http\Controllers\Controller;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminMediaTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminTagTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Illuminate\Http\Request;
use Lara\Admin\Http\Requests\UpdateObjectRequest;
use Lara\Admin\Http\Requests\StoreObjectRequest;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Lara\Common\Models\Taxonomy;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

use Config;

class TagsController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminMediaTrait;
	use AdminObjectTrait;
	use AdminTagTrait;
	use AdminViewTrait;

	/**
	 * @var string
	 */
	protected $modelClass = Tag::class;

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
	 * @return Application|Factory|Response|View
	 */
	public function index(Request $request)
	{

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		// get content language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get related Entity
		$entityKey = $this->getRequestParam($request, 'entity', 'page', $this->entity->getEntityKey());

		$laraKey = $this->getEntityVarByKey($entityKey);
		$this->data->related = new $laraKey;

		// get active taxonomy
		$defaultTaxonomy = $this->getDefaultTaxonomy();
		$taxonomySlug = $this->getRequestParam($request, 'taxonomy', $defaultTaxonomy->slug, $this->entity->getEntityKey());
		$this->data->taxonomy = Taxonomy::where('slug', $taxonomySlug)->first();

		// get all taxonomies
		$this->data->taxonomies = Taxonomy::get();

		if ($this->data->related) {
			$this->data->tree = $this->getEntityTags($this->clanguage, $this->data->related, $this->data->taxonomy->slug);
		} else {
			return response()->view('lara-admin::errors.404', [], 405);
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

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

		// get record
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
	 * @param StoreObjectRequest $request
	 * @return RedirectResponse
	 */
	public function store(StoreObjectRequest $request)
	{

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

		$entityKey = $request->get('entity_key');

		$parentId = $request->get('parent_id');
		$parent = Tag::find($parentId);

		if ($parent) {

			$object = new Tag;

			$object->language = $request->get('language');
			$object->entity_key = $request->get('entity_key');
			$object->taxonomy_id = $request->get('taxonomy_id');
			$object->title = $request->get('title');
			$object->lead = '';
			$object->body = '';
			$object->publish = 1;

			// kalnoy/nestedset
			$object->depth = $parent->depth + 1;
			$object->appendToNode($parent)->save();

			// add language to slug
			$this->updateLanguageSlug($this->entity, $object);

			// rebuild the tag routes
			$this->rebuildTagRoutes($object->id);

		}

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['entity' => $entityKey]);

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
	 * @param Request $request
	 * @param int $id
	 * @return Application|Factory|RedirectResponse|View
	 */
	public function edit(Request $request, int $id)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get record
		$this->data->object = $this->modelClass::findOrFail($id);

		// check admin lock
		if ($this->data->object->locked_by_admin == 1 && !Auth::user()->isAn('administrator')) {
			$entityKey = $request->get('entity');

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['entity' => $entityKey]);
		}

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

		// check admin lock
		if ($object->locked_by_admin == 1 && !Auth::user()->isAn('administrator')) {
			$entityKey = $request->get('entity');

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['entity' => $entityKey]);
		}

		// update slug
		if ($request->input('_new_slug') != $object->slug) {
			$object->slug = $request->input('_new_slug');
		}

		// reset slug
		if ($request->input('_slug_reset') == 1) {
			$object->slug = null;
		}

		// save object to database
		$object->update($request->all());

		// add language to slug
		$this->updateLanguageSlug($this->entity, $object);

		// save images
		$this->saveMedia($request, $this->entity, $object);

		// save seo
		$this->saveSeo($request, $this->entity, $object);

		// rebuild the tree
		$this->rebuildTagRoutes($id);

		flash('Saved successfully')->success();

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.edit', ['id' => $id]);

	}

	public function taggable(Request $request, int $id)
	{

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		// get content language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get record
		$this->data->object = $this->modelClass::findOrFail($id);

		// get related Entity
		$entityKey = $this->getRequestParam($request, 'entity', 'page', $this->entity->getEntityKey());

		$laraKey = $this->getEntityVarByKey($entityKey);
		$relent = new $laraKey;
		$this->data->related = $relent;

		$modelClass = $relent->getEntityModelClass();

		$sortfield = $relent->getSortField();
		$sortorder = $relent->getSortOrder();

		// get all objects
		$this->data->relobjects = $modelClass::langIs($this->clanguage)->orderBy($sortfield, $sortorder)->get();

		// get related objects
		$term = $this->data->object->slug;
		$this->data->tagobjects = $modelClass::langIs($this->clanguage)
			->whereHas('tags', function ($query) use ($term) {
				$query->where(config('lara-common.database.object.tags') . '.slug', $term);
			})->pluck('id')->toArray();


		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}


	public function savetaggable(Request $request, int $id)
	{

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		// get content language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		// get record
		$object = $this->modelClass::findOrFail($id);

		// save related objects
		if ($request->input('_enable_update')) {

			// get related Entity
			$entityKey = $this->getRequestParam($request, 'entity', 'page', $this->entity->getEntityKey());

			$laraKey = $this->getEntityVarByKey($entityKey);
			$relent = new $laraKey;
			$relatedModelClass = $relent->getEntityModelClass();

			// get all related Objects
			$relobjects = $relatedModelClass::langIs($this->clanguage)->get();
			foreach ($relobjects as $relobject) {

				$objectTags = $relobject->tags;

				$currentTagIds = array();

				foreach ($objectTags as $objectTag) {
					$currentTagIds[] = $objectTag->id;
				}

				// current Tag
				$tagId = $object->id;

				// get status for this tag and for this object
				$newStatus = ($request->input('_related_object_' . $relobject->id));

				// define active tags
				$activeTagIds = $currentTagIds;

				if ($newStatus) {
					// add tag ID from Tag Array
					if (!in_array($tagId, $currentTagIds)) {
						$activeTagIds[] = $tagId;
					}
				} else {
					// remove tag ID from Tag Array
					$key = array_search($tagId, $activeTagIds, true);
					if ($key !== false) {
						array_splice($activeTagIds, $key, 1);
					}
				}

				// sync product tags
				$relobject->tags()->sync($activeTagIds);


			}
		}

		flash('Saved successfully')->success();

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.taggable', ['id' => $id]);

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|Response|View
	 */
	public function reorder(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		// get content language
		$this->clanguage = $this->getContentLanguage($request, $this->entity);

		$entitykey = $this->getRequestParam($request, 'entity', 'page', $this->entity->getEntityKey());

		$laraKey = $this->getEntityVarByKey($entitykey);
		$this->data->relatedEntity = new $laraKey;

		if ($this->data->relatedEntity) {

			// get active taxonomy
			$defaultTaxonomy = $this->getDefaultTaxonomy();
			$taxonomySlug = $this->getRequestParam($request, 'taxonomy', $defaultTaxonomy->slug, $this->entity->getEntityKey());
			$this->data->taxonomy = Taxonomy::where('slug', $taxonomySlug)->first();

			$this->data->tree = $this->getEntityTags($this->clanguage, $this->data->relatedEntity, $this->data->taxonomy->slug);

		} else {

			return response()->view('lara-admin::errors.404', [], 405);

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

		$rootId = $newtree[0]['id'];
		$data = $newtree[0]['children'];
		$root = Tag::find($rootId);

		if ($root) {

			// mass assignment (kalnoy/nestedset)
			Tag::rebuildSubtree($root, $data);

			// add depth to db
			$scope = ['language', 'entity_key', 'taxonomy_id'];
			$this->addDepthToNestedSet($root, $scope);

		} else {
			return false;
		}

		// rebuild routes
		$this->rebuildTagRoutes($rootId);

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
		$object = $this->modelClass::findOrFail($id);

		$object->delete();

		flash(ucfirst($this->entity->getEntityKey()) . ' deleted successfully')->success();

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
		$object = $this->modelClass::findOrFail($id);

		// unlock record
		$object->unlockRecord();

		// get last page (pagination) for redirect
		if (session()->has($this->entity->getEntityKey())) {
			$entitySession = session($this->entity->getEntityKey());
			if (array_key_exists('page', $entitySession)) {
				$lastpage = $entitySession['page'];
			}
		}

		// redirect
		if (isset($lastpage) && is_numeric($lastpage) && $lastpage > 1) {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['page' => $lastpage]);
		} else {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
		}

	}

	/**
	 * @param Request $request
	 * @param int $id
	 * @return JsonResponse
	 */
	public function gettags(Request $request, int $id)
	{

		$tags = array();
		$tags['has_tags'] = false;
		$tags['data'] = null;

		$view = EntityView::find($id);

		if ($view) {

			$entity = $view->entity;

			if ($entity->objectrelations->has_tags) {

				$language = $this->getContentLanguage($request, $this->entity);

				// get default taxonomy
				$taxonomy = $this->getDefaultTaxonomy();

				$root = Tag::langIs($language)
					->entityIs($entity->entity_key)
					->taxonomyIs($taxonomy->id)
					->whereNull('parent_id')
					->first();

				$tags['has_tags'] = true;
				$tags['data'] = $root->getDescendants()->toArray();

			}

		}

		return response()->json($tags);

	}

}
