<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class ObjectSeo extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_object_seo';

	/**
	 * @var array
	 */
	protected $fillable = [
		'seo_focus',
		'seo_title',
		'seo_description',
		'seo_keywords',
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

}
