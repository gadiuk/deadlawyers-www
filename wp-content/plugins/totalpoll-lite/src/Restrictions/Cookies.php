<?php

namespace TotalPoll\Restrictions;

/**
 * Cookies Restriction.
 *
 * @package TotalPoll\Restrictions
 */
class Cookies extends Restriction {
	/**
	 * @return bool|\WP_Error
	 */
	public function check() {
		$cookieName  = $this->getCookieName( 'cookies' );
		$cookieValue = absint( $this->getCookie( $cookieName ) );
		$result      = ! ( $cookieValue >= $this->getVotesPerSession() );

		return $result ?: new \WP_Error( 'cookies', $this->getMessage() );
	}

	/**
	 * Apply restriction.
	 */
	public function apply() {
		$cookieTimeout = $this->getTimeout();
		$cookieName    = $this->getCookieName( 'cookies' );
		$cookieValue   = absint( $this->getCookie( $cookieName ) );
		$this->setCookie( $cookieName, $cookieValue + 1, $cookieTimeout );

		// Add received choices as cookies
		$receivedChoicesCookieName  = $this->generateCookieName( 'received_' . $this->getPollId() . $this->getPollUid() );
		$receivedChoicesCookieValue = implode('__', $this->getPollReceivedChoices());
		$this->setCookie( $receivedChoicesCookieName, $receivedChoicesCookieValue, $cookieTimeout );
	}
}
