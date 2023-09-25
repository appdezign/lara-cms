<?php

namespace Lara\Common\Models;

use Silber\Bouncer\Database\Ability;

class Userability extends Ability
{
	/**
	 * @var array
	 */
	protected $fillable = ['name', 'title', 'entity_id', 'entity_type', 'entity_key', 'only_owned'];

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