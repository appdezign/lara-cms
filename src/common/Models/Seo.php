<?php

namespace Lara\Common\Models;

use Lara\Common\Models\EntityBaseModel;

use Carbon\Carbon;

class Seo extends EntityBaseModel
{

	/**
	 * @var string
	 */
	protected $table = 'lara_content_pages';

	/**
	 * @var string[]
	 */
	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
		'deleted_at',
	];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

}
