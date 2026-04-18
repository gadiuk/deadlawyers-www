<?php

namespace TotalPoll\Limitations;


use TotalPollVendors\TotalCore\Limitations\Limitation;

/**
 * Region Limitation
 * @package TotalPoll\Limitations
 */
class Region extends Limitation {
	/**
	 * Limitation check logic.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {
		

		return true;
	}
}
