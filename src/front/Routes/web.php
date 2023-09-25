<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\App;

use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Redirect;
use Lara\Common\Models\Entity;

/*
|
|--------------------------------------------------------------------------
| Route Order
|--------------------------------------------------------------------------
|
| This Routes file is called last
|
| The order is:
|
| /routes/web
| /Lara/Admin/Routes/web
| /Lara/Common/Routes/web
| /Lara/Entity/Routes/web
| /Lara/Front/Routes/web
|
*/

if (!App::runningInConsole() && !config('lara.needs_setup')) {

	if (config('lara.has_frontend')) {

		Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['web', 'httpcache', 'throttle:60,1', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'dateLocale']], function () {

			// $entity_prefix = 'entity';
			// $entity_tag_prefix = 'entitytag';

			$locale = LaravelLocalization::getCurrentLocale();

			// get home
			$rootMenuItem = Menuitem::langIs($locale)
				->menuSlugIs('main')
				->whereNull('parent_id')
				->with('entity')
				->first();

			if ($rootMenuItem) {

				/* ~~~~~~~~~~~~ DYNAMIC ROUTE MIDDLEWARE (start) ~~~~~~~~~~~~ */
				$specialMiddleware = array();
				if ((isset($rootMenuItem->entity) && $rootMenuItem->entity->hasFrontAuth()) == 1 || $rootMenuItem->route_has_auth) {
					$specialMiddleware[] = 'auth';
				}
				if (config('app.env') == 'production' && config('httpcache.enabled')) {
					$specialMiddleware[] = 'ttl:' . config('lara.httpcache_ttl');
				}
				/* ~~~~~~~~~~~~ DYNAMIC ROUTE MIDDLEWARE (end) ~~~~~~~~~~~~ */

				// Search
				Route::get('search', 'Special\SearchController@form')->name('special.search.form')->middleware($specialMiddleware);
				Route::get('searchresult', 'Special\SearchController@result')->name('special.search.result')->middleware($specialMiddleware);

				Route::get('searchresult/{module}', 'Special\SearchController@modresult')->name('special.search.modresult')->middleware($specialMiddleware);

			}

			if (config('lara.dynamic_routes') == true) {

				// debug, use for console command (artisan)
				// $locale = 'nl';
				$locale = LaravelLocalization::getCurrentLocale();

				/**
				 * Get all FOLDERS from the MENU
				 * and create redirects
				 */
				$menuFolders = Menuitem::langIs($locale)
					->typeIs('parent')
					->get();

				foreach ($menuFolders as $menuFolder) {

					if (!empty($menuFolder->route)) {

						$child = $menuFolder->descendants()->defaultOrder()->first();

						if (!empty($child)) {

							$childroute = str_replace('/', '|', $child->route);
							$childroutename = 'special.redirect.' . $childroute;

							Route::get($menuFolder->route, 'Special\FrontRedirectorController@process')
								->name($childroutename);

						} else {

							Route::get($menuFolder->route, 'Special\FrontRedirectorController@redirectHome');

						}

					}
				}

				// 404
				// Route::get('/{route}', 'Error\ErrorController@show')->name('error.show.404');

			}

			// redirects
			$redirects = Redirect::langIs($locale)->isPublished()->where('has_error', 0)->get();

			foreach ($redirects as $redirect) {
				$from = $redirect->redirectfrom;
				$to = $redirect->redirectto;
				$check = MenuItem::langIs($locale)->where('route', $from)->first();
				if ($check) {
					// ignore redirect
					$redirect->has_error = 1;
					$redirect->save();
				} else {
					Route::get($from, 'Special\FrontRedirectorController@process')
						->name($to);
				}
			}

		});

		// API for Pages and Blocks
		Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {

			Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {

				Route::resource('page', 'Api\\Page\\PagesController', ['as' => 'api', 'parameters' => ['page' => 'id']])->only(['index', 'show']);

				$entities = Entity::whereHas('egroup', function ($query) {
					$query->where('key', 'block');
				})->get();

				foreach ($entities as $entity) {

					Route::resource($entity->entity_key, 'Api\\Blocks\\' . $entity->entity_controller, ['as' => 'api', 'parameters' => ['page' => 'id']])->only(['index', 'show']);

				}

			});

		});

		// get CSRF token without HttpCache
		Route::get('csrf/{type}', '\Lara\Front\Http\Controllers\Special\CsrfController@show')->name('front.csrf');

		// get User IP without HttpCache
		Route::get('usrip/{type}', '\Lara\Front\Http\Controllers\Special\UsripController@show')->name('front.usrip');

		// get Login Widget without HttpCache
		Route::get('loginwidget/{type}', '\Lara\Front\Http\Controllers\Special\LoginwidgetController@show')->name('front.loginwidget');



		// Frontend Uploaders
		Route::post('upload/{type}', '\Lara\Front\Http\Controllers\Special\UploadController@process')->name('front.upload');

		Route::post('upload2/{type}', '\Lara\Front\Http\Controllers\Special\Upload2Controller@process')->name('front.upload2');

	} else {

		// Redirect to Dashboard
		Route::get('/{route}', '\Lara\Admin\Http\Controllers\Misc\RedirectorController@process')->name('admin.redirect.dashboard.index');

		// Redirect to Dashboard
		Route::get('/', '\Lara\Admin\Http\Controllers\Misc\RedirectorController@process')->name('admin.redirect.dashboard.index');

	}

}
