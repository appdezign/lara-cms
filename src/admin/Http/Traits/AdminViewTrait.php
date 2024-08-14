<?php

namespace Lara\Admin\Http\Traits;

use Cache;

trait AdminViewTrait
{

	/**
	 * Get the view file
	 *
	 * Because we use default views as fallback,
	 * we have to fetch the view file dynamically
	 * We cache the view file to boost performance
	 *
	 * @param object $entity
	 * @return mixed
	 */
	private function getViewFile(object $entity)
	{

		$laraprefix = 'lara-';

		$cache_key = $entity->getEntityKey() . '_' . $entity->getMethod() . '_view';

		$viewfile = Cache::rememberForever($cache_key, function () use ($entity, $laraprefix) {

			if ($entity->getPrefix() == 'admin') {
				// module can be 'admin' or 'entity', depending on entity group
				$entityViewFile = $laraprefix . $entity->getModule() . '::' . $entity->getEntityKey() . '.' . $entity->getMethod();
			} else {
				// builder
				$entityViewFile = $laraprefix . $entity->getPrefix() . '::' . $entity->getEntityKey() . '.' . $entity->getMethod();
			}

			$defaultViewFile = $laraprefix . $entity->getPrefix() . '::_default.' . $entity->getMethod();

			if (view()->exists($entityViewFile)) {
				return $entityViewFile;
			} else {
				return $defaultViewFile;
			}

		});

		return $viewfile;

	}

	/**
	 * Get all partial view files
	 *
	 * Because we use default partials as fallback,
	 * we have to fetch partials dynamically
	 * We cache the partials to boost performance
	 *
	 * @param object $entity
	 * @return mixed
	 */
	private function getPartials(object $entity)
	{

		$cache_key = $entity->getEntityKey() . '_' . $entity->getMethod() . '_partials';

		$partials = Cache::rememberForever($cache_key, function () use ($entity) {

			$parts = config('lara-front.partials');
			$partialArray = array();
			foreach ($parts as $part) {

				$viewfile = $part['partial'];
				if (!is_null($part['action'])) {
					$method = $part['action'];
				} else {
					$method = $entity->getMethod();
				}
				$partialArray[$viewfile] = $this->getPartial($entity, $method, $viewfile);

			}

			return $partialArray;

		});

		return $partials;

	}

	/**
	 * Get a specific partial view file
	 *
	 * Because we use default partials as fallback,
	 * we have to fetch partials dynamically
	 *
	 * @param object $entity
	 * @param string $method
	 * @param string $viewfile
	 * @return string|null
	 */
	private function getPartial(object $entity, string $method, string $viewfile)
	{
		$laraprefix = 'lara-';

		$entityViewFile = $laraprefix . $entity->getModule() . '::' . $entity->getEntityKey() . '.' . $method . '.' . $viewfile;

		if($entity->getModule() == 'admin') {
			$overrideViewFile = $laraprefix . 'eve' . '::' . $entity->getEntityKey() . '.' . $method . '.' . $viewfile;
		} else {
			$overrideViewFile = null;
		}

		$defaultViewFile = 'lara-admin::_default.' . $method . '.' . $viewfile;

		if ($overrideViewFile && view()->exists($overrideViewFile)) {
			return $overrideViewFile;
		} elseif (view()->exists($entityViewFile)) {
			return $entityViewFile;
		} elseif (view()->exists($defaultViewFile)) {
			return $defaultViewFile;
		} else {
			return null;
		}

	}



}

