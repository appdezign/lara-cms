<?php

namespace Lara\Common\Http\Traits;

use Illuminate\Support\Facades\File;
use Lara\Common\Models\Setting;
use Lara\Common\Models\Translation;

trait CommonTrait {

	private function getCommonLaraVersion()
	{

		$laracomposer = file_get_contents(base_path('/laracms/core/composer.json'));
		$laracomposer = json_decode($laracomposer, true);
		$laraVersionStr = $laracomposer['version'];

		$app = app();
		$laraversion = $app->make('stdClass');

		$laraversion->version = $laraVersionStr;
		list($laraversion->major, $laraversion->minor, $laraversion->patch) = explode('.', $laraVersionStr);

		return $laraversion;

	}

	private function getCommonLaraDBVersion()
	{

		// get current DB version
		$currentBuild = Setting::where('cgroup', 'system')->where('key', 'lara_db_version')->first();

		if ($currentBuild) {
			return $currentBuild->value;
		} else {
			return null;
		}

	}

	/**
	 * @param string $language
	 * @param string $module
	 * @param string $cgroup
	 * @param string $tag
	 * @param string $key
	 * @param string $value
	 * @param bool $force
	 * @return bool
	 */
	private function checkTranslation(string $language, string $module, string $cgroup, string $tag, string $key, string $value, $force = false)
	{

		$trans = Translation::where('language', $language)
			->where('module', $module)
			->where('cgroup', $cgroup)
			->where('tag', $tag)
			->where('key', $key)
			->first();

		$change = false;

		if ($trans) {

			// check value
			if ($trans->value != $value) {
				if (substr($trans->value, 0, 1) == '_' || $force) {
					$trans->value = $value;
					$trans->save();
					$change = true;
				}
			}

		} else {

			Translation::create([
				'language' => $language,
				'module'   => $module,
				'cgroup'   => $cgroup,
				'tag'      => $tag,
				'key'      => $key,
				'value'    => $value,
			]);

			$change = true;

		}

		return $change;

	}

	/**
	 * Save the value of a specific key in the Setting Model
	 *
	 * @param string $cgroup
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	private function setSetting(string $cgroup, string $key, string $value)
	{

		$modelClass = Setting::class;

		// get record
		$object = $modelClass::where('cgroup', $cgroup)
			->where('key', $key)
			->first();

		if ($object) {

			$object->value = $value;
			$object->save();

		} else {

			$modelClass::create([
				'title'  => $key,
				'cgroup' => $cgroup,
				'key'    => $key,
				'value'  => $value,
			]);

		}

	}

	/**
	 * Translate entity key to a full Lara Entity class name
	 *
	 * @param string $entityKey
	 * @return string
	 */
	private function getCommonEntityVarByKey(string $entityKey)
	{

		$laraClass = (ucfirst($entityKey) . 'Entity');

		if (class_exists('\\Lara\\Common\\Lara\\' . $laraClass)) {
			$laraClass = '\\Lara\\Common\\Lara\\' . $laraClass;
		} else {
			$laraClass = '\\Eve\\Lara\\' . $laraClass;
		}

		return $laraClass;

	}

	/**
	 * @return void
	 */
	private function clearCache()
	{

		File::cleanDirectory(storage_path('framework/cache/data'));
		File::delete(base_path('bootstrap/cache/config.php'));
		File::cleanDirectory(storage_path('framework/views'));
		File::cleanDirectory(storage_path('httpcache'));

	}

}
