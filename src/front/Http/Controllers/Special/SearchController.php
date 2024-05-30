<?php

namespace Lara\Front\Http\Controllers\Special;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Illuminate\View\View;

use Lara\Front\Http\Traits\FrontTrait;
use Lara\Front\Http\Traits\FrontEntityTrait;
use Lara\Front\Http\Traits\FrontListTrait;
use Lara\Front\Http\Traits\FrontMenuTrait;
use Lara\Front\Http\Traits\FrontObjectTrait;
use Lara\Front\Http\Traits\FrontRoutesTrait;
use Lara\Front\Http\Traits\FrontThemeTrait;
use Lara\Front\Http\Traits\FrontViewTrait;

use Jenssegers\Agent\Agent;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;

use LaravelLocalization;

class SearchController extends Controller
{

	use FrontTrait;
	use FrontEntityTrait;
	use FrontListTrait;
	use FrontMenuTrait;
	use FrontObjectTrait;
	use FrontRoutesTrait;
	use FrontThemeTrait;
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
			$this->data->grid = $this->getGrid($this->data->layout);

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
				view()->share('firstpageload', $this->getFirstPageLoad());

				return $next($request);
			});

		}

	}

	/**
	 * @return Application|Factory|View
	 */
	public function form()
	{

		// get language versions
		$this->data->langversions = $this->getFrontLanguageVersions($this->language, $this->entity);

		// header tags
		$this->data->htag = $this->getEntityHeaderTag($this->entity);

		$viewfile = '_search.form';

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function result(Request $request)
	{

		$this->data->params = $this->getFrontParams($this->entity, $request);

		$this->data->results = $this->makeNewObj();

		$this->data->singleEntity = null;

		if ($request->has('keywords')) {

			$mainMenuID = $this->getFrontMainMenuId();

			$entities = Menuitem::distinct('entity_id')
				->langIs($this->language)
				->menuIs($mainMenuID)
				->where('type', 'page')
				->orWhere('type', 'entity')
				->where('publish', 1)
				->pluck('entity_id');

			foreach ($entities as $entity_id) {

				$entity = Entity::find($entity_id);
				$entity_key = $entity->getEntityKey();

				$laraEntity = $this->getFrontEntityByKey($entity_key);

				$collection = $entity->getEntityModelClass()::langIs($this->language);

				// filter by search keywords
				$this->data->keywords = $request->get('keywords');
				$keywords = $this->cleanupFrontSearchString($this->data->keywords);

				if ($entity_key == 'page') {
					$collection = $collection->whereNotNull('menuroute');
				}

				$collection = $collection->where(function ($q) use ($entity, $keywords) {
					foreach ($keywords as $value) {

						$entityKey = $entity->getEntityKey();
						$entitySearchFields = config('lara-front.entity_search_fields');
						if(key_exists($entityKey, $entitySearchFields)) {
							// custom search fields
							$customSearchFields = $entitySearchFields[$entityKey];
							foreach($customSearchFields as $customSearchField) {
								$q->orWhere($customSearchField, 'like', "%{$value}%");
							}
						} else {
							// default search fields (title, lead, body)
							$q->orWhere('title', 'like', "%{$value}%");
							if ($entity->columns->has_lead) {
								$q->orWhere('lead', 'like', "%{$value}%");
							}
							if ($entity->columns->has_body) {
								$q->orWhere('body', 'like', "%{$value}%");
							}
						}
					}
				});

				if($laraEntity->hasStatus()) {
					$collection = $collection->where('publish', 1);
				}

				if ($entity_key != 'page') {
					if ($laraEntity->getSortField()) {
						$collection = $collection->orderBy($laraEntity->getSortField(), $laraEntity->getSortOrder());
					}
					if ($laraEntity->getSortField2nd()) {
						$collection = $collection->orderBy($laraEntity->getSortField2nd(), $laraEntity->getSortOrder2nd());
					}
				}

				$objects = $collection->get();

				// get menu urls
				foreach ($objects as $object) {
					$obj = $object;
					$object->url = $this->getFrontSeoUrl($entity_key, 'show', 'index', $obj);
				}

				$this->data->results->$entity_key = $this->makeNewObj();
				$this->data->results->$entity_key->entity = $laraEntity;
				$this->data->results->$entity_key->objects = $objects;

			}

		} else {

			$this->data->keywords = null;
			$this->data->results = [];

		}

		// get language versions
		$this->data->langversions = $this->getFrontLanguageVersions($this->language, $this->entity);

		// header tags
		$this->data->htag = $this->getEntityHeaderTag($this->entity);

		$viewfile = '_search.result';

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	public function modresult(Request $request, $module)
	{

		$this->data->params = $this->getFrontParams($this->entity, $request);

		$this->data->results = $this->makeNewObj();

		$this->data->singleEntity = null;

		if ($request->has('keywords')) {

			$singleEntity = Entity::where('entity_key', $module)->first();

			$mainMenuID = $this->getFrontMainMenuId();

			$ent = Menuitem::distinct('entity_id')
				->langIs($this->language)
				->menuIs($mainMenuID)
				->where('type', 'entity')
				->where('entity_id', $singleEntity->id)
				->first();

			if ($ent) {

				$entity = Entity::find($ent->entity_id);
				$entity_key = $entity->getEntityKey();

				$laraEntity = $this->getFrontEntityByKey($entity_key);

				$this->data->singleEntity = $laraEntity;

				$collection = $entity->getEntityModelClass()::langIs($this->language);

				// filter by search keywords
				$this->data->keywords = $request->get('keywords');
				$keywords = $this->cleanupFrontSearchString($this->data->keywords);

				$collection = $collection->where(function ($q) use ($entity, $keywords) {
					foreach ($keywords as $value) {
						$entityKey = $entity->getEntityKey();
						$entitySearchFields = config('lara-front.entity_search_fields');
						if(key_exists($entityKey, $entitySearchFields)) {
							// custom search fields
							$customSearchFields = $entitySearchFields[$entityKey];
							foreach($customSearchFields as $customSearchField) {
								$q->orWhere($customSearchField, 'like', "%{$value}%");
							}
						} else {
							// default search fields (title, lead, body)
							$q->orWhere('title', 'like', "%{$value}%");
							if ($entity->columns->has_lead) {
								$q->orWhere('lead', 'like', "%{$value}%");
							}
							if ($entity->columns->has_body) {
								$q->orWhere('body', 'like', "%{$value}%");
							}
						}
					}
				});

				if($laraEntity->hasStatus()) {
					$collection = $collection->where('publish', 1);
				}

				$objects = $collection->get();

				// get menu urls
				foreach ($objects as $object) {
					$obj = $object;
					$object->url = $this->getFrontSeoUrl($entity_key, 'show', 'index', $obj);
				}

				$this->data->results->$entity_key = $this->makeNewObj();
				$this->data->results->$entity_key->entity = $laraEntity;
				$this->data->results->$entity_key->objects = $objects;

			}

		}

		$viewfile = '_search.result';

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

}
