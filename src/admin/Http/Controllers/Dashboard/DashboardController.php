<?php

namespace Lara\Admin\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminDbTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Lara\Admin\Http\Traits\AdminAnalyticsTrait;
use Lara\Admin\Http\Traits\AdminTranslationTrait;
use Lara\Admin\Http\Traits\AdminMenuTrait;
use Eve\Http\Traits\EveUpdateTrait;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Jenssegers\Agent\Agent;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Entitygroup;
use Lara\Common\Models\Page;
use LaravelLocalization;

use Bouncer;

use Cache;

use Analytics;

class DashboardController extends Controller
{

	use AdminTrait;
	use AdminDbTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
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
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function purge(Request $request)
	{
		if (!Auth::user()->isAn('administrator')) {
			return response()->view('lara-admin::errors.405', [], 405);
		}

		$this->data->force = $this->getRequestParam($request, 'force');

		$this->data->purgeable = $this->getPurgeableObjects();

		// get view file and partials
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	public function purgeprocess(Request $request)
	{


		$purgeable = $this->getPurgeableObjects();


		foreach($purgeable as $entGroup => $ents) {

			foreach($ents as $ent) {

				$objects = $ent['objects'];

				// get full Lara Entity
				$modelclass =$ent['modelClass'];
				$lara = $this->getEntityVarByModel($modelclass);
				$entity = new $lara;

				foreach($objects as $object) {
					$this->deleteEntityObject($entity, $object, true);
				}
			}
		}



		// menu
		DB::table('lara_menu_menu_items')->whereNotNull('parent_id')->delete();
		DB::table('lara_object_files')->delete();
		DB::table('lara_object_images')->delete();
		DB::table('lara_object_opengraph')->delete();
		DB::table('lara_object_pageables')->delete();
		DB::table('lara_object_related')->delete();
		DB::table('lara_object_sync')->delete();
		DB::table('lara_object_taggables')->delete();
		DB::table('lara_object_videofiles')->delete();
		DB::table('lara_object_videos')->delete();

		// get Homepages, so we can exclude them
		$homepages = Page::where('ishome', 1)->pluck('id')->toArray();

		// Layout
		DB::table('lara_object_layout')->where('entity_type', 'Lara\Common\Models\Page')->whereNotIn('entity_id', $homepages)->delete();

		// SEO
		DB::table('lara_object_seo')->where('entity_type', 'Lara\Common\Models\Page')->whereNotIn('entity_id', $homepages)->delete();
		DB::table('lara_object_seo')->where('entity_type', '!=', 'Lara\Common\Models\Page')->delete();

		// Tags
		DB::table('lara_object_tags')->whereNotNull('parent_id')->where('entity_key', '!=', 'slider')->delete();

		flash('All demo content has been successfully deleted')->success();


		return redirect()->route($this->entity->getPrefix() . '.dashboard.purge');

	}

	private function getPurgeableObjects() {

		$results = $this->makeNewObject();

		$purgeableGroups = ['page', 'entity', 'block', 'form'];

		foreach($purgeableGroups as $groupKey) {
			$result = array();
			$entityGroup = Entitygroup::where('key', $groupKey)->first();
			if ($entityGroup) {
				$entities = Entity::where('group_id', $entityGroup->id)->get();
				foreach ($entities as $ent) {
					$entKey = $ent->entity_key;
					$modelclass = $ent->entity_model_class;

					if($entKey == 'page') {
						$objects = $modelclass::where('ishome', 0)->where('cgroup', 'page')->whereNotIn('slug', ['404','privacy'])->withTrashed()->get();
					} else {
						$objects = $modelclass::withTrashed()->get();
					}

					$objectCount = $objects->count();

					$result[] = [
						'entityKey'   => $entKey,
						'modelClass'   => $modelclass,
						'objectCount' => $objectCount,
						'objects'     => $objects,
					];

				}
			}
			$results->$groupKey = $result;
		}

		return $results;

	}

}

