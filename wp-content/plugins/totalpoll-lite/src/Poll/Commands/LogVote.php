<?php

namespace TotalPoll\Poll\Commands;

use TotalPoll\Contracts\Log\Repository;
use TotalPoll\Contracts\Poll\Model;
use TotalPollVendors\TotalCore\Helpers\Command;

/**
 * Class LogVote
 * @package TotalPoll\Poll\Commands
 */
class LogVote extends Command {
	protected $poll;
	protected $repository;

	/**
	 * LogVote constructor.
	 *
	 * @param Model      $poll
	 * @param Repository $repository
	 */
	public function __construct( Model $poll, Repository $repository ) {
		$this->poll       = $poll;
		$this->repository = $repository;
	}

	/**
	 * Log vote.
	 *
	 * @return mixed
	 */
	protected function handle() {

		/**
		 * Fires before saving the log entry.
		 *
		 * @param Model $poll Poll model object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/poll/command/log', $this->poll );
		$details = [];
		$choices = [];

		$error           = $this->poll->getErrorMessage();
		$receivedChoices = $this->poll->getReceivedChoices();

		if ( $error ):
			$details['error'] = $this->poll->getErrorMessage();
		endif;

		if ( $receivedChoices ):
			$details['choices'] = [];
			foreach ( $receivedChoices as $choice ):
				$choices[]            = $choice['uid'];
				$details['choices'][] = $choice['label'];
			endforeach;
		endif;

		$questions = $this->poll->getQuestions();
		$receivedQuestions = $this->poll->getReceivedQuestions();
		$details['skipped'] = [];

		foreach ($questions as $question) {
			if(! in_array($question['uid'], $receivedQuestions)) {
				$details['skipped'][] = $question['uid'];
			}
		}

		if(empty($details['skipped'])) {
			unset($details['skipped']);
		}

		$log = $this->repository->create(
			apply_filters(
				'totalpoll/filters/poll/command/log/attributes',
				[
					'poll_id' => $this->poll->getId(),
					'action'  => 'vote',
					'status'  => $this->poll->getError() ? 'rejected' : 'accepted',
					'choices' => $choices,
					'details' => $details,
				],
				$this->poll
			)
		);

		/**
		 * Fires after saving the log entry.
		 *
		 * @param \TotalPoll\Contracts\Log\Model  $log  Log entry model object.
		 * @param Model $poll Poll model object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/poll/command/log', $log, $this->poll );

		if ( $log ):
			self::share( 'log', $log );
		endif;

		return $log;
	}
}
