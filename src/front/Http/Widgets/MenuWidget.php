<?php

namespace Lara\Front\Http\Widgets;

use Arrilot\Widgets\AbstractWidget;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;

use Lara\Front\Http\Traits\FrontTrait;
use Lara\Front\Http\Traits\FrontMenuTrait;
use Lara\Front\Http\Traits\FrontRoutesTrait;

use LaravelLocalization;

class MenuWidget extends AbstractWidget
{

	use FrontTrait;
	use FrontMenuTrait;
	use FrontRoutesTrait;

	protected $config = [
		'mnu'      => 'main',
		'showroot' => false,
		'grid'     => null,
		'template' => 'menu',
	];

	public $cacheTime = false; // DO NOT CACHE THE MENU !!!

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.menuWidget.' . $this->config['mnu'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		$activemenu = $this->getActiveMenuArray(true);

		$menu = Menu::where('slug', $this->config['mnu'])->first();

		if ($menu) {


			if ($this->config['showroot']) {

				// kalnoy/nestedset
				$tree = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $language])
					->defaultOrder()
					->where('publish', 1)
					->get()
					->toTree();

			} else {

				// find root first
				$root = Menuitem::langIs($language)
					->menuIs($menu->id)
					->whereNull('parent_id')
					->first();

				// get children
				$tree = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $language])
					->defaultOrder()
					->where('publish', 1)
					->descendantsOf($root->id)
					->toTree();
			}

		} else {

			$tree = null;

		}

		$widgetview = '_widgets.' . $this->config['template'] . '.' . $this->config['mnu'];

		if(view()->exists($widgetview)) {

			return view($widgetview, [
				'config'     => $this->config,
				'grid'       => $this->config['grid'],
				'tree'       => $tree,
				'activemenu' => $activemenu,
			]);

		} else {
			$errorView = (config('app.env') == 'production') ? 'not_found_prod' : 'not_found';
			return view('_widgets._error.' . $errorView, [
				'widgetview' => $widgetview,
			]);
		}

	}

}
