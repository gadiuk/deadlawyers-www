<?php

namespace TotalPoll\Restrictions;

/**
 * IP Restriction.
 * @package TotalPoll\Restrictions
 */
class IPAddress extends Restriction {
	use \TotalPollVendors\TotalCore\Traits\Cookies;

	/**
	 * @return bool|\WP_Error
	 */
	public function check() {
		
		return true;
		

		
	}

	/**
	 * Apply restriction.
	 */
	public function apply() {
		
	}
}
