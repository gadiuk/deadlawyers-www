<?php

namespace TotalPoll\Limitations;


use TotalPollVendors\TotalCore\Limitations\Limitation;

/**
 * Membership Limitation.
 * @package TotalPoll\Limitations
 */
class Membership extends Limitation {
	/**
	 * Limitation check logic.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {
		

		return true;
	}
}
