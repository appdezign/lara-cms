<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

use Rutorika\Sortable\SortableTrait;

class MediaImage extends Model
{

	use SortableTrait;

	/**
	 * @var string
	 */
	protected $table = 'lara_object_images';

	/**
	 * @var array
	 */
	protected $fillable = [

		'filename',
		'mimetype',
		'title',
		'featured',
		'ishero',
		'caption',
		'image_title',
		'image_alt',

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

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeHero(Builder $query)
	{
		return $query->where('hero', 1);
	}

}
