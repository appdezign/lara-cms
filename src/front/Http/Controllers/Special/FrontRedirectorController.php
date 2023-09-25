<?php

namespace Lara\Front\Http\Controllers\Special;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class FrontRedirectorController extends Controller
{

	/**
	 * @var string|null
	 */
	protected $routename;

	public function __construct()
	{

		if (!App::runningInConsole()) {
			$this->routename = Route::current()->getName();
		}

	}

	/**
	 * @param Request $request
	 * @return Application|RedirectResponse|Redirector
	 */
	public function process(Request $request)
	{

		$queryString = $request->getQueryString();

		// catch redirects to full urls
		if(substr($this->routename,0,4) == 'http') {
			return redirect($this->routename);
		}

		$parts = explode('.', $this->routename);

		$newUrl = null;

		if (sizeof($parts) == 3) {

			// assume it's an actual routename
			list($prefix, $redirect, $url) = explode('.', $this->routename);

			$newUrl = str_replace('|', '/', $url);

		} elseif (sizeof($parts) == 2) {

			if ($parts[1] == 'html') {
				// assume it's a url of a detail page
				$newUrl = $this->routename;
			} else {
				$this->redirectHome();
			}

		} elseif (sizeof($parts) == 1) {

			// assume it's a url
			$newUrl = $this->routename;

		} else {

			$this->redirectHome();

		}

		// redirect
		if ($queryString) {
			return redirect($newUrl . '?' . $queryString);
		} else {
			return redirect($newUrl);
		}

	}

	/**
	 * @return RedirectResponse
	 */
	public function redirectHome()
	{

		// redirect to HomePage
		return redirect()->route('special.home.show');

	}

	/**
	 * @return RedirectResponse
	 */
	public function redirectToHomeUrl()
	{

		// redirect to HomePage
		return redirect('/');

	}

	/**
	 * @return RedirectResponse
	 */
	public function redirectSetup()
	{

		// redirect to HomePage
		return redirect()->route('setup.show');

	}

}
