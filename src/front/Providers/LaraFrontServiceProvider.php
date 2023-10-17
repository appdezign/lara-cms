<?php

namespace Lara\Front\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

use Lara\Front\View\Components\FrontFormRowComponent;
use Lara\Front\View\Components\FrontShowRowComponent;

use Lara\Front\Http\Traits\FrontTrait;
use Lara\Front\Http\Traits\FrontThemeTrait;


use Lara\Common\Models\Setting;

use LaravelLocalization;

// use Theme;
use Qirolab\Theme\Theme;

class LaraFrontServiceProvider extends ServiceProvider
{

	use FrontTrait;
	use FrontThemeTrait;

	/**
	 * Bootstrap the module services.
	 *
	 * @return void
	 */
	public function boot()
	{

		// Publish Config
		$this->publishes([
			__DIR__ . '/../../../config/lara-front.php' => config_path('lara-front.php'),
		], 'lara');

		// Publish Translations
		$this->publishes([
			__DIR__ . '/../Resources/Lang' => resource_path('lang/vendor/lara-front'),
		], 'lara');
		$this->loadTranslationsFrom(__DIR__ . '/../Resources/Lang', 'lara-front');

		// Publish Views
		$this->publishes([
			__DIR__.'/../Resources/Views' => resource_path('views/vendor/lara-front'),
		], 'larafront');
		$this->loadViewsFrom(__DIR__.'/../Resources/Views', 'lara-front');

		// register components
		Blade::component('frontformrow', FrontFormRowComponent::class);
		Blade::component('frontshowrow', FrontShowRowComponent::class);

		// Frontend only
		if (!App::runningInConsole() && !$this->app->request->is('admin/*')) {

			// BS5
			// Set theme
			$theme = $this->getFrontTheme();
			$parent = $this->getParentTheme();

			Theme::set($theme, $parent);

			if (!config('lara.needs_setup')) {

				// Share the settings with all views
				$settings = Setting::pluck('value', 'key')->toArray();
				$settingz = json_decode(json_encode($settings), false);
				View::share('settngz', $settingz);

			}

			// get Lara Version
			$laraversion = $this->getFrontLaraVersion();
			View::share('laraversion', $laraversion);

		}

	}

	/**
	 * Register the module services.
	 *
	 * @return void
	 */
	public function register()
	{

		$configPath = __DIR__ . '/../../../config/lara-front.php';
		$this->mergeConfigFrom($configPath, 'lara-front');

		$this->app->register(RouteServiceProvider::class);

	}

}
