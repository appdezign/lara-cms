<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Blacklist extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_sys_blacklist';

	/**
	 * @var string[]
	 */
	protected $fillable = [
		'ipaddress',
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

}
