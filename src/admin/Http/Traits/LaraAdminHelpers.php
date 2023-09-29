<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

use Lara\Common\Models\Category;
use Lara\Common\Models\Entity;

use Lara\Common\Models\MediaFile;
use Lara\Common\Models\MediaImage;
use Lara\Common\Models\MediaVideo;
use Lara\Common\Models\Layout;

use Lara\Common\Models\ObjectOpengraph;
use Lara\Common\Models\ObjectSeo;
use Lara\Common\Models\Sync;

use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Page;
use Lara\Common\Models\Setting;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Taxonomy;
use Lara\Common\Models\Upload;
use Lara\Common\Models\Related;

use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;

use Analytics;

use Spatie\Geocoder\Facades\Geocoder;

use Carbon\Carbon;

use Bouncer;

use Module;

use Image;

use Config;

use Cache;

// use Theme;
use Qirolab\Theme\Theme;

use Log;

trait LaraAdminHelpers
{

	/**
	 * Create a new empty Laravel object
	 *
	 * See: https://alanstorm.com/laravel_objects_make/
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function makeNewObject()
	{

		$app = app();
		$newobject = $app->make('stdClass');

		return $newobject;

	}

	/**
	 * Wrapper for authorize
	 *
	 * If user is an administrator:
	 * Check if the ability exists before we authorize.
	 * If the ability does not exist: create it,
	 * and assign it to the role of administrator
	 *
	 * See also: https://github.com/JosephSilber/bouncer
	 *
	 * @param string $ability
	 * @param string $modelClass
	 * @return mixed
	 */
	private function authorizer(string $ability, string $modelClass)
	{

		if (Auth::user()->isAn('administrator')) {

			if (!Bouncer::allows($ability, $modelClass)) {

				$this->checkAbilities($modelClass);

				Bouncer::refresh();

			}

		}

		return Bouncer::authorize($ability, $modelClass);

	}

	private function checkAdminAccess() {
		if (Auth::user()->isNotAn('administrator')) {
			abort(response()->view('lara-admin::errors.405', [], 405));
		}

		return true;
	}

	/**
	 * Check if the requested ability is defined in the database
	 * If not, add it, and assign it
	 *
	 * @param string $modelClass
	 * @return void
	 */
	private function checkAbilities(string $modelClass)
	{

		$lara = $this->getEntityVarByModel($modelClass);
		$entity = new $lara;

		$abilities = config('lara-admin.abilities');

		foreach ($abilities as $ability) {

			if (!Ability::where('name', $ability)
				->where('entity_key', $entity->getEntityKey())
				->exists()) {

				$data = [
					'name'        => $ability,
					'title'       => ucfirst($entity->getEntityKey()) . ' ' . $ability,
					'entity_type' => $entity->getEntityModelClass(),
					'entity_key'  => $entity->getEntityKey(),

				];

				$newAbility = new Ability;
				$newAbility->forceFill($data);
				$newAbility->save();

				// find roles with level 100 and assign every possible ability
				$roles = Role::where('level', 100)->pluck('name')->toArray();

				foreach ($roles as $role) {
					Bouncer::allow($role)->to($ability, $entity->getEntityModelClass());
				}

			}

		}

		Bouncer::refresh();

	}

	/**
	 * Check all usergroups for this user
	 * and get the highest user level
	 *
	 * @param object $userobject
	 * @return int
	 */
	private function getUserLevel(object $userobject)
	{

		$userlevel = 0;

		foreach ($userobject->roles as $userrole) {
			if ($userrole->level > $userlevel) {
				$userlevel = $userrole->level;
			}
		}

		return $userlevel;
	}

	/**
	 * Get the User Role name from the Role with the highest user level
	 *
	 * @param object $userobject
	 * @return int
	 */
	private function getUserRoleName(object $userobject)
	{

		$userlevel = 0;

		foreach ($userobject->roles as $userrole) {
			if ($userrole->level > $userlevel) {
				$userlevel = $userrole->name;
			}
		}

		return $userlevel;
	}

	private function getActiveFrontTheme()
	{

		if (config('lara.client_theme')) {
			$theme = config('lara.client_theme');
		} else {
			// Fallback
			$theme = 'demo';
		}

		return $theme;

	}

	function getParentFrontTheme()
	{
		return config('theme.parent');
	}

	/**
	 * Get the Lara Entity Class
	 *
	 * @param string $routename
	 * @param string $modelclass
	 * @return object
	 */
	private function getLaraEntity(string $routename, string $modelclass)
	{

		$lara = $this->getEntityVarByModel($modelclass);

		$entity = new $lara;

		list($prefix, $entity_route_key, $method) = explode('.', $routename);

		$entity->setPrefix($prefix);
		$entity->setEntityRouteKey($entity_route_key);
		$entity->setMethod($method);

		// check alias
		$alias_routes = config('lara-common.routes.is_alias');

		if (!empty($alias_routes) && array_key_exists($entity_route_key, $alias_routes)) {

			$alias_config = config('lara-common.routes.has_alias');

			$entity->setAlias($entity_route_key);
			$entity->setIsAlias(true);
			$entity->setAliasIsGroup($alias_config[$entity->getEntityKey()][$entity->getAlias()]['is_group']);

		}

		// filter by relation
		foreach ($entity->getRelations() as $relation) {
			if ($relation->is_filter == 1) {

				$entity->setRelationFilterForeignkey($relation->foreign_key);
				$entity->setRelationFilterEntitykey($relation->relatedEntity->entity_key);
				$entity->setRelationFilterModelclass($relation->relatedEntity->entity_model_class);
				break;
			}
		}

		return $entity;

	}

	/**
	 * Get the Lara Entity Class based on the route name
	 *
	 * @param string $routename
	 * @return object
	 */
	private function getLaraEntityByRoute(string $routename)
	{

		list($prefix, $entity_route_key, $method) = explode('.', $routename);

		$lara = $this->getEntityVarByKey($entity_route_key);

		$entity = new $lara;

		$entity->setPrefix($prefix);
		$entity->setEntityRouteKey($entity_route_key);
		$entity->setMethod($method);

		return $entity;

	}

	/**
	 * Get the full class name based on the short model class name
	 *
	 * @param string $modelClass
	 * @return string
	 */
	private function getEntityVarByModel(string $modelClass)
	{

		$str = '\\' . str_replace('Models', 'Lara', $modelClass) . 'Entity';

		return $str;

	}

	/**
	 * Translate entity key to a full Lara Entity class name
	 *
	 * @param string $entityKey
	 * @return string
	 */
	private function getEntityVarByKey(string $entityKey)
	{

		$laraClass = (ucfirst($entityKey) . 'Entity');

		if (class_exists('\\Lara\\Common\\Lara\\' . $laraClass)) {
			$laraClass = '\\Lara\\Common\\Lara\\' . $laraClass;
		} else {
			$laraClass = '\\Eve\\Lara\\' . $laraClass;
		}

		return $laraClass;

	}

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

	/**
	 * Backend users sometimes close their browser,
	 * while they are still editing an object.
	 * This leaves the object in a locked state.
	 *
	 * When the same user calls the index method of this entity,
	 * we assume that we can unlock all objects of this entity,
	 * that are locked by this specific user
	 *
	 * @param string $modelClass
	 * @return void
	 */
	private function unlockAbandonedObjects(string $modelClass)
	{

		$objects = $modelClass::where('locked_by', Auth::user()->id)->get();

		if (!empty($objects)) {
			foreach ($objects as $object) {
				$object->unlockRecord();
			}
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

		$params->currentpage = $this->getRequestParam($request, 'page', null, $entity->getEntityKey());
		$params->perpage = $this->getRequestParam($request, 'perpage', config('lara.default_perpage'), 'global');

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

						return redirect()->route($entity->getPrefix() . '.' . $entity->getEntityRouteKey() . '.' . $entity->getMethod(),
							$passFilters)->send();
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
		$filters->trashed = $this->getRequestParam($request, 'archive', false, $this->entity->getEntityKey(), $reset);

		// search
		if ($entity->hasSearch()) {
			$keywords = $this->getRequestParam($request, 'keywords', null, $entity->getEntityKey(), $reset);
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
				$filtergroup = $this->getRequestParam($request, 'cgroup', $entity->getDefaultGroup(),
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
			$default_tag = $this->getEntityDefaultTag($entity);
			$filtertaxonomy = $this->getRequestParam($request, 'tag', $default_tag, $entity->getEntityKey(), $reset);
			if (isset($filtertaxonomy) && is_numeric($filtertaxonomy)) {

				// Filter by tag
				$filters->filter = true;
				$filters->filterbytaxonomy = true;
				$filters->tag = $filtertaxonomy;

			}
		}

		// filter by relation
		if ($entity->getRelationFilterEntitykey()) {

			$filterrelation = $this->getRequestParam($request, 'relfilter', null, $entity->entity_key);

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
				$value = $this->getRequestParam($request, $column, null, $entity->getEntityKey(), true);

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
			$clanguage = $this->getContentLanguage($request, $entity);
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

		// check if entity has a hasMany relationship,
		// and lock the object if necessary
		foreach ($objects as $obj) {
			$obj->is_locked = false;
			foreach ($entity->getRelations() as $relation) {

				if ($relation->type == 'hasMany') {
					$rel_model_count = str_plural($relation->entity) . '_count';
					if ($obj->$rel_model_count > 0) {
						$obj->is_locked = true;
					}
				}
			}
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
		$taxonomy = $this->getDefaultTaxonomy();

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
		$filtertaxonomy = $this->getRequestParam($request, 'tag', $firstTag, $entity->getEntityKey());

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
			$filtergroup = $this->getRequestParam($request, 'cgroup', $firstGroup, $entity->getEntityKey());
		}

		// get objects
		$collection = new $modelClass;

		// language
		if ($entity->hasLanguage()) {
			$clanguage = $this->getContentLanguage($request, $entity);
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
	 * @param object $entity
	 * @return int|null
	 */
	private function getEntityDefaultTag(object $entity)
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
	 * Get related items from other entities for the current object
	 *
	 * @param string $entityKey
	 * @param int $id
	 * @return array
	 */
	private function getRelated(string $entityKey, int $id)
	{

		$relatedItems = Related::where('entity_key', $entityKey)
			->where('object_id', $id)
			->get();

		$related = array();
		foreach ($relatedItems as $rel) {

			$relatedObject = $rel->related_model_class::find($rel->related_object_id);

			if ($relatedObject) {

				$related[] = [
					'rel_id'             => $rel->id,
					'related_entity_key' => $rel->related_entity_key,
					'related_object_id'  => $rel->related_object_id,
					'title'              => $relatedObject->title,
					'slug'               => $relatedObject->slug,
					'url'                => '',
				];

			} else {

				// object no longer exists
				Related::destroy($rel->id);

			}
		}

		return $related;
	}

	/**
	 * Save related items from other entities for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param int $id
	 * @return void
	 */
	private function saveRelated(Request $request, object $entity, int $id)
	{

		if ($entity->hasRelated()) {

			if ($request->has('_delete_related')) {

				$relDelArray = explode('_', $request->input('_delete_related'));
				$rel_id = end($relDelArray);

				Related::destroy($rel_id);

			} else {

				$relents = Entity::whereHas('objectrelations', function ($query) {
					$query->where('is_relatable', 1);
				})->get();

				foreach ($relents as $relent) {

					$fieldname = '_new_related_' . $relent->entity_key;

					if (!empty($request->input($fieldname))) {

						Related::create([
							'entity_key'          => $entity->getEntityKey(),
							'object_id'           => $id,
							'related_entity_key'  => $relent->entity_key,
							'related_model_class' => $relent->getEntityModelClass(),
							'related_object_id'   => $request->input($fieldname),
						]);

					}

				}

				// menu items
				$menuEntity = Entity::where('entity_key', 'menuitem')->first();
				$fieldname = '_new_related_' . $menuEntity->entity_key;
				if (!empty($request->input($fieldname))) {

					Related::create([
						'entity_key'          => $entity->getEntityKey(),
						'object_id'           => $id,
						'related_entity_key'  => $menuEntity->entity_key,
						'related_model_class' => $menuEntity->getEntityModelClass(),
						'related_object_id'   => $request->input($fieldname),
					]);

				}

			}

		}

	}

	/**
	 * Get content entities that can be used for related items
	 * For example:
	 * You can use a blog article as a related item,
	 * but you can not use a widget or a slider as a related item.
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param int $id
	 * @return array
	 */
	private function getRelatable(Request $request, object $entity, int $id)
	{

		$clanguage = $this->getContentLanguage($request, $entity);

		$entities = Entity::whereHas('objectrelations', function ($query) {
			$query->where('is_relatable', 1);
		})->get();

		$relatable = array();

		foreach ($entities as $relent) {

			$relatable[$relent->entity_key]['entity_key'] = $relent->entity_key;
			$relatable[$relent->entity_key]['title'] = $relent->title;

			// check if entity is available in the menu
			$check = Menuitem::langIs($clanguage)->where('entity_id', $relent->id)->first();
			if ($check) {
				$relatable[$relent->entity_key]['disabled'] = false;
			} else {
				$relatable[$relent->entity_key]['disabled'] = true;
			}

			// get entity objects
			$prefix = $relent->egroup->key . '_prefix';
			$table = config('lara-common.database.entity.' . $prefix) . str_plural($relent->entity_key);

			/*
			 * we have to left join the related table
			 * to avoid showing items that are already related
			 */

			$related_table = config('lara-common.database.object.related');

			$query = "SELECT o.id, o.title
						FROM " . $table . " o
						LEFT JOIN " . $related_table . " r ON o.id=r.related_object_id
						AND r.related_entity_key=? AND r.entity_key=? AND r.object_id=?
						WHERE o.language=?
						AND o.deleted_at IS NULL
						AND r.id IS NULL ";

			if ($relent->entity_key == 'page') {
				$query .= " AND o.cgroup='page' ";
			}

			if ($relent->columns->sort_field && $relent->columns->sort_order) {
				$sort = " ORDER BY " . $relent->columns->sort_field . " " . $relent->columns->sort_order;
			} else {
				$sort = " ORDER BY id";
			}

			if ($relent->entity_key == $entity->getEntityKey()) {

				// get all non-related objects, and exclude self
				$query .= " AND o.id != ? ";

				// add sorting
				$query = $query . $sort;

				$entityObjects = DB::select($query, [$relent->entity_key, $entity->entity_key, $id, $clanguage, $id]);

			} else {

				// add sorting
				$query = $query . $sort;

				// get all non-related objects
				$entityObjects = DB::select($query, [$relent->entity_key, $entity->entity_key, $id, $clanguage]);

			}

			$entityArray = array();

			foreach ($entityObjects as $entityObject) {
				$entityArray[$entityObject->id] = $entityObject->title;
			}

			$relatable[$relent->entity_key]['objects'] = $entityArray;

		}

		// add menu items with modules
		$menuArray = array();
		$menuitems = Menuitem::where('type', 'entity')->orWhere('type', 'form')->get();
		foreach ($menuitems as $menuitem) {
			$menuArray[$menuitem->id] = $menuitem->title;
		}

		$relatable['menuitem']['entity_key'] = 'menuitem';
		$relatable['menuitem']['title'] = 'Module pages';
		$relatable['menuitem']['disabled'] = false;
		$relatable['menuitem']['objects'] = $menuArray;

		return $relatable;
	}

	/**
	 * Save the custom layout for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveLayout(Request $request, object $entity, object $object)
	{

		if ($entity->hasLayout()) {

			// purge old customn values
			$object->layout()->delete();

			// get default layout
			$partials = $this->getDefaultLayout();

			// loop through layout keys
			foreach ($partials as $key => $value) {

				// compare request value with default value
				if ($request->input('_layout_' . $key) != $value) {

					// save only the custom values
					$object->layout()->create([
						'layout_key'   => $key,
						'layout_value' => $request->input('_layout_' . $key),
					]);

				}
			}
		}

	}

	/**
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveObjectTags(Request $request, object $entity, object $object)
	{

		if ($entity->hasTags()) {

			$object->tags()->sync($request->input('_tags_array'));

		}

	}

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

			$lara = $this->getEntityVarByKey($entity->getEntityKey());
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
	 * @return void
	 */
	private function checkEntityImagePosition(object $entity)
	{

		$status = $this->checkEntityImagePositionDone($this->entity);

		if ($status === false) {

			$lara = $this->getEntityVarByKey($entity->getEntityKey());
			$entity = new $lara;

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
	private function syncFilesArchive($entity)
	{

		if ($entity->hasFiles()) {

			$modelClass = $entity->getEntityModelClass();
			$entkey = $entity->getEntityKey();

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

			$modelClass = $entity->getEntityModelClass();
			$entkey = $entity->getEntityKey();

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
	 * Save the widget settings for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveLaraWidgets(Request $request, object $entity, object $object)
	{

		if ($entity->getEntityKey() == 'page') {

			if ($request->has('_save_larawidget')) {

				$larawidgetSaveArray = explode('_', $request->input('_save_larawidget'));
				$larawidgetID = end($larawidgetSaveArray);

				$larawidget = $object->widgets()->find($larawidgetID);

				if ($request->input('_larawidget_filtertaxonomy_' . $larawidgetID) == 'none') {
					$filtertaxonomy = null;
				} else {
					$filtertaxonomy = $request->input('_larawidget_filtertaxonomy_' . $larawidgetID);
				}

				$larawidget->title = $request->input('_larawidget_title_' . $larawidgetID);
				$larawidget->hook = $request->input('_larawidget_hook_' . $larawidgetID);
				$larawidget->sortorder = $request->input('_larawidget_sortorder_' . $larawidgetID);
				$larawidget->template = $request->input('_larawidget_template_' . $larawidgetID);

				if ($larawidget->type == 'text') {

					$larawidget->body = $request->input('_larawidget_body_' . $larawidgetID);

				} elseif ($larawidget->type == 'module') {

					$larawidget->term = $filtertaxonomy;
					$larawidget->imgreq = $request->input('_larawidget_imgreq_' . $larawidgetID);
					$larawidget->maxitems = $request->input('_larawidget_maxitems_' . $larawidgetID);
					$larawidget->usecache = $request->input('_larawidget_usecache_' . $larawidgetID);

				}

				$larawidget->save();

			}

		}

	}

	/**
	 * Save the sync settings for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveSync(Request $request, object $entity, object $object)
	{

		if ($entity->hasSync()) {

			if ($object->sync()->count() == 0) {

				// no sync defined yet, check for a new sync
				if (!empty($request->input('_new_remote_url'))) {

					// create new sync
					$object->sync()->create([
						'remote_url'    => $request->input('_new_remote_url'),
						'remote_suffix' => $request->input('_new_remote_suffix'),
						'ent_key'       => $entity->getEntityKey(),
						'slug'          => $object->slug,
					]);

				}

			} else {

				if ($request->input('_remote_delete') == 'DELETE') {

					$object->sync->delete();

				} else {

					// update current sync
					$object->sync->remote_url = $request->input('_remote_url');
					$object->sync->save();

				}

			}

		}

	}

	/**
	 * Save the SEO settings for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveSeo(Request $request, object $entity, object $object)
	{

		if ($entity->hasSeo()) {

			if ($object->seo()->count() == 0) {

				// create
				$object->seo()->create([
					'seo_focus'       => $request->input('_seo_focus'),
					'seo_title'       => $request->input('_seo_title'),
					'seo_description' => $request->input('_seo_description'),
					'seo_keywords'    => $request->input('_seo_keywords'),
				]);

			} else {

				// update
				$object->seo->seo_focus = $request->input('_seo_focus');
				$object->seo->seo_title = $request->input('_seo_title');
				$object->seo->seo_description = $request->input('_seo_description');
				$object->seo->seo_keywords = $request->input('_seo_keywords');

				$object->seo->save();

			}

		}

	}

	/**
	 * Save the Opengraph settings for the current object
	 *
	 * @param Request $request
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveOpengraph(Request $request, object $entity, object $object)
	{

		if ($entity->hasOpengraph()) {

			// fix empty values
			$og_title = ($request->input('_og_title')) ? $request->input('_og_title') : '';
			$og_description = ($request->input('_og_description')) ? $request->input('_og_description') : '';
			$og_image = ($request->input('_og_image')) ? $request->input('_og_image') : '';

			if ($object->opengraph()->count() == 0) {

				// create
				if ($request->has('_delete_image')) {
					$object->opengraph()->create([
						'og_title'       => $og_title,
						'og_description' => $og_description,
					]);
				} else {
					$object->opengraph()->create([
						'og_title'       => $og_title,
						'og_description' => $og_description,
						'og_image'       => $og_image,
					]);
				}

			} else {

				// update
				$object->opengraph->og_title = $og_title;
				$object->opengraph->og_description = $og_description;

				if (!$request->has('_delete_image')) {
					$object->opengraph->og_image = $og_image;
				}

				$object->opengraph->save();

			}

		}

	}

	/**
	 * Save GEO Coordinates based on the address
	 *
	 * @param object $entity
	 * @param object $object
	 * @return void
	 */
	private function saveGeo(object $entity, object $object)
	{

		if ($entity->hasFields()) {

			foreach ($entity->getCustomColumns() as $field) {

				if ($field->fieldtype == 'geolocation') {

					$fieldname = $field->fieldname;

					if ($object->$fieldname == 'auto') {

						// check if latitude and longitude are already set
						if (empty($object->latitude) || $object->latitude == 0 || empty($object->longitude) || $object->longitude == 0) {

							// if not, check if address is complete
							if (!empty($object->address) && !empty($object->pcode) && !empty($object->city) && !empty($object->country)) {

								// Get GEO Coordinates from Google API
								$geoAddress = $object->address;
								$geoAddress .= ', ' . $object->pcode;
								$geoAddress .= ', ' . $object->city;
								$geoAddress .= ', ' . $object->country;

								$geo = Geocoder::getCoordinatesForAddress($geoAddress);

								$latitude = $geo['lat'];
								$longitude = $geo['lng'];

								if (!empty($latitude) && !empty($longitude)) {

									// Save GEO Coordinates
									$object->latitude = $latitude;

									$object->longitude = $longitude;

									$object->save();

								}

							}
						}

					}

				}

			}

		}

	}

	/**
	 * Check the GEO settings for the company address
	 *
	 * @return void
	 */
	private function checkGeoSettings($force = false)
	{

		if (config('app.env') == 'production' || $force) {

			// Get and save GEO coordinates if necessary
			$settings = Setting::pluck('value', 'key')->toArray();
			$settings = json_decode(json_encode($settings), false);

			if (empty($settings->company_latitude) || $settings->company_latitude == 0 || empty($settings->company_longitude) || $settings->company_longitude == 0) {

				// if not, check if address is complete
				if (!empty($settings->company_street) && !empty($settings->company_street_nr) && !empty($settings->company_pcode) && !empty($settings->company_city) && !empty($settings->company_country)) {

					// Get GEO Coordinates from Google API
					$geoAddress = $settings->company_street;
					$geoAddress .= ', ' . $settings->company_pcode;
					$geoAddress .= ', ' . $settings->company_city;
					$geoAddress .= ', ' . $settings->company_country;

					$geo = Geocoder::getCoordinatesForAddress($geoAddress);

					$latitude = $geo['lat'];
					$longitude = $geo['lng'];

					if (!empty($latitude) && !empty($longitude)) {

						// Save GEO Coordinates
						$latObject = Setting::keyIs('company_latitude')->first();
						if ($latObject) {
							$latObject->value = $latitude;
							$latObject->save();
						}

						$longObject = Setting::keyIs('company_longitude')->first();
						if ($longObject) {
							$longObject->value = $longitude;
							$longObject->save();
						}

					}

				}

			}

		}

	}

	/**
	 * Get the view file
	 *
	 * Because we use default views as fallback,
	 * we have to fetch the view file dynamically
	 * We cache the view file to boost performance
	 *
	 * @param object $entity
	 * @return mixed
	 */
	private function getViewFile(object $entity)
	{

		$laraprefix = 'lara-';

		$cache_key = $entity->getEntityKey() . '_' . $entity->getMethod() . '_view';

		$viewfile = Cache::rememberForever($cache_key, function () use ($entity, $laraprefix) {

			if ($entity->getPrefix() == 'admin') {
				// module can be 'admin' or 'entity', depending on entity group
				$entityViewFile = $laraprefix . $entity->getModule() . '::' . $entity->getEntityKey() . '.' . $entity->getMethod();
			} else {
				// builder
				$entityViewFile = $laraprefix . $entity->getPrefix() . '::' . $entity->getEntityKey() . '.' . $entity->getMethod();
			}

			$defaultViewFile = $laraprefix . $entity->getPrefix() . '::_default.' . $entity->getMethod();

			if (view()->exists($entityViewFile)) {
				return $entityViewFile;
			} else {
				return $defaultViewFile;
			}

		});

		return $viewfile;

	}

	/**
	 * Get all partial view files
	 *
	 * Because we use default partials as fallback,
	 * we have to fetch partials dynamically
	 * We cache the partials to boost performance
	 *
	 * @param object $entity
	 * @return mixed
	 */
	private function getPartials(object $entity)
	{

		$cache_key = $entity->getEntityKey() . '_' . $entity->getMethod() . '_partials';

		$partials = Cache::rememberForever($cache_key, function () use ($entity) {

			$parts = config('lara-front.partials');
			$partialArray = array();
			foreach ($parts as $part) {

				$viewfile = $part['partial'];
				if (!is_null($part['action'])) {
					$method = $part['action'];
				} else {
					$method = $entity->getMethod();
				}
				$partialArray[$viewfile] = $this->getPartial($entity, $method, $viewfile);

			}

			return $partialArray;

		});

		return $partials;

	}

	/**
	 * Get a specific partial view file
	 *
	 * Because we use default partials as fallback,
	 * we have to fetch partials dynamically
	 *
	 * @param object $entity
	 * @param string $method
	 * @param string $viewfile
	 * @return string|null
	 */
	private function getPartial(object $entity, string $method, string $viewfile)
	{
		$laraprefix = 'lara-';

		if ($entity->getPrefix() == 'admin') {
			// module can be 'admin' or 'entity', depending on entity group
			$entityViewFile = $laraprefix . $entity->getModule() . '::' . $entity->getEntityKey() . '.' . $method . '.' . $viewfile;
		} else {
			// builder
			$entityViewFile = $laraprefix . $entity->getPrefix() . '::' . $entity->getEntityKey() . '.' . $method . '.' . $viewfile;
		}

		$defaultViewFile = 'lara-admin::_default.' . $method . '.' . $viewfile;

		if (view()->exists($entityViewFile)) {
			return $entityViewFile;
		} elseif (view()->exists($defaultViewFile)) {
			return $defaultViewFile;
		} else {
			return null;
		}

	}

	/**
	 * Get the module name based on the entity key
	 *
	 * @param string $entityKey
	 * @return string
	 */
	private function getModuleByEntityKey(string $entityKey)
	{

		$entity = Entity::where('entity_key', $entityKey)->first();

		if ($entity && ($entity->egroup->key == 'entity' || $entity->egroup->key == 'form')) {
			$module = 'eve';
		} else {
			$module = 'admin';
		}

		return $module;

	}

	/**
	 * Get the default full layout of the theme
	 * from the special layout xml file
	 *
	 * The layout that is returned
	 * contains all options for all partials
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getFullLayout()
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = $partial->key;

			$layout->$partial_key = $app->make('stdClass');

			foreach ($partial->items->item as $item) {

				$item_key = (string)$item->itemKey;

				$layout->$partial_key->$item_key = $app->make('stdClass');

				$layout->$partial_key->$item_key->friendlyName = (string)$item->friendlyName;
				$layout->$partial_key->$item_key->partialFile = (string)$item->partialFile;
				$layout->$partial_key->$item_key->isDefault = (string)$item->isDefault;
			}

		}

		return $layout;

	}

	/**
	 * Get the custom layout for the current object
	 * The custom layout can override the default layout settings
	 *
	 * @param object $object
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getObjectLayout(object $object)
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = (string)$partial->key;

			// get custom layout value from database
			foreach ($object->layout as $item) {
				if ($item->layout_key == $partial_key) {
					$layout->$partial_key = $item->layout_value;
				}
			}

			if (empty($layout->$partial_key)) {
				// get default value from layout XML
				foreach ($partial->items->item as $item) {
					if ($item->isDefault == 'true') {
						$layout->$partial_key = (string)$item->partialFile;
					}
				}
			}

		}

		return $layout;

	}

	/**
	 * Get the default full layout of the theme
	 * from the special layout xml file
	 *
	 * The layout that is returned only contains
	 * the default options for the partials
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getDefaultLayout()
	{

		$layoutPath = Theme::path('views') . '/_layout/_layout.xml';
		$partials = simplexml_load_file($layoutPath);

		$app = app();
		$layout = $app->make('stdClass');

		foreach ($partials as $partial) {

			$partial_key = $partial->key;

			foreach ($partial->items->item as $item) {
				if ($item->isDefault == 'true') {
					if ($item->partialFile == 'false') {
						$layout->$partial_key = false;
					} else {
						$layout->$partial_key = (string)$item->partialFile;
					}
				}
			}
		}

		return $layout;

	}

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
	private function getRequestParam(Request $request, string $param, $default = null, $tag = 'global', $reset = false)
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
	 * Get the content language
	 *
	 * Important:
	 * in the backend the content langauge is NOT the same as the interface language
	 * For example:
	 * You can edit Dutch content in an English interface, or vice versa.
	 *
	 * @param Request $request
	 * @param object|null $entity
	 * @return mixed
	 */
	private function getContentLanguage(Request $request, $entity = null)
	{

		$key = 'clanguage';

		$default = config('lara.clanguage_default');

		if (!empty($entity) && $entity->hasLanguage()) {

			// get language from request or session
			$clanguage = $this->getRequestParam($request, $key, $default, 'global');

		} else {

			// set default language
			$clanguage = $default;
		}

		return $clanguage;

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
	 * Get the status of an object
	 *
	 * Check the publish field
	 * Check the publish_from field
	 * Check the publish_to field
	 *
	 * @param object $entity
	 * @param object $object
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getObjectStatus(object $entity, object $object)
	{

		$app = app();
		$status = $app->make('stdClass');

		if ($entity->hasStatus()) {

			if ($object->publish == 0) {

				$status->publish = false;
				$status->message = _lanq('lara-admin::default.message.status_concept');

			} else {

				if ($object->publish_from > Carbon::now()->toDateTimeString()) {

					$status->publish = false;
					$status->message = _lanq('lara-admin::default.message.status_not_published_yet');

				} elseif ($entity->hasExpiration() && !is_null($object->publish_to) && $object->publish_to < Carbon::now()->toDateTimeString()) {

					$status->publish = false;
					$status->message = _lanq('lara-admin::default.message.status_expired');

				} else {

					$status->publish = true;
					$status->message = _lanq('lara-admin::default.message.status_published');

				}

			}

		} else {
			$status->publish = true;
			$status->message = _lanq('lara-admin::default.message.status_always_published');
		}

		return $status;

	}

	/**
	 * Create a flash message
	 *
	 * @param string $entityKey
	 * @param int|null $objectCount
	 * @param string $messageKey
	 * @param string $type
	 * @return void
	 */
	private function flashMessage(string $entityKey, string $messageKey, string $type, int $objectCount = null)
	{

		$module = $this->getModuleByEntityKey($entityKey);

		$entitySingle = _lanq('lara-' . $module . '::' . $entityKey . '.entity.entity_single');
		$entityPlural = _lanq('lara-' . $module . '::' . $entityKey . '.entity.entity_plural');

		$message = _lanq('lara-admin::default.message.' . $messageKey);

		if (empty($objectCount)) {
			$fullMessage = $entitySingle . ' ' . $message;
		} elseif ($objectCount == 1) {
			$fullMessage = $objectCount . ' ' . $entitySingle . ' ' . $message;
		} elseif ($objectCount > 1) {
			$fullMessage = $objectCount . ' ' . $entityPlural . ' ' . $message;
		} else {
			$fullMessage = $entitySingle . ' ' . $message;
		}

		if ($type == 'success') {
			flash($fullMessage)->success();
		} elseif ($type == 'warning') {
			flash($fullMessage)->warning();
		} elseif ($type == 'error') {
			flash($fullMessage)->errorOnDisk();
		} elseif ($type == 'overlay') {
			flash($fullMessage)->overlay();
		}

	}

	/**
	 * Get the value of a specific key in the Setting Model
	 *
	 * @param string $cgroup
	 * @param string $key
	 * @param string|null $type
	 * @return Carbon|string|false
	 */
	private function getSetting(string $cgroup, string $key, $type = null)
	{

		$modelClass = \Lara\Common\Models\Setting::class;

		// get record
		$object = $modelClass::where('cgroup', $cgroup)
			->where('key', $key)
			->first();

		if ($object) {

			if ($type == 'date') {
				return Carbon::createFromFormat('Y-m-d H:i:s', $object->value);
			} else {
				return $object->value;
			}

		} else {

			return false;

		}

	}

	/**
	 * Save the value of a specific key in the Setting Model
	 *
	 * @param string $cgroup
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	private function setSetting(string $cgroup, string $key, string $value)
	{

		$modelClass = \Lara\Common\Models\Setting::class;

		// get record
		$object = $modelClass::where('cgroup', $cgroup)
			->where('key', $key)
			->first();

		if ($object) {

			$object->value = $value;
			$object->save();

		} else {

			$modelClass::create([
				'title'  => $key,
				'cgroup' => $cgroup,
				'key'    => $key,
				'value'  => $value,
			]);

		}

	}

	/**
	 * @param string $language
	 * @param object $entity
	 * @param bool $array
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function getAllEntityTags(string $language, object $entity, bool $array = false)
	{

		if ($array) {
			$tags = array();
		} else {
			$tags = $this->makeNewObject();
		}

		$taxonomies = Taxonomy::get();

		foreach ($taxonomies as $taxonomy) {

			$key = $taxonomy->slug;

			if ($entity->hasTags()) {

				$root = Tag::langIs($language)
					->entityIs($entity->getEntityKey())
					->taxonomyIs($taxonomy->id)
					->whereNull('parent_id')
					->first();

				if (empty($root)) {

					$root = Tag::create([
						'language'    => $language,
						'entity_key'  => $entity->getEntityKey(),
						'taxonomy_id' => $taxonomy->id,
						'title'       => 'root',
						'slug'        => null,
						'body'        => '',
						'lead'        => '',
					]);
				}

				if ($array) {

					// If we want an array, we assume that we don't need the root(s)

					// kalnoy/nestedset
					$tagz = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomy->id])
						->defaultOrder()
						->hasParent()
						->get()
						->toArray();

					$tags = array_merge($tags, $tagz);

				} else {

					// kalnoy/nestedset
					$tags->$key = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomy->id])
						->defaultOrder()
						->get()
						->toTree();

				}

			}

		}

		return $tags;

	}

	/**
	 * Get the entity tag tree
	 * Check if the entity already has a tag root
	 * If not, create a root item
	 *
	 * @param string $language
	 * @param object $entity
	 * @param string|null $taxonomy
	 * @return object|null
	 */
	private function getEntityTags(string $language, object $entity, string $taxonomy = null)
	{

		$tags = null;

		// get Taxonomy ID
		$taxonomyId = $this->getTaxonomyIdbySlug($taxonomy);

		if ($taxonomyId) {

			if ($entity->hasTags()) {

				$root = Tag::langIs($language)
					->entityIs($entity->getEntityKey())
					->taxonomyIs($taxonomyId)
					->whereNull('parent_id')
					->first();

				if (empty($root)) {

					$root = Tag::create([
						'language'    => $language,
						'entity_key'  => $entity->getEntityKey(),
						'taxonomy_id' => $taxonomyId,
						'title'       => 'root',
						'slug'        => null,
						'body'        => '',
						'lead'        => '',
					]);
				}

				// kalnoy/nestedset
				$tags = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomyId])
					->defaultOrder()
					->get()
					->toTree();

			}

		}

		return $tags;

	}

	/**
	 * @param string|null $slug
	 * @return int|null
	 */
	private function getTaxonomyIdbySlug(string $slug = null)
	{

		if ($slug) {
			$taxonomy = Taxonomy::where('slug', $slug)->first();
			if ($taxonomy) {
				return $taxonomy->id;
			} else {
				$defaultTaxonomy = $this->getDefaultTaxonomy();

				return $defaultTaxonomy->id;
			}
		} else {
			$defaultTaxonomy = $this->getDefaultTaxonomy();

			return $defaultTaxonomy->id;
		}

	}

	/**
	 * @return object|null
	 */
	private function getDefaultTaxonomy()
	{

		$taxonomy = Taxonomy::where('is_default', 1)->first();
		if ($taxonomy) {
			return $taxonomy;
		} else {
			return null;
		}

	}

	/**
	 * Kalnoy/nestedset does not store depth in the database
	 * To make it compatible with baum (legacy) we add the depth to the database
	 *
	 * @param object $root
	 * @param array|null $scopeColumns
	 * @return void
	 */
	private function addDepthToNestedSet(object $root, $scopeColumns = null)
	{

		$modelClass = get_class($root);

		// kalnoy/nestedset
		if ($scopeColumns) {

			// get scope values from root
			$scope = array();
			foreach ($scopeColumns as $column) {
				$scope[$column] = $root->$column;
			}

			$tree = $modelClass::scoped($scope)
				->defaultOrder()
				->get()
				->toTree();

		} else {

			$tree = $modelClass::defaultOrder()
				->get()
				->toTree();

		}

		foreach ($tree as $node) {
			$this->addDepthToNode($node);
		}

	}

	/**
	 * Kalnoy/nestedset does not store depth in the database
	 * To make it compatible with baum (legacy) we add the depth to the database
	 *
	 * @param object $node
	 * @return void
	 */
	private function addDepthToNode(object $node)
	{

		$depth = sizeof($node->ancestors);

		$node->depth = $depth;
		$node->save();

		foreach ($node->children as $child) {
			$this->addDepthToNode($child);
		}

	}

	/**
	 * Rebuild the tag routes
	 *
	 * Because we use nested sets and seo urls with tags
	 * we need to rebuild the tag routes everytime we update a tag
	 *
	 * @param int $id
	 * @return void
	 */
	private function rebuildTagRoutes(int $id)
	{

		// get root
		$object = Tag::find($id);

		// kalnoy/nestedset
		$root = $this->getNestedSetTagRoot($object);

		$tree = Tag::scoped(['entity_key' => $object->entity_key, 'language' => $root->language, 'taxonomy_id' => $root->taxonomy_id])
			->defaultOrder()
			->get()
			->toTree();

		foreach ($tree as $node) {
			$this->processTagNode($node);
		}

		$this->clearRouteCache();

	}

	/*
	 * Kalnoy/nestedset
	 * Custom function to replace the getRoot method that Baum (legacy) used
	 */

	/**
	 * @param object $object
	 * @return mixed
	 */
	private function getNestedSetTagRoot(object $node)
	{

		if ($node->isRoot()) {
			return $node;
		} else {
			// get parent
			$parent = Tag::find($node->parent_id);

			return $this->getNestedSetTagRoot($parent);
		}

	}

	/**
	 * Build and save tag route recursively
	 *
	 * @param object $node
	 * @param string|null $parentRoute
	 * @return void
	 */
	private function processTagNode(object $node, $parentRoute = null)
	{

		if ($node->depth == 1) {
			$node->route = $node->slug;
			$node->save();
		}

		if ($node->depth > 1) {
			$node->route = $parentRoute . '.' . $node->slug;
			$node->save();
		}

		foreach ($node->children as $child) {
			// pass parent route to children
			$this->processTagNode($child, $node->route);
		}
	}

	/**
	 * Set the session key to clear the route cache
	 *
	 * The artisan command will be called with an AJAX call
	 * If we call it directly here, the redirects will not work properly
	 *
	 * @return bool
	 */
	private function clearRouteCache()
	{

		session(['routecacheclear' => true]);

		return true;

	}

	/**
	 * Get the real DB column type for custom field types
	 *
	 * Examples:
	 * - mcefull = text
	 * - email = string
	 * - selectone = text
	 *
	 * @param string $coltype
	 * @return string
	 */
	private function getRealColumnType(string $coltype)
	{

		// get supported field types from config
		$fieldTypes = json_decode(json_encode(config('lara-admin.fieldTypes')), false);

		// get real column type for this type
		$realcoltype = $fieldTypes->$coltype->type;

		return $realcoltype;

	}

	/**
	 * @param int $entity_id
	 * @param int $object_id
	 * @return bool
	 */
	private function isObjectInMenu(int $entity_id, int $object_id)
	{

		$menuitem = Menuitem::where('entity_id', $entity_id)->where('object_id', $object_id)->get();

		if ($menuitem->count() > 0) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * @param string $table
	 * @param string $column
	 * @param mixed $value
	 * @param int|null $exclude_id
	 * @return void
	 */
	private function updateAllRows(string $table, string $column, $value, $exclude_id = null)
	{

		if (!empty($exclude_id)) {
			DB::table($table)->where('id', '!=', $exclude_id)->update(array($column => $value));
		} else {
			DB::table($table)->update(array($column => $value));
		}

	}

	/**
	 * Check if the main menu exists
	 * If not, create it
	 *
	 * @return int
	 */
	private function getMainMenuId()
	{

		$mainMenu = Menu::where('slug', 'main')->first();

		if ($mainMenu) {

			return $mainMenu->id;

		} else {

			// create main menu
			$newMainMenu = Menu::create([
				'title' => 'Main',
				'slug'  => 'main',
			]);

			return $newMainMenu->id;

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
	 * Check if this object is an Entity Block (alias)
	 * if so, redirect (back) to the associated entity
	 *
	 * @param object $object
	 * @param object $entity
	 * @return RedirectResponse|false
	 */
	private function redirectModulePageToEntity(object $object, object $entity)
	{

		$slug = explode('-', $object->slug);
		$associated = $slug[0];

		if ($entity->isAlias() && $entity->getAlias() == 'module') {
			return redirect()->route($entity->getPrefix() . '.' . $associated . '.index')->send();
		} else {
			return false;
		}

	}

	/**
	 * @param string $language
	 * @param object $entity
	 * @param string $method
	 * @return object|null
	 */
	private function getModulePage(string $language, object $entity, string $method)
	{

		if ($entity->getEgroup() == 'entity') {

			$modulePageSlug = $entity->entity_key . '-' . $method . '-module-' . $language;

			$modulePage = Page::langIs($language)->groupIs('module')->where('slug', $modulePageSlug)->first();

			return $modulePage; // returns null if not found

		} else {

			return null;

		}

	}

	/**
	 * Before we can delete an entity object, we have to delete all Polymorphic Relations,
	 * because we can not use CASCADE DELETE on Polymorphic Relations.
	 *
	 * @param object $entity
	 * @param object $object
	 * @param bool $force
	 * @return void
	 */
	private function deleteEntityObject(object $entity, object $object, $force = false)
	{

		if ($force) {

			if ($entity->hasImages()) {
				$object->media()->forceDelete();
			}
			if ($entity->hasVideos()) {
				$object->videos()->forceDelete();
			}
			if ($entity->hasFiles()) {
				$object->files()->forceDelete();
			}
			if ($entity->hasLayout()) {
				$object->layout()->forceDelete();
			}
			if ($entity->hasSeo()) {
				$object->seo()->forceDelete();
			}
			if ($entity->hasSync()) {
				$object->sync()->forceDelete();
			}

		} else {

			if ($entity->hasImages()) {
				$object->media()->delete();
			}
			if ($entity->hasVideos()) {
				$object->videos()->delete();
			}
			if ($entity->hasFiles()) {
				$object->files()->delete();
			}
			if ($entity->hasLayout()) {
				$object->layout()->delete();
			}
			if ($entity->hasSeo()) {
				$object->seo()->delete();
			}
			if ($entity->hasSync()) {
				$object->sync()->delete();
			}

		}

		// delete tags
		DB::table(config('lara-common.database.object.taggables'))
			->where('entity_type', $entity->getEntityModelClass())
			->where('entity_id', $object->id)
			->delete();

		// delete related
		DB::table(config('lara-common.database.object.related'))
			->where('entity_key', $entity->getEntityKey())
			->where('object_id', $object->id)
			->delete();

		// delete pageable
		DB::table(config('lara-common.database.object.pageables'))
			->where('entity_type', $entity->getEntityModelClass())
			->where('entity_id', $object->id)
			->delete();

		// delete object itself
		if ($force) {
			$object->forceDelete();
		} else {
			$object->delete();
		}

	}

	/**
	 * @param object $entity
	 * @param array $objectIDs
	 * @return int
	 */
	private function batchDeleteObjects(object $entity, array $objectIDs)
	{

		$objectCount = sizeof($objectIDs);

		foreach ($objectIDs as $objectID) {
			$modelClass = $entity->getEntityModelClass();
			$object = $modelClass::findOrFail($objectID);
			if ($object) {
				$object->delete();
			}
		}

		return $objectCount;

	}

	/**
	 * @param object $entity
	 * @param array $objectIDs
	 * @return int
	 */
	private function batchPublishObjects(object $entity, array $objectIDs)
	{

		$objectCount = sizeof($objectIDs);

		foreach ($objectIDs as $objectID) {
			$modelClass = $entity->getEntityModelClass();
			$object = $modelClass::findOrFail($objectID);
			if ($object) {
				$object->publish = 1;
				$object->save();
			}
		}

		return $objectCount;

	}

	/**
	 * @param object $entity
	 * @param array $objectIDs
	 * @return int
	 */
	private function batchUnPublishObjects(object $entity, array $objectIDs)
	{

		$objectCount = sizeof($objectIDs);

		foreach ($objectIDs as $objectID) {
			$modelClass = $entity->getEntityModelClass();
			$object = $modelClass::findOrFail($objectID);
			if ($object) {
				$object->publish = 0;
				$object->save();
			}
		}

		return $objectCount;

	}

	/**
	 * purge orphans from polymorphic relations
	 *
	 * @return void
	 */
	private function checkForOrphans()
	{

		// timestamp
		$this->setSetting('system', 'lara_cleanup_orphans', Carbon::now());

		// media
		$images = MediaImage::all();
		foreach ($images as $image) {
			if (class_exists($image->entity_type)) {
				$object = $image->entity()->first();
				if (empty($object)) {
					$image->delete();
				}
			} else {
				$image->delete();
			}
		}

		// videos
		$videos = MediaVideo::all();
		foreach ($videos as $video) {
			if (class_exists($video->entity_type)) {
				$object = $video->entity()->first();
				if (empty($object)) {
					$video->delete();
				}
			} else {
				$video->delete();
			}
		}

		// files
		$files = MediaFile::all();
		foreach ($files as $file) {
			if (class_exists($file->entity_type)) {
				$object = $file->entity()->first();
				if (empty($object)) {
					$file->delete();
				}
			} else {
				$file->delete();
			}
		}

		// layout
		$layouts = Layout::all();
		foreach ($layouts as $layout) {
			if (class_exists($layout->entity_type)) {
				$object = $layout->entity()->first();
				if (empty($object)) {
					$layout->delete();
				}
			} else {
				$layout->delete();
			}
		}

		// opengraph
		$ogs = ObjectOpengraph::all();
		foreach ($ogs as $og) {
			if (class_exists($og->entity_type)) {
				$object = $og->entity()->first();
				if (empty($object)) {
					$og->delete();
				}
			} else {
				$og->delete();
			}
		}

		// seo
		$seos = ObjectSeo::all();
		foreach ($seos as $seo) {
			if (class_exists($seo->entity_type)) {
				$object = $seo->entity()->first();
				if (empty($object)) {
					$seo->delete();
				}
			} else {
				$seo->delete();
			}
		}

		// sync
		$syncs = Sync::all();
		foreach ($syncs as $sync) {
			if (class_exists($sync->entity_type)) {
				$object = $sync->entity()->first();
				if (empty($object)) {
					$sync->delete();
				}
			} else {
				$sync->delete();
			}
		}

		// related
		$relatedTable = config('lara-common.database.object.related');
		$relations = DB::table($relatedTable)->get();
		foreach ($relations as $relation) {

			// check primary object
			$entity = Entity::where('entity_key', $relation->entity_key)->first();
			if ($entity) {
				$primaryModelClass = $entity->entity_model_class;
				if (class_exists($primaryModelClass)) {
					$primobj = $primaryModelClass::where('id', $relation->object_id)->first();
					if (empty($primobj)) {
						DB::table($relatedTable)->where('id', $relation->id)->delete();
					}
				} else {
					DB::table($relatedTable)->where('id', $relation->id)->delete();
				}
			} else {
				DB::table($relatedTable)->where('id', $relation->id)->delete();
			}

			// check related object
			if (class_exists($relation->related_model_class)) {
				$relatedModelClass = $relation->related_model_class;
				$relobj = $relatedModelClass::where('id', $relation->related_object_id)->first();
				if (empty($relobj)) {
					DB::table($relatedTable)->where('id', $relation->id)->delete();
				}
			} else {
				DB::table($relatedTable)->where('id', $relation->id)->delete();
			}

		}

		// pageable
		$pageablesTable = config('lara-common.database.object.pageables');
		$pageables = DB::table($pageablesTable)->get();
		foreach ($pageables as $pageable) {
			if (class_exists($pageable->entity_type)) {
				$modelClass = $pageable->entity_type;
				$object = $modelClass::where('id', $pageable->entity_id)->first();
				if (empty($object)) {
					DB::table($pageablesTable)->where('id', $pageable->id)->delete();
				}
			} else {
				DB::table($pageablesTable)->where('id', $pageable->id)->delete();
			}
		}

		// taggable
		$taggablesTable = config('lara-common.database.object.taggables');
		$taggables = DB::table($taggablesTable)->get();
		foreach ($taggables as $taggable) {
			if (class_exists($taggable->entity_type)) {
				$modelClass = $taggable->entity_type;
				$object = $modelClass::where('id', $taggable->entity_id)->first();
				if (empty($object)) {
					DB::table($taggablesTable)->where('id', $taggable->id)->delete();
				}
			} else {
				DB::table($taggablesTable)->where('id', $taggable->id)->delete();
			}
		}

		// tags
		$tags = Tag::all();
		foreach ($tags as $tag) {
			$entity = Entity::where('entity_key', $tag->entity_key)->first();
			if ($entity) {
				// assume this entity is still active
			} else {
				$tag->delete();
			}
		}

	}

	/**
	 * Get all language version of this object
	 *
	 * @param object $entity
	 * @param object $object
	 * @return mixed|null
	 */
	private function getLanguageVersions(object $entity, object $object)
	{

		if ($entity->hasLanguage()) {

			// get all language versions
			if ($object->languageParent) {
				$parent = $object->languageParent;
			} else {
				$parent = $object;
			}
			$children = $parent->languageChildren;

			$langversions = array();

			$langversions['parent'] = [
				'object_id' => $parent->id,
				'langcode'  => $parent->language,
				'title'     => $parent->title,
				'url'       => 'parent url',
				'type'      => 'parent',
			];

			if ($children->count()) {
				foreach ($children as $child) {
					$langversions['children'][] = [
						'object_id' => $child->id,
						'langcode'  => $child->language,
						'title'     => $child->title,
						'url'       => 'child url',
						'type'      => 'child',
					];
				}
			} else {
				$langversions['children'] = null;
			}

			// convert array to object
			$langversions = json_decode(json_encode($langversions), false);

			return $langversions;

		} else {

			return null;

		}

	}

	/**
	 * Get the return page for this widget
	 *
	 * @param Request $request
	 * @param string $entity_key
	 * @param int $id
	 * @return object|null
	 */
	private function getWidgetReturnPage(Request $request, string $entity_key, int $id)
	{

		$returnPageID = $this->getRequestParam($request, 'returnpage', null, $entity_key);

		$returnWidgetID = $this->getRequestParam($request, 'returnwidget', null, $entity_key);

		if ($returnPageID && $returnWidgetID == $id) {
			$returnpage = Page::find($returnPageID);

			return $returnpage;
		} else {
			return null;
		}
	}

	/**
	 * Get the related entity model for this module page
	 * The entity key of the related entity can be found in the page slug
	 *
	 * @param object $object
	 * @return mixed
	 */
	private function getModulePageModule(object $object)
	{

		list($relatedEntityKey, $method, $mpage, $lang) = explode('-', $object->slug);

		$laraClass = $this->getEntityVarByKey($relatedEntityKey);
		$modulePageModule = new $laraClass;

		return $modulePageModule;

	}

	/**
	 * @param string $dbname
	 * @param string|null $connection
	 * @param bool $includeManagedTables
	 * @return mixed
	 */
	private function getDatabaseStructure(string $dbname, $connection = null, $includeManagedTables = false)
	{

		$unmanagedTables = ['lara_auth', 'lara_ent', 'lara_menu', 'lara_object', 'lara_sys'];

		if ($connection) {
			$tables = DB::connection($connection)->select('SHOW TABLES');
		} else {
			$tables = DB::select('SHOW TABLES');
		}
		$varname = 'Tables_in_' . $dbname;

		$objects = array();

		foreach ($tables as $table) {

			$tablename = $table->$varname;

			foreach ($unmanagedTables as $unmanagedTable) {

				if (strpos($tablename, $unmanagedTable) !== false || $includeManagedTables) {

					$objects[$tablename] = [
						'tablename' => $tablename,
					];

					if ($connection) {
						$columns = Schema::connection($connection)->getColumnListing($tablename);
					} else {
						$columns = Schema::getColumnListing($tablename);
					}

					foreach ($columns as $column) {

						if ($connection) {
							$coltype = Schema::connection($connection)->getColumnType($tablename, $column);
						} else {
							$coltype = Schema::getColumnType($tablename, $column);
						}

						$columnLength = $this->getMaxColumnLength($dbname, $tablename, $column, $connection);

						$objects[$tablename]['columns'][$column] = [
							'columnname'   => $column,
							'columntype'   => $coltype,
							'columnlength' => $columnLength,
						];
					}

					break;
				}

			}

		}

		// convert to object
		$objects = json_decode(json_encode($objects), false);

		return $objects;
	}

	/**
	 * @param string $database
	 * @param string $table
	 * @param string $column
	 * @param string|null $connection
	 * @return int|null
	 */
	private function getMaxColumnLength(string $database, string $table, string $column, $connection = null)
	{

		$typeQuery = "SELECT DATA_TYPE
				FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = '$database'
				AND TABLE_NAME = '$table'
				AND COLUMN_NAME = '$column'";

		if ($connection) {
			$types = DB::connection($connection)->select($typeQuery);
		} else {
			$types = DB::select($typeQuery);
		}

		if (!empty($types)) {

			$type = $types[0]->DATA_TYPE;

			if ($type == 'int' || $type == 'tinyint' || $type == 'bigint') {
				$lengthfield = 'NUMERIC_PRECISION';
			} elseif ($type == 'varchar') {
				$lengthfield = 'CHARACTER_MAXIMUM_LENGTH';
			} else {
				return null;
			}

			$lengthQuery = "SELECT $lengthfield
				FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = '$database'
				AND TABLE_NAME = '$table'
				AND COLUMN_NAME = '$column'";

			if ($connection) {
				$result = DB::connection($connection)->select($lengthQuery);
			} else {
				$result = DB::select($lengthQuery);
			}

			if (!empty($result)) {
				return $result[0]->$lengthfield;
			} else {
				return null;
			}

		} else {
			return null;
		}

	}

	/**
	 * check all tables that are NOT managed by the Builder.
	 *
	 * @param string $dbnamesrc
	 * @param string $dbnamedest
	 * @param string|null $connsrc
	 * @param string|null $conndest
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function compareDatabaseStructure(string $dbnamesrc, string $dbnamedest, $connsrc = null, $conndest = null)
	{

		$objects = array();

		$sourceTables = $this->getDatabaseStructure($dbnamesrc, $connsrc, false);
		$destTables = $this->getDatabaseStructure($dbnamedest, $conndest, false);

		$errorcount = 0;

		foreach ($sourceTables as $srcTable) {

			$tablename = $srcTable->tablename;

			// add table to result object
			$objects[$tablename] = [
				'tablename' => $tablename,
			];

			// check if table exists in Dest
			if (property_exists($destTables, $tablename)) {

				$destTable = $destTables->$tablename;

				foreach ($srcTable->columns as $srcColumn) {

					// check if column exists

					$srcColumnName = $srcColumn->columnname;
					$srcColumnType = $srcColumn->columntype;
					$srcColumnLength = $srcColumn->columnlength;

					if (property_exists($destTable->columns, $srcColumnName)) {

						$destColumn = $destTable->columns->$srcColumnName;
						$destColumnType = $destColumn->columntype;
						$destColumnLength = $destColumn->columnlength;

						$typeError = $srcColumnType != $destColumnType;
						$lengthError = $srcColumnLength != $destColumnLength;

						$objects[$tablename]['columns'][] = [
							'columnname'    => $srcColumnName,
							'coltypesrc'    => $srcColumnType,
							'collengthsrc'  => $srcColumnLength,
							'coltypedest'   => $destColumnType,
							'collengthdest' => $destColumnLength,
							'columnerror'   => false,
							'typeerror'     => $typeError,
							'lengtherror'   => $lengthError,
						];

						if ($typeError) {
							$errorcount++;
						}

					} else {

						// column not found in DEST

						$objects[$tablename]['columns'][] = [
							'columnname'    => $srcColumnName,
							'coltypesrc'    => $srcColumnType,
							'collengthsrc'  => $srcColumnLength,
							'coltypedest'   => null,
							'collengthdest' => null,
							'columnerror'   => true,
							'typeerror'     => false,
						];

						$errorcount++;
					}

				}

				$objects[$tablename]['tableerror'] = false;

			} else {

				// table not found in DEST

				foreach ($srcTable->columns as $srcColumn) {

					$srcColumnName = $srcColumn->columnname;
					$srcColumnType = $srcColumn->columntype;
					$srcColumnLength = $srcColumn->columnlength;

					$objects[$tablename]['columns'][] = [
						'columnname'   => $srcColumnName,
						'coltypesrc'   => $srcColumnType,
						'collengthsrc' => $srcColumnLength,
					];
				}

				$objects[$tablename]['tableerror'] = true;

				$errorcount++;
			}
		}

		$app = app();
		$result = $app->make('stdClass');

		// convert to object
		$result->objects = json_decode(json_encode($objects), false);

		// errors
		$result->error = $app->make('stdClass');
		$result->error->error = $errorcount > 0;
		$result->error->errorcount = $errorcount;

		return $result;

	}

	/**
	 * @return void
	 */
	private function clearCacheOnSave($entity)
	{
		if (in_array($entity->getEgroup(), config('lara.clear_cache_on_save'))) {
			Artisan::call('cache:clear');
			Artisan::call('httpcache:clear');
		}
	}

	private function purgeSpam()
	{

		// get all form entities
		$entities = Entity::entityGroupIs('form')->get();
		foreach ($entities as $entity) {
			$modelClass = $entity->getEntityModelClass();
			$trashed = $modelClass::onlyTrashed()->where('deleted_at', '<=', now()->subMonth())->get();
			foreach ($trashed as $object) {
				$object->forceDelete();
			}
		}

	}

	/**
	 * @param $request
	 * @param $object
	 * @return bool
	 */
	private function saveUserProfile($request, $object)
	{

		$profileFields = $this->getProfileFields('array');
		$pfields = array();
		foreach ($request->all() as $fieldkey => $fieldval) {
			if (substr($fieldkey, 0, 9) == '_profile_') {
				$fieldname = substr($fieldkey, 9);
				if (array_key_exists($fieldname, $profileFields)) {
					$pfields[$fieldname] = $request->input($fieldkey);
				}
			}
		}

		$object->profile()->update($pfields);

		Artisan::call('httpcache:clear');

		return true;

	}

	/**
	 * @param string $type
	 * @return mixed
	 */
	private function getProfileFields($type = 'object')
	{

		$profileFields = config('lara-admin.userProfile');
		if ($type == 'array') {
			return $profileFields;
		} elseif ($type == 'object') {
			$profileFields = json_decode(json_encode($profileFields), false);

			return $profileFields;
		} else {
			return $profileFields;
		}

		return $profileFields;
	}

	private function getAdminLaraVersion()
	{

		$laracomposer = file_get_contents(base_path('/laracms/core/composer.json'));
		$laracomposer = json_decode($laracomposer, true);
		$laraVersionStr = $laracomposer['version'];

		$laraversion = $this->makeNewObject();
		$laraversion->version = $laraVersionStr;
		list($laraversion->major, $laraversion->minor, $laraversion->patch) = explode('.', $laraVersionStr);

		return $laraversion;

	}

}

