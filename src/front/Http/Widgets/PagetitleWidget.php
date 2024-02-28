<?php

namespace Lara\Front\Http\Widgets;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\View\View;
use Lara\Common\Models\Headertag;
use Lara\Common\Models\Slider;
use Lara\Common\Models\Tag;

use Lara\Common\Models\Templatewidget;
use Lara\Front\Http\Traits\FrontMenuTrait;
use Lara\Front\Http\Traits\FrontRoutesTrait;
use Lara\Front\Http\Traits\FrontTagTrait;

use LaravelLocalization;

class PagetitleWidget extends AbstractWidget
{

	use FrontMenuTrait;
	use FrontRoutesTrait;
	use FrontTagTrait;

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

		$isMultiLanguage = config('lara.is_multi_language');

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

			$widgetpagetitle = Slider::langIs($language)
				->isPublished()
				->has('media')
				->whereHas('tags', function ($query) use ($activeTerm) {
					$query->where(config('lara-common.database.object.tags') . '.slug', $activeTerm);
				})
				->inRandomOrder()->first();

		} else {

			$widgetpagetitle = null;

		}

		// identifier
		$templateFileName = $this->config['term'];

		// get or create template identifier
		$twidget = Templatewidget::where('type', 'pagetitlewidget')->where('widgetfile', $templateFileName)->first();
		if ($twidget) {
			$twidgetId = $twidget->id;
		} else {
			$newTwidget = Templatewidget::create([
				'type'       => 'pagetitlewidget',
				'widgetfile' => $templateFileName,
			]);
			$twidgetId = $newTwidget->id;
		}

		$headerTag = Headertag::select('id', 'title_tag', 'list_tag')->where('cgroup', 'pagetitlewidget')->where('templatewidget_id', $twidgetId)->first();

		$widgetview = '_widgets.pagetitle.' . $templateFileName;

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'          => $this->config,
				'grid'            => $this->config['grid'],
				'widgetpagetitle' => $widgetpagetitle,
				'menulevelone'    => $menulevelone,
				'menucurrent'     => $menucurrent,
				'headerTag'     => $headerTag,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
