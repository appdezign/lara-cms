<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Support\Facades\Storage;

use Lara\Common\Models\Entity;

use Lara\Common\Models\MediaImage;

use Image;

trait AdminSyncTrait
{

	/**
	 * @param bool $force
	 * @return void
	 */
	private function syncContentFromRemoteApi($force = false)
	{

		$entities = Entity::entityGroupIsOneOf(['page', 'block', 'entity'])->get();

		foreach ($entities as $entity) {

			$laraClass = $this->getEntityVarByKey($entity->entity_key);
			$laraEntity = new $laraClass;

			if ($laraEntity->hasSync()) {

				$modelclass = $laraEntity->getEntityModelClass();
				$objects = $modelclass::has('sync')->get();

				foreach ($objects as $object) {

					$sync = $object->sync;

					if ($sync) {

						// exclude sync to self
						if ($sync->remote_url != config('app.url')) {

							$token = config('lara-front.lara_api_token');

							// sync with remote API
							$api_base_url = $sync->remote_url . $sync->remote_suffix . $sync->ent_key . '/' . $sync->slug;
							$api_query = '?image=full&media=true&api_token=' . $token;
							$json = file_get_contents($api_base_url . $api_query);
							$remote = json_decode($json);

							$remoteArray = (array)$remote;

							if (!empty($remoteArray)) {

								if ($remote->updated_at > $object->updated_at | $force) {

									$object->title = $remote->title;
									if ($laraEntity->hasLead()) {
										$object->lead = $remote->lead;
									}
									if ($laraEntity->hasBody()) {
										$object->body = $remote->body;
									}

									$object->slug_lock = 1;

									$object->save();

									if ($laraEntity->hasSeo()) {
										$seo = $object->seo;
										if($seo) {
											$seo->seo_title = $remote->seo_title;
											$seo->seo_description = $remote->seo_description;
											$seo->seo_keywords = $remote->seo_keywords;
											$seo->save();
										} else {
											$object->seo()->create([
												'seo_title'       => $remote->seo_title,
												'seo_description' => $remote->seo_description,
												'seo_keywords'    => $remote->seo_keywords,
											]);
										}
									}

									// purge local images from DB
									$object->media()->delete();

									// save remote images
									if (isset($remote->media)) {
										foreach ($remote->media as $remoteImg) {

											$this->saveRemoteMedia($sync->remote_url, $sync->ent_key, $object, $remoteImg);

										}

									}

								}

							}
						}

					}

				}

			}

		}

	}

	/**
	 * @param string $remote_url
	 * @param string $entityKey
	 * @param object $object
	 * @param object $remoteImg
	 * @return void
	 */
	private function saveRemoteMedia(string $remote_url, string $entityKey, object $object, object $remoteImg)
	{

		$laraClass = $this->getEntityVarByKey($entityKey);
		$laraEntity = new $laraClass;

		$imgfilename = $remoteImg->filename;

		$remote_image_path = $remoteImg->image_url;

		if (!empty($imgfilename)) {

			$disk = $laraEntity->getDiskForImages();
			$imagePath = Storage::disk($disk)->path($laraEntity->getEntityKey());

			// process image
			Image::make($remote_image_path)->save( $imagePath . '/' .$imgfilename);

			// Save to DB using relation
			$newMediaObject = new MediaImage([
				'filename'    => $remoteImg->filename,
				'mimetype'    => $remoteImg->mimetype,
				'title'       => $remoteImg->title,
				'featured'    => $remoteImg->featured,
				'caption'     => $remoteImg->caption,
				'image_title' => $remoteImg->image_title,
				'image_alt'   => $remoteImg->image_alt,
			]);

			$object->media()->save($newMediaObject);

		}

	}

}