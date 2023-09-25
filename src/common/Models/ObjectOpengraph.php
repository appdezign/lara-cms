<?php

namespace Lara\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class ObjectOpengraph extends Model
{

	/**
	 * @var string
	 */
	protected $table = 'lara_object_opengraph';

	/**
	 * @var array
	 */
	protected $fillable = [
		'og_title',
		'og_description',
		'og_image',
	];

	// set table name
	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
	}

	/**
	 * @return MorphTo
	 */
	public function entity() {
		return $this->morphTo();
	}

}
