<?php

namespace Lara\Front\Http\Traits;

trait FrontThemeTrait
{

	private function getFrontTheme()
	{

		if (config('lara.client_theme')) {
			$theme = config('lara.client_theme');
		} else {
			// Fallback
			$theme = 'demo';
		}

		return $theme;

	}

	function getParentTheme()
	{
		return config('theme.parent');
	}


}
