<?php

// Uploads path
$upload = wp_upload_dir();

// TotalPoll environment
return apply_filters(
	'totalpoll/filters/environment',
	[
		'id'             => 'totalpoll',
		'name'           => 'TotalPoll',
		'version'        => '4.12.0',
		'source'         => 'totalsuite.net',
		'versions'       => [
			'wp'    => $GLOBALS['wp_version'],
			'php'   => PHP_VERSION,
			'mysql' => $GLOBALS['wpdb']->db_version(),
		],
		'textdomain'     => 'totalpoll',
		'domain'         => empty( $_SERVER['SERVER_NAME'] ) ? 'localhost' : $_SERVER['SERVER_NAME'],
		'root'           => TOTALPOLL_ROOT,
		'path'           => wp_normalize_path( plugin_dir_path( TOTALPOLL_ROOT ) ),
		'url'            => plugin_dir_url( TOTALPOLL_ROOT ),
		'basename'       => plugin_basename( TOTALPOLL_ROOT ),
		'rest-namespace' => 'totalpoll/v4',
		'namespace'      => 'TotalPoll',
		'dirname'        => dirname( plugin_basename( TOTALPOLL_ROOT ) ),
		'cache'          => [
			'path' => WP_CONTENT_DIR . '/cache/totalpoll/',
			'url'  => content_url( '/cache/totalpoll/' ),
		],
		'exports'        => [
			'path' => WP_CONTENT_DIR . '/exports/totalpoll/',
			'url'  => content_url( '/exports/totalpoll/' ),
		],
		'slug'           => 'totalpoll',
		'prefix'         => 'totalpoll_',
		'short-prefix'   => 'tp_',
		'options-key'    => 'totalpoll_options_repository',
		'tracking-key'   => 'totalpoll_tracking',
		'onboarding-key' => 'totalpoll_onboarding',
		'db'             => [
			'version'    => '400',
			'option-key' => 'totalpoll_db_version',
			'tables'     => [
				'log'     => function () {
					return $GLOBALS['wpdb']->prefix . 'totalpoll_log';
				},
				'votes'   => function () {
					return $GLOBALS['wpdb']->prefix . 'totalpoll_votes';
				},
				'entries' => function () {
					return $GLOBALS['wpdb']->prefix . 'totalpoll_entries';
				},
			],
			'prefix'     => function () {
				return (string) $GLOBALS['wpdb']->prefix;
			},
			'charset'    => (string) $GLOBALS['wpdb']->get_charset_collate(),
		],
		'api'            => [
			'update'             => 'https://totalsuite.net/api/v2/products/totalpoll/update/',
			'store'              => 'https://totalsuite.net/api/v2/products/totalpoll/store/{{license}}/',
			'activation'         => 'https://totalsuite.net/api/v2/products/totalpoll/activate/',
			'check-access-token' => 'https://totalsuite.net/api/v2/users/check/',
			'blogFeed'           => 'https://totalsuite.net/wp-json/wp/v2/blog_article',
			'tracking'           => [
				'nps'         => 'https://collect.totalsuite.net/nps',
				'uninstall'   => 'https://collect.totalsuite.net/uninstall',
				'environment' => 'https://collect.totalsuite.net/env',
				'events'      => 'https://collect.totalsuite.net/event',
				'log'         => 'https://collect.totalsuite.net/log',
				'onboarding'  => 'https://collect.totalsuite.net/onboarding',
			],
		],
		'links'          => [
			'activation'     => admin_url( 'edit.php?post_type=poll&page=dashboard&tab=dashboard>activation' ),
			'my-account'     => admin_url( 'edit.php?post_type=poll&page=dashboard&tab=dashboard>my-account' ),
			'upgrade-to-pro' => admin_url( 'edit.php?post_type=poll&page=upgrade-to-pro' ),
			'signin-account' => 'https://totalsuite.net/ext/auth/signin',
			'changelog'      => 'https://totalsuite.net/product/totalpoll/changelog/#version-4.12.0',
			'website'        => 'https://totalsuite.net/product/totalpoll/',
			'support'        => 'https://totalsuite.net/support/?utm_source=in-app&utm_medium=support-box&utm_campaign=totalpoll',
			'customization'  => 'https://totalsuite.net/services/new/?department=25',
			'translate'      => 'https://totalsuite.net/translate/',
			'search'         => 'https://totalsuite.net/documentation/totalpoll/',
			'forums'         => 'https://wordpress.org/support/plugin/totalpoll-lite/',
			'totalsuite'     => 'https://totalsuite.net/',
			'subscribe'      => 'https://subscribe.misqtech.com/totalsuite/',
			'twitter'        => 'https://twitter.com/totalsuite',
			'facebook'       => 'https://fb.me/totalsuite',
			'youtube'        => 'https://www.youtube.com/channel/UCp44ZQMpZhBB6chpKWoeEOw/',
			'totalrating'    => 'https://totalsuite.net/products/totalrating/',
			'totalcontest'   => 'https://totalsuite.net/products/totalcontest/',
			'totalsurvey'    => 'https://totalsuite.net/products/totalsurvey/',
		],
		'requirements'   => [
			'wp'    => '4.8',
			'php'   => '5.5',
			'mysql' => '5.5',
		],
		'recommended'    => [
			'wp'    => '5.0',
			'php'   => '7.0',
			'mysql' => '8.0',
		],
		'autoload'       => [
			'loader' => dirname( TOTALPOLL_ROOT ) . '/vendor/autoload.php',
			'psr4'   => [
				"TotalPoll\\Modules\\Templates\\"  => [
					trailingslashit( $upload['basedir'] . '/totalpoll/templates/' ),
					dirname( TOTALPOLL_ROOT ) . '/modules/templates',
				],
				"TotalPoll\\Modules\\Extensions\\" => [
					trailingslashit( $upload['basedir'] . '/totalpoll/extensions/' ),
					dirname( TOTALPOLL_ROOT ) . '/modules/extensions',
				],
			],
		],
	]
);
