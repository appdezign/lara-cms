<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class MediaVideo extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_object_videos';

	/**
	 * @var array
	 */
	protected $fillable = [

		'title',
		'youtubecode',
		'featured',

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
	 * @return MorphTo
	 */
	public function entity()
	{
		return $this->morphTo();
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeFeatured(Builder $query)
	{
		return $query->where('featured', 1);
	}

}
