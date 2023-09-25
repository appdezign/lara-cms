<?php

namespace Lara\Admin\Http\Controllers\Misc;

use Lara\Common\Models\Upload;
use Lara\Common\Models\Entity;
use Lara\Common\Models\User;

use App\Http\Controllers\Controller;
use Lara\Admin\Http\Traits\LaraAdminHelpers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use LaravelLocalization;

class UploadController extends Controller
{

	use LaraAdminHelpers;

	public function __construct()
	{
		//
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function process(Request $request, $filetype)
	{

		$entityKey = $request->get('entity_key');
		$laraClass = $this->getEntityVarByKey($entityKey);
		$laraEntity = new $laraClass;
		$entityType = $laraEntity->getEntityModelClass();

		$objectId = $request->get('object_id');
		$mimetype = $request->file('fileuploads')->getMimeType();

		$token = $request->get('_token');
		$dzSessionId = $request->get('dz_session_id');

		if (Auth::check()) {

			// remove previous upload attempts
			DB::table(config('lara-common.database.sys.uploads'))
				->where('user_id', Auth::user()->id)
				->where('entity_type', $entityType)
				->where('object_id', $objectId)
				->where('token', $token)
				->where('dz_session_id', '!=', $dzSessionId)
				->delete();

			// cleanup filename
			$originalMediaName = $request->file('fileuploads')->getClientOriginalName();
			$mediaExtension = $request->file('fileuploads')->getClientOriginalExtension();
			$mediaName = pathinfo($originalMediaName, PATHINFO_FILENAME);
			$cleanMediaName = str_slug($mediaName);

			$timestamp = date('YmdHis');
			$newMediaName = $timestamp . '-' . $cleanMediaName . '.' . $mediaExtension;

			Upload::create([
				'user_id'       => Auth::user()->id,
				'entity_type'   => $entityType,
				'object_id'     => $objectId,
				'token'         => $token,
				'dz_session_id' => $dzSessionId,
				'filename'      => $newMediaName,
				'filetype'      => $filetype,
				'mimetype'      => $mimetype,
			]);

			// move file to temp folder
			// get the assigned Disk for this entity and this upload type
			if ($filetype == 'image') {
				$storageDisk = $laraEntity->getDiskForImages();
			} elseif ($filetype == 'file') {
				$storageDisk = $laraEntity->getDiskForFiles();
			} elseif ($filetype == 'videofile') {
				$storageDisk = $laraEntity->getDiskForVideos();
			} else {
				// default
				$storageDisk = $laraEntity->getDiskForImages();
			}

			$request->file('fileuploads')->storeAs(
				'_temp',
				$newMediaName,
				$storageDisk
			);

			return \Response::json('success', 200);

		} else {

			return \Response::json('error', 500);

		}

	}

}
