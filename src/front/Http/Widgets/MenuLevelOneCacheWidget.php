<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;

use LaravelLocalization;

class MenuLevelOneCacheWidget extends AbstractWidget
{

	protected $config = [
		'mnu'  => 'main',
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
		return 'lara.widgets.menuLevelOneWidget.' . $this->config['mnu'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		$menu = Menu::where('slug', $this->config['mnu'])->first();

		if ($menu) {

			// find root first
			$root = Menuitem::langIs($language)
				->menuIs($menu->id)
				->whereNull('parent_id')
				->first();

			// get children
			$menulevelone = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $language])
				->defaultOrder()
				->withDepth()
				->having('depth', '=', 1)
				->where('publish', 1)
				->descendantsOf($root->id)
				->toTree();

		} else {

			$menulevelone = null;

		}

		$widgetview = '_widgets.menu-level-one.' . $this->config['mnu'];

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'       => $this->config,
				'grid'         => $this->config['grid'],
				'menulevelone' => $menulevelone,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
