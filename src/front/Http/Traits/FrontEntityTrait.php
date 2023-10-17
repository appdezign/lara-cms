<?php

namespace Lara\Front\Http\Traits;


use Illuminate\Contracts\Container\BindingResolutionException;

trait FrontEntityTrait
{

	/**
	 * Get the Lara Entity Class
	 *
	 * @param string $routename
	 * @return mixed
	 */
	private function getFrontEntity(string $routename)
	{

		$route = $this->prepareFrontRoute($routename);

		$lara = $this->getEntityVar($route->entity_key);

		$entity = new $lara;

		$entity->setPrefix($route->prefix);
		$entity->setEntityRouteKey($route->entity_key);
		$entity->setMethod($route->method);

		if (isset($route->object_id)) {
			$entity->setObjectId($route->object_id);
		}

		$entity->setActiveRoute($routename);
		$entity->setBaseEntityRoute($route->prefix . '.' . $route->entity_key);

		if (isset($route->activetags)) {
			$entity->setActiveTags($route->activetags);
		}

		if (isset($route->parent_route)) {
			$entity->setParentRoute($route->parent_route);
		}

		return $entity;

	}

	/**
	 * Get the Lara Entity Class by key
	 *
	 * @param string $entity_key
	 * @return mixed|null
	 */
	private function getFrontEntityByKey(string $entity_key)
	{

		$lara = $this->getEntityVar($entity_key);

		if ($lara) {
			$entity = new $lara;
		} else {
			$entity = null;
		}

		return $entity;

	}

	/**
	 * Translate entity key to a full Lara Entity class name
	 *
	 * @param string $entity_key
	 * @return string
	 */
	private function getEntityVar(string $entity_key)
	{

		$laraClass = '\Lara\Common\Lara\\' . ucfirst($entity_key) . 'Entity';

		if (!class_exists($laraClass)) {

			$laraClass = '\Eve\Lara\\' . ucfirst($entity_key) . 'Entity';

			if (!class_exists($laraClass)) {

				$laraClass = null;

			}

		}

		return $laraClass;

	}

	/**
	 * @param string|null $routename
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function prepareFrontRoute($routename = null)
	{

		$app = app();
		$route = $app->make('stdClass');

		if (empty($routename)) {

			// assume this is a Page
			$route->prefix = 'entity';
			$route->entity_key = 'page';
			$route->method = 'show';

		} else {

			$parts = explode('.', $routename);

			if ($parts[0] == 'special') {

				if ($parts[1] == 'home') {

					$route->prefix = 'entity';
					$route->entity_key = 'page';
					$route->method = 'show';

				}

				if ($parts[1] == 'search') {

					$route->prefix = 'special';
					$route->entity_key = 'search';
					$route->method = end($parts);

				}

				if ($parts[1] == 'user') {
					$route->prefix = 'special';
					$route->entity_key = 'user';
					$route->method = end($parts);
				}

			} else {

				if ($parts[0] == 'entitytag' || $parts[0] == 'contenttag') {

					if (end($parts) == 'show') {

						$route->prefix = $parts[0];
						$route->entity_key = $parts[1];
						$route->method = end($parts);

						$route->activetags = array();

						for ($i = 2; $i < (sizeof($parts) - 2); $i++) {
							$route->activetags[] = $parts[$i];
						}

						$route->parent_route = substr($routename, 0, -5);

					} else {

						$route->prefix = $parts[0];
						$route->entity_key = $parts[1];
						$route->method = end($parts);

						$route->activetags = array();

						for ($i = 2; $i < (sizeof($parts) - 1); $i++) {
							$route->activetags[] = $parts[$i];
						}

					}

				} else {

					if (sizeof($parts) == 3) {

						// get prefix, model and method from route
						list($route->prefix, $route->entity_key, $route->method) = explode('.', $routename);

					}

					if (sizeof($parts) == 4) {

						if (end($parts) == 'show') {

							/**
							 * If we show an object from a list view (master > detail),
							 * then we need to be able to go back to that specific list view.
							 *
							 * To accomplish that, we add 'parent method' in the route name,
							 * and here we get it from the route name and pass it on to the entity object,
							 * which is passed on to the 'show view'
							 */

							// get prefix, model, parent-method, and method from route
							list($route->prefix, $route->entity_key, $route->parent_method, $route->method) = explode('.', $routename);
							$route->parent_route = $route->prefix . '.' . $route->entity_key . '.' . $route->parent_method;

						} else {

							// get prefix, model, method and id from route
							list($route->prefix, $route->entity_key, $route->method, $route->object_id) = explode('.', $routename);

						}

					}

				}

			}

		}

		return $route;

	}


}
