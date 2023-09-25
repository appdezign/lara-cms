<?php

namespace Lara\Front\Http\Controllers\Special;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginwidgetController extends Controller
{

	public function __construct()
	{
		//
	}

	/**
	 * @param Request $request
	 * @param string $type
	 * @return Application|Factory|View
	 */
	public function show(Request $request, string $type)
	{
		// type options: link, user, menu

		if ($request->has('returnto')) {
			$returnto = $request->get('returnto');
		} else {
			$returnto = null;
		}

		return view('_user.loginwidget.show', [
			'type'     => $type,
			'returnto' => $returnto,
		]);

	}

}
