<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Taxonomy;

trait AdminTagTrait
{

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
			$tags = $this->makeNewTreeObject();
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
	 * Create a new empty Laravel object
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	private function makeNewTreeObject()
	{

		$app = app();
		$newobject = $app->make('stdClass');

		return $newobject;

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



}

