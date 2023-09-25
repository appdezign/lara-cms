<?php

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;

if (!function_exists('_lanq')) {

	/**
	 * @param string $fullkey
	 * @param array $replace
	 * @param string|null $locale
	 * @return string|null
	 */
	function _lanq(string $fullkey, $replace = [], $locale = null) {

		$translation = null;

		if (__($fullkey, $replace = [], $locale = null) == $fullkey) {

			if (str_contains($fullkey, '::')) {

				list($module, $langkey) = explode('::', $fullkey);

				$key_array = explode('.', $langkey);

				if (sizeof($key_array) == 3) {

					// no translation found, use last part of key
					list($group, $tag, $key) = explode('.', $langkey);

					$tempkey = '_' . $key;

					if (!empty($key)) {
						addMissingLanguageKey($module, $group, $tag, $key, $tempkey);
					}

					$translation = $tempkey;

				} else {
					dd($fullkey);
				}

			} else {
				dd($fullkey);
			}

		} else {
			// use translation
			$translation = __($fullkey, $replace = [], $locale = null);
		}

		return $translation;

	}

}

if (!function_exists('lang_from_db')) {

	/**
	 * @param string $module
	 * @param string $cgroup
	 * @param string $tag
	 * @param string $key
	 * @return mixed
	 */
	function lang_from_db(string $module, string $cgroup, string $tag, string $key) {

		$translations = Lara\Common\Models\Translation::where('module', $module)
			->where('cgroup', $cgroup)
			->where('tag', $tag)
			->where('key', $key)
			->pluck('value', 'language');

		return $translations;
	}
}

if (!function_exists('addMissingLanguageKey')) {

	/**
	 * @param string $module
	 * @param string $cgroup
	 * @param string $tag
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	function addMissingLanguageKey(string $module, string $cgroup, string $tag, string $key, string $value) {

		$supportedLocales = array_keys(config('laravellocalization.supportedLocales'));
		foreach ($supportedLocales as $locale) {

			$translation = Lara\Common\Models\Translation::langIs($locale)
				->where('module', $module)
				->where('cgroup', $cgroup)
				->where('tag', $tag)
				->where('key', $key)
				->first();

			if ($translation === null) {
				$new = Lara\Common\Models\Translation::create([
					'language' => $locale,
					'module'   => $module,
					'cgroup'    => $cgroup,
					'tag'      => $tag,
					'key'      => $key,
					'value'    => $value,
				]);
			}

		}

	}

}

if (!function_exists('processFieldData')) {

	/**
	 * @param array $available
	 * @param string $fieldname
	 * @param object $object
	 * @return array
	 */
	function processFieldData(array $available, string $fieldname, object $object) {

		$available = (array)$available;
		$fieldval = $object->$fieldname;

		if (!empty($fieldval)) {
			$fielddata = [$fieldval => $fieldval] + $available;
		} else {
			$fielddata = $available;
		}

		return $fielddata;
	}
}

if (!function_exists('getFieldState')) {

	/**
	 * @param object $object
	 * @param object $entfield
	 * @return bool
	 */
	function getFieldState(object $object, object $entfield) {

		$multiple = false;

		$state = $entfield->fieldstate;
		$field = $entfield->condition_field;
		$operator = $entfield->condition_operator;
		$value = $entfield->condition_value;

		// Multiple values (OR)
		$or_array = array_map('trim', explode('||', $value));
		if(sizeof($or_array) > 1) {
			$multiple = true;
		}

		if ($state == 'enabled') {
			return true;
		} elseif ($state == 'hidden') {
			return false;
		} elseif ($state == 'enabledif') {

			if ($operator == 'isequal') {
				if($multiple) {
					$or = false;
					foreach($or_array as $val) {
						if ($object->$field == $val) {
							$or = true;
						}
					}
					return $or;
				} else {
					// single vale
					if ($object->$field == $value) {
						return true;
					} else {
						return false;
					}
				}
			} elseif ($operator == 'isnotequal') {
				if ($object->$field != $value) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} else {
			return true;

		}

	}
}

if (!function_exists('parseFileFormats')) {

	/**
	 * @param array $fileformats
	 * @return string
	 */
	function parseFileFormats(array $fileformats) {

		$parsed = array();

		// cleanup array
		foreach($fileformats as $item) {

			$arr = explode('/', $item);
			if(sizeof($arr) > 1) {
				$parsed[] = $arr[1];
			} else {
				$item = trim($item);
				$item = str_replace('.','', $item);
				$parsed[] = $item;
			}

		}

		$formatstr = implode(', ', $parsed);

		return $formatstr;
	}
}
