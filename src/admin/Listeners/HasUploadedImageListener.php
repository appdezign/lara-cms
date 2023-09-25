<?php
namespace Lara\Admin\Listeners;

use UniSharp\LaravelFilemanager\Events\ImageWasUploaded;
use Intervention\Image\Facades\Image;

class HasUploadedImageListener
{
	/**
	 * Handle the event.
	 *
	 * @param ImageWasUploaded $event
	 * @return void
	 */
	public function handle(ImageWasUploaded $event) {
		$method = 'on'.class_basename($event);
		if (method_exists($this, $method)) {
			call_user_func([$this, $method], $event);
		}
	}

	/**
	 * resize uploaded image to max width (and preserve aspect ratio)
	 *
	 * @param ImageWasUploaded $event
	 * @return void
	 */
	public function onImageWasUploaded(ImageWasUploaded $event) {
		$path = $event->path();

		$max_width = config('lara.image_max_width');

		$img = Image::make($path);

		if($img->width() > $max_width) {

			$img->resize($max_width, null, function ($constraint) {
				$constraint->aspectRatio();
			});
			$img->save($path);

		}

	}

}
