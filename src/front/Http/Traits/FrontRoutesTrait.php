<?php

namespace Lara\Front\Http\Traits;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Lara\Common\Models\Menuitem;

trait FrontRoutesTrait
{

	/**
	 * Get the Laravel route name from the given url
	 *
	 * @param string $url
	 * @return mixed
	 */
	private function getRouteFromUrl(string $url)
	{

		$route = app('router')->getRoutes()->match(app('request')->create($url))->getName();

		return $route;

	}

	/**
	 * Get a complete Frontent SEO Route for a specific entity list or object
	 *
	 * Prefix options:
	 * - entity (entity is defined in the main menu)
	 * - entitytag (entity is defined in the main menu, and has tags)
	 * - content (entity is NOT defined in the main menu, use preview route)
	 * - contenttag (entity is NOT defined in the main menu, and has tags)
	 *
	 * @param string $entity_key
	 * @param string $method
	 * @return string
	 */
	private function getFrontSeoRoute(string $entity_key, string $method)
	{

		// entity
		if (Route::has('entitytag.' . $entity_key . '.' . $method)) {
			$route = 'entitytag.' . $entity_key . '.' . $method;
		} elseif (Route::has('entity.' . $entity_key . '.' . $method)) {
			$route = 'entity.' . $entity_key . '.' . $method;
		} elseif (Route::has('contenttag.' . $entity_key . '.' . $method)) {
			$route = 'contenttag.' . $entity_key . '.' . $method;
		} else {
			$route = 'content.' . $entity_key . '.' . $method;
		}

		return $route;

	}

	/**
	 * Check if a page with a preview route can be redirected to a menu route
	 *
	 * @param string $language
	 * @param object $entity
	 * @param int $id
	 * @return false|Application|RedirectResponse
	 */
	private function checkPageRoute(string $language, object $entity, int $id)
	{

		if ($entity->getEntityKey() == 'page' && $entity->getPrefix() == 'content') {

			$menuitem = Menuitem::where('type', 'page')
				->where('object_id', $id)
				->first();

			if ($menuitem) {
				return redirect($language . '/' . $menuitem->route)->send();
			} else {
				// this is a preview page, check if user is logged in
				if (Auth::check()) {
					return false;
				} else {
					return redirect(route('error.show.404', '404'))->send();
				}
			}

		} else {
			return false;
		}

	}

	/**
	 * Check if a page with a preview route can be redirected to a menu route
	 *
	 * @param string $language
	 * @param object $entity
	 * @param object $object
	 * @return false|Application|RedirectResponse
	 */
	private function checkEntityRoute(string $language, object $entity, object $object)
	{

		$isPreview = false;

		if ($entity->getPrefix() == 'content' || $entity->getPrefix() == 'contenttag') {

			if ($object->publish == 1) {

				$menuitem = Menuitem::langIs($language)->where('type', 'entity')
					->where('entity_id', $entity->id)
					->whereNull('tag_id')
					->first();

				if ($menuitem) {

					$redirectUrl = $language . '/' . $menuitem->route . '/' . $object->slug;

					if ($entity->hasTags()) {
						$redirectUrl = $redirectUrl . '.html';
					}

					redirect($redirectUrl)->send();

				} else {
					// this is a preview page, check if user is logged in
					if (Auth::check()) {
						$isPreview = true;
					} else {
						return redirect(route('error.show.404', '404'))->send();
					}
				}

			} else {
				// this is a preview page, check if user is logged in
				if (Auth::check()) {
					$isPreview = true;
				} else {
					return redirect(route('error.show.404', '404'))->send();
				}
			}

		}

		return $isPreview;

	}

}
