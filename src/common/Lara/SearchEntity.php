<?php

namespace Lara\Common\Lara;

class SearchEntity extends LaraTool
{

	/**
	 * @var string
	 */
	public $entity_key = 'search';

	/**
	 * @var string
	 */
	protected $module = 'front';

	/**
	 * @var string
	 */
	protected $egroup = 'misc';

	/**
	 * @var string
	 */
	protected $title = 'Search';

	/**
	 * @var string
	 */
	protected $entity_controller = 'SearchController';

}

