<?php

namespace TotalPoll\Migrations\Polls\TotalPoll;

use TotalPoll\Contracts\Migrations\Poll\Extract as ExtractContract;
use TotalPoll\Contracts\Migrations\Poll\Template\Poll;

/**
 * Extract Polls.
 * @package TotalPoll\Migrations\Polls\TotalPoll
 */
class Extract implements ExtractContract {
	/**
	 * Count polls.
	 *
	 * @return int
	 */
	public function getCount() {
		return count( $this->getPollsIds() );
	}

	/**
	 * Get polls.
	 *
	 * @return array
	 */
	public function getPolls() {
		$pollsIds       = array_slice( $this->getPollsIds(), 0, 5 );
		$extractedPolls = [];

		if ( ! empty( $pollsIds ) ):

			foreach ( $pollsIds as $pollId ):
				$poll['id']       = $pollId;
				$poll['title']    = get_the_title( $pollId );
				$poll['question'] = get_post_meta( $pollId, 'question', true );

				$poll['choices'] = [];
				$choicesCount    = (int) get_post_meta( $pollId, 'choices', true );
				for ( $choicesStart = 0; $choicesStart < $choicesCount; $choicesStart ++ ):
					$poll['choices'][ $choicesStart ]            = [];
					$poll['choices'][ $choicesStart ]['content'] = get_post_meta( $pollId, "choice_{$choicesStart}_content", true );
					$poll['choices'][ $choicesStart ]['votes']   = get_post_meta( $pollId, "choice_{$choicesStart}_votes", true );
				endfor;

				$poll['options'] = [
					'limitations'   => get_post_meta( $pollId, 'settings_limitations', true ),
					'results'       => get_post_meta( $pollId, 'settings_results', true ),
					'choices'       => get_post_meta( $pollId, 'settings_choices', true ),
					'fields'        => get_post_meta( $pollId, 'settings_fields', true ),
					'design'        => get_post_meta( $pollId, 'settings_design', true ),
					'screens'       => get_post_meta( $pollId, 'settings_screens', true ),
					'logs'          => get_post_meta( $pollId, 'settings_logs', true ),
					'notifications' => get_post_meta( $pollId, 'settings_notifications', true ),
				];

				$extractedPolls[] = $poll;
			endforeach;

		endif;

		return $extractedPolls;
	}

	/**
	 * Get options.
	 *
	 * @return array
	 */
	public function getOptions() {
		$options              = (array) get_option( 'totalpoll_options', [] );
		$options['recaptcha'] = [
			'key'    => get_option( '_tp_options_captcha_site_key' ),
			'secret' => get_option( '_tp_options_captcha_site_secret' ),
		];

		return $options;
	}

	/**
	 * Get polls ids array.
	 *
	 * @return array
	 */
	private function getPollsIds() {
		return get_posts(
			[
				'post_type'      => 'poll',
				'posts_per_page' => - 1,
				'meta_key'       => 'choices',
				'fields'         => 'ids',
				'post_status'    => 'any',
				'meta_query'     => [
					[
						'key'     => 'choices',
						'compare' => 'EXISTS',
					],
					[
						'key'     => '_migrated',
						'value'   => 'migrated',
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);
	}

	/**
	 * @param Poll $poll
	 *
	 * @return array
	 */
	public function getLogEntries( Poll $poll ) {
		$extractedLogEntries = [];
		$choicesMap          = [];
		$logsCount           = (int) get_post_meta( $poll->getId(), '_mp_logs', true );

		foreach ( $poll['choices'] as $choice ):
			$choicesMap[ $choice['uid'] ] = $choice['label'];
		endforeach;

		if ( $logsCount > 0 ):
			for ( $startOffset = 0; $startOffset < $logsCount; $startOffset ++ ):
				$logEntry = get_post_meta( '_mp_logs_' . $startOffset );
				if ( $logEntry ):
					$choices = [];
					foreach ( $logEntry['choices'] as $choice ):
						$choiceUid = array_search( $choice, $choicesMap );
						if ( $choiceUid ):
							$choices[] = $choiceUid;
						endif;
					endforeach;
					$extractedLogEntries[] = [
						'ip'        => $logEntry['ip'],
						'useragent' => $logEntry['useragent'],
						'user_id'   => empty( $logEntry['details']['user_id'] ) ? 0 : trim( str_replace( 'User ID:', '', $logEntry['details']['user_id'] ) ),
						'poll_id'   => $poll->getNewId(),
						'choices'   => $choices,
						'action'    => 'vote',
						'status'    => $logEntry['status'] ? 'accepted' : 'rejected',
						'details'   => empty( $logEntry['details'] ) ? [] : $logEntry['details'],
						'date'      => TotalPoll( 'datetime', [ $logEntry['time'] ] ),
					];
				endif;
			endfor;
		endif;

		return $extractedLogEntries;
	}

	/**
	 * @param Poll $poll
	 *
	 * @return array
	 */
	public function getSubmissions( Poll $poll ) {
		$extractedSubmissions = [];
		$submissionsCount     = (int) get_post_meta( $poll->getId(), '_mp_submissions', true );

		if ( $submissionsCount > 0 ):
			for ( $startOffset = 0; $startOffset < $submissionsCount; $startOffset ++ ):
				$submission = get_post_meta( '_mp_submissions_' . $startOffset );
				if ( $submission ):
					$extractedSubmissions[] = [
						'poll_id' => $poll->getNewId(),
						'log_id'  => 0,
						'user_id' => 0,
						'date'    => TotalPoll( 'datetime', [ $submission['__submission_date'] ] ),
						'fields'  => $submission['fields'],
						'details' => [],
					];
				endif;
			endfor;
		endif;

		return $extractedSubmissions;
	}

	/**
	 * Get migrated polls ids.
	 *
	 * @return array
	 */
	public function getMigratedPollsIds() {
		return [];
	}
}