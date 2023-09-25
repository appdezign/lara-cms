<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Cta;

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

		$widgetview = '_widgets.cta.' . $this->config['template'];

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'    => $this->config,
				'grid'      => $this->config['grid'],
				'widgetcta' => $widgetcta,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
