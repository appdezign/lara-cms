<?php

namespace Lara\Common\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Validation\ValidationException;

use Google2FA;

class TwoFactorController extends Controller
{

    public function verify(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|string',
        ]);

        $user = Auth::user();

		$recoveryCodes = json_decode($user->two_factor_recovery_codes, true);

        $oneTimePassword = $request->input('one_time_password');

        if (Google2FA::verifyKey($user->two_factor_secret, $oneTimePassword)) {
			// One Time Password
	        Google2FA::login();
	        return redirect()->intended('/admin');
        } elseif(in_array($oneTimePassword, $recoveryCodes)) {
			// Recovery Code
	        Google2FA::login();
	        return redirect()->intended('/admin');
        } else {
            throw ValidationException::withMessages([
                'one_time_password' => [__('The one time password is invalid.')],
            ]);
        }

    }

}
