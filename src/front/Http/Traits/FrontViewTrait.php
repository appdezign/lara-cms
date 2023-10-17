<?php

namespace Lara\Front\Http\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\File;
use Qirolab\Theme\Theme;

trait FrontViewTrait
{

	/**
	 * Get the view file
	 *
	 * The view file is based on:
	 * - the entity key
	 * - the entity method
	 *
	 * @param object $entity
	 * @return string
	 */
	private function getFrontViewFile(object $entity)
	{

		$method = $entity->getMethod();

		$view = $entity->getViews()->where('method', $method)->first();

		$viewfile = $view->filename;

		$viewpath = 'content.' . $entity->getEntityKey() . '.' . $viewfile;

		$this->checkThemeViewFile($entity, $viewpath);

		return $viewpath;

	}

	/**
	 * @param object $entity
	 * @param string $viewpath
	 * @return void
	 */
	private function checkThemeViewFile(object $entity, string $viewpath)
	{

		if (config('app.env') != 'production') {

			$ds = DIRECTORY_SEPARATOR;

			$themeBasePath = config('theme.base_path');

			if (!view()->exists($viewpath)) {

				$defaultViewPath = config('theme.parent');
				$clientViewPath = config('theme.active');

				$srcDir = $themeBasePath . $ds . $defaultViewPath . $ds . 'views' . $ds . 'content' . $ds . '_templates' . $ds . $entity->getEgroup();
				$destDir = $themeBasePath . $ds . $clientViewPath . $ds . 'views' . $ds . $entity->getEntityKey() . $ds;
				$result = File::copyDirectory($srcDir, $destDir);
			}
		}

	}

	/**
	 * Get the default full layout of the theme
	 * from the special layout xml file
	 *
	 * The layout that is returned only contains
	 * the default options for the partials
	 *
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getDefaultThemeLayout()
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = $partial->key;

			foreach ($partial->items->item as $item) {
				if ($item->isDefault == 'true') {
					if ($item->partialFile == 'hidden') {
						$layout->$partial_key = false;
					} else {
						$layout->$partial_key = (string)$item->partialFile;
					}
				}
			}
		}

		return $layout;

	}

	/**
	 * Get the grid for this layout
	 *
	 * @param object $layout
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getGrid(object $layout)
	{

		$app = app();
		$grid = $app->make('stdClass');

		$bs = config('lara-front.lara_front_bootstrap_version');

		// default values
		$grid->module = 'module-sm';
		$grid->container = 'container';

		$grid->hasSidebar = 'has-no-sidebar';
		$grid->hasSidebarLeft = false;
		$grid->leftCols = 'hidden';
		$grid->hasSidebarRight = false;
		$grid->rightCols = 'hidden';

		$grid->contentCols = ($bs == 5) ? 'col-12' : 'col-sm-12';

		$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

		if (substr($layout->content, 0, 5) == 'boxed') {

			// boxed
			$grid->container = 'container';

			list($boxed, $sidebar, $type, $cols) = explode('_', $layout->content);

			$colcount = (int)$cols;

			if ($sidebar == 'default') {

				if ($type == 'col') {

					$gridcols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					if ($colcount < 12) {
						$offset = (12 - $colcount) / 2;
						$offsetcols = ($bs == 5) ? ' offset-lg-' . $offset : ' col-sm-offset-' . $offset;
					} else {
						$offsetcols = '';
					}

					$grid->gridColumns = $gridcols . $offsetcols;

				}

			} elseif ($sidebar == 'sidebar') {

				$grid->hasSidebar = 'has-sidebar';

				if ($type == 'left') {

					$grid->hasSidebarLeft = true;
					$grid->leftCols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					$contentcols = 12 - $colcount;
					$grid->contentCols = ($bs == 5) ? 'col-lg-' . $contentcols : 'col-sm-' . $contentcols;

					$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

				} elseif ($type == 'right') {

					$grid->hasSidebarRight = true;
					$grid->rightCols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					$contentcols = 12 - $colcount;
					$grid->contentCols = ($bs == 5) ? 'col-lg-' . (string)$contentcols : 'col-sm-' . (string)$contentcols;

					$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

				} elseif ($type == 'leftright') {
					// two sidebars

					$grid->hasSidebarLeft = true;
					$grid->leftCols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					$grid->hasSidebarRight = true;
					$grid->rightCols = ($bs == 5) ? 'col-lg-' . $cols : 'col-sm-' . $cols;

					$contentcols = 12 - (2 * $colcount);
					$grid->contentCols = ($bs == 5) ? 'col-lg-' . (string)$contentcols : 'col-sm-' . (string)$contentcols;

					$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

				} else {
					//
				}

			} else {
				// default
				$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';
			}

		} elseif (substr($layout->content, 0, 4) == 'full') {

			// full width
			$grid->container = 'container-fluid';
			$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';

		} else {

			// default
			$grid->container = 'container';
			$grid->gridColumns = ($bs == 5) ? 'col-12' : 'col-sm-12';
		}

		return $grid;

	}

	private function getGridVars($entity)
	{

		$varsFile = null;

		foreach (Theme::getViewPaths() as $themePath) {
			$gridVarsFile = $themePath . '/_grid/vars.php';
			if (file_exists($gridVarsFile)) {
				$varsFile = $gridVarsFile;
			}
		}

		return $varsFile;

	}

	/**
	 * @param $entity
	 * @return string|null
	 */
	private function getGridOverride($entity)
	{

		$override = null;

		foreach (Theme::getViewPaths() as $themePath) {
			$entityPath = $themePath . '/content/' . $entity->getEntityKey();
			$gridFile = $entityPath . '/' . $entity->getMethod() . '/_grid/vars.php';
			if (file_exists($gridFile)) {
				$override = $gridFile;
				break;
			}
		}

		return $override;

	}

	/**
	 * Get the custom layout for the current object
	 * The custom layout can override the default layout settings
	 *
	 * @param object $object
	 * @param object|null $params
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getObjectThemeLayout(object $object, $params = null)
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = (string)$partial->key;

			$found = false;

			// get custom layout value from database
			foreach ($object->layout as $item) {

				if ($item->layout_key == $partial_key) {
					if ($item->layout_value == 'hidden') {
						$layout->$partial_key = false;
					} else {
						$layout->$partial_key = $item->layout_value;
					}

					$found = true;

				}
			}

			if (!$found) {
				// get default value from layout XML
				foreach ($partial->items->item as $item) {
					if ($item->isDefault == 'true') {
						if ((string)$item->partialFile == 'hidden') {
							$layout->$partial_key = false;
						} else {
							$layout->$partial_key = (string)$item->partialFile;

						}
					}
				}
			}

		}

		if (!empty($params) && $params->showtags == 'filterbytaxonomy') {
			// force left sidebar for tag menu
			$layout->content = 'boxed_sidebar_left_3';
		}

		return $layout;

	}

}
