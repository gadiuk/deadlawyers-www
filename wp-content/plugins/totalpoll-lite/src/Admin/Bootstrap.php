<?php

namespace TotalPoll\Admin;

use TotalPollVendors\TotalCore\Application;
use TotalPollVendors\TotalCore\Contracts\Foundation\Environment;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Strings;


/**
 * Class Bootstrap
 *
 * @package TotalPoll\Admin
 */
class Bootstrap {
	/**
	 * @var array $pages
	 */
	public $pages = [];
	/**
	 * @var string|null $requestedPage
	 */
	public $requestedPage = null;
	/**
	 * @var null|Application $currentPage
	 */
	public $currentPage = null;
	/**
	 * @var array $env
	 */
	public $env = [];

	/**
	 * @var \TotalPollVendors\TotalCore\Http\Request
	 */
	public $request = null;

	/**
	 * @var array
	 */
	protected $messages = [];

	/**
	 * @var array
	 */
	protected $onboarding = [];

	/**
	 * Bootstrap constructor.
	 *
	 * @param  Request  $request
	 * @param  Environment  $env
	 */
	public function __construct( Request $request, Environment $env ) {
		$this->messages = apply_filters( 'totalpoll/filters/admin/messages', [
			'reset-success' => [ esc_html__( 'Poll has been reset', 'totalpoll' ), 'updated' ],
			'reset-error'   => [ esc_html__( 'Poll reset failed', 'totalpoll' ), 'error' ],
		] );

		$this->onboarding = get_option( 'totalpoll_onboarding', [ 'status' => 'init' ] );

		// TotalPoll Request
		$this->request = $request;

		$this->env = $env;

		$this->pages = [];

		// TotalPoll Pages

		$this->pages = array_merge( $this->pages, [
			'dashboard'      => [
				'title'      => esc_html__( 'Dashboard', 'totalpoll' ),
				'name'       => esc_html__( 'Dashboard', 'totalpoll' ),
				'capability' => 'manage_options',
			],
			'entries'        => [
				'title'      => esc_html__( 'Entries', 'totalpoll' ),
				'name'       => esc_html__( 'Entries', 'totalpoll' ),
				'capability' => 'edit_polls',
			],
			'insights'       => [
				'title'      => esc_html__( 'Insights', 'totalpoll' ),
				'name'       => esc_html__( 'Insights', 'totalpoll' ),
				'capability' => 'edit_polls',
			],
			'log'            => [
				'title'      => esc_html__( 'Log', 'totalpoll' ),
				'name'       => esc_html__( 'Logs', 'totalpoll' ),
				'capability' => 'manage_options',
			],
			'templates'      => [
				'title'      => esc_html__( 'Templates', 'totalpoll' ),
				'name'       => esc_html__( 'Templates', 'totalpoll' ),
				'capability' => 'manage_options',
			],
			'extensions'     => [
				'title'      => esc_html__( 'Extensions', 'totalpoll' ),
				'name'       => esc_html__( 'Extensions', 'totalpoll' ),
				'capability' => 'manage_options',
			],
			'options'        => [
				'title'      => esc_html__( 'Options', 'totalpoll' ),
				'name'       => esc_html__( 'Options', 'totalpoll' ),
				'capability' => 'manage_options',
			],
			
			'upgrade-to-pro' => [
				'title'      => esc_html__( 'Upgrade to Pro', 'totalpoll' ),
				'name'       => esc_html__( 'Upgrade to Pro', 'totalpoll' ),
				'capability' => 'manage_options',
			],
			
		] );

		if ( ! defined( 'TP_PRESET_CPT_NAME' ) ) {
			$this->pages =
				array_slice( $this->pages, 0, 1 ) +
				[
					'presets' => [
						'title'      => esc_html__( 'Presets', 'totalpoll' ),
						'name'       => esc_html__( 'Presets', 'totalpoll' ),
						'capability' => 'manage_options',
					],
				] + array_slice( $this->pages, 1, count( $this->pages ) - 1 );
		}

		if ( Arrays::getDotNotation( $this->onboarding,
		                             'status',
		                             'init' ) === 'init' && current_user_can( 'manage_options' ) ) {
			$this->pages['onboarding'] = [
				'title'      => esc_html__( 'Onboarding', 'totalpoll' ),
				'name'       => esc_html__( 'Onboarding', 'totalpoll' ),
				'capability' => 'manage_options',
			];
		}


		// Requested page
		$this->requestedPage = $request->request( 'page' );

		// Hook into WordPress menu and screen
		add_action( 'current_screen', [ $this, 'screen' ] );
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_filter( 'admin_body_class', [ $this, 'directionClass' ] );

		// Display the page, if present
		if ( $GLOBALS['pagenow'] === 'edit.php' && $request->request( 'post_type' ) === TP_POLL_CPT_NAME ):
			if ( array_key_exists( $this->requestedPage, $this->pages ) ):
				$this->currentPage = TotalPoll( "admin.pages.{$this->requestedPage}" );
			endif;
		endif;

		// Notice
		add_action( 'admin_notices', [ $this, 'showMessages' ] );
		// Cleanup
		add_action( 'before_delete_post', [ $this, 'pollCleanup' ] );
		// Poll reset
		add_action( 'admin_action_reset_poll', [ $this, 'resetPoll' ] );

		/**
		 * Fires when admin is bootstrapped.
		 *
		 * @since 4.0.0
		 * @order 10
		 */
		do_action( 'totalpoll/actions/bootstrap-admin' );
	}

	/**
	 * Register admin assets.
	 */
	public function registerAssets() {
		// Add a dynamic part to assets URL when debugging to prevent cache.
		$assetsVersion = WP_DEBUG ? time() : $this->env['version'];
		$baseUrl       = $this->env['url'];

		// ------------------------------
		// Vendor
		// ------------------------------
		wp_register_script( 'angular', "{$baseUrl}assets/dist/scripts/vendor/angular.min.js", $assetsVersion );
		wp_register_script( 'angular-resource',
		                    "{$baseUrl}assets/dist/scripts/vendor/angular-resource.min.js",
		                    [ 'angular' ],
		                    $assetsVersion );
		wp_register_script( 'angular-file-input',
		                    "{$baseUrl}assets/dist/scripts/vendor/ng-file-input.min.js",
		                    [ 'angular' ],
		                    $assetsVersion );
		wp_register_script( 'angular-dnd-lists',
		                    "{$baseUrl}assets/dist/scripts/vendor/angular-drag-and-drop-lists.min.js",
		                    [ 'angular' ],
		                    $assetsVersion );
		wp_register_script( 'jquery-datetimepicker',
		                    "{$baseUrl}assets/dist/scripts/vendor/jquery.datetimepicker.full.min.js",
		                    [ 'jquery' ],
		                    $assetsVersion );
		wp_register_script( 'platform.js',
		                    "{$baseUrl}assets/dist/scripts/vendor/platform.js",
		                    [ 'angular' ],
		                    $assetsVersion );
		wp_register_script( 'chart.js',
		                    "{$baseUrl}assets/dist/scripts/vendor/Chart.min.js",
		                    [ 'angular' ],
		                    $assetsVersion );
		wp_register_style( 'jquery-datetimepicker',
		                   "{$baseUrl}assets/dist/styles/vendor/datetimepicker.css",
		                   [],
		                   $assetsVersion );
		wp_add_inline_script( 'angular',
		                      sprintf( "var totalpollAjaxURL = '%s'",
		                               esc_js( wp_nonce_url( admin_url( 'admin-ajax.php' ), 'totalpoll' ) ) ) );
		/**
		 * @asset-style totalpoll-admin-totalcore
		 */
		wp_register_style(
			'totalpoll-admin-totalcore',
			"{$baseUrl}assets/dist/styles/admin-totalcore.css",
			[],
			$assetsVersion
		);

		wp_register_script(
			'totalpoll-admin-onboarding',
			"{$baseUrl}assets/dist/scripts/onboarding.js",
			[ 'jquery', 'wp-util', 'angular', 'angular-resource' ],
			$assetsVersion
		);

		// ------------------------------
		// Dashboard
		// ------------------------------
		/**
		 * @asset-script totalpoll-admin-dashboard
		 */
		wp_register_script(
			'totalpoll-admin-dashboard',
			"{$baseUrl}assets/dist/scripts/dashboard.js",
			[ 'angular', 'angular-resource' ],
			$assetsVersion
		);
		/**
		 * @asset-style totalpoll-admin-dashboard
		 */
		wp_register_style(
			'totalpoll-admin-dashboard',
			"{$baseUrl}assets/dist/styles/admin-dashboard.css",
			[ 'totalpoll-admin-totalcore' ],
			$assetsVersion
		);

		// ------------------------------
		// Editor
		// ------------------------------
		/**
		 * @asset-script totalpoll-admin-poll-editor
		 */
		wp_register_script(
			'totalpoll-admin-poll-editor',
			"{$baseUrl}assets/dist/scripts/poll-editor.js",
			[
				'chart.js',
				'wp-color-picker',
				'jquery-datetimepicker',
				'media-views',
				'angular',
				'angular-resource',
				'angular-dnd-lists',
			],
			$assetsVersion
		);
		/**
		 * @asset-style totalpoll-admin-poll-editor
		 */
		wp_register_style(
			'totalpoll-admin-poll-editor',
			"{$baseUrl}assets/dist/styles/admin-poll-editor.css",
			[ 'wp-color-picker', 'jquery-datetimepicker', 'totalpoll-admin-totalcore' ],
			$assetsVersion
		);

		// ------------------------------
		// Polls listing
		// ------------------------------
		/**
		 * @asset-style totalpoll-admin-listing
		 */
		wp_register_style(
			'totalpoll-admin-poll-listing',
			"{$baseUrl}assets/dist/styles/admin-poll-listing.css",
			[],
			$assetsVersion
		);

		// ------------------------------
		// Entries
		// ------------------------------
		/**
		 * @asset-script totalpoll-admin-entries
		 */
		wp_register_script(
			'totalpoll-admin-entries',
			"{$baseUrl}assets/dist/scripts/entries.js",
			[ 'jquery-datetimepicker', 'angular', 'angular-resource', 'platform.js' ],
			$assetsVersion
		);
		/**
		 * @asset-style totalpoll-admin-entries
		 */
		wp_register_style(
			'totalpoll-admin-entries',
			"{$baseUrl}assets/dist/styles/admin-entries.css",
			[ 'jquery-datetimepicker', 'totalpoll-admin-totalcore' ],
			$assetsVersion
		);

		// ------------------------------
		// Insights
		// ------------------------------
		/**
		 * @asset-script totalpoll-admin-insights
		 */
		wp_register_script(
			'totalpoll-admin-insights',
			"{$baseUrl}assets/dist/scripts/insights.js",
			[ 'jquery-datetimepicker', 'angular', 'angular-resource', 'platform.js', 'chart.js' ],
			$assetsVersion
		);
		/**
		 * @asset-style totalpoll-admin-insights
		 */
		wp_register_style(
			'totalpoll-admin-insights',
			"{$baseUrl}assets/dist/styles/admin-insights.css",
			[ 'jquery-datetimepicker', 'totalpoll-admin-totalcore' ],
			$assetsVersion
		);

		// ------------------------------
		// Log
		// ------------------------------
		/**
		 * @asset-script totalpoll-admin-log
		 */
		wp_register_script( 'totalpoll-admin-log',
		                    "{$baseUrl}assets/dist/scripts/log.js",
		                    [ 'jquery-datetimepicker', 'angular', 'angular-resource', 'platform.js' ],
		                    $assetsVersion
		);
		/**
		 * @asset-style totalpoll-admin-log
		 */
		wp_register_style(
			'totalpoll-admin-log',
			"{$baseUrl}assets/dist/styles/admin-log.css",
			[ 'jquery-datetimepicker', 'totalpoll-admin-totalcore' ],
			$assetsVersion
		);

		// ------------------------------
		// Modules
		// ------------------------------
		/**
		 * @asset-script totalpoll-admin-modules
		 */
		wp_register_script(
			'totalpoll-admin-modules',
			"{$baseUrl}assets/dist/scripts/modules.js",
			[ 'angular', 'angular-resource', 'angular-file-input' ],
			$assetsVersion
		);
		/**
		 * @asset-style totalpoll-admin-modules
		 */
		wp_register_style(
			'totalpoll-admin-modules',
			"{$baseUrl}assets/dist/styles/admin-modules.css",
			[ 'totalpoll-admin-totalcore' ],
			$assetsVersion
		);

		// ------------------------------
		// Options
		// ------------------------------
		/**
		 * @asset-script totalpoll-admin-options
		 */
		wp_register_script(
			'totalpoll-admin-options',
			"{$baseUrl}assets/dist/scripts/options.js",
			[ 'angular', 'angular-resource' ],
			$assetsVersion
		);
		/**
		 * @asset-script totalpoll-admin-options
		 */
		wp_register_style(
			'totalpoll-admin-options',
			"{$baseUrl}assets/dist/styles/admin-options.css",
			[ 'totalpoll-admin-totalcore' ],
			$assetsVersion
		);

		
		// ------------------------------
		// Upgrade to pro
		// ------------------------------
		/**
		 * @asset-script totalpoll-admin-options
		 */
		wp_register_style(
			'totalpoll-admin-upgrade-to-pro',
			"{$baseUrl}assets/dist/styles/admin-upgrade-to-pro.css",
			[ 'totalpoll-admin-totalcore' ],
			$assetsVersion
		);
		

		do_action( 'totalpoll/actions/admin/assets', $baseUrl, $assetsVersion );
	}

	/**
	 * Poll screens.
	 */
	public function screen() {
		global $current_screen;

		$totalPollPostTypes = apply_filters( 'totalpoll/filters/posttypes', [ TP_POLL_CPT_NAME ] );


		$isTotalPoll = in_array( $current_screen->post_type, $totalPollPostTypes );

		$isEditor       = $GLOBALS['current_screen']->base === 'post' && $isTotalPoll;
		$isPollsListing = $GLOBALS['current_screen']->base === 'edit' && $isTotalPoll;

		if ( $isTotalPoll ):
			// Register assets
			$this->registerAssets();

			add_filter( 'admin_footer_text', [ $this, 'footerText' ] );
			add_filter( 'update_footer', [ $this, 'footerVersion' ] );
		endif;

		if ( $isEditor ):
			if ( $current_screen->post_type === TP_POLL_CPT_NAME ) {
				TotalPoll( 'admin.poll.editor' );
			} else {
				do_action( 'totalpoll/actions/admin/screen/editor', $current_screen->post_type );
			}
		elseif ( $isPollsListing ):
			if ( $current_screen->post_type === TP_POLL_CPT_NAME ) {
				TotalPoll( 'admin.poll.listing' );
			} else {
				do_action( 'totalpoll/actions/admin/screen/listing', $current_screen->post_type );
			}
		endif;
	}

	/**
	 * Admin menu.
	 *
	 * @action-callback admin_menu
	 */
	public function menu() {
		$slug = 'edit.php?post_type=' . TP_POLL_CPT_NAME;

		foreach ( $this->pages as $pageSlug => $page ):
			add_submenu_page(
				$slug,
				$page['title'],
				$page['name'],
				empty( $page['capability'] ) ? 'manage_options' : $page['capability'],
				$pageSlug,
				[ $this, 'page' ]
			);
		endforeach;

		add_filter( 'parent_file', function ( $file ) use ( $slug ) {
			if ( isset( $GLOBALS['submenu'][ $slug ] ) ):
				foreach ( $GLOBALS['submenu'][ $slug ] as $itemIndex => $item ):
					if ( $item[2] === 'dashboard' ):
						unset( $GLOBALS['submenu'][ $slug ][ $itemIndex ] );
						array_unshift( $GLOBALS['submenu'][ $slug ], $item );
					endif;
				endforeach;

				$pages = array_keys( $this->pages );
				foreach ( $GLOBALS['submenu'][ $slug ] as $index => $item ):
					if ( in_array( $item[2], $pages, true ) ):
						$GLOBALS['submenu'][ $slug ][ $index ][4] = ! empty( $GLOBALS['plugin_page'] ) && $GLOBALS['plugin_page'] === $item[2] && $GLOBALS['typenow'] === TP_POLL_CPT_NAME ? 'current' : '';
						$GLOBALS['submenu'][ $slug ][ $index ][2] = add_query_arg( [ 'page' => $item[2] ], $slug );
					endif;
				endforeach;
			endif;

			return $file;
		} );
	}

	/**
	 * Footer text.
	 *
	 * @action-callback admin_footer_text
	 * @return string
	 */
	public function footerText() {
		$text = wp_kses( __( '{{product}} is part of <a href="{{totalsuite}}" target="_blank">TotalSuite</a>, a suite of robust and maintained plugins for WordPress.',
		                     'totalpoll' ),
		                 [ 'a' => [ 'href' => [] ] ] );

		return Strings::template(
			$text,
			[
				'product'    => $this->env['name'],
				'totalsuite' => add_query_arg(
					[
						'utm_source'   => 'in-app',
						'utm_medium'   => 'footer',
						'utm_campaign' => 'totalpoll',
					],
					$this->env['links.totalsuite']
				),
			]
		);
	}

	/**
	 * Footer version.
	 *
	 * @action-callback update_footer
	 * @return string
	 */
	public function footerVersion() {
		return "{$this->env['name']} {$this->env['version']}";
	}

	/**
	 * Render current page.
	 */
	public function page() {
		echo $this->currentPage;
		do_action( 'totalsuite/in-app-assets' );
	}

	/**
	 * Add direction (rtl|ltr) to body css classes.
	 *
	 * @param $classes
	 *
	 * @return string
	 */
	public function directionClass( $classes ) {
		return $classes . ( is_rtl() ? 'is-rtl' : ' is-ltr' );
	}

	/**
	 * @param $postId
	 */
	public function pollCleanup( $postId ) {
		$post = get_post( $postId );

		if ( $post && $post->post_type == TP_POLL_CPT_NAME ):
			TotalPoll( 'log.repository' )->delete( [ 'poll_id' => $postId ] );
			TotalPoll( 'entries.repository' )->delete( [ 'poll_id' => $postId ] );
			TotalPoll( 'polls.repository' )->deleteVotes( [ 'poll_id' => $postId ] );
			TotalPoll( 'polls.repository' )->getById( $postId )->refreshUid();
		endif;
	}

	/**
	 * Reset Poll
	 */
	function resetPoll() {
		if ( ! wp_verify_nonce( $this->request->query( '_wpnonce' ) ?: '', 'reset_poll' ) ):
			wp_die( esc_html__( 'Nonce check failed.', 'totalpoll' ) );
		endif;

		$poll = (int) $this->request->query( 'poll', 0 );
		$url  = wp_get_referer();

		if ( check_admin_referer( 'reset_poll' ) && current_user_can( 'delete_poll', $poll ) ) {
			$this->pollCleanup( $poll );
			$url = add_query_arg( 'message', 'reset-success', $url );
		} else {
			$url = add_query_arg( 'message', 'reset-error', $url );
		}
		wp_redirect( $url );
		exit();
	}

	/**
	 * Show admin messages / admin notice hook.
	 */
	function showMessages() {
		$message = $this->request->query( 'message' );

		if ( array_key_exists( $message, $this->messages ) ) {
			list( $content, $type ) = $this->messages[ $message ];
			printf( '<div class="notice %s"><p>%s</p></div>', $type, $content );
		}
	}
}
