<?php

namespace TotalPoll\Admin\Entries;

use TotalPollVendors\TotalCore\Admin\Pages\Page as TotalCoreAdminPage;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Page
 * @package TotalPoll\Admin\Entries
 */
class Page extends TotalCoreAdminPage {
	/**
	 * Page assets.
	 */
	public function assets() {
		/**
		 * @asset-script totalpoll-admin-entries
		 */
		wp_enqueue_script( 'totalpoll-admin-entries' );
		/**
		 * @asset-style totalpoll-admin-entries
		 */
		wp_enqueue_style( 'totalpoll-admin-entries' );

		// Some variables for frontend controller
		wp_localize_script(
			'totalpoll-admin-entries',
			'TotalPollEntries',
			[ 'pollId' => $this->request->query( 'poll' ) ]
		);
	}

	/**
	 * Page content.
	 */
	public function render() {
		/**
		 * Filters the list of available formats that can be used for export.
		 *
		 * @param array $formats Array of formats [id => label].
		 *
		 * @since 4.0.0
		 * @return array
		 */
		$formats = apply_filters(
			'totalpoll/filters/admin/entries/formats',
			[
				'html' => esc_html__( 'HTML', 'totalpoll' ),
				
			]
		);

		include __DIR__ . '/views/index.php';
	}
}
