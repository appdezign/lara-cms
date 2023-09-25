<?php

namespace Lara\Admin\Http\ViewComposers;

use Illuminate\View\View;
use \Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Lara\Common\Models\Entity;

use Lara\Admin\Http\Traits\LaraAdminHelpers;

use LaravelLocalization;

use Bouncer;

use Cache;

class AdminMenuComposer {

	use LaraAdminHelpers;

	/**
	 * @var string
	 */
	protected $modelslug = '';

	/**
	 * @var array
	 */
	protected $sidebarMenu = array();

	/**
	 * @var string
	 */
	protected $adminprefix;

	public function __construct(Request $request) {

		// get active prefix, model and method
		$routename = Route::current()->getName();

		$parts = explode('.', $routename);
		if (count($parts) < 3) {
			// route is unknown, use default route
			$routename = 'admin.dashboard.index';
		}
		list($prefix, $entityKey, $method) = explode('.', $routename);

		$this->modelslug = $entityKey;

		// get sidebar-menu from cache for current role
		$user_role = $this->getUserRoleName(Auth::user());
		$cache_key = 'lara.admin.sidebar-menu.' . $user_role;

		$this->sidebarMenu = Cache::rememberForever($cache_key, function () use ($request) {

			$alias_routes = config('lara-common.routes.has_alias');

			// get admin prefix
			$this->adminprefix = config('lara.adminprefix');
			if (!empty($this->adminprefix)) {
				$this->adminprefix = $this->adminprefix . '/';
			}

			// get sidebar-menu from DB
			if ($request->is($this->adminprefix . '*')) {

				$sidebarGroups = config('lara-admin.admin_menu_groups');

				foreach ($sidebarGroups as $groupkey => $groupval) {

					if ($groupkey == 'root') {

						// add static menu items to root
						$this->sidebarMenu = $groupval['chld'];

						// add dynamic menu items to root
						$rootItems = Entity::where('menu_parent', $groupkey)
							->whereNot('entity_key', 'dashboard')
							->orderBy('menu_position', 'asc')
							->get();

						foreach ($rootItems as $rootItem) {

							if (Bouncer::allows('view', $rootItem->getEntityModelClass())) {

								if ($rootItem->getMenuPosition() > 0) {
									$this->sidebarMenu[ $rootItem->entity_key ] = [
										'name' => $rootItem->title,
										'slug' => $rootItem->entity_key,
										'icon' => $rootItem->getMenuIcon(),
										'chld' => '',
									];
								}

								// add alias
								if (!empty($alias_routes) && array_key_exists($rootItem->entity_key, $alias_routes)) {
									foreach ($alias_routes[ $rootItem->entity_key ] as $alias) {
										if ($alias['add_to_menu']) {
											$akey = $alias['aliaskey'];
											$this->sidebarMenu[ $akey ] = [
												'name' => $akey,
												'slug' => $akey,
												'icon' => $rootItem->getMenuIcon(),
												'chld' => '',
											];
										}
									}
								}

							}

						}

					} else {

						$this->sidebarMenu[ $groupkey ] = $groupval;
						$items = Entity::where('menu_parent', $groupkey)
							->orderBy('menu_position', 'asc')
							->get();
						foreach ($items as $item) {
							if (Bouncer::allows('view', $item->getEntityModelClass())) {

								if ($item->getMenuPosition() > 0) {
									$this->sidebarMenu[ $groupkey ]['chld'][ $item->entity_key ] = [
										'name' => $item->title,
										'slug' => $item->entity_key,
									];
								}

								// add alias
								if (!empty($alias_routes) && array_key_exists($item->entity_key, $alias_routes)) {
									foreach ($alias_routes[ $item->entity_key ] as $alias) {
										if ($alias['add_to_menu']) {
											$akey = $alias['aliaskey'];
											$this->sidebarMenu[ $groupkey ]['chld'][ $akey ] = [
												'name' => $akey,
												'slug' => $akey,
												'icon' => $item->getMenuIcon(),
												'chld' => '',
											];
										}
									}
								}

							}

						}

					}

				}

				// remove empty groups
				foreach($sidebarGroups as $groupkey => $groupval) {
					if($groupkey != 'root') {
						if(empty($this->sidebarMenu[ $groupkey ]['chld'])) {
							unset($this->sidebarMenu[ $groupkey ]);
						}
					}
				}
			}


			if (Auth::user()->isAn('administrator')) {
				$this->sidebarMenu['users']['chld'][] = [
					'name' => 'Roles',
					'slug' => 'role',
				];
				$this->sidebarMenu['users']['chld'][] = [
					'name' => 'Abilities',
					'slug' => 'ability',
				];
			}

			return $this->sidebarMenu;

		});

	}

	/**
	 * @param View $view
	 * @return void
	 */
	public function compose(View $view) {

		$data = array(
			'sidebarMenu'  => $this->sidebarMenu,
			'activeModule' => $this->modelslug,
		);

		$view->with($data);

	}

}