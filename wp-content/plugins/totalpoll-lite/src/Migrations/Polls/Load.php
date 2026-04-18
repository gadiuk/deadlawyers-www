<?php

namespace TotalPoll\Migrations\Polls;

use TotalPoll\Contracts\Log\Model as LogModel;
use TotalPoll\Contracts\Migrations\Poll\Template\LogEntry;
use TotalPoll\Contracts\Migrations\Poll\Template\Options;
use TotalPoll\Contracts\Migrations\Poll\Template\Poll;
use TotalPoll\Contracts\Migrations\Poll\Template\Submission;
use TotalPoll\Contracts\Migrations\Poll\Template\Submission as SubmissionModel;

/**
 * Load Polls.
 * @package TotalPoll\Migrations\Polls
 */
class Load implements \TotalPoll\Contracts\Migrations\Poll\Load {

	/**
	 * @param Poll $poll
	 *
	 * @return Poll
	 */
	public function loadPoll( Poll $poll ) {
		$poll['presetUid'] = md5( $poll->getId() );

		$defaults = TotalPoll( 'polls.defaults', [] );
		$model    = wp_parse_args( $poll->toArray(), $defaults );

		$id = wp_insert_post(
			[
				'ID'           => $poll->getNewId(),
				'post_title'   => $poll->getTitle(),
				'post_content' => wp_slash( json_encode( $model ) ),
				'post_type'    => TP_POLL_CPT_NAME,
			]
		);

		if ( is_int( $id ) ):
			$poll->setNewId( $id );
		endif;

		$choicesVotes = [];
		foreach ( $poll['questions'] as $question ):
			foreach ( $question['choices'] as $choice ):
				$choicesVotes[ $choice['uid'] ] = $choice['votes'];
			endforeach;
		endforeach;

		TotalPoll( 'polls.repository' )->setVotes( $poll->getNewId(), $choicesVotes );

		update_post_meta( $poll->getNewId(), '_migrated', 'migrated' );

		return $poll;
	}

	/**
	 * @param Options $options
	 *
	 * @return array
	 */
	public function loadOptions( Options $options ) {
		return TotalPoll( 'options' )->setOptions( $options->toArray() );
	}

	/**
	 * @param Poll $poll
	 * @param LogEntry $logEntry
	 *
	 * @return LogModel
	 */
	public function loadLogEntry( Poll $poll, LogEntry $logEntry ) {
		return TotalPoll( 'log.repository' )->create( $logEntry->toArray() );
	}

	/**
	 * @param Poll $poll
	 * @param Submission $submission
	 *
	 * @return SubmissionModel
	 */
	public function loadSubmission( Poll $poll, Submission $submission ) {
		return TotalPoll( 'entries.repository' )->create( $submission->toArray() );
	}
}
