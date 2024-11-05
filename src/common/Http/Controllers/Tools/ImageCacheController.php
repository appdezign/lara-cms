<?php

namespace Lara\Common\Http\Controllers\Tools;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\Laravel\Facades\Image;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response as IlluminateResponse;
use Config;

class ImageCacheController extends BaseController
{

	/**
	 * @var int|null
	 */
	protected $width = 960;

	/**
	 * @var int|null
	 */
	protected $height = 0; // auto height

	/**
	 * @var int
	 */
	protected $quality = 100;

	/**
	 * @var int
	 */
	protected $fit = 1;

	/**
	 * @var string
	 */
	protected $fitpos = 'center';

	/**
	 * @var array
	 */
	protected $positions = [
		'top-left',
		'top',
		'top-right',
		'left',
		'center',
		'right',
		'bottom-left',
		'bottom',
		'bottom-right',
	];

	/**
	 * @param $width
	 * @param $height
	 * @param int $fit
	 * @param string $fitpos
	 * @param int $quality
	 * @param string $filename
	 * @return IlluminateResponse
	 */
	public function process($width = null, $height = null, int $fit, string $fitpos, int $quality, string $filename)
	{

		$cachedPath = storage_path('imgcache/x' . $width . '/y' . $height . '/f' . $fit . '/' . $fitpos . '/' . $filename);

		// check cached version
		if (file_exists($cachedPath) && is_file($cachedPath)) {
			$mime = mime_content_type($cachedPath);
			$content = file_get_contents($cachedPath);
			return new IlluminateResponse($content, 200, array(
				'Content-Type' => $mime,
			));
		} else {

			$sourcePath = $this->getImagePath($filename);

			// exclude gifs
			$mime = mime_content_type($sourcePath);
			if ($mime == 'image/gif') {
				$content = file_get_contents($sourcePath);
				return new IlluminateResponse($content, 200, array(
					'Content-Type' => $mime,
				));
			}

			$newCachedPath = $this->processImage($width, $height, $fit, $fitpos, $quality, $sourcePath, $cachedPath);

			if (file_exists($newCachedPath) && is_file($newCachedPath)) {
				$mime = mime_content_type($newCachedPath);
				$content = file_get_contents($newCachedPath);
				return new IlluminateResponse($content, 200, array(
					'Content-Type' => $mime,
				));
			}
		}

	}

	/**
	 * @param $width
	 * @param $height
	 * @param int $fit
	 * @param string $fitpos
	 * @param int $quality
	 * @param string $sourcePath
	 * @param string $cachedPath
	 * @return void
	 */
	private function processImage($width = null, $height = null, int $fit, string $fitpos, int $quality, string $sourcePath, string $cachedPath) {

		$image = Image::read($sourcePath);

		if (is_numeric($fit) && ($fit == 2 || $fit == 1 || $fit == 0)) {
			$this->fit = $fit;
		}

		if (in_array($fitpos, $this->positions)) {
			$this->fitpos = $fitpos;
		}

		if (is_numeric($width)) {
			if ($width > 0) {
				$this->width = (int)$width;
			} else {
				$this->width = null;
			}
		}

		if (is_numeric($height)) {
			if ($height > 0) {
				$this->height = (int)$height;
			} else {
				$this->height = null;
			}
		}

		if (is_numeric($quality) && $quality >= 0 && $quality <= 100) {
			$this->quality = $quality;
		}

		if ($this->fit == 1) {

			if (is_null($this->width)) {
				//scale proportionally with fixed height
				$image->scale(height: $this->height);
			} elseif (is_null($this->height)) {
				//scale proportionallywith fixed width
				$image->scale(width: $this->width);
			} else {
				// cover given canvas, use cropping
				$image->cover($this->width, $this->height, $this->fitpos);
			}

		} elseif ($this->fit == 2) {

			// padding
			$image->pad($this->width, $this->height, config('image.custom.paddingColor'));

		} else {

			if (is_null($this->width)) {
				// scale proportionally with fixed height
				// legacy (same as fit = 1)
				$image->scale(height: $this->height);
			} elseif (is_null($this->height)) {
				// scale proportionallywith fixed width
				// legacy (same as fit = 1)
				$image->scale(width: $this->width);
			} else {
				// force resizing, might result in stretching
				$image->resize($this->width, $this->height);
			}

		}

		// check if specific cache direcory exists
		$cacheDir = 'x' .$width . '/y' . $height . '/f' . $fit . '/' . $fitpos;
		if(!File::isDirectory($cacheDir)) {
			Storage::disk('imgcache')->makeDirectory($cacheDir);
		}

		// save to cache
		$image->save($cachedPath, $quality);

		return $cachedPath;

	}

	/**
	 * Returns full image path from given filename
	 *
	 * @param string $filename
	 * @return string
	 */
	private function getImagePath(string $filename)
	{
		// find file
		foreach (config('imagecache.paths') as $path) {
			// don't allow '..' in filenames
			$image_path = $path . '/' . str_replace('..', '', $filename);
			if (file_exists($image_path) && is_file($image_path)) {
				// file found
				return $image_path;
			}
		}

		$default_path = Storage::disk('localdisk')->path('default/default-image.jpg');
		if (file_exists($default_path) && is_file($default_path)) {
			// file found
			return $default_path;
		}

		// file not found
		abort(404);
	}

}
