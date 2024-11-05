<?php

return [

	'lara_front_bootstrap_version' => env('LARA_FRONT_BS_VERSION', 5),

	'asset_theme_path' => 'assets/themes/',

	'widget_cache_time' => '1440',

	'uploads' => [
		'no_checks' => true,
		'max_per_day' => 2,
	],

	'animation' => [
		'class' => 'fade-up',
		'offset' => 80,
		'duration' => '800',
		'delay' => 0,
		'easing' => 'ease-out-quad',
	],

	'entity_search_fields' => [
		'product' => [
			'title',
			'sku',
		],
	],

	'nav_history_max' => 10,

	'front_menu' => [

		'show_home_in_menu' => true,

		'front_menu_types' => [
			'page'   => 'Page',
			'parent' => 'Folder',
			'entity' => 'Module',
			'form'   => 'Form',
			'url'   => 'URL',
		],

	],

	'partials' => [

		'pagetitle'      => [
			'partial'  => 'pagetitle',
			'action'   => null,
		],
		'header'      => [
			'partial'  => 'header',
			'action'   => null,
		],
		'batch'       => [
			'partial'  => 'batch',
			'action'   => null,
		],
		'status'      => [
			'partial'  => 'status',
			'action'   => null,
		],
		'content'     => [
			'partial'  => 'content',
			'action'   => null,
		],
		'seo'         => [
			'partial'  => 'seo',
			'action'   => null,
		],
		'opengraph'         => [
			'partial'  => 'opengraph',
			'action'   => null,
		],
		'groups'      => [
			'partial'  => 'groups',
			'action'   => null,
		],
		'author'      => [
			'partial'  => 'author',
			'action'   => null,
		],
		'sync'      => [
			'partial'  => 'sync',
			'action'   => null,
		],
		'footer'      => [
			'partial'  => 'footer',
			'action'   => null,
		],
		'fields'  => [
			'partial'  => 'fields',
			'action'   => 'fields',
		],
		'relations'    => [
			'partial'  => 'relations',
			'action'   => 'fields',
		],
	],

	'mail' => [
		'bgcolor' => '#ddd',
		'textcolor' => '#000',
		'fontfam' => 'Verdana, sans-serif',
		'fontsize' => '12px',
		'headerBgColor' => '#08c',
		'headerColor' => '#fff',
		'footerBgColor' => '#08c',
		'footerColor' => '#fff',
	],

	'lara_api_token' => env('LARA_API_TOKEN'),

	'lara_api_object_limit' => env('LARA_API_OBJECT_LIMIT', 20),

	'lara_api_default_columns' => [
		'id',
		'title',
		'created_at',
		'updated_at',
		'publish_from',
	],

	'loginwidget' => [
		'loginurl' => '/',
	],

	'shortcode' => [
		'bootstrap_gutter_class' => 'gx-48',
		'bootstrap_breakpoint' => 'md',
	],

	'skip_direct_download_related_docs' => true,

];