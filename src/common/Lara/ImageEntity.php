<?php

namespace Lara\Common\Lara;

class ImageEntity extends LaraTool
{

	/**
	 * @var string
	 */
	public $entity_key = 'image';

	/**
	 * @var string
	 */
	protected $module = 'admin';

	/**
	 * @var string
	 */
	protected $egroup = 'media';

	/**
	 * @var string
	 */
	protected $title = 'Images';

	/**
	 * @var string
	 */
	protected $entity_controller = 'ImageController';

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

