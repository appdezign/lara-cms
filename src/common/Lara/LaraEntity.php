<?php

namespace Lara\Common\Lara;

use Illuminate\Support\Facades\Storage;
use Lara\Common\Models\Entity;

use Cache;

class LaraEntity
{

	/**
	 * @var object|null
	 */
	protected $entity;

	/* ~~~~~~~~~~~~ SET IN CHILD CLASS ~~~~~~~~~~~~ */

	/**
	 * @var string
	 */
	public $entity_key;

	/**
	 * @var string
	 */
	protected $module; // eve, admin

	/* ~~~~~~~~~~~~ GET FROM DB ~~~~~~~~~~~~ */

	/**
	 * @var int
	 */
	public $id; // used by the menu editor

	/**
	 * @var string
	 */
	protected $egroup;

	/**
	 * @var string|null
	 */
	protected $title;

	/**
	 * @var string|null
	 */
	protected $entity_controller;

	/**
	 * @var string|null
	 */
	protected $entity_model_class;


	/**
	 * @var string|null
	 */
	protected $menuParent;

	/**
	 * @var int|null
	 */
	protected $menuPosition;

	/**
	 * @var string|null
	 */
	protected $menuIcon;

	/* ~~~~~~~~~~~~ SET AT RUNTIME ~~~~~~~~~~~~ */

	/**
	 * @var string|null
	 */
	protected $entity_route_key;

	/**
	 * @var string|null
	 */
	protected $prefix;

	/**
	 * @var string|null
	 */
	protected $method;

	/**
	 * @var int|null
	 */
	protected $object_id;

	/* ~~~~~~~~~~~~ FRONTEND ~~~~~~~~~~~~ */

	/**
	 * @var string|null
	 */
	protected $active_route;

	/**
	 * @var string|null
	 */
	protected $base_entity_route;

	/**
	 * @var string|null
	 */
	protected $parent_route;

	/**
	 * @var array
	 */
	protected $activetags;

	/**
	 * @var bool
	 */
	protected $is_home = false;

	/* ~~~~~~~~~~~~ ALIAS ~~~~~~~~~~~~ */

	/**
	 * @var string|null
	 */
	protected $alias;

	/**
	 * @var bool
	 */
	protected $isAlias = false;

	/**
	 * @var bool
	 */
	protected $aliasIsGroup = false;

	/* ~~~~~~~~~~~~ MISCELANEOUS ~~~~~~~~~~~~ */

	/**
	 * @var bool
	 */
	protected $hasResourceRoutes = false;

	/**
	 * @var bool
	 */
	protected $hasFrontAuth = false;

	/**
	 * @var array
	 */
	protected $with = [];

	/* ~~~~~~~~~~~~ OPTIONS ~~~~~~~~~~~~ */

	/**
	 * @var bool
	 */
	protected $hasUser = false;

	/**
	 * @var bool
	 */
	protected $hasLanguage = false;

	/**
	 * @var bool
	 */
	protected $hasSlug = false;

	/**
	 * @var bool
	 */
	protected $hasLead = false;

	/**
	 * @var bool
	 */
	protected $hasBody = false;

	/**
	 * @var bool
	 */
	protected $hasStatus = false;

	/**
	 * @var bool
	 */
	protected $hasHideinlist = false;

	/**
	 * @var bool
	 */
	protected $hasExpiration = false;

	/**
	 * @var bool
	 */
	protected $hasApp = false;

	/**
	 * @var bool
	 */
	protected $hasGroups = false;

	/**
	 * @var array
	 */
	protected $groups = [];

	/**
	 * @var string|null
	 */
	protected $defaultGroup;

	/**
	 * @var bool
	 */
	protected $isSortable = false;

	/**
	 * @var string|null
	 */
	protected $sortField = 'id';

	/**
	 * @var string|null
	 */
	protected $sortOrder = 'asc';

	/**
	 * @var string|null
	 */
	protected $sortField2nd = null;

	/**
	 * @var string|null
	 */
	protected $sortOrder2nd = null;

	/**
	 * @var bool
	 */
	protected $hasFields = false;

	/* ~~~~~~~~~~~~ SETTINGS ~~~~~~~~~~~~ */

	/**
	 * @var bool
	 */
	protected $hasSeo = false;

	/**
	 * @var bool
	 */
	protected $hasOpengraph = false;

	/**
	 * @var bool
	 */
	protected $hasLayout = false;

	/**
	 * @var bool
	 */
	protected $hasRelated = false;

	/**
	 * @var bool
	 */
	protected $isRelatable = false;

	/**
	 * @var bool
	 */
	protected $hasTags = false;

	/**
	 * @var bool
	 */
	protected $defaultTag;

	/**
	 * @var bool
	 */
	protected $hasSync = false;

	/**
	 * @var bool
	 */
	protected $hasImages = false;

	/**
	 * @var bool
	 */
	protected $hasVideos = false;

	/**
	 * @var bool
	 */
	protected $hasFiles = false;

	/**
	 * @var bool
	 */
	protected $hasVideoFiles = false;

	/**
	 * @var int
	 */
	protected $maxImages = 0;

	/**
	 * @var int
	 */
	protected $maxVideos = 0;

	/**
	 * @var int
	 */
	protected $maxFiles = 0;

	/**
	 * @var int
	 */
	protected $maxVideoFiles = 0;

	/**
	 * @var string|null
	 */
	protected $diskImages = null;

	/**
	 * @var string|null
	 */
	protected $diskVideos = null;

	/**
	 * @var string|null
	 */
	protected $diskFiles = null;

	/**
	 * @var string|null
	 */
	protected $imageUrl = null;

	/**
	 * @var string|null
	 */
	protected $videoUrl = null;

	/**
	 * @var string|null
	 */
	protected $fileUrl = null;


	/* ~~~~~~~~~~~~ PANELS ~~~~~~~~~~~~ */

	/**
	 * @var bool
	 */
	protected $hasSearch = false;

	/**
	 * @var bool
	 */
	protected $hasBatch = false;

	/**
	 * @var bool
	 */
	protected $hasFilters = false;

	/**
	 * @var bool
	 */
	protected $showAuthor = false;

	/**
	 * @var bool
	 */
	protected $showStatus = false;

	/**
	 * @var bool
	 */
	protected $hasTinyLead = false;

	/**
	 * @var bool
	 */
	protected $hasTinyBody = false;

	/* ~~~~~~~~~~~~ Fields, Views, Relations ~~~~~~~~~~~~ */

	/**
	 * entity fields
	 *
	 * @var array
	 */
	protected $customcolumns = [];

	/**
	 * entity views
	 *
	 * @var array
	 */
	protected $views = [];

	/**
	 * entity relations
	 *
	 * @var array
	 */
	protected $relations = [];

	/**
	 * @var string|null
	 */
	protected $relationFilterForeignkey;

	/**
	 * @var string|null
	 */
	protected $relationFilterEntitykey;

	/**
	 * @var string|null
	 */
	protected $relationFilterModelclass;

	public function __construct()
	{

		// get entity configuration from database
		$this->entity = $this->getEntity($this->entity_key);

	}

	/* ~~~~~~~~~~~~ Getters & Setters (runtime) ~~~~~~~~~~~~ */

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return void
	 */
	public function setId(int $id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getEntityKey()
	{
		return $this->entity_key;
	}

	/**
	 * @param string $entity_key
	 * @return void
	 */
	public function setEntityKey(string $entity_key)
	{
		$this->entity_key = $entity_key;
	}

	/**
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * @param string $module
	 * @return void
	 */
	public function setModule(string $module)
	{
		$this->module = $module;
	}

	/**
	 * @return string
	 */
	public function getEgroup()
	{
		return $this->egroup;
	}

	/**
	 * @param string $egroup
	 * @return void
	 */
	public function setEgroup(string $egroup)
	{
		$this->egroup = $egroup;
	}

	/**
	 * @return string|null
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string|null $title
	 * @return void
	 */
	public function setTitle(string $title = null)
	{
		$this->title = $title;
	}

	/**
	 * @return string|null
	 */
	public function getEntityModelClass()
	{
		return $this->entity_model_class;
	}

	/**
	 * @param string|null $entity_model_class
	 * @return void
	 */
	public function setEntityModelClass(string $entity_model_class = null)
	{
		$this->entity_model_class = $entity_model_class;
	}

	/**
	 * @return string|null
	 */
	public function getEntityController()
	{
		return $this->entity_controller;
	}

	/**
	 * @param string|null $entity_controller
	 * @return void
	 */
	public function setEntityController(string $entity_controller = null)
	{
		$this->entity_controller = $entity_controller;
	}

	/**
	 * @return string|null
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @param string|null $method
	 * @return void
	 */
	public function setMethod(string $method = null)
	{
		$this->method = $method;
	}

	/**
	 * @return int|null
	 */
	public function getObjectId()
	{
		return $this->object_id;
	}

	/**
	 * @param int|null $object_id
	 * @return void
	 */
	public function setObjectId(int $object_id = null)
	{
		$this->object_id = $object_id;
	}

	/**
	 * @return string|null
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * @param string|null $prefix
	 * @return void
	 */
	public function setPrefix(string $prefix = null)
	{
		$this->prefix = $prefix;
	}

	/**
	 * @return string|null
	 */
	public function getEntityRouteKey()
	{
		return $this->entity_route_key;
	}

	/**
	 * @param string|null $entity_route_key
	 * @return void
	 */
	public function setEntityRouteKey(string $entity_route_key = null)
	{
		$this->entity_route_key = $entity_route_key;
	}

	/**
	 * @return array
	 */
	public function getActiveTags()
	{
		return $this->activetags;
	}

	/**
	 * @param array $activetags
	 * @return void
	 */
	public function setActiveTags(array $activetags)
	{
		$this->activetags = $activetags;
	}

	/**
	 * @return string|null
	 */
	public function getActiveRoute()
	{
		return $this->active_route;
	}

	/**
	 * @param string|null $active_route
	 * @return void
	 */
	public function setActiveRoute(string $active_route = null)
	{
		$this->active_route = $active_route;
	}

	/**
	 * @return bool
	 */
	public function isHome()
	{
		return $this->is_home;
	}

	/**
	 * @param bool $is_home
	 * @return void
	 */
	public function setIsHome(bool $is_home)
	{
		$this->is_home = $is_home;
	}

	/**
	 * @return string|null
	 */
	public function getBaseEntityRoute()
	{
		return $this->base_entity_route;
	}

	/**
	 * @param string|null $base_entity_route
	 * @return void
	 */
	public function setBaseEntityRoute(string $base_entity_route = null)
	{
		$this->base_entity_route = $base_entity_route;
	}

	/**
	 * @return string|null
	 */
	public function getParentRoute()
	{
		return $this->parent_route;
	}

	/**
	 * @param string|null $parent_route
	 * @return void
	 */
	public function setParentRoute(string $parent_route = null)
	{
		$this->parent_route = $parent_route;
	}

	/**
	 * @return string|null
	 */
	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * @param string|null $alias
	 * @return void
	 */
	public function setAlias(string $alias = null)
	{
		$this->alias = $alias;
	}

	/**
	 * @return bool
	 */
	public function isAlias()
	{
		return $this->isAlias;
	}

	/**
	 * @param bool $isAlias
	 * @return void
	 */
	public function setIsAlias(bool $isAlias)
	{
		$this->isAlias = $isAlias;
	}

	/**
	 * @return bool
	 */
	public function getAliasIsGroup()
	{
		return $this->aliasIsGroup;
	}

	/**
	 * @param bool $aliasIsGroup
	 * @return void
	 */
	public function setAliasIsGroup(bool $aliasIsGroup)
	{
		$this->aliasIsGroup = $aliasIsGroup;
	}

	/* ~~~~~~~~~~~~ Entity ~~~~~~~~~~~~ */

	/**
	 * @return array
	 */
	public function getEager()
	{
		if ($this->entity->columns->has_user == 1) {
			$this->with[] = 'user';
		}
		if ($this->entity->objectrelations->has_images == 1) {
			$this->with[] = 'media';
		}
		if ($this->entity->objectrelations->has_files == 1) {
			$this->with[] = 'files';
		}

		return $this->with;

	}

	/**
	 * @return bool
	 */
	public function hasResourceRoutes()
	{
		$this->hasResourceRoutes = boolval($this->entity->resource_routes);

		return $this->hasResourceRoutes;
	}

	/**
	 * @return bool
	 */
	public function hasFrontAuth()
	{
		$this->hasFrontAuth = boolval($this->entity->has_front_auth);

		return $this->hasFrontAuth;
	}

	/**
	 * @return string|null
	 */
	public function getMenuParent()
	{
		$this->menuParent = $this->entity->menu_parent;

		return $this->menuParent;
	}

	/**
	 * @return int|null
	 */
	public function getMenuPosition()
	{
		$this->menuPosition = $this->entity->menu_position;

		return $this->menuPosition;
	}

	/**
	 * @return string|null
	 */
	public function getMenuIcon()
	{
		$this->menuIcon = $this->entity->menu_icon;

		return $this->menuIcon;
	}

	/* ~~~~~~~~~~~~ SETTINGS ~~~~~~~~~~~~ */

	/**
	 * @return bool
	 */
	public function hasSeo()
	{
		$this->hasSeo = boolval($this->entity->objectrelations->has_seo);

		return $this->hasSeo;
	}

	/**
	 * @return bool
	 */
	public function hasOpengraph()
	{
		$this->hasOpengraph = boolval($this->entity->objectrelations->has_opengraph);

		return $this->hasOpengraph;
	}

	/**
	 * @return bool
	 */
	public function hasLayout()
	{
		$this->hasLayout = boolval($this->entity->objectrelations->has_layout);

		return $this->hasLayout;
	}

	/**
	 * @return bool
	 */
	public function hasRelated()
	{
		$this->hasRelated = boolval($this->entity->objectrelations->has_related);

		return $this->hasRelated;
	}

	/**
	 * @return bool
	 */
	public function isRelatable()
	{
		$this->isRelatable = boolval($this->entity->objectrelations->is_relatable);

		return $this->isRelatable;
	}

	/**
	 * @return bool
	 */
	public function hasTags()
	{
		$this->hasTags = boolval($this->entity->objectrelations->has_tags);

		return $this->hasTags;
	}

	/**
	 * @return bool
	 */
	public function getDefaultTag()
	{
		$this->defaultTag = $this->entity->objectrelations->tag_default;

		return $this->defaultTag;
	}

	/**
	 * @return bool
	 */
	public function hasSync()
	{
		$this->hasSync = boolval($this->entity->objectrelations->has_sync);

		return $this->hasSync;
	}

	/**
	 * @return bool
	 */
	public function hasImages()
	{
		$this->hasImages = boolval($this->entity->objectrelations->has_images);

		return $this->hasImages;
	}

	/**
	 * @return bool
	 */
	public function hasVideos()
	{
		$this->hasVideos = boolval($this->entity->objectrelations->has_videos);

		return $this->hasVideos;
	}

	/**
	 * @return bool
	 */
	public function hasFiles()
	{
		$this->hasFiles = boolval($this->entity->objectrelations->has_files);

		return $this->hasFiles;
	}

	/**
	 * @return bool
	 */
	public function hasVideoFiles()
	{
		$this->hasVideoFiles = boolval($this->entity->objectrelations->has_videofiles);

		return $this->hasVideoFiles;
	}

	/**
	 * @return int
	 */
	public function getMaxImages()
	{
		$this->maxImages = intval($this->entity->objectrelations->max_images);

		return intval($this->maxImages);
	}

	/**
	 * @return int
	 */
	public function getMaxVideos()
	{
		$this->maxVideos = intval($this->entity->objectrelations->max_videos);

		return intval($this->maxVideos);
	}

	/**
	 * @return int
	 */
	public function getMaxFiles()
	{
		$this->maxFiles = intval($this->entity->objectrelations->max_files);

		return intval($this->maxFiles);
	}

	/**
	 * @return int
	 */
	public function getMaxVideoFiles()
	{
		$this->maxVideoFiles = intval($this->entity->objectrelations->max_videofiles);

		return intval($this->maxVideoFiles);
	}

	/**
	 * @return string|null
	 */
	public function getDiskForImages()
	{
		$this->diskImages = $this->entity->objectrelations->disk_images;

		if(empty($this->diskImages)) {
			$this->diskImages = config('lara-admin.upload_disks.default');
		}

		return $this->diskImages;
	}

	/**
	 * @return string|null
	 */
	public function getDiskForVideos()
	{
		$this->diskVideos = $this->entity->objectrelations->disk_videos;

		if(empty($this->diskVideos)) {
			$this->diskVideos = config('lara-admin.upload_disks.default');
		}

		return $this->diskVideos;
	}

	/**
	 * @return string|null
	 */
	public function getDiskForFiles()
	{
		$this->diskFiles = $this->entity->objectrelations->disk_files;

		if(empty($this->diskFiles)) {
			$this->diskFiles = config('lara-admin.upload_disks.default');
		}

		return $this->diskFiles;
	}

	/**
	 * @return string|null
	 */
	public function getUrlForImages()
	{
		$this->imageUrl = Storage::disk($this->getDiskForImages())->url($this->getEntityKey());
		return $this->imageUrl;
	}

	/**
	 * @return string|null
	 */
	public function getUrlForVideos()
	{
		$this->videoUrl = Storage::disk($this->getDiskForVideos())->url($this->getEntityKey());
		return $this->videoUrl;
	}

	/**
	 * @return string|null
	 */
	public function getUrlForFiles()
	{
		$this->fileUrl = Storage::disk($this->getDiskForFiles())->url($this->getEntityKey());
		return $this->fileUrl;
	}

	/* ~~~~~~~~~~~~ OPTIONS ~~~~~~~~~~~~ */

	/**
	 * @return bool
	 */
	public function hasUser()
	{
		$this->hasUser = boolval($this->entity->columns->has_user);

		return $this->hasUser;
	}

	/**
	 * @return bool
	 */
	public function hasLanguage()
	{
		$this->hasLanguage = boolval($this->entity->columns->has_lang);

		return $this->hasLanguage;
	}

	/**
	 * @return bool
	 */
	public function hasSlug()
	{
		$this->hasSlug = boolval($this->entity->columns->has_slug);

		return $this->hasSlug;
	}

	/**
	 * @return bool
	 */
	public function hasLead()
	{
		$this->hasLead = boolval($this->entity->columns->has_lead);

		return $this->hasLead;
	}

	/**
	 * @return bool
	 */
	public function hasBody()
	{
		$this->hasBody = boolval($this->entity->columns->has_body);

		return $this->hasBody;
	}

	/**
	 * @return bool
	 */
	public function hasStatus()
	{
		$this->hasStatus = boolval($this->entity->columns->has_status);

		return $this->hasStatus;
	}

	/**
	 * @return bool
	 */
	public function hasHideinlist()
	{
		$this->hasHideinlist = boolval($this->entity->columns->has_hideinlist);

		return $this->hasHideinlist;
	}

	/**
	 * @return bool
	 */
	public function hasExpiration()
	{
		$this->hasExpiration = boolval($this->entity->columns->has_expiration);

		return $this->hasExpiration;
	}

	/**
	 * @return bool
	 */
	public function hasApp()
	{
		$this->hasApp = boolval($this->entity->columns->has_app);

		return $this->hasApp;
	}

	/**
	 * @return bool
	 */
	public function hasGroups()
	{
		$this->hasGroups = boolval($this->entity->columns->has_groups);

		return $this->hasGroups;
	}

	/**
	 * @return string|null
	 */
	public function getDefaultGroup()
	{
		$this->defaultGroup = $this->entity->columns->group_default;

		return $this->defaultGroup;
	}

	/**
	 * @return array
	 */
	public function getGroups()
	{
		// convert comma separated values to an array
		$values = $this->entity->columns->group_values;
		$array = array_map('trim', explode(',', $values));

		// prepare array for Select2
		$this->groups = array_combine($array, $array);

		return $this->groups;

	}

	/**
	 * @return bool
	 */
	public function isSortable()
	{
		$this->isSortable = boolval($this->entity->columns->is_sortable);

		return $this->isSortable;
	}

	/**
	 * @return string|null
	 */
	public function getSortField()
	{
		$this->sortField = $this->entity->columns->sort_field;

		if(empty($this->sortField)) {
			$this->sortField = 'id';
		}

		return $this->sortField;
	}

	/**
	 * @return string|null
	 */
	public function getSortOrder()
	{
		$this->sortOrder = $this->entity->columns->sort_order;

		if(empty($this->sortOrder)) {
			$this->sortOrder = 'asc';
		}

		return $this->sortOrder;
	}

	/**
	 * @return string|null
	 */
	public function getSortField2nd()
	{
		$this->sortField2nd = $this->entity->columns->sort2_field;

		return $this->sortField2nd;
	}

	/**
	 * @return string|null
	 */
	public function getSortOrder2nd()
	{
		$this->sortOrder2nd = $this->entity->columns->sort2_order;

		return $this->sortOrder2nd;
	}

	/**
	 * @return bool
	 */
	public function hasFields()
	{
		$this->hasFields = boolval($this->entity->columns->has_fields);

		return $this->hasFields;
	}

	/* ~~~~~~~~~~~~ PANELS ~~~~~~~~~~~~ */

	/**
	 * @return bool
	 */
	public function hasSearch()
	{
		$this->hasSearch = boolval($this->entity->panels->has_search);

		return $this->hasSearch;
	}

	/**
	 * @return bool
	 */
	public function hasBatch()
	{
		$this->hasBatch = boolval($this->entity->panels->has_batch);

		return $this->hasBatch;
	}

	/**
	 * @return bool
	 */
	public function hasFilters()
	{
		$this->hasFilters = boolval($this->entity->panels->has_filters);

		return $this->hasFilters;
	}

	/**
	 * @return bool
	 */
	public function getShowAuthor()
	{
		$this->showAuthor = boolval($this->entity->panels->show_author);

		return $this->showAuthor;
	}

	/**
	 * @return bool
	 */
	public function getShowStatus()
	{
		$this->showStatus = boolval($this->entity->panels->show_status);

		return $this->showStatus;
	}

	/**
	 * @return bool
	 */
	public function hasTinyLead()
	{
		$this->hasTinyLead = boolval($this->entity->panels->has_tiny_lead);

		return $this->hasTinyLead;
	}

	/**
	 * @return bool
	 */
	public function hasTinyBody()
	{
		$this->hasTinyBody = boolval($this->entity->panels->has_tiny_body);

		return $this->hasTinyBody;
	}

	/* ~~~~~~~~~~~~ RELATIONS ~~~~~~~~~~~~ */

	/**
	 * @return array
	 */
	public function getCustomColumns()
	{
		$this->customcolumns = $this->entity->customcolumns;

		foreach ($this->customcolumns as $field) {

			$field->fieldvalues = array();

			if (!empty($field->fielddata)) {
				// process fielddata
				$fielddata = array_map('trim', explode(',', $field->fielddata));
				$fielddata = array_combine($fielddata, $fielddata);
				$field->fieldvalues = $fielddata;
			}

		}

		return $this->customcolumns;
	}

	/**
	 * @return array
	 */
	public function getViews()
	{
		$this->views = $this->entity->views;

		return $this->views;
	}

	/**
	 * @return array
	 */
	public function getRelations()
	{
		$this->relations = $this->entity->relations;

		return $this->relations;
	}

	/**
	 * @return string|null
	 */
	public function getRelationFilterForeignkey() {
		return $this->relationFilterForeignkey;
	}

	/**
	 * @param string|null $foreign_key
	 * @return void
	 */
	public function setRelationFilterForeignkey(string $foreign_key = null)
	{
		$this->relationFilterForeignkey = $foreign_key;
	}

	/**
	 * @return string|null
	 */
	public function getRelationFilterEntitykey() {
		return $this->relationFilterEntitykey;
	}

	/**
	 * @param string|null $entity_key
	 * @return void
	 */
	public function setRelationFilterEntitykey(string $entity_key = null)
	{
		$this->relationFilterEntitykey = $entity_key;
	}

	/**
	 * @return string|null
	 */
	public function getRelationFilterModelclass() {
		return $this->relationFilterModelclass;
	}

	/**
	 * @param string|null $model_class
	 * @return void
	 */
	public function setRelationFilterModelclass(string $model_class = null)
	{
		$this->relationFilterModelclass = $model_class;
	}

	/**
	 * @param string $entity_key
	 * @return mixed
	 */
	public function getEntity(string $entity_key)
	{

		$cachekey = 'entity_config_' . $entity_key;

		$entity = Cache::rememberForever($cachekey, function () use ($entity_key) {
			return Entity::where('entity_key', $entity_key)
				->with([
					'egroup',
					'objectrelations',
					'columns',
					'customcolumns',
					'relations',
					'views',
				])->first();
		});

		// set properties
		if($entity->id) {
			$this->setId($entity->id);
		}

		$this->setEgroup($entity->egroup->key);
		$this->setTitle($entity->title);
		$this->setEntityModelClass($entity->entity_model_class);
		$this->setEntityController($entity->entity_controller);

		return $entity;

	}

}
