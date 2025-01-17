<?php

namespace Lara\Common\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Closure;

class Force2fa {

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {


	    $user = Auth::user();
		$twoFactorDisabled = empty($user->two_factor_secret);
		$allowedRoutes = ['admin.user.2fa', 'admin.user.save2fa'];
	    $routeName = Route::currentRouteName();

		if(config('lara.force_2fa') && $twoFactorDisabled && !in_array($routeName, $allowedRoutes)) {
			return redirect()->route('admin.user.2fa');
		} else {
			return $next($request);
		}

    }
}
