<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class MediaVideoFile extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_object_videofiles';

	/**
	 * @var array
	 */
	protected $fillable = [

		'filename',
		'mimetype',
		'title',
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

}
