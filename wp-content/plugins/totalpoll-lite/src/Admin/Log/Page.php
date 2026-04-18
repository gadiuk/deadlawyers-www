<?php

namespace TotalPoll\Admin\Log;

use TotalPollVendors\TotalCore\Admin\Pages\Page as TotalCoreAdminPage;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Page
 * @package TotalPoll\Admin\Log
 */
class Page extends TotalCoreAdminPage {
	/**
	 * Page assets.
	 */
	public function assets() {
		/**
		 * @asset-script totalpoll-admin-log
		 */
		wp_enqueue_script( 'totalpoll-admin-log' );
		/**
		 * @asset-style totalpoll-admin-log
		 */
		wp_enqueue_style( 'totalpoll-admin-log' );

		// Some variables for frontend controller
		wp_localize_script(
			'totalpoll-admin-log',
			'TotalPollLog',
			[ 'pollId' => $this->request->query( 'poll' ) ]
		);
	}

	/**
	 * Page content.
	 */
	public function render() {
		/**
		 * Filters the list of columns in log browser.
		 *
		 * @param array $columns Array of columns.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$columns = apply_filters(
			'totalpoll/filters/admin/log/columns',
			[
				'status'     => [ 'label' => esc_html__( 'Status', 'totalpoll' ), 'default' => true, ],
				'action'     => [ 'label' => esc_html__( 'Action', 'totalpoll' ), 'default' => true, ],
				'date'       => [ 'label' => esc_html__( 'Date', 'totalpoll' ), 'default' => true, ],
				'ip'         => [ 'label' => esc_html__( 'IP', 'totalpoll' ), 'default' => true, ],
				'browser'    => [ 'label' => esc_html__( 'Browser', 'totalpoll' ), 'default' => true, ],
				'poll'       => [ 'label' => esc_html__( 'Poll', 'totalpoll' ), 'default' => true, ],
				'user_name'  => [ 'label' => esc_html__( 'User name', 'totalpoll' ), 'default' => false, ],
				'user_id'    => [ 'label' => esc_html__( 'User ID', 'totalpoll' ), 'default' => false, ],
				'user_login' => [ 'label' => esc_html__( 'User login', 'totalpoll' ), 'default' => true, ],
				'user_email' => [ 'label' => esc_html__( 'User email', 'totalpoll' ), 'default' => false, ],
				'details'    => [ 'label' => esc_html__( 'Details', 'totalpoll' ), 'default' => false, 'compact' => true ],
			]
		);
		/**
		 *
		 * Filters the list of available formats that can be used for export.
		 *
		 * @param array $formats Array of formats [id => label].
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$formats = apply_filters(
			'totalpoll/filters/admin/log/formats',
			[
				'html' => esc_html__( 'HTML', 'totalpoll' ),
				
			]
		);

		include __DIR__ . '/views/index.php';
	}
}
