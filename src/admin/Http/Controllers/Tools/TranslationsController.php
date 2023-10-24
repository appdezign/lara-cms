<?php

namespace Lara\Admin\Http\Controllers\Tools;

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

use Lara\Admin\Http\Traits\AdminTranslationTrait;

use Lara\Common\Models\Language;
use Lara\Common\Models\Translation;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

use File;

use Lang;

class TranslationsController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
	use AdminViewTrait;

	use AdminTranslationTrait;

	/**
	 * @var string
	 */
	protected $modelClass = Translation::class;

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

		// get content languages
		$this->data->clanguages = Language::isPublished()->orderBy('position')->get();
		$this->data->clanguage = $this->getContentLanguage($request, $this->entity);

		// get modules
		$modules = config('lara-common.translations.modules');
		$this->data->modules = json_decode(json_encode($modules), false);

		// get current module
		if (Auth::user()->isAn('administrator')) {
			$defaultmodule = 'lara-admin';
		} else {
			$defaultmodule = 'lara-front';
		}
		$module = $this->getRequestParam($request, 'module', $defaultmodule, $this->entity->getEntityKey());

		// get group
		$filtergroup = $this->getRequestParam($request, 'cgroup', null, $this->entity->getEntityKey(), true);

		// duplicates
		$this->data->duplicates = $this->findDuplicates($this->data->clanguage, $module, $filtergroup);

		// get objects
		$this->data->objects = $this->getTranslationObjects($this->entity, $module, $request, $this->data->clanguage);

		// get translations counts
		$this->data->tcount = $this->getTranslationCount($module);

		// check sync
		$this->data->needsync = $this->checkTranslationSync();

		// get distinct group values
		$this->data->groups = $this->modelClass::distinct()
			->where('module', $module)
			->orderBy('cgroup')
			->pluck('cgroup', 'cgroup')
			->toArray();

		// get distinct tag values
		$this->data->tags = $this->modelClass::distinct()
			->where('module', $module)
			->orderBy('tag')
			->pluck('tag', 'tag')
			->toArray();

		// get params
		$this->data->filters = $this->getTranslationIndexParams($this->entity, $module, $request);

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{

		// check if user has access to method
		$this->authorizer('create', $this->modelClass);

		$params = [
			'module'  => $request->input('module'),
			'cgroup'  => $request->input('_filtergroup'),
			'tag'     => $request->input('_filtertaxonomy'),
			'missing' => $request->input('_missing'),
		];

		if ($request->get('cgroup') == 'new') {
			if ($request->get('_new_group')) {
				$cgroup = $request->get('_new_group');
			} else {
				flash(_lanq('lara-admin::translation.message.new_groupname_empty'))->error();
				return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', $params);
			}
		} else {
			$cgroup = $request->get('cgroup');
		}

		if ($request->get('tag') == 'new') {
			if($request->get('_new_tag')) {
				$tag = $request->get('_new_tag');
			} else {
				flash(_lanq('lara-admin::translation.message.new_tagname_empty'))->error();
				return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', $params);
			}
		} else {
			$tag = $request->get('tag');
		}

		$request->merge(['cgroup' => $cgroup]);
		$request->merge(['tag' => $tag]);

		// save model
		$this->modelClass::create($request->all());

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', $params);

	}

	/**
	 * @param Request $request
	 * @return void
	 */
	public function update(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		dd($request);

	}

	/**
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function batch(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		$id = $request->get('translation_id');
		$object = $this->modelClass::findOrFail($id);

		$object->value = $request->get('value');
		$object->save();

		flash('Saved successfully')->success();

		$missing = ($request->input('_missing') == 1 ? 'true' : 'false');
		$params = [
			'module'  => $request->input('_module'),
			'cgroup'  => $request->input('_filtergroup'),
			'tag'     => $request->input('_filtertaxonomy'),
			'missing' => $missing,
		];

		// redirect
		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', $params);

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function import(Request $request)
	{

		$modules = config('lara-common.translations.modules');
		$this->data->modules = json_decode(json_encode($modules), false);

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
	public function saveimport(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		$modules = $request->input('objcts');

		$importCount = $this->importTranslationsFromFile($modules);

		flash($importCount . ' translations imported from file')->success();

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function export(Request $request)
	{

		$modules = config('lara-common.translations.modules');
		$this->data->modules = json_decode(json_encode($modules), false);

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
	public function saveexport(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		$modules = $request->input('objcts');

		$exportCount = $this->exportTranslationsToFile($modules);

		// set timestamp for sync
		$this->setLastTranslationSync();

		flash($exportCount . ' ' . _lanq('lara-admin::translation.message.exported_to_file'))->success();

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

	}

	/**
	 * @param Request $request
	 * @return Application|Factory|View
	 */
	public function merge(Request $request)
	{

		$modules = config('lara-common.translations.modules');
		$this->data->modules = json_decode(json_encode($modules), false);

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
	public function savemerge(Request $request)
	{

		// check if user has access to method
		$this->authorizer('update', $this->modelClass);

		$modules = $request->input('objcts');

		$this->mergeTranslations($modules);

		// set timestamp for sync
		$this->setLastTranslationSync();

		flash(_lanq('lara-admin::translation.message.translations_merged'))->success();

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function destroy(int $id)
	{

		// check if user has access to method
		$this->authorizer('delete', $this->modelClass);

		// get record
		$object = $this->modelClass::findOrFail($id);

		// delete object
		$object->forceDelete();

		// flash message
		$this->flashMessage($this->entity->getEntityKey(), 'delete_success', 'succes',1);

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

	}

	/**
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function check(Request $request) {

		// check if user has access to method
		$this->authorizer('view', $this->modelClass);

		// purge
		$this->purgeOrphanTranslations();

		// check all translations
		$this->checkAllTranslations();

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

	}

}

