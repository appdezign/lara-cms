<?php

namespace Lara\Common\Lara;

class DashboardEntity extends LaraTool
{

	/**
	 * @var string
	 */
	public $entity_key = 'dashboard';

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
	protected $title = 'Dashboard';

	/**
	 * @var string
	 */
	protected $entity_controller = 'DashboardController';

	/**
	 * @var string|null
	 */
	protected $menuParent = 'root';

	/**
	 * @var int|null
	 */
	protected $menuPosition = 101;

	/**
	 * @var string|null
	 */
	protected $menuIcon = 'fa-dashboard';

}

