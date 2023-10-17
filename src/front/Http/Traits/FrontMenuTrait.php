<?php

namespace Lara\Front\Http\Traits;

use Illuminate\Support\Facades\URL;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Page;

use Cache;

trait FrontMenuTrait
{

	/**
	 * Get the HomePage
	 *
	 * If the Mainmenu is synced to the Pages,
	 * get it from pages table directly (faster)
	 *
	 * If not, get the page ID from the menu table
	 *
	 * @param string $language
	 * @return object|null
	 */
	private function getHomePage(string $language)
	{

		$mainMenuID = $this->getFrontMainMenuId();

		if ($mainMenuID) {

			$home = Menuitem::langIs($language)
				->menuIs($mainMenuID)
				->whereNull('parent_id')
				->first();

			if ($home->object_id) {

				return Page::find($home->object_id);

			} else {
				return null;
			}

		} else {
			return null;
		}

	}

	/**
	 * Check if the main menu exists
	 * If not, create it
	 *
	 * @return int
	 */
	private function getFrontMainMenuId()
	{

		$mainMenu = Menu::where('slug', 'main')->first();

		if (empty($mainMenu)) {

			// create main menu
			$newMainMenu = Menu::create([
				'title' => 'Main',
				'slug'  => 'main',
			]);

			return $newMainMenu->id;

		} else {

			return $mainMenu->id;
		}

	}

	/**
	 * Get the full path of the active menu item
	 *
	 * @param bool $getIdOnly
	 * @return array
	 */
	private function getActiveMenuArray($getIdOnly = false)
	{

		$base_url = URL::to('/');
		$slug = substr(URL::current(), strlen($base_url));
		$route = substr($slug, 4);
		$language = substr($slug, 1, 2);
		$routename = $this->getRouteFromSlug($slug);

		$activeMenuArray = array();

		if ($routename == 'special.home.show') {

			// HOME PAGE
			$activeMenuItem = Menuitem::where('routename', $routename)->first();

			if ($activeMenuItem) {

				// add current menu item
				if ($getIdOnly) {
					$activeMenuArray[] = $activeMenuItem->id;
				} else {
					$activeMenuArray[] = $activeMenuItem;
				}

			}

		} else {

			$routeparts = explode('.', $routename);

			if (end($routeparts) == 'show') {

				// detail page, get parent
				$routepos = strrpos($route, '/');
				$route = substr($route, 0, $routepos);

				$rnamepos = strrpos($routename, '.');
				$routename = substr($routename, 0, $rnamepos);

			} else {

				// remove tags from routename
				$prefix = $routeparts[0];
				$entityKey = $routeparts[1];
				$method = end($routeparts);
				$routename = $prefix . '.' . $entityKey . '.' . $method;

			}

			// find by url
			$activeMenuItem = Menuitem::langIs($language)->where('route', $route)->first();

			if (!$activeMenuItem) {
				// find by routename
				$activeMenuItem = Menuitem::langIs($language)->where('routename', $routename)->first();
			}

			if ($activeMenuItem) {

				// add current menu item
				if ($getIdOnly) {
					$activeMenuArray[] = $activeMenuItem->id;
				} else {
					$activeMenuArray[] = $activeMenuItem;
				}

				// add parents
				$activeMenuArray = $this->getMenuParent($activeMenuItem, $activeMenuArray, $getIdOnly);

			}

		}

		return $activeMenuArray;

	}

	/**
	 * Get the Laravel route name from the given url
	 *
	 * @param string $url
	 * @return mixed
	 */
	private function getRouteFromSlug(string $slug)
	{

		$route = app('router')->getRoutes()->match(app('request')->create($slug))->getName();

		return $route;

	}

	/**
	 * Get the active Menu Item object, based on the current url
	 *
	 * @param string $language
	 * @return mixed
	 */
	private function getActiveMenuItem(string $language)
	{

		$base_url = URL::to('/');
		$slug = substr(URL::current(), strlen($base_url));

		$route = substr($slug, 4);

		$routename = $this->getRouteFromSlug($slug);

		if ($routename == 'special.home.show') {

			// HOME PAGE
			$activeMenuItem = Menuitem::langIs($language)->where('routename', $routename)->first();

		} else {

			$routeparts = explode('.', $routename);

			if (end($routeparts) == 'show') {

				// detail page, get parent
				$routepos = strrpos($route, '/');
				$route = substr($route, 0, $routepos);

				$rnamepos = strrpos($routename, '.');
				$routename = substr($routename, 0, $rnamepos);

			} else {

				// remove tags from routename
				$prefix = $routeparts[0];
				$entityKey = $routeparts[1];
				$method = end($routeparts);
				$routename = $prefix . '.' . $entityKey . '.' . $method;

			}

			// find by url
			$activeMenuItem = Menuitem::langIs($language)->where('route', $route)->first();

			if (!$activeMenuItem) {
				// find by routename
				$activeMenuItem = Menuitem::langIs($language)->where('routename', $routename)->first();
			}

		}

		return $activeMenuItem;

	}

	/**
	 * Get the parent Menu item object (recursive)
	 *
	 * @param object $menuitem
	 * @param array $menuarray
	 * @param bool $getIdOnly
	 * @return mixed
	 */
	private function getMenuParent(object $menuitem, array $menuarray, $getIdOnly = false)
	{

		if (!empty($menuitem->parent_id)) {

			$parent = Menuitem::where('id', $menuitem->parent_id)->first();

			if (!empty($parent)) {

				if ($getIdOnly) {
					$menuarray[] = $parent->id;
				} else {
					$menuarray[] = $parent;
				}

				// recursive
				if (!empty($parent->parent_id)) {
					$menuarray = $this->getMenuParent($parent, $menuarray, $getIdOnly);
				}

			}

		}

		return $menuarray;

	}

	/**
	 * Get all the routes from the main menu,
	 * and pass these routes to all the views,
	 * so we can access the routes from blade views.
	 *
	 * Examples for a read-more button (blade):
	 * {{ route($data->eroutes['page']['about']) }}
	 * {{ route($data->eroutes['entity']['blog']) }}
	 *
	 * @param string $language
	 * @return mixed
	 */
	private function getMenuEntityRoutes(string $language)
	{

		$cache_key = 'front_menu_entity_routes';

		$routes = Cache::rememberForever($cache_key, function () use ($language) {

			$mainMenuID = $this->getFrontMainMenuId();

			$menu_array = array();

			// entities
			$entitymenu = Menuitem::langIs($language)
				->menuIs($mainMenuID)
				->get();

			foreach ($entitymenu as $item) {
				if ($item->type == 'entity') {
					$menu_array['entity'][$item->entity->entity_key] = $item->routename;
				}
				if ($item->type == 'page') {
					$menu_array['page'][$item->slug] = 'entity.page.show.' . $item->object_id;
				}
				if ($item->type == 'form') {
					$menu_array['form'][$item->slug] = $item->routename;
				}
			}

			return $menu_array;

		});

		return $routes;

	}

	private function getPageChildren($language)
	{

		$collection = collect();

		$activeMenu = $this->getActiveMenuArray(true);

		if ($activeMenu) {

			$activeMenuId = $activeMenu[0];
			$menu = Menu::where('slug', 'main')->first();

			if ($menu) {

				$activeMenuObject = Menuitem::langIs($language)
					->menuIs($menu->id)
					->where('id', $activeMenuId)
					->first();

				if ($activeMenuObject) {

					$depth = $activeMenuObject->depth + 1;

					$submenu = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $this->language])
						->defaultOrder()
						->withDepth()
						->having('depth', '=', $depth)
						->where('publish', 1)
						->descendantsOf($activeMenuObject->id)
						->toArray();

					foreach ($submenu as $menuitem) {
						if ($menuitem['type'] == 'page') {
							$pageid = $menuitem['object_id'];
							$page = Page::find($pageid);
							if ($page) {
								// add page to collection
								$collection->push($page);

							}
						}
					}
				}
			}

			return $collection;

		} else {
			return null;
		}

	}

}
