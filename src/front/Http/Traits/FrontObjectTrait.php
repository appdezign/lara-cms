<?php

namespace Lara\Front\Http\Traits;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Lara\Common\Models\Entity;
use Lara\Common\Models\Menu;
use Lara\Common\Models\Menuitem;
use Lara\Common\Models\Page;
use Lara\Common\Models\Related;
use Lara\Common\Models\Setting;
use Lara\Common\Models\User;

trait FrontObjectTrait
{

	/**
	 * Get related objects from other entities
	 *
	 * @param string $entity_key
	 * @param int $id
	 * @return mixed
	 */
	private function getFrontRelated(string $entity_key, int $id)
	{

		$relatedItems = Related::where('entity_key', $entity_key)
			->where('object_id', $id)
			->get();

		$related = array();

		foreach ($relatedItems as $rel) {

			$item = app()->make('stdClass');

			$item->entity_key = $rel->related_entity_key;
			$item->object_id = $rel->related_object_id;

			// get related object
			$object = $rel->related_model_class::findOrFail($rel->related_object_id);
			$item->title = $object->title;
			$item->slug = $object->slug;

			// check if related Item is a Doc entity
			if ($rel->related_entity_key == 'doc' && !config('lara-front.skip_direct_download_related_docs')) {
				$doc = \Eve\Models\Doc::find($rel->related_object_id);
				if ($doc && $doc->hasFiles()) {
					$item->target = '_blank';
					$laraDocClass = $this->getFrontEntByKey('doc');
					$laraDocEntity = new $laraDocClass;
					$item->url = $laraDocEntity->getUrlForFiles() . $doc->files[0]->filename;
				} else {
					$item->url = $this->getFrontSeoUrl($rel->related_entity_key, 'show', 'index', $object);
					$item->target = '_self';
				}
			} elseif ($rel->related_entity_key == 'menuitem') {
				$item->url = route($object->routename);
				$item->target = '_self';
			} else {
				$item->url = $this->getFrontSeoUrl($rel->related_entity_key, 'show', 'index', $object);
				$item->target = '_self';
			}

			$related[] = $item;

		}

		return $related;

	}

	/**
	 * Get the URL for a specific entity method
	 * For single detail pages (show) we also need the parent method (index)
	 *
	 * @param string $entity_key
	 * @param string $method
	 * @param string|null $parentmethod
	 * @param object|null $object
	 * @return string
	 */
	private function getFrontSeoUrl(string $entity_key, string $method, $parentmethod = null, $object = null)
	{

		if ($entity_key == 'page') {

			// page
			if (Route::has('entity.page.show.' . $object->id)) {
				$route = route('entity.page.show.' . $object->id);
			} else {
				$route = route('content.page.show', ['id' => $object->slug]);
			}

		} else {

			// entity

			if (!empty($object)) {

				// single (show)
				if (Route::has('entitytag.' . $entity_key . '.' . $parentmethod . '.' . $method)) {
					$route = route('entitytag.' . $entity_key . '.' . $parentmethod . '.' . $method,
						['slug' => $object->slug]);
				} elseif (Route::has('entity.' . $entity_key . '.' . $parentmethod . '.' . $method)) {
					$route = route('entity.' . $entity_key . '.' . $parentmethod . '.' . $method,
						['slug' => $object->slug]);
				} elseif (Route::has('contenttag.' . $entity_key . '.' . $parentmethod . '.' . $method)) {
					$route = route('contenttag.' . $entity_key . '.' . $parentmethod . '.' . $method,
						['id' => $object->id]);
				} else {
					$route = route('content.' . $entity_key . '.' . $parentmethod . '.' . $method,
						['id' => $object->id]);
				}

			} else {

				// list (index)
				if (Route::has('entitytag.' . $entity_key . '.' . $method)) {
					$route = route('entitytag.' . $entity_key . '.' . $method);
				} elseif (Route::has('entity.' . $entity_key . '.' . $method)) {
					$route = route('entity.' . $entity_key . '.' . $method);
				} elseif (Route::has('contenttag.' . $entity_key . '.' . $method)) {
					$route = route('contenttag.' . $entity_key . '.' . $method);
				} else {
					$route = route('content.' . $entity_key . '.' . $method);
				}

			}

		}

		return $route;

	}

	/**
	 * Get the Lara Entity Class by key
	 *
	 * @param string $entity_key
	 * @return mixed|null
	 */
	private function getFrontEntByKey(string $entity_key)
	{

		$lara = $this->getFrontEntityVar($entity_key);

		if ($lara) {
			$entity = new $lara;
		} else {
			$entity = null;
		}

		return $entity;

	}

	/**
	 * Translate entity key to a full Lara Entity class name
	 *
	 * @param string $entity_key
	 * @return string
	 */
	private function getFrontEntityVar(string $entity_key)
	{

		$laraClass = '\Lara\Common\Lara\\' . ucfirst($entity_key) . 'Entity';

		if (!class_exists($laraClass)) {

			$laraClass = '\Eve\Lara\\' . ucfirst($entity_key) . 'Entity';

			if (!class_exists($laraClass)) {

				$laraClass = null;

			}

		}

		return $laraClass;

	}

	/**
	 * Get Page Block for Email
	 *
	 * @param string $language
	 * @param string $entity_key
	 * @return mixed
	 */
	private function getEmailPageContent(string $language, string $entity_key)
	{

		$slug = $entity_key . '-email-' . $language;

		$object = Page::langIs($language)
			->groupIs('email')
			->where('slug', $slug)->first();

		if (empty($object)) {

			$title = ucfirst($entity_key) . ' Email Title';

			// get default backend user
			$user = User::where('username', 'admin')->first();

			$object = $this->createNewModulePage($user->id, $language, $title, 'email', $slug);

		}

		return $object;

	}

	/**
	 * Create a specific module page
	 *
	 * @param int $user_id
	 * @param string $language
	 * @param string $title
	 * @param string $cgroup
	 * @param string $slug
	 * @return mixed
	 */
	private function createNewModulePage(int $user_id, string $language, string $title, string $cgroup, string $slug)
	{

		$entity = Entity::where('entity_key', 'page')->first();
		$lara = $this->getFrontEntByKey($entity->entity_key);
		$pageEntity = new $lara;

		$data = [
			'title'     => $title,
			'menuroute' => '',
		];

		if ($pageEntity->hasUser()) {
			$data = array_merge($data, ['user_id' => $user_id]);
		}
		if ($pageEntity->hasLanguage()) {
			$data = array_merge($data, ['language' => $language]);
		}
		if ($pageEntity->hasSlug()) {
			$data = array_merge($data, ['slug' => $slug, 'slug_lock' => 1]);
		}
		if ($pageEntity->hasBody()) {
			$data = array_merge($data, ['body' => '']);
		}
		if ($pageEntity->hasLead()) {
			$data = array_merge($data, ['lead' => '']);
		}
		if ($pageEntity->hasGroups()) {
			$data = array_merge($data, ['cgroup' => $cgroup]);
		}
		if ($pageEntity->hasStatus()) {
			$data = array_merge($data, ['publish' => 1, 'publish_from' => Carbon::now()]);
		}

		$newModulePage = Page::create($data);

		return $newModulePage;
	}

	/**
	 * Find special Module Page by their Slug
	 *
	 * Most content entities, other that Pages, are often displayed as lists,
	 * either with or without a master/detail structure.
	 * Well known examples are blogs, team pages, events, etc.
	 *
	 * When a specific index method (!Page) is attached to a frontend menu item,
	 * we automatically attach a special kind of page (called a 'module page') to this menu item.
	 * A 'module page' is technically a Page object with a group value of 'module'.
	 *
	 * This so-called 'module page' can be seen as a 'container' in which the list is displayed.
	 * Think of it as a Wordpress page, with a shortcode to a special plugin in it.
	 *
	 * This module page gives us the following advantages:
	 * - we can add a custom intro (title, text, images, hooks) to the list
	 * - we can assign custom layout to the module page
	 * - we can add seo to the module page
	 *
	 * Because module page are fetched by their unique slugs ('team-index-module-[lang]'),
	 * the slugs are always locked, and cannot be modified by webmasters.
	 *
	 * @param string $language
	 * @param object $entity
	 * @param string $method
	 * @return mixed
	 */
	private function getModulePageBySlug(string $language, object $entity, string $method)
	{

		$modulePageSlug = $entity->getEntityKey() . '-' . $method . '-module-' . $language;
		$modulePageTitle = ucfirst($entity->getEntityKey()) . ' ' . ucfirst($method) . ' Module Page';

		$modulePage = Page::langIs($language)->groupIs('module')->where('slug', $modulePageSlug)->first();

		if (empty($modulePage)) {

			$newModulePage = $this->createNewModulePage(Auth::user()->id, $language, $modulePageTitle, 'module', $modulePageSlug);

			return $newModulePage;

		} else {

			if (isset($modulePage->lead)) {
				$modulePage->lead = $this->replaceShortcodes($modulePage->lead);
			}
			if (isset($modulePage->body)) {
				$modulePage->body = $this->replaceShortcodes($modulePage->body);
			}

			return $modulePage;

		}

	}

	/**
	 * Get SEO values for a specific object
	 *
	 * Fallback: default values
	 *
	 * @param object $object
	 * @param object|null $fallback
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getSeo(object $object, $fallback = null)
	{

		$app = app();
		$seo = $app->make('stdClass');

		// SEO Title
		if ($object->seo && !empty($object->seo->seo_title)) {
			$seo->seo_title = $object->seo->seo_title;
		} elseif ($fallback && $fallback->seo && !empty($fallback->seo->seo_title)) {
			$seo->seo_title = $fallback->seo->seo_title;
		} else {
			$seo->seo_title = $object->title;
		}

		// SEO Description
		if ($object->seo && !empty($object->seo->seo_description)) {
			$seo->seo_description = $object->seo->seo_description;
		} elseif ($fallback && $fallback->seo && !empty($fallback->seo->seo_description)) {
			$seo->seo_description = $fallback->seo->seo_description;
		} else {
			$seo->seo_description = $this->getDefaultSeoByKey($object->language, 'seo_description');
		}

		// SEO Keywords
		if ($object->seo && !empty($object->seo->seo_keywords)) {
			$seo->seo_keywords = $object->seo->seo_keywords;
		} elseif ($fallback && $fallback->seo && !empty($fallback->seo->seo_keywords)) {
			$seo->seo_keywords = $fallback->seo->seo_keywords;
		} else {
			$seo->seo_keywords = $this->getDefaultSeoByKey($object->language, 'seo_keywords');
		}

		return $seo;

	}

	/**
	 * Get the default SEO value for a specific key
	 *
	 * The default SEO values are set  on the home page
	 *
	 * @param string $language
	 * @param string $key
	 * @return string|null
	 */
	private function getDefaultSeoByKey(string $language, string $key)
	{

		$object = $this->getHomePageObject($language);

		if ($object && isset($object->seo)) {
			$value = $object->seo->$key;
		} else {
			$value = null;
		}

		return $value;

	}

	/**
	 * Get the HomePage
	 *
	 * If the Mainmenu is synced to the Pages,
	 * get it from pages table directly (faster)
	 *
	 * If not, get the page ID from the menu table
	 *
	 * @param string $language
	 * @return object|null
	 */
	private function getHomePageObject(string $language)
	{

		$mainMenuID = $this->getFrontMainMnuId();

		if ($mainMenuID) {

			$home = Menuitem::langIs($language)
				->menuIs($mainMenuID)
				->whereNull('parent_id')
				->first();

			if ($home->object_id) {

				return Page::find($home->object_id);

			} else {
				return null;
			}

		} else {
			return null;
		}

	}

	/**
	 * Check if the main menu exists
	 * If not, create it
	 *
	 * @return int
	 */
	private function getFrontMainMnuId()
	{

		$mainMenu = Menu::where('slug', 'main')->first();

		if (empty($mainMenu)) {

			// create main menu
			$newMainMenu = Menu::create([
				'title' => 'Main',
				'slug'  => 'main',
			]);

			return $newMainMenu->id;

		} else {

			return $mainMenu->id;
		}

	}

	/**
	 * Get all the default SEO values
	 *
	 * The default SEO values are set on the home page
	 *
	 * @param string $language
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getDefaultSeo(string $language)
	{

		$object = $this->getHomePageObject($language);

		$app = app();
		$seo = $app->make('stdClass');

		if (!empty($object)) {

			if ($object->seo) {
				$seo->seo_title = $object->seo->seo_title;
				$seo->seo_description = $object->seo->seo_description;
				$seo->seo_keywords = $object->seo->seo_keywords;
			} else {
				$seo->seo_title = null;
				$seo->seo_description = null;
				$seo->seo_keywords = null;
			}

		} else {

			$seo->seo_title = null;
			$seo->seo_description = null;
			$seo->seo_keywords = null;

		}

		return $seo;

	}

	/**
	 * Get all the Opengraph data
	 *
	 * @param object $object
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function getOpengraph(object $object)
	{

		// get settings
		$settings = Setting::pluck('value', 'key')->toArray();
		$settngz = json_decode(json_encode($settings), false);

		$app = app();
		$og = $app->make('stdClass');

		// Title
		if ($object->opengraph && !empty($object->opengraph->og_title)) {
			$og->og_title = $object->opengraph->og_title;
		} else {
			$og->og_title = $object->title;
		}

		// Description
		if (isset($settngz->og_descr_max)) {
			$og->og_descr_max = $settngz->og_descr_max;
		}

		if ($object->opengraph && !empty($object->opengraph->og_description)) {
			$og->og_description = $object->opengraph->og_description;
		} else {
			if ($object->lead != '') {
				$og->og_description = str_limit(strip_tags($object->lead), $og->og_descr_max, '');
			} elseif ($object->body != '') {
				$og->og_description = str_limit(strip_tags($object->body), $og->og_descr_max, '');
			} else {
				$og->og_description = '';
			}
		}

		// Image
		if ($object->media->count()) {

			if ($object->opengraph && !empty($object->opengraph->og_image)) {
				$og->og_image = $object->opengraph->og_image;
			} else {
				// use featured image
				$og->og_image = $object->featured->filename;
			}

			if (isset($settngz->og_image_width)) {
				$og->og_image_width = $settngz->og_image_width;
			} else {
				$og->og_image_width = 1200; // Facebook recommended width
			}
			if (isset($settngz->og_image_height)) {
				$og->og_image_height = $settngz->og_image_height;
			} else {
				$og->og_image_height = 630; // Facebook recommended height
			}
		} else {
			$og->og_image = null;
		}

		// Type
		if (isset($settngz->og_type)) {
			$og->og_type = $settngz->og_type;
		} else {
			$og->og_type = null;
		}

		// Site name
		if (isset($settngz->og_site_name)) {
			$og->og_site_name = $settngz->og_site_name;
		} else {
			$og->og_site_name = null;
		}

		return $og;

	}

	/**
	 * Replace the shortcodes in a content string
	 *
	 * @param string|null $str
	 * @return string|null
	 */
	private function replaceShortcodes(string $str = null)
	{
		if ($str) {

			// See: Lara\Admin\Resources\Views\_scripts\tiny.blade.php

			$columns = [
				2 => [
					'colcount' => 2,
					'cols'     => [
						[
							'colnr'    => '1',
							'colwidth' => 6,
						],
						[
							'colnr'    => '2',
							'colwidth' => 6,
						],
					],
				],
				3 => [
					'colcount' => 3,
					'cols'     => [
						[
							'colnr'    => '1',
							'colwidth' => 4,
						],
						[
							'colnr'    => '2',
							'colwidth' => 4,
						],
						[
							'colnr'    => '3',
							'colwidth' => 4,
						],
						[
							'colnr'    => '12',
							'colwidth' => 8,
						],
						[
							'colnr'    => '23',
							'colwidth' => 8,
						],
					],
				],
				4 => [
					'colcount' => 4,
					'cols'     => [
						[
							'colnr'    => '1',
							'colwidth' => 3,
						],
						[
							'colnr'    => '2',
							'colwidth' => 3,
						],
						[
							'colnr'    => '3',
							'colwidth' => 3,
						],
						[
							'colnr'    => '4',
							'colwidth' => 3,
						],
						[
							'colnr'    => '123',
							'colwidth' => 9,
						],
						[
							'colnr'    => '234',
							'colwidth' => 9,
						],

					],
				],
			];

			$columns = json_decode(json_encode($columns), false);

			foreach ($columns as $cl) {

				$varfound = 'sc_col' . $cl->colcount . '_found';
				$$varfound = false;

				foreach ($cl->cols as $rcol) {

					$rcol->colnr = $rcol->colnr;

					$var_str = 'pos_str' . $rcol->colnr . $cl->colcount;
					$var_end = 'pos_end' . $rcol->colnr . $cl->colcount;
					$$var_str = strpos($str, '[kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']');
					$$var_end = strpos($str, '[/kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']');

					if ($$var_str !== false || $$var_end !== false) {
						// shortcode found
						$$varfound = true;
					}
				}

				if ($$varfound == true) {

					// first remove the <p> tags form the shortcode
					foreach ($cl->cols as $rcol) {

						$str = str_replace('<p>[kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']</p>', '[kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']',
							$str);
						$str = str_replace('<p>[/kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']</p>',
							'[/kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']', $str);
					}

					// check if shortcode is complete

					$varcomplete = 'sc_col' . $cl->colcount . '_complete';
					$$varcomplete = true;

					foreach ($cl->cols as $rcol) {

						$var_str = 'pos_str' . $rcol->colnr . $cl->colcount;
						$var_end = 'pos_end' . $rcol->colnr . $cl->colcount;

						if ($$var_str === false || $$var_end === false) {
							// shortcode incomplete
							$$varcomplete = false;
						}
					}

					if ($$varcomplete = true) {

						// correct shortcode found, start replacing

						$gutterClass = config('lara-front.shortcode.bootstrap_gutter_class');
						$breakpoint = config('lara-front.shortcode.bootstrap_breakpoint');

						foreach ($cl->cols as $rcol) {

							if ($rcol->colnr == 1) {
								$str = str_replace('[kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']',
									'<div class="row '. $gutterClass .'"><div class="col-' . $breakpoint . '-' . $rcol->colwidth . '">', $str);
							} else {
								$str = str_replace('[kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']',
									'<div class="col-' . $breakpoint . '-' . $rcol->colwidth . '">', $str);
							}

							if ($rcol->colnr < $cl->colcount) {
								$str = str_replace('[/kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']', '</div>', $str);
							} else {
								$str = str_replace('[/kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']', '</div></div>', $str);
							}

						}

					} else {

						// incorrect shortcode, remove all shortcodes
						foreach ($cl->cols as $rcol) {

							$str = str_replace('[kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']', '', $str);
							$str = str_replace('[/kolom_' . $rcol->colnr . 'van' . $cl->colcount . ']', '', $str);
						}

					}

				}

			}

			return $str;

		} else {

			return null;

		}

	}

	/**
	 * @param $language
	 * @param $entity
	 * @param $object
	 * @param $menuTag
	 * @return string
	 */
	private function getEntityListUrl($language, $entity, $object, $menuTag): string
	{

		if ($menuTag) {
			$node = Menuitem::where('language', $language)
				->where('entity_id', $entity->id)
				->where('tag_id', $menuTag->id)
				->first();

			if ($node) {
				$url = url($language . '/' . $node->route);
			} else {
				$url = $this->getDefaultEntityListUrl($language, $entity);
			}
		} else {
			$url = $this->getDefaultEntityListUrl($language, $entity);
		}

		return $url;

		/*
		// get base url
		$baseUrl = config('app.url');
		$baseLenth = strlen($baseUrl);

		// check previous url
		$prevUrl = URL::previous();
		$prevBaseUrl = substr($prevUrl, 0, $baseLenth);

		if ($prevBaseUrl == $baseUrl) {

			// add 4 characters for the language prefix
			$baseLenthWithLanguage = $baseLenth + 4;

			// get the full route
			$previousRoute = substr($prevUrl, $baseLenthWithLanguage);

			// remove parameters
			list($prevRoute) = explode('?', $previousRoute);

			// check if the previous url was the same entity, maybe with a menu tag(!)
			$menuItem = Menuitem::where('route', $prevRoute)->where('entity_id', $entity->id)->first();

			if($menuItem) {
				return $prevUrl;
			} else {
				return $defaultUrl;
			}

		} else {
			return $defaultUrl;
		}
		*/

	}

	private function getDefaultEntityListUrl($language, $entity): string
	{
		$node = Menuitem::where('language', $language)
			->where('entity_id', $entity->id)
			->whereNull('tag_id')
			->first();

		if ($node) {
			$url = url($language . '/' . $node->route);
		} else {
			$url = route($entity->getPrefix() . '.' . $entity->getEntityKey() . '.index');
		}

		return $url;
	}

}
