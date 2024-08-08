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

class MenuSubWidget extends AbstractWidget
{

	use FrontTrait;
	use FrontMenuTrait;
	use FrontRoutesTrait;

	protected $config = [
		'mnu'   => 'main',
		'slug'  => 'products',
		'depth' => 0,
		'force' => false,
		'grid'  => null,
	];

	public $cacheTime = false;

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

		$objectStatusArray = ($this->config['force']) ? [1, 0] : [1];

		if ($menu) {

			// find subroot first
			if(is_numeric($this->config['slug'])) {
				// find by ID
				$menuId = $this->config['slug'];
				$subroot = Menuitem::find($menuId);
			} else {
				// find by slug
				$subroot = Menuitem::langIs($language)
					->menuIs($menu->id)
					->where('slug', $this->config['slug'])
					->first();
			}

			if($subroot) {

				if ($this->config['depth'] == 1) {

					$depth = $subroot->depth + 1;

					// get children of subroot
					$tree = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $language])
						->defaultOrder()
						->withDepth()
						->having('depth', '=', $depth)
						->whereIn('publish', $objectStatusArray)
						->descendantsOf($subroot->id)
						->toTree();

				} else {

					// get children of subroot
					$tree = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $language])
						->defaultOrder()
						->whereIn('publish', $objectStatusArray)
						->descendantsOf($subroot->id)
						->toTree();

				}

			} else {
				$tree = null;
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
