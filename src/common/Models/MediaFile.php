<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class MediaFile extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_object_files';

	/**
	 * @var array
	 */
	protected $fillable = [

		'filename',
		'mimetype',
		'title',
		'docdate',

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
