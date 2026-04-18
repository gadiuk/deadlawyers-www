<?php

namespace TotalPoll\Migrations\Polls\Templates;

use TotalPoll\Contracts\Migrations\Poll\Template\Options as OptionsContract;
use TotalPollVendors\TotalCore\Helpers\Arrays;

/**
 * Options Migration Template.
 * @package TotalPoll\Migrations\Polls\Templates
 */
class Options extends Template implements OptionsContract {
	/**
	 * @param $section
	 * @param $value
	 *
	 * @return mixed
	 */
	public function addOption( $section, $value ) {
		$this->data['options'] = Arrays::setDotNotation( $this->data['options'], $section, $value );
	}
}