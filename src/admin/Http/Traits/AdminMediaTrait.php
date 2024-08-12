<?php

namespace Lara\Admin\Http\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Lara\Common\Models\Entity;
use Lara\Common\Models\MediaVideo;
use Lara\Common\Models\Setting;
use Lara\Common\Models\Upload;

trait AdminMediaTrait
{

	/**
	 * Save the newly uploaded images for the current object
	 * to the appropriate folder and to the database
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveMedia(Request $request, object $entity, object $object)
	{

		if ($entity->hasImages()) {

			$this->checkCroppingColumn();

			if ($request->has('_delete_image')) {

				$imgDelArray = explode('_', $request->input('_delete_image'));
				$imageID = end($imgDelArray);

				$imgObject = $object->media()->find($imageID);

				// If we are deleting the featured image,
				// we have to set a new one
				if ($imgObject->featured == 1) {
					$newFeaturedImage = $object->media->where('featured', 0)->first();
					if ($newFeaturedImage) {
						$newFeaturedImage->featured = 1;
						$newFeaturedImage->save();
					}
				}

				// check if this is the OpenGraph Image
				if ($entity->hasOpengraph()) {
					if ($object->opengraph) {
						if ($imgObject->filename == $object->opengraph->og_image) {
							$object->opengraph->og_image = '';
							$object->opengraph->save();
						}
					}
				}

				$imgObject->delete();

				$this->checkImagePosition($entity, $object);

			} elseif ($request->has('_save_image')) {

				$imgSaveArray = explode('_', $request->input('_save_image'));
				$imageID = end($imgSaveArray);

				$img = $object->media()->find($imageID);

				if ($img->ishero == 0) {
					if ($request->input('_image_ishero_' . $imageID) == 1) {
						// unset old hero images
						$object->media()->where('ishero', 1)->update(['ishero' => 0, 'hide_in_gallery' => 0]);
						// set new hero image
						$img->ishero = 1;
						$img->hide_in_gallery = config('lara-admin.featured.hide_in_gallery');
					}
				} else {
					$img->ishero = $request->input('_image_ishero_' . $imageID);
					$img->hide_in_gallery = $request->input('_hide_in_gallery_' . $imageID);
				}

				if ($img->featured == 0) {
					if ($request->input('_image_featured_' . $imageID) == 1) {
						// unset old featured images
						$object->media()->where('featured', 1)->update(['featured' => 0, 'hide_in_gallery' => 0]);
						// set new featured image
						$img->featured = 1;
						$img->hide_in_gallery = config('lara-admin.featured.hide_in_gallery');
					}
				} else {
					$img->featured = $request->input('_image_featured_' . $imageID);
					$img->hide_in_gallery = $request->input('_hide_in_gallery_' . $imageID);
				}

				// icon
				if ($request->input('_image_isicon_' . $imageID) == 1) {
					$img->isicon = 1;
					$img->hide_in_gallery = 1;
				} else {
					$img->isicon = 0;
					$img->hide_in_gallery = $request->input('_hide_in_gallery_' . $imageID);;
				}

				$img->herosize = $request->input('_herosize_' . $imageID);
				$img->caption = $request->input('_caption_' . $imageID);
				$img->image_alt = $request->input('_image_alt_' . $imageID);
				$img->image_title = $request->input('_image_title_' . $imageID);
				$img->prevent_cropping = $request->input('_prevent_cropping_' . $imageID);

				$img->save();

				$this->checkImagePosition($entity, $object);

			} else {

				if ($request->has('_cancel_image_upload')) {

					//

				} else {

					// get temp files from database
					$uploads = Upload::currentUser()
						->entityTypeIs($entity->getEntityModelClass())
						->objectIs($object->id)
						->tokenIs($request->get('_token'))
						->typeIs('image')
						->get();

					// save images
					foreach ($uploads as $upload) {

						// move file to public folder
						$tempPath = '_temp/' . $upload->filename;
						$imgPath = $entity->getEntityKey() . '/' . $upload->filename;

						if (Storage::disk($entity->getDiskForImages())->exists($tempPath)) {

							try {
								Storage::disk($entity->getDiskForImages())->move($tempPath, $imgPath);
							} catch (\Exception $e) {
								// dd($e);
							}

							// Save to DB using relation
							$object->media()->create([
								'filename'         => $upload->filename,
								'mimetype'         => $upload->mimetype,
								'title'            => $upload->filename,
								'featured'         => 0,
								'ishero'           => 0,
								'herosize'         => 0,
								'prevent_cropping' => 0,
								'hide_in_gallery'  => config('lara-admin.featured.hide_in_gallery'),
							]);

						}

					}

					$this->checkImagePosition($entity, $object);

				}

				DB::table(config('lara-common.database.sys.uploads'))
					->where('user_id', Auth::user()->id)
					->where('entity_type', $entity->getEntityModelClass())
					->where('object_id', $object->id)
					->where('token', $request->get('_token'))
					->where('filetype', 'image')
					->delete();

			}

		}

	}

	private function checkCroppingColumn()
	{

		$colname = 'prevent_cropping';
		$after = 'image_alt';
		$tablename = config('lara-common.database.object.images');
		if (!Schema::hasColumn($tablename, $colname)) {
			Schema::table($tablename, function ($table) use ($colname, $after) {
				$table->boolean($colname)->default(0)->after($after);
			});
		}

	}

	/**
	 * @return void
	 */
	private function checkAllImagePositions()
	{

		$entities = Entity::EntityGroupIsOneOf(['page', 'block', 'entity', 'tag'])->get();
		foreach ($entities as $entity) {

			$lara = $this->getMediaEntityVarByKey($entity->getEntityKey());
			$entity = new $lara;

			if ($entity->hasImages()) {
				$modelClass = $entity->getEntityModelClass();
				$objects = $modelClass::get();
				foreach ($objects as $object) {
					$this->checkImagePosition($entity, $object);
				}
			}

		}

	}

	/**
	 * @param object $entity
	 * @return bool
	 */
	private function checkEntityImagePosition(object $entity) : bool
	{

		$status = $this->checkEntityImagePositionDone($this->entity);

		if ($status === false) {

			if ($entity->hasImages()) {
				$modelClass = $entity->getEntityModelClass();
				$objects = $modelClass::get();
				foreach ($objects as $object) {
					$this->checkImagePosition($entity, $object);
				}
			}

			$tag = '_lara_' . $entity->entity_key;
			$param = 'imagecheck';
			if (session()->has($tag)) {
				$tagSession = session($tag);
				$tagSession[$param] = 'true';
				session([$tag => $tagSession]);
			} else {
				session([
					$tag => [
						$param => 'true',
					],
				]);
			}

			return true;

		} else {

			return false;

		}

	}

	private function checkEntityImagePositionDone($entity)
	{

		$tag = '_lara_' . $entity->entity_key;
		$param = 'imagecheck';

		if (session()->has($tag)) {
			$tagSession = session($tag);
			if (array_key_exists($param, $tagSession)) {
				if ($tagSession[$param] == 'true') {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param object $entity
	 * @param object $object
	 * @return bool
	 */
	private function checkImagePosition(object $entity, object $object)
	{

		$touched = false;

		if ($object->media()->count()) {

			/*
			 * if the object has one or more images, but no featured image,
			 * we mark the first image as featured
			 */
			if (empty($object->featured)) {
				$firstImage = $object->media[0];
				$firstImage->featured = 1;
				$firstImage->hide_in_gallery = config('lara-admin.featured.hide_in_gallery');
				$firstImage->save();
				$touched = true;
				$this->refreshModel($entity, $object);
			}

			/*
			 * if the object has multiple featured images,
			 * we keep the first as featured
			 */
			if ($object->media()->where('featured', 1)->count() > 1) {
				$multiple = $object->media()->where('featured', 1)->get();
				foreach ($multiple as $key => $item) {
					if ($key > 0) {
						$item->featured = 0;
						$item->hide_in_gallery = 0;
						$item->save();
					}
				}
				$touched = true;
				$this->refreshModel($entity, $object);
			}

			/*
			 * Set position of featured image
			 */
			$featuredPosition = $this->getImagePositionBase($entity, $object, 'featured') + 1;
			$featured = $object->featured;
			if ($featured) {
				if ($featured->position != $featuredPosition) {
					$featured->position = $featuredPosition;
					$featured->save();
					$touched = true;
					$this->refreshModel($entity, $object);
				}

			}

			/*
			 * if the object has multiple hero images,
			 * we keep the first as hero
			 */
			if ($object->media()->where('ishero', 1)->count() > 1) {
				$multiple = $object->media()->where('ishero', 1)->get();
				foreach ($multiple as $key => $item) {
					if ($key > 0) {
						$item->ishero = 0;
						$item->hide_in_gallery = 0;
						$item->save();
					}
				}
				$touched = true;
				$this->refreshModel($entity, $object);
			}

			/*
			 * Set position of hero image
			 */
			$heroPosition = $this->getImagePositionBase($entity, $object, 'hero') + 1;
			$hero = $object->hero;
			if ($hero) {
				if ($hero->position != $heroPosition) {
					$hero->position = $heroPosition;
					$hero->save();
					$touched = true;
					$this->refreshModel($entity, $object);
				}
			}

			$base = $this->getImagePositionBase($entity, $object);
			$i = 1;
			foreach ($object->gallery as $image) {
				$pos = $base + $i;
				if ($image->position != $pos) {
					$image->position = $pos;
					$image->save();
					$touched = true;
				}
				$i++;
			}
		}

		return $touched;

	}

	/**
	 * @param object $entity
	 * @param object $object
	 * @return object|null
	 */
	private function refreshModel(object $entity, object $object)
	{
		// reload object
		$modelClass = $entity->getEntityModelClass();
		$objectId = $object->id;
		$object = $modelClass::find($objectId);

		return $object;
	}

	/**
	 * @param object $entity
	 * @param object $object
	 * @param string|null $type
	 * @return int
	 */
	private function getImagePositionBase(object $entity, object $object, $type = null)
	{

		$entityId = $entity->id;
		$base = (($entityId * 100) + $object->id) * 1000;

		if ($type == 'featured') {
			$sub = 100;
		} elseif ($type == 'hero') {
			$sub = 200;
		} else {
			$sub = 300;
		}

		return $base + $sub;

	}

	/**
	 * Save the videos for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveVideo(Request $request, object $entity, object $object)
	{
		if ($entity->hasVideos()) {

			if ($request->has('_delete_video')) {

				$vidDelArray = explode('_', $request->input('_delete_video'));
				$videoID = end($vidDelArray);

				$vidObject = $object->videos()->find($videoID);

				// If we are deleting the featured video,
				// we have to set a new one
				if ($vidObject->featured == 1) {
					$newFeaturedVideo = $object->videos->where('featured', 0)->first();
					if ($newFeaturedVideo) {
						$newFeaturedVideo->featured = 1;
						$newFeaturedVideo->save();
					}
				}

				$vidObject->delete();

			} elseif ($request->has('_save_video')) {

				$vidSaveArray = explode('_', $request->input('_save_video'));
				$videoID = end($vidSaveArray);

				$video = $object->videos()->find($videoID);

				if ($video->featured == 0) {
					if ($request->input('_video_featured_' . $videoID) == 1) {
						// unset all videos
						$object->videos()->update(['featured' => 0]);
						// set new featured video
						$video->featured = 1;
					}
				}

				$video->title = $request->input('_title_' . $videoID);
				$video->youtubecode = $request->input('_youtubecode_' . $videoID);

				$video->save();

			} else {

				// add video

				if ($request->input('_youtubecode')) {

					$featured = $object->videos()->count() == 0 ? 1 : 0;

					$newVideoObject = new MediaVideo([
						'title'       => $request->input('_title'),
						'youtubecode' => $request->input('_youtubecode'),
						'featured'    => $featured,
					]);

					$object->videos()->save($newVideoObject);

				}

			}

		}

	}

	/**
	 * Save the newly uploaded files for the current object
	 * to the appropriate folder and to the database
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveFile(Request $request, object $entity, object $object)
	{

		if ($entity->hasFiles()) {

			if ($request->has('_delete_file')) {

				$fileDelArray = explode('_', $request->input('_delete_file'));
				$fileID = end($fileDelArray);

				$object->files()->find($fileID)->delete();

			} elseif ($request->has('_save_file')) {

				$fileSaveArray = explode('_', $request->input('_save_file'));
				$fileID = end($fileSaveArray);

				$file = $object->files()->find($fileID);

				$file->title = $request->input('_doctitle_' . $fileID);
				$file->docdate = $request->input('_docdate_' . $fileID);

				$file->save();

			} else {

				if ($request->has('_cancel_file_upload')) {

					//

				} else {

					// get temp files from database
					$uploads = Upload::currentUser()
						->entityTypeIs($entity->getEntityModelClass())
						->objectIs($object->id)
						->tokenIs($request->get('_token'))
						->typeIs('file')
						->get();

					// save files
					foreach ($uploads as $upload) {

						// move file to public folder
						$tempPath = '_temp/' . $upload->filename;
						$imgPath = $entity->getEntityKey() . '/' . $upload->filename;

						if (Storage::disk($entity->getDiskForFiles())->exists($tempPath)) {

							try {
								Storage::disk($entity->getDiskForFiles())->move($tempPath, $imgPath);
							} catch (\Exception $e) {
								// dd($e);
							}

							// Save to DB using relation
							$object->files()->create([
								'filename' => $upload->filename,
								'mimetype' => $upload->mimetype,
								'title'    => $upload->filename,
								'docdate'  => Carbon::today(),
							]);

						}

					}

				}

			}

			DB::table(config('lara-common.database.sys.uploads'))
				->where('user_id', Auth::user()->id)
				->where('entity_type', $entity->getEntityModelClass())
				->where('object_id', $object->id)
				->where('token', $request->get('_token'))
				->where('filetype', 'file')
				->delete();

			$this->syncFilesArchive($entity);
		}

	}

	/**
	 * @param $entity
	 */
	private function syncFilesArchive($entity): bool
	{

		if ($entity->hasFiles()) {

			$entkey = $entity->getEntityKey();

			$syncKey = 'last_media_file_sync';
			if(!$this->checkMediaSync($entkey, $syncKey)) {
				return false;
			}

			$modelClass = $entity->getEntityModelClass();

			// get all objects
			if (method_exists($modelClass, 'withTrashed')) {
				$objects = $modelClass::withTrashed()->get();
			} else {
				$objects = $modelClass::get();
			}

			// check archive directory
			$entityArchivePath = $entkey . '/_archive';
			if (!Storage::disk($entity->getDiskForFiles())->exists($entityArchivePath)) {
				Storage::disk($entity->getDiskForFiles())->makeDirectory($entityArchivePath);
				if (Storage::disk($entity->getDiskForFiles())->exists('_temp/.htaccess')) {
					Storage::disk($entity->getDiskForFiles())->copy('_temp/.htaccess', $entityArchivePath . '/.htaccess');
				}
			}

			// check trash directory
			$entityTrashPath = $entkey . '/_trash';
			if (!Storage::disk($entity->getDiskForFiles())->exists($entityTrashPath)) {
				Storage::disk($entity->getDiskForFiles())->makeDirectory($entityTrashPath);
				if (Storage::disk($entity->getDiskForFiles())->exists('_temp/.htaccess')) {
					Storage::disk($entity->getDiskForFiles())->copy('_temp/.htaccess', $entityTrashPath . '/.htaccess');
				}
			}

			foreach ($objects as $object) {

				foreach ($object->files as $fileObject) {

					$publishPath = $entkey . '/' . $fileObject->filename;
					$archivePath = $entkey . '/_archive/' . $fileObject->filename;
					$trashPath = $entkey . '/_trash/' . $fileObject->filename;

					if ($object->trashed()) {

						// move file to trash
						if (Storage::disk($entity->getDiskForFiles())->exists($publishPath)) {
							try {
								Storage::disk($entity->getDiskForFiles())->move($publishPath, $trashPath);
							} catch (\Exception $e) {
								// dd($e);
							}
						} elseif (Storage::disk($entity->getDiskForFiles())->exists($archivePath)) {
							try {
								Storage::disk($entity->getDiskForFiles())->move($archivePath, $trashPath);
							} catch (\Exception $e) {
								// dd($e);
							}
						}

					} else {

						if ($object->publish == 0) {
							// move file to archive
							if (!Storage::disk($entity->getDiskForFiles())->exists($archivePath)) {
								if (Storage::disk($entity->getDiskForFiles())->exists($publishPath)) {
									try {
										Storage::disk($entity->getDiskForFiles())->move($publishPath, $archivePath);
									} catch (\Exception $e) {
										// dd($e);
									}
								}
							}
						}

						if ($object->publish == 1) {
							if (!Storage::disk($entity->getDiskForFiles())->exists($publishPath)) {
								// file is missing from public folder
								// try to get it from archive or trash
								if (Storage::disk($entity->getDiskForFiles())->exists($archivePath)) {
									try {
										Storage::disk($entity->getDiskForFiles())->move($archivePath, $publishPath);
									} catch (\Exception $e) {
										// dd($e);
									}
								} elseif (Storage::disk($entity->getDiskForFiles())->exists($trashPath)) {
									try {
										Storage::disk($entity->getDiskForFiles())->move($trashPath, $publishPath);
									} catch (\Exception $e) {
										// dd($e);
									}
								}
							}
						}
					}
				}
			}

			$this->purgeOrphanFiles($entity);

			$this->setLastMediaSync($entkey, $syncKey);

			return true;

		} else {

			return false;

		}

	}

	private function purgeOrphanFiles($entity)
	{

		$modelClass = $entity->getEntityModelClass();
		$entkey = $entity->getEntityKey();

		// get All Objects (to EXCLUDE images)
		$allObjects = $modelClass::get();
		$excludeImages = array();
		foreach ($allObjects as $objct) {
			foreach ($objct->media as $mediaObject) {
				$excludeImages[] = $mediaObject->filename;
			}
		}

		// get all PUBLISHED objects
		if ($entity->hasStatus()) {
			$publishedObjects = $modelClass::where('publish', 1)->get();
		} else {
			$publishedObjects = $modelClass::get();
		}
		$publishedObjectFiles = array();
		foreach ($publishedObjects as $publishedObject) {
			foreach ($publishedObject->files as $fileObject) {
				$publishedObjectFiles[] = $fileObject->filename;
			}
			foreach ($publishedObject->videofiles as $videofileObject) {
				$publishedObjectFiles[] = $videofileObject->filename;
			}
		}

		// PUBLISHED Objects: check files on disk
		$filesOnDisk = Storage::disk($entity->getDiskForFiles())->files($entkey);
		$excludeFiles = config('lara-admin.exclude_files_from_purge');
		foreach ($filesOnDisk as $fileOnDisk) {
			$parts = preg_split("#/#", $fileOnDisk);
			if ($parts[0] == $entkey) {
				$filename = $parts[1];
				if (!in_array($filename, $excludeFiles)) {
					if (!in_array($filename, $publishedObjectFiles) && !in_array($filename, $excludeImages)) {
						$publishPath = $entkey . '/' . $filename;
						$trashPath = $entkey . '/_trash/' . $filename;
						try {
							Storage::disk($entity->getDiskForFiles())->move($publishPath, $trashPath);
						} catch (\Exception $e) {
							// dd($e);
						}
					}
				}
			}
		}

		// get all CONCEPT objects
		if ($entity->hasStatus()) {
			$conceptObjects = $modelClass::where('publish', 0)->get();
		} else {
			$conceptObjects = $modelClass::get();
		}
		$conceptObjectFiles = array();
		foreach ($conceptObjects as $conceptObject) {
			foreach ($conceptObject->files as $fileObject) {
				$conceptObjectFiles[] = $fileObject->filename;
			}
			foreach ($conceptObject->videofiles as $videofileObject) {
				$conceptObjectFiles[] = $videofileObject->filename;
			}
		}

		// CONCEPT Objects: check files on disk
		$filesOnDisk = Storage::disk($entity->getDiskForFiles())->files($entkey . '/_archive');
		$excludeFiles = config('lara-admin.exclude_files_from_purge');
		foreach ($filesOnDisk as $fileOnDisk) {
			$parts = preg_split("#/#", $fileOnDisk);
			if ($parts[0] == $entkey && $parts[1] == '_archive') {
				$filename = $parts[2];
				if (!in_array($filename, $excludeFiles)) {
					if (!in_array($filename, $conceptObjectFiles) && !in_array($filename, $excludeImages)) {
						$archivePath = $entkey . '/_archive/' . $filename;
						$trashPath = $entkey . '/_trash/' . $filename;
						try {
							Storage::disk($entity->getDiskForFiles())->move($archivePath, $trashPath);
						} catch (\Exception $e) {
							// dd($e);
						}
					}
				}
			}
		}
	}

	/**
	 * Save the newly uploaded files for the current object
	 * to the appropriate folder and to the database
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveVideoFile(Request $request, object $entity, object $object)
	{

		if ($entity->hasVideoFiles()) {

			if ($request->has('_delete_videofile')) {

				$videofileDelArray = explode('_', $request->input('_delete_videofile'));
				$videofileID = end($videofileDelArray);

				$vidfileObject = $object->videofiles()->find($videofileID);

				// If we are deleting the featured videofile,
				// we have to set a new one
				if ($vidfileObject->featured == 1) {
					$newFeaturedVideoFile = $object->videofiles->where('featured', 0)->first();
					if ($newFeaturedVideoFile) {
						$newFeaturedVideoFile->featured = 1;
						$newFeaturedVideoFile->save();
					}
				}

				$vidfileObject->delete();

			} elseif ($request->has('_save_videofile')) {

				$videofileSaveArray = explode('_', $request->input('_save_videofile'));
				$videofileID = end($videofileSaveArray);

				$videofile = $object->videofiles()->find($videofileID);

				if ($videofile->featured == 0) {
					if ($request->input('_videofile_featured_' . $videofileID) == 1) {
						// unset all videos
						$object->videofiles()->update(['featured' => 0]);
						// set new featured video
						$videofile->featured = 1;
					}
				}

				$videofile->title = $request->input('_doctitle_' . $videofileID);

				$videofile->save();

			} else {

				if ($request->has('_cancel_file_upload')) {

					//

				} else {

					// get temp files from database
					$uploads = Upload::currentUser()
						->entityTypeIs($entity->getEntityModelClass())
						->objectIs($object->id)
						->tokenIs($request->get('_token'))
						->typeIs('videofile')
						->get();

					// save files
					foreach ($uploads as $upload) {

						// move file to public folder
						$tempPath = '_temp/' . $upload->filename;
						$imgPath = $entity->getEntityKey() . '/' . $upload->filename;

						if (Storage::disk($entity->getDiskForvideos())->exists($tempPath)) {

							try {
								Storage::disk($entity->getDiskForvideos())->move($tempPath, $imgPath);
							} catch (\Exception $e) {
								// dd($e);
							}

							// Save to DB using relation
							$featured = $object->videofiles()->count() == 0 ? 1 : 0;
							$object->videofiles()->create([
								'filename' => $upload->filename,
								'mimetype' => $upload->mimetype,
								'title'    => $upload->filename,
								'featured' => $featured,
							]);

						}

					}

				}

			}

			DB::table(config('lara-common.database.sys.uploads'))
				->where('user_id', Auth::user()->id)
				->where('entity_type', $entity->getEntityModelClass())
				->where('object_id', $object->id)
				->where('token', $request->get('_token'))
				->where('filetype', 'videofile')
				->delete();

			$this->syncVideoFilesArchive($entity);
		}

	}

	/**
	 * @param $entity
	 */
	private function syncVideoFilesArchive($entity)
	{

		if ($entity->hasVideoFiles()) {

			$entkey = $entity->getEntityKey();

			$syncKey = 'last_media_videofile_sync';
			if(!$this->checkMediaSync($entkey, $syncKey)) {
				return false;
			}

			$modelClass = $entity->getEntityModelClass();

			// get all objects
			if (method_exists($modelClass, 'withTrashed')) {
				$objects = $modelClass::withTrashed()->get();
			} else {
				$objects = $modelClass::get();
			}

			// check archive directory
			$entityArchivePath = $entkey . '/_archive';
			if (!Storage::disk($entity->getDiskForVideos())->exists($entityArchivePath)) {
				Storage::disk($entity->getDiskForVideos())->makeDirectory($entityArchivePath);
				if (Storage::disk($entity->getDiskForVideos())->exists('_temp/.htaccess')) {
					Storage::disk($entity->getDiskForVideos())->copy('_temp/.htaccess', $entityArchivePath . '/.htaccess');
				}
			}

			// check trash directory
			$entityTrashPath = $entkey . '/_trash';
			if (!Storage::disk($entity->getDiskForVideos())->exists($entityTrashPath)) {
				Storage::disk($entity->getDiskForVideos())->makeDirectory($entityTrashPath);
				if (Storage::disk($entity->getDiskForVideos())->exists('_temp/.htaccess')) {
					Storage::disk($entity->getDiskForVideos())->copy('_temp/.htaccess', $entityTrashPath . '/.htaccess');
				}
			}

			foreach ($objects as $object) {

				foreach ($object->videofiles as $videofileObject) {

					$publishPath = $entkey . '/' . $videofileObject->filename;
					$archivePath = $entkey . '/_archive/' . $videofileObject->filename;
					$trashPath = $entkey . '/_trash/' . $videofileObject->filename;

					if ($object->trashed()) {

						// move file to trash
						if (Storage::disk($entity->getDiskForVideos())->exists($publishPath)) {
							try {
								Storage::disk($entity->getDiskForVideos())->move($publishPath, $trashPath);
							} catch (\Exception $e) {
								// dd($e);
							}
						} elseif (Storage::disk($entity->getDiskForVideos())->exists($archivePath)) {
							try {
								Storage::disk($entity->getDiskForVideos())->move($archivePath, $trashPath);
							} catch (\Exception $e) {
								// dd($e);
							}
						}

					} else {

						if ($object->publish == 0) {
							// move file to archive
							if (!Storage::disk($entity->getDiskForVideos())->exists($archivePath)) {
								if (Storage::disk($entity->getDiskForVideos())->exists($publishPath)) {
									try {
										Storage::disk($entity->getDiskForVideos())->move($publishPath, $archivePath);
									} catch (\Exception $e) {
										// dd($e);
									}
								}
							}
						}

						if ($object->publish == 1) {
							if (!Storage::disk($entity->getDiskForVideos())->exists($publishPath)) {
								// file is missing from public folder
								// try to get it from archive or trash
								if (Storage::disk($entity->getDiskForVideos())->exists($archivePath)) {
									try {
										Storage::disk($entity->getDiskForVideos())->move($archivePath, $publishPath);
									} catch (\Exception $e) {
										// dd($e);
									}
								} elseif (Storage::disk($entity->getDiskForVideos())->exists($trashPath)) {
									try {
										Storage::disk($entity->getDiskForVideos())->move($trashPath, $publishPath);
									} catch (\Exception $e) {
										// dd($e);
									}
								}
							}
						}
					}
				}
			}

			$this->purgeOrphanFiles($entity);

			$this->setLastMediaSync($entkey, $syncKey);

		}

	}

	private function checkOrphanFiles($entity)
	{

		$sessionkey = '_lara_orphan_filecheck_' . $entity->getEntityKey();

		if (session()->has($sessionkey)) {

			return false;

		} else {

			$this->syncFilesArchive($entity);
			$this->syncVideoFilesArchive($entity);

			session([$sessionkey => true]);

			return true;
		}

	}

	/**
	 * Translate entity key to a full Lara Entity class name
	 *
	 * @param string $entityKey
	 * @return string
	 */
	private function getMediaEntityVarByKey(string $entityKey)
	{

		$laraClass = (ucfirst($entityKey) . 'Entity');

		if (class_exists('\\Lara\\Common\\Lara\\' . $laraClass)) {
			$laraClass = '\\Lara\\Common\\Lara\\' . $laraClass;
		} else {
			$laraClass = '\\Eve\\Lara\\' . $laraClass;
		}

		return $laraClass;

	}

	private function checkMediaSync($entkey, $key) : bool
	{
		$lastSyncStr = $this->getLastMediaSync($entkey, $key);

		$lastSync = Carbon::createFromFormat("Y-m-d H:i:s", $lastSyncStr);
		$now = Carbon::now();

		return $lastSync->lt($now->subDay());

	}

	private function getLastMediaSync($entkey, $key) : string
	{
		$settingkey = $key . '_' . $entkey;

		$timestamp = date("Y-m-d H:i:s");

		$setting = Setting::firstOrCreate(
			['key' => $settingkey],
			['cgroup' => 'system', 'key' => $settingkey, 'title' => $settingkey, 'value' => $timestamp]
		);

		return $setting->value;

	}

	private function setLastMediaSync($entkey, $key) : bool
	{

		$settingkey = $key . '_' . $entkey;

		$timestamp = date("Y-m-d H:i:s");

		Setting::updateOrCreate(
			['cgroup' => 'system', 'key' => $settingkey, 'title' => $settingkey],
			['value' => $timestamp]
		);

		return true;

	}

}

