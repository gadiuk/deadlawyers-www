<?php

namespace TotalPoll\Restrictions;

/**
 * Authenticated User Restriction.
 * @package TotalPoll\Restrictions
 */
class LoggedInUser extends Restriction {
	use \TotalPollVendors\TotalCore\Traits\Cookies;

	/**
	 * @return bool|\WP_Error
	 */
	public function check() {
		
		return true;
		

		
	}

	/**
	 * Apply Restriction.
	 */
	public function apply() {
		
	}
}
