<?php

namespace Lara\Common\Http\Middleware;

use Closure;
use Auth;
use App;
use Config;

use Lara\Common\Models\Language;

use Jenssegers\Date\Date;

class UserLocale {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {

		if(!empty(Auth::user()->user_language)) {
			$language = Auth::user()->user_language;
		} else {
			$default = Language::where('publish', 1)->where('backend', 1)->where('backend_default', 1)->first();
			if($default) {
				$language = $default->code;
			} else {
				// fall back
				$language = Config::get('app.locale');
			}
		}

		App::setLocale($language);

		Date::setLocale($language);

		return $next($request);
	}

}