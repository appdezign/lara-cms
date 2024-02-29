<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Slider;
use Lara\Common\Models\Tag;

use LaravelLocalization;

class ParallaxWidget extends AbstractWidget
{

	protected $config = [
		'term' => 'home',
		'grid' => null,
	];

	public $cacheTime = false;

	public function __construct(array $config = [])
	{
		$this->cacheTime = config('lara-front.widget_cache_time');
		parent::__construct($config);
	}

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.parallaxWidget.' . $this->config['term'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		$isMultiLanguage = config('lara.is_multi_language');

		$term = $this->config['term'];

		if ($isMultiLanguage) {
			$activeTerm = $term . '-' . $language;
		} else {
			$activeTerm = $term;
		}

		$taxonomy = $this->getFrontDefaultTaxonomy();
		$tag = Tag::langIs($language)
			->entityIs('slider')
			->taxonomyIs($taxonomy->id)
			->where('slug', $activeTerm)->first();

		if ($tag) {

			$entity = $this->getFrontEntityByKey('slider');
			$modelClass = $entity->getEntityModelClass();

			// get the first slider
			$widgetparallax = $modelClass::langIs($language)
				->isPublished()
				->has('media')
				->whereHas('tags', function ($query) use ($activeTerm) {
					$query->where(config('lara-common.database.object.tags') . '.slug', $activeTerm);
				})
				->orderBy($entity->getSortField(), $entity->getSortOrder())
				->first();

		} else {

			$widgetparallax = null;

		}

		// identifier
		$templateFileName = $this->config['term'];

		$headerTag = $this->getWidgetHeaderTag($templateFileName, 'sliderwidget');

		$widgetview = '_widgets.parallax.' . $templateFileName;

		if (view()->exists($widgetview)) {

			return view($widgetview, [
				'config'         => $this->config,
				'grid'           => $this->config['grid'],
				'widgetparallax' => $widgetparallax,
				'headerTag'      => $headerTag,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';

			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
