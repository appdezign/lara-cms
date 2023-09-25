<?php

namespace Lara\Common\Lara;

class CacheEntity extends LaraTool
{

	/**
	 * @var string
	 */
	public $entity_key = 'cache';

	/**
	 * @var string
	 */
	protected $module = 'admin';

	/**
	 * @var string
	 */
	protected $egroup = 'misc';

	/**
	 * @var string
	 */
	protected $title = 'Cache';

	/**
	 * @var string
	 */
	protected $entity_controller = 'CacheController';

	/**
	 * @var string|null
	 */
	protected $menuParent = null;

	/**
	 * @var int|null
	 */
	protected $menuPosition = null;

	/**
	 * @var string|null
	 */
	protected $menuIcon = null;

}

