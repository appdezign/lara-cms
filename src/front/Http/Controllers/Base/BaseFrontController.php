<?php

namespace Lara\Front\Http\Controllers\Base;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use Illuminate\View\View;

use Lara\Front\Http\Traits\FrontTrait;
use Lara\Front\Http\Traits\FrontAuthTrait;
use Lara\Front\Http\Traits\FrontEntityTrait;
use Lara\Front\Http\Traits\FrontListTrait;
use Lara\Front\Http\Traits\FrontMenuTrait;
use Lara\Front\Http\Traits\FrontObjectTrait;
use Lara\Front\Http\Traits\FrontRoutesTrait;
use Lara\Front\Http\Traits\FrontSecurityTrait;
use Lara\Front\Http\Traits\FrontTagTrait;
use Lara\Front\Http\Traits\FrontThemeTrait;
use Lara\Front\Http\Traits\FrontViewTrait;

use Lara\Common\Models\Taxonomy;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use ReflectionClass;
use ReflectionException;

// use Theme;
use Qirolab\Theme\Theme;

class BaseFrontController extends Controller
{

	use FrontTrait;
	use FrontAuthTrait;
	use FrontEntityTrait;
	use FrontListTrait;
	use FrontMenuTrait;
	use FrontObjectTrait;
	use FrontRoutesTrait;
	use FrontSecurityTrait;
	use FrontTagTrait;
	use FrontThemeTrait;
	use FrontViewTrait;

	/**
	 * @var string
	 */
	protected $modelClass;

	/**
	 * @var string|null
	 */
	protected $routename;

	/**
	 * @var object
	 */
	protected $entity;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var object
	 */
	protected $data;

	/**
	 * @var bool
	 */
	protected $ismobile;

	/**
	 * @var object
	 */
	protected $globalwidgets;

	/**
	 * BaseFrontController constructor.
	 *
	 * @throws BindingResolutionException
	 * @throws ReflectionException
	 */
	public function __construct()
	{

		// get model class from child controller
		$this->modelClass = $this->determineModelClass();

		// get language
		$this->language = LaravelLocalization::getCurrentLocale();

		// create an empty Laravel object to hold all the data (see: https://goo.gl/ufmFHe)
		$this->data = $this->makeNewObj();

		if (!App::runningInConsole()) {

			// get route name
			$this->routename = Route::current()->getName();

			// get entity
			$this->entity = $this->getFrontEntity($this->routename);

			// get default seo
			$this->data->seo = $this->getDefaultSeo($this->language);

			// get default layout
			$this->data->layout = $this->getDefaultThemeLayout();

			// get entity routes from menu
			$this->data->eroutes = $this->getMenuEntityRoutes($this->language);

			// get global widgets
			$this->globalwidgets = $this->getGlobalWidgets($this->language);

			// get agent
			$agent = new Agent();
			$this->ismobile = $agent->isMobile();

			// share data with all views, see: https://goo.gl/Aqxquw
			$this->middleware(function ($request, $next) {
				view()->share('entity', $this->entity);
				view()->share('language', $this->language);
				view()->share('ismobile', $this->ismobile);
				view()->share('globalwidgets', $this->globalwidgets);
				view()->share('activemenu', $this->getActiveMenuArray());
				view()->share('firstpageload', $this->getFirstPageLoad());

				return $next($request);
			});

		}

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return Application|Factory|View
	 * @throws BindingResolutionException
	 */
	public function index(Request $request)
	{

		// get menu category
		$this->data->menutag = $this->getMenuTag($this->language, $this->entity, $request);

		// get params
		$this->data->params = $this->getFrontParams($this->entity, $request);

		$this->data->userview = $this->getFrontRequestParam($request, 'view', null, $this->entity->entity_key);

		// get objects
		$this->data->objects = $this->getFrontObjects($this->language, $this->entity, $request, $this->data->menutag, $this->data->params);

		// get object count
		if ($this->data->params->tagsview == 'sort') {
			$objectCount = 0;
			foreach ($this->data->objects as $taxonomy => $terms) {
				foreach ($terms as $node) {
					if (array_key_exists('objects', $node->toArray())) {
						$objectCount = $objectCount + $node->objects->count();
					}
				}
			}
		} else {
			$objectCount = $this->data->objects->count();
		}
		$this->data->objectCount = $objectCount;

		// get tags
		// kalnoy/nestedset
		if ($this->entity->hasTags()) {
			$this->data->tags = $this->makeNewObj();
			$taxonomies = Taxonomy::get();
			foreach ($taxonomies as $taxonomy) {
				$taxonomySlug = $taxonomy->slug;
				$this->data->tags->$taxonomySlug = $this->getTags($this->language, $this->entity, 'tree', $taxonomySlug);
			}
		}

		if ($this->data->params->showtags == 'filterbytaxonomy') {

			// overrule layout for tag menu in left sidebar
			$this->data->layout->content = 'boxed_sidebar_left_3';

			// get current tag
			$this->data->tag = $this->getTagBySlug($this->language, $this->entity, $this->data->params->filterbytaxonomy);

			// get current tag children
			$this->data->children = $this->getTagChildren($this->language, $this->entity, $this->data->params->filterbytaxonomy);

		} else {

			$this->data->tag = null;
			$this->data->children = null;

		}

		// get related module page for Layout, SEO, Intro, etc.
		$this->data->modulepage = $this->getModulePageBySlug($this->language, $this->entity, 'index');

		// Use menu tag or module page for Intro
		if ($this->data->menutag) {
			$this->data->page = $this->data->menutag;
		} else {
			$this->data->page = $this->data->modulepage;
		}

		// seo
		$this->data->seo = $this->getSeo($this->data->modulepage);

		// opengraph
		$this->data->opengraph = $this->getOpengraph($this->data->modulepage);

		// get language versions
		$this->data->langversions = $this->getFrontLanguageVersions($this->language, $this->entity);

		// override default layout with custom module page layout
		$this->data->layout = $this->getObjectThemeLayout($this->data->modulepage, $this->data->params);
		$this->data->grid = $this->getGrid($this->data->layout);

		// template vars & override
		$this->data->gridvars = $this->getGridVars($this->entity);
		$this->data->override = $this->getGridOverride($this->entity);

		$viewfile = $this->getFrontViewFile($this->entity);

		// infinite scroll
		if ($this->data->params->infinite) {
			$viewfile = 'content.' . $this->entity->getEntityKey() . '.infinite';
			if ($request->ajax()) {
				$view = view('content.' . $this->entity->getEntityKey() . '.infinite.loop_infinite', ['data' => $this->data])->render();

				return response()->json(['html' => $view]);
			}
		}

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param Request $request
	 * @param string|int|null $slug
	 * @return Application|Factory|RedirectResponse|View
	 * @throws BindingResolutionException
	 */
	public function show(Request $request, $slug = null)
	{

		// get menutaxonomy (for previous and next model)
		$this->data->menutag = $this->getSingleMenuTag($this->language, $this->entity, $request);

		// get params
		$this->data->params = $this->getFrontParams($this->entity, $request);

		$this->data->tag = $this->getTagBySlug($this->language, $this->entity, $this->data->params->filterbytaxonomy);

		$eager = ['user'];
		if ($this->entity->hasImages()) {
			$eager[] = 'media';
		}
		if ($this->entity->hasFiles()) {
			$eager[] = 'files';
		}

		if (is_numeric($slug)) {

			$id = $slug;
			$this->data->object = $this->modelClass::find($id);

		} else {

			$this->data->object = $this->modelClass::langIs($this->language)
				->with($eager)
				->where('slug', $slug)
				->first();

		}

		if (empty($this->data->object)) {

			// object not found, redirect to list
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityKey() . '.index');

		} else {

			// redirect entity objects to their menu url, if possible
			$isPreview = $this->checkEntityRoute($this->language, $this->entity, $this->data->object);

			// if the page is not a preview, make sure it is published
			if (!$isPreview && ($this->entity->hasStatus() && $this->data->object->publish == 0)) {
				return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityKey() . '.index');
			}

		}

		// replace shortcodes
		if ($this->entity->hasLead()) {
			$this->data->object->lead = $this->replaceShortcodes($this->data->object->lead);
		}
		if ($this->entity->hasBody()) {
			$this->data->object->body = $this->replaceShortcodes($this->data->object->body);
		}

		// related objects (from other entities)
		if ($this->entity->hasRelated()) {
			$this->data->relatedObjects = $this->getFrontRelated($this->entity->getEntityKey(), $this->data->object->id);
		}

		// get related module page (from parent index method) for Layout and SEO
		$this->data->modulepage = $this->getModulePageBySlug($this->language, $this->entity, 'index');

		// Use object for Hero if it has a hero image
		// Otherwise use menu tag or module page for Hero
		if ($this->data->object->hasHero()) {
			$this->data->page = $this->data->object;
		} else {
			if ($this->data->menutag) {
				$this->data->page = $this->data->menutag;
			} else {
				$this->data->page = $this->data->modulepage;
			}
		}

		// get tags
		// kalnoy/nestedset
		if ($this->entity->hasTags()) {
			$this->data->tags = $this->makeNewObj();
			$taxonomies = Taxonomy::get();
			foreach ($taxonomies as $taxonomy) {
				$taxonomySlug = $taxonomy->slug;
				$this->data->tags->$taxonomySlug = $this->getTags($this->language, $this->entity, 'tree', $taxonomySlug);
			}
		}

		// seo
		$this->data->seo = $this->getSeo($this->data->object, $this->data->modulepage);

		// opengraph
		$this->data->opengraph = $this->getOpengraph($this->data->object);

		// get language versions
		$this->data->langversions = $this->getFrontLanguageVersions($this->language, $this->entity, $this->data->object);

		// override default layout with custom module page layout
		$this->data->layout = $this->getObjectThemeLayout($this->data->modulepage);
		$this->data->grid = $this->getGrid($this->data->layout);

		// template vars & override
		$this->data->gridvars = $this->getGridVars($this->entity);
		$this->data->override = $this->getGridOverride($this->entity);

		$this->data->next = $this->getNextObject($this->language, $this->entity, $this->data->object, $this->data->params, $this->data->menutag);
		$this->data->prev = $this->getPrevObject($this->language, $this->entity, $this->data->object, $this->data->params, $this->data->menutag);

		$this->data->entityListUrl = $this->getEntityListUrl($this->language, $this->entity, $this->data->object, $this->data->menutag);

		$viewfile = $this->getFrontViewFile($this->entity);

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * Display the specified resource as a feed.
	 *
	 * @param Request $request
	 * @param string|null $type
	 * @return Application|ResponseFactory|Response
	 * @throws BindingResolutionException
	 */
	public function feed(Request $request, $type = null)
	{

		$format = ($type) ? $type : 'rss';

		// get objects
		$this->data->objects = $this->getFeedObjects($this->language, $this->entity, $request, config('lara.rss_feed_limit'));

		$company = $this->getSettingsByGroup('company');

		$meta = $this->makeNewObj();

		$meta->title = $company->company_name . ' - ' . _lanq('lara-' . $this->entity->getModule() . '::' . $this->entity->getEntityKey() . '.entity.entity_title');
		$meta->link = $request->url();
		$meta->description = $this->data->seo->seo_description;
		$meta->language = $this->language;
		$meta->updated = $this->data->objects->first()->updated_at;

		$this->data->meta = $meta;

		$this->data->feedtype = $format; // rss, atom

		$contents = view('feed.feed')->with('data', $this->data);

		return response($contents)->header('Content-Type', 'application/xml;charset=UTF-8');

	}

	/**
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
