<?php

namespace Lara\Admin\Http\Traits;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Lara\Common\Models\Entity;
use Lara\Common\Models\Layout;
use Lara\Common\Models\MediaFile;
use Lara\Common\Models\MediaImage;
use Lara\Common\Models\MediaVideo;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\ObjectOpengraph;
use Lara\Common\Models\ObjectSeo;
use Lara\Common\Models\Page;
use Lara\Common\Models\Sync;
use Lara\Common\Models\Tag;

trait AdminTrait
{

	/**
	 * Create a new empty Laravel object
	 *
	 * See: https://alanstorm.com/laravel_objects_make/
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function makeNewObject()
	{

		$app = app();
		$newobject = $app->make('stdClass');

		return $newobject;

	}

	/**
	 * Get the specified request parameter
	 *
	 * The fallback order is:
	 * - request
	 * - session
	 * - default
	 *
	 * A request parameter can be stored in the session globally,
	 * or it can be stored for the specific content entity
	 *
	 * @param Request $request
	 * @param string $param
	 * @param mixed $default
	 * @param string $tag
	 * @param bool $reset
	 * @return bool|mixed|null
	 */
	private function getRequestParam(Request $request, string $param, $default = null, $tag = 'global', $reset = false)
	{

		$tag = '_lara_' . $tag;

		if ($request->has($param) || $reset == true) {

			$value = $request->get($param);

			if (session()->has($tag)) {

				$tagSession = session($tag);
				$tagSession[$param] = $value;
				session([$tag => $tagSession]);

			} else {

				session([
					$tag => [
						$param => $value,
					],
				]);

			}

		} else {

			if (session()->has($tag)) {
				$tagSession = session($tag);
				if (array_key_exists($param, $tagSession)) {
					if (!empty($tagSession[$param])) {
						$value = $tagSession[$param];
					} else {
						$value = $default;
					}
				} else {
					$value = $default;
				}
			} else {
				$value = $default;
			}
		}

		// convert true/false strings to boolean
		if ($value == 'true') {
			return true;
		} elseif ($value == 'false') {
			return false;
		} else {
			return $value;
		}

	}

	/**
	 * Backend users sometimes close their browser,
	 * while they are still editing an object.
	 * This leaves the object in a locked state.
	 *
	 * When the same user calls the index method of this entity,
	 * we assume that we can unlock all objects of this entity,
	 * that are locked by this specific user
	 *
	 * @param string $modelClass
	 * @return void
	 */
	private function unlockAbandonedObjects(string $modelClass)
	{

		$objects = $modelClass::where('locked_by', Auth::user()->id)->get();

		if (!empty($objects)) {
			foreach ($objects as $object) {
				$object->unlockRecord();
			}
		}

	}

	/**
	 * Get the content language
	 *
	 * Important:
	 * in the backend the content langauge is NOT the same as the interface language
	 * For example:
	 * You can edit Dutch content in an English interface, or vice versa.
	 *
	 * @param Request $request
	 * @param object|null $entity
	 * @return mixed
	 */
	private function getContentLanguage(Request $request, $entity = null)
	{

		$key = 'clanguage';

		$default = config('app.locale');

		if (!empty($entity) && $entity->hasLanguage()) {

			// get language from request or session
			$clanguage = $this->getRequestParam($request, $key, $default, 'global');

		} else {

			// set default language
			$clanguage = $default;
		}

		return $clanguage;

	}

	/**
	 * Get the status of an object
	 *
	 * Check the publish field
	 * Check the publish_from field
	 * Check the publish_to field
	 *
	 * @param object $entity
	 * @param object $object
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getObjectStatus(object $entity, object $object)
	{

		$app = app();
		$status = $app->make('stdClass');

		if ($entity->hasStatus()) {

			if ($object->publish == 0) {

				$status->publish = false;
				$status->message = _lanq('lara-admin::default.message.status_concept');

			} else {

				if ($object->publish_from > Carbon::now()->toDateTimeString()) {

					$status->publish = false;
					$status->message = _lanq('lara-admin::default.message.status_not_published_yet');

				} elseif ($entity->hasExpiration() && !is_null($object->publish_to) && $object->publish_to < Carbon::now()->toDateTimeString()) {

					$status->publish = false;
					$status->message = _lanq('lara-admin::default.message.status_expired');

				} else {

					$status->publish = true;
					$status->message = _lanq('lara-admin::default.message.status_published');

				}

			}

		} else {
			$status->publish = true;
			$status->message = _lanq('lara-admin::default.message.status_always_published');
		}

		return $status;

	}

	/**
	 * Create a flash message
	 *
	 * @param string $entityKey
	 * @param int|null $objectCount
	 * @param string $messageKey
	 * @param string $type
	 * @return void
	 */
	private function flashMessage(string $entityKey, string $messageKey, string $type, int $objectCount = null)
	{

		$module = $this->getModuleByEntKey($entityKey);

		$entitySingle = _lanq('lara-' . $module . '::' . $entityKey . '.entity.entity_single');
		$entityPlural = _lanq('lara-' . $module . '::' . $entityKey . '.entity.entity_plural');

		$message = _lanq('lara-admin::default.message.' . $messageKey);

		if (empty($objectCount)) {
			$fullMessage = $entitySingle . ' ' . $message;
		} elseif ($objectCount == 1) {
			$fullMessage = $objectCount . ' ' . $entitySingle . ' ' . $message;
		} elseif ($objectCount > 1) {
			$fullMessage = $objectCount . ' ' . $entityPlural . ' ' . $message;
		} else {
			$fullMessage = $entitySingle . ' ' . $message;
		}

		if ($type == 'success') {
			flash($fullMessage)->success();
		} elseif ($type == 'warning') {
			flash($fullMessage)->warning();
		} elseif ($type == 'error') {
			flash($fullMessage)->errorOnDisk();
		} elseif ($type == 'overlay') {
			flash($fullMessage)->overlay();
		}

	}

	/**
	 * Get the module name based on the entity key
	 *
	 * @param string $entityKey
	 * @return string
	 */
	private function getModuleByEntKey(string $entityKey)
	{

		$entity = Entity::where('entity_key', $entityKey)->first();

		if ($entity && ($entity->egroup->key == 'entity' || $entity->egroup->key == 'form')) {
			$module = 'eve';
		} else {
			$module = 'admin';
		}

		return $module;

	}

	/**
	 * Get the value of a specific key in the Setting Model
	 *
	 * @param string $cgroup
	 * @param string $key
	 * @param string|null $type
	 * @return Carbon|string|false
	 */
	private function getSetting(string $cgroup, string $key, $type = null)
	{

		$modelClass = \Lara\Common\Models\Setting::class;

		// get record
		$object = $modelClass::where('cgroup', $cgroup)
			->where('key', $key)
			->first();

		if ($object) {

			if ($type == 'date') {
				return Carbon::createFromFormat('Y-m-d H:i:s', $object->value);
			} else {
				return $object->value;
			}

		} else {

			return false;

		}

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

		$modelClass = \Lara\Common\Models\Setting::class;

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
	 * @param int $entity_id
	 * @param int $object_id
	 * @return bool
	 */
	private function isObjectInMenu(int $entity_id, int $object_id)
	{

		$menuitem = Menuitem::where('entity_id', $entity_id)->where('object_id', $object_id)->get();

		if ($menuitem->count() > 0) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Check if the main menu exists
	 * If not, create it
	 *
	 * @return int
	 */
	private function getMainMenuId()
	{

		$mainMenu = Menu::where('slug', 'main')->first();

		if ($mainMenu) {

			return $mainMenu->id;

		} else {

			// create main menu
			$newMainMenu = Menu::create([
				'title' => 'Main',
				'slug'  => 'main',
			]);

			return $newMainMenu->id;

		}

	}

	/**
	 * @param string $language
	 * @param object $entity
	 * @param string $method
	 * @return object|null
	 */
	private function getModulePage(string $language, object $entity, string $method)
	{

		if ($entity->getEgroup() == 'entity') {

			$modulePageSlug = $entity->entity_key . '-' . $method . '-module-' . $language;

			$modulePage = Page::langIs($language)->groupIs('module')->where('slug', $modulePageSlug)->first();

			return $modulePage; // returns null if not found

		} else {

			return null;

		}

	}

	/**
	 * Get the return page for this widget
	 *
	 * @param Request $request
	 * @param string $entity_key
	 * @param int $id
	 * @return object|null
	 */
	private function getWidgetReturnPage(Request $request, string $entity_key, int $id)
	{

		$returnPageID = $this->getRequestParam($request, 'returnpage', null, $entity_key);

		$returnWidgetID = $this->getRequestParam($request, 'returnwidget', null, $entity_key);

		if ($returnPageID && $returnWidgetID == $id) {
			$returnpage = Page::find($returnPageID);

			return $returnpage;
		} else {
			return null;
		}
	}

	/**
	 * Get the related entity model for this module page
	 * The entity key of the related entity can be found in the page slug
	 *
	 * @param object $object
	 * @return mixed
	 */
	private function getModulePageModule(object $object)
	{

		list($relatedEntityKey, $method, $mpage, $lang) = explode('-', $object->slug);

		$laraClass = $this->getEntVarByKey($relatedEntityKey);
		$modulePageModule = new $laraClass;

		return $modulePageModule;

	}

	/**
	 * Translate entity key to a full Lara Entity class name
	 *
	 * @param string $entityKey
	 * @return string
	 */
	private function getEntVarByKey(string $entityKey)
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
	private function clearCacheOnSave($entity)
	{
		if (in_array($entity->getEgroup(), config('lara.clear_cache_on_save'))) {
			Artisan::call('cache:clear');
			Artisan::call('httpcache:clear');
		}
	}

	private function purgeSpam()
	{

		// get all form entities
		$entities = Entity::entityGroupIs('form')->get();
		foreach ($entities as $entity) {
			$modelClass = $entity->getEntityModelClass();
			$trashed = $modelClass::onlyTrashed()->where('deleted_at', '<=', now()->subMonth())->get();
			foreach ($trashed as $object) {
				$object->forceDelete();
			}
		}

	}

	private function getAdminLaraVersion()
	{

		$laracomposer = file_get_contents(base_path('/laracms/core/composer.json'));
		$laracomposer = json_decode($laracomposer, true);
		$laraVersionStr = $laracomposer['version'];

		$laraversion = $this->makeNewObject();
		$laraversion->version = $laraVersionStr;
		list($laraversion->major, $laraversion->minor, $laraversion->patch) = explode('.', $laraVersionStr);

		return $laraversion;

	}

	/**
	 * Check if this object is an Entity Block (alias)
	 * if so, redirect (back) to the associated entity
	 *
	 * @param object $object
	 * @param object $entity
	 * @return RedirectResponse|false
	 */
	private function redirectModulePageToEntity(object $object, object $entity)
	{

		$slug = explode('-', $object->slug);
		$associated = $slug[0];

		if ($entity->isAlias() && $entity->getAlias() == 'module') {
			return redirect()->route($entity->getPrefix() . '.' . $associated . '.index')->send();
		} else {
			return false;
		}

	}

	/**
	 * purge orphans from polymorphic relations
	 *
	 * @return void
	 */
	private function checkForOrphans()
	{

		// timestamp
		$this->setSetting('system', 'lara_cleanup_orphans', Carbon::now());

		// media
		$images = MediaImage::all();
		foreach ($images as $image) {
			if (class_exists($image->entity_type)) {
				$object = $image->entity()->first();
				if (empty($object)) {
					$image->delete();
				}
			} else {
				$image->delete();
			}
		}

		// videos
		$videos = MediaVideo::all();
		foreach ($videos as $video) {
			if (class_exists($video->entity_type)) {
				$object = $video->entity()->first();
				if (empty($object)) {
					$video->delete();
				}
			} else {
				$video->delete();
			}
		}

		// files
		$files = MediaFile::all();
		foreach ($files as $file) {
			if (class_exists($file->entity_type)) {
				$object = $file->entity()->first();
				if (empty($object)) {
					$file->delete();
				}
			} else {
				$file->delete();
			}
		}

		// layout
		$layouts = Layout::all();
		foreach ($layouts as $layout) {
			if (class_exists($layout->entity_type)) {
				$object = $layout->entity()->first();
				if (empty($object)) {
					$layout->delete();
				}
			} else {
				$layout->delete();
			}
		}

		// opengraph
		$ogs = ObjectOpengraph::all();
		foreach ($ogs as $og) {
			if (class_exists($og->entity_type)) {
				$object = $og->entity()->first();
				if (empty($object)) {
					$og->delete();
				}
			} else {
				$og->delete();
			}
		}

		// seo
		$seos = ObjectSeo::all();
		foreach ($seos as $seo) {
			if (class_exists($seo->entity_type)) {
				$object = $seo->entity()->first();
				if (empty($object)) {
					$seo->delete();
				}
			} else {
				$seo->delete();
			}
		}

		// sync
		$syncs = Sync::all();
		foreach ($syncs as $sync) {
			if (class_exists($sync->entity_type)) {
				$object = $sync->entity()->first();
				if (empty($object)) {
					$sync->delete();
				}
			} else {
				$sync->delete();
			}
		}

		// related
		$relatedTable = config('lara-common.database.object.related');
		$relations = DB::table($relatedTable)->get();
		foreach ($relations as $relation) {

			// check primary object
			$entity = Entity::where('entity_key', $relation->entity_key)->first();
			if ($entity) {
				$primaryModelClass = $entity->entity_model_class;
				if (class_exists($primaryModelClass)) {
					$primobj = $primaryModelClass::where('id', $relation->object_id)->first();
					if (empty($primobj)) {
						DB::table($relatedTable)->where('id', $relation->id)->delete();
					}
				} else {
					DB::table($relatedTable)->where('id', $relation->id)->delete();
				}
			} else {
				DB::table($relatedTable)->where('id', $relation->id)->delete();
			}

			// check related object
			if (class_exists($relation->related_model_class)) {
				$relatedModelClass = $relation->related_model_class;
				$relobj = $relatedModelClass::where('id', $relation->related_object_id)->first();
				if (empty($relobj)) {
					DB::table($relatedTable)->where('id', $relation->id)->delete();
				}
			} else {
				DB::table($relatedTable)->where('id', $relation->id)->delete();
			}

		}

		// pageable
		$pageablesTable = config('lara-common.database.object.pageables');
		$pageables = DB::table($pageablesTable)->get();
		foreach ($pageables as $pageable) {
			if (class_exists($pageable->entity_type)) {
				$modelClass = $pageable->entity_type;
				$object = $modelClass::where('id', $pageable->entity_id)->first();
				if (empty($object)) {
					DB::table($pageablesTable)->where('id', $pageable->id)->delete();
				}
			} else {
				DB::table($pageablesTable)->where('id', $pageable->id)->delete();
			}
		}

		// taggable
		$taggablesTable = config('lara-common.database.object.taggables');
		$taggables = DB::table($taggablesTable)->get();
		foreach ($taggables as $taggable) {
			if (class_exists($taggable->entity_type)) {
				$modelClass = $taggable->entity_type;
				$object = $modelClass::where('id', $taggable->entity_id)->first();
				if (empty($object)) {
					DB::table($taggablesTable)->where('id', $taggable->id)->delete();
				}
			} else {
				DB::table($taggablesTable)->where('id', $taggable->id)->delete();
			}
		}

		// tags
		$tags = Tag::all();
		foreach ($tags as $tag) {
			$entity = Entity::where('entity_key', $tag->entity_key)->first();
			if ($entity) {
				// assume this entity is still active
			} else {
				$tag->delete();
			}
		}

	}

	/**
	 * @param $object
	 * @return bool
	 */
	private function updateLanguageSlug($entity, $object): bool
	{
		if(config('lara.is_multi_language')) {
			if($entity->hasSlug()) {
				$suffix = '-' . $object->language;
				if(substr($object->slug, -3) != $suffix) {
					$newSlug = $this->getUniqueSlug($entity,$object, $object->language);
					$object->slug = $newSlug . $suffix;
					$object->save();
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	private function getUniqueSlug($entity, $object, $language) {

		$modelClass = $entity->getEntityModelClass();

		// check if slug exists
		$slugs = $modelClass::where('id', '!=', $object->id)->pluck('slug')->toArray();
		$newSlug = $this->getUniqueRecursive($language, $object->slug, $slugs);

		return $newSlug;

	}

	private function getUniqueRecursive($language, $mySlug, $slugs, $counter = 0) {

		$newSlug = $mySlug;

		foreach($slugs as $slug) {
			if(substr($slug, -3) == '-' . $language) {
				$baseSlug = substr($slug, 0, strlen($slug) - 3);
				if($counter > 0) {
					$newSlug = $mySlug . '-' . $counter;
				}
				if($newSlug == $baseSlug) {
					$counter++;
					$this->getUniqueRecursive($language, $mySlug, $slugs, $counter);
				}
			}
		}

		if($counter > 0) {
			$mySlug = $mySlug . '-' . $counter;
		}

		return $mySlug;

	}

}

