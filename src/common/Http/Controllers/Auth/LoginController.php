<?php

namespace Lara\Common\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Lara\Front\Http\Traits\FrontTrait;

use Lara\Common\Models\User;
use Lara\Common\Lara\UserEntity;

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

// use Theme;
use Qirolab\Theme\Theme;

class LoginController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles authenticating users for the application and
	| redirecting them to your home screen. The controller uses a trait
	| to conveniently provide its functionality to your applications.
	|
	*/

	use FrontTrait;

	use AuthenticatesUsers;

	/**
	 * Where to redirect users after login.
	 *
	 * @var string
	 */
	// protected $redirectTo = '/admin';

	/**
	 * @var object
	 */
	protected $data;

	/**
	 * @var object
	 */
	protected $entity;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(Request $request)
	{

		$this->middleware('guest')->except('logout');

		$this->autologin($request);

		$this->entity = new UserEntity();
		$this->entity->setMethod('loginform');

		$this->data = $this->makeNewObj();

	}

	/**
	 * @param Request $request
	 * @return false
	 */
	private function autoLogin(Request $request)
	{

		$clientIp = request()->ip();
		$whiteList = config('lara-common.auto_login');

		if ($request->has('key') && in_array($clientIp, $whiteList)) {

			$userkey = $request->input('key');
			$user = User::where('api_token', $userkey)->first();

			if ($user) {

				if ($user->mainRole->name == 'administrator') {

					Auth::login($user);

					$user->last_login = Carbon::now();
					$user->is_loggedin = 1;
					$user->save();

					return redirect()->intended(route('admin.dashboard.index'));

				}
			}
		}

		return false;
	}

	/**
	 *
	 * override default login view
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function showLoginForm(Request $request)
	{

		// hard override of the intended url
		if ($request->has('returnto')) {
			$returnTo = $request->get('returnto');
			$returnToUrl = url($returnTo);
			session(['url.intended' => $returnToUrl]);
		}

		// header tags
		$this->data->htag = $this->getEntityHeaderTag($this->entity);

		if (config('lara.auth.has_front_auth')) {
			if ($this->intendedIsBackend()) {
				// show backend login
				return view('lara-common::auth.login', [
					'data' => $this->data,
				]);
			} else {
				// show frontend login
				return view('_user.auth.login', [
					'data' => $this->data,
				]);
			}
		} else {
			// show backend login
			$defaultViewFile = 'lara-common::auth.login';
			$overrideViewFile = 'lara-eve::auth.login';
			if (view()->exists($overrideViewFile)) {
				$viewFile = $overrideViewFile;
			} else {
				$viewFile = $defaultViewFile;
			}

			return view($viewFile, [
				'data' => $this->data,
			]);
		}

	}

	/**
	 *
	 * Override credentials
	 *
	 * Get the needed authorization credentials from the request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	protected function credentials(Request $request)
	{
		$field = filter_var($request->get($this->username()), FILTER_VALIDATE_EMAIL)
			? $this->username()
			: 'username';

		return [
			$field     => $request->get($this->username()),
			'password' => $request->password,
		];
	}

	/**
	 * Override authenticated
	 *
	 * The user has been authenticated.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param mixed $user
	 * @return mixed
	 */
	protected function authenticated(Request $request, $user)
	{

		$loginType = $request->input('_login_type');

		$intended = $this->getIntended();
		$isEmailVerification = $this->isEmailVerification($intended);

		// prevent users from logging in, if they are not verified,
		// unless the redirect is the email verification route(!)
		if (!$isEmailVerification && !$user->hasVerifiedEmail()) {
			Auth::logout();
			flash(_lanq('lara-common::auth.loginform.not_verified'))->warning();

			return redirect('/login');
		}

		$user->last_login = Carbon::now();
		$user->is_loggedin = 1;
		$user->save();

		// redirect
		$has_backend_access = false;
		foreach ($user->roles as $role) {
			if ($role->has_backend_access == 1) {
				$has_backend_access = true;
			}
		}

		if ($loginType == 'backend') {
			// check if user has backend access
			if ($has_backend_access) {
				return redirect()->intended(route('admin.dashboard.index'));
			} else {
				return redirect()->intended(route('special.home.show'));
			}
		} elseif ($loginType == 'frontend') {
			// redirect to home page
			return redirect()->intended(route('special.home.show'));
		} else {
			// default
			return redirect()->intended(route('special.home.show'));
		}

	}

	/**
	 * Override logout
	 *
	 * Log the user out of the application.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return RedirectResponse
	 */
	public function logout(Request $request)
	{

		$user = Auth::user();
		$user->is_loggedin = 0;
		$user->save();

		$this->guard()->logout();

		$request->session()->invalidate();

		if ($request->has('redirect')) {
			$redirect = $request->input('redirect');
		} else {
			$redirect = 'login';
		}

		return redirect()->route($redirect);

	}

	private function isEmailVerification($intended)
	{

		$isVerify = false;

		$parts = explode('/', $intended);
		if (is_array($parts) && sizeof($parts) >= 3) {
			$module = $parts[1];
			$method = $parts[2];
			if ($module == 'email' && $method == 'verify') {
				$isVerify = true;
			}
		}

		return $isVerify;

	}

	private function intendedIsBackend()
	{

		$intended = $this->getIntended();
		if ($intended) {
			if (substr($intended, 1, 5) == 'admin') {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}

	private function getIntended()
	{

		if (session()->has('url.intended')) {
			$intendedUrl = session()->get('url.intended');

			return substr($intendedUrl, strlen(config('app.url')));
		} else {
			return null;
		}
	}

}
