<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lara\Common\Models\Entity;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Related;

trait AdminRelatedTrait
{

	/**
	 * Get related items from other entities for the current object
	 *
	 * @param string $entityKey
	 * @param int $id
	 * @return array
	 */
	private function getRelated(string $entityKey, int $id)
	{

		$relatedItems = Related::where('entity_key', $entityKey)
			->where('object_id', $id)
			->get();

		$related = array();
		foreach ($relatedItems as $rel) {

			$relatedObject = $rel->related_model_class::find($rel->related_object_id);

			if ($relatedObject) {

				$related[] = [
					'rel_id'             => $rel->id,
					'related_entity_key' => $rel->related_entity_key,
					'related_object_id'  => $rel->related_object_id,
					'title'              => $relatedObject->title,
					'slug'               => $relatedObject->slug,
					'url'                => '',
				];

			} else {

				// object no longer exists
				Related::destroy($rel->id);

			}
		}

		return $related;
	}

	/**
	 * Save related items from other entities for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param int $id
	 * @return void
	 */
	private function saveRelated(Request $request, object $entity, int $id)
	{

		if ($entity->hasRelated()) {

			if ($request->has('_delete_related')) {

				$relDelArray = explode('_', $request->input('_delete_related'));
				$rel_id = end($relDelArray);

				Related::destroy($rel_id);

			} else {

				$relents = Entity::whereHas('objectrelations', function ($query) {
					$query->where('is_relatable', 1);
				})->get();

				foreach ($relents as $relent) {

					$fieldname = '_new_related_' . $relent->entity_key;

					if (!empty($request->input($fieldname))) {

						Related::create([
							'entity_key'          => $entity->getEntityKey(),
							'object_id'           => $id,
							'related_entity_key'  => $relent->entity_key,
							'related_model_class' => $relent->getEntityModelClass(),
							'related_object_id'   => $request->input($fieldname),
						]);

					}

				}

				// menu items
				$menuEntity = Entity::where('entity_key', 'menuitem')->first();
				$fieldname = '_new_related_' . $menuEntity->entity_key;
				if (!empty($request->input($fieldname))) {

					Related::create([
						'entity_key'          => $entity->getEntityKey(),
						'object_id'           => $id,
						'related_entity_key'  => $menuEntity->entity_key,
						'related_model_class' => $menuEntity->getEntityModelClass(),
						'related_object_id'   => $request->input($fieldname),
					]);

				}

			}

		}

	}

	/**
	 * Get content entities that can be used for related items
	 * For example:
	 * You can use a blog article as a related item,
	 * but you can not use a widget or a slider as a related item.
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param int $id
	 * @return array
	 */
	private function getRelatable(Request $request, object $entity, int $id)
	{

		$clanguage = $this->getRelContentLanguage($request, $entity);

		$entities = Entity::whereHas('objectrelations', function ($query) {
			$query->where('is_relatable', 1);
		})->get();

		$relatable = array();

		foreach ($entities as $relent) {

			$relatable[$relent->entity_key]['entity_key'] = $relent->entity_key;
			$relatable[$relent->entity_key]['title'] = $relent->title;

			// check if entity is available in the menu
			$check = Menuitem::langIs($clanguage)->where('entity_id', $relent->id)->first();
			if ($check) {
				$relatable[$relent->entity_key]['disabled'] = false;
			} else {
				$relatable[$relent->entity_key]['disabled'] = true;
			}

			// get entity objects
			$prefix = $relent->egroup->key . '_prefix';
			$table = config('lara-common.database.entity.' . $prefix) . str_plural($relent->entity_key);

			/*
			 * we have to left join the related table
			 * to avoid showing items that are already related
			 */

			$related_table = config('lara-common.database.object.related');

			$query = "SELECT o.id, o.title
						FROM " . $table . " o
						LEFT JOIN " . $related_table . " r ON o.id=r.related_object_id
						AND r.related_entity_key=? AND r.entity_key=? AND r.object_id=?
						WHERE o.language=?
						AND o.deleted_at IS NULL
						AND r.id IS NULL ";

			if ($relent->entity_key == 'page') {
				$query .= " AND o.cgroup='page' ";
			}

			if ($relent->columns->sort_field && $relent->columns->sort_order) {
				$sort = " ORDER BY " . $relent->columns->sort_field . " " . $relent->columns->sort_order;
			} else {
				$sort = " ORDER BY id";
			}

			if ($relent->entity_key == $entity->getEntityKey()) {

				// get all non-related objects, and exclude self
				$query .= " AND o.id != ? ";

				// add sorting
				$query = $query . $sort;

				$entityObjects = DB::select($query, [$relent->entity_key, $entity->entity_key, $id, $clanguage, $id]);

			} else {

				// add sorting
				$query = $query . $sort;

				// get all non-related objects
				$entityObjects = DB::select($query, [$relent->entity_key, $entity->entity_key, $id, $clanguage]);

			}

			$entityArray = array();

			foreach ($entityObjects as $entityObject) {
				$entityArray[$entityObject->id] = $entityObject->title;
			}

			$relatable[$relent->entity_key]['objects'] = $entityArray;

		}

		// add menu items with modules
		$menuArray = array();
		$menuitems = Menuitem::where('type', 'entity')->orWhere('type', 'form')->get();
		foreach ($menuitems as $menuitem) {
			$menuArray[$menuitem->id] = $menuitem->title;
		}

		$relatable['menuitem']['entity_key'] = 'menuitem';
		$relatable['menuitem']['title'] = 'Module pages';
		$relatable['menuitem']['disabled'] = false;
		$relatable['menuitem']['objects'] = $menuArray;

		return $relatable;
	}

	/**
	 * Get the content language
	 *
	 * @param Request $request
	 * @param object|null $entity
	 * @return mixed
	 */
	private function getRelContentLanguage(Request $request, $entity = null)
	{

		$key = 'clanguage';

		$default = config('lara.clanguage_default');

		if (!empty($entity) && $entity->hasLanguage()) {

			// get language from request or session
			$clanguage = $this->getRequestParam($request, $key, $default, 'global');

		} else {

			// set default language
			$clanguage = $default;
		}

		return $clanguage;

	}


}

