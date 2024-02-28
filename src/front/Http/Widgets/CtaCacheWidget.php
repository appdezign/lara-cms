<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Cta;

use Lara\Common\Models\Headertag;
use Lara\Common\Models\Templatewidget;
use LaravelLocalization;

class CtaCacheWidget extends AbstractWidget
{

	protected $config = [
		'hook'     => null,
		'template' => 'default',
		'grid'     => null,
	];

	public $cacheTime = false;

	public function __construct(array $config = [])
	{
		$this->cacheTime = config('lara-front.widget_cache_time');
		parent::__construct($config);
	}

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.ctaWidget.' . $this->config['hook'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		$widgetcta = Cta::langIs($language)->where('hook', $this->config['hook'])->first();

		// identifier
		$templateFileName = $this->config['template'];

		// get or create template identifier
		$twidget = Templatewidget::where('type', 'ctawidget')->where('widgetfile', $templateFileName)->first();
		if ($twidget) {
			$twidgetId = $twidget->id;
		} else {
			$newTwidget = Templatewidget::create([
				'type'       => 'ctawidget',
				'widgetfile' => $templateFileName,
			]);
			$twidgetId = $newTwidget->id;
		}

		$headerTag = Headertag::select('id', 'title_tag', 'list_tag')->where('cgroup', 'ctawidget')->where('templatewidget_id', $twidgetId)->first();


		$widgetview = '_widgets.cta.' . $templateFileName;

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'    => $this->config,
				'grid'      => $this->config['grid'],
				'widgetcta' => $widgetcta,
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
