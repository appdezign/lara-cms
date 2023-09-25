<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;

use Illuminate\Database\Eloquent\Model;

use Kalnoy\Nestedset\Collection;
use Kalnoy\Nestedset\NodeTrait;

class Tag extends Model
{

	use Sluggable, NodeTrait {
		NodeTrait::replicate insteadof Sluggable;
		Sluggable::replicate as replct;
	}

	/**
	 * @var string
	 */
	protected $table = 'lara_object_tags';

	/**
	 * @var string[]
	 */
	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
	];

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'locked_at' => 'datetime',
	];

	// Baum nested set scope (deprecated)
	//  protected $scoped = array('language', 'entity_key');

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
	 * kalnoy/nestedset - scope
	 *
	 * @return string[]
	 */
	protected function getScopeAttributes()
	{
		return ['language', 'entity_key', 'taxonomy_id'];
	}

	/**
	 * @return BelongsTo
	 */
	public function taxonomy()
	{
		return $this->belongsTo('Lara\Common\Models\Taxonomy', 'taxonomy_id');
	}

	/**
	 * kalnoy/nestedset - column override (_lft)
	 *
	 * @return string
	 */
	public function getLftName()
	{
		return 'lft';
	}

	/**
	 * kalnoy/nestedset - column override (_rgt)
	 *
	 * @return string
	 */
	public function getRgtName()
	{
		return 'rgt';
	}

	/**
	 * @return array
	 */
	public function sluggable(): array
	{
		return [
			'slug' => [
				'source' => 'title',
			],
		];
	}

	/**
	 * @return MorphMany
	 */
	public function media()
	{
		return $this->morphMany('Lara\Common\Models\MediaImage', 'entity')->orderBy('position');
	}

	/**
	 * @return bool
	 */
	public function hasMedia()
	{
		$count = $this->media->count();
		return boolval($count);
	}

	/**
	 * Featured (legacy)
	 * @return mixed
	 */
	public function getFirstimgAttribute()
	{
		return $this->media->first();
	}

	/**
	 * @return bool
	 */
	public function hasFeatured()
	{
		$count = $this->media->where('featured', 1)->count();
		return boolval($count);
	}

	/**
	 * @return mixed
	 */
	public function getFeaturedAttribute()
	{
		return $this->media->where('featured', 1)->first();
	}

	/**
	 * @return bool
	 */
	public function hasHero()
	{
		$count = $this->media->where('ishero', 1)->count();
		return boolval($count);
	}

	/**
	 * @return mixed
	 */
	public function getHeroAttribute()
	{
		return $this->media->where('ishero', 1)->first();
	}

	/**
	 * @return bool
	 */
	public function hasGallery()
	{
		$count = $this->media->where('featured', 0)->where('ishero', 0)->count();
		return boolval($count);
	}

	/**
	 * @return mixed
	 */
	public function getGalleryAttribute()
	{
		return $this->media->where('featured', 0)->where('ishero', 0);
	}

	/**
	 * @return bool
	 */
	public function heroIsFeatured()
	{
		$hero = $this->media->where('ishero', 1)->first();

		if ($hero) {
			$featured = $this->media->where('featured', 1)->first();
			if ($featured) {
				if ($hero->id == $featured->id) {
					return true;
				} else {
					return false;
				}

			} else {
				return false;
			}
		} else {
			return false;
		}

	}
	/**
	 * @return MorphOne
	 */
	public function seo()
	{
		return $this->morphOne('Lara\Common\Models\ObjectSeo', 'entity');
	}

	/**
	 * @param Builder $query
	 * @param string $entity_key
	 * @return Builder
	 */
	public function scopeEntityIs(Builder $query, string $entity_key)
	{
		return $query->where('entity_key', $entity_key);
	}

	/**
	 * @param Builder $query
	 * @param int $taxonomyId
	 * @return Builder
	 */
	public function scopeTaxonomyIs(Builder $query, int $taxonomyId)
	{
		return $query->where('taxonomy_id', $taxonomyId);
	}

	/**
	 * Language scope.
	 *
	 * @param Builder $query
	 * @param string $language
	 * @return Builder
	 */
	public function scopeLangIs(Builder $query, string $language)
	{
		return $query->where('language', $language);
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
