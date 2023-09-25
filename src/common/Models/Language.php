<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_sys_languages';

	/**
	 * @var string[]
	 */
	protected $guarded = [
		'id',
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
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeIsDefault(Builder $query)
	{
		return $query->where('default', 1);
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeIsPublished(Builder $query)
	{
		return $query->where('publish', 1);
	}

}
