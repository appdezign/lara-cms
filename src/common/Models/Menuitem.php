<?php

namespace Lara\Common\Models;

use Cviebrock\EloquentSluggable\Sluggable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use Kalnoy\Nestedset\Collection;
use Kalnoy\Nestedset\NodeTrait;

class Menuitem extends Model
{

	use Sluggable, NodeTrait {
		NodeTrait::replicate insteadof Sluggable;
		Sluggable::replicate as replct;
	}

	/**
	 * @var string
	 */
	protected $table = 'lara_menu_menu_items';

	/**
	 * @var string[]
	 */
	protected $guarded = ['id'];

	// Baum nested set scope (deprecated)
	// protected $scoped = array('language', 'menu_id');

	// set table name
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

	/**
	 * get Table Name
	 *
	 * @return mixed
	 */
	public static function getTableName() {
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
	 * kalnoy/nestedset - scope
	 *
	 * @return array
	 */
	protected function getScopeAttributes()
	{
		return ['language', 'menu_id'];
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
	 * @return BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo('Lara\Common\Models\User')->select(array('id', 'name'));
	}

	/**
	 * @return BelongsTo
	 */
	public function language()
	{
		return $this->belongsTo('Lara\Common\Models\Language');
	}

	/**
	 * @return BelongsTo
	 */
	public function menu()
	{
		return $this->belongsTo('Lara\Common\Models\Menu', 'menu_id');
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
	public function entityview()
	{
		return $this->belongsTo('Lara\Common\Models\EntityView', 'entity_view_id');
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
	 * @param int $menu_id
	 * @return Builder
	 */
	public function scopeMenuIs(Builder $query, int $menu_id)
	{
		return $query->where('menu_id', $menu_id);
	}

	/**
	 * @param Builder $query
	 * @param string $slug
	 * @return Builder
	 */
	public function scopeMenuSlugIs(Builder $query, string $slug)
	{
		return $query->whereHas('menu', function ($query) use ($slug) {
			$query->where(config('lara-common.database.menu.menus') . '.slug', $slug);
		});
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

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeIsPublished(Builder $query)
	{
		return $query->where('publish', 1);
	}

	/**
	 * @param object|null $node
	 * @return string
	 */
	public static function renderNode(object $node = null)
	{

		$html = '<ul>';

		if ($node->isLeaf()) {
			$html .= '<li>' . $node->name . '</li>';
		} else {
			$html .= '<li>' . $node->name;

			$html .= '<ul>';

			foreach ($node->children as $child) {
				$html .= self::renderNode($child);
			}

			$html .= '</ul>';

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
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
