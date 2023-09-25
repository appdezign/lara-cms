<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Upload extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_sys_uploads';

	/**
	 * @var array
	 */
	protected $fillable = [

		'user_id',
		'entity_type',
		'object_id',
		'token',
		'dz_session_id',
		'filename',
		'filetype',
		'mimetype',

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

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeCurrentUser(Builder $query)
	{
		return $query->where('user_id', Auth::user()->id);
	}

	/**
	 * @param Builder $query
	 * @param string $modelClass
	 * @return Builder
	 */
	public function scopeEntityTypeIs(Builder $query, string $modelClass)
	{
		return $query->where('entity_type', $modelClass);
	}

	/**
	 * @param Builder $query
	 * @param int $object_id
	 * @return Builder
	 */
	public function scopeObjectIs(Builder $query, int $object_id)
	{
		return $query->where('object_id', $object_id);
	}

	/**
	 * @param Builder $query
	 * @param string $token
	 * @return Builder
	 */
	public function scopeTokenIs($query, string $token)
	{
		return $query->where('token', $token);
	}

	/**
	 * @param Builder $query
	 * @param string $type
	 * @return Builder
	 */
	public function scopeTypeIs(Builder $query, string $type)
	{
		return $query->where('filetype', $type);
	}

}
