<?php

namespace Lara\Front\Http\Widgets;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Larawidget;

use Arrilot\Widgets\AbstractWidget;

use LaravelLocalization;

use Lara\Front\Http\Traits\LaraFrontHelpers;

class LaraEntityWidget extends AbstractWidget
{

	use LaraFrontHelpers;

	protected $config = [
		'widget_id' => null,
		'grid'      => null,
	];

	public $cacheTime = false; // do not cache entities like events

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.entity.' . $this->config['widget_id'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		$larawidget = Larawidget::find($this->config['widget_id']);

		$relentkey = $larawidget->relentkey;
		$entity = $this->getFrontEntityByKey($relentkey);

		if ($entity) {

			$term = ($larawidget->term != 'none') ? $larawidget->term : null;

			if ($term) {
				// get the full Tag object
				$taxonomy = $this->getFrontDefaultTaxonomy();
				$widgetTaxonomy = Tag::langIs($language)
					->entityIs($entity->getEntityKey())
					->taxonomyIs($taxonomy->id)
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

			if ($larawidget->imgreq) {
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

			if (is_numeric($larawidget->maxitems) && $larawidget->maxitems > 0) {
				$collection = $collection->limit($larawidget->maxitems);
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

		if ($larawidget->template) {
			$widgetview = '_widgets.lara.entity.' . $larawidget->template . '_' . $relentkey;
		} else {
			$widgetview = '_widgets.lara.entity.default_' . $relentkey;
		}

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'            => $this->config,
				'grid'              => $this->config['grid'],
				'widgetObjects'     => $widgetObjects,
				'widgetTaxonomy'    => $widgetTaxonomy,
				'widgetTaxonomies'  => $widgetTaxonomies,
				'widgetEntityRoute' => $widgetEntityRoute,
				'larawidget'        => $larawidget,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
