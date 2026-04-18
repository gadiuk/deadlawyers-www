<?php

namespace TotalPollVendors\TotalCore\Contracts\Http;

/**
 * Interface Response
 * @package TotalPollVendors\TotalCore\Contracts\Http
 */
interface Response {
	/**
	 * Send response.
	 *
	 * @return $this
	 */
	public function send();
}