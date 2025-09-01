<?php

namespace Lara\Common\Lara;

class ExportEntity extends LaraTool
{

	/**
	 * @var string
	 */
	public $entity_key = 'export';

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
	protected $title = 'Export';

	/**
	 * @var string
	 */
	protected $entity_controller = 'ExportController';

	/**
	 * @var string|null
	 */
	protected $menuParent = 'tools';

	/**
	 * @var int|null
	 */
	protected $menuPosition = 810;

	/**
	 * @var string|null
	 */
	protected $menuIcon = 'fa-file-download';

}

