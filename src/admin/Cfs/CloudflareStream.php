<?php

namespace Lara\Admin\Cfs;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

use Lara\Admin\Http\Traits\AdminEntityTrait;

class CloudflareStream
{

	use AdminEntityTrait;

	private $accountId;
	private $authKey;
	private $authEMail;
	private $guzzle;

	/**
	 * CloudflareStream constructor.
	 *
	 * See: https://github.com/afloeter/laravel-cloudflare-stream/
	 *
	 * @param string $accountId
	 * @param string $authKey
	 * @param string $authEMail
	 * @param null $privateKey
	 */
	public function __construct(string $accountId, string $authKey, string $authEMail)
	{

		$this->accountId = $accountId;
		$this->authKey = $authKey;
		$this->authEMail = $authEMail;

		$this->guzzle = new Client([
			'base_uri' => 'https://api.cloudflare.com/client/v4/',
		]);

	}

	public function list()
	{

		$parameters = [
			'include_counts' => true,
			'limit'          => 1000,
			'asc'            => false,
		];

		$result = json_decode($this->request('accounts/' . $this->accountId . '/stream?' . http_build_query($parameters))->getBody()->getContents());

		if ($result) {
			return $result->result->videos;
		} else {
			return null;
		}


	}

	public function video(string $uid)
	{

		return $this->request('accounts/' . $this->accountId . '/stream/' . $uid)->getBody()->getContents();

	}

	public function getVideo(string $uid)
	{

		$data = json_decode($this->video($uid));

		return $data->result;

	}

	public function upload($entityKey, $videoFile, $videoName)
	{


		/*
		 * Supported formats:
		 * MP4, MKV, MOV, AVI, FLV, MPEG-2 TS, MPEG-2 PS, MXF, LXF, GXF, 3GP, WebM, MPG, QuickTime
		 */

		$lara = $this->getEntityVarByKey($entityKey);
		$entity = new $lara;

		$ds = DIRECTORY_SEPARATOR;
		$archive = '_archive';
		$baseUrl = config('app.url');
		$baseMediaUrl = $baseUrl . '/assets/media';
		$baseVideoUrl = $baseMediaUrl . $ds . $entityKey;

		if (Storage::disk($entity->getDiskForVideos())->exists($entityKey . $ds . $videoFile)) {
			$videoUrl = $baseVideoUrl . $ds . $videoFile;
		} elseif((Storage::disk($entity->getDiskForVideos())->exists($entityKey . $ds . $archive . $ds .$videoFile))) {
			$videoUrl = $baseVideoUrl . $ds . $archive . $ds  . $videoFile;
		} else {
			return null;
		}

		$data = [
			'url'  => $videoUrl,
			'meta' => [
				'name' => $videoName,
			],
		];

		return json_decode($this->request('accounts/' . $this->accountId . '/stream/copy', 'post', $data)->getBody()->getContents());

	}

	public function status(string $uid)
	{
		$data = json_decode($this->video($uid));

		return $data->result->readyToStream;

	}


	public function getMeta(string $uid)
	{

		// Get all data
		$data = json_decode($this->video($uid));

		// Return meta data
		return $data->result->meta;
	}

	public function setMeta(string $uid, array $meta)
	{
		// Merge meta data
		$meta = [
			'meta' => array_merge($this->getMeta($uid), $meta)
		];

		// Request
		$response = $this->request('accounts/' . $this->accountId . '/stream/' . $uid, 'post', $meta);

		// Return result
		return $response->getBody()->getContents();
	}

	public function removeMeta(string $uid, string $metaKey)
	{

		// Merge meta data
		$meta = [
			'meta' => array_merge($this->getMeta($uid))
		];

		// Remove key
		if (array_key_exists($metaKey, $meta['meta'])) {
			unset($meta['meta'][$metaKey]);
		}

		// Request
		return $this->request('accounts/' . $this->accountId . '/stream/' . $uid, 'post', $meta)->getBody()->getContents();

	}

	public function getName(string $uid)
	{
		$meta = $this->getMeta($uid);
		return $meta['name'];
	}

	public function setName(string $uid, string $name)
	{
		return $this->setMeta($uid, ['name' => $name]);
	}

	public function getPlaybackURLs(string $uid)
	{

		// Get all data
		$video = json_decode($this->video($uid), true);

		// Return playback URLs
		return json_encode($video['result']['playback']);

	}

	public function getDimensions(string $uid)
	{
		// Get all data
		$video = json_decode($this->video($uid), true);

		// Return playback URLs
		return json_encode($video['result']['input']);
	}

	public function delete(string $uid)
	{
		return json_decode($this->request('accounts/' . $this->accountId . '/stream/' . $uid, 'delete')->getBody()->getContents());
	}


	private function request(string $endpoint, $method = 'get', $data = [])
	{
		// Define headers.
		$headers = [
			'X-Auth-Key'   => $this->authKey,
			'X-Auth-Email' => $this->authEMail,
			'Content-Type' => 'application/json',
		];

		// Define options for post request method...
		if (count($data) && $method === "post") {
			$options = [
				'headers'            => $headers,
				RequestOptions::JSON => $data,
			];
		} else {
			$options = [
				'headers' => $headers,
			];
		}

		return $this->guzzle->request($method, $endpoint, $options);
	}

}
