<?php

namespace Lara\Common\Http\Middleware;

use Closure;

use LaravelLocalization;

use Jenssegers\Date\Date;

class DateLocale {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {

		$language = LaravelLocalization::getCurrentLocale();

		Date::setLocale($language);

		return $next($request);
	}

}