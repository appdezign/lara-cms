<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Templatewidget extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_sys_entitywidgets';

	/**
	 * @var string[]
	 */
	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
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
	 * lock record
	 * @return void
	 */
	public function lockRecord()
	{
		$this->attributes['locked_at'] = Carbon::now();
		$this->attributes['locked_by'] = Auth::user()->id;
		$this->save();
	}

	/**
	 * unlock record
	 * @return void
	 */
	public function unlockRecord()
	{
		$this->attributes['locked_at'] = null;
		$this->attributes['locked_by'] = null;
		$this->save();
	}

}
