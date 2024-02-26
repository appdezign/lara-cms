<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Slider;
use Lara\Common\Models\Tag;

use LaravelLocalization;

use Lara\Front\Http\Traits\FrontEntityTrait;
use Lara\Front\Http\Traits\FrontTagTrait;


class SliderWidget extends AbstractWidget
{

	use FrontEntityTrait;
	use FrontTagTrait;

	protected $config = [
		'term'        => 'home',
		'grid'        => null,
		'sliderclass' => null,
	];

	public $cacheTime = false;

	public function __construct(array $config = [])
	{
		$this->cacheTime = config('lara-front.widget_cache_time');
		parent::__construct($config);
	}

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.sliderWidget.' . $this->config['term'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		$isMultiLanguage = config('lara.is_multi_language');

		$term = $this->config['term'];

		if($isMultiLanguage) {
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

			$widgetsliders = $modelClass::langIs($language)
				->isPublished()
				->has('media')
				->whereHas('tags', function ($query) use ($activeTerm) {
					$query->where(config('lara-common.database.object.tags') . '.slug', $activeTerm);
				})
				->orderBy($entity->getSortField(), $entity->getSortOrder())
				->get();

		} else {

			$widgetsliders = null;

		}

		$widgetview = '_widgets.slider.' . $this->config['term'];

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'        => $this->config,
				'grid'          => $this->config['grid'],
				'sliderclass'   => $this->config['sliderclass'],
				'widgetsliders' => $widgetsliders,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
