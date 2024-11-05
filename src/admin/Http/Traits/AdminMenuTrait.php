<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Redirect;
use Lara\Common\Models\Page;
use Lara\Common\Models\Entity;
use Lara\Common\Models\EntityView;
use Lara\Common\Models\Tag;

use Carbon\Carbon;

trait AdminMenuTrait
{

	/**
	 * kalnoy/nestedset
	 *
	 * Custom recursive function to replace the getRoot method that Baum used
	 *
	 * @param object $object
	 * @return mixed
	 */
	private function getNestedSetMenuRoot(object $object)
	{

		if (is_null($object->parent_id)) {
			return $object;
		} else {
			// get parent
			$parent = Menuitem::find($object->parent_id);
			$object = $this->getNestedSetMenuRoot($parent);

			return $object;
		}

	}

	/**
	 * Get the menu tree ('MenuItem') of the current language
	 * and the current menu group ('Menu')
	 *
	 * @param string $language
	 * @param int $menu_id
	 * @return object|null
	 */
	private function getMenuTree(string $language, int $menu_id)
	{

		$menu = Menu::find($menu_id);

		$tree = null;

		if ($menu) {

			$root = Menuitem::langIs($language)
				->menuIs($menu_id)
				->whereNull('parent_id')
				->first();

			if ($root) {

				$tree = Menuitem::scoped(['menu_id' => $menu_id, 'language' => $language])
					->defaultOrder()
					->get()
					->toTree();

			} else {

				if ($menu->slug == 'main') {

					// main menu is empty, create homepage

					$user_id = Auth::user()->id;
					$title = '[' . strtoupper($language) . '] homepage';
					$menuslug = 'homepage-' . $language;

					$homepage = Page::langIs($language)->where('ishome', 1)->first();

					if ($homepage) {
						$page_id = $homepage->id;
					} else {
						$page_id = $this->createNewPage($user_id, $language, $title, 'page', null, 0, 1);
					}

					$homeMenuItem = Menuitem::create([
						'language'        => $language,
						'menu_id'         => $menu_id,
						'title'           => $title,
						'slug'            => 'homepage-' . $menuslug,
						'type'            => 'page',
						'route'           => null,
						'routename'       => 'special.home.show',
						'route_has_auth'  => 0,
						'entity_id'       => 1,
						'entity_view_id'  => 101,
						'object_id'       => $page_id,
						'url'             => null,
						'locked_by_admin' => 0,
						'publish'         => 1,
						'parent_id'       => null,
					]);

				} else {

					$rootMenuItem = Menuitem::create([
						'language'        => $language,
						'menu_id'         => $menu_id,
						'title'           => $menu->title,
						'slug'            => null,
						'type'            => 'root',
						'route'           => null,
						'routename'       => null,
						'route_has_auth'  => 0,
						'entity_id'       => null,
						'entity_view_id'  => null,
						'object_id'       => null,
						'url'             => null,
						'locked_by_admin' => 0,
						'publish'         => 1,
						'parent_id'       => null,
					]);

				}

				$tree = Menuitem::scoped(['menu_id' => $menu_id, 'language' => $language])
					->defaultOrder()
					->get()
					->toTree();

			}

		}

		return $tree;

	}

	/**
	 * Get all menu items that are (or can be) parents
	 * If so, we can add children to the tree
	 *
	 * @param string $language
	 * @param int $menu_id
	 * @return object
	 */
	private function getMenuParents(string $language, int $menu_id)
	{

		$parentObjects = Menuitem::where(function ($query) use ($language, $menu_id) {
			$query->where('language', $language)
				->where('menu_id', $menu_id)
				->whereNull('parent_id');
		})->orWhere(function ($query) use ($language, $menu_id) {
			$query->where('language', $language)
				->where('menu_id', $menu_id)
				->where('type', 'parent');
		})->get();

		$parents = array();

		foreach ($parentObjects as $parentObject) {
			$childCount = Menuitem::where('parent_id', $parentObject->id)->count();
			$disabled = ($parentObject->depth > 0 && $childCount > config('lara-admin.menu.max_children'));
			$parents[] = [
				'id'         => $parentObject->id,
				'title'      => $parentObject->title,
				'childcount' => $childCount,
				'disabled'   => $disabled,
			];
		}

		return $parents;
	}

	/**
	 * Store the menu item
	 *
	 * @param object $entity
	 * @param Request $request
	 * @return int|null
	 */
	private function storeMenuItem(object $entity, Request $request)
	{

		$clanguage = $this->getContentLanguage($request, $entity);

		$parent = Menuitem::find($request->get('new_parent_id'));

		$menuId = $request->get('menu_id');

		$object = new Menuitem;

		$object->language = $clanguage;
		$object->menu_id = $menuId;
		$object->title = $request->get('new_title');
		$object->type = $request->get('new_type');

		if(config('app.env') == 'production') {
			$object->publish = 0;
		} else {
			$object->publish = 1;
		}

		$error = false;

		// type PAGE
		if ($request->get('new_type') == 'page') {

			if (is_numeric($request->get('new_object_id'))) {
				// existing page
				$new_object_id = $request->get('new_object_id');
			} else {
				// new page
				$new_object_id = $this->createNewPage(Auth::user()->id, $clanguage, $request->get('new_title'));

			}

			$pageEntity = Entity::where('entity_key', 'page')->first();

			// get default entity view for Page entity
			$pageEntityView = $pageEntity->views()->where('method', 'show')->first();

			if ($pageEntity) {

				$object->entity_id = $pageEntity->id;

				$object->entity_view_id = $pageEntityView->id;
				$object->object_id = $new_object_id;
				$object->url = null;

				// build routename
				$prefix = 'entity';
				$object->routename = $prefix . '.' . $pageEntity->getEntityKey() . '.' . $pageEntityView->method . '.' . $new_object_id;

			} else {
				$error = true;
			}

		}

		// type PARENT
		if ($request->get('new_type') == 'parent') {

			$object->entity_id = null;
			$object->entity_view_id = null;
			$object->object_id = null;
			$object->url = null;
			$object->routename = null;

		}

		// type URL
		if ($request->get('new_type') == 'url') {

			$object->url = $request->get('url');

			$object->entity_id = null;
			$object->entity_view_id = null;
			$object->object_id = null;
			$object->routename = null;

		}

		// type ENTITY
		if ($request->get('new_type') == 'entity') {

			$entityViewID = $request->get('new_entity_view_id');
			$entityView = EntityView::find($entityViewID);

			if ($entityView) {

				$ent = $entityView->entity;

				// find or create module page
				$this->findOrCreateModulePageBySlug($clanguage, $ent, $entityView);

				$object->entity_id = $ent->id;

				$object->entity_view_id = $entityView->id;
				$object->object_id = null;
				$object->url = null;

				// build routename
				$prefix = ($ent->objectrelations->has_tags ? 'entitytag' : 'entity');
				$object->routename = $prefix . '.' . $ent->getEntityKey() . '.' . $entityView->method;

			} else {
				$error = true;
			}

		}

		// type FORM
		if ($request->get('new_type') == 'form') {

			$entityViewID = $request->get('new_entity_form_view_id');
			$entityView = EntityView::find($entityViewID);

			if ($entityView) {

				$ent = $entityView->entity;

				// find or create module page
				$this->findOrCreateModulePageBySlug($clanguage, $ent, $entityView);

				$object->entity_id = $ent->id;

				$object->entity_view_id = $entityView->id;
				$object->object_id = null;
				$object->url = null;

				$prefix = 'form';
				$object->routename = $prefix . '.' . $ent->getEntityKey() . '.' . $entityView->method;

			} else {
				$error = true;
			}

		}

		if ($error === false) {

			$object->depth = $parent->depth + 1;
			$object->appendToNode($parent)->save();

			// add language to slug
			if(config('lara.multi_language_slugs_in_menu')) {
				$this->updateLanguageSlug($entity, $object);
			}

			return $object->id;

		} else {

			return null;

		}

	}

	/**
	 * Update the Menu Item
	 *
	 * @param object $menuItemEntity
	 * @param Request $request
	 * @param int $menuItemId
	 * @return void
	 */
	private function updateMenuItem(object $menuItemEntity, Request $request, int $menuItemId)
	{

		$clanguage = $this->getContentLanguage($request, $menuItemEntity);

		$object = Menuitem::findOrFail($menuItemId);

		$object->title = $request->get('title');
		$object->type = $request->get('type');
		$object->publish = $request->get('publish');

		if ($request->has('reset_slug')) {
			$object->slug = null;
		}

		if ($request->has('slug_lock')) {
			$object->slug_lock = 1;
		} else {
			$object->slug_lock = 0;
		}

		if ($request->has('locked_by_admin')) {
			$object->locked_by_admin = 1;
		} else {
			$object->locked_by_admin = 0;
		}

		if ($request->has('route_has_auth')) {
			$object->route_has_auth = 1;
		} else {
			$object->route_has_auth = 0;
		}

		$error = false;

		// type PAGE
		if ($request->get('type') == 'page') {

			if (is_numeric($request->get('object_id'))) {
				// existing page
				$obj_id = $request->get('object_id');
			} else {
				// new page
				$obj_id = $this->createNewPage(Auth::user()->id, $clanguage, $request->get('title'));
			}

			$pageEntity = Entity::where('entity_key', 'page')->first();

			// get default show method for Page entity
			$pageEntityView = $pageEntity->views()->where('method', 'show')->first();

			if ($pageEntity) {

				$object->entity_id = $pageEntity->id;

				$object->entity_view_id = $pageEntityView->id;
				$object->object_id = $obj_id;
				$object->url = null;

				// build routename
				if (empty($object->parent_id)) {
					// homepage
					$object->routename = 'special.home.show';
				} else {
					$prefix = 'entity';
					$object->routename = $prefix . '.' . $pageEntity->entity_key . '.' . $pageEntityView->method . '.' . $obj_id;
				}

			} else {
				$error = true;
			}

		}

		// type PARENT
		if ($request->get('type') == 'parent') {

			$object->entity_id = null;
			$object->entity_view_id = null;
			$object->object_id = null;
			$object->url = null;
			$object->routename = null;

		}

		// type URL
		if ($request->get('type') == 'url') {

			$object->url = $request->get('url');

			$object->entity_id = null;
			$object->entity_view_id = null;
			$object->object_id = null;
			$object->routename = null;

		}

		// type ENTITY
		if ($request->get('type') == 'entity') {

			$entityViewID = $request->get('entity_view_id');
			$entityView = EntityView::find($entityViewID);

			if ($entityView) {

				$ent = $entityView->entity;

				$modelClass = $ent->entity_model_class;
				$lara = $this->getEntityVarByModel($modelClass);
				$entity = new $lara;

				// find or create module page
				$this->findOrCreateModulePageBySlug($clanguage, $entity, $entityView);

				$object->entity_id = $ent->id;

				$object->entity_view_id = $entityView->id;
				$object->object_id = null;
				$object->url = null;

				// build routename
				$prefix = ($ent->objectrelations->has_tags ? 'entitytag' : 'entity');
				$object->routename = $prefix . '.' . $ent->getEntityKey() . '.' . $entityView->method;

				// tag

				if ($request->has('tag_id')) {
					$tag_id = $request->get('tag_id');
					if ($tag_id) {
						$tag = Tag::find($tag_id);
						if ($tag) {
							$object->tag_id = $tag_id;
							// check parent
							$this->checkMenuTagParent($object);
						} else {
							$object->tag_id = null;
						}
					} else {
						$object->tag_id = null;
					}
				}

			} else {
				$error = true;
			}

		}

		// type FORM
		if ($request->get('type') == 'form') {

			$entityViewID = $request->get('entity_form_view_id');
			$entityView = EntityView::find($entityViewID);

			if ($entityView) {

				$ent = $entityView->entity;

				// find or create module page
				$this->findOrCreateModulePageBySlug($clanguage, $ent, $entityView);

				$object->entity_id = $ent->id;

				$object->entity_view_id = $entityView->id;
				$object->object_id = null;
				$object->url = null;

				$prefix = 'form';
				$object->routename = $prefix . '.' . $ent->getEntityKey() . '.' . $entityView->method;

			} else {
				$error = true;
			}

		}

		if ($error === false) {

			$object->save();

			if(config('lara.multi_language_slugs_in_menu')) {
				$this->updateLanguageSlug($menuItemEntity, $object);
			}

			$this->rebuildMenuRoutes($menuItemId);

		}

	}

	private function checkMenuTagParent($node)
	{

		$menuTagParent = Menuitem::langIs($node->language)->menuIs($node->menu_id)->where('entity_id', $node->entity_id)->whereNull('tag_id')->first();

		if (empty($menuTagParent)) {

			// get root
			$root = Menuitem::langIs($node->language)->menuIs($node->menu_id)->whereNull('parent_id')->first();

			if ($root) {

				// get entity
				$ent = Entity::where('id', $node->entity_id)->first();

				if ($ent) {

					// add entity parent to root
					$entParent = new Menuitem;
					$entParent->language = $node->language;
					$entParent->menu_id = $node->menu_id;
					$entParent->title = ucfirst($ent->entity_key);
					$entParent->type = 'entity';
					$entParent->tag_id = null;
					$entParent->routename = 'entitytag.' . $ent->entity_key . '.index';
					$entParent->route_has_auth = 0;
					$entParent->entity_id = $node->entity_id;
					$entParent->entity_view_id = $node->entity_view_id;
					$entParent->object_id = null;
					$entParent->url = null;
					$entParent->publish = 0;
					$entParent->depth = 1;

					$entParent->appendToNode($root)->save();

					$entParent->route = $entParent->slug;
					$entParent->depth = 1;
					$entParent->save();

				}
			}

		}

		return true;
	}

	/**
	 * Rebuild the menu routes
	 *
	 * Because we use nested sets and seo urls, we need to
	 * rebuild the menu routes everytime we update a menu item
	 *
	 * @param int $menuItemId
	 * @param bool $createRedirects
	 * @return void
	 */
	private function rebuildMenuRoutes(int $menuItemId, $createRedirects = false)
	{

		// get current menu item
		$object = Menuitem::find($menuItemId);

		// get root
		$root = $this->getNestedSetMenuRoot($object);

		// kalnoy/nestedset
		$tree = Menuitem::scoped(['menu_id' => $root->menu_id, 'language' => $root->language])
			->defaultOrder()
			->get()
			->toTree();

		foreach ($tree as $node) {
			$this->processMenuNode($node, null, false, $createRedirects);
		}

		$this->syncPagesWithMenu($root->language);

		$this->clearMenuRouteCache();

	}

	/**
	 * Build and save menu route recursively
	 *
	 * @param object $node
	 * @param string|null $parentRoute
	 * @param bool $forceauth
	 * @param bool $createRedirects
	 * @return void
	 */
	private function processMenuNode(object $node, $parentRoute = null, $forceauth = false, $createRedirects = false)
	{

		if ($node->depth == 0) {
			// homepage
			$node->route = null;
			$node->save();
		}

		if ($node->depth == 1) {

			$newRoute = $node->slug;
			$currentRoute = $node->route;

			if ($createRedirects) {
				if ($currentRoute) { // do not process new menu items
					if ($newRoute != $currentRoute) {
						// create redirect
						$this->createRedirect($node, $currentRoute, $newRoute, '301', 1);

					}
				}
			}

			$node->route = $newRoute;
			$node->save();
		}

		if ($node->depth > 1) {

			// add parent route
			$newRoute = $parentRoute . '/' . $node->slug;
			$currentRoute = $node->route;

			if ($createRedirects) {
				if ($currentRoute) { // do not process new menu items
					if ($newRoute != $currentRoute) {
						$this->createRedirect($node, $currentRoute, $newRoute, '301', 1);
					}
				}
			}

			$node->route = $newRoute;
			$node->save();
		}

		if ($forceauth) {
			$node->route_has_auth = 1;
			$node->save();
		}

		if ($node->type == 'parent') {
			if ($node->route_has_auth == 1) {
				$forceauth = true;
			}
		}

		foreach ($node->children as $child) {
			// pass parent route to children
			$this->processMenuNode($child, $node->route, $forceauth, $createRedirects);
		}

	}

	/**
	 * @param object $node
	 * @param string $from
	 * @param string $to
	 * @param string $type
	 * @param int $lock
	 * @param bool $isdetail
	 * @return void
	 */
	private function createRedirect(object $node, string $from, string $to, string $type, int $lock, $isdetail = false)
	{

		if (config('app.env') == 'production') {

			$newRedirect = Redirect::create([
				'language'        => $node->language,
				'title'           => $from,
				'redirectfrom'    => $from,
				'redirectto'      => $to,
				'redirecttype'    => $type,
				'publish'         => 1,
				'locked_by_admin' => $lock,
				'auto_generated'  => 1,

			]);

			if (!$isdetail && $node->type == 'entity') {

				// find master-detail urls
				$entity = Entity::find($node->entity_id);
				$modelClass = $entity->entity_model_class;

				$objects = $modelClass::where('publish', 1)->get();
				foreach ($objects as $object) {

					if ($entity->objectrelations->has_tags) {
						$detailFrom = $from . '/' . $object->slug . '.html';
						$detailTo = $to . '/' . $object->slug . '.html';
					} else {
						$detailFrom = $from . '/' . $object->slug;
						$detailTo = $to . '/' . $object->slug;
					}

					$this->createRedirect($node, $detailFrom, $detailTo, $type, $lock, true);
				}

			}

		}

	}

	/**
	 * Sync the Page objects to the Menu
	 *
	 * Because we want to be able to SORT the pages in the backend
	 * by their position in the frontend menu (and show their seo path),
	 * we sync the menu urls to the Page Model.
	 *
	 * We could of course use a JOIN when we fetch the Page objects,
	 * but in this case we opt for performance instead of normalisation.
	 *
	 * @param string $clanguage
	 * @return void
	 */
	private function syncPagesWithMenu(string $clanguage)
	{

		// reset page positions
		DB::table(config('lara-common.database.entity.entity_prefix') . 'pages')
			->where('language', $clanguage)
			->update(['ishome' => 0, 'position' => 0, 'menuroute' => null]);

		$menus = Menu::get();

		foreach ($menus as $menu) {

			$position = 0;

			$tree = $this->getMenuTree($clanguage, $menu->id);

			if ($tree) {
				foreach ($tree as $node) {

					$position++;

					$lastposition = $this->syncPage($clanguage, $node, $menu->id, $position);

					$position = ($lastposition > $position ? $lastposition : $position);

				}

			}

		}

		// reorder module pages
		$modulePages = Page::groupIs('module')->orderBy('slug')->get();
		$i = 8001;
		foreach ($modulePages as $modulePage) {
			$modulePage->position = $i;
			$modulePage->save();

			$i++;
		}

		// reorder uncategorized pages
		$pages = Page::where('position', 0)->get();
		$i = 9001;
		foreach ($pages as $page) {
			$page->position = $i;
			$page->save();

			$i++;
		}

	}

	/**
	 * Sync the Page object to the Menu
	 *
	 * @param string $clanguage
	 * @param object $node
	 * @param int $menuid
	 * @param int $position
	 * @return mixed
	 */
	private function syncPage(string $clanguage, object $node, int $menuid, int $position)
	{

		if ($node->type == 'page') {

			// get page
			$page = Page::find($node->object_id);

			if (!empty($page)) {

				$pagePosition = ($menuid * 100) + $position;

				// set menu route
				$page->menuroute = '/' . $node->route;
				$page->position = $pagePosition;

				// set home
				if (is_null($node->parent_id)) {
					$page->ishome = 1;
				}

				$page->save();

			}

		}

		foreach ($node->children as $child) {

			$position++;

			$lastposition = $this->syncPage($clanguage, $child, $menuid, $position);

			$position = ($lastposition > $position ? $lastposition : $position);

		}

		return $position;

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
	 * @param object $entityView
	 * @return mixed
	 */
	private function findOrCreateModulePageBySlug(string $language, object $entity, object $entityView)
	{

		$modulePageSlug = $entity->getEntityKey() . '-' . $entityView->method . '-module-' . $language;
		$modulePageTitle = ucfirst($entity->getEntityKey()) . ' ' . ucfirst($entityView->method) . ' Module Page';

		$checkModulePage = Page::langIs($language)->groupIs('module')->where('slug', $modulePageSlug)->first();

		if ($checkModulePage) {

			// use existing module page
			$modulePageId = $checkModulePage->id;

		} else {

			// create new module page
			$modulePageId = $this->createNewPage(Auth::user()->id, $language, $modulePageTitle, 'module', $modulePageSlug, 1);

		}

		return $modulePageId;
	}

	/**
	 * Create a new Page, or Page Block
	 *
	 * @param int $user_id
	 * @param string $language
	 * @param string $title
	 * @param string $group
	 * @param string|null $slug
	 * @param int $sluglock
	 * @param int $ishome
	 * @return mixed
	 */
	private function createNewPage($user_id, string $language, string $title, string $group = 'page', $slug = null, int $sluglock = 0, int $ishome = 0)
	{

		$data = [
			'title'     => $title,
			'ishome'    => $ishome,
			'cgroup'    => $group,
			'menuroute' => '',
		];

		$entity = new \Lara\Common\Lara\PageEntity;

		if ($entity->hasUser()) {
			$data['user_id'] = $user_id;
		}
		if ($entity->hasLanguage()) {
			$data['language'] = $language;
		}
		if ($entity->hasSlug()) {
			$data['slug'] = $slug;
			$data['slug_lock'] = $sluglock;
		}
		if ($entity->hasLead()) {
			$data['lead'] = '';
		}
		if ($entity->hasBody()) {
			$data['body'] = '';
		}
		if ($entity->hasStatus()) {
			$data['publish'] = 1;
			$data['publish_from'] = Carbon::now();
		}

		$newPage = Page::create($data);

		return $newPage->id;
	}

	/**
	 * Set the session key to clear the route cache
	 *
	 * The artisan command will be called with an AJAX call
	 * If we call it directly here, the redirects will not work properly
	 *
	 * @return bool
	 */
	private function clearMenuRouteCache()
	{

		session(['routecacheclear' => true]);

		return true;

	}


}