<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

use Silber\Bouncer\Database\HasRolesAndAbilities;

use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
	use SoftDeletes;
	use Notifiable;
	use HasRolesAndAbilities;

	/**
	 * @var string
	 */
	protected $table = 'lara_auth_users';

	/**
	 * @var string[]
	 */
	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
		'deleted_at',
	];

	/**
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	// set table name
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

	/**
	 * get Table Columns
	 *
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
	}

	/**
	 * override method of MustVerifyEmail
	 *
	 * @return bool
	 */
	public function hasVerifiedEmail() {
		if($this->hasBackendAccess()) {
			return true;
		} else {
			return ! is_null($this->email_verified_at);
		}
	}

	/**
	 * @return HasOne
	 */
	public function profile()
	{

		$this->checkProfile($this->id);

		return $this->hasOne('Lara\Common\Models\UserProfile');
	}

	/**
	 * @param string $password
	 * @return void
	 */
	public function setPasswordAttribute(string $password)
	{
		$this->attributes['password'] = bcrypt($password);
	}

	/**
	 * @return mixed|null
	 */
	public function getMainRoleAttribute()
	{
		return $this->roles()->orderBy('level', 'DESC')->first();
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeIsWeb(Builder $query)
	{
		return $query->where('type', 'web');
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeIsApi(Builder $query)
	{
		return $query->where('type', 'api');
	}

	public function hasBackendAccess()
	{
		$hasBackendAccess = false;
		foreach ($this->roles as $role) {
			if ($role->has_backend_access == 1) {
				$hasBackendAccess = true;
			}
		}

		return $hasBackendAccess;
	}

	public function hasLevel($level)
	{
		$hasLevel = false;
		foreach ($this->roles as $role) {
			if ($role->level >= $level) {
				$hasLevel = true;
			}
		}

		return $hasLevel;
	}

	/**
	 * lock record
	 *
	 * @return void
	 */
	public function lockRecord()
	{
		$this->attributes['locked_at'] = Carbon::now();
		$this->attributes['locked_by'] = Auth::user()->id;
		$this->save();
	}

	/**
	 * unlock record
	 *
	 * @return void
	 */
	public function unlockRecord()
	{
		$this->attributes['locked_at'] = null;
		$this->attributes['locked_by'] = null;
		$this->save();
	}

	private function checkProfile($userId)
	{

		// check relation manually so we can create if not exist
		$profile = UserProfile::where('user_id', $userId)->first();
		if (empty($profile)) {
			$profileFields = $this->getProfileFields();
			$fields = array();
			$fields['user_id'] = $userId;
			foreach ($profileFields as $profileField) {
				$fields[$profileField->name] = $profileField->default;
			}
			UserProfile::forceCreate($fields);

			return false;
		} else {
			return true;
		}

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
}
