<?php

namespace TotalPoll\Admin\Modules;

use TotalPollVendors\TotalCore\Admin\Pages\Page as TotalCoreAdminPage;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Page
 * @package TotalPoll\Admin\Modules
 */
class Page extends TotalCoreAdminPage {
	/**
	 * Page assets.
	 */
	public function assets() {
		/**
		 * @asset-script totalpoll-admin-modules
		 */
		wp_enqueue_script( 'totalpoll-admin-modules' );
		/**
		 * @asset-style totalpoll-admin-modules
		 */
		wp_enqueue_style( 'totalpoll-admin-modules' );
	}

	/**
	 * Page content.
	 */
	public function render() {
		include __DIR__ . '/views/index.php';
	}
}
