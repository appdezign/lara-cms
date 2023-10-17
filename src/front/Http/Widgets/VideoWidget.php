<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Video;
use Lara\Common\Models\Tag;

use LaravelLocalization;

use Lara\Front\Http\Traits\FrontEntityTrait;
use Lara\Front\Http\Traits\FrontRoutesTrait;
use Lara\Front\Http\Traits\FrontTagTrait;

class VideoWidget extends AbstractWidget
{

	use FrontEntityTrait;
	use FrontRoutesTrait;
	use FrontTagTrait;

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
		return 'lara.widgets.videoWidget.' . $this->config['term'];
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

			$entity = $this->getFrontEntityByKey('video');

			$modelClass = $entity->getEntityModelClass();

			$widgetvideo = $modelClass::langIs($language)
				->isPublished()
				->whereHas('tags', function ($query) use ($term) {
					$query->where(config('lara-common.database.object.tags') . '.slug', $term);
				})
				->orderBy($entity->getSortField(), $entity->getSortOrder())
				->first();

		} else {

			$widgetvideo = null;

		}

		$widgetview = '_widgets.video.' . $this->config['term'];

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'      => $this->config,
				'grid'        => $this->config['grid'],
				'widgetvideo' => $widgetvideo,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
