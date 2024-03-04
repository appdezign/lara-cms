<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Entitygroup;
use Lara\Common\Models\Language;
use Lara\Common\Models\Setting;
use Lara\Common\Models\Translation;

use File;

use Bouncer;

use Config;

trait AdminTranslationTrait
{

	/**
	 * Get the Translation objects
	 *
	 * Can be filtered by:
	 * - module
	 * - group
	 * - tag
	 * - keyword (search)
	 *
	 * @param object $entity
	 * @param string $module
	 * @param Request $request
	 * @param string $clanguage
	 * @return mixed
	 */
	private function getTranslationObjects(object $entity, string $module, Request $request, string $clanguage)
	{

		$modelClass = $entity->getEntityModelClass();

		// get filters
		$missing = $this->getRequestParam($request, 'missing', false, $entity->getEntityKey(), true);
		$filtertaxonomy = $this->getRequestParam($request, 'tag', null, $entity->getEntityKey(), true);

		$defaultgroup = config('lara-translations.modules.' . $module . '.default');
		$filtergroup = $this->getRequestParam($request, 'cgroup', $defaultgroup, $entity->getEntityKey(), true);

		if ($missing === true) {

			$objects = $modelClass::langIs($clanguage)
				->where('module', $module)
				->where(DB::raw("substring(value, 1, 1)"), '=', '_')
				->orWhere('value', '')
				->orderBy('cgroup', 'asc')
				->orderBy('tag', 'asc')
				->orderBy('key', 'asc')
				->get();

		} else {

			// get search results
			if ($request->has('keywords')) {

				// get keywords
				$keywords_str = $request->get('keywords');
				$keywords = $this->cleanupSearchString($keywords_str);

				$objects = $modelClass::langIs($clanguage)
					->where('module', $module)
					->where(function ($q) use ($keywords) {
						foreach ($keywords as $value) {
							$q->orWhere('key', 'like', "%{$value}%");
						}
					})
					->orderBy('tag', 'asc')
					->orderBy('key', 'asc')
					->get();

			} else {

				if (empty($filtertaxonomy)) {

					$objects = $modelClass::langIs($clanguage)
						->where('module', $module)
						->where('cgroup', $filtergroup)
						->orderBy('tag', 'asc')
						->orderBy('key', 'asc')
						->get();

				} else {

					$objects = $modelClass::langIs($clanguage)
						->where('module', $module)
						->where('cgroup', $filtergroup)
						->where('tag', $filtertaxonomy)
						->orderBy('tag', 'asc')
						->orderBy('key', 'asc')
						->get();

				}

			}
		}

		return $objects;

	}

	/**
	 * Get all parameters for the index method
	 *
	 * Because we use the same views for different entities,
	 * the view needs to know a few specific parameters:
	 *  - search
	 *  - filter by module, tag or group
	 *
	 * @param object $entity
	 * @param string $module
	 * @param Request $request
	 * @return object
	 */
	private function getTranslationIndexParams(object $entity, string $module, Request $request)
	{

		$missing = $this->getRequestParam($request, 'missing', false, $entity->getEntityKey(), true);
		$filtertaxonomy = $this->getRequestParam($request, 'tag', null, $entity->getEntityKey(), true);

		$defaultGroup = 'default';
		$filtergroup = $this->getRequestParam($request, 'cgroup', $defaultGroup, $entity->getEntityKey(), true);

		$params = (object)array(
			'search'           => false,
			'filterbytaxonomy' => false,
			'filtertaxonomy'   => '',
			'filterbygroup'    => false,
			'filtergroup'      => '',
			'missing'          => false,
			'module'           => $module,
		);

		if ($missing === true) {

			$params->missing = true;
			$params->search = false;
			$params->filterbytaxonomy = false;
			$params->filterbygroup = false;

		} else {

			if ($request->has('keywords')) {

				$params->missing = false;
				$params->search = true;
				$params->filterbytaxonomy = false;
				$params->filterbygroup = false;

			} else {

				if (empty($filtertaxonomy)) {

					$params->missing = false;
					$params->search = false;
					$params->filterbytaxonomy = false;
					$params->filterbygroup = true;

					$params->filtergroup = $filtergroup;

				} else {

					$params->missing = false;
					$params->search = false;
					$params->filterbytaxonomy = true;
					$params->filterbygroup = true;

					$params->filtertaxonomy = $filtertaxonomy;
					$params->filtergroup = $filtergroup;

				}

			}
		}

		return $params;

	}

	/**
	 * Get the file and db count of the translations
	 *
	 * @param string $module
	 * @return array
	 */
	private function getTranslationCount(string $module)
	{

		$count = array();

		$supportedLocales = array_keys(config('laravellocalization.supportedLocales'));

		foreach ($supportedLocales as $locale) {

			$filecount = $this->countTranslationsInFiles($module, $locale);
			$count['file'][$locale] = $filecount;

			$dbcount = $this->countTranslationsInDatabase($module, $locale);
			$count['db'][$locale] = $dbcount;

		}

		return $count;

	}

	/**
	 * Get the file count of the translations
	 *
	 * @param string $module
	 * @param string $locale
	 * @return int
	 */
	private function countTranslationsInFiles(string $module, string $locale)
	{

		$counter = 0;

		$resourcepath = resource_path('lang/vendor/' . $module . '/');
		$langpath = $resourcepath . $locale . '/';

		$files = File::allFiles($langpath);
		foreach ($files as $file) {

			$contents = File::getRequire($file);

			foreach ($contents as $tag => $values) {

				foreach ($values as $key => $value) {

					$counter++;

				}

			}
		}

		return $counter;

	}

	/**
	 * Get the db count of the translations
	 *
	 * @param string $module
	 * @param string $locale
	 * @return mixed
	 */
	private function countTranslationsInDatabase(string $module, string $locale)
	{

		$count = Translation::langIs($locale)
			->where('module', $module)
			->count();

		return $count;

	}

	/**
	 * Import translations from file to db
	 *
	 * @param array|null $modules
	 * @return int
	 */
	private function importTranslationsFromFile($modules = null)
	{

		if (empty($modules)) {
			// merge all modules
			$allmodules = config('lara-common.translations.modules');
			$modules = array_keys($allmodules);
		}

		$tablename = config('lara-common.database.sys.translations');

		$supportedLocales = array_keys(config('laravellocalization.supportedLocales'));

		$counter = 0;

		if (!empty($modules)) {


			foreach ($modules as $module) {

				DB::table($tablename)->where('module', $module)->delete();

				$resourcepath = resource_path('lang/vendor/' . $module . '/');

				foreach ($supportedLocales as $locale) {

					$langpath = $resourcepath . $locale . '/';
					$files = File::allFiles($langpath);
					foreach ($files as $file) {

						$cgroup = basename($file, ".php");

						$contents = File::getRequire($file);

						foreach ($contents as $tag => $values) {

							foreach ($values as $key => $value) {

								$object = new Translation;

								$object->language = $locale;
								$object->module = $module;
								$object->cgroup = $cgroup;
								$object->tag = $tag;
								$object->key = $key;
								$object->value = $value;

								$object->save();

								$counter++;

							}

						}
					}

				}

			}

			$this->setLastTranslationSync();

		}

		return $counter;

	}

	/**
	 * Export translations from db to file
	 *
	 * @param array|null $modules
	 * @return int
	 */
	private function exportTranslationsToFile($modules = null)
	{

		if (empty($modules)) {
			// merge all modules
			$allmodules = config('lara-common.translations.modules');
			$modules = array_keys($allmodules);
		}

		$supportedLocales = array_keys(config('laravellocalization.supportedLocales'));

		$counter = 0;

		foreach ($supportedLocales as $locale) {

			foreach ($modules as $module) {

				$resourcepath = resource_path('lang/vendor/' . $module . '/');
				$localepath = $resourcepath . $locale . '/';

				$this->unlockFilesInTranslationDir($localepath);

				File::cleanDirectory($localepath);

				// get groups
				$groups = Translation::distinct()
					->where('module', $module)
					->select('cgroup')
					->orderBy('cgroup', 'asc')
					->get();

				foreach ($groups as $group) {

					// get tags
					$tags = Translation::distinct()
						->where('module', $module)
						->where('cgroup', $group->cgroup)
						->select('tag')
						->orderBy('tag', 'asc')
						->get();

					$contents = "<?php\n\nreturn [\n";

					foreach ($tags as $tag) {

						$contents .= "\t'" . $tag->tag . "' => [\n";

						$objects = Translation::langIs($locale)
							->where('module', $module)
							->where('cgroup', $group->cgroup)
							->where('tag', $tag->tag)
							->orderBy('key', 'asc')
							->select('cgroup', 'key', 'value')
							->get();

						foreach ($objects as $object) {
							$contents .= "\t\t'" . $object->key . "' => '" . addslashes($object->value) . "',\n";
							$counter++;
						}

						$contents .= "\t],\n";

					}

					$contents .= "];\n";

					// define path
					$path = $localepath . $group->cgroup . '.php';

					// write to file
					File::put($path, $contents);

				}

				$this->lockFilesInTranslationDir($localepath);

			}

		}

		return $counter;
	}

	/**
	 * Check if the translation files need to be synced
	 * We compare the last db update to the last sync date
	 *
	 * @return bool
	 */
	private function checkTranslationSync()
	{

		// check sync
		$latest = Translation::orderBy('updated_at', 'desc')->first();

		if ($latest) {

			$lastUpdate = $latest->updated_at;

			$lastSync = $this->getLastTranslationSync();

			if ($lastUpdate->gt($lastSync)) {
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}

	}

	/**
	 * Get the timestamp of the last translation sync
	 *
	 * @return mixed
	 */
	private function getLastTranslationSync()
	{

		$group = 'system';
		$key = 'translation_file_sync';
		$type = 'date';

		$value = $this->getSetting($group, $key, $type);

		return $value;

	}

	/**
	 * Save the timestamp of the last translation sync
	 *
	 * @return bool
	 */
	private function setLastTranslationSync()
	{

		$group = 'system';
		$key = 'translation_file_sync';
		$value = date("Y-m-d H:i:s");

		$this->setSetting($group, $key, $value);

		return true;

	}

	/**
	 * Purge all translations of missing entities
	 *
	 * @return void
	 */
	private function purgeOrphanTranslations()
	{

		// get all entities
		$entities = Entity::whereHas('egroup', function ($query) {
			$query->where('key', 'entity')->orWhere('key', 'form');
		})->pluck('entity_key')->toArray();

		// get all translations
		$translations = Translation::where('module', 'eve')->get();
		foreach ($translations as $translation) {
			if (!in_array($translation->cgroup, $entities)) {
				$translation->forceDelete();
			}
		}

	}

	/**
	 * @param string $language
	 * @param string $module
	 * @param string|null $cgroup
	 * @return array
	 */
	private function findDuplicates(string $language, string $module, $cgroup = null)
	{

		$result = array();

		$collection = Translation::where('language', $language)
			->where('module', $module);

		if ($cgroup) {
			$collection = $collection->where('cgroup', $cgroup);
		}

		$objects = $collection->get();

		foreach ($objects as $object) {

			$duplicate = Translation::where('language', $object->language)
				->where('module', $object->module)
				->where('cgroup', $object->cgroup)
				->where('tag', $object->tag)
				->where('key', $object->key)
				->where('id', '!=', $object->id)
				->first();

			if ($duplicate) {

				$app = app();
				$res = $app->make('stdClass');

				$res->source = $object;
				$res->duplicate = $duplicate;

				$result[] = $res;

			}

		}

		return $result;

	}

	/**
	 * @param array $modules
	 * @return void
	 */
	private function mergeTranslations($modules = null): void
	{

		if (empty($modules)) {
			// merge all modules
			$allmodules = config('lara-common.translations.modules');
			$modules = array_keys($allmodules);
		}

		// DB entries have priority!
		// Entries that are in the files, but not in the DB, will be added to the DB

		foreach ($modules as $module) {

			$locales = array_keys(config('laravellocalization.supportedLocales'));

			foreach ($locales as $locale) {
				$moduleFromFile = $this->getAllTranslationsFromFile($module, $locale);

				foreach ($moduleFromFile as $cgroup => $tags) {

					foreach ($tags as $tag => $entries) {

						foreach ($entries as $key => $val) {

							// check if DB entry exists
							$check = Translation::where('language', $locale)
								->where('module', $module)
								->where('cgroup', $cgroup)
								->where('tag', $tag)
								->where('key', $key)
								->first();

							if (!$check) {

								// add entry from file to DB
								Translation::create([
									'language' => $locale,
									'module'   => $module,
									'cgroup'   => $cgroup,
									'tag'      => $tag,
									'key'      => $key,
									'value'    => $val,

								]);

							}

						}

					}

				}

			}

		}

		$this->exportTranslationsToFile($modules);

	}

	/**
	 * @param string $module
	 * @param string $locale
	 * @return mixed
	 */
	private function getAllTranslationsFromFile(string $module, string $locale)
	{

		$translations = array();

		$resourcepath = resource_path('lang/vendor/' . $module . '/');
		$langpath = $resourcepath . $locale . '/';

		$files = File::allFiles($langpath);

		foreach ($files as $file) {

			$cgroup = basename($file, ".php");

			$contents = File::getRequire($file);
			foreach ($contents as $tag => $values) {
				$translations[$cgroup][$tag] = $values;
			}
		}

		$translations = json_decode(json_encode($translations), false);

		return $translations;

	}

	/**
	 * @param string $module
	 * @param string $locale
	 * @return mixed
	 */
	private function getAllTranslationsFromCore(string $module, string $locale)
	{

		$translations = array();

		$moduleDir = substr($module, 5);
		$resourcepath = config('lara.lara_path') . '/src/' . $moduleDir . '/Resources/Lang/';
		$langpath = $resourcepath . $locale . '/';

		$files = File::allFiles($langpath);

		foreach ($files as $file) {

			$cgroup = basename($file, ".php");

			$contents = File::getRequire($file);
			foreach ($contents as $tag => $values) {
				$translations[$cgroup][$tag] = $values;
			}
		}

		$translations = json_decode(json_encode($translations), false);

		return $translations;

	}

	private function checkAllTranslations()
	{

		// get all published languages
		$this->data->clanguages = Language::isPublished()->orderBy('position')->get();

		// get default language
		$default = Language::isDefault()->first();
		$defaultLangCode = $default->code;

		// check all transaltion
		$translations = Translation::langIs($defaultLangCode)->get();

		foreach ($translations as $translation) {

			$module = $translation->module;
			$cgroup = $translation->cgroup;
			$tag = $translation->tag;
			$key = $translation->key;

			foreach ($this->data->clanguages as $clang) {
				$langcode = $clang->code;

				if ($langcode != $defaultLangCode) {
					// check if translation exists in database
					$trans = Translation::langIs($langcode)
						->where('module', $module)
						->where('cgroup', $cgroup)
						->where('tag', $tag)
						->where('key', $key)
						->first();

					if (empty($trans)) {
						Translation::create([
							'language' => $langcode,
							'module'   => $module,
							'cgroup'   => $cgroup,
							'tag'      => $tag,
							'key'      => $key,
							'value'    => '_' . $key,
						]);
					}
				}
			}
		}
	}

	/**
	 * Lock files (chmod 0444) in directories that the Builder writes to.
	 * This way we prevent local-to-remote sync overwrite updated configs, language files, etc.
	 *
	 * @param string $dirpath
	 * @param string|null $pattern
	 * @return void
	 */
	private function lockFilesInTranslationDir(string $dirpath, $pattern = null)
	{

		$files = File::allFiles($dirpath);

		foreach ($files as $file) {

			if (!empty($pattern)) {

				$filename = $file->getFilename();

				if (substr($filename, 0, strlen($pattern)) == $pattern) {

					chmod($file->getPathname(), 0444);

				}

			} else {

				chmod($file->getPathname(), 0444);

			}

		}

	}

	/**
	 * Unlock files in a directory temporarily,
	 * so the Builder can write to it
	 *
	 * @param string $dirpath
	 * @param string|null $pattern
	 * @return void
	 */
	private function unlockFilesInTranslationDir(string $dirpath, $pattern = null)
	{

		$files = File::allFiles($dirpath);

		foreach ($files as $file) {

			if (!empty($pattern)) {

				$filename = $file->getFilename();

				if (substr($filename, 0, strlen($pattern)) == $pattern) {

					chmod($file->getPathname(), 0644);

				}

			} else {

				chmod($file->getPathname(), 0644);

			}

		}

		sleep(1);

	}

	private function checkForTranslationUpdates()
	{

		$laraCoreVer = $this->getLaraCoreVersion();
		$laraCoreVersion = $laraCoreVer->version;
		$languageVersion = $this->getTranslationVersion();

		$updates = array();

		if (version_compare($laraCoreVersion, $languageVersion, '>')) {
			$updates[] = $laraCoreVersion;
		}

		if (!empty($updates)) {

			$allmodules = config('lara-common.translations.modules');
			unset($allmodules['lara-eve']);
			$modules = array_keys($allmodules);

			foreach ($modules as $module) {
				$locales = array_keys(config('laravellocalization.supportedLocales'));
				foreach ($locales as $locale) {
					$moduleFromFile = $this->getAllTranslationsFromCore($module, $locale);
					foreach ($moduleFromFile as $cgroup => $tags) {
						foreach ($tags as $tag => $entries) {
							foreach ($entries as $key => $val) {
								$check = Translation::where('language', $locale)
									->where('module', $module)
									->where('cgroup', $cgroup)
									->where('tag', $tag)
									->where('key', $key)
									->first();
								if ($check) {
									if (str_starts_with($check->value, '_')) {
										if (!str_starts_with($val, '_')) {
											// update DB value
											$check->value = $val;
											$check->save();
										}
									}
								} else {
									Translation::create([
										'language' => $locale,
										'module'   => $module,
										'cgroup'   => $cgroup,
										'tag'      => $tag,
										'key'      => $key,
										'value'    => $val,
									]);
								}
							}
						}
					}
				}
			}

			$this->exportTranslationsToFile($modules);

			$this->setTranslationVersion($laraCoreVersion);

			return end($updates);

		} else {
			return null;
		}

	}

	private function getLaraCoreVersion()
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

	private function getTranslationVersion()
	{

		// current version
		$currentVersion = Setting::where('cgroup', 'system')->where('key', 'lara_translation_version')->first();

		if (empty($currentVersion)) {

			$currentVersion = Setting::create([
				'title'  => 'Lara Translation Version',
				'cgroup' => 'system',
				'key'    => 'lara_translation_version',
				'value'  => '1.0.0',
			]);
		}

		return $currentVersion->value;

	}

	private function setTranslationVersion(string $value)
	{

		$modelClass = \Lara\Common\Models\Setting::class;

		// get record
		$object = $modelClass::where('cgroup', 'system')
			->where('key', 'lara_translation_version')
			->first();

		if ($object) {

			$object->value = $value;
			$object->save();

		} else {

			$modelClass::create([
				'title'  => 'Lara Translation Version',
				'cgroup' => 'system',
				'key'    => 'lara_translation_version',
				'value'  => $value,
			]);

		}

	}

}
