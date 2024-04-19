<?php

use Illuminate\Support\Facades\App;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

if (!App::runningInConsole() && !config('lara.needs_setup')) {

	$adminprefix = config('lara.adminprefix');
	$builderprefix = config('lara.builderprefix');

	Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
		\UniSharp\LaravelFilemanager\Lfm::routes();
	});

	Route::group(['prefix' => $adminprefix, 'middleware' => ['web', 'userLocale']], function () {

		/*
		|--------------------------------------------------------------------------
		| Routenames
		|--------------------------------------------------------------------------
		|
		| Make sure we alway use routenames with the format:
		| 'prefix.entity.method'
		| for all entity routes
		|
		*/

		Route::group(['middleware' => ['auth', 'verified', 'backend']], function () {


			// Redirect to Dashboard
			Route::get('/', 'Misc\RedirectorController@process')->name('admin.redirect.dashboard.index');

			// Dashboard
			Route::get('dashboard', 'Dashboard\DashboardController@index')->name('admin.dashboard.index');
			Route::get('dashboard/refresh', 'Dashboard\DashboardController@refresh')->name('admin.dashboard.refresh');
			Route::post('dashboard/getrefresh', 'Dashboard\DashboardController@getrefresh')->name('admin.dashboard.getrefresh');
			Route::get('dashboard/db', 'Dashboard\DashboardController@dbshow')->name('admin.dashboard.dbshow');
			Route::post('dashboard/db', 'Dashboard\DashboardController@dbcheck')->name('admin.dashboard.dbcheck');
			Route::get('dashboard/purge', 'Dashboard\DashboardController@purge')->name('admin.dashboard.purge');
			Route::post('dashboard/purgeprocess', 'Dashboard\DashboardController@purgeprocess')->name('admin.dashboard.purgeprocess');

			// Cache Manager
			Route::get('cache', 'Misc\CacheController@index')->name('admin.cache.index');
			Route::post('cache', 'Misc\CacheController@clear')->name('admin.cache.clear');
			Route::get('routecache', 'Misc\RoutecacheController@clear')->name('admin.routecache.clear');

			// Profile
			Route::get('user/profile', 'Auth\ProfileController@edit')->name('admin.user.profile');
			Route::patch('user/profile', 'Auth\ProfileController@update')->name('admin.user.saveprofile');

			// translation check
			Route::get('translation/check', 'Tools\\TranslationsController@check')->name('admin.translation.check');

			// translation import
			Route::get('translation/import', 'Tools\\TranslationsController@import')->name('admin.translation.import');
			Route::post('translation/import', 'Tools\\TranslationsController@saveimport')->name('admin.translation.saveimport');

			// translation export
			Route::get('translation/export', 'Tools\\TranslationsController@export')->name('admin.translation.export');
			Route::post('translation/export', 'Tools\\TranslationsController@saveexport')->name('admin.translation.saveexport');

			// translation merge
			Route::get('translation/merge', 'Tools\\TranslationsController@merge')->name('admin.translation.merge');
			Route::post('translation/merge', 'Tools\\TranslationsController@savemerge')->name('admin.translation.savemerge');

			// language export
			Route::get('language/export', 'Menu\\LanguagesController@export')->name('admin.language.export');
			Route::post('language/export', 'Menu\\LanguagesController@saveexport')->name('admin.language.saveexport');

			// language export
			Route::get('language/purge', 'Menu\\LanguagesController@purge')->name('admin.language.purge');
			Route::post('language/purge', 'Menu\\LanguagesController@purgeprocess')->name('admin.language.purgeprocess');

			Route::get('image/reorder/{type}/{id}', 'Media\\ImageController@reorder')->name('admin.image.reorder');

			// Entity routes
			$entities = Lara\Common\Models\Entity::entityGroupIsNot('entity')->entityGroupIsNot('form')->where('resource_routes', 1)->get();

			foreach ($entities as $entity) {

				$ekey = $entity->getEntityKey();
				$ecntr = $entity->getEntityController();
				$prfx = ucfirst($entity->egroup->key);

				Route::get($ekey . '/{id}/unlock', $prfx . '\\' . $ecntr . '@unlock')->name('admin.' . $ekey . '.unlock');

				Route::post($ekey . '/reorder', $prfx . '\\' . $ecntr . '@savereorder')->name('admin.' . $ekey . '.savereorder');
				Route::get($ekey . '/reorder', $prfx . '\\' . $ecntr . '@reorder')->name('admin.' . $ekey . '.reorder');

				Route::post($ekey . '/batch', $prfx . '\\' . $ecntr . '@batch')->name('admin.' . $ekey . '.batch');

				if($ekey == 'tag') {
					Route::get($ekey . '/{id}/taggable', $prfx . '\\' . $ecntr . '@taggable')->name('admin.' . $ekey . '.taggable');
					Route::post($ekey . '/{id}/savetaggable', $prfx . '\\' . $ecntr . '@savetaggable')->name('admin.' . $ekey . '.savetaggable');
				}

				Route::resource($ekey, $prfx . '\\' . $ecntr, ['as' => 'admin', 'parameters' => [$ekey => 'id']]);

			}

			/*
			|--------------------------------------------------------------------------
			| Alias
			|--------------------------------------------------------------------------
			*/

			// Page alias
			Route::post('module/batch', 'Page\\PagesController@batch')->name('admin.module.batch');
			Route::get('module/{id}/unlock', 'Page\\PagesController@unlock')->name('admin.module.unlock');
			Route::resource('module', 'Page\\PagesController', ['as' => 'admin', 'parameters' => ['module' => 'id']]);

			/*
			|--------------------------------------------------------------------------
			| Builder
			|--------------------------------------------------------------------------
			*/

			// Entity
			Route::get('entity/{id}/unlock', 'Builder\EntityController@unlock')->name('admin.entity.unlock');
			Route::post('entity/reorder', 'Builder\EntityController@savereorder')->name('admin.entity.savereorder');
			Route::post('entity/batch', 'Builder\EntityController@batch')->name('admin.entity.batch');
			Route::post('entity/seed', 'Builder\EntityController@seed')->name('admin.entity.seed');
			Route::get('entity/export', 'Builder\EntityController@export')->name('admin.entity.export');
			Route::get('entity/{id}/reorder', 'Builder\EntityController@reorder')->name('admin.entity.reorder');
			Route::resource('entity', 'Builder\EntityController', ['as' => 'admin', 'parameters' => ['entity' => 'id']]);

			// Form
			Route::get('form/{id}/unlock', 'Builder\FormController@unlock')->name('admin.form.unlock');
			Route::post('form/reorder', 'Builder\FormController@savereorder')->name('admin.form.savereorder');
			Route::post('form/batch', 'Builder\FormController@batch')->name('admin.form.batch');
			Route::post('form/seed', 'Builder\FormController@seed')->name('admin.form.seed');
			Route::get('form/export', 'Builder\FormController@export')->name('admin.form.export');
			Route::get('form/{id}/reorder', 'Builder\FormController@reorder')->name('admin.form.reorder');
			Route::resource('form', 'Builder\FormController', ['as' => 'admin', 'parameters' => ['form' => 'id']]);

			// Entity Group Manager
			Route::get('entitygroup/{id}/unlock', 'Builder\EntitygroupsController@unlock')->name('admin.entitygroup.unlock');
			Route::post('entitygroup/reorder', 'Builder\EntitygroupsController@savereorder')->name('admin.entitygroup.savereorder');
			Route::get('entitygroup/reorder', 'Builder\EntitygroupsController@reorder')->name('admin.entitygroup.reorder');
			Route::resource('entitygroup', 'Builder\EntitygroupsController', ['as' => 'admin', 'parameters' => ['entitygroup' => 'id']]);

			/*
			|--------------------------------------------------------------------------
			| Special Routes
			|--------------------------------------------------------------------------
			*/

			// get entity tags (for AJAX calls in Menu editor)
			Route::get('tag/{id}/gettags/', 'Tag\\TagsController@gettags')->name('admin.tag.gettags');

			// Uploader
			Route::post('upload/{type}', '\Lara\Admin\Http\Controllers\Misc\UploadController@process')->name('admin.upload');

			// Sortable
			Route::post('sort', '\Rutorika\Sortable\SortableController@sort')->name('admin.sort');

		});

	});

}



