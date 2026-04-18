<?php

namespace TotalPoll\Restrictions;

use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Restrictions\Restriction as RestrictionBase;

/**
 * Base Restriction.
 *
 * @package TotalPoll\Restrictions
 */
abstract class Restriction extends RestrictionBase {
	use \TotalPollVendors\TotalCore\Traits\Cookies;

	/**
	 * @return bool
	 */
	public function isFullCheck() {
		return (bool) Arrays::getDotNotation( $this->args, 'fullCheck', false );
	}

	/**
	 * @return mixed
	 */
	public function getPollId() {
		return $this->args['poll']->getId();
	}

	/**
	 * @return mixed
	 */
	public function getPollUid() {
		return $this->args['poll']->getUid();
	}

	public function getPollReceivedChoices() {
		$choices = $this->args['poll']->getReceivedChoices();

		return array_column( $choices, 'uid' );
	}

	/**
	 * @param  int  $default
	 *
	 * @return int
	 */
	public function getTimeout( $default = 3600 ) {
		return (int) Arrays::getDotNotation( $this->args, 'timeout', 3600 );
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return (string) Arrays::getDotNotation( $this->args, 'action' );
	}

	/**
	 * @param  int  $default
	 *
	 * @return int
	 */
	public function getVotesPerSession( $default = 1 ) {
		return (int) Arrays::getDotNotation( $this->args, 'perSession', $default );
	}

	/**
	 * @param  int  $default
	 *
	 * @return int
	 */
	public function getVotesPerIP( $default = 1 ) {
		return (int) Arrays::getDotNotation( $this->args, 'perIP', $default );
	}

	/**
	 * @param  int  $default
	 *
	 * @return int
	 */
	public function getVotesPerUser( $default = 1 ) {
		return (int) Arrays::getDotNotation( $this->args, 'perUser', $default );
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		return empty( $this->args['message'] ) ? esc_html__( 'You cannot vote again.',
		                                                     'totalpoll' ) : (string) $this->args['message'];
	}

	/**
	 * @param $prefix
	 *
	 * @return string
	 */
	public function getCookieName( $prefix ) {
		$name = $this->generateCookieName( $prefix . $this->getAction() . $this->getPollId() . $this->getPollUid() );

		/**
		 * Filters poll cookie name.
		 *
		 * @param  string  $name  Cookie name.
		 * @param  string  $prefix  Prefix.
		 * @param  string  $args  Args.
		 * @param  self  $this  Restriction object.
		 *
		 * @return string
		 * @since 4.1.3
		 */
		return apply_filters( 'totalpoll/filters/poll/cookies/name', $name, $prefix, $this->args, $this );
	}
}
