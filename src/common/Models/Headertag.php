<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class Headertag extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_sys_headertags';

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
	 * @param Builder $query
	 * @param string $key
	 * @return Builder
	 */
	public function scopeKeyIs(Builder $query, string $key)
	{
		return $query->where('key', $key);
	}

	/**
	 * @param Builder $query
	 * @param string $cgroup
	 * @return Builder
	 */
	public function scopeGroupIs(Builder $query, string $cgroup)
	{
		return $query->where('cgroup', $cgroup);
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

	/**
	 * @return BelongsTo
	 */
	public function entity()
	{
		return $this->belongsTo('Lara\Common\Models\Entity');
	}

	/**
	 * @return BelongsTo
	 */
	public function larawidget()
	{
		return $this->belongsTo('Lara\Common\Models\Larawidget');
	}

}
