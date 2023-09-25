<?php

namespace Lara\Front\Http\Traits;

use Google\Cloud\Translate\TranslateClient;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Routing\Redirector;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lara\Common\Models\Entity;
use Lara\Common\Models\Page;
use Lara\Common\Models\Language;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Setting;
use Lara\Common\Models\Larawidget;
use Lara\Common\Models\Taxonomy;
use Lara\Common\Models\User;
use Lara\Common\Models\Related;
use Lara\Common\Models\Blacklist;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

use Carbon\Carbon;

// use Theme;
use Qirolab\Theme\Theme;

use Cache;

trait LaraFrontHelpers
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

	private function getFrontTheme()
	{

		if (config('lara.client_five_theme')) {
			// Bootstrap 5 theme
			$theme = config('lara.client_five_theme');
		} elseif (config('lara.client_theme')) {
			// Bootstrap 3 theme
			$theme = config('lara.client_theme');
		} else {
			// Fallback
			$theme = 'demo';
		}

		return $theme;

	}

	function getParentTheme()
	{
		return config('theme.parent');
	}

	/**
	 * Cleanup Search String
	 *
	 * @param string $str
	 * @return array|false|string[]
	 */
	private function cleanupFrontSearchString(string $str)
	{

		$keywords = preg_split('/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY);

		return $keywords;

	}

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
		$routename = $this->getRouteFromUrl($slug);

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

		$routename = $this->getRouteFromUrl($slug);

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
	 * Build the collection for the index method
	 *
	 * Options:
	 * - sort by tag
	 * - filter by tags
	 * - eager loading of user, media, files
	 * - paginate or not
	 * - sorting
	 *
	 * @param string $language
	 * @param object $entity
	 * @param Request $request
	 * @param object|null $menutaxonomy
	 * @return object|null
	 */
	private function getFrontObjects(string $language, object $entity, Request $request, $menutaxonomy = null, $params = null, $override = null)
	{

		$method = $entity->getMethod();

		$view = $entity->getViews()->where('method', $method)->first();

		$isgrid = substr($view->type, 0, 4) == 'grid';

		if ($view->showtags == '_sortbytaxonomy') {

			// get active tag, if any
			if ($params->filterbytaxonomy) {
				$taxonomy = Taxonomy::where('slug', $params->taxonomy)->first();
				$activetag = Tag::where('taxonomy_id', $taxonomy->id)->where('slug', $params->filterbytaxonomy)->first();
			} else {
				if($menutaxonomy) {
					$activetag = $menutaxonomy;
				} else {
					$activetag = null;
				}
			}

			// show objects ordered by tags (disable pagination)
			// kalnoy/nestedset
			$tags = $this->getAllTags($language, $entity, $activetag);

			foreach ($tags as $taxonomy => $terms) {
				if (!empty($terms)) {
					foreach ($terms as $node) {
						$this->getTagObjects($node, $entity, $language, $isgrid, $menutaxonomy);
					}
				}
			}

			return $tags;

		} else {

			// start collection
			$modelClass = $entity->getEntityModelClass();
			$collection = new $modelClass;

			if ($entity->hasLanguage()) {
				$collection = $collection->langIs($language);
			}

			if ($entity->hasStatus()) {
				$collection = $collection->isPublished();
			}

			if ($entity->hasHideinlist()) {
				$collection = $collection->where('publish_hide', 0);
			}

			if ($entity->hasExpiration()) {
				$collection = $collection->isNotExpired();
			}

			if ($isgrid && $entity->getEntityKey() != 'video' && !$entity->hasVideos() && !$entity->hasVideoFiles()) {
				$collection = $collection->has('media');
			}

			if (method_exists($entity->getEntityModelClass(), 'scopeFront')) {
				$collection = $collection->front();
			}

			if ($view->showtags == 'filterbytaxonomy') {
				if (!empty($entity->getActiveTags())) {

					$activeTags = $entity->getActiveTags();
					$term = end($activeTags);
					$excludeEntityTags = config('lara-eve.use_tags_for_sorting.' . $entity->getEntityKey());
					$excludeTags = (!empty($excludeEntityTags)) ? $excludeEntityTags : [];
					if (!in_array($term, $excludeTags)) {
						$collection = $collection->whereHas('tags', function ($query) use ($term) {
							$query->where(config('lara-common.database.object.tags') . '.slug', $term);
						});
					}

				}

				if (!empty($params->xtratags)) {
					foreach ($params->xtratags as $xtag) {
						$collection = $collection->whereHas('tags', function ($query) use ($xtag) {
							$query->where(config('lara-common.database.object.tags') . '.slug', $xtag['slug']);
						});
					}
				}

			} else {

				if ($menutaxonomy) {
					$collection = $collection->whereHas('tags', function ($query) use ($menutaxonomy) {
						$query->where(config('lara-common.database.object.tags') . '.id', $menutaxonomy->id);
					});
				}

			}

			// eager loading: user
			if ($entity->getShowAuthor()) {
				$collection = $collection->with('user');
			}

			// eager loading: media
			if ($entity->hasImages()) {
				$collection = $collection->with('media');
			}

			// eager loading: files
			if ($entity->hasFiles()) {
				$collection = $collection->with('files');
			}

			// OrderBy
			if (isset($override) && isset($override->sortfield) && isset($override->sortorder)) {

				$collection = $collection->orderBy($override->sortfield, $override->sortorder);

				if (isset($override->sortfield2nd) && isset($override->sortorder2nd)) {
					$collection = $collection->orderBy($override->sortfield2nd, $override->sortorder2nd);
				}

			} else {

				foreach ($entity->getCustomColumns() as $field) {
					if ($field->fieldname == 'sticky') {
						$collection = $collection->orderBy('sticky', 'desc');
					}
				}
				if ($entity->getSortField()) {
					$collection = $collection->orderBy($entity->getSortField(), $entity->getSortOrder());
				}
				if ($entity->getSortField2nd()) {
					$collection = $collection->orderBy($entity->getSortField2nd(), $entity->getSortOrder2nd());
				}

			}

			if (isset($override) && isset($override->paginate)) {

				// custom pagination
				if ($override->paginate > 0) {
					$objects = $collection->paginate($override->paginate);
				} else {
					if (isset($override) && isset($override->limit)) {
						// custom limit
						$objects = $collection->limit($override->limit)->get();
					} else {
						$objects = $collection->get();
					}
				}

			} else {

				// entity view pagination
				if ($view->paginate > 0) {
					$objects = $collection->paginate($view->paginate);
				} else {
					if (isset($override) && isset($override->limit)) {
						$objects = $collection->limit($override->limit)->get();
					} else {
						$objects = $collection->get();
					}
				}
			}

			// prepare route variables (master > detail)
			foreach ($objects as $obj) {
				if ($menutaxonomy) {
					$obj->routeVars = ['slug' => $obj->slug, $menutaxonomy->taxonomy->slug => $menutaxonomy->slug];
				} else {
					$obj->routeVars = ['slug' => $obj->slug];
				}
			}

			return $objects;

		}

	}

	/**
	 * Build the collection for a JSON feed
	 *
	 * @param string $language
	 * @param object $entity
	 * @param Request $request
	 * @param int|null $limit
	 * @return object
	 */
	private function getFeedObjects(string $language, object $entity, Request $request, $limit = null)
	{

		// start collection
		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($entity->hasLanguage()) {
			$collection = $collection->langIs($language);
		}

		if ($entity->hasStatus()) {
			$collection = $collection->isPublished();
		}

		if (method_exists($entity->getEntityModelClass(), 'scopeFront')) {
			$collection = $collection->front();
		}

		if ($entity->getActiveTags()) {

			$activeTags = $entity->getActiveTags();
			$term = end($activeTags);

			$collection = $collection->whereHas('tags', function ($query) use ($term) {
				$query->where(config('lara-common.database.object.tags') . '.slug', $term);
			});

		}

		// eager loading: user
		if ($entity->getShowAuthor()) {
			$collection = $collection->with('user');
		}

		// OrderBy
		foreach ($entity->getCustomColumns() as $field) {
			if ($field->fieldname == 'sticky') {
				$collection = $collection->orderBy('sticky', 'desc');
			}
		}
		if ($entity->getSortField()) {
			$collection = $collection->orderBy($entity->getSortField(), $entity->getSortOrder());
		}
		if ($entity->getSortField2nd()) {
			$collection = $collection->orderBy($entity->getSortField2nd(), $entity->getSortOrder2nd());
		}

		if ($limit && is_numeric($limit)) {
			$collection = $collection->limit($limit);
		}

		return $collection->get();

	}

	/**
	 * Get all objects for one specific tag
	 * The objects are added to the tag tree (nested set)
	 *
	 * @param object $node
	 * @param object $entity
	 * @param string $language
	 * @param bool $isgrid
	 * @return int
	 */
	private function getTagObjects(object $node, object $entity, string $language, bool $isgrid, $menutaxonomy = null)
	{

		$totalcount = 0;

		// get objects for this tag (skip root tag)
		if ($node->depth > 0) {

			$termId = $node->id;

			// start collection
			$collection = $entity->getEntityModelClass()::langIs($language)
				->isPublished()
				->when(($isgrid && $entity->getEntityKey() != 'video'), function ($query) {
					return $query->has('media');
				});

			$collection = $collection->whereHas('tags', function ($query) use ($termId) {
				$query->where(config('lara-common.database.object.tags') . '.id', $termId);
			});

			if ($entity->hasHideinlist()) {
				$collection = $collection->where('publish_hide', 0);
			}

			if (method_exists($entity->getEntityModelClass(), 'scopeFront')) {
				$collection = $collection->front();
			}

			// eager loading: user
			if ($entity->getShowAuthor()) {
				$collection = $collection->with('user');
			}

			// eager loading: media
			if ($entity->hasImages()) {
				$collection = $collection->with('media');
			}

			// eager loading: files
			if ($entity->hasFiles() == 1) {
				$collection = $collection->with('files');
			}

			// OrderBy
			if ($entity->getSortField()) {
				$collection = $collection->orderBy($entity->getSortField(), $entity->getSortOrder());
			}
			if ($entity->getSortField2nd()) {
				$collection = $collection->orderBy($entity->getSortField2nd(), $entity->getSortOrder2nd());
			}

			// get the collection
			$objects = $collection->get();

			// add object collection to node
			$node->objects = $objects;

			// add objectcount to node
			$node->objectcount = $node->objects->count();

			// prepare route variables (master > detail)
			foreach ($node->objects as $obj) {
				if ($menutaxonomy) {
					$obj->routeVars = ['slug' => $obj->slug, $menutaxonomy->taxonomy->slug => $menutaxonomy->slug];
				} else {
					$obj->routeVars = ['slug' => $obj->slug];
				}
			}

			// add objectcount from this tag to totalcount
			$totalcount = $node->objectcount;

		}

		if (!$node->isLeaf()) {
			foreach ($node->children as $child) {
				$childobjectcount = $this->getTagObjects($child, $entity, $language, $isgrid);

				// add child count to total count
				$totalcount = $totalcount + $childobjectcount;

			}
		}

		// add totalcount to node
		$node->childobjectcount = $totalcount;

		return $totalcount;
	}

	/**
	 * Get the next object in a master detail structure (index/show)
	 *
	 * @param string $language
	 * @param object $entity
	 * @param object $object
	 * @param object $params
	 * @param object|null $menutaxonomy
	 * @return mixed
	 */
	private function getNextObject(string $language, object $entity, object $object, object $params, $menutaxonomy = null, $override = null)
	{

		$view = $entity->getViews()->where('method', 'index')->first();
		$isgrid = substr($view->type, 0, 4) == 'grid';

		if (isset($override) && isset($override->sortfield) && isset($override->sortorder)) {
			$sortField = $override->sortfield;
			$sortOrder = $override->sortorder;
			$objectSortValue = $object->$sortField;
		} else {
			$sortField = $entity->getSortField();
			$sortOrder = $entity->getSortOrder();
			$objectSortValue = $object->$sortField;
		}

		if (empty($objectSortValue)) {
			return null;
		}

		if (isset($override) && isset($override->sortfield2nd) && isset($override->sortordernd)) {
			$sortField2nd = $override->sortfield2nd;
			$sortOrder2nd = $override->sortordernd;
			$objectSortValue2nd = $object->$sortField2nd;
		} else {
			if ($entity->getSortField2nd()) {
				$sortField2nd = $entity->getSortField2nd();
				$sortOrder2nd = $entity->getSortOrder2nd();
				$objectSortValue2nd = $object->$sortField2nd;
			} else {
				$sortField2nd = null;
				$sortOrder2nd = null;
				$objectSortValue2nd = null;
			}

		}

		if ($sortField2nd) {
			// first
			if ($sortOrder == 'asc') {
				$operator = '>=';
			} else {
				$operator = '<=';
			}
			// second
			if ($sortOrder2nd == 'asc') {
				$operator2nd = '>';
			} else {
				$operator2nd = '<';
			}
		} else {
			if ($sortOrder == 'asc') {
				$operator = '>';
			} else {
				$operator = '<';
			}
			$operator2nd = null;
		}

		// start collection
		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($entity->hasLanguage()) {
			$collection = $collection->langIs($language);
		}

		if ($entity->hasStatus()) {
			$collection = $collection->isPublished();
		}

		if ($isgrid && !$entity->hasVideos() && !$entity->hasVideoFiles()) {
			$collection = $collection->has('media');
		}

		if ($menutaxonomy) {
			$collection = $collection->whereHas('tags', function ($query) use ($menutaxonomy) {
				$query->where(config('lara-common.database.object.tags') . '.id', $menutaxonomy->id);
			});
		} else {
			if (!empty($entity->getActiveTags())) {
				$activeTags = $entity->getActiveTags();
				$term = end($activeTags);
				$currentTag = Tag::where('entity_key', $entity->getEntityKey())->where('slug', $term)->first();
				if ($currentTag && $currentTag->publish == 1) {
					$collection = $collection->whereHas('tags', function ($query) use ($term) {
						$query->where(config('lara-common.database.object.tags') . '.slug', $term);
					});
				}
			}
			if (!empty($params->xtratags)) {
				foreach ($params->xtratags as $xtag) {
					$collection = $collection->whereHas('tags', function ($query) use ($xtag) {
						$query->where(config('lara-common.database.object.tags') . '.slug', $xtag['slug']);
					});
				}
			}
		}

		// split collection
		$collection = $collection->where($sortField, $operator, $objectSortValue);
		if ($sortField2nd) {
			$collection = $collection->where($sortField2nd, $operator2nd, $objectSortValue2nd);
		}

		// order by first
		$collection = $collection->orderBy($sortField, $sortOrder);

		// order by second
		if ($sortField2nd) {
			$collection = $collection->orderBy($sortField2nd, $sortOrder2nd);
		}

		return $collection->first();

	}

	/**
	 * Get the previous object in a master detail structure (index/show)
	 *
	 * @param string $language
	 * @param object $entity
	 * @param object $object
	 * @param object $params
	 * @param object|null $menutaxonomy
	 * @return mixed
	 */
	private function getPrevObject(string $language, object $entity, object $object, object $params, $menutaxonomy = null, $override = null)
	{

		$view = $entity->getViews()->where('method', 'index')->first();
		$isgrid = substr($view->type, 0, 4) == 'grid';

		if (isset($override) && isset($override->sortfield) && isset($override->sortorder)) {
			$sortField = $override->sortfield;
			$sortOrder = $override->sortorder;
			$objectSortValue = $object->$sortField;
		} else {
			$sortField = $entity->getSortField();
			$sortOrder = $entity->getSortOrder();
			$objectSortValue = $object->$sortField;
		}

		if (empty($objectSortValue)) {
			return null;
		}

		if (isset($override) && isset($override->sortfield2nd) && isset($override->sortorder2nd)) {
			$sortField2nd = $override->sortfield2nd;
			$sortOrder2nd = $override->sortorder2nd;
			$objectSortValue2nd = $object->$sortField2nd;
		} else {
			if ($entity->getSortField2nd()) {
				$sortField2nd = $entity->getSortField2nd();
				$sortOrder2nd = $entity->getSortOrder2nd();
				$objectSortValue2nd = $object->$sortField2nd;
			} else {
				$sortField2nd = null;
				$sortOrder2nd = null;
				$objectSortValue2nd = null;
			}
		}

		if ($sortField2nd) {
			// first
			if ($sortOrder == 'asc') {
				$reverseSortOrder = 'desc';
				$reverseOperator = '<=';
			} else {
				$reverseSortOrder = 'asc';
				$reverseOperator = '>=';
			}
			// second
			if ($sortOrder2nd == 'asc') {
				$reverseSortOrder2nd = 'desc';
				$reverseOperator2nd = '<';
			} else {
				$reverseSortOrder2nd = 'asc';
				$reverseOperator2nd = '>';
			}
		} else {
			if ($sortOrder == 'asc') {
				$reverseSortOrder = 'desc';
				$reverseOperator = '<';
			} else {
				$reverseSortOrder = 'asc';
				$reverseOperator = '>';
			}
			$reverseSortOrder2nd = null;
			$reverseOperator2nd = null;
		}

		// start collection
		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($entity->hasLanguage()) {
			$collection = $collection->langIs($language);
		}

		if ($entity->hasStatus()) {
			$collection = $collection->isPublished();
		}

		if ($isgrid && !$entity->hasVideos() && !$entity->hasVideoFiles()) {
			$collection = $collection->has('media');
		}

		if ($menutaxonomy) {
			$collection = $collection->whereHas('tags', function ($query) use ($menutaxonomy) {
				$query->where(config('lara-common.database.object.tags') . '.id', $menutaxonomy->id);
			});
		} else {
			if (!empty($entity->getActiveTags())) {
				$activeTags = $entity->getActiveTags();
				$term = end($activeTags);
				$currentTag = Tag::where('entity_key', $entity->getEntityKey())->where('slug', $term)->first();
				if ($currentTag && $currentTag->publish == 1) {
					$collection = $collection->whereHas('tags', function ($query) use ($term) {
						$query->where(config('lara-common.database.object.tags') . '.slug', $term);
					});
				}

			}
			if (!empty($params->xtratags)) {
				foreach ($params->xtratags as $xtag) {
					$collection = $collection->whereHas('tags', function ($query) use ($xtag) {
						$query->where(config('lara-common.database.object.tags') . '.slug', $xtag['slug']);
					});
				}
			}
		}

		// split collection
		$collection = $collection->where($sortField, $reverseOperator, $objectSortValue);
		if ($sortField2nd) {
			$collection = $collection->where($sortField2nd, $reverseOperator2nd, $objectSortValue2nd);
		}

		// order by first
		$collection = $collection->orderBy($sortField, $reverseSortOrder);

		// order by second
		if ($sortField2nd) {
			$collection = $collection->orderBy($sortField2nd, $reverseSortOrder2nd);
		}

		return $collection->first();

	}

	/**
	 * Get related objects from other entities
	 *
	 * @param string $entity_key
	 * @param int $id
	 * @return mixed
	 */
	private function getFrontRelated(string $entity_key, int $id)
	{

		$relatedItems = Related::where('entity_key', $entity_key)
			->where('object_id', $id)
			->get();

		$related = array();

		foreach ($relatedItems as $rel) {

			$item = app()->make('stdClass');

			$item->entity_key = $rel->related_entity_key;
			$item->object_id = $rel->related_object_id;

			// get related object
			$object = $rel->related_model_class::findOrFail($rel->related_object_id);
			$item->title = $object->title;
			$item->slug = $object->slug;

			// check if related Item is a Doc entity
			if ($rel->related_entity_key == 'doc') {
				$doc = \Eve\Models\Doc::find($rel->related_object_id);
				if ($doc && $doc->hasFiles()) {
					$item->target = '_blank';
					$laraDocClass = $this->getFrontEntityByKey('doc');
					$laraDocEntity = new $laraDocClass;
					$item->url = $laraDocEntity->getUrlForFiles() . $doc->files[0]->filename;
				} else {
					$item->url = $this->getFrontSeoUrl($rel->related_entity_key, 'show', 'index', $object);
					$item->target = '_self';
				}
			} elseif ($rel->related_entity_key == 'menuitem') {
				$item->url = route($object->routename);
				$item->target = '_self';
			} else {
				$item->url = $this->getFrontSeoUrl($rel->related_entity_key, 'show', 'index', $object);
				$item->target = '_self';
			}

			$related[] = $item;

		}

		return $related;

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
	 * Get the URL for a specific entity method
	 * For single detail pages (show) we also need the parent method (index)
	 *
	 * @param string $entity_key
	 * @param string $method
	 * @param string|null $parentmethod
	 * @param object|null $object
	 * @return string
	 */
	private function getFrontSeoUrl(string $entity_key, string $method, $parentmethod = null, $object = null)
	{

		if ($entity_key == 'page') {

			// page
			if (Route::has('entity.page.show.' . $object->id)) {
				$route = route('entity.page.show.' . $object->id);
			} else {
				$route = route('content.page.show', ['id' => $object->slug]);
			}

		} else {

			// entity

			if (!empty($object)) {

				// single (show)
				if (Route::has('entitytag.' . $entity_key . '.' . $parentmethod . '.' . $method)) {
					$route = route('entitytag.' . $entity_key . '.' . $parentmethod . '.' . $method,
						['slug' => $object->slug]);
				} elseif (Route::has('entity.' . $entity_key . '.' . $parentmethod . '.' . $method)) {
					$route = route('entity.' . $entity_key . '.' . $parentmethod . '.' . $method,
						['slug' => $object->slug]);
				} elseif (Route::has('contenttag.' . $entity_key . '.' . $parentmethod . '.' . $method)) {
					$route = route('contenttag.' . $entity_key . '.' . $parentmethod . '.' . $method,
						['slug' => $object->slug]);
				} else {
					$route = route('content.' . $entity_key . '.' . $parentmethod . '.' . $method,
						['slug' => $object->slug]);
				}

			} else {

				// list (index)
				if (Route::has('entitytag.' . $entity_key . '.' . $method)) {
					$route = route('entitytag.' . $entity_key . '.' . $method);
				} elseif (Route::has('entity.' . $entity_key . '.' . $method)) {
					$route = route('entity.' . $entity_key . '.' . $method);
				} elseif (Route::has('contenttag.' . $entity_key . '.' . $method)) {
					$route = route('contenttag.' . $entity_key . '.' . $method);
				} else {
					$route = route('content.' . $entity_key . '.' . $method);
				}

			}

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

				$menuitem = Menuitem::where('type', 'entity')
					->where('entity_id', $entity->id)
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

	/**
	 * Get all parameters for the index method
	 *
	 * We need to pass several parameters to the view:
	 *  - viewtype
	 *  - grid
	 *  - paginate
	 *  - (filter by) tag
	 *
	 * @param object $entity
	 * @param Request $request
	 * @return RedirectResponse|object
	 */
	private function getFrontParams(object $entity, Request $request)
	{

		$params = (object)array(
			'viewtype'         => '',
			'isgrid'           => false,
			'listtype'         => '',
			'vtype'            => '',
			'showtags'         => '',
			'tagsview'         => '',
			'gridcol'          => 0,
			'paginate'         => false,
			'infinite'         => false,
			'prevnext'         => false,
			'filter'           => false,
			'filterbytaxonomy' => '',
			'taxonomy'         => '',
			'isdefaultaxonomy' => false,
			'xtratags'         => [],
		);

		if ($entity->entity_key == 'search') {

			$params = (object)array(
				'viewtype'         => '',
				'isgrid'           => false,
				'listtype'         => 'list',
				'vtype'            => 'list',
				'showtags'         => 'none',
				'tagsview'         => 'default',
				'gridcols'         => 0,
				'gridcol'          => 0,
				'paginate'         => false,
				'infinite'         => false,
				'prevnext'         => false,
				'filter'           => false,
				'filterbytaxonomy' => '',
				'taxonomy'         => '',
				'isdefaultaxonomy' => false,
				'xtratags'         => [],
			);

			return $params;

		}

		$method = $entity->getMethod();

		if (empty($method)) {
			// default method
			if ($entity->entity_key == 'page') {
				$method = 'show';
			} else {
				$method = 'index';
			}
		}

		$view = $entity->getViews()->where('method', $method)->first();
		if (empty($view)) {
			// create default view
			$view = $this->makeNewObj();
			$view->method = 'show';
			$view->filename = 'show';
			$view->type = '_single';
			$view->showtags = 'none';
			$view->paginate = 0;
			$view->infinite = 0;
			$view->prevnext = 0;
		}

		$params->viewtype = $view->type;
		if (substr($params->viewtype, 0, 4) == 'grid') {
			$params->isgrid = true;
			$params->listtype = 'grid';
		} else {
			$params->isgrid = false;
			$params->listtype = 'list';
		}

		$params->showtags = $view->showtags;

		if ($view->showtags == '_sortbytaxonomy') {
			$params->tagsview = 'sort';
		} elseif ($view->showtags == 'filterbytaxonomy') {
			$params->tagsview = 'filter';
		} else {
			$params->tagsview = 'default';
		}

		if ($view->type == '_single') {

			$params->vtype = 'single';
			$params->gridcols = 0;
			$params->gridcol = 0;
			$params->prevnext = (bool)$view->prevnext;

		} else {

			$isgrid = substr($view->type, 0, 4) == 'grid';

			if ($isgrid) {
				$params->vtype = 'grid';

				$params->gridcols = substr($view->type, -1);
				$params->gridcol = (12 / $params->gridcols);
			} else {
				$params->vtype = 'list';
				$params->gridcols = 0;
				$params->gridcol = 0;
			}

		}

		if ($view->infinite == 1) {
			$params->infinite = true;
		} else {
			$params->infinite = false;
		}

		if ($view->showtags == 'none') {

			if ($view->paginate > 0) {
				$params->paginate = true;
			} else {
				$params->paginate = false;
			}

		} elseif ($view->showtags == '_sortbytaxonomy') {

			$params->paginate = false;

		} elseif ($view->showtags == 'filterbytaxonomy') {

			if ($view->paginate > 0) {
				$params->paginate = true;
			} else {
				$params->paginate = false;
			}

		}

		if ($view->showtags == 'filterbytaxonomy' || $view->showtags == '_sortbytaxonomy' || $view->type == '_single') {

			// primary tags (URL)
			if (!empty($entity->getActiveTags())) {
				$activeTags = $entity->getActiveTags();
				$term = end($activeTags);

				$tag = Tag::where('slug', $term)->first();
				$taxonomy = $tag->taxonomy;
				$params->taxonomy = $taxonomy->slug;
				if ($taxonomy) {
					if ($taxonomy->is_default == 1) {
						$params->isdefaultaxonomy = true;
					} else {
						$params->isdefaultaxonomy = false;
					}
				}

			}

			if (!empty($term)) {
				$params->filter = true;
				$params->filterbytaxonomy = $term;

			}

			// secondary tags (GET)
			$params->xtratags = array();

			$taxonomies = Taxonomy::where('is_default', 0)->get();
			foreach ($taxonomies as $taxonomy) {
				$taxonomySlug = $taxonomy->slug;
				$tagSlug = $this->getFrontRequestParam($request, $taxonomySlug, null, $entity->entity_key, true);

				if ($tagSlug) {

					if ($view->showtags == 'filterbytaxonomy') {
						// redirect if tag is not in GET variables
						if (!isset($_GET[$taxonomySlug])) {
							return redirect()->route(Route::currentRouteName(), [$taxonomySlug => $tagSlug])->send();
						}
					}

					// get Tag object
					$tag = Tag::where('slug', $tagSlug)->first();
					if ($tag) {
						$params->xtratags[$taxonomySlug] = [
							'slug'  => $tag->slug,
							'title' => $tag->title,
						];
					}

				} else {

					if ($view->showtags == 'filterbytaxonomy') {
						// redirect if empty tag is in GET variables
						if (isset($_GET[$taxonomySlug]) && $_GET[$taxonomySlug] == '') {
							return redirect()->route(Route::currentRouteName())->send();
						}
					}

				}

			}
		}

		return $params;

	}

	/**
	 * When a menu item, that has an entity index method,
	 * can be filtered immediately by tag
	 *
	 * @param string $language
	 * @param object $entity
	 * @param Request $request
	 * @return object|null
	 */
	private function getMenuTag(string $language, object $entity, Request $request)
	{

		if ($entity->getEgroup() == 'entity') {
			$activeMenuItem = $this->getActiveMenuItem($language);
			if ($activeMenuItem) {
				$tag_id = $activeMenuItem->tag_id;
				if ($tag_id) {
					$tag = Tag::find($tag_id);
				} else {
					$tag = null;
				}
			} else {
				$tag = null;
			}
		} else {
			$tag = null;
		}

		return $tag;

	}

	private function getSingleMenuTag(string $language, object $entity, Request $request)
	{

		if ($entity->getEgroup() == 'entity') {

			$defaultTaxonomy = $this->getFrontDefaultTaxonomy();
			$tax = $defaultTaxonomy->slug;

			if ($request->has($tax)) {

				$tagSlug = $request->get($tax);
				$tag = Tag::where('taxonomy_id', $defaultTaxonomy->id)->where('slug', $tagSlug)->first();
				if ($tag) {
					return $tag;
				} else {
					return null;
				}
			} else {
				return null;
			}

		} else {
			return null;
		}

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
	 * @param string|null $entity
	 * @param bool $reset
	 * @return mixed
	 */
	private function getFrontRequestParam(Request $request, string $param, $default = null, $entity = null, $reset = false)
	{

		if (!empty($entity)) {

			// store request param for specific entity only

			if ($request->has($param) || $reset == true) {

				$value = $request->get($param);

				if (session()->has($entity)) {

					$entitySession = session($entity);
					$entitySession[$param] = $value;
					session([$entity => $entitySession]);

				} else {

					session([
						$entity => [
							$param => $value,
						],
					]);

				}

			} else {

				if (session()->has($entity)) {
					$entitySession = session($entity);
					if (array_key_exists($param, $entitySession)) {
						if (!empty($entitySession[$param])) {
							$value = $entitySession[$param];
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

		} else {

			// store request param globally

			if ($request->has($param) || $reset == true) {
				$value = $request->get($param);
				session([$param => $value]);
			} else {
				if (session()->has($param)) {
					if (!empty(session($param))) {
						$value = session($param);
					} else {
						$value = $default;
					}
				} else {
					$value = $default;
				}
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
	 * Get Page Block for Email
	 *
	 * @param string $language
	 * @param string $entity_key
	 * @return mixed
	 */
	private function getEmailPageContent(string $language, string $entity_key)
	{

		$slug = $entity_key . '-email-' . $language;

		$object = Page::langIs($language)
			->groupIs('email')
			->where('slug', $slug)->first();

		if (empty($object)) {

			$title = ucfirst($entity_key) . ' Email Title';

			// get default backend user
			$user = User::where('username', 'admin')->first();

			$object = $this->createNewModulePage($user->id, $language, $title, 'email', $slug);

		}

		return $object;

	}

	/**
	 * Get the view file
	 *
	 * The view file is based on:
	 * - the entity key
	 * - the entity method
	 *
	 * @param object $entity
	 * @return string
	 */
	private function getFrontViewFile(object $entity)
	{

		$method = $entity->getMethod();

		$view = $entity->getViews()->where('method', $method)->first();

		$viewfile = $view->filename;

		$viewpath = 'content.' . $entity->getEntityKey() . '.' . $viewfile;

		$this->checkThemeViewFile($entity, $viewpath);

		return $viewpath;

	}

	/**
	 * @param object $entity
	 * @param string $viewpath
	 * @return void
	 */
	private function checkThemeViewFile(object $entity, string $viewpath)
	{

		if (config('app.env') != 'production') {

			$ds = DIRECTORY_SEPARATOR;

			$themeBasePath = config('theme.base_path');

			if (!view()->exists($viewpath)) {

				$defaultViewPath = config('theme.parent');
				$clientViewPath = config('theme.active');

				$srcDir = $themeBasePath . $ds . $defaultViewPath . $ds . 'views' . $ds . 'content' . $ds . '_templates' . $ds . $entity->getEgroup();
				$destDir = $themeBasePath . $ds . $clientViewPath . $ds . 'views' . $ds . $entity->getEntityKey() . $ds;
				$result = File::copyDirectory($srcDir, $destDir);
			}
		}

	}

	/**
	 * Get the default full layout of the theme
	 * from the special layout xml file
	 *
	 * The layout that is returned only contains
	 * the default options for the partials
	 *
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getDefaultThemeLayout()
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = $partial->key;

			foreach ($partial->items->item as $item) {
				if ($item->isDefault == 'true') {
					if ($item->partialFile == 'hidden') {
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
	 * Get the grid for this layout
	 *
	 * @param object $layout
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getGrid(object $layout)
	{

		$app = app();
		$grid = $app->make('stdClass');

		$bs = config('lara-front.lara_front_bootstrap_version');

		// default values
		$grid->module = 'module-sm';
		$grid->container = 'container';

		$grid->hasSidebar = 'has-no-sidebar';
		$grid->hasSidebarLeft = false;
		$grid->leftCols = 'hidden';
		$grid->hasSidebarRight = false;
		$grid->rightCols = 'hidden';

		$grid->contentCols = ($bs == 5) ? 'col-12' : 'col-sm-12';

		$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

		if (substr($layout->content, 0, 5) == 'boxed') {

			// boxed
			$grid->container = 'container';

			list($boxed, $sidebar, $type, $cols) = explode('_', $layout->content);

			$colcount = (int)$cols;

			if ($sidebar == 'default') {

				if ($type == 'col') {

					$gridcols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					if ($colcount < 12) {
						$offset = (12 - $colcount) / 2;
						$offsetcols = ($bs == 5) ? ' offset-lg-' . $offset : ' col-sm-offset-' . $offset;
					} else {
						$offsetcols = '';
					}

					$grid->gridColumns = $gridcols . $offsetcols;

				}

			} elseif ($sidebar == 'sidebar') {

				$grid->hasSidebar = 'has-sidebar';

				if ($type == 'left') {

					$grid->hasSidebarLeft = true;
					$grid->leftCols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					$contentcols = 12 - $colcount;
					$grid->contentCols = ($bs == 5) ? 'col-lg-' . $contentcols : 'col-sm-' . $contentcols;

					$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

				} elseif ($type == 'right') {

					$grid->hasSidebarRight = true;
					$grid->rightCols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					$contentcols = 12 - $colcount;
					$grid->contentCols = ($bs == 5) ? 'col-lg-' . (string)$contentcols : 'col-sm-' . (string)$contentcols;

					$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

				} elseif ($type == 'leftright') {
					// two sidebars

					$grid->hasSidebarLeft = true;
					$grid->leftCols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					$grid->hasSidebarRight = true;
					$grid->rightCols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					$contentcols = 12 - (2 * $colcount);
					$grid->contentCols = ($bs == 5) ? 'col-lg-' . (string)$contentcols : 'col-sm-' . (string)$contentcols;

					$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

				} else {
					//
				}

			} else {
				// default
				$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';
			}

		} elseif (substr($layout->content, 0, 4) == 'full') {

			// full width
			$grid->container = 'container-fluid';
			$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

		} else {

			// default
			$grid->container = 'container';
			$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';
		}

		return $grid;

	}

	private function getGridVars($entity)
	{

		$varsFile = null;

		foreach (Theme::getViewPaths() as $themePath) {
			$gridVarsFile = $themePath . '/_grid/vars.php';
			if (file_exists($gridVarsFile)) {
				$varsFile = $gridVarsFile;
			}
		}

		return $varsFile;

	}

	/**
	 * @param $entity
	 * @return string|null
	 */
	private function getGridOverride($entity)
	{

		$override = null;

		foreach (Theme::getViewPaths() as $themePath) {
			$entityPath = $themePath . '/content/' . $entity->getEntityKey();
			$gridFile = $entityPath . '/' . $entity->getMethod() . '/_grid/vars.php';
			if (file_exists($gridFile)) {
				$override = $gridFile;
				break;
			}
		}

		return $override;

	}

	/**
	 * Get the custom layout for the current object
	 * The custom layout can override the default layout settings
	 *
	 * @param object $object
	 * @param object|null $params
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getObjectThemeLayout(object $object, $params = null)
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = (string)$partial->key;

			$found = false;

			// get custom layout value from database
			foreach ($object->layout as $item) {

				if ($item->layout_key == $partial_key) {
					if ($item->layout_value == 'hidden') {
						$layout->$partial_key = false;
					} else {
						$layout->$partial_key = $item->layout_value;
					}

					$found = true;

				}
			}

			if (!$found) {
				// get default value from layout XML
				foreach ($partial->items->item as $item) {
					if ($item->isDefault == 'true') {
						if ((string)$item->partialFile == 'hidden') {
							$layout->$partial_key = false;
						} else {
							$layout->$partial_key = (string)$item->partialFile;

						}
					}
				}
			}

		}

		if (!empty($params) && $params->showtags == 'filterbytaxonomy') {
			// force left sidebar for tag menu
			$layout->content = 'boxed_sidebar_left_3';
		}

		return $layout;

	}

	/**
	 * Get SEO values for a specific object
	 *
	 * Fallback: default values
	 *
	 * @param object $object
	 * @param object|null $fallback
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getSeo(object $object, $fallback = null)
	{

		$app = app();
		$seo = $app->make('stdClass');

		// SEO Title
		if ($object->seo && !empty($object->seo->seo_title)) {
			$seo->seo_title = $object->seo->seo_title;
		} elseif ($fallback && $fallback->seo && !empty($fallback->seo->seo_title)) {
			$seo->seo_title = $fallback->seo->seo_title;
		} else {
			$seo->seo_title = $object->title;
		}

		// SEO Description
		if ($object->seo && !empty($object->seo->seo_description)) {
			$seo->seo_description = $object->seo->seo_description;
		} elseif ($fallback && $fallback->seo && !empty($fallback->seo->seo_description)) {
			$seo->seo_description = $fallback->seo->seo_description;
		} else {
			$seo->seo_description = $this->getDefaultSeoByKey($object->language, 'seo_description');
		}

		// SEO Keywords
		if ($object->seo && !empty($object->seo->seo_keywords)) {
			$seo->seo_keywords = $object->seo->seo_keywords;
		} elseif ($fallback && $fallback->seo && !empty($fallback->seo->seo_keywords)) {
			$seo->seo_keywords = $fallback->seo->seo_keywords;
		} else {
			$seo->seo_keywords = $this->getDefaultSeoByKey($object->language, 'seo_keywords');
		}

		return $seo;

	}

	/**
	 * Get the default SEO value for a specific key
	 *
	 * The default SEO values are set  on the home page
	 *
	 * @param string $language
	 * @param string $key
	 * @return string|null
	 */
	private function getDefaultSeoByKey(string $language, string $key)
	{

		$object = $this->getHomePage($language);

		if ($object && isset($object->seo)) {
			$value = $object->seo->$key;
		} else {
			$value = null;
		}

		return $value;

	}

	/**
	 * Get all the default SEO values
	 *
	 * The default SEO values are set on the home page
	 *
	 * @param string $language
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getDefaultSeo(string $language)
	{

		$object = $this->getHomePage($language);

		$app = app();
		$seo = $app->make('stdClass');

		if (!empty($object)) {

			if ($object->seo) {
				$seo->seo_title = $object->seo->seo_title;
				$seo->seo_description = $object->seo->seo_description;
				$seo->seo_keywords = $object->seo->seo_keywords;
			} else {
				$seo->seo_title = null;
				$seo->seo_description = null;
				$seo->seo_keywords = null;
			}

		} else {

			$seo->seo_title = null;
			$seo->seo_description = null;
			$seo->seo_keywords = null;

		}

		return $seo;

	}

	/**
	 * Get all the Opengraph data
	 *
	 * @param object $object
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getOpengraph(object $object)
	{

		// get settings
		$settings = Setting::pluck('value', 'key')->toArray();
		$settngz = json_decode(json_encode($settings), false);

		$app = app();
		$og = $app->make('stdClass');

		// Title
		if ($object->opengraph && !empty($object->opengraph->og_title)) {
			$og->og_title = $object->opengraph->og_title;
		} else {
			$og->og_title = $object->title;
		}

		// Description
		if (isset($settngz->og_descr_max)) {
			$og->og_descr_max = $settngz->og_descr_max;
		}

		if ($object->opengraph && !empty($object->opengraph->og_description)) {
			$og->og_description = $object->opengraph->og_description;
		} else {
			if ($object->lead != '') {
				$og->og_description = str_limit(strip_tags($object->lead), $og->og_descr_max, '');
			} elseif ($object->body != '') {
				$og->og_description = str_limit(strip_tags($object->body), $og->og_descr_max, '');
			} else {
				$og->og_description = '';
			}
		}

		// Image
		if ($object->media->count()) {

			if ($object->opengraph && !empty($object->opengraph->og_image)) {
				$og->og_image = $object->opengraph->og_image;
			} else {
				// use featured image
				$og->og_image = $object->featured->filename;
			}

			if (isset($settngz->og_image_width)) {
				$og->og_image_width = $settngz->og_image_width;
			} else {
				$og->og_image_width = 1200; // Facebook recommended width
			}
			if (isset($settngz->og_image_height)) {
				$og->og_image_height = $settngz->og_image_height;
			} else {
				$og->og_image_height = 630; // Facebook recommended height
			}
		} else {
			$og->og_image = null;
		}

		// Type
		if (isset($settngz->og_type)) {
			$og->og_type = $settngz->og_type;
		} else {
			$og->og_type = null;
		}

		// Site name
		if (isset($settngz->og_site_name)) {
			$og->og_site_name = $settngz->og_site_name;
		} else {
			$og->og_site_name = null;
		}

		return $og;

	}

	/**
	 * Find special Module Page by their Slug
	 *
	 * Most content entities, other that Pages, are often displayed as lists,
	 * either with or without a master/detail structure.
	 * Well known examples are blogs, team pages, events, etc.
	 *
	 * When a specific index method (!Page) is attached to a frontend menu item,
	 * we automatically attach a special kind of page (called a 'module page') to this menu item.
	 * A 'module page' is technically a Page object with a group value of 'module'.
	 *
	 * This so-called 'module page' can be seen as a 'container' in which the list is displayed.
	 * Think of it as a Wordpress page, with a shortcode to a special plugin in it.
	 *
	 * This module page gives us the following advantages:
	 * - we can add a custom intro (title, text, images, hooks) to the list
	 * - we can assign custom layout to the module page
	 * - we can add seo to the module page
	 *
	 * Because module page are fetched by their unique slugs ('team-index-module-[lang]'),
	 * the slugs are always locked, and cannot be modified by webmasters.
	 *
	 * @param string $language
	 * @param object $entity
	 * @param string $method
	 * @return mixed
	 */
	private function getModulePageBySlug(string $language, object $entity, string $method)
	{

		$modulePageSlug = $entity->getEntityKey() . '-' . $method . '-module-' . $language;
		$modulePageTitle = ucfirst($entity->getEntityKey()) . ' ' . ucfirst($method) . ' Module Page';

		$modulePage = Page::langIs($language)->groupIs('module')->where('slug', $modulePageSlug)->first();

		if (empty($modulePage)) {

			$newModulePage = $this->createNewModulePage(Auth::user()->id, $language, $modulePageTitle, 'module', $modulePageSlug);

			return $newModulePage;

		} else {

			if (isset($modulePage->lead)) {
				$modulePage->lead = $this->replaceShortcodes($modulePage->lead);
			}
			if (isset($modulePage->body)) {
				$modulePage->body = $this->replaceShortcodes($modulePage->body);
			}

			return $modulePage;

		}

	}

	/**
	 * Create a specific module page
	 *
	 * @param int $user_id
	 * @param string $language
	 * @param string $title
	 * @param string $cgroup
	 * @param string $slug
	 * @return mixed
	 */
	private function createNewModulePage(int $user_id, string $language, string $title, string $cgroup, string $slug)
	{

		$entity = Entity::where('entity_key', 'page')->first();
		$lara = $this->getFrontEntityByKey($entity->entity_key);
		$pageEntity = new $lara;

		$data = [
			'title'     => $title,
			'menuroute' => '',
		];

		if ($pageEntity->hasUser()) {
			$data = array_merge($data, ['user_id' => $user_id]);
		}
		if ($pageEntity->hasLanguage()) {
			$data = array_merge($data, ['language' => $language]);
		}
		if ($pageEntity->hasSlug()) {
			$data = array_merge($data, ['slug' => $slug, 'slug_lock' => 1]);
		}
		if ($pageEntity->hasBody()) {
			$data = array_merge($data, ['body' => '']);
		}
		if ($pageEntity->hasLead()) {
			$data = array_merge($data, ['lead' => '']);
		}
		if ($pageEntity->hasGroups()) {
			$data = array_merge($data, ['cgroup' => $cgroup]);
		}
		if ($pageEntity->hasStatus()) {
			$data = array_merge($data, ['publish' => 1, 'publish_from' => Carbon::now()]);
		}

		$newModulePage = Page::create($data);

		return $newModulePage;
	}

	/**
	 * @param string $language
	 * @param object $entity
	 * @param $activetag
	 * @return array|mixed
	 * @throws BindingResolutionException
	 */
	private function getAllTags(string $language, object $entity, $activetag = false)
	{

		$tags = $this->makeNewObj();

		if ($activetag) {
			$taxonomies = Taxonomy::where('id', $activetag->taxonomy_id)->get();
		} else {
			$taxonomies = Taxonomy::get();
		}

		foreach ($taxonomies as $taxonomy) {

			$key = $taxonomy->slug;

			if ($entity->hasTags()) {

				$root = Tag::langIs($language)
					->entityIs($entity->getEntityKey())
					->taxonomyIs($taxonomy->id)
					->whereNull('parent_id')
					->first();

				if (empty($root)) {

					Tag::create([
						'language'    => $language,
						'entity_key'  => $entity->getEntityKey(),
						'taxonomy_id' => $taxonomy->id,
						'title'       => 'root',
						'slug'        => null,
						'body'        => '',
						'lead'        => '',
					]);
				}

				if ($activetag) {

					// get subtree
					$tags->$key = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomy->id])
						->defaultOrder()
						->descendantsOf($activetag->id)
						->toTree();

				} else {
					// get full tree
					$tags->$key = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomy->id])
						->defaultOrder()
						->get()
						->toTree();
				}
			}
		}

		return $tags;

	}

	/**
	 * Get all the tags for a specific entity
	 * Return it as a nested set (tree), or an array
	 *
	 * @param string $language
	 * @param object $entity
	 * @param string $type
	 * @param string $taxonomy
	 * @return object|null
	 */
	private function getTags(string $language, object $entity, string $type = 'tree', string $taxonomy = null, bool $withCount = true)
	{

		$tags = null;

		// get Taxonomy ID
		$taxonomyId = $this->getFrontTaxonomyIdbySlug($taxonomy);

		if ($taxonomyId) {

			if ($entity->hasTags()) {

				$root = Tag::langIs($language)
					->entityIs($entity->getEntityKey())
					->whereNull('parent_id')
					->first();

				if ($root) {

					if ($type == 'array') {

						// kalnoy/nestedset
						$tags = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomyId])
							->defaultOrder()
							->get()
							->toArray();

					} elseif ($type == 'tree') {

						// kalnoy/nestedset
						$tags = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomyId])
							->defaultOrder()
							->get()
							->toTree();

					} else {

						$tags = null;

					}

				}

			}

		}

		if($withCount) {
			$tags = $this->getTreeCount($tags, $entity, $type);
		}

		return $tags;

	}

	/**
	 * @param mixed $tags
	 * @param object $entity
	 * @param string $type
	 */
	private function getTreeCount($tags, object $entity, string $type)
	{

		foreach ($tags as $node) {
			$this->getTagCount($node, $entity, $type);
		}

		return $tags;

	}

	/**
	 * @param mixed $node
	 * @param object $entity
	 * @param string $type
	 */
	private function getTagCount($node, object $entity, string $type)
	{

		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($type == 'array') {

			if ($entity->hasLanguage()) {
				$collection = $collection->langIs($node['language']);
			}

			if ($entity->hasStatus()) {
				$collection = $collection->isPublished();
			}

			$collection = $collection->whereHas('tags', function ($query) use ($node) {
				$query->where(config('lara-common.database.object.tags') . '.id', $node['id']);
			});

			// add object count to node
			$node['object_count'] = $collection->count();

		} else {

			// tree

			if ($entity->hasLanguage()) {
				$collection = $collection->langIs($node->language);
			}

			if ($entity->hasStatus()) {
				$collection = $collection->isPublished();
			}

			$collection = $collection->whereHas('tags', function ($query) use ($node) {
				$query->where(config('lara-common.database.object.tags') . '.id', $node->id);
			});

			// add object count to node
			$node->object_count = $collection->count();

			foreach ($node->children as $child) {
				$this->getTagCount($child, $entity, $type);
			}

		}

	}

	/**
	 * @param string|null $slug
	 * @return int|null
	 */
	private function getFrontTaxonomyIdbySlug(string $slug = null)
	{

		if ($slug) {
			$taxonomy = Taxonomy::where('slug', $slug)->first();
			if ($taxonomy) {
				return $taxonomy->id;
			} else {
				$defaultTaxonomy = $this->getFrontDefaultTaxonomy();

				return $defaultTaxonomy->id;
			}
		} else {
			$defaultTaxonomy = $this->getFrontDefaultTaxonomy();

			return $defaultTaxonomy->id;
		}

	}

	/**
	 * @return object|null
	 */
	private function getFrontDefaultTaxonomy()
	{

		$taxonomy = Taxonomy::where('is_default', 1)->first();
		if ($taxonomy) {
			return $taxonomy;
		} else {
			return null;
		}

	}

	/**
	 * Get all entity tags that are used by one or more objects
	 *
	 * @param string $language
	 * @param object $entity
	 * @param object $objects
	 * @return object|null
	 */
	private function getTagsFromCollection(string $language, object $entity, object $objects)
	{

		$cache_key = $entity->getEntityKey() . '_tags';

		$tags = Cache::remember($cache_key, 86400, function () use ($language, $entity, $objects) {

			// get used tags from collection
			$activeTags = array();
			foreach ($objects as $object) {
				foreach ($object->tags as $wtag) {
					$activeTags[$wtag->slug] = $wtag->title;
				}
			}

			// get all entity tags in correct order

			// kalnoy/nestedset
			$entityTags = $this->getTags($language, $entity, 'array');

			if (!empty($entityTags)) {

				$tags = array();

				// remove unused tags
				$i = 0;
				foreach ($entityTags as $tag) {
					if (array_key_exists($tag['slug'], $activeTags)) {
						$tags[$i]['title'] = $tag['title'];
						$tags[$i]['slug'] = $tag['slug'];
						$i++;
					}
				}

			} else {

				$tags = null;

			}

			return $tags;

		});

		// convert array to standard object
		$tags = json_decode(json_encode($tags), false);

		return $tags;

	}

	/**
	 * Get all Tag children of a Tag tree (nested set)
	 *
	 * Children are fetched recursively,
	 * so it includes grandchildren great-grandchildren, etc
	 *
	 * @param string $language
	 * @param object $entity
	 * @param string $term
	 * @return object|null
	 */
	private function getTagChildren(string $language, object $entity, string $term)
	{

		// kalnoy/nestedset
		$tag = $this->getTagBySlug($language, $entity, $term);

		$children = null;

		if ($tag) {
			$children = $tag->descendants()
				->defaultOrder()
				->get()
				->toTree();

		}

		return $children;

	}

	/**
	 * @param string $language
	 * @param object $entity
	 * @param string $slug
	 * @return object|null
	 */
	private function getTagBySlug(string $language, object $entity, string $slug)
	{

		if ($slug) {
			$tag = Tag::langIs($language)->entityIs($entity->getEntityKey())->where('slug', $slug)->first();
		} else {
			$tag = null;
		}

		return $tag;

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
	 * Replace the shortcodes in a content string
	 *
	 * @param string|null $str
	 * @return string|null
	 */
	private function replaceShortcodes(string $str = null)
	{
		if ($str) {

			$col_min = 2;
			$col_max = 4;

			for ($col = $col_min; $col <= $col_max; $col++) {

				$varfound = 'sc_col' . $col . '_found';
				$$varfound = false;

				for ($i = 1; $i <= $col; $i++) {

					$var_str = 'pos_str' . $i . $col;
					$var_end = 'pos_end' . $i . $col;
					$$var_str = strpos($str, '[kolom_' . $i . 'van' . $col . ']');
					$$var_end = strpos($str, '[/kolom_' . $i . 'van' . $col . ']');

					if ($$var_str !== false || $$var_end !== false) {
						// shortcode found
						$$varfound = true;
					}
				}

				if ($$varfound == true) {

					// first remove the <p> tags form the shortcode
					for ($i = 1; $i <= $col; $i++) {
						$str = str_replace('<p>[kolom_' . $i . 'van' . $col . ']</p>', '[kolom_' . $i . 'van' . $col . ']',
							$str);
						$str = str_replace('<p>[/kolom_' . $i . 'van' . $col . ']</p>',
							'[/kolom_' . $i . 'van' . $col . ']', $str);
					}

					// check if shortcode is complete

					$varcomplete = 'sc_col' . $col . '_complete';
					$$varcomplete = true;

					for ($i = 1; $i <= $col; $i++) {

						$var_str = 'pos_str' . $i . $col;
						$var_end = 'pos_end' . $i . $col;

						if ($$var_str === false || $$var_end === false) {
							// shortcode incomplete
							$$varcomplete = false;
						}
					}

					if ($$varcomplete = true) {

						// correct shortcode found, start replacing
						for ($i = 1; $i <= $col; $i++) {

							if ($i == 1) {
								$str = str_replace('[kolom_' . $i . 'van' . $col . ']',
									'<div class="row"><div class="col-sm-' . (12 / $col) . '">', $str);
							} else {
								$str = str_replace('[kolom_' . $i . 'van' . $col . ']',
									'<div class="col-sm-' . (12 / $col) . '">', $str);
							}

							if ($i < $col) {
								$str = str_replace('[/kolom_' . $i . 'van' . $col . ']', '</div>', $str);
							} else {
								$str = str_replace('[/kolom_' . $i . 'van' . $col . ']', '</div></div>', $str);
							}

						}

					} else {

						// incorrect shortcode, remove all shortcodes
						for ($i = 1; $i <= $col; $i++) {
							$str = str_replace('[kolom_' . $i . 'van' . $col . ']', '', $str);
							$str = str_replace('[/kolom_' . $i . 'van' . $col . ']', '', $str);
						}

					}

				}

			}

			return $str;

		} else {

			return null;

		}

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
								$version->route = url($lang->code . '/' . $languageRoute . '/' . $object->slug);
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
								$version->route = url($lang->code . '/' . $languageRoute . '/' . $sibling->slug);
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

	/**
	 * @param object $entity
	 * @return array
	 */
	private function getValidationRules(object $entity)
	{

		$requiredFields = array();

		foreach ($entity->getCustomColumns() as $field) {

			$fieldname = $field->fieldname;

			if ($field->fieldtype == 'email') {
				if ($field->required) {
					$requiredFields[$fieldname] = 'email:rfc,dns|required';
				} else {
					$requiredFields[$fieldname] = 'email:rfc,dns';
				}
			} else {
				if ($field->required) {
					$requiredFields[$fieldname] = 'required';
				}
			}
		}

		return $requiredFields;

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

	/**
	 * @param object $entity
	 * @param object $object
	 * @param array $fieldtypes
	 * @return object
	 */
	private function detectSpam(object $entity, object $object, array $fieldtypes)
	{

		$data = $this->makeNewObj();

		// patch 6.2.23 - start
		$isBlackListed = false;
		if (isset($object->ipaddress)) {
			$isBlackListed = $this->isBlacklisted($object->ipaddress);
		}

		if ($isBlackListed) {

			// ip address is blacklisted
			// no further checks necessary
			$data->result = true;
			$data->message = 'too many requests ...';

		} else {
			// patch 6.2.23 - end

			$spamScore = 0;

			// check for links
			$detectLinks = $this->detectLinkInString($entity, $object, $fieldtypes);
			if ($detectLinks) {
				$spamScore = $spamScore + config('lara.forms_anti_spam.spam_score_link');
			}

			// check for email addresses
			$detectEmails = $this->detectEmailInString($entity, $object, $fieldtypes);
			if ($detectEmails) {
				$spamScore = $spamScore + config('lara.forms_anti_spam.spam_score_email');
			}

			// detect language
			$matchLang = $this->matchLanguage($entity, $object);
			if (!$matchLang) {
				$spamScore = $spamScore + config('lara.forms_anti_spam.spam_score_language');
			}

			// check total score
			if ($spamScore >= config('lara.forms_anti_spam.threshold')) {
				$data->result = true;
				$data->message = 'spam detected';

				// patch 6.2.23 - start
				$this->addToBlacklist($object->ipaddress);
				// patch 6.2.23 - end

			} else {
				$data->result = false;
				$data->message = 'passed';
			}
		}

		return $data;

	}

	/*
	 * part of patch 6.2.23
	 */
	private function addToBlacklist($ipaddress)
	{

		return Blacklist::create(['ipaddress' => $ipaddress]);

	}

	/*
	 * part of patch 6.2.23
	 */
	private function isBlacklisted($ipaddress)
	{

		// check blacklist table
		$this->checkBlackListTable();

		$blackListCheck = Blacklist::where('ipaddress', $ipaddress)->first();

		if ($blackListCheck) {
			return true;
		} else {
			return false;
		}

	}

	/*
	 * part of patch 6.2.23
	 */
	private function checkBlackListTable()
	{

		// check blacklist table
		$tablename = 'lara_sys_blacklist';
		if (!Schema::hasTable($tablename)) {
			Schema::create($tablename, function (Blueprint $table) {
				$table->increments('id');
				$table->string('ipaddress')->nullable();
				$table->timestamps();
			});
		}

		return true;

	}

	/*
	 * part of patch 6.2.23
	 */
	private function checkBlackListColumn($entity)
	{

		$column = 'ipaddress';

		$modelClass = $entity->getEntityModelClass();
		$model = new $modelClass;
		$tablename = $model->getTable();

		if (!Schema::hasColumn($tablename, $column)) {
			Schema::table($tablename, function ($table) use ($column) {
				$table->string($column)->nullable();
			});
		}

	}

	/**
	 * @param object $entity
	 * @param object $object
	 * @param array $fieldtypes
	 * @return bool
	 */
	private function detectEmailInString(object $entity, object $object, array $fieldtypes): bool
	{
		// This regular expression extracts all emails from a string:
		$regexp = '/([a-z0-9_\.\-])+(\@|\[at\])+(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';

		$stringHasEmail = false;

		foreach ($entity->getCustomColumns() as $field) {
			if (in_array($field->fieldtype, $fieldtypes)) {
				$fieldname = $field->fieldname;
				$fieldval = $object->$fieldname;
				preg_match_all($regexp, $fieldval, $m);
				if (sizeof($m[0]) > 0) {
					$stringHasEmail = true;
					$object->$fieldname = '[SPAM] - ' . $fieldval;
					$object->save();
				}
			}
		}

		return $stringHasEmail;

	}

	/**
	 * @param object $entity
	 * @param object $object
	 * @param array $fieldtypes
	 * @return bool
	 */
	private function detectLinkInString(object $entity, object $object, array $fieldtypes): bool
	{

		$patterns = config('lara.detect_link_patterns');

		$stringHasLinks = false;

		foreach ($entity->getCustomColumns() as $field) {
			if (in_array($field->fieldtype, $fieldtypes)) {
				$fieldname = $field->fieldname;
				$fieldval = $object->$fieldname;
				foreach ($patterns as $pattern) {
					if (Str::contains($fieldval, $pattern)) {
						$stringHasLinks = true;
						$object->$fieldname = '[SPAM] - ' . $fieldval;
						$object->save();
					}
				}
			}
		}

		return $stringHasLinks;
	}

	/**
	 * @param object $entity
	 * @param $object
	 * @return bool
	 */
	private function matchLanguage(object $entity, $object): bool
	{

		$matchLang = true;

		if (config('lara.detect_language.enabled')) {

			if (config('lara.google_translate_api_key')) {

				$translate = new TranslateClient([
					'key' => config('lara.google_translate_api_key'),
				]);

				$allowedLanguages = config('lara.detect_language.languages_allowed');
				$detectFields = config('lara.detect_language.entity_fields');
				$wordThresholdMin = config('lara.detect_language.wordcount_threshold_min');
				$wordThresholdMax = config('lara.detect_language.wordcount_threshold_max');

				if (array_key_exists($entity->getEntityKey(), $detectFields)) {

					$entkey = $entity->getEntityKey();
					$detectEntityFields = $detectFields[$entkey];

					foreach ($entity->getCustomColumns() as $field) {
						if (in_array($field->fieldname, $detectEntityFields)) {
							$fieldname = $field->fieldname;
							$fieldval = $object->$fieldname;
							if (str_word_count($fieldval) > $wordThresholdMin) {
								if (str_word_count($fieldval) < $wordThresholdMax) {
									$result = $translate->detectLanguage($fieldval);
									if (!in_array($result['languageCode'], $allowedLanguages)) {
										// detected language is not allowed, mark as spam
										$matchLang = false;
									}
								} else {
									// too many words, mark as spam
									$matchLang = false;
								}

							}
						}
					}
				}
			}
		}

		return $matchLang;

	}

	private function getGuestUser()
	{
		$userId = Cache::remember('guest_user_id', 86400, function () {

			$user = User::where('username', 'guest')->first();

			if ($user) {
				return $user;
			} else {
				$newuser = User::create([
					'type'          => 'web',
					'is_admin'      => 0,
					'name'          => 'guest',
					'firstname'     => 'guest',
					'middlename'    => null,
					'lastname'      => 'guest',
					'username'      => 'guest',
					'email'         => 'guest@laracms.nl',
					'password'      => 'jyKTvaZAGXme!',
					'user_language' => 'nl',
				]);
				$newuser->assign('member');

				return $newuser;
			}

		});

		return $userId;
	}

	private function saveFrontUserProfile($request, $object)
	{

		$profileFields = $this->getFrontProfileFields('array');
		$pfields = array();
		foreach ($request->all() as $fieldkey => $fieldval) {
			if (substr($fieldkey, 0, 9) == '_profile_') {
				$fieldname = substr($fieldkey, 9);
				if (array_key_exists($fieldname, $profileFields)) {
					$pfields[$fieldname] = $request->input($fieldkey);
				}
			}
		}

		$object->profile()->update($pfields);

	}

	/**
	 * @param string $type
	 * @return mixed
	 */
	private function getFrontProfileFields($type = 'object')
	{

		$profileFields = config('lara-admin.userProfile');
		if ($type == 'array') {
			return $profileFields;
		} elseif ($type == 'object') {
			$profileFields = json_decode(json_encode($profileFields), false);

			return $profileFields;
		} else {
			return $profileFields;
		}

		return $profileFields;
	}

	/**
	 * @param $language
	 * @param $entity
	 * @param $object
	 * @param $menuTag
	 * @return string
	 */
	private function getEntityListUrl($language, $entity, $object, $menuTag): string
	{

		if ($menuTag) {
			$node = Menuitem::where('language', $language)
				->where('entity_id', $entity->id)
				->where('tag_id', $menuTag->id)
				->first();

			if ($node) {
				$url = url($language . '/' . $node->route);
			} else {
				$url = $this->getDefaultEntityListUrl($language, $entity);
			}
		} else {
			$url = $this->getDefaultEntityListUrl($language, $entity);
		}

		return $url;

		/*
		// get base url
		$baseUrl = config('app.url');
		$baseLenth = strlen($baseUrl);

		// check previous url
		$prevUrl = URL::previous();
		$prevBaseUrl = substr($prevUrl, 0, $baseLenth);

		if ($prevBaseUrl == $baseUrl) {

			// add 4 characters for the language prefix
			$baseLenthWithLanguage = $baseLenth + 4;

			// get the full route
			$previousRoute = substr($prevUrl, $baseLenthWithLanguage);

			// remove parameters
			list($prevRoute) = explode('?', $previousRoute);

			// check if the previous url was the same entity, maybe with a menu tag(!)
			$menuItem = Menuitem::where('route', $prevRoute)->where('entity_id', $entity->id)->first();

			if($menuItem) {
				return $prevUrl;
			} else {
				return $defaultUrl;
			}

		} else {
			return $defaultUrl;
		}
		*/

	}

	private function getDefaultEntityListUrl($language, $entity): string
	{
		$node = Menuitem::where('language', $language)
			->where('entity_id', $entity->id)
			->whereNull('tag_id')
			->first();

		if ($node) {
			$url = url($language . '/' . $node->route);
		} else {
			$url = route($entity->getPrefix() . '.' . $entity->getEntityKey() . '.index');
		}

		return $url;
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

}
