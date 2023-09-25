<?php

namespace Lara\Front\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use Lara\Common\Models\User;
use Lara\Front\Http\Traits\LaraFrontHelpers;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

class BaseProfileController extends Controller
{

	use LaraFrontHelpers;

	/**
	 * @var string
	 */
	protected $modelClass = User::class;

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

				return $next($request);
			});
		}

	}

	public function form(Request $request)
	{

		if(!config('lara.auth.has_front_profile')) {
			return redirect()->route('special.home.show');
		}

		$this->data->object = User::find(Auth::user()->id);

		// get params
		$this->data->params = $this->getFrontParams($this->entity, $request);

		// get related module page for SEO and Intro
		$this->data->modulepage = $this->getModulePageBySlug($this->language, $this->entity, $this->entity->getMethod());

		// Use module page for Intro
		$this->data->page = $this->data->modulepage;

		// seo
		$this->data->seo = $this->getSeo($this->data->modulepage);

		// opengraph
		$this->data->opengraph = $this->getOpengraph($this->data->modulepage);

		// override default layout with custom module page layout
		$this->data->layout = $this->getObjectThemeLayout($this->data->modulepage);
		$this->data->grid = $this->getGrid($this->data->layout);

		$viewfile = '_user.profile.form';

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	public function process(Request $request)
	{

		if(!config('lara.auth.has_front_profile')) {
			return redirect()->route('special.home.show');
		}

		$id = Auth::user()->id;

		$object = $this->modelClass::findOrFail($id);

		if ($request->input('_password') != '') {
			$object->password = $request->input('_password');
		}

		// save object
		$object->update($request->all());

		// update profile
		$this->saveFrontUserProfile($request, $object);

		return redirect()->route('special.user.profile');

	}


}

