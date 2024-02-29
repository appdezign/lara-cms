<?php

namespace Lara\Front\Http\Controllers\Page;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use Illuminate\View\View;

use Lara\Front\Http\Traits\FrontTrait;
use Lara\Front\Http\Traits\FrontEntityTrait;
use Lara\Front\Http\Traits\FrontListTrait;
use Lara\Front\Http\Traits\FrontMenuTrait;
use Lara\Front\Http\Traits\FrontObjectTrait;
use Lara\Front\Http\Traits\FrontRoutesTrait;
use Lara\Front\Http\Traits\FrontViewTrait;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Qirolab\Theme\Theme;

class BaseHomeController extends Controller
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

			$this->entity->setIsHome(true);

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
	 * Display the specified resource.
	 *
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function show(Request $request)
	{

		// get params
		$this->data->params = $this->getFrontParams($this->entity, $request);

		// get object
		$this->data->object = $this->getHomePage($this->language);

		// replace shortcodes
		if ($this->entity->hasLead()) {
			$this->data->object->lead = $this->replaceShortcodes($this->data->object->lead);
		}
		if ($this->entity->hasBody()) {
			$this->data->object->body = $this->replaceShortcodes($this->data->object->body);
		}

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

		return view('content.home.show', [
			'data' => $this->data,
		]);

	}

}
