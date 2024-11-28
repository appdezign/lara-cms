<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Lara\Common\Models\Setting;

use Spatie\Geocoder\Facades\Geocoder;

use Qirolab\Theme\Theme;

trait AdminObjectTrait
{

	/**
	 * Save the custom layout for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveLayout(Request $request, object $entity, object $object)
	{

		if ($entity->hasLayout()) {

			// purge old customn values
			$object->layout()->delete();

			// get default layout
			$partials = $this->getDefaultLayout();

			// loop through layout keys
			foreach ($partials as $key => $value) {

				// compare request value with default value
				if ($request->input('_layout_' . $key) != $value) {

					// save only the custom values
					$object->layout()->create([
						'layout_key'   => $key,
						'layout_value' => $request->input('_layout_' . $key),
					]);

				}
			}
		}

	}

	/**
	 * Save the widget settings for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveLaraWidgets(Request $request, object $entity, object $object)
	{

		if ($entity->getEntityKey() == 'page') {

			if ($request->has('_save_larawidget')) {

				$larawidgetSaveArray = explode('_', $request->input('_save_larawidget'));
				$larawidgetID = end($larawidgetSaveArray);

				$larawidget = $object->widgets()->find($larawidgetID);

				if ($request->input('_larawidget_filtertaxonomy_' . $larawidgetID) == 'none') {
					$filtertaxonomy = null;
				} else {
					$filtertaxonomy = $request->input('_larawidget_filtertaxonomy_' . $larawidgetID);
				}

				$larawidget->title = $request->input('_larawidget_title_' . $larawidgetID);
				$larawidget->hook = $request->input('_larawidget_hook_' . $larawidgetID);
				$larawidget->sortorder = $request->input('_larawidget_sortorder_' . $larawidgetID);
				$larawidget->template = $request->input('_larawidget_template_' . $larawidgetID);

				if ($larawidget->type == 'text') {

					$larawidget->body = $request->input('_larawidget_body_' . $larawidgetID);

				} elseif ($larawidget->type == 'module') {

					$larawidget->term = $filtertaxonomy;
					$larawidget->imgreq = $request->input('_larawidget_imgreq_' . $larawidgetID);
					$larawidget->maxitems = $request->input('_larawidget_maxitems_' . $larawidgetID);
					$larawidget->usecache = $request->input('_larawidget_usecache_' . $larawidgetID);

				}

				$larawidget->save();

			}

		}

	}

	/**
	 * Save the sync settings for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveSync(Request $request, object $entity, object $object)
	{

		if ($entity->hasSync()) {

			if ($object->sync()->count() == 0) {

				// no sync defined yet, check for a new sync
				if (!empty($request->input('_new_remote_url'))) {

					// create new sync
					$object->sync()->create([
						'remote_url'    => $request->input('_new_remote_url'),
						'remote_suffix' => $request->input('_new_remote_suffix'),
						'ent_key'       => $entity->getEntityKey(),
						'slug'          => $object->slug,
					]);

				}

			} else {

				if ($request->input('_remote_delete') == 'DELETE') {

					$object->sync->delete();

				} else {

					// update current sync
					$object->sync->remote_url = $request->input('_remote_url');
					$object->sync->save();

				}

			}

		}

	}

	/**
	 * Save the SEO settings for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveSeo(Request $request, object $entity, object $object)
	{

		if ($entity->hasSeo()) {

			if ($object->seo()->count() == 0) {

				// create
				$object->seo()->create([
					'seo_focus'       => $request->input('_seo_focus'),
					'seo_title'       => $request->input('_seo_title'),
					'seo_description' => $request->input('_seo_description'),
					'seo_keywords'    => $request->input('_seo_keywords'),
				]);

			} else {

				// update
				$object->seo->seo_focus = $request->input('_seo_focus');
				$object->seo->seo_title = $request->input('_seo_title');
				$object->seo->seo_description = $request->input('_seo_description');
				$object->seo->seo_keywords = $request->input('_seo_keywords');

				$object->seo->save();

			}

		}

	}

	/**
	 * Save the Opengraph settings for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveOpengraph(Request $request, object $entity, object $object)
	{

		if ($entity->hasOpengraph()) {

			// fix empty values
			$og_title = ($request->input('_og_title')) ? $request->input('_og_title') : '';
			$og_description = ($request->input('_og_description')) ? $request->input('_og_description') : '';
			$og_image = ($request->input('_og_image')) ? $request->input('_og_image') : '';

			if ($object->opengraph()->count() == 0) {

				// create
				if ($request->has('_delete_image')) {
					$object->opengraph()->create([
						'og_title'       => $og_title,
						'og_description' => $og_description,
					]);
				} else {
					$object->opengraph()->create([
						'og_title'       => $og_title,
						'og_description' => $og_description,
						'og_image'       => $og_image,
					]);
				}

			} else {

				// update
				$object->opengraph->og_title = $og_title;
				$object->opengraph->og_description = $og_description;

				if (!$request->has('_delete_image')) {
					$object->opengraph->og_image = $og_image;
				}

				$object->opengraph->save();

			}

		}

	}

	/**
	 * Save GEO Coordinates based on the address
	 *
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveGeo(object $entity, object $object)
	{

		if ($entity->hasFields()) {

			foreach ($entity->getCustomColumns() as $field) {

				if ($field->fieldtype == 'geolocation') {

					$fieldname = $field->fieldname;

					if ($object->$fieldname == 'auto') {

						// check if latitude and longitude are already set
						if (empty($object->latitude) || $object->latitude == 0 || empty($object->longitude) || $object->longitude == 0) {

							// if not, check if address is complete
							if (!empty($object->address) && !empty($object->pcode) && !empty($object->city) && !empty($object->country)) {

								// Get GEO Coordinates from Google API
								$geoAddress = $object->address;
								$geoAddress .= ', ' . $object->pcode;
								$geoAddress .= ', ' . $object->city;
								$geoAddress .= ', ' . $object->country;

								$geo = Geocoder::getCoordinatesForAddress($geoAddress);

								$latitude = $geo['lat'];
								$longitude = $geo['lng'];

								if (!empty($latitude) && !empty($longitude)) {

									// Save GEO Coordinates
									$object->latitude = $latitude;

									$object->longitude = $longitude;

									$object->save();

								}

							}
						}

					}

				}

			}

		}

	}

	/**
	 * Check the GEO settings for the company address
	 *
	 * @return void
	 */
	private function checkGeoSettings($force = false)
	{

		if (config('app.env') == 'production' || $force) {

			// Get and save GEO coordinates if necessary
			$settings = Setting::pluck('value', 'key')->toArray();
			$settings = json_decode(json_encode($settings), false);

			if (empty($settings->company_latitude) || $settings->company_latitude == 0 || empty($settings->company_longitude) || $settings->company_longitude == 0) {

				// if not, check if address is complete
				if (!empty($settings->company_street) && !empty($settings->company_street_nr) && !empty($settings->company_pcode) && !empty($settings->company_city) && !empty($settings->company_country)) {

					// Get GEO Coordinates from Google API
					$geoAddress = $settings->company_street;
					$geoAddress .= ', ' . $settings->company_pcode;
					$geoAddress .= ', ' . $settings->company_city;
					$geoAddress .= ', ' . $settings->company_country;

					$geo = Geocoder::getCoordinatesForAddress($geoAddress);

					$latitude = $geo['lat'];
					$longitude = $geo['lng'];

					if (!empty($latitude) && !empty($longitude)) {

						// Save GEO Coordinates
						$latObject = Setting::keyIs('company_latitude')->first();
						if ($latObject) {
							$latObject->value = $latitude;
							$latObject->save();
						}

						$longObject = Setting::keyIs('company_longitude')->first();
						if ($longObject) {
							$longObject->value = $longitude;
							$longObject->save();
						}

					}

				}

			}

		}

	}

	/**
	 * Get the custom layout for the current object
	 * The custom layout can override the default layout settings
	 *
	 * @param object $object
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getObjectLayout(object $object)
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = (string)$partial->key;

			// get custom layout value from database
			foreach ($object->layout as $item) {
				if ($item->layout_key == $partial_key) {
					$layout->$partial_key = $item->layout_value;
				}
			}

			if (empty($layout->$partial_key)) {
				// get default value from layout XML
				foreach ($partial->items->item as $item) {
					if ($item->isDefault == 'true') {
						$layout->$partial_key = (string)$item->partialFile;
					}
				}
			}

		}

		return $layout;

	}

	/**
	 * Get the default full layout of the theme
	 * from the special layout xml file
	 *
	 * The layout that is returned only contains
	 * the default options for the partials
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getDefaultLayout()
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = $partial->key;

			foreach ($partial->items->item as $item) {
				if ($item->isDefault == 'true') {
					if ($item->partialFile == 'false') {
						$layout->$partial_key = false;
					} else {
						$layout->$partial_key = (string)$item->partialFile;
					}
				}
			}
		}

		return $layout;

	}

	/**
	 * Before we can delete an entity object, we have to delete all Polymorphic Relations,
	 * because we can not use CASCADE DELETE on Polymorphic Relations.
	 *
	 * @param object $entity
	 * @param object $object
	 * @param bool $force
	 * @return void
	 */
	private function deleteEntityObject(object $entity, object $object, $force = false)
	{

		if ($force) {

			if ($entity->hasImages()) {
				$object->media()->forceDelete();
			}
			if ($entity->hasVideos()) {
				$object->videos()->forceDelete();
			}
			if ($entity->hasFiles()) {
				$object->files()->forceDelete();
			}
			if ($entity->hasLayout()) {
				$object->layout()->forceDelete();
			}
			if ($entity->hasSeo()) {
				$object->seo()->forceDelete();
			}
			if ($entity->hasSync()) {
				$object->sync()->forceDelete();
			}

		} else {

			if ($entity->hasImages()) {
				$object->media()->delete();
			}
			if ($entity->hasVideos()) {
				$object->videos()->delete();
			}
			if ($entity->hasFiles()) {
				$object->files()->delete();
			}
			if ($entity->hasLayout()) {
				$object->layout()->delete();
			}
			if ($entity->hasSeo()) {
				$object->seo()->delete();
			}
			if ($entity->hasSync()) {
				$object->sync()->delete();
			}

		}

		// delete tags
		DB::table(config('lara-common.database.object.taggables'))
			->where('entity_type', $entity->getEntityModelClass())
			->where('entity_id', $object->id)
			->delete();

		// delete related
		DB::table(config('lara-common.database.object.related'))
			->where('entity_key', $entity->getEntityKey())
			->where('object_id', $object->id)
			->delete();

		// delete pageable
		DB::table(config('lara-common.database.object.pageables'))
			->where('entity_type', $entity->getEntityModelClass())
			->where('entity_id', $object->id)
			->delete();

		// delete object itself
		if ($force) {
			$object->forceDelete();
		} else {
			$object->delete();
		}

	}

	/**
	 * @param object $entity
	 * @param array $objectIDs
	 * @return int
	 */
	private function batchDeleteObjects(object $entity, array $objectIDs)
	{

		$objectCount = sizeof($objectIDs);

		foreach ($objectIDs as $objectID) {
			$modelClass = $entity->getEntityModelClass();
			$object = $modelClass::findOrFail($objectID);
			if ($object) {
				$object->delete();
			}
		}

		return $objectCount;

	}

	/**
	 * @param object $entity
	 * @param array $objectIDs
	 * @return int
	 */
	private function batchPublishObjects(object $entity, array $objectIDs)
	{

		$objectCount = sizeof($objectIDs);

		foreach ($objectIDs as $objectID) {
			$modelClass = $entity->getEntityModelClass();
			$object = $modelClass::findOrFail($objectID);
			if ($object) {
				$object->publish = 1;
				$object->save();
			}
		}

		return $objectCount;

	}

	/**
	 * @param object $entity
	 * @param array $objectIDs
	 * @return int
	 */
	private function batchUnPublishObjects(object $entity, array $objectIDs)
	{

		$objectCount = sizeof($objectIDs);

		foreach ($objectIDs as $objectID) {
			$modelClass = $entity->getEntityModelClass();
			$object = $modelClass::findOrFail($objectID);
			if ($object) {
				$object->publish = 0;
				$object->save();
			}
		}

		return $objectCount;

	}

	/**
	 * Get all language version of this object
	 *
	 * @param object $entity
	 * @param object $object
	 * @return mixed|null
	 */
	private function getLanguageVersions(object $entity, object $object)
	{

		if ($entity->hasLanguage()) {

			// get all language versions
			if ($object->languageParent) {
				$parent = $object->languageParent;
			} else {
				$parent = $object;
			}
			$children = $parent->languageChildren;

			$langversions = array();

			$langversions['parent'] = [
				'object_id' => $parent->id,
				'langcode'  => $parent->language,
				'title'     => $parent->title,
				'url'       => 'parent url',
				'type'      => 'parent',
			];

			if ($children->count()) {
				foreach ($children as $child) {
					$langversions['children'][] = [
						'object_id' => $child->id,
						'langcode'  => $child->language,
						'title'     => $child->title,
						'url'       => 'child url',
						'type'      => 'child',
					];
				}
			} else {
				$langversions['children'] = null;
			}

			// convert array to object
			$langversions = json_decode(json_encode($langversions), false);

			return $langversions;

		} else {

			return null;

		}

	}

	/**
	 * @param $object
	 * @param $default
	 * @return array|string[]
	 */
	private function getBladeTemplates($object, bool $default = false): array
	{

		if ($default) {

			$fileArray = ['default' => 'default'];

		} else {

			$themepath = config('theme.active');
			$widgetpath = 'laracms/themes/' . $themepath . '/views/_widgets/lara/';

			$fileArray = array();

			if ($object->type == 'module') {

				$bladepath = $widgetpath . 'entity';
				$files = Storage::disk('lara')->files($bladepath);

				foreach ($files as $file) {
					$filename = basename($file);
					$pos = strrpos($filename, '.blade.php');
					if ($pos !== false) {
						$tname = substr($filename, 0, $pos);
						if (str_ends_with($tname, $object->relentkey)) {
							$val = substr($tname, 0, strlen($tname) - strlen($object->relentkey) - 1);
							$fileArray[$val] = $val;
						}
					}
				}

			} else {

				$bladepath = $widgetpath . 'text';
				$files = Storage::disk('lara')->files($bladepath);

				foreach ($files as $file) {
					$filename = basename($file);
					$pos = strrpos($filename, '.blade.php');
					if ($pos !== false) {
						$tname = substr($filename, 0, $pos);
						if (str_starts_with($tname, $object->type)) {
							$val = substr($tname, strlen($object->type) + 1);
	 						$fileArray[$val] = $val;
						}
					}
				}
			}
		}

		return $fileArray;

	}

}

