<?php

namespace Lara\Admin\Http\Controllers\Misc;

use App\Http\Controllers\Controller;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;


class RedirectorController extends Controller {

	/**
	 * @var string|null
	 */
	protected $routename;

	public function __construct() {
		if (!App::runningInConsole()) {
			$this->routename = Route::current()->getName();
		}
	}

	/**
	 * @return RedirectResponse
	 */
	public function process() {

		list($prefix, $redirect, $entityKey, $method) = explode('.', $this->routename);

		$newroute = $prefix . '.' . $entityKey . '.' . $method;

		// redirect
		return redirect()->route($newroute);

	}


}
