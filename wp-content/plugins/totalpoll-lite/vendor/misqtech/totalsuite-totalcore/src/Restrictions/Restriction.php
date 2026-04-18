<?php

namespace TotalPollVendors\TotalCore\Restrictions;


use TotalPollVendors\TotalCore\Contracts\Restrictions\Restriction as RestrictionContract;

/**
 * Class Restriction
 * @package TotalPollVendors\TotalCore\Restrictions
 */
abstract class Restriction implements RestrictionContract {
	/**
	 * @var array $args
	 */
	public $args = [];

	/**
	 * Restriction constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		$this->args = $args;
	}
}
