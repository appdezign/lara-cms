<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Language;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Tag;

trait LaraLanguage
{

	/**
	 * @param string $source
	 * @param string $dest
	 * @return null
	 */
	private function copyLanguageContent(string $source, string $dest)
	{

		// check the source language
		$sourcelang = Language::where('code', $source)->first();
		if (!$sourcelang || $sourcelang->publish != 1) {
			flash('select the correct languages')->error();

			return null;
		}

		// check the destination language
		$destlang = Language::where('code', $dest)->first();
		if (!$destlang || $destlang->publish != 1) {
			flash('select the correct languages')->error();

			return null;
		}

		// purge destination tags
		DB::table(config('lara-common.database.object.tags'))->where('language', $dest)->delete();

		// purge menu items
		DB::table(config('lara-common.database.menu.menuitems'))->where('language', $dest)->delete();

		// get all entities
		$entities = Entity::entityGroupIsOneOf(['page', 'block', 'entity'])->get();

		foreach ($entities as $entity) {

			// define Model Class
			$modelClass = $entity->entity_model_class;

			$lara = $this->getEntityVarByModel($modelClass);
			$laraEntity = new $lara;

			// purge destination objects
			$oldDestObjects = $modelClass::langIs($dest)->get();

			foreach ($oldDestObjects as $oldDestObject) {
				$this->deleteEntityObject($laraEntity, $oldDestObject, true);
			}

			/*
			 * Create New Entity Tags
			 */
			$tree = $this->getEntityTags($source, $laraEntity);

			if ($laraEntity->hasTags()) {
				if ($tree) {
					foreach ($tree as $node) {
						$this->getNewLanguageTag($node, $source, $dest, $laraEntity->getEntityKey(), null);
					}
				}
			}

			/*
			 * Create New Entity Objects
			 */
			$objects = $modelClass::langIs($source)->get();

			foreach ($objects as $object) {

				$newObject = $object->replicate();

				$newObject->language = $dest;
				$newObject->language_parent = $object->id;

				// title
				$newObject->title = '[' . strtoupper($dest) . '] ' . $object->title;

				// lead
				if ($laraEntity->hasLead()) {
					if ($laraEntity->hasTinyLead()) {
						$newObject->lead = '<p>[' . strtoupper($dest) . ']</p> ' . $object->lead;
					} else {
						$newObject->lead = '[' . strtoupper($dest) . '] ' . $object->lead;
					}
				}

				// body
				if ($laraEntity->hasBody()) {
					if ($laraEntity->hasTinyBody()) {
						$newObject->body = '<p>[' . strtoupper($dest) . ']</p> ' . $object->body;
					} else {
						$newObject->body = '[' . strtoupper($dest) . '] ' . $object->body;
					}
				}

				// Pages
				if ($laraEntity->getEntityKey() == 'page') {
					$newObject->menuroute = null;
				}

				if ($laraEntity->getEntityKey() == 'page' && $object->cgroup == 'module') {
					$parts = explode('-', $object->slug);
					if (sizeof($parts) == 4) {
						list($ent, $view, $mpage, $lang) = explode('-', $object->slug);
					} elseif (sizeof($parts) == 3) {
						// fix legacy module page slug
						list($ent, $view, $mpage) = explode('-', $object->slug);
						$object->slug = $ent . '-' . $view . '-' . $mpage . '-' . $source;
						$object->save();
					} else {
						// incorrect slug, try to fix
						$ent = $parts[0];
						$view = 'index';
						$mpage = 'module';
					}
					$newObject->slug = $ent . '-' . $view . '-' . $mpage . '-' . $dest;
				} else {
					$newObject->slug = $dest . '-' . $object->slug;
				}

				$newObject->save();

				/*
				 * Add object tag relations
				 */
				$newTags = array();
				foreach ($object->tags as $objectTag) {
					$destTag = $this->getLanguageSibling($objectTag, $dest);
					if($destTag) {
						$newTags[] = $destTag->id;
					}
				}
				$newObject->tags()->sync($newTags);

				/*
				 * Add media relations
				 */
				foreach ($object->media as $image) {
					$newMedia = $image->replicate();
					$newMedia->entity_id = $newObject->id;
					$newfilename = $this->copyLanguageMediaFile($laraEntity, $newMedia->filename, $dest, 'image');
					$newMedia->filename = $newfilename;
					$newMedia->save();
				}

				/*
				 * Add file relations
				 */
				foreach ($object->files as $file) {
					$newFile = $file->replicate();
					$newFile->entity_id = $newObject->id;
					$newfilename = $this->copyLanguageMediaFile($laraEntity, $newFile->filename, $dest, 'file');
					$newFile->filename = $newfilename;
					$newFile->save();
				}

				/*
				 * Add video relations
				 */
				foreach ($object->videos as $video) {
					$newVideo = $video->replicate();
					$newVideo->entity_id = $newObject->id;
					$newVideo->save();
				}

				/*
				 * Add layout relations
				 */
				foreach ($object->layout as $layout) {
					$newLayout = $layout->replicate();
					$newLayout->entity_id = $newObject->id;
					$newLayout->save();
				}

			}

		}

		/*
		 * Create New Menu
		 */

		$menus = Menu::get();
		foreach ($menus as $menu) {

			// get source menu tree
			$root = Menuitem::langIs($source)
				->menuIs($menu->id)
				->whereNull('parent_id')
				->first();

			// kalnoy/nestedset
			$tree = Menuitem::scoped(['menu_id' => $menu->id, 'language' => $source])
				->defaultOrder()
				->get()
				->toTree();

			// create new menu tree
			foreach ($tree as $node) {
				$this->getNewLanguageMenuItem($node, $source, $dest, null);
			}

			// rebuild new menu
			$newroot = Menuitem::langIs($dest)
				->menuIs($menu->id)
				->whereNull('parent_id')
				->first();

			$this->rebuildMenuRoutes($newroot->id);

		}

		// syn new pages with new menu
		$this->syncPagesWithMenu($dest);

		return null;

	}

	/**
	 * @param object $node
	 * @param string $source
	 * @param string $dest
	 * @param string $entity_key
	 * @param object|null $parent
	 * @return void
	 */
	private function getNewLanguageTag(object $node, string $source, string $dest, string $entity_key, $parent = null)
	{

		$newTag = $node->replicate();

		$newTag->language = $dest;
		$newTag->language_parent = $node->id;

		$newTag->title = '[' . strtoupper($dest) . '] ' . $node->title;
		$newTag->slug = $dest . '-' . $node->slug;

		if ($parent) {
			$newTag->parent_id = $parent->id;
		}

		$newTag->save();

		$this->rebuildTagRoutes($newTag->id);

		// pass new tag as parent
		$newParent = $newTag;

		if (!$node->isLeaf()) {
			foreach ($node->children as $child) {
				$this->getNewLanguageTag($child, $source, $dest, $entity_key, $newParent);
			}
		}

	}

	/**
	 * @param object $entity
	 * @param string $filename
	 * @param string $dest
	 * @param string $type
	 * @return string
	 */
	private function copyLanguageMediaFile(object $entity, string $filename, string $dest, string $type)
	{
		$epath = $entity->getEntityKey();

		if ($type == 'image') {

			$disk = $entity->getDiskForImages();

		} elseif ($type == 'video') {

			$disk = $entity->getDiskForVideos();

		} elseif ($type == 'file') {

			$disk = $entity->getDiskForFiles();

		} else {
			return null;
		}

		// add language code to filename
		$parts = pathinfo($filename);
		$newfilename = $parts['filename'] . '-' . $dest . '.' . $parts['extension'];

		// copy file
		Storage::disk($disk)->copy($epath . '/' . $filename, $epath . '/' . $newfilename);

		return $newfilename;

	}

	/**
	 * @param object $object
	 * @param string $dest
	 * @return mixed
	 */
	private function getLanguageSibling(object $object, string $dest)
	{

		$modelClass = get_class($object);

		if ($object->languageParent) {
			$parent = $object->languageParent;
		} else {
			$parent = $object;
		}

		$sibling = $modelClass::langIs($dest)->where('language_parent', $parent->id)->first();

		return $sibling;

	}

	/**
	 * @param object $node
	 * @param string $source
	 * @param string $dest
	 * @param object|null $parent
	 * @return bool
	 */
	private function getNewLanguageMenuItem(object $node, string $source, string $dest, $parent = null)
	{

		$newMenuItem = $node->replicate();

		$newMenuItem->language = $dest;

		$newMenuItem->title = '[' . strtoupper($dest) . '] ' . $node->title;
		$newMenuItem->slug = $dest . '-' . $node->slug;

		if ($parent) {
			$newMenuItem->parent_id = $parent->id;
		}

		// find destination pages and replace the source pages
		if ($newMenuItem->object_id) {
			$entity = Entity::find($newMenuItem->entity_id);
			$relatedModelClass = $entity->entity_model_class;
			$relatedObject = $relatedModelClass::find($newMenuItem->object_id);
			$sibling = $this->getLanguageSibling($relatedObject, $dest);

			// new object ID
			$newMenuItem->object_id = $sibling->id;

			// new Routename
			if ($newMenuItem->parent_id) {
				// standard page
				$newMenuItem->routename = 'entity.page.show.' . $sibling->id;
			} else {
				// homepage
			}
		}

		// menu tag
		if ($newMenuItem->tag_id) {
			$sourceTag = Tag::find($newMenuItem->tag_id);
			$tagSibling = $this->getLanguageSibling($sourceTag, $dest);

			$newMenuItem->tag_id = $tagSibling->id;
		}

		$newMenuItem->save();

		// pass new tag as parent
		$newParent = $newMenuItem;

		if (!$node->isLeaf()) {
			foreach ($node->children as $child) {
				$this->getNewLanguageMenuItem($child, $source, $dest, $newParent);
			}
		}

		return true;
	}

}