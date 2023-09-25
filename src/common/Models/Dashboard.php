<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_sys_dashboard';

	/**
	 * @var string[]
	 */
	protected $guarded = array(
		'id',
		'created_at',
		'updated_at',
	);

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
