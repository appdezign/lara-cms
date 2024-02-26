<?php

namespace Lara\Admin\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Lara\Admin\Http\Traits\AdminLanguageTrait;
use Lara\Admin\Http\Traits\AdminMenuTrait;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Language;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Page;
use Lara\Common\Models\Redirect;
use Lara\Common\Models\Tag;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

use File;

use Lang;

class LanguagesController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
	use AdminViewTrait;
	use AdminLanguageTrait;
	use AdminMenuTrait;

	/**
	 * @var string
	 */
	protected $modelClass = Language::class;

	/**
	 * @var string
	 */
	protected $clanguage;

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
			$this->entity = $this->getLaraEntity($this->routename, $this->modelClass);

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
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function index(Request $request)
	{

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		// get languages
		$base = Language::isDefault()->first();
		$baseLangCode = $base->code;
		$relLangCode = $this->getContentLanguage($request, $this->entity);

		if ($relLangCode == $baseLangCode) {

			$this->data->objects = null;

		} else {

			// get active entities
			$entities = Entity::EntityGroupIsOneOf(['page', 'entity'])->whereNotNull('menu_position')->get();

			$languageObjects = array();

			foreach ($entities as $entity) {

				$modelClass = $entity->entity_model_class;

				// get objects from base language
				if ($entity->entity_key == 'page') {
					$objects = $modelClass::where('language', $baseLangCode)
						->where('cgroup', 'page')
						->orderBy($entity->columns->sort_field, $entity->columns->sort_order)
						->get();
				} else {
					$objects = $modelClass::where('language', $baseLangCode)
						->orderBy($entity->columns->sort_field, $entity->columns->sort_order)
						->get();
				}

				$items = array();

				foreach ($objects as $object) {

					$item = $this->makeNewObject();

					$item->base_id = $object->id;
					$item->base_title = $object->title;

					// check if base item has related item
					$rel = $modelClass::langIs($relLangCode)->where('language_parent', $object->id)->first();
					if ($rel) {
						$item->rel_id = $rel->id;
						$item->rel_title = $rel->title;
					} else {
						$item->rel_id = null;
						$item->rel_title = null;
					}

					$items[] = $item;
				}

				$languageObjects[$entity->entity_key] = $items;

			}

			$this->data->objects = $languageObjects;

		}

		// get data for dropdowns
		$entities = Entity::EntityGroupIsOneOf(['page', 'entity'])->whereNotNull('menu_position')->get();

		$relObjects = array();

		foreach ($entities as $entity) {

			$modelClass = $entity->entity_model_class;

			// get objects from base language
			if ($entity->entity_key == 'page') {
				$objects = $modelClass::where('language', $relLangCode)->where('cgroup', 'page')->pluck('title', 'id')->toArray();
			} else {
				$objects = $modelClass::where('language', $relLangCode)->pluck('title', 'id')->toArray();
			}

			$relObjects[$entity->entity_key] = $objects;

		}

		$this->data->relobjects = $relObjects;

		$this->data->baseLangCode = $baseLangCode;
		$this->data->relLangCode = $relLangCode;

		// dd($this->data);

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	public function batch(Request $request)
	{

		if ($request->has('saverel')) {
			$baseItem = $request->input('saverel');
			$arr = explode('_', $baseItem);

			if (sizeof($arr) == 3) {

				$entity_key = $arr[1];
				$base_id = $arr[2];
				$identifier = $entity_key . '_' . $base_id;

				if ($request->has($identifier)) {
					if (is_numeric($request->input($identifier))) {
						$newRelId = $request->input($identifier);

						// get entity
						$ent = Entity::where('entity_key', $entity_key)->first();
						if ($ent) {

							// get model class
							$modelClass = $ent->entity_model_class;

							// get related object
							$relObject = $modelClass::where('id', $newRelId)->first();

							// purge previous related object
							$relatedLanguage = $relObject->language;
							$prevs = $modelClass::langIs($relatedLanguage)->where('language_parent', $base_id)->get();
							foreach ($prevs as $prev) {
								$prev->language_parent = null;
								$prev->save();
							}

							// save new related
							if ($relObject) {
								$relObject->language_parent = $base_id;
								$relObject->save();
							}
						}
					}
				}

				// flash message
				flash('klaar')->success();

			}

		}

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|Response|View
	 */
	public function export(Request $request)
	{

		if (!Auth::user()->isAn('administrator')) {
			return response()->view('lara-admin::errors.405', [], 405);
		}

		if (config('lara-admin.languages_content.can_export')) {

			$this->data->force = $this->getRequestParam($request, 'force');

			// get language, and prepare for select
			$languages = Language::where('publish', 1)->get();
			$this->data->languages = array();
			foreach ($languages as $lang) {
				$this->data->languages[$lang->code] = $lang->code;
			}

			// get view file and partials
			$this->data->partials = $this->getPartials($this->entity);
			$viewfile = $this->getViewFile($this->entity);

			// pass all variables to the view
			return view($viewfile, [
				'data' => $this->data,
			]);

		} else {

			return response()->view('lara-admin::errors.405', [], 405);

		}

	}

	/**
	 * @param Request $request
	 * @return RedirectResponse|Response
	 */
	public function saveexport(Request $request)
	{

		if (!Auth::user()->isAn('administrator')) {
			return response()->view('lara-admin::errors.405', [], 405);
		}

		if (config('lara-admin.languages_content.can_export')) {

			$langs = Language::where('publish', 1)->pluck('code')->toArray();

			$source = $request->input('langfrom');
			$dest = $request->input('langto');
			$prefix = $request->input('prefix');

			$usePrefix = $prefix == 1;

			if (in_array($source, $langs) && in_array($dest, $langs) && $source != $dest) {

				$this->copyLanguageContent($source, $dest, $usePrefix);

				Artisan::call('cache:clear');
				Artisan::call('config:clear');
				Artisan::call('view:clear');

				$request->session()->put('routecacheclear', true);

			} else {

				flash('select the correct languages')->error();
			}

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

		} else {

			return response()->view('lara-admin::errors.405', [], 405);

		}

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|Response|View
	 */
	public function purge(Request $request)
	{

		if (!Auth::user()->isAn('administrator')) {
			return response()->view('lara-admin::errors.405', [], 405);
		}

		if (config('lara-admin.languages_content.can_purge')) {

			$this->data->force = $this->getRequestParam($request, 'force');

			// get language, and prepare for select
			$languages = Language::where('publish', 1)->get();
			$this->data->languages = array();
			foreach ($languages as $lang) {
				$this->data->languages[$lang->code] = $lang->code;
			}

			// get view file and partials
			$this->data->partials = $this->getPartials($this->entity);
			$viewfile = $this->getViewFile($this->entity);

			// pass all variables to the view
			return view($viewfile, [
				'data' => $this->data,
			]);

		} else {

			return response()->view('lara-admin::errors.405', [], 405);

		}

	}

	/**
	 * @param Request $request
	 * @return RedirectResponse|Response
	 */
	public function purgeprocess(Request $request)
	{

		if (!Auth::user()->isAn('administrator')) {
			return response()->view('lara-admin::errors.405', [], 405);
		}

		if (config('lara-admin.languages_content.can_purge')) {


			$langs = Language::where('publish', 1)->pluck('code')->toArray();

			$langpurge = $request->input('langpurge');

			if (in_array($langpurge, $langs)) {

				// Page

				$entity = Entity::where('entity_key', 'page')->first();
				$modelClass = $entity->entity_model_class;
				$lara = $this->getEntityVarByModel($modelClass);
				$laraEntity = new $lara;

				$objects = Page::withTrashed()->langIs($langpurge)->where('ishome', 0)->get();
				foreach ($objects as $object) {
					$this->deleteEntityObject($laraEntity, $object, true);
				}

				// Block, Entity
				$entities = Entity::EntityGroupIs('block')->get();
				foreach ($entities as $entity) {

					$modelClass = $entity->entity_model_class;
					$lara = $this->getEntityVarByModel($modelClass);
					$laraEntity = new $lara;

					$objects = $modelClass::withTrashed()->langIs($langpurge)->get();
					foreach ($objects as $object) {
						$this->deleteEntityObject($laraEntity, $object, true);
					}
				}

				// Entity
				$entities = Entity::EntityGroupIs('entity')->get();
				foreach ($entities as $entity) {

					$modelClass = $entity->entity_model_class;
					$lara = $this->getEntityVarByModel($modelClass);
					$laraEntity = new $lara;

					$objects = $modelClass::withTrashed()->langIs($langpurge)->get();
					foreach ($objects as $object) {
						$this->deleteEntityObject($laraEntity, $object, true);
					}
				}

				// Tags
				$tags = Tag::langIs($langpurge)->where('entity_key', '!=', 'slider')->get();
				foreach ($tags as $tag) {
					$tag->forceDelete();
				}

				// Redirects
				$redirects = Redirect::langIs($langpurge)->get();
				foreach ($redirects as $redirect) {
					$redirect->forceDelete();
				}

				// Menu
				$menuitems = Menuitem::langIs($langpurge)->whereNotNull('parent_id')->get();
				foreach ($menuitems as $menuitem) {
					$menuitem->forceDelete();
				}

				$this->checkForOrphans();

				Artisan::call('cache:clear');
				Artisan::call('config:clear');
				Artisan::call('view:clear');
				$request->session()->put('routecacheclear', true);

				flash('all language content purged')->success();

			} else {

				flash('select the correct language')->error();
			}

			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

		} else {

			return response()->view('lara-admin::errors.405', [], 405);

		}

	}

}

