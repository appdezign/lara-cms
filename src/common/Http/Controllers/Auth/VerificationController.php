<?php

namespace Lara\Common\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;

use Illuminate\Http\Request;
use Lara\Common\Models\User;

class VerificationController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Email Verification Controller
	|--------------------------------------------------------------------------
	|
	| This controller is responsible for handling email verification for any
	| user that recently registered with the application. Emails may also
	| be re-sent if the user didn't receive the original email message.
	|
	*/

	use VerifiesEmails;

	/**
	 * Where to redirect users after verification.
	 *
	 * @var string
	 */
	protected $redirectTo = '/login';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// see: https://stackoverflow.com/questions/52949374/laravel-email-verification-forced-to-be-logged-in

		// $this->middleware('auth');
		$this->middleware('signed')->only('verify');
		$this->middleware('throttle:6,1')->only('verify', 'resend');

	}

	public function verify(Request $request)
	{

		// see: https://stackoverflow.com/questions/52949374/laravel-email-verification-forced-to-be-logged-in

		$userId = $request->route('id');
		$user = User::findOrFail($userId);


		if (! hash_equals((string) $request->route('id'), (string) $user->getKey())) {
			throw new AuthorizationException;
		}

		if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
			throw new AuthorizationException;
		}

		if ($user->hasVerifiedEmail()) {
			return redirect($this->redirectPath());
		}

		if ($user->markEmailAsVerified()) {
			event(new Verified($user));
		}

		if ($response = $this->verified($request)) {
			return $response;
		}

		return redirect($this->redirectPath())->with('verified', true);
	}

	/*
	public function show(Request $request)
	{
		return $request->user()->hasVerifiedEmail()
			? redirect($this->redirectPath())
			: view('_user.auth.verify');
	}
	*/
}