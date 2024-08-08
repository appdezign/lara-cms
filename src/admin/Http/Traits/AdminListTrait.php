<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;

use Lara\Common\Models\Tag;
use Lara\Common\Models\Taxonomy;

trait AdminListTrait
{

	/**
	 * Get the specified request parameter
	 *
	 * The fallback order is:
	 * - request
	 * - session
	 * - default
	 *
	 * A request parameter can be stored in the session globally,
	 * or it can be stored for the specific content entity
	 *
	 * @param Request $request
	 * @param string $param
	 * @param mixed $default
	 * @param string $tag
	 * @param bool $reset
	 * @return bool|mixed|null
	 */
	private function getListRequestParam(Request $request, string $param, $default = null, $tag = 'global', $reset = false)
	{

		$tag = '_lara_' . $tag;

		if ($request->has($param) || $reset == true) {

			$value = $request->get($param);

			if (session()->has($tag)) {

				$tagSession = session($tag);
				$tagSession[$param] = $value;
				session([$tag => $tagSession]);

			} else {

				session([
					$tag => [
						$param => $value,
					],
				]);

			}

		} else {

			if (session()->has($tag)) {
				$tagSession = session($tag);
				if (array_key_exists($param, $tagSession)) {
					if (!empty($tagSession[$param])) {
						$value = $tagSession[$param];
					} else {
						$value = $default;
					}
				} else {
					$value = $default;
				}
			} else {
				$value = $default;
			}
		}

		// convert true/false strings to boolean
		if ($value == 'true') {
			return true;
		} elseif ($value == 'false') {
			return false;
		} else {
			return $value;
		}

	}

	/**
	 * Get all parameters for the index method
	 *
	 * Because we use the same views for different entities,
	 * the view needs to know a few specific parameters:
	 *  - search
	 *  - filter by tag or group
	 *  - paginate or not
	 *
	 * @param object $entity
	 * @param Request $request
	 * @param object $filters
	 * @return object
	 */
	private function getIndexParams(object $entity, Request $request, object $filters)
	{

		$params = (object)array(
			'paginate'    => false,
			'currentpage' => 1,
			'perpage'     => config('lara.default_perpage'),
		);

		$params->currentpage = $this->getListRequestParam($request, 'page', null, $entity->getEntityKey());
		$params->perpage = $this->getListRequestParam($request, 'perpage', config('lara.default_perpage'), 'global');

		if (!$filters->search && is_numeric($params->perpage)) {
			$params->paginate = true;
		} else {
			$params->paginate = false;
		}

		return $params;

	}

	/**
	 * Get Filters for Index method
	 *
	 * @param object $entity
	 * @param Request $request
	 * @param array|null $autocols
	 * @return object
	 */
	private function getIndexFilters(object $entity, Request $request, $autocols = null)
	{

		// Reset all entity filters
		// This is usefull for switching language
		if ($request->has('resetfilters') && $request->get('resetfilters') == 'true') {
			$sessionVars = session()->all();
			foreach ($sessionVars as $sessionKey => $sessionVar) {
				if (str_starts_with($sessionKey, '_lara_')) {
					if ($sessionKey != '_lara_global') {
						Session::forget($sessionKey);
					}
				}
			}
			return redirect()->route($entity->getPrefix() . '.' . $entity->getEntityRouteKey() . '.' . $entity->getMethod())->send();
		}


		// First check if the session has filters that are NOT in the request
		// if so, redirect, and add the sessions filters to the request
		$entitySession = session('_lara_' . $entity->getEntityKey());

		if ($entitySession) {
			if (array_key_exists('passfilters', $entitySession)) {
				$sessionFilters = $entitySession['passfilters'];
				$requestFilters = $request->all();
				foreach ($sessionFilters as $filterkey => $filtervalue) {
					if (!array_key_exists($filterkey, $requestFilters)) {
						$passFilters = array_merge($sessionFilters, $requestFilters);

						return redirect()->route($entity->getPrefix() . '.' . $entity->getEntityRouteKey() . '.' . $entity->getMethod(), $passFilters)->send();
					}
				}
			}
		}

		$filters = (object)array(
			'trashed'          => false,
			'filter'           => false,
			'search'           => false,
			'keywords'         => null,
			'filterbygroup'    => false,
			'cgroup'           => null,
			'filterbytaxonomy' => false,
			'tag'              => null,
			'filterbyrelation' => false,
			'filterrelation'   => null,
			'autofilter'       => false,
			'autofilters'      => [],
			'passfilters'      => [],
		);

		if ($request->has('reset') && $request->get('reset') == 'true') {
			$reset = true;
		} else {
			$reset = false;
		}

		// show archive
		$filters->trashed = $this->getListRequestParam($request, 'archive', false, $this->entity->getEntityKey(), $reset);

		// search
		if ($entity->hasSearch()) {
			$keywords = $this->getListRequestParam($request, 'keywords', null, $entity->getEntityKey(), $reset);
			if (isset($keywords)) {

				// filter by keywords (search)
				$filters->search = true;
				$filters->keywords = $this->cleanupSearchString($keywords);

			}
		}

		// groups
		if ($entity->hasGroups()) {
			if ($entity->isAlias() && $entity->getAliasIsGroup()) {
				$filtergroup = $entity->getAlias();
			} else {
				$filtergroup = $this->getListRequestParam($request, 'cgroup', $entity->getDefaultGroup(),
					$entity->getEntityKey());
			}
			if (!empty($filtergroup) && $filtergroup != 'reset') {

				// Filter by group
				$filters->filter = true;
				$filters->filterbygroup = true;
				$filters->cgroup = $filtergroup;

			}
		}

		// tags
		if ($entity->hasTags()) {
			$default_tag = $this->getLstDefaultTag($entity);
			$filtertaxonomy = $this->getListRequestParam($request, 'tag', $default_tag, $entity->getEntityKey(), $reset);
			if (isset($filtertaxonomy) && is_numeric($filtertaxonomy)) {

				// Filter by tag
				$filters->filter = true;
				$filters->filterbytaxonomy = true;
				$filters->tag = $filtertaxonomy;

			}
		}

		// filter by relation
		if ($entity->getRelationFilterEntitykey()) {

			$filterrelation = $this->getListRequestParam($request, 'relfilter', null, $entity->entity_key);

			if (isset($filterrelation) && is_numeric($filterrelation)) {

				// Filter by relation
				$filters->filter = true;
				$filters->filterbyrelation = true;
				$filters->filterrelation = $filterrelation;
			}

		}

		// Autofilters
		$columns = $this->getEntityColums($entity);

		// add custom columns, if any
		if (!empty($autocols)) {
			$columns = array_merge($columns, $autocols);
		}

		foreach ($columns as $column) {

			// check request
			if ($request->has($column)) {

				// get the GET request value AND store it in the session!!
				$value = $this->getListRequestParam($request, $column, null, $entity->getEntityKey(), true);

				if ($value) {
					$filters->autofilters[$column] = $value;
					$filters->autofilter = true;
					$filters->filter = true;
				}

			} else {

				// check session for existing autofilter for this column
				if (session()->has($entity->getEntityKey())) {

					$entitySession = session($entity->getEntityKey());

					if (array_key_exists($column, $entitySession)) {
						if (!empty($entitySession[$column])) {
							$filters->autofilters[$column] = $entitySession[$column];
							$filters->autofilter = true;
							$filters->filter = true;
						}
					}

				}

			}

		}

		// Store filters in session

		$filters->passfilters = $filters->autofilters;

		if ($filters->filterbytaxonomy) {
			$filters->passfilters['tag'] = $filters->tag;
		}

		$sessiontag = '_lara_' . $entity->getEntityKey();

		if (session()->has($sessiontag)) {

			$tagSession = session($sessiontag);
			$tagSession['passfilters'] = $filters->passfilters;
			session([$sessiontag => $tagSession]);

		} else {

			session([
				$sessiontag => [
					'passfilters' => $filters->passfilters,
				],
			]);

		}

		return $filters;

	}

	/**
	 * Build the collection for the index method
	 *
	 * Options:
	 * - filter by group or tag, or keyword (search)
	 * - eager loading of tags, user, media, files, relation
	 * - paginate or not
	 * - sorting
	 *
	 * @param object $entity
	 * @param Request $request
	 * @param object|null $params
	 * @param object|null $filters
	 * @return object
	 */
	private function getEntityObjects(object $entity, Request $request, $params = null, $filters = null, $trashed = false)
	{

		// start collection
		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		// language
		if ($entity->hasLanguage()) {
			$clanguage = $this->getLstContentLanguage($request, $entity);
			$collection = $collection->langIs($clanguage);
		}

		// archive
		if ($filters->trashed) {
			$collection = $collection->onlyTrashed();
		}

		if ($filters->search) {

			// SEARCH
			$keywords = $filters->keywords;

			$collection = $collection->where(function ($q) use ($entity, $keywords) {
				foreach ($keywords as $value) {

					$entityKey = $entity->getEntityKey();
					$entitySearchFields = config('lara-front.entity_search_fields');
					if(key_exists($entityKey, $entitySearchFields)) {
						// custom search fields
						$customSearchFields = $entitySearchFields[$entityKey];
						foreach($customSearchFields as $customSearchField) {
							$q->orWhere($customSearchField, 'like', "%{$value}%");
						}
					} else {
						// default search fields (title, lead, body)
						$q->orWhere('title', 'like', "%{$value}%");
					}

				}
			});

		} elseif ($filters->filterbygroup) {

			// GROUP
			$collection = $collection->where('cgroup', $filters->cgroup);

		} elseif ($filters->filterbytaxonomy) {

			// TAG
			$filtertaxonomy = $filters->tag;
			$collection = $collection->whereHas('tags', function ($query) use ($filtertaxonomy) {
				$query->where(config('lara-common.database.object.tags') . '.id', $filtertaxonomy);
			});

		} elseif ($filters->filterbyrelation) {

			// RELATION
			$filterrelation = $filters->filterrelation;
			$filterkey = $entity->getRelationFilterForeignkey();
			$collection = $collection->where($filterkey, $filterrelation);

		} elseif ($filters->autofilter) {

			// AUTO FILTER
			foreach ($filters->autofilters as $filterkey => $filterval) {
				$collection = $collection->where($filterkey, $filterval);
			}

		}

		// eager loading
		$collection = $collection->with($entity->getEager());

		// eager loading: tags
		if ($entity->hasTags()) {
			$collection = $collection->with([
				'tags' => function ($query) use ($entity) {
					$query->where(config('lara-common.database.object.tags') . '.entity_key', $entity->getEntityKey());
				},
			]);
		}

		// eager loading: relation
		foreach ($entity->getRelations() as $relation) {

			if ($relation->type == 'belongsTo') {
				$collection = $collection->with($relation->relatedEntity->entity_key);
			}
			if ($relation->type == 'hasMany') {
				$collection = $collection->withCount(str_plural($relation->relatedEntity->entity_key));
			}
		}

		// orderBy
		foreach ($entity->getCustomColumns() as $field) {
			if ($field->fieldname == 'sticky') {
				$collection = $collection->orderBy('sticky', 'desc');
			}
		}
		if ($entity->getSortField()) {
			$collection = $collection->orderBy($entity->getSortField(), $entity->getSortOrder());
		}
		if ($entity->getSortField2nd()) {
			$collection = $collection->orderBy($entity->getSortField2nd(), $entity->getSortOrder2nd());
		}

		// get collection
		if ($params->paginate) {

			// paginate
			$objects = $collection->paginate($params->perpage);
			$objects->appends($filters->passfilters); // add request vars to paginator

			// check pagination
			$this->checkPagination($objects->count(), $entity);

		} else {

			// get objects
			$objects = $collection->get();

		}

		return $objects;

	}

	/**
	 * Get all the columns of this Entity Model for the autofilters
	 * Exclude the cgroup column, because it has its own filter
	 *
	 * @param object $entity
	 * @return array
	 */
	private function getEntityColums(object $entity)
	{

		$modelClass = $entity->getEntityModelClass();
		$obj = new $modelClass;
		$columns = $obj->getTableColumns();

		// remove cgroup
		if (($key = array_search('cgroup', $columns)) !== false) {
			unset($columns[$key]);
		}

		return $columns;
	}

	/**
	 * Basic version of getEntityObjects,
	 * without language, tags, media, files, user
	 *
	 * Options:
	 * - filter by group or keyword (search)
	 * - sorting
	 *
	 * @param object $entity
	 * @param Request $request
	 * @param object|null $params
	 * @param object|null $filters
	 * @return object
	 */
	private function getObjects(object $entity, Request $request, $params = null, $filters = null)
	{

		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($filters->search) {

			// SEARCH
			$keywords = $filters->keywords;
			$collection->where(function ($q) use ($keywords) {
				foreach ($keywords as $value) {
					$q->orWhere('title', 'like', "%{$value}%");
				}
			});

		} elseif ($filters->filterbygroup) {

			// GROUP
			$collection->where('cgroup', $filters->cgroup);

		} elseif ($filters->autofilter) {

			// AUTO FILTER
			foreach ($filters->autofilters as $filterkey => $filterval) {
				$collection->where($filterkey, $filterval);
			}

		}

		// OrderBy
		$collection->orderBy($entity->getSortField(), $entity->getSortOrder());

		// get or paginate collection
		if ($params->paginate) {

			// paginate
			$objects = $collection->paginate($params->perpage);
			$objects->appends($filters->passfilters); // add request vars to paginator

			// check pagination
			$this->checkPagination($objects->count(), $entity);

		} else {

			// get objects
			$objects = $collection->get();

		}

		return $objects;

	}

	/**
	 * If we want to reorder the entity object manually,
	 * we do this for one tag (default taxonomy), or one group
	 *
	 * If there is no active tag or active group,
	 * we use the first tag, or the first group
	 *
	 * @param string $language
	 * @param object $entity
	 * @param Request $request
	 * @param object|null $tags
	 * @return mixed
	 */
	private function getSortedObjects(string $language, object $entity, Request $request, $tags = null)
	{

		$tagsArray = (array)$tags;

		$modelClass = $entity->getEntityModelClass();

		// get default taxonomy
		$taxonomy = $this->getLstDefaultTaxonomy();

		// get default tag
		if (!empty($tagsArray)) {
			$rootTag = Tag::langIs($language)
				->entityIs($entity->getEntityKey())
				->taxonomyIs($taxonomy->id)
				->whereNull('parent_id')
				->first();
			if ($rootTag->children->count()) {
				$firstTag = $rootTag->children[0]['id'];
			} else {
				$firstTag = null;
			}
		} else {
			$firstTag = null;
		}

		// get active tag
		$filtertaxonomy = $this->getListRequestParam($request, 'tag', $firstTag, $entity->getEntityKey());

		// get groups
		$groups = (array)$entity->getGroups();

		// get default group
		if (sizeof($groups) > 0) {
			reset($groups);
			$firstGroup = key($groups);
		} else {
			$firstGroup = null;
		}

		// get active group
		if (!empty($entity->getAlias())) {
			$filtergroup = $entity->getAlias();
		} else {
			$filtergroup = $this->getListRequestParam($request, 'cgroup', $firstGroup, $entity->getEntityKey());
		}

		// get objects
		$collection = new $modelClass;

		// language
		if ($entity->hasLanguage()) {
			$clanguage = $this->getLstContentLanguage($request, $entity);
			$collection = $collection->langIs($clanguage);
		}

		if ($entity->hasTags() && !empty($filtertaxonomy)) {

			// Filtered by Tag
			$collection = $collection->whereHas('tags', function ($query) use ($filtertaxonomy) {
				$query->where(config('lara-common.database.object.tags') . '.id', $filtertaxonomy);
			})->sorted();

		} elseif ($entity->hasGroups() && !empty($filtergroup) && $filtergroup != '') {

			// Filtered by Group
			$collection = $collection->where('cgroup', $filtergroup)->sorted();
		} else {

			// Unfiltered
			$collection = $collection->sorted();

		}

		$objects = $collection->get();

		return $objects;

	}

	/**
	 * Cleanup Search String
	 *
	 * @param string $str
	 * @return array[]|false|string[]
	 */
	private function cleanupSearchString(string $str)
	{

		$keywords = preg_split('/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY);

		return $keywords;

	}

	/**
	 * Check if the current pagination page is still valid
	 *
	 * When you are on a page > 1, and you increase the number of items to display,
	 * you might end up on a pagination page that is no longer valid.
	 * If so, we redirect to page 1.
	 *
	 * @param int $objectcount
	 * @param object $entity
	 * @return false|RedirectResponse
	 */
	private function checkPagination(int $objectcount, object $entity)
	{

		if ($objectcount == 0 && isset($_GET['page']) && $_GET['page'] > 1) {

			return redirect()->route($entity->getPrefix() . '.' . $entity->getEntityRouteKey() . '.' . $entity->getMethod(),
				['page' => 1])->send();

		} else {

			return false;

		}

	}

	/**
	 * Get last used page (pagination) from session
	 * This is useful for redirection
	 *
	 * @param string $entityKey
	 * @return int|null
	 */
	private function getLastPage(string $entityKey)
	{

		if (session()->has('_lara_' . $entityKey)) {
			$entitySession = session('_lara_' . $entityKey);
			if (array_key_exists('page', $entitySession)) {
				$lastpage = $entitySession['page'];
				if (is_numeric($lastpage)) {
					// if $lastpage is 1, there is no need to redirect
					if ($lastpage > 1) {
						return $lastpage;
					} else {
						return null;
					}
				} else {
					return null;
				}
			} else {
				return null;
			}
		} else {
			return null;
		}

	}

	/**
	 * @return object|null
	 */
	private function getLstDefaultTaxonomy()
	{

		$taxonomy = Taxonomy::where('is_default', 1)->first();
		if ($taxonomy) {
			return $taxonomy;
		} else {
			return null;
		}

	}

	/**
	 * @param object $entity
	 * @return int|null
	 */
	private function getLstDefaultTag(object $entity)
	{

		if (!empty($entity->getDefaultTag())) {
			$deftag = Tag::entityIs($entity->getEntityKey())->where('slug', $entity->getDefaultTag())->first();
			if ($deftag) {
				$default_tag = $deftag->id;
			} else {
				$default_tag = null;
			}
		} else {
			$default_tag = null;
		}

		return $default_tag;
	}

	/**
	 * Get the content language
	 *
	 * @param Request $request
	 * @param object|null $entity
	 * @return mixed
	 */
	private function getLstContentLanguage(Request $request, $entity = null)
	{

		$key = 'clanguage';

		$default = config('lara.clanguage_default');

		if (!empty($entity) && $entity->hasLanguage()) {

			// get language from request or session
			$clanguage = $this->getListRequestParam($request, $key, $default, 'global');

		} else {

			// set default language
			$clanguage = $default;
		}

		return $clanguage;

	}

}

