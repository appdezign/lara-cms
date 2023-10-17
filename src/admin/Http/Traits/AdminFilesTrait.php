<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Support\Facades\File;

trait AdminFilesTrait
{

	/**
	 * Lock files (chmod 0444) in directories that the Builder writes to.
	 * This way we prevent local-to-remote sync overwrite updated configs, language files, etc.
	 *
	 * @param string $dirpath
	 * @param string|null $pattern
	 * @return void
	 */
	private function lockFilesInDir(string $dirpath, $pattern = null)
	{

		$files = File::allFiles($dirpath);

		foreach ($files as $file) {

			if (!empty($pattern)) {

				$filename = $file->getFilename();

				if (substr($filename, 0, strlen($pattern)) == $pattern) {

					chmod($file->getPathname(), 0444);

				}

			} else {

				chmod($file->getPathname(), 0444);

			}

		}

	}

	/**
	 * Unlock files in a directory temporarily,
	 * so the Builder can write to it
	 *
	 * @param string $dirpath
	 * @param string|null $pattern
	 * @return void
	 */
	private function unlockFilesInDir(string $dirpath, $pattern = null)
	{

		$files = File::allFiles($dirpath);

		foreach ($files as $file) {

			if (!empty($pattern)) {

				$filename = $file->getFilename();

				if (substr($filename, 0, strlen($pattern)) == $pattern) {

					chmod($file->getPathname(), 0644);

				}

			} else {

				chmod($file->getPathname(), 0644);

			}

		}

		sleep(1);

	}


}

