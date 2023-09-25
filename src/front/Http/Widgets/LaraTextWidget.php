<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Larawidget;

class LaraTextWidget extends AbstractWidget
{

	protected $config = [
		'widget_id' => null,
		'grid'      => null,
	];

	public $cacheTime = false;

	public function __construct(array $config = [])
	{
		$this->cacheTime = config('lara-front.widget_cache_time');
		parent::__construct($config);
	}

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.textWidget.' . $this->config['widget_id'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$larawidget = Larawidget::find($this->config['widget_id']);

		if ($larawidget->template) {
			$widgetview = '_widgets.lara.text.' . $larawidget->type . '_' . $larawidget->template;
		} else {
			$widgetview = '_widgets.lara.text.' . $larawidget->type . '_default';
		}

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'     => $this->config,
				'grid'       => $this->config['grid'],
				'larawidget' => $larawidget,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
