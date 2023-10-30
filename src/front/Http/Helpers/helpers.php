<?php

if (!function_exists('_cimg')) {

	/**
	 * @param string $filename
	 * @param int $width
	 * @param int $height
	 * @param string $disk
	 * @param int $fit
	 * @param string $fitpos
	 * @param int $quality
	 * @return string
	 */
	function _cimg(string $filename, int $width, int $height, $disk = 'localdisk', $fit = 1, $fitpos = 'center', $quality = 90)
	{

		if($disk == 's3') {
			return route('s3cache', ['width' => $width, 'height' => $height, 'fit' => $fit, 'fitpos' => $fitpos, 'quality' => $quality, 'filename' => $filename]);
		} else {
			return route('imgcache', ['width' => $width, 'height' => $height, 'fit' => $fit, 'fitpos' => $fitpos, 'quality' => $quality, 'filename' => $filename]);
		}

	}
}

if (!function_exists('_imgdim')) {

	/*
	 * prevent cropping
	 */
	function _imgdim(int $width, int $height, bool $preventCropping = false, bool $forceCropping = true)
	{
		$result = array();

		$result['w'] = $width;

		if($height == 0) {
			// no cropping
			$result['h'] = 0;
			$result['f'] = 2;
		} else {
			if($preventCropping && !$forceCropping) {
				// no cropping
				$result['h'] = 0;
				$result['f'] = 2;
			} else {
				// cropping
				$result['h'] = $height;
				$result['f'] = 1;
			}
		}

		return $result;
	}
}

if (!function_exists('in_array_r')) {

	/**
	 * @param string $needle
	 * @param array $haystack
	 * @param bool $strict
	 * @return bool
	 */
	function in_array_r(string $needle, array $haystack, $strict = false)
	{
		foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
				return true;
			}
		}

		return false;
	}
}

