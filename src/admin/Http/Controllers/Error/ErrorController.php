<?php

namespace Lara\Admin\Http\Controllers\Error;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

use Jenssegers\Agent\Agent;

class ErrorController extends Controller {

	/**
	 * @var string|null
	 */
	protected $routename;

	/**
	 * @var bool
	 */
	protected $ismobile;

	/**
	 * @var bool
	 */
	protected $isbuilder = false;

	public function __construct() {

		// get agent
		$agent = new Agent();
		$this->ismobile = $agent->isMobile();

		if (!App::runningInConsole()) {

			$this->routename = Route::current()->getName();

			$this->middleware(function ($request, $next) {
				view()->share('ismobile', $this->ismobile);

				return $next($request);
			});
		}
	}

	/**
	 * @return Application|Factory|View
	 */
	public function show() {

		$parts = explode('.', $this->routename);

		$id = end($parts);

		$viewfile = 'lara-admin::errors.'.$id;

		return view($viewfile);

	}




}
