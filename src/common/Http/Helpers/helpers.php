<?php

if (!function_exists('get_youtube_id')) {

	/**
	 * @param string $url
	 * @return false|string
	 */
	function get_youtube_id(string $url)
	{

		if (str_contains($url, 'youtu.be')) {

			$parts = explode('/', $url);
			$youtubeCode = end($parts);

			if (strlen($youtubeCode) == 11) {
				return $youtubeCode;
			} else {
				return false;
			}

		} elseif (str_contains($url, 'youtube.com/watch')) {

			$youtubeCode = '';

			$parts = explode('?', $url);
			$query = end($parts);
			$vars = explode('&', $query);
			foreach ($vars as $var) {
				$pts = explode('=', $var);
				if ($pts[0] == 'v') {
					$youtubeCode = $pts[1];
				}
			}

			if (strlen($youtubeCode) == 11) {
				return $youtubeCode;
			} else {
				return false;
			}

		} else {

			return false;

		}

	}

}

if (!function_exists('recurse_copy')) {

	/**
	 * @param string $src
	 * @param string $dst
	 * @return void
	 */
	function recurse_copy(string $src, string $dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file)) {
					recurse_copy($src . '/' . $file, $dst . '/' . $file);
				} else {
					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

}

if (!function_exists('delete_directory')) {

	/**
	 * @param string $dir
	 * @return bool
	 */
	function delete_directory(string $dir)
	{
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}

			if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}
		}

		return rmdir($dir);
	}

}