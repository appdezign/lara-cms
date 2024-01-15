<?php

return [

	'lara_maj_ver'    => '8',
	'lara_db_version' => '8.1.11',

	'lara_db_builds' => [
		'6.0.1',
		'6.0.2',
		'6.0.3',
		'6.0.4',
		'6.2.1',
		'6.2.5',
		'6.2.8',
		'6.2.16',
		'6.2.20',
		'6.2.22',
		'6.2.23',
		'6.3.1',
		'7.0.1',
		'7.1.0',
		'7.1.4',
		'7.5.11',
		'7.5.36',
		'7.5.46',
		'8.1.11',
	],

	'auth' => [
		'has_front_auth'     => env('LARA_AUTH_HAS_FRONT_AUTH', false),
		'can_register'       => env('LARA_AUTH_CAN_REGISTER', false),
		'can_reset_password' => env('LARA_AUTH_CAN_RESET_PASSWORD', false),
		'has_front_profile'  => env('LARA_AUTH_HAS_FRONT_PROFILE', false),
	],

	'lara_title'       => 'Lara 7',
	'lara_title_short' => 'L7',

	'eve_path'  => base_path('laracms/app'),
	'lara_path' => base_path('laracms/core'),

	'debug'     => false,

	'needs_setup' => env('LARA_NEEDS_SETUP', false),

	'scheduler_id' => env('LARA_SCHEDULER_ID', 0),

	'admin_company_name'  => 'Firmaq Beheer',
	'admin_company_email' => 'beheer@firmaq.nl',

	'rss_feed_limit' => 20,

	'has_seo_advanced' => env('LARA_SEO_ADVANCED', false),

	'has_cron_job' => env('APP_TASK', false),

	'has_frontend' => env('LARA_HAS_FRONTEND', true),

	'has_remote_sync' => env('LARA_HAS_REMOTE_SYNC', false),

	// BS5
	'client_theme' => env('LARA_CLIENT_THEME', 'demo'),

	'lara_remember_login' => env('LARA_REMEMBER_LOGIN', false),

	'frontapp_chat_id'             => env('FRONTAPP_CHAT_ID', null),
	'frontapp_verification_secret' => env('FRONTAPP_VERIFICATION_SECRET', null),

	'freshdesk_widget_id' => env('FRESHDESK_WIDGET_ID', null),

	'google_maps_api_key'      => env('GOOGLE_MAPS_API_KEY'),
	'google_translate_api_key' => env('GOOGLE_TRANSLATE_API_KEY'),

	'google_recaptcha_site_key'   => env('GOOGLE_RECAPTCHA_SITE_KEY'),
	'google_recaptcha_secret_key' => env('GOOGLE_RECAPTCHA_SECRET_KEY'),

	'adminprefix'       => 'admin',
	'builderprefix'     => 'builder',
	'dbprefix'          => 'lara',
	'default_perpage'   => 10,
	'clanguage_default' => 'nl',
	'dynamic_routes'    => true,

	'image_max_width' => 1920,

	'widget_object_limit' => 12,

	'clear_cache_on_save' => [
		'page',
		'block',
		'menu',
		'tag',
		'entity',
	],

	'httpcache_on_forms' => env('LARA_HTTPCACHE_ON_FORMS', false),
	'httpcache_ttl'      => env('LARA_HTTPCACHE_TTL', 3600),

	'upload_allowed_images'     => [
		'image/jpeg',
		'image/png',
	],
	'upload_allowed_files'      => [
		'application/pdf',
		'.doc',
		'.docx',
		'.xls',
		'.xlsx',
		'.ppt',
		'.pptx',
	],
	'upload_allowed_videofiles' => [
		'video/mp4',
	],

	/*
	'upload_allowed_videofiles' => [
		'video/mp4',
		'.mkv',
		'.mov',
		'.avi',
		'video/MP2T',
		'video/MP2P',
		'application/mxf',
		'application/lxf',
		'application/gxf',
		'video/3gpp',
		'video/webm',
		'video/mpeg',
		'video/quicktime',
	],
	*/

	'web_video_formats' => [
		'video/mp4',
		'video/webm',
	],

	'upload_maxsize_image'     => 10,
	'upload_maxsize_file'      => 20,
	'upload_maxsize_videofile' => 50,

	'detect_language' => [
		'enabled'           => true,
		'languages_allowed' => [
			'nl',
			'fy',
		],

		'entity_fields' => [
			'contactform' => [
				'comment',
			],
		],

		'wordcount_threshold_min' => '5',
		'wordcount_threshold_max' => '500',

	],

	'detect_link_patterns' => [
		'http:',
		'https:',
	],

	'forms_anti_spam' => [
		'threshold'           => 100,
		'spam_score_link'     => 50,
		'spam_score_email'    => 50,
		'spam_score_language' => 100,
	],
];
