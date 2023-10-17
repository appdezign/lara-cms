<?php

namespace Lara\Admin\Http\Traits;

use Lara\Common\Models\Entity;

trait AdminEntityTrait
{

	/**
	 * Get the Lara Entity Class
	 *
	 * @param string $routename
	 * @param string $modelclass
	 * @return object
	 */
	private function getLaraEntity(string $routename, string $modelclass)
	{

		$lara = $this->getEntityVarByModel($modelclass);

		$entity = new $lara;

		list($prefix, $entity_route_key, $method) = explode('.', $routename);

		$entity->setPrefix($prefix);
		$entity->setEntityRouteKey($entity_route_key);
		$entity->setMethod($method);

		// check alias
		$alias_routes = config('lara-common.routes.is_alias');

		if (!empty($alias_routes) && array_key_exists($entity_route_key, $alias_routes)) {

			$alias_config = config('lara-common.routes.has_alias');

			$entity->setAlias($entity_route_key);
			$entity->setIsAlias(true);
			$entity->setAliasIsGroup($alias_config[$entity->getEntityKey()][$entity->getAlias()]['is_group']);

		}

		// filter by relation
		foreach ($entity->getRelations() as $relation) {
			if ($relation->is_filter == 1) {

				$entity->setRelationFilterForeignkey($relation->foreign_key);
				$entity->setRelationFilterEntitykey($relation->relatedEntity->entity_key);
				$entity->setRelationFilterModelclass($relation->relatedEntity->entity_model_class);
				break;
			}
		}

		return $entity;

	}

	/**
	 * Get the Lara Entity Class based on the route name
	 *
	 * @param string $routename
	 * @return object
	 */
	private function getLaraEntityByRoute(string $routename)
	{

		list($prefix, $entity_route_key, $method) = explode('.', $routename);

		$lara = $this->getEntityVarByKey($entity_route_key);

		$entity = new $lara;

		$entity->setPrefix($prefix);
		$entity->setEntityRouteKey($entity_route_key);
		$entity->setMethod($method);

		return $entity;

	}

	/**
	 * Get the full class name based on the short model class name
	 *
	 * @param string $modelClass
	 * @return string
	 */
	private function getEntityVarByModel(string $modelClass)
	{

		$str = '\\' . str_replace('Models', 'Lara', $modelClass) . 'Entity';

		return $str;

	}

	/**
	 * Translate entity key to a full Lara Entity class name
	 *
	 * @param string $entityKey
	 * @return string
	 */
	private function getEntityVarByKey(string $entityKey)
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
	 * Get the module name based on the entity key
	 *
	 * @param string $entityKey
	 * @return string
	 */
	private function getModuleByEntityKey(string $entityKey)
	{

		$entity = Entity::where('entity_key', $entityKey)->first();

		if ($entity && ($entity->egroup->key == 'entity' || $entity->egroup->key == 'form')) {
			$module = 'eve';
		} else {
			$module = 'admin';
		}

		return $module;

	}





}

