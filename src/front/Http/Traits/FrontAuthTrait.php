<?php

namespace Lara\Front\Http\Traits;

use Lara\Common\Models\User;

trait FrontAuthTrait
{

	private function getGuestUser()
	{
		$userId = Cache::remember('guest_user_id', 86400, function () {

			$user = User::where('username', 'guest')->first();

			if ($user) {
				return $user;
			} else {
				$newuser = User::create([
					'type'          => 'web',
					'is_admin'      => 0,
					'name'          => 'guest',
					'firstname'     => 'guest',
					'middlename'    => null,
					'lastname'      => 'guest',
					'username'      => 'guest',
					'email'         => 'guest@laracms.nl',
					'password'      => 'jyKTvaZAGXme!',
					'user_language' => 'nl',
				]);
				$newuser->assign('member');

				return $newuser;
			}

		});

		return $userId;
	}

	private function saveFrontUserProfile($request, $object)
	{

		$profileFields = $this->getFrontProfileFields('array');
		$pfields = array();
		foreach ($request->all() as $fieldkey => $fieldval) {
			if (substr($fieldkey, 0, 9) == '_profile_') {
				$fieldname = substr($fieldkey, 9);
				if (array_key_exists($fieldname, $profileFields)) {
					$pfields[$fieldname] = $request->input($fieldkey);
				}
			}
		}

		$object->profile()->update($pfields);

	}

	/**
	 * @param string $type
	 * @return mixed
	 */
	private function getFrontProfileFields($type = 'object')
	{

		$profileFields = config('lara-admin.userProfile');
		if ($type == 'array') {
			return $profileFields;
		} elseif ($type == 'object') {
			$profileFields = json_decode(json_encode($profileFields), false);

			return $profileFields;
		} else {
			return $profileFields;
		}

		return $profileFields;
	}


}
