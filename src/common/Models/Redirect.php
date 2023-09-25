<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class Redirect extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_menu_redirects';

	/**
	 * @var string[]
	 */
	protected $guarded = ['id'];

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
	 * @param string $language
	 * @return Builder
	 */
	public function scopeLangIs(Builder $query, string $language)
	{
		return $query->where('language', $language);
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeIsPublished(Builder $query)
	{
		return $query->where('publish', 1);
	}

	/**
	 * @return void
	 */
	public function lockRecord()
	{
		$this->attributes['locked_at'] = Carbon::now();
		$this->attributes['locked_by'] = Auth::user()->id;
		$this->save();
	}

	/**
	 * @return void
	 */
	public function unlockRecord()
	{
		$this->attributes['locked_at'] = null;
		$this->attributes['locked_by'] = null;
		$this->save();
	}
}
