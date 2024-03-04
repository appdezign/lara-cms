<?php

namespace Lara\Admin\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Lara\Admin\Http\Traits\AdminAnalyticsTrait;
use Lara\Admin\Http\Traits\AdminTranslationTrait;
use Lara\Admin\Http\Traits\AdminMenuTrait;
use Eve\Http\Traits\EveUpdateTrait;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

use Cache;

use Analytics;

class DashboardController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminViewTrait;

	use AdminTranslationTrait;
	use AdminAnalyticsTrait;
	use AdminMenuTrait;
	use EveUpdateTrait;

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

			// get basic entity
			$this->entity = $this->getLaraEntityByRoute($this->routename);

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
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function index(Request $request)
	{

		// get Google Analytics
		$this->data->site = $this->getSiteStats(false, true);
		$this->data->page = $this->getPageStats(false, true);
		$this->data->ref = $this->getReferrerStats(false, true);
		$this->data->user = $this->getUserStats(false, true);
		$this->data->browser = $this->getBrowserStats(false, true);

		// get Lara Analytics
		$this->data->content = $this->getContentStats();
		$this->data->lara_users = $this->getLaraUserStats();

		// get last sync
		$this->data->lastsync = $this->getLastAnalyticsSync();

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * @return RedirectResponse
	 */
	public function refresh(Request $request)
	{

		$this->data->types = $this->makeNewObject();

		$this->data->types->sitestats = false;
		$this->data->types->pagestats = false;
		$this->data->types->refstats = false;
		$this->data->types->userstats = false;
		$this->data->types->browserstats = false;

		$singletype = $this->getRequestParam($request, 'gatype', null, $this->entity->getEntityKey(), true);

		if (isset($this->data->types->$singletype)) {
			$this->data->types->$singletype = true;
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
	 * @param array $types
	 * @return RedirectResponse
	 */
	public function getrefresh(Request $request)
	{


		$types = $request->input('objcts');

		if (!empty($types)) {

			$this->refreshAnalytics($types);

		} else {

			flash('No data types selected')->warning();
		}

		return redirect()->route($this->entity->getPrefix() . '.dashboard.index');

	}

	/**
	 * @return Application|Factory|View
	 */
	public function dbshow()
	{

		$this->data->dbcurrent = config('lara-common.database.db_database');

		$this->data->objects = $this->getDatabaseStructure($this->data->dbcurrent);

		// get view file and partials
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * @return Application|Factory|View
	 */
	public function dbcheck()
	{

		$this->data->dbsource = config('lara-common.database.db_database_src');
		$this->data->dbdest = config('lara-common.database.db_database');

		$connsrc = config('lara-common.database.db_connection_src');
		$conndest = config('lara-common.database.db_connection');

		// check database
		$this->data->result = $this->compareDatabaseStructure($this->data->dbsource, $this->data->dbdest, $connsrc, $conndest);

		// get view file and partials
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

}

