<?php

namespace TotalPoll\Limitations;


use TotalPollVendors\TotalCore\Limitations\Limitation;

/**
 * Quota Limitation
 * @package TotalPoll\Limitations
 */
class Quota extends Limitation {
	/**
	 * Limitation check logic.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {
		

		return true;
	}
}
