<?php

namespace Lara\Common\Http\Controllers\Tools;

use Illuminate\Support\Facades\Storage;

use Closure;
use Intervention\Image\ImageManager;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response as IlluminateResponse;
use Config;

use Image;

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
	 * Get HTTP response of template applied image file
	 *
	 * @param int|null $width
	 * @param int|null $height
	 * @param int $fit
	 * @param string $fitpos
	 * @param int $quality
	 * @param string $filename
	 * @return IlluminateResponse
	 */
	public function process($width = null, $height = null, int $fit, string $fitpos, int $quality, string $filename)
	{

		$path = $this->getImagePath($filename);

		// The Intervention\Image package does not support Gifs, so we exclude them
		$mime = mime_content_type($path);
		if($mime == 'image/gif') {
			$content = file_get_contents($path);
			return new IlluminateResponse($content, 200, array(
				'Content-Type'  => $mime,
			));
		}

		// resize and cache for a month
		$content = Image::cache(function ($image) use ($path, $width, $height, $fit, $fitpos, $quality) {

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

				// cover given canvas
				// use cropping

				$image->make($path)->fit($this->width, $this->height, null, $this->fitpos);

			} elseif ($this->fit == 2) {

				// contain image in given canvas
				// do not use cropping,
				// fill canvas with default background-color (#fff, or transparent)

				$img = Image::make($path);
				$orinalAspectRatio = $img->width() / $img->height();

				if ($this->width == 0) {

					$this->width = (int)round($this->height * $orinalAspectRatio, 0);
					$newAspectRatio = $orinalAspectRatio;

				} elseif ($this->height == 0) {

					$this->height = (int)round($this->width / $orinalAspectRatio, 0);
					$newAspectRatio = $orinalAspectRatio;

				} else {

					$newAspectRatio = $this->width / $this->height;

				}

				if ($newAspectRatio < $orinalAspectRatio) {

					$image->make($path)
						->resize($this->width, null, function ($constraint) {
							$constraint->aspectRatio();
						});

				} else {

					$image->make($path)
						->resize(null, $this->height, function ($constraint) {
							$constraint->aspectRatio();
						});

				}

				$image->resizeCanvas($this->width, $this->height);

			} else {

				// resize
				// do not use cropping
				// do not use canvas filling

				if (is_null($this->width) || is_null($this->height)) {

					// maintain aspect ration
					$image->make($path)
						->resize($this->width, $this->height, function ($constraint) {
							$constraint->aspectRatio();
						});

				} else {

					// force resizing to given width height
					// might result in stretching

					$image->make($path)->resize($this->width, $this->height);

				}

			}

		}, 43200, false);

		return $this->buildResponse($content);
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

	/**
	 * Builds HTTP response from given image data
	 *
	 * @param string $content
	 * @return IlluminateResponse
	 */
	private function buildResponse(string $content)
	{
		// define mime type
		$mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $content);

		// return http response
		return new IlluminateResponse($content, 200, array(
			'Content-Type'  => $mime,
			'Cache-Control' => 'max-age=' . (config('imagecache.lifetime') * 60) . ', public',
			'Etag'          => md5($content),
		));
	}
}
