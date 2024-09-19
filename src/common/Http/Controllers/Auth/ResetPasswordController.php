<?php

namespace Lara\Common\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

	    if (!config('lara.auth.has_front_auth')) {
		    $this->redirectTo = '/';
	    } else {
		    $this->redirectTo = route('admin.dashboard.index');
	    }
    }

	public function showResetForm(Request $request)
	{

		if(config('lara.auth.can_reset_password')) {

			$token = $request->route()->parameter('token');
			$email = $request->input('email');

			$data = [
				'token' => $token,
				'email' => $email
			];

			if (config('lara.auth.has_front_auth')) {
				return view('_user.auth.passwords.reset', $data);
			} else {
				return view('lara-common::auth.passwords.reset', $data);
			}

		} else {
			return redirect()->route('special.home.show');
		}
	}

	protected function setUserPassword($user, $password)
	{
		// the password is encrypted in the model !!!
		$user->password = $password;
	}

	protected function redirectTo() {
		if (config('lara.auth.has_front_auth')) {
			return route('special.home.show');
		} else {
			return route('admin.dashboard.index');
		}

	}

}
