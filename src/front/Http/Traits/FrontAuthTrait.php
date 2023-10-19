<?php

namespace Lara\Front\Http\Traits;

use Lara\Common\Models\User;

trait FrontAuthTrait
{

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
