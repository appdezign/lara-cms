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

class MenuSubCacheWidget extends AbstractWidget
{

	use FrontTrait;
	use FrontMenuTrait;
	use FrontRoutesTrait;

	protected $config = [
		'mnu'   => 'main',
		'slug'  => 'products',
		'depth' => 0,
		'force' => 'false',
		'grid'  => null,
	];

	public $cacheTime = false;

	public function __construct(array $config = [])
	{
		$this->cacheTime = config('lara-front.widget_cache_time');
		parent::__construct($config);
	}

	public function cacheKey(array $params = [])
	{
		return 'lara.widgets.menuSubWidget.' . $this->config['slug'];
	}

	/**
	 * @return Application|Factory|View
	 */
	public function run()
	{

		$language = LaravelLocalization::getCurrentLocale();

		$activemenu = $this->getActiveMenuArray(true);

		$menu = Menu::where('slug', $this->config['mnu'])->first();

		if (!empty($menu)) {

			// find subroot first
			$subroot = Menuitem::langIs($language)
				->menuIs($menu->id)
				->where('slug', $this->config['slug'])
				->first();

			if ($this->config['depth'] == 1) {

				$depth = $subroot->depth + 1;

				// get children of subroot
				$tree = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $language])
					->defaultOrder()
					->withDepth()
					->having('depth', '=', $depth)
					->where('publish', 1)
					->descendantsOf($subroot->id)
					->toTree();

			} else {

				// get children of subroot
				$tree = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $language])
					->defaultOrder()
					->where('publish', 1)
					->descendantsOf($subroot->id)
					->toTree();

			}

		} else {

			$tree = null;

		}

		$widgetview = '_widgets.menu.sub';

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
