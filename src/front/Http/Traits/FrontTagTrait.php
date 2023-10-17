<?php

namespace Lara\Front\Http\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Taxonomy;

use Cache;

trait FrontTagTrait
{

	/**
	 * @param string $language
	 * @param object $entity
	 * @param $activetag
	 * @return array|mixed
	 * @throws BindingResolutionException
	 */
	private function getAllTags(string $language, object $entity, $activetag = false)
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
						->descendantsOf($activetag->id)
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

	/**
	 * Get all the tags for a specific entity
	 * Return it as a nested set (tree), or an array
	 *
	 * @param string $language
	 * @param object $entity
	 * @param string $type
	 * @param string $taxonomy
	 * @return object|null
	 */
	private function getTags(string $language, object $entity, string $type = 'tree', string $taxonomy = null, bool $withCount = true)
	{

		$tags = null;

		// get Taxonomy ID
		$taxonomyId = $this->getFrontTaxonomyIdbySlug($taxonomy);

		if ($taxonomyId) {

			if ($entity->hasTags()) {

				$root = Tag::langIs($language)
					->entityIs($entity->getEntityKey())
					->whereNull('parent_id')
					->first();

				if ($root) {

					if ($type == 'array') {

						// kalnoy/nestedset
						$tags = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomyId])
							->defaultOrder()
							->get()
							->toArray();

					} elseif ($type == 'tree') {

						// kalnoy/nestedset
						$tags = Tag::scoped(['entity_key' => $entity->getEntityKey(), 'language' => $language, 'taxonomy_id' => $taxonomyId])
							->defaultOrder()
							->get()
							->toTree();

					} else {

						$tags = null;

					}

				}

			}

		}

		if ($withCount) {
			$tags = $this->getTreeCount($tags, $entity, $type);
		}

		return $tags;

	}

	/**
	 * @param mixed $tags
	 * @param object $entity
	 * @param string $type
	 */
	private function getTreeCount($tags, object $entity, string $type)
	{

		foreach ($tags as $node) {
			$this->getTagCount($node, $entity, $type);
		}

		return $tags;

	}

	/**
	 * @param mixed $node
	 * @param object $entity
	 * @param string $type
	 */
	private function getTagCount($node, object $entity, string $type)
	{

		$modelClass = $entity->getEntityModelClass();
		$collection = new $modelClass;

		if ($type == 'array') {

			if ($entity->hasLanguage()) {
				$collection = $collection->langIs($node['language']);
			}

			if ($entity->hasStatus()) {
				$collection = $collection->isPublished();
			}

			$collection = $collection->whereHas('tags', function ($query) use ($node) {
				$query->where(config('lara-common.database.object.tags') . '.id', $node['id']);
			});

			// add object count to node
			$node['object_count'] = $collection->count();

		} else {

			// tree

			if ($entity->hasLanguage()) {
				$collection = $collection->langIs($node->language);
			}

			if ($entity->hasStatus()) {
				$collection = $collection->isPublished();
			}

			$collection = $collection->whereHas('tags', function ($query) use ($node) {
				$query->where(config('lara-common.database.object.tags') . '.id', $node->id);
			});

			// add object count to node
			$node->object_count = $collection->count();

			foreach ($node->children as $child) {
				$this->getTagCount($child, $entity, $type);
			}

		}

	}

	/**
	 * @param string|null $slug
	 * @return int|null
	 */
	private function getFrontTaxonomyIdbySlug(string $slug = null)
	{

		if ($slug) {
			$taxonomy = Taxonomy::where('slug', $slug)->first();
			if ($taxonomy) {
				return $taxonomy->id;
			} else {
				$defaultTaxonomy = $this->getFrontDefaultTaxonomy();

				return $defaultTaxonomy->id;
			}
		} else {
			$defaultTaxonomy = $this->getFrontDefaultTaxonomy();

			return $defaultTaxonomy->id;
		}

	}

	/**
	 * @return object|null
	 */
	private function getFrontDefaultTaxonomy()
	{

		$taxonomy = Taxonomy::where('is_default', 1)->first();
		if ($taxonomy) {
			return $taxonomy;
		} else {
			return null;
		}

	}

	/**
	 * Get all entity tags that are used by one or more objects
	 *
	 * @param string $language
	 * @param object $entity
	 * @param object $objects
	 * @return object|null
	 */
	private function getTagsFromCollection(string $language, object $entity, object $objects)
	{

		$cache_key = $entity->getEntityKey() . '_tags';

		$tags = Cache::remember($cache_key, 86400, function () use ($language, $entity, $objects) {

			// get used tags from collection
			$activeTags = array();
			foreach ($objects as $object) {
				foreach ($object->tags as $wtag) {
					$activeTags[$wtag->slug] = $wtag->title;
				}
			}

			// get all entity tags in correct order

			// kalnoy/nestedset
			$entityTags = $this->getTags($language, $entity, 'array');

			if (!empty($entityTags)) {

				$tags = array();

				// remove unused tags
				$i = 0;
				foreach ($entityTags as $tag) {
					if (array_key_exists($tag['slug'], $activeTags)) {
						$tags[$i]['title'] = $tag['title'];
						$tags[$i]['slug'] = $tag['slug'];
						$i++;
					}
				}

			} else {

				$tags = null;

			}

			return $tags;

		});

		// convert array to standard object
		$tags = json_decode(json_encode($tags), false);

		return $tags;

	}

	/**
	 * Get all Tag children of a Tag tree (nested set)
	 *
	 * Children are fetched recursively,
	 * so it includes grandchildren great-grandchildren, etc
	 *
	 * @param string $language
	 * @param object $entity
	 * @param string $term
	 * @return object|null
	 */
	private function getTagChildren(string $language, object $entity, string $term)
	{

		// kalnoy/nestedset
		$tag = $this->getTagBySlug($language, $entity, $term);

		$children = null;

		if ($tag) {
			$children = $tag->descendants()
				->defaultOrder()
				->get()
				->toTree();

		}

		return $children;

	}

	/**
	 * @param string $language
	 * @param object $entity
	 * @param string $slug
	 * @return object|null
	 */
	private function getTagBySlug(string $language, object $entity, string $slug)
	{

		if ($slug) {
			$tag = Tag::langIs($language)->entityIs($entity->getEntityKey())->where('slug', $slug)->first();
		} else {
			$tag = null;
		}

		return $tag;

	}

}
