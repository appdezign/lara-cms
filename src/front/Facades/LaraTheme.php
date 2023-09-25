<?php

namespace Lara\Front\Facades;

class LaraTheme extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return \Lara\Front\Helpers\LaraTheme::class;
    }
}
