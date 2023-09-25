<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;

class Related extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_object_related';

	/**
	 * @var string[]
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * @var bool
	 */
	public $timestamps = false;


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

}
