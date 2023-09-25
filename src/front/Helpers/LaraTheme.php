<?php

namespace Lara\Front\Helpers;

use Qirolab\Theme\Theme;

class LaraTheme
{
    public function url($str = null): ?string
    {

		list($cleanStr) = explode('?', $str);

        $themePath = config('lara-front.asset_theme_path');

        $active = Theme::active();
        $parent = Theme::parent();

        // active
        $activeUrl =  $themePath . $active . '/' . $str;
        $activePath = (public_path($themePath . $active . '/' . $cleanStr));

        // parent
        $parentUrl =  $themePath . $parent . '/' . $str;
        $parentPath = (public_path($themePath . $parent . '/' . $cleanStr));


        if(file_exists($activePath)) {
            return asset($activeUrl);
        } elseif(file_exists($parentPath)) {
            return asset($parentUrl);
        } else {
            return null;
        }

    }

    /**
     * Return css link for $href
     *
     * @param  string $href
     * @return string
     */
    public function css($href)
    {
        return sprintf('<link media="all" type="text/css" rel="stylesheet" href="%s">', $this->url($href));
    }

    /**
     * Return script link for $href
     *
     * @param  string $href
     * @return string
     */
    public function js($href)
    {
        return sprintf('<script src="%s"></script>', $this->url($href));
    }

	/**
	 * Return img tag
	 *
	 * @param  string $src
	 * @param  string $alt
	 * @param  string $Class
	 * @param  array $attributes
	 * @return string
	 */
	public function img($src, $alt = '', $class = '', $attributes = [])
	{
		return sprintf('<img src="%s" alt="%s" class="%s" %s>',
			$this->url($src),
			$alt,
			$class,
			$this->HtmlAttributes($attributes)
		);
	}

	/**
	 * Return attributes in html format
	 *
	 * @param  array $attributes
	 * @return string
	 */
	private function HtmlAttributes($attributes)
	{
		$formatted = join(' ', array_map(function ($key) use ($attributes) {
			if (is_bool($attributes[$key])) {
				return $attributes[$key] ? $key : '';
			}
			return $key . '="' . $attributes[$key] . '"';
		}, array_keys($attributes)));
		return $formatted;
	}


}
