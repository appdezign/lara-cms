<?php

namespace Lara\Admin\Http\Traits;

use Analytics;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
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
	private function getSiteStats(bool $forceRefresh = false, bool $useCacheOnly = false): ?object
	{

		// get config
		$conf = $this->getAnalyticsConfig();

		// set property ID dynamically
		Analytics::setPropertyId($conf->propID);

		if (!empty($conf->propID)) {

			$type = 'site';
			$days = config('lara-admin.analytics.defaultDays');
			$period = Period::days($days);

			$cache_key = 'lara.admin.analytics-' . $conf->prefix . '-' . $type . '-' . $days . '-days';

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
	 * @param bool $useCacheOnly
	 * @return object|null
	 */
	private function getPageStats(bool $forceRefresh = false, bool $useCacheOnly = false): ?object
	{

		// get config
		$conf = $this->getAnalyticsConfig();

		// set property ID dynamically
		Analytics::setPropertyId($conf->propID);

		if (!empty($conf->propID)) {

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
	 * @param bool $useCacheOnly
	 * @return object|null
	 */
	private function getReferrerStats(bool $forceRefresh = false, bool $useCacheOnly = false): ?object
	{

		// get config
		$conf = $this->getAnalyticsConfig();

		// set property ID dynamically
		Analytics::setPropertyId($conf->propID);

		if (!empty($conf->propID)) {

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
	 * @param bool $useCacheOnly
	 * @return object|null
	 */
	private function getUserStats(bool $forceRefresh = false, bool $useCacheOnly = false): ?object
	{

		// get config
		$conf = $this->getAnalyticsConfig();

		// set property ID dynamically
		Analytics::setPropertyId($conf->propID);

		if (!empty($conf->propID)) {

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
	 * @param bool $useCacheOnly
	 * @return object|null
	 * @throws BindingResolutionException
	 */
	private function getBrowserStats(bool $forceRefresh = false, bool $useCacheOnly = false): ?object
	{

		// get config
		$conf = $this->getAnalyticsConfig();

		// set property ID dynamically
		Analytics::setPropertyId($conf->propID);

		if (!empty($conf->propID)) {

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
	 * @return object
	 */
	private function getAnalyticsConfig(): object
	{

		$app = app();
		$conf = $app->make('stdClass');

		// single site
		$conf->multisite = false;
		$conf->prefix = 'default';
		$conf->propID = config('analytics.property_id');

		if (class_exists('\\Eve\\Models\\Subsite')) {
			$subsite = \Eve\Models\Subsite::where('user_id', Auth::user()->id)->first();
			if($subsite) {
				if(!empty($subsite->ga4propid)) {
					// multisite
					$conf->multisite = true;
					$conf->prefix = $subsite->id;
					$conf->propID = $subsite->ga4propid;
				}
			}
		}

		return $conf;
	}

	/**
	 * @param bool $forceRefresh
	 * @return object|null
	 */
	private function getContentStats(bool $forceRefresh = false): ?object
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
	private function getLaraUserStats(): mixed
	{

		$limit = 10;

		return User::isWeb()->limit($limit)->get();

	}

	/**
	 * @param array|null $types
	 * @return void
	 */
	private function refreshAnalytics($types = null): void
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
	private function getLastAnalyticsSync(): mixed
	{

		$object = Setting::where('cgroup', 'system')
			->where('key', 'google_analytics_sync')
			->first();

		if ($object) {
			return Carbon::createFromFormat('Y-m-d H:i:s', $object->value);
		} else {
			return false;
		}

	}

	/**
	 * Save the timestamp of the last translation sync
	 *
	 * @return void
	 */
	private function setLastAnalyticsSync(): void
	{

		$timestamp = date("Y-m-d H:i:s");

		Setting::updateOrCreate(
			['cgroup' => 'system', 'key' => 'google_analytics_sync'],
			['value' => $timestamp]
		);

	}

	/**
	 * @param object $collection
	 * @param int $limit
	 * @return array
	 */
	private function limitStringsInArray(object $collection, int $limit): array
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