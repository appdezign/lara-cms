<?php

namespace Lara\Common\Http\Controllers\Setup;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use Illuminate\View\View;
use Lara\Admin\Http\Traits\LaraAdminHelpers;

use Illuminate\Http\Request;

use Lara\Common\Models\Entity;

class SetupController extends Controller {

	use LaraAdminHelpers;

	/**
	 * @var string
	 */
	protected $source;

	/**
	 * @var string
	 */
	protected $dest;

	public function __construct() {

		$this->source = config('lara.lara_path') . '/src/common/Database/_Migrations';
		$this->dest = config('lara.lara_path') . '/src/common/Database/Migrations';

	}

	/**
	 * @return Application|Factory|View
	 */
	public function show() {

		try {

			DB::connection()->getPdo();

			$dbsuccess = true;

			$dbname = DB::connection()->getDatabaseName();

			$dbmessage = 'Connected to database successfully :';
			$dbmessage .= '<ul>';
			$dbmessage .= '<li>' . $dbname . '</li>';
			$dbmessage .= '</ul>';

		} catch (\Exception $e) {

			$dbsuccess = false;
			$dbmessage = "ERROR: can not connect to the database. Please check your configuration.";

		}

		return view('lara-common::setup.start', [
			'dbmessage' => $dbmessage,
			'dbsuccess' => $dbsuccess,
		]);

	}

	/**
	 * @param int $step
	 * @return Application|Factory|View
	 */
	public function stepshow(int $step) {

		$dbname = DB::connection()->getDatabaseName();

		if ($step == 1) {

			return view('lara-common::setup.step', [
				'dbname' => $dbname,
				'step' => $step,
			]);

		} elseif ($step == 2) {

			return view('lara-common::setup.step', [
				'dbname' => $dbname,
				'step' => $step,
			]);

		} elseif ($step == 3) {

			$entities = Entity::entityGroupIsOneOf(['page', 'entity'])->get();

			return view('lara-common::setup.step', [
				'dbname' => $dbname,
				'entities' => $entities,
				'step'     => $step,
			]);

		} elseif ($step == 4) {

			$entities = Entity::entityGroupIsOneOf(['page', 'entity'])->get();

			return view('lara-common::setup.step', [
				'dbname' => $dbname,
				'entities' => $entities,
				'step'     => $step,
			]);

		} elseif ($step == 5) {

			return view('lara-common::setup.step', [
				'dbname' => $dbname,
				'step' => $step,
			]);
		} elseif ($step == 6) {

			return view('lara-common::setup.step', [
				'dbname' => $dbname,
				'step' => $step,
			]);

		} else {

			return view('lara-common::setup.step', [
				'dbname' => $dbname,
				'step' => 1,
			]);

		}

	}

	/**
	 * @return RedirectResponse
	 */
	public function start() {

		flash('Setup has started')->success();

		return redirect()->route('setup.stepshow', ['step' => 1]);

	}

	/**
	 * @param int $step
	 * @return Application|RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function stepprocess(int $step) {

		if ($step == 1) {

			$this->migrateFresh($step);

		} elseif ($step == 2) {

			$this->seedEntities($step);

		} elseif ($step == 3) {

			$this->migrateContent($step);

		} elseif ($step == 4) {

			$this->seedContent($step);

		} elseif ($step == 5) {

			$this->clearAllCache();

			$this->setEnvironmentValue();

			return redirect('/admin');

		}

		$nextstep = $step + 1;

		return redirect()->route('setup.stepshow', ['step' => $nextstep]);

	}

	/**
	 * @param int $step
	 * @return void
	 */
	private function migrateFresh(int $step) {

		$source = $this->source . DIRECTORY_SEPARATOR . '_step' . $step;

		// Purge old migration files
		File::cleanDirectory($this->dest);

		// copy migration files from this step
		File::copyDirectory($source, $this->dest);

		// Migrate everything except the entity groups content, block, form
		Artisan::call('migrate:fresh', [
			'--force' => true,
		]);

		// Purge migration files
		File::cleanDirectory($this->dest);

		flash('Step ' . $step . ' was completed successfully')->success();

	}

	/**
	 * @param int $step
	 * @return void
	 */
	private function seedEntities(int $step) {

		// Seed Entities
		Artisan::call('db:seed', [
			'--class' => 'DatabaseEntitySeeder',
			'--force' => true,
		]);

		flash('Step ' . $step . ' was completed successfully')->success();

	}

	/**
	 * @param int $step
	 * @return void
	 */
	private function migrateContent(int $step) {

		$source = $this->source . DIRECTORY_SEPARATOR . '_step' . $step;

		// copy migration files from this step
		File::copyDirectory($source, $this->dest);

		// Migrate the entity groups content, block, form
		Artisan::call('migrate', [
			'--force' => true,
		]);

		// Purge migration files
		File::cleanDirectory($this->dest);

		flash('Step ' . $step . ' was completed successfully')->success();

	}

	/**
	 * @param int $step
	 * @return void
	 */
	private function seedContent(int $step) {

		// Seed Auth, Miscellaneous, System, and Content
		Artisan::call('db:seed', [
			'--class' => 'DatabaseAuthSeeder',
			'--force' => true,
		]);
		Artisan::call('db:seed', [
			'--class' => 'DatabaseObjectSeeder',
			'--force' => true,
		]);
		Artisan::call('db:seed', [
			'--class' => 'DatabaseSysSeeder',
			'--force' => true,
		]);
		Artisan::call('db:seed', [
			'--class' => 'DatabaseContentSeeder',
			'--force' => true,
		]);

		flash('Step ' . $step . ' was completed successfully')->success();

	}

	/**
	 * @return void
	 */
	private function setEnvironmentValue() {

		$envFile = app()->environmentFilePath();
		$str = file_get_contents($envFile);

		$str = str_replace("LARA_NEEDS_SETUP=true", "LARA_NEEDS_SETUP=false", $str);

		$fp = fopen($envFile, 'w');
		fwrite($fp, $str);
		fclose($fp);

	}

	/**
	 * @return void
	 */
	private function clearAllCache() {

		Artisan::call('cache:clear');
		Artisan::call('config:clear');
		Artisan::call('view:clear');

	}

}

