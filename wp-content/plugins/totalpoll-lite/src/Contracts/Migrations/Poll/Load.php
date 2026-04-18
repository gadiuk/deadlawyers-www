<?php

namespace TotalPoll\Contracts\Migrations\Poll;

use TotalPoll\Contracts\Migrations\Poll\Template\Submission;
use TotalPoll\Contracts\Migrations\Poll\Template\LogEntry;
use TotalPoll\Contracts\Migrations\Poll\Template\Options;
use TotalPoll\Contracts\Migrations\Poll\Template\Poll;

/**
 * Interface Load
 * @package TotalPoll\Contracts\Migrations\Poll
 */
interface Load {

	/**
	 * @param Poll $poll
	 *
	 * @return mixed
	 */
	public function loadPoll( Poll $poll );

	/**
	 * @param Options $options
	 *
	 * @return mixed
	 */
	public function loadOptions( Options $options );

	/**
	 * @param Poll     $poll
	 * @param LogEntry $logEntry
	 *
	 * @return mixed
	 */
	public function loadLogEntry( Poll $poll, LogEntry $logEntry );

	/**
	 * @param Poll       $poll
	 * @param Submission $submission
	 *
	 * @return mixed
	 */
	public function loadSubmission( Poll $poll, Submission $submission );
}