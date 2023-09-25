<?php

namespace Lara\Admin\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Lara\Admin\Http\Traits\LaraAdminHelpers;
use Lara\Admin\Http\Traits\LaraAnalytics;
use Lara\Admin\Http\Traits\LaraTranslation;
use Lara\Admin\Http\Traits\LaraDbUpdate;
use Lara\Admin\Http\Traits\LaraMenu;
use Eve\Http\Traits\EveUpdate;

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

	use LaraAdminHelpers;
	use LaraTranslation;
	use LaraAnalytics;
	use LaraDbUpdate;
	use LaraMenu;
	use EveUpdate;

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

		/* ~~~~~~~~~~~~~~~~ check for updates (start) ~~~~~~~~~~~~~~~~ */
		$processLara = $request->get('update-lara') == 'true';
		$processEve = $request->get('update-eve') == 'true';

		$laraUpdates = $this->checkForLaraUpdates($processLara);
		$eveUpdates = $this->checkForEveUpdates($processEve);

		$this->data->updates = $this->makeNewObject();
		$this->data->updates->lara = $laraUpdates;
		$this->data->updates->eve = $eveUpdates;

		// get new versions
		if ($request->has('newversion')) {
			$this->data->newversion = $request->get('newversion');
		}
		if ($request->has('eveversion')) {
			$this->data->eveversion = $request->get('eveversion');
		}
		/* ~~~~~~~~~~~~~~~~ check for updates (end) ~~~~~~~~~~~~~~~~ */

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

