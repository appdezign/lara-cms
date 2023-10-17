<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Qirolab\Theme\Theme;

trait AdminThemeTrait
{

	private function getActiveFrontTheme()
	{

		if (config('lara.client_theme')) {
			$theme = config('lara.client_theme');
		} else {
			// Fallback
			$theme = 'demo';
		}

		return $theme;

	}

	function getParentFrontTheme()
	{
		return config('theme.parent');
	}

	/**
	 * Get the default full layout of the theme
	 * from the special layout xml file
	 *
	 * The layout that is returned
	 * contains all options for all partials
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getFullLayout()
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = $partial->key;

			$layout->$partial_key = $app->make('stdClass');

			foreach ($partial->items->item as $item) {

				$item_key = (string)$item->itemKey;

				$layout->$partial_key->$item_key = $app->make('stdClass');

				$layout->$partial_key->$item_key->friendlyName = (string)$item->friendlyName;
				$layout->$partial_key->$item_key->partialFile = (string)$item->partialFile;
				$layout->$partial_key->$item_key->isDefault = (string)$item->isDefault;
			}

		}

		return $layout;

	}

}

