<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

if (!App::runningInConsole()) {

	Route::group(['middleware' => ['web']], function () {

		// Setup
		if (config('lara.needs_setup')) {

			// Redirect root
			Route::get('/', '\Lara\Front\Http\Controllers\Special\FrontRedirectorController@redirectSetup');

			// Setup
			Route::get('setup', '\Lara\Common\Http\Controllers\Setup\SetupController@show')->name('setup.show');

			Route::post('setup', '\Lara\Common\Http\Controllers\Setup\SetupController@start')->name('setup.start');

			Route::get('setup/{step}', '\Lara\Common\Http\Controllers\Setup\SetupController@stepshow')->name('setup.stepshow');

			Route::post('setup/{step}', '\Lara\Common\Http\Controllers\Setup\SetupController@stepprocess')->name('setup.stepprocess');

		} else {

			// Auth
			Auth::routes(['verify' => true]);

			Route::post('2fa/verify', '\Lara\Common\Http\Controllers\Auth\TwoFactorController@verify')->name('2fa.verify');


			// Dynamic Images
			Route::get('images/cache/{width}/{height}/{fit}/{fitpos}/{quality}/{filename}', '\Lara\Common\Http\Controllers\Tools\ImageCacheController@process')->name('imgcache');

			// Original Images
			Route::get('images/nocache/{filename}', '\Lara\Common\Http\Controllers\Tools\ImageCacheController@nocache')->name('imgnocache');

		}

	});

}


