<?php

namespace Lara\Front\Http\Controllers\Error;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Illuminate\View\View;
use Jenssegers\Agent\Agent;
use Lara\Common\Models\Page;
use Lara\Common\Models\Entity;
use Lara\Common\Models\User;
use Lara\Front\Http\Traits\LaraFrontHelpers;

use LaravelLocalization;

class ErrorController extends Controller
{

	use LaraFrontHelpers;

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

		$this->data = $this->makeNewObj();

		if (!App::runningInConsole()) {

			// get route name
			$this->routename = Route::current()->getName();

			// get Page entity
			$this->entity = $this->getFrontEntityByKey('page');

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
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function show(Request $request)
	{

		$parts = explode('.', $this->routename);

		$errorId = end($parts);

		// get params
		$this->data->params = $this->getFrontParams($this->entity, $request);

		$errorPage = Page::langIs($this->language)
			->where('slug', $errorId)
			->first();

		if ($errorPage) {

			$this->data->object = $errorPage;

		} else {

			if ($errorId == '404') {
				if ($this->language == 'nl') {
					$title = 'Oeps, de pagina die u zoekt, is niet gevonden';
				} else {
					$title = 'Oops, the page you requested was not found';
				}
			} else {
				$title = 'Oops, error ' . $errorId;
			}

			$user = User::where('username', 'admin')->first();
			if ($user) {
				$pageData = [
					'user_id'  => $user->id,
					'language' => $this->language,
					'title'    => $title,
					'slug'     => $errorId,
					'body'     => '',
					'cgroup'   => 'page',
				];
				$entity = Entity::where('entity_key', 'page')->first();
				if($entity->columns->has_lead == 1) {
					$pageData['lead'] = '';
				}
				$errorPage = Page::create($pageData);
				$this->data->object = $errorPage;
			} else {
				dd('Oops');
			}

		}

		// get language versions
		$this->data->langversions = $this->getFrontLanguageVersions($this->language, $this->entity, $this->data->object);


		$this->data->grid = $this->getGrid($this->data->layout);

		// template vars & override
		$this->data->gridvars = $this->getGridVars($this->entity);
		$this->data->override = $this->getGridOverride($this->entity);

		$viewfile = '_error.' . $errorId;

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

}
