<?php

namespace Lara\Admin\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Lara\Admin\Http\Traits\AdminAnalyticsTrait;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

use Cache;

class CacheController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
	use AdminViewTrait;

	use AdminAnalyticsTrait;

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
			$this->entity = $this->getLaraEntityByRoute($this->routename);

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
	 * @return Application|Factory|View
	 */
	public function index()
	{

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function clear(Request $request)
	{

		$objectIDs = $request->input('objcts');

		if (!empty($objectIDs)) {

			if (in_array('appcache', $objectIDs)) {
				File::cleanDirectory(storage_path('framework/cache/data'));
			}
			if (in_array('configcache', $objectIDs)) {
				File::delete(base_path('bootstrap/cache/config.php'));
			}
			if (in_array('viewcache', $objectIDs)) {
				File::cleanDirectory(storage_path('framework/views'));
			}
			if (in_array('httpcache', $objectIDs)) {
				File::cleanDirectory(storage_path('httpcache'));
			}
			if (in_array('routecache', $objectIDs)) {
				$request->session()->put('routecacheclear', true);
			}
			if (in_array('imgcache', $objectIDs)) {
				File::cleanDirectory(storage_path('imgcache'));
			}
			if (in_array('anacache', $objectIDs)) {
				$this->refreshAnalytics();
			}

			sleep(1);

			// flash message
			flash('All selected caches were successfully cleared')->success();

		} else {

			flash('No cache types selected')->warning();
		}

		return redirect()->route('admin.cache.index');

	}

}

