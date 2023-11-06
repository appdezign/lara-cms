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
