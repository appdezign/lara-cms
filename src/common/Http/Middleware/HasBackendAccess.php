<?php

namespace Lara\Common\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Closure;

class HasBackendAccess {

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {

    	$user = Auth::user();

	    $has_backend_access = false;
	    foreach ($user->roles as $role) {
		    if ($role->has_backend_access == 1) {
			    $has_backend_access = true;
		    }
	    }

	    if ($has_backend_access) {
		    return $next($request);
	    } else {
	    	if(config('lara.has_frontend')) {
			    return redirect()->route('special.home.show');
		    } else {
			    abort(405, 'Unauthorized action.');
		    }
	    }

    }
}
