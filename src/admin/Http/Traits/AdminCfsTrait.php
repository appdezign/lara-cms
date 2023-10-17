<?php

namespace Lara\Admin\Http\Traits;

use Lara\Common\Models\MediaVideoFile;

use Lara\Admin\Cfs\CloudflareStream;

trait AdminCfsTrait
{

	private function syncCloudFlareStream()
	{

		if (config('cloudflare-stream.accountId')) {

			$this->accountId = config('cloudflare-stream.accountId');
			$this->authKey = config('cloudflare-stream.authKey');
			$this->authEMail = config('cloudflare-stream.authEMail');

			$this->cfs = new CloudflareStream($this->accountId, $this->authKey, $this->authEMail);

			// update status of recently uploaded files
			$syncVideos = MediaVideoFile::whereNotNull('cfs_uid')->where('cfs_ready', 0)->get();
			foreach ($syncVideos as $syncVideo) {
				$uid = $syncVideo->cfs_uid;
				$readyToStream = $this->cfs->status($uid);
				if ($readyToStream) {
					$syncVideo->cfs_ready = 1; // ready for streaming
					$syncVideo->cfs_thumb_offset = 2; // 2 seconds offset
					$syncVideo->save();
				}
			}

			// upload new local videos
			$newVideos = MediaVideoFile::whereNull('cfs_uid')->get();
			foreach ($newVideos as $newVideo) {

				// upload to CloudFlare Stream
				$result = $this->cfs->upload('post', $newVideo->filename, $newVideo->title);
				if ($result) {
					$uid = $result->result->uid;
					// save uid to DB
					$newVideo->cfs_uid = $uid;
					$newVideo->save();
				}

			}
		}

	}

	private function purgeCloudFlareStream($force = false)
	{

		if (config('cloudflare-stream.accountId')) {

			$this->accountId = config('cloudflare-stream.accountId');
			$this->authKey = config('cloudflare-stream.authKey');
			$this->authEMail = config('cloudflare-stream.authEMail');

			$this->cfs = new CloudflareStream($this->accountId, $this->authKey, $this->authEMail);

			// check for orphans
			$videos = $this->cfs->list();

			$purge = array();

			foreach ($videos as $video) {
				$uid = $video->uid;
				// check local DB
				$videoFile = MediaVideoFile::where('cfs_uid', $uid)->first();
				if (empty($videoFile)) {
					$purge[] = $uid;
				}
			}

			if ($force) {
				foreach ($purge as $uid) {
					$this->cfs->delete($uid);
				}
			} else {
				// dd($purge);
			}

		}

	}

}