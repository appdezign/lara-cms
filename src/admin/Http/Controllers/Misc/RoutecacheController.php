<?php

namespace Lara\Admin\Http\Controllers\Misc;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class RoutecacheController extends Controller {

	public function __construct() {

	}

	/**
	 * Flush the route cache
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function clear(Request $request) {

		$exitcode = Artisan::call('route:trans:cache');

		$request->session()->put('routecacheclear', false);

		return response()->json([
			'success' => true,
			'payload' => [
				'routecache' => 'clear',
				'exitcode' => $exitcode,
			],
		]);

	}

}
