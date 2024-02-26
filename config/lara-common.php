<?php

return [

	'auto_login' => [
		'77.164.138.35',
		'46.44.173.133',
	],

	'database' => [

		'db_connection' => env('DB_CONNECTION', null),
		'db_database'   => env('DB_DATABASE'),

		'db_connection_src' => env('DB_CONNECTION_SRC', null),
		'db_database_src'   => env('DB_DATABASE_SRC', null),

		'prefix' => 'lara_',

		'entity' => [
			'block_prefix'   => 'lara_blocks_',
			'content_prefix' => 'lara_content_',
			'entity_prefix'  => 'lara_content_',
			'page_prefix'    => 'lara_content_',
			'form_prefix'    => 'lara_form_',
		],

		'auth' => [
			'users'           => 'lara_auth_users',
			'profiles'        => 'lara_auth_users_profiles',
			'password_resets' => 'lara_auth_password_resets',
			'roles'           => 'lara_auth_roles',
			'has_roles'       => 'lara_auth_has_roles',
			'abilities'       => 'lara_auth_abilities',
			'has_abilities'   => 'lara_auth_has_abilities',
		],

		'menu' => [
			'menus'     => 'lara_menu_menus',
			'menuitems' => 'lara_menu_menu_items',
			'redirects' => 'lara_menu_redirects',
		],

		'object' => [
			'images'     => 'lara_object_images',
			'videos'     => 'lara_object_videos',
			'videofiles' => 'lara_object_videofiles',
			'files'      => 'lara_object_files',
			'layout'     => 'lara_object_layout',
			'tags'       => 'lara_object_tags',
			'taxonomy'   => 'lara_object_taxonomies',
			'taggables'  => 'lara_object_taggables',
			'pageables'  => 'lara_object_pageables',
			'related'    => 'lara_object_related',
			'sync'       => 'lara_object_sync',
			'seo'        => 'lara_object_seo',
			'opengraph'  => 'lara_object_opengraph',
		],

		'sys' => [
			'dashboard'    => 'lara_sys_dashboard',
			'jobs'         => 'lara_sys_jobs',
			'languages'    => 'lara_sys_languages',
			'settings'     => 'lara_sys_settings',
			'uploads'      => 'lara_sys_uploads',
			'translations' => 'lara_sys_translations',
			'blacklist'    => 'lara_sys_blacklist',
			'headertags'    => 'lara_sys_headertags',
			'entitywidgets' => 'lara_sys_entitywidgets',
		],

		'ent' => [
			'entities'              => 'lara_ent_entities',
			'entitycolumns'         => 'lara_ent_entity_columns',
			'entitycustomcolumns'   => 'lara_ent_entity_custom_columns',
			'entitygroups'          => 'lara_ent_entity_groups',
			'entityobjectrelations' => 'lara_ent_entity_object_relations',
			'entitypanels'          => 'lara_ent_entity_panels',
			'entityrelations'       => 'lara_ent_entity_relations',
			'entityviews'           => 'lara_ent_entity_views',
		],

	],

	'routes' => [

		'has_alias' => [
			'page' => [
				'module' => [
					'aliaskey'    => 'module',
					'add_to_menu' => false,
					'is_group'    => true,
				],
			],

		],

		'is_alias' => [
			'module' => 'page',
		],
	],

	'translations' => [

		'modules' => [
			'lara-admin'  => [
				'key'     => 'lara-admin',
				'default' => 'default',
			],
			'lara-common' => [
				'key'     => 'lara-common',
				'default' => 'auth',
			],
			'lara-front'  => [
				'key'     => 'lara-front',
				'default' => 'default',
			],
			'lara-eve'    => [
				'key'     => 'lara-eve',
				'default' => 'default',
			],
		],
	],

	'import_on_update' => [

		'dirs'  => [
			'example' => [
				'folder' => null,
				'src'    => null,
				'dest'   => null,
				'backup' => true,
			],
		],
		'files' => [
			'logo' => [
				'file'   => null,
				'src'    => null,
				'dest'   => null,
				'backup' => false,
			],
		],

	],
];
