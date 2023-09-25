<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Artisan;

use Cviebrock\EloquentSluggable\Sluggable;

use Rutorika\Sortable\SortableTrait;

use Carbon\Carbon;

class Page extends EntityBaseModel
{

	use Sluggable;

	use SortableTrait;

	/**
	 * @var string
	 */
	protected $table = 'lara_content_pages';

	/**
	 * @var string[]
	 */
	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
		'deleted_at',
	];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

	protected static function boot()
	{
		parent::boot();
		static::saving(function () {
			Artisan::call('httpcache:clear');
		});
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
	public function languageParent()
	{
		return $this->belongsTo('Lara\Common\Models\Page', 'language_parent');
	}

	/**
	 * @return HasMany
	 */
	public function languageChildren()
	{
		return $this->hasMany('Lara\Common\Models\Page', 'language_parent');
	}

	/**
	 * @return MorphToMany
	 */
	public function widgets()
	{
		return $this->morphedByMany('Lara\Common\Models\Larawidget', 'entity', config('lara-common.database.object.pageables'))
			->where('isglobal', 0)
			->orderBy('hook', 'asc')
			->orderBy('sortorder', 'asc');
	}

}
