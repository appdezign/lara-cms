<?php

namespace Lara\Common\Lara;

/*
 * Placeholder Class for Entities with no content, and therefor no MODEL
 */
class LaraTool
{

	/* ~~~~~~~~~~~~ SET IN CHILD CLASS ~~~~~~~~~~~~ */

	/**
	 * @var string
	 */
	public $entity_key;

	/**
	 * @var string
	 */
	protected $module; // eve, admin

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
	protected $hasResourceRoutes = true;

	/* ~~~~~~~~~~~~ OPTIONS ~~~~~~~~~~~~ */

	/**
	 * @var bool
	 */
	protected $hasLanguage = false;

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

	/* ~~~~~~~~~~~~ Getters & Setters ~~~~~~~~~~~~ */

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
	 * @return bool
	 */
	public function hasResourceRoutes()
	{
		return $this->hasResourceRoutes;
	}

	/**
	 * @return string|null
	 */
	public function getMenuParent()
	{
		return $this->menuParent;
	}

	/**
	 * @return int|null
	 */
	public function getMenuPosition()
	{
		return $this->menuPosition;
	}

	/**
	 * @return string|null
	 */
	public function getMenuIcon()
	{
		return $this->menuIcon;
	}

	/**
	 * @return bool
	 */
	public function hasLanguage()
	{
		return $this->hasLanguage;
	}

	/**
	 * @return array
	 */
	public function getCustomColumns()
	{
		return $this->customcolumns;
	}

	/**
	 * @return array
	 */
	public function getViews()
	{
		return $this->views;
	}

	/**
	 * @return array
	 */
	public function getRelations()
	{
		return $this->relations;
	}

}
