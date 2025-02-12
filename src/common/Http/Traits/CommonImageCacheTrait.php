<?php

namespace Lara\Common\Http\Traits;

use Illuminate\Support\Facades\File;

trait CommonImageCacheTrait
{

	private function purgeImageChache()
	{
		return File::cleanDirectory(storage_path('imgcache'));
	}

}