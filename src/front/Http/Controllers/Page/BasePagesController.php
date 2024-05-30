<?php

namespace Lara\Front\Http\Controllers\Page;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use Illuminate\View\View;
use Lara\Common\Models\Page;

use Lara\Common\Models\Taxonomy;

use Lara\Front\Http\Traits\FrontTrait;
use Lara\Front\Http\Traits\FrontEntityTrait;
use Lara\Front\Http\Traits\FrontListTrait;
use Lara\Front\Http\Traits\FrontMenuTrait;
use Lara\Front\Http\Traits\FrontObjectTrait;
use Lara\Front\Http\Traits\FrontRoutesTrait;
use Lara\Front\Http\Traits\FrontViewTrait;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

class BasePagesController extends Controller
{

	use FrontTrait;
	use FrontEntityTrait;
	use FrontListTrait;
	use FrontMenuTrait;
	use FrontObjectTrait;
	use FrontRoutesTrait;
	use FrontViewTrait;

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

	public function __construct()
	{

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
	 * Display the page.
	 *
	 * @param Request $request
	 * @param string|int|null $id
	 * @return Application|Factory|View
	 */
	public function show(Request $request, $id = null)
	{

		// get params
		$this->data->params = $this->getFrontParams($this->entity, $request);

		$eager = ['user'];
		if ($this->entity->hasImages()) {
			$eager[] = 'media';
		}
		if ($this->entity->hasVideos()) {
			$eager[] = 'videos';
		}
		if ($this->entity->hasFiles()) {
			$eager[] = 'files';
		}

		if (empty($id)) {
			// assume it is a page with a named route, inluding the ID !
			if (!empty($this->entity->getObjectId())) {
				$id = $this->entity->getObjectId();
			} else {
				// TODO 404
			}
		}

		if (is_numeric($id)) {
			$this->data->object = Page::with($eager)->find($id);
		} else {
			$slug = $id;
			$this->data->object = Page::langIs($this->language)
				->where('slug', $slug)
				->with($eager)
				->first();
		}

		if (!empty($this->data->object)) {

			// redirect pages to their menu url, if possible
			$this->checkPageRoute($this->language, $this->entity, $this->data->object->id);

			// get childs pages
			$this->data->children = $this->getPageChildren($this->language);

			// replace shortcodes
			$this->data->object = $this->replaceAllSortCodes($this->entity, $this->data->object);

			// Use Page object for Intro (Hero)
			$this->data->page = $this->data->object;

			// seo
			$this->data->seo = $this->getSeo($this->data->object);

			// opengraph
			$this->data->opengraph = $this->getOpengraph($this->data->object);

			// get language versions
			$this->data->langversions = $this->getFrontLanguageVersions($this->language, $this->entity, $this->data->object);

			// header tags
			$this->data->htag = $this->getEntityHeaderTag($this->entity);

			// override default layout with custom page layout
			$this->data->layout = $this->getObjectThemeLayout($this->data->object);

			$this->data->grid = $this->getGrid($this->data->layout);

			// template vars & override
			$this->data->gridvars = $this->getGridVars($this->entity);
			$this->data->override = $this->getGridOverride($this->entity);

			// related objects (from other entities)
			if ($this->entity->hasRelated()) {
				$this->data->relatedObjects = $this->getFrontRelated($this->entity->getEntityKey(), $this->data->object->id);
			}

			$viewfile = $this->getFrontViewFile($this->entity);

		} else {
			// 404
			$viewfile = '_error.404';
		}

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

}
