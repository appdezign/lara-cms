<?php

namespace Lara\Common\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

use Lara\Common\Http\Middleware\DateLocale;
use Lara\Common\Http\Middleware\HasBackendAccess;
use Lara\Common\Http\Middleware\UserLocale;

use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;

use Igaster\LaravelTheme\Middleware\setTheme;
use Barryvdh\HttpCache\Middleware\CacheRequests;
use Barryvdh\HttpCache\Middleware\SetTtl;

use Lara\Common\Models\Entity;
use Lara\Common\Models\User;

use Lara\Common\Http\Traits\LaraCommonTrait;

use LaravelLocalization;

use Lang;

use Bouncer;

use Cache;

use Carbon\Carbon;

class LaraCommonServiceProvider extends ServiceProvider
{

	use LaraCommonTrait;

	/**
	 * Bootstrap the module services.
	 *
	 * @return void
	 */
	public function boot(\Illuminate\Routing\Router $router)
	{

		// register global middleware
		$router->aliasMiddleware('userLocale', UserLocale::class);
		$router->aliasMiddleware('dateLocale', DateLocale::class);
		$router->aliasMiddleware('backend', HasBackendAccess::class);

		$router->aliasMiddleware('localize', LaravelLocalizationRoutes::class);
		$router->aliasMiddleware('localizationRedirect', LaravelLocalizationRedirectFilter::class);
		$router->aliasMiddleware('localeSessionRedirect', LocaleSessionRedirect::class);
		$router->aliasMiddleware('localeViewPath', LaravelLocalizationViewPath::class);

		// $router->aliasMiddleware('setTheme', setTheme::class);

		$router->aliasMiddleware('httpcache', CacheRequests::class);
		$router->aliasMiddleware('ttl', SetTtl::class);

		// Publish Config
		$this->publishes([
			__DIR__ . '/../../../config/lara.php' => config_path('lara.php'),
			__DIR__ . '/../../../config/lara-common.php' => config_path('lara-common.php'),
			__DIR__ . '/../../../config/lara-eve.php' => config_path('lara-eve.php'),
		], 'lara');

		// Publish Translations
		$this->publishes([
			__DIR__ . '/../Resources/Lang' => resource_path('lang/vendor/lara-common'),
		], 'lara');
		$this->loadTranslationsFrom(__DIR__ . '/../Resources/Lang', 'lara-common');

		// Publish Views
		$this->publishes([
			__DIR__ . '/../Resources/Views' => resource_path('views/vendor/lara-common'),
		], 'laraviews');
		$this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'lara-common');

		// Migrations
		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		/**
		 * Override Bouncer table names
		 */
		$tablenames = config('lara-common.database');

		Bouncer::tables([
			'roles'          => $tablenames['auth']['roles'],
			'abilities'      => $tablenames['auth']['abilities'],
			'assigned_roles' => $tablenames['auth']['has_roles'],
			'permissions'    => $tablenames['auth']['has_abilities'],
		]);

		/**
		 * Disable email verification for backend users
		 */
		if (!config('lara.needs_setup') && !App::runningInConsole()) {
			$backendUsers = \Lara\Common\Models\User::where('type', 'web')->get();
			foreach ($backendUsers as $backendUser) {
				if ($backendUser->hasBackendAccess()) {
					if (isset($backendUser->email_verified_at) && empty($backendUser->email_verified_at)) {
						$backendUser->email_verified_at = Carbon::today();
						$backendUser->save();
					}
				}
			}
		}

		/**
		 * Override Image cache directories
		 */
		if (!config('lara.needs_setup') && !App::runningInConsole()) {

			$paths = array();
			$entities = Entity::whereHas('objectrelations', function ($query) {
				$query->where('has_images', 1);
			})->get();

			foreach ($entities as $entity) {

				$imageDisk = $entity->objectrelations->disk_images;
				$path = Storage::disk($imageDisk)->path($entity->entity_key);

				// check if directory exists
				if (!is_dir($path)) {
					// create media directory for this entity
					mkdir($path);
				}
				// add path to array
				$paths[] = $path;
			}

			config(['imagecache.paths' => $paths]);

		}

		/**
		 * Override Sortable models
		 */
		if (!config('lara.needs_setup') && !App::runningInConsole()) {

			$sortable = array();

			$sortable['builder'] = '\\Lara\\Common\\Models\\EntityCustomColumns';
			$sortable['image'] = '\\Lara\\Common\\Models\\MediaImage';

			$entities = Entity::whereHas('columns', function ($query) {
				$query->where('is_sortable', 1);
			})->get();

			foreach ($entities as $entity) {
				$sortable[$entity->entity_key] = $entity->getEntityModelClass();
			}
			config(['sortable.entities' => $sortable]);

		}

		// get Lara Version
		$laraversion = $this->getCommonLaraVersion();
		View::share('laraversion', $laraversion);


	}

	/**
	 * Register the module services.
	 *
	 * @return void
	 */
	public function register()
	{

		$configPath = __DIR__ . '/../../../config/lara-common.php';
		$this->mergeConfigFrom($configPath, 'lara-common');

		$this->app->register(RouteServiceProvider::class);

	}

}
