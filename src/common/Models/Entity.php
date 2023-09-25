<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class Entity extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_ent_entities';

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
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->attributes['title'];
	}

	/**
	 * @return mixed
	 */
	public function getEntityModelClass()
	{
		return $this->attributes['entity_model_class'];
	}

	/**
	 * @return mixed
	 */
	public function getEntityKey()
	{
		return $this->attributes['entity_key'];
	}

	/**
	 * @return mixed
	 */
	public function getEntityController()
	{
		return $this->attributes['entity_controller'];
	}

	/**
	 * @return bool
	 */
	public function hasResourceRoutes()
	{
		return boolval($this->attributes['resource_routes']);
	}

	/**
	 * @return bool
	 */
	public function hasFrontAuth()
	{
		return boolval($this->attributes['has_front_auth']);
	}

	/**
	 * @return mixed
	 */
	public function getMenuParent()
	{
		return $this->attributes['menu_parent'];
	}

	/**
	 * @return mixed
	 */
	public function getMenuPosition()
	{
		return $this->attributes['menu_position'];
	}

	/**
	 * @return mixed
	 */
	public function getMenuIcon()
	{
		return $this->attributes['menu_icon'];
	}

	/**
	 * Get table name.
	 *
	 * @return mixed
	 */
	public static function getTableName()
	{
		return with(new static)->getTable();
	}

	/**
	 * one-to-many (reverse) relationship
	 *
	 * @return BelongsTo
	 */
	public function egroup()
	{
		return $this->belongsTo('Lara\Common\Models\Entitygroup', 'group_id');
	}

	/**
	 * one-to-one relationship
	 *
	 * @return HasOne
	 */
	public function columns()
	{
		return $this->hasOne('Lara\Common\Models\EntityColumn');
	}

	/**
	 * one-to-one relationship
	 *
	 * @return HasOne
	 */
	public function objectrelations()
	{
		return $this->hasOne('Lara\Common\Models\EntityObjectRelation');
	}

	/**
	 * one-to-one relationship
	 *
	 * @return HasOne
	 */
	public function panels()
	{
		return $this->hasOne('Lara\Common\Models\EntityPanel');
	}

	/**
	 * one-to-many relationship
	 *
	 * @return HasMany
	 */
	public function customcolumns()
	{
		return $this->hasMany('Lara\Common\Models\EntityCustomColumns')
			->orderBy('fieldhook', 'desc')
			->orderBy('position', 'asc');
	}

	/**
	 * one-to-many relationship
	 *
	 * @return HasMany
	 */
	public function relations()
	{
		return $this->hasMany('Lara\Common\Models\EntityRelation');
	}

	/**
	 * one-to-many relationship
	 *
	 * @return HasMany
	 */
	public function views()
	{
		return $this->hasMany('Lara\Common\Models\EntityView');
	}

	/**
	 * @param Builder $query
	 * @param string $key
	 * @return Builder
	 */
	public function scopeEntityGroupIs(Builder $query, string $key)
	{
		return $query->whereHas('egroup', function ($q) use ($key) {
			$q->where('key', $key);
		});
	}

	/**
	 * @param Builder $query
	 * @param string $key
	 * @return Builder
	 */
	public function scopeEntityGroupIsNot(Builder $query, string $key)
	{
		return $query->whereHas('egroup', function ($q) use ($key) {
			$q->where('key', '!=', $key);
		});
	}

	/**
	 * @param Builder $query
	 * @param array $keys
	 * @return Builder
	 */
	public function scopeEntityGroupIsOneOf(Builder $query, array $keys)
	{
		return $query->whereHas('egroup', function ($q) use ($keys) {
			$q->whereIn('key', $keys);
		});
	}

	/**
	 * @param string $value
	 *
	 * @return void
	 */
	public function setSortFieldAttribute(string $value)
	{
		if ($this->attributes['is_sortable'] == 1) {
			$this->attributes['sort_field'] = 'position';
		} else {
			$this->attributes['sort_field'] = $value;
		}
	}

	/**
	 * @param string $value
	 *
	 * @return void
	 */
	public function setSortOrderAttribute(string $value)
	{
		if ($this->attributes['is_sortable'] == 1) {
			$this->attributes['sort_order'] = 'asc';
		} else {
			$this->attributes['sort_order'] = $value;
		}
	}

	/**
	 * @param string $value
	 *
	 * @return void
	 */
	public function setGroupValuesAttribute(string $value)
	{

		if (empty($value)) {

			$this->attributes['group_values'] = '';

		} else {

			$result = json_decode($value);

			if (json_last_error() == JSON_ERROR_NONE) {
				$this->attributes['group_values'] = $value;
			} else {
				$array = array_map('trim', explode(',', $value));
				$json = json_encode($array, JSON_FORCE_OBJECT);
				$this->attributes['group_values'] = $json;
			}
		}

	}

	/**
	 * @param string|null $value
	 * @return string
	 */
	public function getGroupValuesAttribute(string $value = null)
	{
		$array = json_decode($value, true);

		if (json_last_error() == JSON_ERROR_NONE) {
			return join(', ', $array);
		} else {
			return $value;
		}
	}

	/**
	 * lock record
	 *
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
	 *
	 * @return void
	 */
	public function unlockRecord()
	{
		$this->attributes['locked_at'] = null;
		$this->attributes['locked_by'] = null;
		$this->save();
	}

}
