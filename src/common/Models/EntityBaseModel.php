<?php

namespace Lara\Common\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

use Lara\Common\Http\Traits\CommonTrait;


use Carbon\Carbon;

class EntityBaseModel extends Model
{

	use SoftDeletes;
	use CommonTrait;

	/**
	 * @var string[]
	 */
	protected $guarded = ['id'];

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'deleted_at' => 'datetime',
		'locked_at' => 'datetime',
		'publish_from' => 'datetime',
		'publish_to' => 'datetime',
	];

	protected static function boot()
	{
		parent::boot();
		static::saving(function () {
			Artisan::call('httpcache:clear');
		});
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
	 * get Table Columns
	 *
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
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
	 * @return BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo('Lara\Common\Models\User')->withTrashed();
	}

	/**
	 * @return BelongsTo
	 */
	public function language()
	{
		return $this->belongsTo('Lara\Common\Models\Language');
	}

	/**
	 * @return MorphToMany
	 */
	public function tags()
	{
		return $this->morphToMany('Lara\Common\Models\Tag', 'entity', config('lara-common.database.object.taggables'))->orderBy('taxonomy_id', 'asc')->orderBy('depth', 'asc');
	}

	/**
	 * @return mixed|null
	 */
	public function getFirstTagAttribute()
	{
		return $this->tags()->first();
	}

	/**
	 * @return MorphMany
	 */
	public function layout()
	{
		return $this->morphMany('Lara\Common\Models\Layout', 'entity');
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
	public function hasIcon()
	{
		$count = $this->media->where('isicon', 1)->count();
		return boolval($count);
	}

	/**
	 * @return mixed
	 */
	public function getIconAttribute()
	{
		return $this->media->where('isicon', 1)->first();
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
		return $this->media->where('hide_in_gallery', 0);
	}

	/**
	 * @return bool
	 */
	public function heroIsFeatured() : bool
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
	 * @return bool
	 */
	public function iconIsFeatured() : bool
	{
		$icon = $this->media->where('isicon', 1)->first();

		if ($icon) {
			$featured = $this->media->where('featured', 1)->first();
			if ($featured) {
				if ($icon->id == $featured->id) {
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
	 * @return MorphMany
	 */
	public function files()
	{
		return $this->morphMany('Lara\Common\Models\MediaFile', 'entity');
	}

	/**
	 * @return bool
	 */
	public function hasFiles()
	{
		$count = $this->files->count();
		return boolval($count);
	}

	/**
	 * @return mixed
	 */
	public function getFirstfileAttribute()
	{
		return $this->files->first();
	}

	/**
	 * @return MorphMany
	 */
	public function videos()
	{
		// we use 'orderby featured desc',
		// so the first video is always the featured image
		return $this->morphMany('Lara\Common\Models\MediaVideo', 'entity')->orderBy('featured', 'desc');
	}

	/**
	 * @return bool
	 */
	public function hasVideos()
	{
		$count = $this->videos->count();
		return boolval($count);
	}

	/**
	 * @return mixed
	 */
	public function getVideoAttribute()
	{
		return $this->videos->first();
	}

	/**
	 * @return MorphMany
	 */
	public function videofiles()
	{
		return $this->morphMany('Lara\Common\Models\MediaVideoFile', 'entity');
	}

	/**
	 * @return bool
	 */
	public function hasVideoFiles()
	{
		$count = $this->videofiles->count();
		return boolval($count);
	}

	/**
	 * @return mixed
	 */
	public function getVideofileAttribute()
	{
		return $this->videofiles->first();
	}

	/**
	 * @return MorphOne
	 */
	public function sync()
	{
		return $this->morphOne('Lara\Common\Models\Sync', 'entity');
	}

	/**
	 * @return MorphOne
	 */
	public function seo()
	{
		return $this->morphOne('Lara\Common\Models\ObjectSeo', 'entity');
	}

	/**
	 * @return MorphOne
	 */
	public function opengraph()
	{
		return $this->morphOne('Lara\Common\Models\ObjectOpengraph', 'entity');
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
	 * Publish scope.
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeIsPublished(Builder $query)
	{
		return $query->where('publish', 1)
			->whereDate('publish_from', '<', Carbon::now()->toDateTimeString());
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeIsNotExpired(Builder $query)
	{
		return $query->where(function ($query) {
			$query->whereDate('publish_to', '>', Carbon::now()->toDateTimeString())
				->orWhereNull('publish_to');
		});
	}

	/**
	 * ContentGroup scope.
	 *
	 * @param Builder $query
	 * @param string $cgroup
	 * @return Builder
	 */
	public function scopeGroupIs(Builder $query, string $cgroup)
	{
		return $query->where('cgroup', $cgroup);
	}

	/**
	 * @param string|null $date
	 * @return void
	 * @throws Exception
	 */
	public function setPublishFromAttribute(string $date = null)
	{
		if (!is_null($date)) {
			$this->attributes['publish_from'] = Carbon::parse($date);
		} else {
			$this->attributes['publish_from'] = null;
		}
	}

	/**
	 * @param string|null $date
	 * @return void
	 * @throws Exception
	 */
	public function setPublishToAttribute(string $date = null)
	{
		if (!is_null($date)) {
			$this->attributes['publish_to'] = Carbon::parse($date);
		} else {
			$this->attributes['publish_to'] = null;
		}
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
