<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Slider;
use Lara\Common\Models\Tag;

use LaravelLocalization;

use Lara\Front\Http\Traits\LaraFrontHelpers;

class SliderWidget extends AbstractWidget
{

	use LaraFrontHelpers;

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

		$term = $this->config['term'];

		$taxonomy = $this->getFrontDefaultTaxonomy();
		$tag = Tag::langIs($language)
			->entityIs('slider')
			->taxonomyIs($taxonomy->id)
			->where('slug', $term)->first();

		if ($tag) {

			$entity = $this->getFrontEntityByKey('slider');

			$modelClass = $entity->getEntityModelClass();

			$widgetsliders = $modelClass::langIs($language)
				->isPublished()
				->has('media')
				->whereHas('tags', function ($query) use ($term) {
					$query->where(config('lara-common.database.object.tags') . '.slug', $term);
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
