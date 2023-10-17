<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;

use Bouncer;

trait AdminAuthTrait
{

	/**
	 * Wrapper for authorize
	 *
	 * If user is an administrator:
	 * Check if the ability exists before we authorize.
	 * If the ability does not exist: create it,
	 * and assign it to the role of administrator
	 *
	 * See also: https://github.com/JosephSilber/bouncer
	 *
	 * @param string $ability
	 * @param string $modelClass
	 * @return mixed
	 */
	private function authorizer(string $ability, string $modelClass)
	{

		if (Auth::user()->isAn('administrator')) {

			if (!Bouncer::allows($ability, $modelClass)) {

				$this->checkAbilities($modelClass);

				Bouncer::refresh();

			}

		}

		return Bouncer::authorize($ability, $modelClass);

	}

	private function checkAdminAccess() {
		if (Auth::user()->isNotAn('administrator')) {
			abort(response()->view('lara-admin::errors.405', [], 405));
		}

		return true;
	}

	/**
	 * Check if the requested ability is defined in the database
	 * If not, add it, and assign it
	 *
	 * @param string $modelClass
	 * @return void
	 */
	private function checkAbilities(string $modelClass)
	{

		$lara = $this->getAuthEntityVarByModel($modelClass);
		$entity = new $lara;

		$abilities = config('lara-admin.abilities');

		foreach ($abilities as $ability) {

			if (!Ability::where('name', $ability)
				->where('entity_key', $entity->getEntityKey())
				->exists()) {

				$data = [
					'name'        => $ability,
					'title'       => ucfirst($entity->getEntityKey()) . ' ' . $ability,
					'entity_type' => $entity->getEntityModelClass(),
					'entity_key'  => $entity->getEntityKey(),

				];

				$newAbility = new Ability;
				$newAbility->forceFill($data);
				$newAbility->save();

				// find roles with level 100 and assign every possible ability
				$roles = Role::where('level', 100)->pluck('name')->toArray();

				foreach ($roles as $role) {
					Bouncer::allow($role)->to($ability, $entity->getEntityModelClass());
				}

			}

		}

		Bouncer::refresh();

	}

	/**
	 * Check all usergroups for this user
	 * and get the highest user level
	 *
	 * @param object $userobject
	 * @return int
	 */
	private function getUserLevel(object $userobject)
	{

		$userlevel = 0;

		foreach ($userobject->roles as $userrole) {
			if ($userrole->level > $userlevel) {
				$userlevel = $userrole->level;
			}
		}

		return $userlevel;
	}

	/**
	 * Get the User Role name from the Role with the highest user level
	 *
	 * @param object $userobject
	 * @return int
	 */
	private function getUserRoleName(object $userobject)
	{

		$userlevel = 0;

		foreach ($userobject->roles as $userrole) {
			if ($userrole->level > $userlevel) {
				$userlevel = $userrole->name;
			}
		}

		return $userlevel;
	}


	/**
	 * @param $request
	 * @param $object
	 * @return bool
	 */
	private function saveUserProfile($request, $object)
	{

		$profileFields = $this->getProfileFields('array');
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

		Artisan::call('httpcache:clear');

		return true;

	}

	/**
	 * @param string $type
	 * @return mixed
	 */
	private function getProfileFields($type = 'object')
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

	private function getAuthEntityVarByModel(string $modelClass)
	{

		$str = '\\' . str_replace('Models', 'Lara', $modelClass) . 'Entity';

		return $str;

	}


}

