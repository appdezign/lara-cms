<?php

namespace Lara\Common\Models;

use Lara\Common\Models\Entity;

use Carbon\Carbon;

class Form extends Entity
{

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

}
