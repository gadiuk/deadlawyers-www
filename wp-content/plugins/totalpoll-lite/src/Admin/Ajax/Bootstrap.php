<?php

namespace TotalPoll\Admin\Ajax;

use Exception;
use TotalPoll\Admin\Preset\Repository;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Bootstrap
 *
 * @package TotalPoll\Admin\Ajax
 * @since   1.0.0
 */
class Bootstrap {

	/**
	 * Attach ajax actions to their appropriate callbacks.
	 */
	public function __construct() {
		// Checking nonce for all AJAX actions
		if ( wp_doing_ajax() ):
			$ajaxAction = (string) TotalPoll( 'http.request' )->request( 'action' );
		
			if ( strstr( $ajaxAction, 'totalpoll_' ) !== false ):
				$nonce = TotalPoll( 'http.request' )->request( '_wpnonce' );

				if ( ! wp_verify_nonce( $nonce, 'totalpoll' ) ):
					wp_send_json_error( [ 'message' => esc_html__( 'Nonce check failed.', 'totalpoll' ) ], 401 );
					exit;
				endif;
			endif;
		endif;

		if ( current_user_can( 'edit_polls' ) ):
			// ------------------------------
			// Polls
			// ------------------------------
			/**
			 * @action wp_ajax_totalpoll_polls_add_to_sidebar
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_polls_add_to_sidebar', function () {
				TotalPoll( 'admin.ajax.polls' )->addToSidebar();
			} );

			/**
			 * @action wp_ajax_totalpoll_nps
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_nps', function () {
				if ( current_user_can( 'manage_options' ) ) {
					$nps            = TotalPoll( 'http.request' )->request( 'nps' );
					$nps['product'] = TotalPoll( 'env' )->get( 'slug' );
					$nps['uid']     = TotalPoll()->uid();
					update_option( 'totalpoll_nps', $nps );

					wp_remote_post( 'https://collect.totalsuite.net/nps', [
						'body'     => $nps,
						'blocking' => false,
					] );
				}

				wp_send_json_success();
			} );

			/**
			 * @action wp_ajax_totalpoll_onboarding
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_onboarding', function () {
				if ( current_user_can( 'manage_options' ) ) {
					$onboarding['product'] = TotalPoll( 'env' )->get( 'slug' );
					$onboarding['uid']     = TotalPoll()->uid();
					$onboarding['date']    = date( DATE_ATOM );
					$onboarding['data']    = TotalPoll( 'http.request' )->request( 'onboarding' );

					update_option( 'totalpoll_onboarding', $onboarding['data'] );

					wp_remote_post( 'https://collect.totalsuite.net/onboarding', [
						'body'     => $onboarding,
						'blocking' => false,
					] );
				}

				wp_send_json_success();
			} );

			/**
			 * @action wp_ajax_totalpoll_tracking
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_tracking_features', function () {
				$action = TotalPoll( 'http.request' )->request( 'event' );
				$target = TotalPoll( 'http.request' )->request( 'target' );

				wp_send_json_success();
			} );

			/**
			 * @action wp_ajax_totalpoll_tracking
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_tracking_screens', function () {
				$key = TotalPoll( 'env' )->get( 'tracking-key', 'totalpoll_tracking' );

				$tracking = (array) get_option( $key, [
					'screens'  => [],
					'features' => [],
				] );


				$tracking['screens'][] = [
					'screen' => TotalPoll( 'http.request' )->request( 'label' ),
					'date'   => date( DATE_ATOM ),
				];

				update_option( $key, $tracking );

				wp_send_json_success();
			} );

			// ------------------------------
			// Entries
			// ------------------------------
			/**
			 * @action wp_ajax_totalpoll_entries_list
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_entries_list', function () {
				TotalPoll( 'admin.ajax.entries' )->fetch();
			} );
			/**
			 * @action wp_ajax_totalpoll_entries_download
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_entries_download', function () {
				TotalPoll( 'admin.ajax.entries' )->download();
			} );

			/**
			 * @action wp_ajax_totalpoll_entries_polls
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_entries_polls', function () {
				TotalPoll( 'admin.ajax.entries' )->polls();
			} );

			/**
			 * @action wp_ajax_totalpoll_entries_purge
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_entries_purge', function () {
				TotalPoll( 'admin.ajax.entries' )->purge();
			} );

			/**
			 * @action wp_ajax_totalpoll_entries_remove
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_entries_remove', function () {
				TotalPoll( 'admin.ajax.entries' )->remove();
			} );

			// ------------------------------
			// Insights
			// ------------------------------
			/**
			 * @action wp_ajax_totalpoll_insights_metrics
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_insights_metrics', function () {
				TotalPoll( 'admin.ajax.insights' )->metrics();
			} );
			/**
			 * @action wp_ajax_totalpoll_insights_polls
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_insights_polls', function () {
				TotalPoll( 'admin.ajax.insights' )->polls();
			} );

			/**
			 * @action wp_ajax_totalpoll_insights_download
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_insights_download', function () {
				TotalPoll( 'admin.ajax.insights' )->download();
			} );

			/**
			 * @action wp_ajax_totalpoll_blog_feed
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_dashboard_blog_feed', function () {
				TotalPoll( 'admin.ajax.dashboard' )->blog();
			} );
		endif;

		if ( current_user_can( 'manage_options' ) ):
			// ------------------------------
			// Dashboard
			// ------------------------------
			/**
			 * @action wp_ajax_totalpoll_dashboard_activate
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_dashboard_activate', function () {
				TotalPoll( 'admin.ajax.dashboard' )->activate();
			} );

			/**
			 * @action wp_ajax_totalpoll_dashboard_deactivate
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_dashboard_deactivate', function () {
				TotalPoll( 'admin.ajax.dashboard' )->deactivate();
			} );

			/**
			 * @action wp_ajax_totalpoll_dashboard_account
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_dashboard_account', function () {
				TotalPoll( 'admin.ajax.dashboard' )->account();
			} );
			/**
			 * @action wp_ajax_totalpoll_dashboard_polls_overview
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_dashboard_polls_overview', function () {
				TotalPoll( 'admin.ajax.dashboard' )->polls();
			} );

			// ------------------------------
			// Log
			// ------------------------------
			/**
			 * @action wp_ajax_totalpoll_log_list
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_log_list', function () {
				TotalPoll( 'admin.ajax.log' )->fetch();
			} );
			/**
			 * @action wp_ajax_totalpoll_log_download
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_log_download', function () {
				TotalPoll( 'admin.ajax.log' )->download();
			} );
			/**
			 * @action wp_ajax_totalpoll_log_remove
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_log_remove', function () {
				TotalPoll( 'admin.ajax.log' )->remove();
			} );

			/**
			 * @action wp_ajax_totalpoll_log_remove
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_log_purge', function () {
				TotalPoll( 'admin.ajax.log' )->purge();
			} );

			add_action( 'wp_ajax_totalpoll_log_export', function () {
				TotalPoll( 'admin.ajax.log' )->export();
			} );

			add_action( 'wp_ajax_totalpoll_log_export_status', function () {
				TotalPoll( 'admin.ajax.log' )->exportStatus();
			} );

			// ------------------------------
			// Modules
			// ------------------------------
			/**
			 * @action wp_ajax_totalpoll_modules_install_from_file
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_modules_install_from_file', function () {
				TotalPoll( 'admin.ajax.modules' )->installFromFile();
			} );
			/**
			 * @action wp_ajax_totalpoll_modules_install_from_store
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_modules_install_from_store', function () {
				TotalPoll( 'admin.ajax.modules' )->installFromStore();
			} );
			/**
			 * @action wp_ajax_totalpoll_modules_list
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_modules_list', function () {
				try {
					TotalPoll( 'admin.ajax.modules' )->fetch();
				} catch ( Exception $exception ) {
					wp_send_json_error( $exception );
				}
			} );
			/**
			 * @action wp_ajax_totalpoll_modules_update
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_modules_update', function () {
				TotalPoll( 'admin.ajax.modules' )->update();
			} );
			/**
			 * @action wp_ajax_totalpoll_modules_uninstall
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_modules_uninstall', function () {
				TotalPoll( 'admin.ajax.modules' )->uninstall();
			} );
			/**
			 * @action wp_ajax_totalpoll_modules_activate
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_modules_activate', function () {
				TotalPoll( 'admin.ajax.modules' )->activate();
			} );
			/**
			 * @action wp_ajax_totalpoll_modules_deactivate
			 * @since  4.0.0
			 */
			add_action( 'wp_ajax_totalpoll_modules_deactivate', function () {
				TotalPoll( 'admin.ajax.modules' )->deactivate();
			} );

			// ------------------------------
			// Options
			// ------------------------------
			add_action( 'wp_ajax_totalpoll_options_save_options', function () {
				TotalPoll( 'admin.ajax.options' )->saveOptions();
			} );
			add_action( 'wp_ajax_totalpoll_options_purge', function () {
				TotalPoll( 'admin.ajax.options' )->purge();
			} );
			add_action( 'wp_ajax_totalpoll_options_migrate_polls', function () {
				TotalPoll( 'admin.ajax.options' )->migratePolls();
			} );
		endif;

		// ------------------------------
		// Templates
		// ------------------------------
		/**
		 * @action wp_ajax_totalpoll_templates_get_defaults
		 * @since  4.0.0
		 */
		add_action( 'wp_ajax_totalpoll_templates_get_defaults', function () {
			TotalPoll( 'admin.ajax.templates' )->getDefaults();
		} );
		/**
		 * @action wp_ajax_totalpoll_templates_get_preview
		 * @since  4.0.0
		 */
		add_action( 'wp_ajax_totalpoll_templates_get_preview', function () {
			TotalPoll( 'admin.ajax.templates' )->getPreview();
		} );
		/**
		 * @action wp_ajax_totalpoll_templates_get_settings
		 * @since  4.0.0
		 */
		add_action( 'wp_ajax_totalpoll_templates_get_settings', function () {
			TotalPoll( 'admin.ajax.templates' )->getSettings();
		} );

		/**
		 * Fires when AJAX handlers are bootstrapped.
		 *
		 * @since 4.0.0
		 * @order 7
		 */
		do_action( 'totalpoll/actions/bootstrap-ajax' );
	}

}
