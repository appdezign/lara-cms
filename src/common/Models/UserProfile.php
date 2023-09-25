<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_auth_users_profiles';

	/**
	 * @var array
	 */
	protected $fillable = [
		'dark_mode',
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
	 * @return BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo('Lara\Common\Models\User');
	}

}
