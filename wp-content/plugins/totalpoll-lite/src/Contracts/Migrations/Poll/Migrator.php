<?php

namespace TotalPoll\Contracts\Migrations\Poll;

use TotalPoll\Contracts\Migrations\Poll\Template\Poll;

/**
 * Interface Migrator
 * @package TotalPoll\Contracts\Migrations\Poll
 */
interface Migrator extends \JsonSerializable {

	/**
	 * @return int
	 */
	public function getCount();

	/**
	 * @return int
	 */
	public function getMigratedCount();

	/**
	 * @return Poll[]
	 */
	public function migrate();

}