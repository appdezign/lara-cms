<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Menuitem;

use Lara\Front\Http\Traits\LaraFrontHelpers;

use LaravelLocalization;

class BreadcrumbWidget extends AbstractWidget
{

	use LaraFrontHelpers;

	protected $config = [
		'lang' => 'nl',
		'grid' => null,
	];

	public $cacheTime = false;

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.breadcrumbWidget.' . $this->config['lang'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$activemenu = $this->getActiveMenuArray(true);

		$breadcrumb = array();

		foreach ($activemenu as $activeitem) {

			$menuitem = Menuitem::find($activeitem);

			$menuroute = url($this->config['lang'] . '/' . $menuitem->route);

			$breadcrumb[$menuitem->id]['title'] = $menuitem->title;
			$breadcrumb[$menuitem->id]['route'] = $menuroute;

		}

		$breadcrumb = array_reverse($breadcrumb);

		$widgetview = '_widgets.menu.breadcrumb';

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'     => $this->config,
				'grid'       => $this->config['grid'],
				'breadcrumb' => $breadcrumb,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
