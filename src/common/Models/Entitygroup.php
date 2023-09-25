<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

use Rutorika\Sortable\SortableTrait;

use Carbon\Carbon;

class Entitygroup extends Model
{

	use SortableTrait;

	/**
	 * @var string
	 */
	protected $table = 'lara_ent_entity_groups';

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
	 * one-to-many relationship
	 *
	 * @return HasMany
	 */
	public function entities()
	{
		return $this->hasMany('Lara\Common\Models\Entity');
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
	 * @param string $key
	 * @return Builder
	 */
	public function scopeKeyIsNot(Builder $query, string $key)
	{
		return $query->where('key', '!=', $key);
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
