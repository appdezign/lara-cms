<?php

return [

	'filter_by_user' => [
		'min_user_level' => 100,
		'entities' => [],
	],

	'manual' => [
		'online' => [
			'url' => 'https://userguide.laracms.nl/'
		],
	],

	'page' => [
		'cgroups_with_lead' => [
			'email',
		],
		'templates_with_lead' => [
			'featured_left',
			'featured_left_responsive',
			'featured_right',
			'featured_right_responsive',
		],
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

	// Laravel 11
	'defaultColumns' => [
		'id'              => [
			'name'     => 'id',
			'type'     => 'int',
			'validate' => false,
		],
		'user_id'         => [
			'name'     => 'user_id',
			'type'     => 'int',
			'validate' => false,
		],
		'language'        => [
			'name'     => 'language',
			'type'     => 'varchar',
			'validate' => false,
		],
		'language_parent' => [
			'name'     => 'language_parent',
			'type'     => 'int',
			'validate' => false,
		],
		'title'           => [
			'name'     => 'title',
			'type'     => 'varchar',
			'validate' => true,
		],
		'slug'            => [
			'name'     => 'slug',
			'type'     => 'varchar',
			'validate' => false,
		],

		'created_at' => [
			'name'     => 'created_at',
			'type'     => 'timestamp',
			'validate' => false,
		],
		'updated_at' => [
			'name'     => 'updated_at',
			'type'     => 'timestamp',
			'validate' => false,
		],
		'deleted_at' => [
			'name'     => 'deleted_at',
			'type'     => 'timestamp',
			'validate' => false,
		],

		'locked_at' => [
			'name'     => 'locked_at',
			'type'     => 'timestamp',
			'validate' => false,
		],
		'locked_by' => [
			'name'     => 'locked_by',
			'type'     => 'int',
			'validate' => false,
		],
	],

	// Laravel 11
	'optionalColumns' => [
		'slug_lock'       => [
			'name'     => 'slug_lock',
			'type'     => 'tinyint',
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
			'type'     => 'tinyint',
			'validate' => true,
		],
		'publish_hide'    => [
			'name'     => 'publish_hide',
			'type'     => 'tinyint',
			'validate' => true,
		],
		'publish_from'    => [
			'name'     => 'publish_from',
			'type'     => 'timestamp',
			'validate' => true,
		],
		'publish_to'      => [
			'name'     => 'publish_to',
			'type'     => 'timestamp',
			'validate' => false,
		],
		'show_in_app'     => [
			'name'     => 'show_in_app',
			'type'     => 'tinyint',
			'validate' => false,
		],
		'seo_focus'       => [
			'name'     => 'seo_focus',
			'type'     => 'varchar',
			'validate' => true,
		],
		'seo_title'       => [
			'name'     => 'seo_title',
			'type'     => 'varchar',
			'validate' => true,
		],
		'seo_description' => [
			'name'     => 'seo_description',
			'type'     => 'varchar',
			'validate' => true,
		],
		'seo_keywords'    => [
			'name'     => 'seo_keywords',
			'type'     => 'varchar',
			'validate' => true,
		],
		'cgroup'          => [
			'name'     => 'cgroup',
			'type'     => 'varchar',
			'validate' => false,
		],
		'position'        => [
			'name'     => 'position',
			'type'     => 'int',
			'validate' => false,
		],
		'ipaddress'       => [
			'name'     => 'ipaddress',
			'type'     => 'varchar',
			'validate' => false,
		],

	],

	'userProfile' => [
		'dark_mode' => [
			'name'     => 'dark_mode',
			'type'     => 'tinyint',
			'default'  => 0,
			'readonly' => false,
		],
	],

	// Laravel 11
	'fieldTypes' => [
		'string'        => [
			'name'  => 'string',
			'type'  => 'varchar',
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
			'type'  => 'varchar',
			'check' => true,
		],
		'datetime'      => [
			'name'  => 'datetime',
			'type'  => 'timestamp',
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
			'type'  => 'int',
			'check' => true,
		],
		'intunsigned'   => [
			'name'  => 'intunsigned',
			'type'  => 'int',
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
			'type'  => 'varchar',
			'check' => true,
		],
		'boolean'       => [
			'name'  => 'boolean',
			'type'  => 'tinyint',
			'check' => true,
		],
		'yesno'         => [
			'name'  => 'yesno',
			'type'  => 'tinyint',
			'check' => true,
		],
		'video'         => [
			'name'  => 'video',
			'type'  => 'varchar',
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
		'color'         => [
			'name'  => 'color',
			'type'  => 'varchar',
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
			'type'  => 'varchar',
			'check' => true,
		],
		'text'    => [
			'name'  => 'text',
			'type'  => 'text',
			'check' => true,
		],
		'email'   => [
			'name'  => 'email',
			'type'  => 'varchar',
			'check' => true,
		],
		'integer' => [
			'name'  => 'integer',
			'type'  => 'int',
			'check' => true,
		],
		'date'    => [
			'name'  => 'date',
			'type'  => 'date',
			'check' => true,
		],
		'yesno'   => [
			'name'  => 'yesno',
			'type'  => 'tinyint',
			'check' => true,
		],
		'boolean' => [
			'name'  => 'boolean',
			'type'  => 'tinyint',
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