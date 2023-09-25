<?php

namespace Lara\Front\Http\Widgets;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Tag;

use Arrilot\Widgets\AbstractWidget;

use LaravelLocalization;

use Lara\Front\Http\Traits\LaraFrontHelpers;

use Carbon\Carbon;

class EntityCacheWidget extends AbstractWidget
{

	use LaraFrontHelpers;

	protected $config = [
		'entity_key'  => null,
		'parent'      => null,
		'term'        => null,
		'needs_image' => true,
		'count'       => 0,
		'title'       => null,
		'grid'        => null,
		'sortfield'   => null,
		'sortorder'   => null,
		'exclude'     => null,
		'since'       => null,
	];

	public $cacheTime = false;

	public function __construct(array $config = [])
	{
		$this->cacheTime = config('lara-front.widget_cache_time');
		parent::__construct($config);
	}

	public function cacheKey(array $params = [])
	{

		$cachekey = 'lara.widgets.entity.' . $this->config['parent'] . '.' . $this->config['entity_key'];

		if ($this->config['term']) {
			$cachekey = $cachekey . '.' . $this->config['term'];
		}

		if ($this->config['sortfield']) {
			$cachekey = $cachekey . '.' . $this->config['sortfield'];
		}

		return $cachekey;

	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		$entity = $this->getFrontEntityByKey($this->config['entity_key']);

		if ($entity) {

			$term = $this->config['term'];

			if ($term) {
				// get the full Tag object
				$widgetTaxonomy = Tag::langIs($language)
					->entityIs($entity->getEntityKey())
					->where('slug', $term)->first();
				if (empty($widgetTaxonomy)) {
					$term = null;
				}
			} else {
				$widgetTaxonomy = null;
			}

			// start collection
			$modelClass = $entity->getEntityModelClass();
			$collection = new $modelClass;

			if ($this->config['exclude']) {
				$collection = $collection->where('id', '!=', $this->config['exclude']);
			}

			if ($this->config['since']) {
				$days = $this->config['since'];
				$collection = $collection->where('created_at', '>', Carbon::now()->subDays($days)->toDateTimeString());
			}

			if ($entity->hasLanguage()) {
				$collection = $collection->langIs($language);
			}

			if ($entity->hasStatus()) {
				$collection = $collection->isPublished();
			}

			if ($entity->hasHideinlist()) {
				$collection = $collection->where('publish_hide', 0);
			}

			if ($entity->hasExpiration()) {
				$collection = $collection->isNotExpired();
			}

			if (method_exists($modelClass, 'scopeFront')) {
				$collection = $collection->front();
			}

			if ($this->config['needs_image']) {
				$collection = $collection->has('media');
			}

			if ($entity->hasImages()) {
				$collection = $collection->with('media');
			}

			if ($term) {
				$collection = $collection->whereHas('tags', function ($query) use ($term) {
					$query->where(config('lara-common.database.object.tags') . '.slug', $term);
				});

			} else {
				$collection = $collection->with([
					'tags' => function ($query) use ($entity) {
						$query->where(config('lara-common.database.object.tags') . '.entity_key', $entity->getEntityKey());
					},
				]);
			}

			if ($this->config['sortfield'] && $this->config['sortorder']) {

				$collection = $collection->orderBy($this->config['sortfield'], $this->config['sortorder']);

			} else {

				foreach ($entity->getCustomColumns() as $field) {
					if ($field->fieldname == 'sticky') {
						$collection = $collection->orderBy('sticky', 'desc');
					}
				}
				if ($entity->getSortField()) {
					$collection = $collection->orderBy($entity->getSortField(), $entity->getSortOrder());
				}
				if ($entity->getSortField2nd()) {
					$collection = $collection->orderBy($entity->getSortField2nd(), $entity->getSortOrder2nd());
				}
			}

			if (is_numeric($this->config['count']) && $this->config['count'] > 0) {
				$collection = $collection->limit($this->config['count']);
			}

			// get collection
			$widgetObjects = $collection->get();

			// get all tags
			if ($entity->hasTags()) {
				$widgetTaxonomies = $this->getTagsFromCollection($language, $entity, $widgetObjects);
			} else {
				$widgetTaxonomies = null;
			}

			$widgetEntityRoute = $this->getFrontSeoRoute($entity->getEntityKey(), 'index');

		} else {

			$widgetObjects = null;
			$widgetTaxonomy = null;
			$widgetTaxonomies = null;
			$widgetEntityRoute = null;

		}

		$widgetview = '_widgets.entity.' . $this->config['parent'] . '_' . $this->config['entity_key'];

		if (view()->exists($widgetview)) {

			return view($widgetview, [
				'config'            => $this->config,
				'grid'              => $this->config['grid'],
				'widgetObjects'     => $widgetObjects,
				'widgetTaxonomy'    => $widgetTaxonomy,
				'widgetTaxonomies'  => $widgetTaxonomies,
				'widgetEntityRoute' => $widgetEntityRoute,
				'widgetTitle'       => $this->config['title'],
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
