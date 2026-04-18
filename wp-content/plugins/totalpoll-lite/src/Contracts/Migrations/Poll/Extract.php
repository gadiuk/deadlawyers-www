<?php

namespace TotalPoll\Contracts\Migrations\Poll;

use TotalPoll\Contracts\Migrations\Poll\Template\Poll;

/**
 * Interface Extract
 * @package TotalPoll\Contracts\Migrations\Poll
 */
interface Extract {

	/**
	 * Count polls.
	 *
	 * @return int
	 */
	public function getCount();

	/**
	 * Get polls.
	 *
	 * @return array
	 */
	public function getPolls();

	/**
	 * Get options.
	 *
	 * @return array
	 */
	public function getOptions();

	/**
	 * Get log entries.
	 *
	 * @param Poll $poll
	 *
	 * @return array
	 */
	public function getLogEntries( Poll $poll );

	/**
	 * Get submissions.
	 *
	 * @param Poll $poll
	 *
	 * @return array
	 */
	public function getSubmissions( Poll $poll );

	/**
	 * Get migrated polls ids.
	 *
	 * @return array
	 */
	public function getMigratedPollsIds();

}