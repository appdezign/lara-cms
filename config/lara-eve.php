<?php

return [

	'use_tags_for_sorting' => [
		'service' => []
	],

	'override_front_entity_objects' => [
		'event' => [
			'sortfield'    => 'startdate',
			'sortorder'    => 'asc',
			'sortfield2nd' => 'starttime',
			'sortorder2nd' => 'asc',
			// 'paginate' => 10,
			// 'limit' => 25,
		]
	],

	'export_language_content' => [
		'page' => [],
		'blog' => [
			'source',
			'location',
		],
		'team' => [
			'role',
			'firstname',
			'middlename',
		],
		'event' => [],
		'location' => [],
		'portfolio' => [],
		'gallery' => [],
		'video' => [],
		'doc' => [],
		'testimonial' => [
			'role',
			'quoteshort',
		],
		'service' => [],
		'slider' => [
			'caption',
			'subtitle',
			'payoff',
			'urltitle',
			'urltext',
		],
		'larawidget' => [],
		'cta' => [],
	],

];
