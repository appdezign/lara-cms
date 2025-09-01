<?php

namespace Lara\Admin\Http\Controllers\Tools;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminDbTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;
use Lara\Admin\Http\Traits\AdminExportTrait;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Export;

use Jenssegers\Agent\Agent;
use LaravelLocalization;
use Bouncer;
use Cache;

class ExportController extends Controller
{

	use AdminTrait;
	use AdminDbTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
	use AdminViewTrait;
	use AdminExportTrait;

	/**
	 * @var string|null
	 */
	protected $modelClass = Export::class;

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

	/**
	 * @var string
	 */
	protected $mainLanguage;

	public function __construct()
	{

		// create an empty Laravel object to hold all the data
		$this->data = $this->makeNewObject();

		if (!App::runningInConsole()) {

			$this->mainLanguage = config('app.locale');

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

	public function index(Request $request)
	{

		// check if user has access to method
		if (Auth::user()->isNotAn('administrator')) {
			abort(response()->view('lara-admin::errors.405', [], 405));
		}

		$this->data->export = $this->getExport();

		$this->data->mainLanguage = $this->mainLanguage;

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	public function show(Request $request, $id)
	{

		// check if user has access to method
		if (Auth::user()->isNotAn('administrator')) {
			abort(response()->view('lara-admin::errors.405', [], 405));
		}

		$this->data->mainLanguage = $this->mainLanguage;

		$entity = Entity::find($id);

		if ($entity) {
			$this->data->export = $this->getExport($id);
		} else {
			$this->data->export = null;
		}

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		return view($viewfile, [
			'data' => $this->data,
		]);

	}

	public function export(Request $request, $id)
	{

		// check if user has access to method
		if (Auth::user()->isNotAn('administrator')) {
			abort(response()->view('lara-admin::errors.405', [], 405));
		}

		if($id == 0) {
			// export all entities to disk

			$export = $this->getExport();

			foreach($export as $ent) {

				$filename = $ent->entity_key . '.csv';

				$exportPath = Storage::disk('localdisk')->path('_export');
				if (!File::isDirectory($exportPath)) {
					File::makeDirectory($exportPath);
				}
				$filepath = $exportPath . '/' .$filename;

				$csv = $this->buildCsVContent($ent);

				// dd($csv);

				file_put_contents($filepath, $csv);

			}

		} else {
			// download CSV for single entity

			$ent = Entity::find($id);

			if ($ent) {

				$csv = $this->buildCsVContent($ent);

				// dd($csv);


				header('Content-Disposition: attachment; filename="' . $ent->entity_key . '.csv"');
				header("Cache-control: private");
				header("Content-type: application/force-download");
				header("Content-Transfer-Encoding: UTF-8");

				echo $csv;
				exit();

			}
		}

		return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');

	}

}

