<?php

namespace Lara\Front\Http\Traits;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Lara\Common\Models\Entity;
use Lara\Common\Models\Headertag;
use Lara\Common\Models\Language;
use Lara\Common\Models\Larawidget;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Page;
use Lara\Common\Models\Setting;

trait FrontTrait
{

	/**
	 * Create a new empty Laravel object
	 *
	 * See: https://alanstorm.com/laravel_objects_make/
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function makeNewObj()
	{

		$app = app();
		$newobject = $app->make('stdClass');

		return $newobject;

	}

	/**
	 * Get all settings from a specific group
	 *
	 * Example:
	 * get all data of the group: 'company'
	 *
	 * @param string $group
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getSettingsByGroup(string $group)
	{

		$settings = Setting::groupIs($group)->get();

		$app = app();
		$object = $app->make('stdClass');

		foreach ($settings as $setting) {
			$key = $setting->key;
			$value = $setting->value;
			$object->$key = $value;
		}

		return $object;

	}



	/**
	 * Get all the language versions of an object or an entity
	 *
	 * @param string $curlang
	 * @param object $entity
	 * @param object|null $object
	 * @return array
	 * @throws BindingResolutionException
	 */
	private function getFrontLanguageVersions(string $curlang, object $entity = null, $object = null)
	{

		$versions = array();

		$languages = Language::isPublished()->get();

		foreach ($languages as $lang) {

			$version = $this->makeNewObj();

			$version->langcode = $lang->code;
			$version->langname = $lang->name;

			if ($lang->code == $curlang) {

				/*
				 * find url and route for current active page
				 */

				$version->active = true;

				if ($entity) {

					if ($entity->entity_key == 'page') {

						// The Page entity has no index method,
						// so we should have an object here
						if (!empty($object)) {

							$menuitem = Menuitem::langIs($lang->code)
								->isPublished()
								->where('entity_id', $entity->id)
								->where('object_id', $object->id)
								->first();

							if ($menuitem) {

								$languageRoutename = $menuitem->routename;
								$languageRoute = $menuitem->route;

								$version->entity = $entity->entity_key;
								$version->object = $object->id;
								$version->route = url($lang->code . '/' . $languageRoute);
								$version->routename = $languageRoutename;

							}

						}

					} else {

						// find entity in menu

						$menuitem = Menuitem::langIs($lang->code)->isPublished()->where('entity_id', $entity->id)->first();

						if ($menuitem) {

							$languageRoutename = $menuitem->routename;
							$languageRoute = $menuitem->route;

							$version->entity = $entity->entity_key;
							if (!empty($object)) {
								$version->object = $object->id;
								if($entity->hasTags()) {
									$version->route = url($lang->code . '/' . $languageRoute . '/' . $object->slug . '.html');
								} else {
									$version->route = url($lang->code . '/' . $languageRoute . '/' . $object->slug);
								}
							} else {
								$version->object = null;
								$version->route = url($lang->code . '/' . $languageRoute);
							}
							$version->routename = $languageRoutename;

						}

					}
				}

			} else {

				/*
				 * find url and route for language sibling
				 */

				$sibling = null;

				$version->active = false;

				if ($object) {
					// find sibling
					$sibling = $this->getFrontLanguageSibling($object, $lang->code);
				}

				$found = false;

				if ($entity) {

					if ($entity->entity_key == 'page') {

						// find page in menu
						if ($sibling) {

							$menuitem = Menuitem::langIs($lang->code)
								->isPublished()
								->where('entity_id', $entity->id)
								->where('object_id', $sibling->id)
								->first();

							if ($menuitem) {

								$languageRoutename = $menuitem->routename;
								$languageRoute = $menuitem->route;

								$version->entity = $entity->entity_key;
								$version->object = $sibling->id;
								$version->route = url($lang->code . '/' . $languageRoute);
								$version->routename = $languageRoutename;

								$found = true;

							}
						}

					} else {

						// find entity in menu
						$menuitem = Menuitem::langIs($lang->code)->isPublished()->where('entity_id', $entity->id)->first();

						if ($menuitem) {

							$languageRoutename = $menuitem->routename;
							$languageRoute = $menuitem->route;

							$version->entity = $entity->entity_key;
							if ($sibling) {
								$version->object = $sibling->id;
								if($entity->hasTags()) {
									$version->route = url($lang->code . '/' . $languageRoute . '/' . $sibling->slug) . '.html';
								} else {
									$version->route = url($lang->code . '/' . $languageRoute . '/' . $sibling->slug);
								}
							} else {
								$version->object = null;
								$version->route = url($lang->code . '/' . $languageRoute);
							}
							$version->routename = $languageRoutename;

							$found = true;

						}

					}

				}

				if (!$found) {

					// fall back to homepage

					$menuitem = Menuitem::langIs($lang->code)->whereNull('parent_id')->first();

					if ($menuitem) {
						$version->entity = $menuitem->entity->entity_key;
						$version->object = $menuitem->routename;
						$version->route = url($lang->code . '/');
						$version->routename = 'special.home.show';

					}

				}
			}

			$versions[] = $version;

		}

		return $versions;

	}

	/**
	 * Get a specific language version for this object
	 *
	 * @param object $object
	 * @param string $dest
	 * @return object|null
	 */
	private function getFrontLanguageSibling(object $object, string $dest)
	{

		$modelClass = get_class($object);

		// find parent
		if ($object->languageParent) {
			$parent = $object->languageParent;
		} else {
			$parent = $object;
		}

		// check if we're looking for the parent itself
		if ($parent->language == $dest) {
			return $parent;
		} else {
			// get and return sibling
			$sibling = $modelClass::langIs($dest)->where('language_parent', $parent->id)->first();

			return $sibling;
		}

	}

	/**
	 * Get all the global widgets
	 *
	 * @return object
	 */
	private function getGlobalWidgets($language)
	{

		$widgets = Larawidget::where('language', $language)->where('isglobal', 1)->get();

		return $widgets;

	}


	private function getFrontLaraVersion()
	{

		$laracomposer = file_get_contents(base_path('/laracms/core/composer.json'));
		$laracomposer = json_decode($laracomposer, true);
		$laraVersionStr = $laracomposer['version'];

		$laraversion = $this->makeNewObj();
		$laraversion->version = $laraVersionStr;
		list($laraversion->major, $laraversion->minor, $laraversion->patch) = explode('.', $laraVersionStr);

		return $laraversion;

	}

	private function getFirstPageLoad(): bool
	{

		if (session()->has('lara_first_page_load') && session()->get('lara_first_page_load')) {
			return false;
		} else {
			session(['lara_first_page_load' => true]);

			return true;
		}

	}

	private function getHeaderTag($entity) {

		$htag = $this->makeNewObj();

		$headerTag = Headertag::where('cgroup', 'module')->where('entity_id', $entity->id)->first();

		if($headerTag) {
			$htag->id = $headerTag->id;
			$htag->titleTag = $headerTag->title_tag;
			$htag->listTag = $headerTag->list_tag;

		} else {
			$htag->id = null;
			$htag->titleTag = 'h1';
			$htag->listTag = 'h3';
		}

		return $htag;
	}

}
