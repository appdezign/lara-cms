<?php

namespace Lara\Front\LaraTheme\Facade;

use Illuminate\Support\Facades\Facade;
use Lara\Front\LaraTheme\Helpers\LaraThemeHelpers;

class LaraTheme extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LaraThemeHelpers::class;
    }
}
