<?php

return [

	'manual' => [
		'online' => [
			'url' => 'https://userguide.laracms.nl/'
		],
	],

	'page' => [
		'cgroups_with_lead' => [
			'email',
		],
		'templates_with_lead' => [],
	],

	'settings' => [
		'company_team_photo_ratio' => [
			'1x1' => '1x1',
			'2x1' => '2x1',
			'3x1' => '3x1',
			'3x2' => '3x2',
			'4x1' => '4x1',
			'4x3' => '4x3',
			'16x9' => '16x9',
			'2x3' => '2x3',
			'3x4' => '3x4',
		],
	],

	'menu' => [
		'max_children' => '10',
		'disable_parent_change' => false,
	],

	'hero_sizes' => [
		0 => 'small',
		1 => 'medium',
		2 => 'large',
		3 => 'xl',
		4 => 'xxl',
		5 => 'xxxl',
	],

	'featured' => [
		'hide_in_gallery' => 1,
	],

	'taxonomy' => [
		'show_default_only' => false,
		'disable_root_parents' => true,
	],

	'analytics' => [
		'defaultDays'     => 14,
		'topPagesLimit'   => 10,
		'topRefLimit'     => 10,
		'topBrowserLimit' => 6,
		'timeout'         => 10,
	],

	'admin_menu_groups' => [
		'root'    => [
			'name' => 'Root',
			'slug' => '',
			'icon' => '',
			'chld' => [
				'dashboard' => [
					'name' => 'Dashboard',
					'slug' => 'dashboard',
					'icon' => 'fad fa-home-alt',
					'chld' => '',
				],
			],
		],
		'menu'    => [
			'name' => 'Menu',
			'slug' => '',
			'icon' => 'fad fa-th-list',
			'chld' => [],
		],
		'modules' => [
			'name' => 'Modules',
			'slug' => '',
			'icon' => 'fad fa-dice-d6',
			'chld' => [],
		],
		'blocks'  => [
			'name' => 'Blocks',
			'slug' => '',
			'icon' => 'fad fa-copy',
			'chld' => [],
		],
		'forms'   => [
			'name' => 'Forms',
			'slug' => '',
			'icon' => 'fad fa-poll-h',
			'chld' => [],
		],
		'tools'   => [
			'name' => 'Tools',
			'slug' => '',
			'icon' => 'fad fa-tools',
			'chld' => [],
		],
		'seo'   => [
			'name' => 'SEO',
			'slug' => '',
			'icon' => 'fad fa-chart-bar',
			'chld' => [],
		],
		'users'   => [
			'name' => 'Users',
			'slug' => '',
			'icon' => 'fad fa-user-circle',
			'chld' => [],
		],
	],

	'abilities' => [
		'view'   => 'view',
		'create' => 'create',
		'update' => 'update',
		'delete' => 'delete',
	],

	'defaultColumns' => [
		'id'              => [
			'name'     => 'id',
			'type'     => 'integer',
			'validate' => false,
		],
		'user_id'         => [
			'name'     => 'user_id',
			'type'     => 'integer',
			'validate' => false,
		],
		'language'        => [
			'name'     => 'language',
			'type'     => 'string',
			'validate' => false,
		],
		'language_parent' => [
			'name'     => 'language_parent',
			'type'     => 'integer',
			'validate' => false,
		],
		'title'           => [
			'name'     => 'title',
			'type'     => 'string',
			'validate' => true,
		],
		'slug'            => [
			'name'     => 'slug',
			'type'     => 'string',
			'validate' => false,
		],

		'created_at' => [
			'name'     => 'created_at',
			'type'     => 'datetime',
			'validate' => false,
		],
		'updated_at' => [
			'name'     => 'updated_at',
			'type'     => 'datetime',
			'validate' => false,
		],
		'deleted_at' => [
			'name'     => 'deleted_at',
			'type'     => 'datetime',
			'validate' => false,
		],

		'locked_at' => [
			'name'     => 'locked_at',
			'type'     => 'datetime',
			'validate' => false,
		],
		'locked_by' => [
			'name'     => 'locked_by',
			'type'     => 'integer',
			'validate' => false,
		],
	],

	'optionalColumns' => [
		'slug_lock'       => [
			'name'     => 'slug_lock',
			'type'     => 'boolean',
			'validate' => false,
		],
		'lead'            => [
			'name'     => 'lead',
			'type'     => 'text',
			'validate' => true,
		],
		'body'            => [
			'name'     => 'body',
			'type'     => 'text',
			'validate' => true,
		],
		'publish'         => [
			'name'     => 'publish',
			'type'     => 'boolean',
			'validate' => true,
		],
		'publish_hide'    => [
			'name'     => 'publish_hide',
			'type'     => 'boolean',
			'validate' => true,
		],
		'publish_from'    => [
			'name'     => 'publish_from',
			'type'     => 'datetime',
			'validate' => true,
		],
		'publish_to'      => [
			'name'     => 'publish_to',
			'type'     => 'datetime',
			'validate' => false,
		],
		'show_in_app'     => [
			'name'     => 'show_in_app',
			'type'     => 'boolean',
			'validate' => false,
		],
		'seo_focus'       => [
			'name'     => 'seo_focus',
			'type'     => 'string',
			'validate' => true,
		],
		'seo_title'       => [
			'name'     => 'seo_title',
			'type'     => 'string',
			'validate' => true,
		],
		'seo_description' => [
			'name'     => 'seo_description',
			'type'     => 'string',
			'validate' => true,
		],
		'seo_keywords'    => [
			'name'     => 'seo_keywords',
			'type'     => 'string',
			'validate' => true,
		],
		'cgroup'          => [
			'name'     => 'cgroup',
			'type'     => 'string',
			'validate' => false,
		],
		'position'        => [
			'name'     => 'position',
			'type'     => 'integer',
			'validate' => false,
		],
		'ipaddress'       => [
			'name'     => 'ipaddress',
			'type'     => 'string',
			'validate' => false,
		],

	],

	'userProfile' => [
		'dark_mode' => [
			'name'     => 'dark_mode',
			'type'     => 'boolean',
			'default'  => 0,
			'readonly' => false,
		],
	],

	'fieldTypes' => [
		'string'        => [
			'name'  => 'string',
			'type'  => 'string',
			'check' => true,
		],
		'text'          => [
			'name'  => 'text',
			'type'  => 'text',
			'check' => true,
		],
		'mcefull'       => [
			'name'  => 'mcefull',
			'type'  => 'text',
			'check' => true,
		],
		'mcemin'        => [
			'name'  => 'mcemin',
			'type'  => 'text',
			'check' => true,
		],
		'email'         => [
			'name'  => 'email',
			'type'  => 'string',
			'check' => true,
		],
		'datetime'      => [
			'name'  => 'datetime',
			'type'  => 'datetime',
			'check' => true,
		],
		'date'          => [
			'name'  => 'date',
			'type'  => 'date',
			'check' => true,
		],
		'time'          => [
			'name'  => 'time',
			'type'  => 'time',
			'check' => true,
		],
		'integer'       => [
			'name'  => 'integer',
			'type'  => 'integer',
			'check' => true,
		],
		'intunsigned'   => [
			'name'  => 'intunsigned',
			'type'  => 'integer',
			'check' => true,
		],
		'decimal101'    => [
			'name'  => 'decimal101',
			'type'  => 'decimal',
			'check' => true,
		],
		'decimal142'    => [
			'name'  => 'decimal142',
			'type'  => 'decimal',
			'check' => true,
		],
		'decimal164'    => [
			'name'  => 'decimal164',
			'type'  => 'decimal',
			'check' => true,
		],
		'decimal108'    => [
			'name'  => 'decimal108',
			'type'  => 'decimal',
			'check' => true,
		],
		'decimal118'    => [
			'name'  => 'decimal118',
			'type'  => 'decimal',
			'check' => true,
		],
		'geolocation'   => [
			'name'  => 'geolocation',
			'type'  => 'string',
			'check' => true,
		],
		'boolean'       => [
			'name'  => 'boolean',
			'type'  => 'boolean',
			'check' => true,
		],
		'yesno'         => [
			'name'  => 'yesno',
			'type'  => 'boolean',
			'check' => true,
		],
		'video'         => [
			'name'  => 'video',
			'type'  => 'string',
			'check' => true,
		],
		'selectone'     => [
			'name'  => 'selectone',
			'type'  => 'text',
			'check' => true,
		],
		'selectone2one' => [
			'name'  => 'selectone2one',
			'type'  => 'text',
			'check' => true,
		],
		'radio'     => [
			'name'  => 'radio',
			'type'  => 'text',
			'check' => true,
		],
		'color'         => [
			'name'  => 'color',
			'type'  => 'string',
			'check' => true,
		],
		'custom'        => [
			'name'  => 'custom',
			'type'  => 'text',
			'check' => true,
		],
	],

	'fieldFormTypes' => [
		'string'  => [
			'name'  => 'string',
			'type'  => 'string',
			'check' => true,
		],
		'text'    => [
			'name'  => 'text',
			'type'  => 'text',
			'check' => true,
		],
		'email'   => [
			'name'  => 'email',
			'type'  => 'string',
			'check' => true,
		],
		'integer' => [
			'name'  => 'integer',
			'type'  => 'integer',
			'check' => true,
		],
		'date'    => [
			'name'  => 'date',
			'type'  => 'date',
			'check' => true,
		],
		'yesno'   => [
			'name'  => 'yesno',
			'type'  => 'yesno',
			'check' => true,
		],
		'boolean' => [
			'name'  => 'boolean',
			'type'  => 'boolean',
			'check' => true,
		],
		'selectone' => [
			'name'  => 'selectone',
			'type'  => 'text',
			'check' => true,
		],
		'radio'     => [
			'name'  => 'radio',
			'type'  => 'text',
			'check' => true,
		],
	],

	'fieldHooks'      => [
		'after'   => 'after',
		'before'  => 'before',
		'between' => 'between',
	],
	'formFieldHooks'  => [
		'default' => 'default',
	],
	'fieldStates'     => [
		'enabled'   => 'always enabled',
		'enabledif' => 'enabled if',
		'hidden'    => 'hidden',
		'disabled'  => 'disabled',
	],
	'relationTypes'   => [
		'hasOne'  => 'hasOne',
		'hasMany' => 'hasMany',
	],

	/*
	|--------------------------------------------------------------------------
	| Entity View Types
	|--------------------------------------------------------------------------
	|
	| Types that start with an underscore can NOT have pagination!
	|
	*/
	'entityViewTypes' => [
		'_single' => 'Single',
		'list'    => 'List',
		'grid2'   => 'Grid 2',
		'grid3'   => 'Grid 3',
		'grid4'   => 'Grid 4',
	],

	'entityViewFormTypes' => [
		'_form' => 'Form',
	],

	'entityViewShowTags' => [
		'none'             => 'None',
		'_sortbytaxonomy'  => 'Sort by tags',
		'filterbytaxonomy' => 'Filter by tags',
	],

	'entityViewMethods' => [
		'index'  => 'index',
		'show'   => 'show',
		'custom' => 'custom',
	],

	'entityViewFormMethods' => [
		'form' => 'form',
	],

	'upload_disks' => [
		'disks'          => [
			'localdisk' => [
				'key'  => 'localdisk',
				'name' => 'Local Disk',
			],
			's3'        => [
				'key'  => 's3',
				'name' => 'AWS S3',
			],
		],
		'use_for_images' => [
			'localdisk',
		],
		'use_for_videos' => [
			'localdisk',
			's3',
		],
		'use_for_files'  => [
			'localdisk',
			's3',
		],
		'default'        => 'localdisk',
	],

	/*
	|--------------------------------------------------------------------------
	| Locked Models and Controller
	|--------------------------------------------------------------------------
	|
	| We can lock models and controllers,
	| so we can modify them manually,
	| and they will not be overwritten by the Builder
	|
	*/

	'locked_models' => [
		'page',
		'seo',
		'larawidget',
		'event',
	],

	'locked_entities' => [],

	'locked_admin_controllers' => [],

	'locked_front_controllers' => [],

	'exclude_files_from_purge' => [
		'.htaccess',
	],

	'languages_content' => [
		'can_purge' => false,
		'can_export' => false,
		'add_lang_to_content' => false,
	],

	'builder_custom_columns_max' => 50,

];