<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityObjectRelation extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_ent_entity_object_relations';

	/**
	 * @var string[]
	 */
	protected $guarded = ['id'];

	// see: https://stackoverflow.com/questions/65153476/
	// public $timestamps = false;

	// set table name
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

	/**
	 * @return bool
	 */
	public function usesTimestamps() : bool{
		return false;
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
	 * @return BelongsTo
	 */
	public function entity()
	{
		return $this->belongsTo('Lara\Common\Models\Entity');
	}

	/**
	 * @param string|null $value
	 * @return void
	 */
	public function setGroupValuesAttribute(string $value = null)
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

}
