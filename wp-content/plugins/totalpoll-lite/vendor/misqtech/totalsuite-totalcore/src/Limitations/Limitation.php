<?php

namespace TotalPollVendors\TotalCore\Limitations;


use TotalPollVendors\TotalCore\Contracts\Limitations\Limitation as LimitationContract;

/**
 * Class Limitation
 * @package TotalPollVendors\TotalCore\Limitations
 */
abstract class Limitation implements LimitationContract {
	/**
	 * @var array $args
	 */
	public $args = [];

	/**
	 * Limitation constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		$this->args = $args;
	}
}
