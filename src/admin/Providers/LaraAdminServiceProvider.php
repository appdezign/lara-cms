<?php

namespace Lara\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

use Lara\Admin\View\Components\BadgeComponent;
use Lara\Admin\View\Components\BoxHeaderComponent;
use Lara\Admin\View\Components\CloseLeftComponent;
use Lara\Admin\View\Components\CloseRightComponent;
use Lara\Admin\View\Components\FormRowComponent;
use Lara\Admin\View\Components\FormRowReqComponent;
use Lara\Admin\View\Components\FormRowBadgeComponent;
use Lara\Admin\View\Components\ShowRowComponent;

use Lara\Admin\Http\Traits\LaraAdminHelpers;

use Lara\Common\Models\Setting;

use LaravelLocalization;

// use Theme;
use Qirolab\Theme\Theme;

use Lang;

class LaraAdminServiceProvider extends ServiceProvider
{

	use LaraAdminHelpers;

	/**
	 * Bootstrap the module services.
	 *
	 * @return void
	 */
	public function boot()
	{

		// Publish Config
		$this->publishes([
			__DIR__ . '/../../../config/lara-admin.php' => config_path('lara-admin.php'),
			__DIR__ . '/../../../config/lara-eve.php' => config_path('lara-eve.php'),
		], 'lara');

		// Publish Translations
		$this->publishes([
			__DIR__ . '/../Resources/Lang' => resource_path('lang/vendor/lara-admin'),
		], 'lara');
		$this->loadTranslationsFrom(__DIR__ . '/../Resources/Lang', 'lara-admin');

		// Publish Assets
		$this->publishes([
			__DIR__.'/../Resources/Assets/_public' => public_path('assets/admin'),
		], 'lara');

		// Publish Views (optional)
		$this->publishes([
			__DIR__.'/../Resources/Views' => resource_path('views/vendor/lara-admin'),
		], 'laraviews');
		$this->loadViewsFrom(__DIR__.'/../Resources/Views', 'lara-admin');

		// register components
		Blade::component('badge', BadgeComponent::class);
		Blade::component('boxheader', BoxHeaderComponent::class);
		Blade::component('closeleft', CloseLeftComponent::class);
		Blade::component('closeright', CloseRightComponent::class);
		Blade::component('formrow', FormRowComponent::class);
		Blade::component('formrowreq', FormRowReqComponent::class);
		Blade::component('formrowbadge', FormRowBadgeComponent::class);
		Blade::component('showrow', ShowRowComponent::class);

		// Backend only
		if (!App::runningInConsole() && ($this->app->request->is('admin/*') || $this->app->request->is('builder/*'))) {

			// we need the frontend theme, so we can edit the layout options of an object
			$theme = $this->getActiveFrontTheme();
			$parent = $this->getParentFrontTheme();
			Theme::set($theme, $parent);

			// Admin Menu
			View::composer(
				'lara-admin::_partials.menu', 'Lara\Admin\Http\ViewComposers\AdminMenuComposer'
			);

			// Language selector
			View::composer(
				'lara-admin::_partials.header', 'Lara\Admin\Http\ViewComposers\ContentLanguageComposer'
			);

			// Share the settings with all views
			$settings = Setting::pluck('value', 'key')->toArray();
			$settingz = json_decode(json_encode($settings), false);
			View::share('settngz', $settingz);

			// get Lara Version
			$laraversion = $this->getAdminLaraVersion();
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

		$configPath = __DIR__ . '/../../../config/lara-admin.php';
		$this->mergeConfigFrom($configPath, 'lara-admin');

		$this->app->register(RouteServiceProvider::class);

	}
}
