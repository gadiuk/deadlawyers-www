<?php

namespace TotalPollVendors\TotalCore\Contracts\Limitations;

/**
 * Interface Limitation
 * @package TotalPollVendors\TotalCore\Contracts\Limitations
 */
interface Limitation {
	/**
	 * Limitation logic.
	 *
	 * @return bool
	 */
	public function check();
}