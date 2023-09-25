<?php

namespace Lara\Common\Models;

use Silber\Bouncer\Database\Role;

use Carbon\Carbon;

class Userrole extends Role
{

	/**
	 * @var array
	 */
	protected $fillable = ['name', 'title', 'level', 'has_backend_access'];

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