<?php

namespace Lara\Admin\Http\Traits;

use Analytics;
use GuzzleHttp\Exception\GuzzleException;
use Lara\Common\Models\Entity;
use Lara\Common\Models\Setting;
use Lara\Common\Models\User;
use Spatie\Analytics\Period;

use Carbon\Carbon;

use Cache;

trait AdminAnalyticsTrait
{

	/**
	 * @param bool $forceRefresh
	 * @param bool $useCacheOnly
	 * @return object|null
	 */
	private function getSiteStats($forceRefresh = false, $useCacheOnly = false)
	{

		if (config('analytics.property_id')) {

			$type = 'site';
			$days = config('lara-admin.analytics.defaultDays');
			$period = Period::days($days);
			$cache_key = 'lara.admin.analytics-' . $type . '-' . $days . '-days';

			if ($useCacheOnly) {
				$rawdata = Cache::get($cache_key);
			} else {
				if (!Cache::has($cache_key) || $forceRefresh) {
					try {
						$rawdata = Analytics::fetchTotalVisitorsAndPageViews($period);
					} catch (GuzzleException $e) {
						flash('Google Analytics API timeout')->warning();
						return null;
					}
					Cache::forever($cache_key, $rawdata);
				} else {
					$rawdata = Cache::get($cache_key);
				}
			}

			// split collection for chart
			$data = $this->makeNewObject();
			if (!empty($rawdata)) {
				$data->dates = $rawdata->pluck('date');
				$data->visitors = $rawdata->pluck('activeUsers');
				$data->pageviews = $rawdata->pluck('screenPageViews');
			} else {
				$data->dates = null;
				$data->visitors = null;
				$data->pageviews = null;
			}

			return $data;

		} else {
			return null;
		}

	}

	/**
	 * @param bool $forceRefresh
	 * @return object|null
	 */
	private function getPageStats($forceRefresh = false, $useCacheOnly = false)
	{

		if (config('analytics.property_id')) {

			$type = 'page';
			$limit = config('lara-admin.analytics.topPagesLimit');
			$days = config('lara-admin.analytics.defaultDays');
			$period = Period::days($days);
			$cache_key = 'lara.admin.analytics-' . $type . '-' . $days . '-days';

			if ($useCacheOnly) {
				$rawdata = Cache::get($cache_key);
			} else {
				if (!Cache::has($cache_key) || $forceRefresh) {
					try {
						$rawdata = Analytics::fetchMostVisitedPages($period, $limit);
					} catch (GuzzleException $e) {
						flash('Google Analytics API timeout')->warning();
						return null;
					}
					Cache::forever($cache_key, $rawdata);
				} else {
					$rawdata = Cache::get($cache_key);
				}
			}

			// split collection for chart
			$data = $this->makeNewObject();
			if (!empty($rawdata)) {
				$data->urls = $this->limitStringsInArray($rawdata->pluck('fullPageUrl'), 40);
				$data->pageviews = $rawdata->pluck('screenPageViews');
			} else {
				$data->urls = null;
				$data->pageviews = null;
			}

			return $data;

		} else {
			return null;
		}

	}

	/**
	 * @param bool $forceRefresh
	 * @return object|null
	 */
	private function getReferrerStats($forceRefresh = false, $useCacheOnly = false)
	{

		if (config('analytics.property_id')) {

			$type = 'ref';
			$limit = config('lara-admin.analytics.topRefLimit');
			$days = config('lara-admin.analytics.defaultDays');
			$period = Period::days($days);
			$cache_key = 'lara.admin.analytics-' . $type . '-' . $days . '-days';

			if ($useCacheOnly) {
				$rawdata = Cache::get($cache_key);
			} else {
				if (!Cache::has($cache_key) || $forceRefresh) {
					try {
						$rawdata = Analytics::fetchTopReferrers($period, $limit);
					} catch (GuzzleException $e) {
						flash('Google Analytics API timeout')->warning();
						return null;
					}
					Cache::forever($cache_key, $rawdata);
				} else {
					$rawdata = Cache::get($cache_key);
				}
			}

			// split collection for chart
			$data = $this->makeNewObject();
			if (!empty($rawdata)) {
				$data->urls = $this->limitStringsInArray($rawdata->pluck('pageReferrer'), 40);
				$data->pageviews = $rawdata->pluck('screenPageViews');
			} else {
				$data->urls = null;
				$data->pageviews = null;
			}

			return $data;

		} else {
			return null;
		}

	}

	/**
	 * @param bool $forceRefresh
	 * @return object|null
	 */
	private function getUserStats($forceRefresh = false, $useCacheOnly = false)
	{

		if (config('analytics.property_id')) {

			$type = 'user';
			$days = config('lara-admin.analytics.defaultDays');
			$period = Period::days($days);
			$cache_key = 'lara.admin.analytics-' . $type . '-' . $days . '-days';

			if ($useCacheOnly) {
				$rawdata = Cache::get($cache_key);
			} else {
				if (!Cache::has($cache_key) || $forceRefresh) {
					try {
						$rawdata = Analytics::fetchUserTypes($period);
					} catch (GuzzleException $e) {
						flash('Google Analytics API timeout')->warning();
						return null;
					}
					Cache::forever($cache_key, $rawdata);
				} else {
					$rawdata = Cache::get($cache_key);
				}
			}

			// split collection for chart
			$data = $this->makeNewObject();
			if (!empty($rawdata)) {
				$data->type = $rawdata->pluck('newVsReturning');
				$data->sessions = $rawdata->pluck('activeUsers');
			} else {
				$data->type = null;
				$data->sessions = null;
			}

			return $data;

		} else {

			return null;

		}

	}

	/**
	 * @param bool $forceRefresh
	 * @return object|null
	 */
	private function getBrowserStats($forceRefresh = false, $useCacheOnly = false)
	{

		if (config('analytics.property_id')) {

			$type = 'browser';

			$limit = config('lara-admin.analytics.topBrowserLimit');
			$days = config('lara-admin.analytics.defaultDays');
			$period = Period::days($days);

			$cache_key = 'lara.admin.analytics-' . $type . '-' . $days . '-days';

			if ($useCacheOnly) {
				$rawdata = Cache::get($cache_key);
			} else {
				if (!Cache::has($cache_key) || $forceRefresh) {
					try {
						$rawdata = Analytics::fetchTopBrowsers($period, $limit);
					} catch (GuzzleException $e) {
						flash('Google Analytics API timeout')->warning();
						return null;
					}
					Cache::forever($cache_key, $rawdata);
				} else {
					$rawdata = Cache::get($cache_key);
				}
			}

			// split collection for chart
			$data = $this->makeNewObject();
			if (!empty($rawdata)) {
				$data->type = $rawdata->pluck('browser');
				$data->sessions = $rawdata->pluck('screenPageViews');
			} else {
				$data->type = null;
				$data->sessions = null;
			}

			return $data;

		} else {

			return null;

		}

	}

	/**
	 * @param bool $forceRefresh
	 * @return object|null
	 */
	private function getContentStats($forceRefresh = false)
	{

		$cache_key = 'lara.admin.dashboard-content';

		if ($forceRefresh) {
			Cache::forget($cache_key);
		}

		$data = Cache::rememberForever($cache_key, function () {

			$entities = Entity::entityGroupIsOneOf(['page', 'entity'])->get();

			// split collection for chart
			$ents = $this->makeNewObject();
			$ents->title = array();
			$ents->count = array();

			foreach ($entities as $entity) {

				$modelclass = $entity->entity_model_class;
				$modelclass::count();
				$ents->title[] = $entity->title;
				$ents->count[] = $modelclass::count();
			}

			return $ents;
		});

		return $data;

	}

	/**
	 * @return mixed
	 */
	private function getLaraUserStats()
	{

		$limit = 10;

		$users = User::isWeb()->limit($limit)->get();

		return $users;

	}

	/**
	 * @param array|null $types
	 * @return void
	 */
	private function refreshAnalytics($types = null)
	{

		if (config('analytics.property_id') ) {

			if (empty($types)) {

				// get all data types
				$this->getSiteStats(true);
				$this->getPageStats(true);
				$this->getReferrerStats(true);
				$this->getUserStats(true);
				$this->getBrowserStats(true);

			} else {

				if (in_array('sitestats', $types)) {
					$this->getSiteStats(true);
				}
				if (in_array('pagestats', $types)) {
					$this->getPageStats(true);
				}
				if (in_array('refstats', $types)) {
					$this->getReferrerStats(true);
				}
				if (in_array('userstats', $types)) {
					$this->getUserStats(true);
				}
				if (in_array('browserstats', $types)) {
					$this->getBrowserStats(true);
				}

			}

		}

		$this->setLastAnalyticsSync();

	}

	/**
	 * Get the timestamp of the last translation sync
	 *
	 * @return mixed
	 */
	private function getLastAnalyticsSync()
	{

		$object = Setting::where('cgroup', 'system')
			->where('key', 'google_analytics_sync')
			->first();

		if ($object) {
			$dateObject = Carbon::createFromFormat('Y-m-d H:i:s', $object->value);

			return $dateObject;
		} else {
			return false;
		}

	}

	/**
	 * Save the timestamp of the last translation sync
	 *
	 * @return bool
	 */
	private function setLastAnalyticsSync()
	{

		$timestamp = date("Y-m-d H:i:s");

		Setting::updateOrCreate(
			['cgroup' => 'system', 'key' => 'google_analytics_sync'],
			['value' => $timestamp]
		);

		return true;

	}

	/**
	 * @param object $collection
	 * @param int $limit
	 * @return array
	 */
	private function limitStringsInArray(object $collection, int $limit)
	{

		$array = array();
		foreach ($collection as $item) {
			if (strlen($item) > $limit) {
				$array[] = '..' . substr($item, -($limit));
			} else {
				$array[] = $item;
			}
		}

		return $array;

	}

}