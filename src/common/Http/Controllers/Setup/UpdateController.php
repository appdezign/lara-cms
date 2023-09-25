<?php

namespace Lara\Common\Http\Controllers\Setup;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Lara\Admin\Http\Traits\LaraAdminHelpers;
use Lara\Admin\Http\Traits\LaraBuilder;
use Lara\Admin\Http\Traits\LaraTranslation;
use Lara\Common\Http\Traits\LaraUpdate;

use Illuminate\Http\Request;

class UpdateController extends Controller
{

	use LaraAdminHelpers;
	use LaraBuilder;
	use LaraTranslation;
	use LaraUpdate;

	/**
	 * @var bool
	 */
	protected $debug;

	/**
	 * @var string
	 */
	protected $laraVersion;

	public function __construct(Request $request)
	{
		$this->laraVersion = config('lara.lara_db_version');

		$this->debug = $request->has('debug') && $request->input('debug') == 'true';
	}

	/**
	 * @param Request $request
	 * @return Application|RedirectResponse|Redirector
	 */
	public function process(Request $request)
	{
		// dd('test');

		$this->preUpdate();

		$this->migrateEntityGroups();

		$this->migrateEntities();

		$this->migrateAuth();

		$this->fixSettings();

		$this->migrateObjectRelations();

		$this->migrateSeo();

		$this->migrateMenus();

		$this->migrateSystem();

		$this->migrateWidgets();

		$this->migrateSliders();

		$this->migrateCtas();

		$this->migrateModulePages();

		$this->fixContentLayout();

		$this->migrateManagedTables();

		$this->createNewEntityFiles();

		$this->migrateMediaFolder();

		$this->addIndexToPivotTable();

		$this->updateTranslationTable();

		$this->importTranslationsFromFile();
		// $this->mergeTranslations();

		$this->importFiles();

		if ($this->debug) {

			// dd('done');

		} else {

			$this->cleanupBackups();

			$this->setNewVersion();

			$this->clearAllCache();

			$this->setEnvironmentValue();

		}

		return redirect('/admin');

	}

}

