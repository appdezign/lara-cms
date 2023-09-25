<?php

namespace Lara\Front\Http\Widgets;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\View\View;
use Lara\Common\Models\Slider;
use Lara\Common\Models\Tag;

use Lara\Front\Http\Traits\LaraFrontHelpers;

use LaravelLocalization;

class PagetitleWidget extends AbstractWidget
{

	use LaraFrontHelpers;

	protected $config = [
		'term' => 'pagetitle',
		'grid'           => null,
	];

	public $cacheTime = false;  // DO NOT CACHE THE PAGE TITLE !!!

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.pageWidget.' . $this->config['term'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		// Pass the active menu (current and level ) array to the widget view
		$activemenu = $this->getActiveMenuArray(false);

		if (!empty($activemenu)) {
			if (count($activemenu) > 1) {
				// get second to last item (= level one)
				$menulevelone = $activemenu[count($activemenu) - 2];
			} else {
				// home
				$menulevelone = $activemenu[0];
			}
			$menucurrent = $activemenu[0];
		} else {
			$menulevelone = null;
			$menucurrent = null;
		}

		$term = $this->config['term'];

		$taxonomy = $this->getFrontDefaultTaxonomy();
		$tag = Tag::langIs($language)
			->entityIs('slider')
			->taxonomyIs($taxonomy->id)
			->where('slug', $term)->first();

		if ($tag) {

			$widgetpagetitle = Slider::langIs($language)
				->isPublished()
				->has('media')
				->whereHas('tags', function ($query) use ($term) {
					$query->where(config('lara-common.database.object.tags') . '.slug', $term);
				})
				->inRandomOrder()->first();

		} else {

			$widgetpagetitle = null;

		}

		$widgetview = '_widgets.pagetitle.' . $this->config['term'];

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'          => $this->config,
				'grid'            => $this->config['grid'],
				'widgetpagetitle' => $widgetpagetitle,
				'menulevelone'    => $menulevelone,
				'menucurrent'     => $menucurrent,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
