<?php

namespace Lara\Front\Http\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Taxonomy;

trait FrontListTrait
{

	/**
	 * Build the collection for the index method
	 *
	 * Options:
	 * - sort by tag
	 * - filter by tags
	 * - eager loading of user, media, files
	 * - paginate or not
	 * - sorting
	 *
	 * @param string $language
	 * @param object $entity
	 * @param Request $request
	 * @param object|null $menutaxonomy
	 * @return object|null
	 */
	private function getFrontObjects(string $language, object $entity, Request $request, $menutaxonomy = null, $params = null, $override = null)
	{

		$method = $entity->getMethod();

		$view = $entity->getViews()->where('method', $method)->first();

		$isgrid = substr($view->type, 0, 4) == 'grid';

		if ($view->showtags == '_sortbytaxonomy') {

			// get active tag, if any
			if ($params->filterbytaxonomy) {
				$taxonomy = Taxonomy::where('slug', $params->taxonomy)->first();
				$activetag = Tag::where('taxonomy_id', $taxonomy->id)->where('slug', $params->filterbytaxonomy)->first();
			} else {
				if ($menutaxonomy) {
					$activetag = $menutaxonomy;
				} else {
					$activetag = null;
				}
			}

			// show objects ordered by tags (disable pagination)
			// kalnoy/nestedset
			$tags = $this->getAllEntityTags($language, $entity, $activetag);

			foreach ($tags as $taxonomy => $terms) {
				if (!empty($terms)) {
					foreach ($terms as $node) {
						$this->getTagObjects($node, $entity, $language, $isgrid, $menutaxonomy);
					}
				}
			}

			return $tags;

		} else {

			// start collection
			$modelClass = $entity->getEntityModelClass();
			$collection = new $modelClass;

			if ($entity->hasLanguage()) {
				$collection = $collection->langIs($language);
			}

			if ($entity->hasStatus()) {
				if($this->getTestMode($request) == 'showall') {
					$collection = $collection->whereIn('publish', [0,1]);
				} else {
					$collection = $collection->isPublished();
				}
			}

			if ($entity->hasHideinlist()) {
				$collection = $collection->where('publish_hide', 0);
			}

			if ($entity->hasExpiration()) {
				$collection = $collection->isNotExpired();
			}

			if ($isgrid && $entity->getEntityKey() != 'video' && !$entity->hasVideos() && !$entity->hasVideoFiles()) {
				$collection = $collection->has('media');
			}

			if (method_exists($entity->getEntityModelClass(), 'scopeFront')) {
				if($this->getTestMode($request) != 'showall') {
					$collection = $collection->front();
				}
			}

			if ($view->showtags == 'filterbytaxonomy') {

				if ($menutaxonomy) {

					// if the menu-item has a tag, then we overrule the other filters
					$collection = $collection->whereHas('tags', function ($query) use ($menutaxonomy) {
						$query->where(config('lara-common.database.object.tags') . '.id', $menutaxonomy->id);
					});

				} else {

					if (!empty($entity->getActiveTags())) {

						$activeTags = $entity->getActiveTags();
						$term = end($activeTags);
						$excludeEntityTags = config('lara-eve.use_tags_for_sorting.' . $entity->getEntityKey());
						$excludeTags = (!empty($excludeEntityTags)) ? $excludeEntityTags : [];
						if (!in_array($term, $excludeTags)) {
							$collection = $collection->whereHas('tags', function ($query) use ($term) {
								$query->where(config('lara-common.database.object.tags') . '.slug', $term);
							});
						}

					}

					if (!empty($params->xtratags)) {
						foreach ($params->xtratags as $xtag) {
							$collection = $collection->whereHas('tags', function ($query) use ($xtag) {
								$query->where(config('lara-common.database.object.tags') . '.slug', $xtag['slug']);
							});
						}
					}
				}

			} else {

				if ($menutaxonomy) {
					$collection = $collection->whereHas('tags', function ($query) use ($menutaxonomy) {
						$query->where(config('lara-common.database.object.tags') . '.id', $menutaxonomy->id);
					});
				}

			}

			// eager loading: user
			if ($entity->getShowAuthor()) {
				$collection = $collection->with('user');
			}

			// eager loading: media
			if ($entity->hasImages()) {
				$collection = $collection->with('media');
			}

			// eager loading: files
			if ($entity->hasFiles()) {
				$collection = $collection->with('files');
			}

			// OrderBy
			if (isset($override) && isset($override->sortfield) && isset($override->sortorder)) {

				$collection = $collection->orderBy($override->sortfield, $override->sortorder);

				if (isset($override->sortfield2nd) && isset($override->sortorder2nd)) {
					$collection = $collection->orderBy($override->sortfield2nd, $override->sortorder2nd);
				}

			} else {

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

			}

			if (isset($override) && isset($override->paginate)) {

				// custom pagination
				if ($override->paginate > 0) {
					$objects = $collection->paginate($override->paginate);
				} else {
					if (isset($override) && isset($override->limit)) {
						// custom limit
						$objects = $collection->limit($override->limit)->get();
					} else {
						$objects = $collection->get();
					}
				}

			} else {

				// entity view pagination
				if ($view->paginate > 0) {
					$objects = $collection->paginate($view->paginate);
				} else {
					if (isset($override) && isset($override->limit)) {
						$objects = $collection->limit($override->limit)->get();
					} else {
						$objects = $collection->get();
					}
				}
			}

			// prepare route variables (master > detail)
			foreach ($objects as $obj) {
				if ($menutaxonomy) {
					$obj->routeVars = ['slug' => $obj->slug, $menutaxonomy->taxonomy->slug => $menutaxonomy->slug];
				} else {
					$obj->routeVars = ['slug' => $obj->slug];
				}
			}

			return $objects;

		}

	}

	/**
	 * Build the collection for a JSON feed
	 *
	 * @param string $language
	 * @param object $entity
	 * @param Request $request
	 * @param int|null $limit
	 * @return object
	 */
	private function getFeedObjects(string $language, object $entity, Request $request, $limit = null)
	{

		// start collection
		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($entity->hasLanguage()) {
			$collection = $collection->langIs($language);
		}

		if ($entity->hasStatus()) {
			$collection = $collection->isPublished();
		}

		if (method_exists($entity->getEntityModelClass(), 'scopeFront')) {
			$collection = $collection->front();
		}

		if ($entity->getActiveTags()) {

			$activeTags = $entity->getActiveTags();
			$term = end($activeTags);

			$collection = $collection->whereHas('tags', function ($query) use ($term) {
				$query->where(config('lara-common.database.object.tags') . '.slug', $term);
			});

		}

		// eager loading: user
		if ($entity->getShowAuthor()) {
			$collection = $collection->with('user');
		}

		// OrderBy
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

		if ($limit && is_numeric($limit)) {
			$collection = $collection->limit($limit);
		}

		return $collection->get();

	}

	/**
	 * Get all objects for one specific tag
	 * The objects are added to the tag tree (nested set)
	 *
	 * @param object $node
	 * @param object $entity
	 * @param string $language
	 * @param bool $isgrid
	 * @return int
	 */
	private function getTagObjects(object $node, object $entity, string $language, bool $isgrid, $menutaxonomy = null)
	{

		$totalcount = 0;

		// get objects for this tag (skip root tag)
		if ($node->depth > 0) {

			$termId = $node->id;

			// start collection
			$collection = $entity->getEntityModelClass()::langIs($language)
				->isPublished()
				->when(($isgrid && $entity->getEntityKey() != 'video'), function ($query) {
					return $query->has('media');
				});

			$collection = $collection->whereHas('tags', function ($query) use ($termId) {
				$query->where(config('lara-common.database.object.tags') . '.id', $termId);
			});

			if ($entity->hasHideinlist()) {
				$collection = $collection->where('publish_hide', 0);
			}

			if (method_exists($entity->getEntityModelClass(), 'scopeFront')) {
				$collection = $collection->front();
			}

			// eager loading: user
			if ($entity->getShowAuthor()) {
				$collection = $collection->with('user');
			}

			// eager loading: media
			if ($entity->hasImages()) {
				$collection = $collection->with('media');
			}

			// eager loading: files
			if ($entity->hasFiles() == 1) {
				$collection = $collection->with('files');
			}

			// OrderBy
			if ($entity->getSortField()) {
				$collection = $collection->orderBy($entity->getSortField(), $entity->getSortOrder());
			}
			if ($entity->getSortField2nd()) {
				$collection = $collection->orderBy($entity->getSortField2nd(), $entity->getSortOrder2nd());
			}

			// get the collection
			$objects = $collection->get();

			// add object collection to node
			$node->objects = $objects;

			// add objectcount to node
			$node->objectcount = $node->objects->count();

			// prepare route variables (master > detail)
			foreach ($node->objects as $obj) {
				if ($menutaxonomy) {
					$obj->routeVars = ['slug' => $obj->slug, $menutaxonomy->taxonomy->slug => $menutaxonomy->slug];
				} else {
					$obj->routeVars = ['slug' => $obj->slug];
				}
			}

			// add objectcount from this tag to totalcount
			$totalcount = $node->objectcount;

		}

		if (!$node->isLeaf()) {
			foreach ($node->children as $child) {
				$childobjectcount = $this->getTagObjects($child, $entity, $language, $isgrid, $menutaxonomy);

				// add child count to total count
				$totalcount = $totalcount + $childobjectcount;

			}
		}

		// add totalcount to node
		$node->childobjectcount = $totalcount;

		return $totalcount;
	}

	/**
	 * Get the next object in a master detail structure (index/show)
	 *
	 * @param string $language
	 * @param object $entity
	 * @param object $object
	 * @param object $params
	 * @param object|null $menutaxonomy
	 * @return mixed
	 */
	private function getNextObject(string $language, object $entity, object $object, object $params, $menutaxonomy = null, $override = null)
	{

		$view = $entity->getViews()->where('method', 'index')->first();
		$isgrid = substr($view->type, 0, 4) == 'grid';

		if (isset($override) && isset($override->sortfield) && isset($override->sortorder)) {
			$sortField = $override->sortfield;
			$sortOrder = $override->sortorder;
			$objectSortValue = $object->$sortField;
		} else {
			$sortField = $entity->getSortField();
			$sortOrder = $entity->getSortOrder();
			$objectSortValue = $object->$sortField;
		}

		if (empty($objectSortValue)) {
			return null;
		}

		if (isset($override) && isset($override->sortfield2nd) && isset($override->sortordernd)) {
			$sortField2nd = $override->sortfield2nd;
			$sortOrder2nd = $override->sortordernd;
			$objectSortValue2nd = $object->$sortField2nd;
		} else {
			if ($entity->getSortField2nd()) {
				$sortField2nd = $entity->getSortField2nd();
				$sortOrder2nd = $entity->getSortOrder2nd();
				$objectSortValue2nd = $object->$sortField2nd;
			} else {
				$sortField2nd = null;
				$sortOrder2nd = null;
				$objectSortValue2nd = null;
			}

		}

		if ($sortField2nd) {
			// first
			if ($sortOrder == 'asc') {
				$operator = '>=';
			} else {
				$operator = '<=';
			}
			// second
			if ($sortOrder2nd == 'asc') {
				$operator2nd = '>';
			} else {
				$operator2nd = '<';
			}
		} else {
			if ($sortOrder == 'asc') {
				$operator = '>';
			} else {
				$operator = '<';
			}
			$operator2nd = null;
		}

		// start collection
		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($entity->hasLanguage()) {
			$collection = $collection->langIs($language);
		}

		if ($entity->hasStatus()) {
			$collection = $collection->isPublished();
		}

		if ($isgrid && !$entity->hasVideos() && !$entity->hasVideoFiles()) {
			$collection = $collection->has('media');
		}

		if ($menutaxonomy) {
			$collection = $collection->whereHas('tags', function ($query) use ($menutaxonomy) {
				$query->where(config('lara-common.database.object.tags') . '.id', $menutaxonomy->id);
			});
		} else {
			if (!empty($entity->getActiveTags())) {
				$activeTags = $entity->getActiveTags();
				$term = end($activeTags);
				$currentTag = Tag::where('entity_key', $entity->getEntityKey())->where('slug', $term)->first();
				if ($currentTag && $currentTag->publish == 1) {
					$collection = $collection->whereHas('tags', function ($query) use ($term) {
						$query->where(config('lara-common.database.object.tags') . '.slug', $term);
					});
				}
			}
			if (!empty($params->xtratags)) {
				foreach ($params->xtratags as $xtag) {
					$collection = $collection->whereHas('tags', function ($query) use ($xtag) {
						$query->where(config('lara-common.database.object.tags') . '.slug', $xtag['slug']);
					});
				}
			}
		}

		// split collection
		$collection = $collection->where($sortField, $operator, $objectSortValue);
		if ($sortField2nd) {
			$collection = $collection->where($sortField2nd, $operator2nd, $objectSortValue2nd);
		}

		// order by first
		$collection = $collection->orderBy($sortField, $sortOrder);

		// order by second
		if ($sortField2nd) {
			$collection = $collection->orderBy($sortField2nd, $sortOrder2nd);
		}

		return $collection->first();

	}

	/**
	 * Get the previous object in a master detail structure (index/show)
	 *
	 * @param string $language
	 * @param object $entity
	 * @param object $object
	 * @param object $params
	 * @param object|null $menutaxonomy
	 * @return mixed
	 */
	private function getPrevObject(string $language, object $entity, object $object, object $params, $menutaxonomy = null, $override = null)
	{

		$view = $entity->getViews()->where('method', 'index')->first();
		$isgrid = substr($view->type, 0, 4) == 'grid';

		if (isset($override) && isset($override->sortfield) && isset($override->sortorder)) {
			$sortField = $override->sortfield;
			$sortOrder = $override->sortorder;
			$objectSortValue = $object->$sortField;
		} else {
			$sortField = $entity->getSortField();
			$sortOrder = $entity->getSortOrder();
			$objectSortValue = $object->$sortField;
		}

		if (empty($objectSortValue)) {
			return null;
		}

		if (isset($override) && isset($override->sortfield2nd) && isset($override->sortorder2nd)) {
			$sortField2nd = $override->sortfield2nd;
			$sortOrder2nd = $override->sortorder2nd;
			$objectSortValue2nd = $object->$sortField2nd;
		} else {
			if ($entity->getSortField2nd()) {
				$sortField2nd = $entity->getSortField2nd();
				$sortOrder2nd = $entity->getSortOrder2nd();
				$objectSortValue2nd = $object->$sortField2nd;
			} else {
				$sortField2nd = null;
				$sortOrder2nd = null;
				$objectSortValue2nd = null;
			}
		}

		if ($sortField2nd) {
			// first
			if ($sortOrder == 'asc') {
				$reverseSortOrder = 'desc';
				$reverseOperator = '<=';
			} else {
				$reverseSortOrder = 'asc';
				$reverseOperator = '>=';
			}
			// second
			if ($sortOrder2nd == 'asc') {
				$reverseSortOrder2nd = 'desc';
				$reverseOperator2nd = '<';
			} else {
				$reverseSortOrder2nd = 'asc';
				$reverseOperator2nd = '>';
			}
		} else {
			if ($sortOrder == 'asc') {
				$reverseSortOrder = 'desc';
				$reverseOperator = '<';
			} else {
				$reverseSortOrder = 'asc';
				$reverseOperator = '>';
			}
			$reverseSortOrder2nd = null;
			$reverseOperator2nd = null;
		}

		// start collection
		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($entity->hasLanguage()) {
			$collection = $collection->langIs($language);
		}

		if ($entity->hasStatus()) {
			$collection = $collection->isPublished();
		}

		if ($isgrid && !$entity->hasVideos() && !$entity->hasVideoFiles()) {
			$collection = $collection->has('media');
		}

		if ($menutaxonomy) {
			$collection = $collection->whereHas('tags', function ($query) use ($menutaxonomy) {
				$query->where(config('lara-common.database.object.tags') . '.id', $menutaxonomy->id);
			});
		} else {
			if (!empty($entity->getActiveTags())) {
				$activeTags = $entity->getActiveTags();
				$term = end($activeTags);
				$currentTag = Tag::where('entity_key', $entity->getEntityKey())->where('slug', $term)->first();
				if ($currentTag && $currentTag->publish == 1) {
					$collection = $collection->whereHas('tags', function ($query) use ($term) {
						$query->where(config('lara-common.database.object.tags') . '.slug', $term);
					});
				}

			}
			if (!empty($params->xtratags)) {
				foreach ($params->xtratags as $xtag) {
					$collection = $collection->whereHas('tags', function ($query) use ($xtag) {
						$query->where(config('lara-common.database.object.tags') . '.slug', $xtag['slug']);
					});
				}
			}
		}

		// split collection
		$collection = $collection->where($sortField, $reverseOperator, $objectSortValue);
		if ($sortField2nd) {
			$collection = $collection->where($sortField2nd, $reverseOperator2nd, $objectSortValue2nd);
		}

		// order by first
		$collection = $collection->orderBy($sortField, $reverseSortOrder);

		// order by second
		if ($sortField2nd) {
			$collection = $collection->orderBy($sortField2nd, $reverseSortOrder2nd);
		}

		return $collection->first();

	}

	/**
	 * Cleanup Search String
	 *
	 * @param string $str
	 * @return array|false|string[]
	 */
	private function cleanupFrontSearchString(string $str)
	{

		$keywords = preg_split('/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY);

		return $keywords;

	}

	/**
	 * Get all parameters for the index method
	 *
	 * We need to pass several parameters to the view:
	 *  - viewtype
	 *  - grid
	 *  - paginate
	 *  - (filter by) tag
	 *
	 * @param object $entity
	 * @param Request $request
	 * @return RedirectResponse|object
	 */
	private function getFrontParams(object $entity, Request $request)
	{

		$params = (object)array(
			'viewtype'         => '',
			'isgrid'           => false,
			'listtype'         => '',
			'vtype'            => '',
			'showtags'         => '',
			'tagsview'         => '',
			'gridcol'          => 0,
			'paginate'         => false,
			'infinite'         => false,
			'prevnext'         => false,
			'filter'           => false,
			'filterbytaxonomy' => '',
			'taxonomy'         => '',
			'isdefaultaxonomy' => false,
			'xtratags'         => [],
		);

		if ($entity->entity_key == 'search') {

			$params = (object)array(
				'viewtype'         => '',
				'isgrid'           => false,
				'listtype'         => 'list',
				'vtype'            => 'list',
				'showtags'         => 'none',
				'tagsview'         => 'default',
				'gridcols'         => 0,
				'gridcol'          => 0,
				'paginate'         => false,
				'infinite'         => false,
				'prevnext'         => false,
				'filter'           => false,
				'filterbytaxonomy' => '',
				'taxonomy'         => '',
				'isdefaultaxonomy' => false,
				'xtratags'         => [],
			);

			return $params;

		}

		$method = $entity->getMethod();

		if (empty($method)) {
			// default method
			if ($entity->entity_key == 'page') {
				$method = 'show';
			} else {
				$method = 'index';
			}
		}

		$view = $entity->getViews()->where('method', $method)->first();
		if (empty($view)) {
			// create default view

			$app = app();
			$view = $app->make('stdClass');

			$view->method = 'show';
			$view->filename = 'show';
			$view->type = '_single';
			$view->showtags = 'none';
			$view->paginate = 0;
			$view->infinite = 0;
			$view->prevnext = 0;
		}

		$params->viewtype = $view->type;
		if (substr($params->viewtype, 0, 4) == 'grid') {
			$params->isgrid = true;
			$params->listtype = 'grid';
		} else {
			$params->isgrid = false;
			$params->listtype = 'list';
		}

		$params->showtags = $view->showtags;

		if ($view->showtags == '_sortbytaxonomy') {
			$params->tagsview = 'sort';
		} elseif ($view->showtags == 'filterbytaxonomy') {
			$params->tagsview = 'filter';
		} else {
			$params->tagsview = 'default';
		}

		if ($view->type == '_single') {

			$params->vtype = 'single';
			$params->gridcols = 0;
			$params->gridcol = 0;
			$params->prevnext = (bool)$view->prevnext;

		} else {

			$isgrid = substr($view->type, 0, 4) == 'grid';

			if ($isgrid) {
				$params->vtype = 'grid';

				$params->gridcols = substr($view->type, -1);
				$params->gridcol = (12 / $params->gridcols);
			} else {
				$params->vtype = 'list';
				$params->gridcols = 0;
				$params->gridcol = 0;
			}

		}

		if ($view->infinite == 1) {
			$params->infinite = true;
		} else {
			$params->infinite = false;
		}

		if ($view->showtags == 'none') {

			if ($view->paginate > 0) {
				$params->paginate = true;
			} else {
				$params->paginate = false;
			}

		} elseif ($view->showtags == '_sortbytaxonomy') {

			$params->paginate = false;

		} elseif ($view->showtags == 'filterbytaxonomy') {

			if ($view->paginate > 0) {
				$params->paginate = true;
			} else {
				$params->paginate = false;
			}

		}

		if ($view->showtags == 'filterbytaxonomy' || $view->showtags == '_sortbytaxonomy' || $view->type == '_single') {

			// primary tags (URL)
			if (!empty($entity->getActiveTags())) {
				$activeTags = $entity->getActiveTags();
				$term = end($activeTags);

				$tag = Tag::where('slug', $term)->first();
				$taxonomy = $tag->taxonomy;
				$params->taxonomy = $taxonomy->slug;
				if ($taxonomy) {
					if ($taxonomy->is_default == 1) {
						$params->isdefaultaxonomy = true;
					} else {
						$params->isdefaultaxonomy = false;
					}
				}

			}

			if (!empty($term)) {
				$params->filter = true;
				$params->filterbytaxonomy = $term;

			}

			// secondary tags (GET)
			$params->xtratags = array();

			$taxonomies = Taxonomy::where('is_default', 0)->get();
			foreach ($taxonomies as $taxonomy) {
				$taxonomySlug = $taxonomy->slug;
				$tagSlug = $this->getFrontRequestParam($request, $taxonomySlug, null, $entity->entity_key, true);

				if ($tagSlug) {

					if ($view->showtags == 'filterbytaxonomy') {
						// redirect if tag is not in GET variables
						if (!isset($_GET[$taxonomySlug])) {
							return redirect()->route(Route::currentRouteName(), [$taxonomySlug => $tagSlug])->send();
						}
					}

					// get Tag object
					$tag = Tag::where('slug', $tagSlug)->first();
					if ($tag) {
						$params->xtratags[$taxonomySlug] = [
							'slug'  => $tag->slug,
							'title' => $tag->title,
						];
					}

				} else {

					if ($view->showtags == 'filterbytaxonomy') {
						// redirect if empty tag is in GET variables
						if (isset($_GET[$taxonomySlug]) && $_GET[$taxonomySlug] == '') {
							return redirect()->route(Route::currentRouteName())->send();
						}
					}

				}

			}
		}

		return $params;

	}

	/**
	 * When a menu item, that has an entity index method,
	 * can be filtered immediately by tag
	 *
	 * @param string $language
	 * @param object $entity
	 * @param Request $request
	 * @return object|null
	 */
	private function getMenuTag(string $language, object $entity, Request $request)
	{

		if ($entity->getEgroup() == 'entity') {
			$activeMenuItem = $this->getActiveMnuItem($language);
			if ($activeMenuItem) {
				$tag_id = $activeMenuItem->tag_id;
				if ($tag_id) {
					$tag = Tag::find($tag_id);
				} else {
					$tag = null;
				}
			} else {
				$tag = null;
			}
		} else {
			$tag = null;
		}

		return $tag;

	}

	/**
	 * Get the active Menu Item object, based on the current url
	 *
	 * @param string $language
	 * @return mixed
	 */
	private function getActiveMnuItem(string $language)
	{

		$base_url = URL::to('/');
		$slug = substr(URL::current(), strlen($base_url));

		$route = substr($slug, 4);

		$routename = $this->getTheRouteFromUrl($slug);

		if ($routename == 'special.home.show') {

			// HOME PAGE
			$activeMenuItem = Menuitem::langIs($language)->where('routename', $routename)->first();

		} else {

			$routeparts = explode('.', $routename);

			if (end($routeparts) == 'show') {

				// detail page, get parent
				$routepos = strrpos($route, '/');
				$route = substr($route, 0, $routepos);

				$rnamepos = strrpos($routename, '.');
				$routename = substr($routename, 0, $rnamepos);

			} else {

				// remove tags from routename
				$prefix = $routeparts[0];
				$entityKey = $routeparts[1];
				$method = end($routeparts);
				$routename = $prefix . '.' . $entityKey . '.' . $method;

			}

			// find by url
			$activeMenuItem = Menuitem::langIs($language)->where('route', $route)->first();

			if (!$activeMenuItem) {
				// find by routename
				$activeMenuItem = Menuitem::langIs($language)->where('routename', $routename)->first();
			}

		}

		return $activeMenuItem;

	}

	/**
	 * Get the Laravel route name from the given url
	 *
	 * @param string $url
	 * @return mixed
	 */
	private function getTheRouteFromUrl(string $url)
	{

		$route = app('router')->getRoutes()->match(app('request')->create($url))->getName();

		return $route;

	}

	private function getSingleMenuTag(string $language, object $entity, Request $request)
	{

		if ($entity->getEgroup() == 'entity') {

			$defaultTaxonomy = $this->getTheDefaultTaxonomy();
			$tax = $defaultTaxonomy->slug;

			if ($request->has($tax)) {

				$tagSlug = $request->get($tax);
				$tag = Tag::where('taxonomy_id', $defaultTaxonomy->id)->where('slug', $tagSlug)->first();
				if ($tag) {
					return $tag;
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

	private function getTheDefaultTaxonomy()
	{

		$taxonomy = Taxonomy::where('is_default', 1)->first();
		if ($taxonomy) {
			return $taxonomy;
		} else {
			return null;
		}

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
	 * @param string|null $entity
	 * @param bool $reset
	 * @return mixed
	 */
	private function getFrontRequestParam(Request $request, string $param, $default = null, $entity = null, $reset = false)
	{

		if (!empty($entity)) {

			// store request param for specific entity only

			if ($request->has($param) || $reset == true) {

				$value = $request->get($param);

				if (session()->has($entity)) {

					$entitySession = session($entity);
					$entitySession[$param] = $value;
					session([$entity => $entitySession]);

				} else {

					session([
						$entity => [
							$param => $value,
						],
					]);

				}

			} else {

				if (session()->has($entity)) {
					$entitySession = session($entity);
					if (array_key_exists($param, $entitySession)) {
						if (!empty($entitySession[$param])) {
							$value = $entitySession[$param];
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

		} else {

			// store request param globally

			if ($request->has($param) || $reset == true) {
				$value = $request->get($param);
				session([$param => $value]);
			} else {
				if (session()->has($param)) {
					if (!empty(session($param))) {
						$value = session($param);
					} else {
						$value = $default;
					}
				} else {
					$value = $default;
				}
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
	 * @param string $language
	 * @param object $entity
	 * @param $activetag
	 * @return array|mixed
	 * @throws BindingResolutionException
	 */
	private function getAllEntityTags(string $language, object $entity, $activetag = false)
	{

		$app = app();
		$tags = $app->make('stdClass');

		if ($activetag) {
			$taxonomies = Taxonomy::where('id', $activetag->taxonomy_id)->get();
		} else {
			$taxonomies = Taxonomy::get();
		}

		foreach ($taxonomies as $taxonomy) {

			$key = $taxonomy->slug;

			if ($entity->hasTags()) {

				$root = Tag::langIs($language)
					->entityIs($entity->getEntityKey())
					->taxonomyIs($taxonomy->id)
					->whereNull('parent_id')
					->first();

				if (empty($root)) {

					Tag::create([
						'language'    => $language,
						'entity_key'  => $entity->getEntityKey(),
						'taxonomy_id' => $taxonomy->id,
						'title'       => 'root',
						'slug'        => null,
						'body'        => '',
						'lead'        => '',
					]);
				}

				if ($activetag) {

					// get subtree
					$tags->$key = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomy->id])
						->defaultOrder()
						->descendantsAndSelf($activetag->id)
						->toTree();

				} else {
					// get full tree
					$tags->$key = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomy->id])
						->defaultOrder()
						->get()
						->toTree();
				}
			}
		}

		return $tags;

	}

	private function getTestMode($request) {

		if (Auth::check()) {
			if($request->has('testkey') && $request->input('testkey') == config('lara.testkey')) {
				if($request->has('testmode')) {
					return $request->input('testmode');
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
}
