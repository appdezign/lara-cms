<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityRelation extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_ent_entity_relations';

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
		return $this->belongsTo('Lara\Common\Models\Entity', 'entity_id');
	}

	/**
	 * @return BelongsTo
	 */
	public function relatedEntity()
	{
		return $this->belongsTo('Lara\Common\Models\Entity', 'related_entity_id');
	}

	/**
	 * @param Builder $query
	 * @param string $type
	 * @return Builder
	 */
	public function scopeTypeIs(Builder $query, string $type)
	{
		return $query->where('type', $type);
	}

}
