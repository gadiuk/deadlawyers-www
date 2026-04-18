<?php

namespace TotalPoll\Migrations\Polls\WPPolls;

use TotalPoll\Contracts\Migrations\Poll\Extract as ExtractContract;
use TotalPoll\Contracts\Migrations\Poll\Template\Poll;

/**
 * Extract Polls.
 * @package TotalPoll\Migrations\Polls\WPPolls
 */
class Extract implements ExtractContract {
	/**
	 * @var \wpdb $database
	 */
	protected $database;
	/**
	 * @var array
	 */
	private $tables;

	/**
	 * Extract constructor.
	 */
	public function __construct() {
		$this->database = TotalPoll( 'database' );
		$this->tables   = [
			'questions' => "{$this->database->prefix}pollsq",
			'answers'   => "{$this->database->prefix}pollsa",
			'log'       => "{$this->database->prefix}pollsip",
		];
	}

	/**
	 * Count polls.
	 *
	 * @return int
	 */
	public function getCount() {

        $tableName = "{$this->database->prefix}pollsq";

        if ( $this->database->get_var( "SHOW TABLES LIKE '{$this->tables['questions']}'" ) == $tableName ) :
            return (int) $this->database->get_var( "SELECT COUNT(pollq_id) AS total FROM {$this->tables['questions']}" );
        endif;

        return 0;
	}

	/**
	 * @return array
	 */
	public function getMigratedPollsIds() {
		return (array) get_option( 'wp-polls_poll_migrated', [] );
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

			$options = [
				'poll_bar'                  => get_option( 'poll_bar', false ),
				'poll_ans_sortby'           => get_option( 'poll_ans_sortby', false ),
				'poll_ans_sortorder'        => get_option( 'poll_ans_sortorder', false ),
				'poll_ans_result_sortby'    => get_option( 'poll_ans_result_sortby', false ),
				'poll_ans_result_sortorder' => get_option( 'poll_ans_result_sortorder', false ),
				'poll_allowtovote'          => get_option( 'poll_allowtovote', false ),
				'poll_cookielog_expiry'     => get_option( 'poll_cookielog_expiry', false ),
				'poll_logging_method'       => get_option( 'poll_logging_method', false ),
				'poll_bar_bg'               => get_option( 'poll_bar_bg', false ),
				'poll_bar_border'           => get_option( 'poll_bar_border', false ),
			];



			foreach ( $pollsIds as $pollId ):
				$query = $this->database->prepare( "SELECT pollq_question AS question, pollq_timestamp AS start_date, pollq_expiry AS end_date, pollq_multiple AS multiple FROM {$this->tables['questions']} WHERE pollq_id = %d", $pollId );
				$poll  = $this->database->get_row( $query, ARRAY_A );

				if ( ! $poll ):
					continue;
				endif;

				$query           = $this->database->prepare( "SELECT polla_answers AS label, polla_votes AS votes FROM {$this->tables['answers']} WHERE polla_qid = %d", $pollId );
				$poll['choices'] = $this->database->get_results( $query, ARRAY_A );
				$poll['id']      = $pollId;
				$poll['options'] = $options;

				$extractedPolls[] = $poll;
			endforeach;

		endif;

		return $extractedPolls;
	}

	/**
	 * Get options.
	 */
	public function getOptions() {
		return [];
	}

	/**
	 * Get log entries.
	 *
	 * @param Poll $poll
	 *
	 * @return array
	 */
	public function getLogEntries( Poll $poll ) {
		$pollsIds            = $this->getPollsIds();
		$extractedLogEntries = [];
		$choicesMap          = [];

		foreach ( $poll['choices'] as $choice ):
			$choicesMap[ $choice['uid'] ] = $choice['label'];
		endforeach;

		if ( ! empty( $pollsIds ) ):

			foreach ( $pollsIds as $pollId ):
				$query    = $this->database->prepare( "SELECT polla_answers AS choices, FROM_UNIXTIME(pollip_timestamp) AS date, pollip_userid AS user_id, pollip_ip AS ip FROM {$this->tables['log']} INNER JOIN {$this->tables['answers']} ON polla_aid = pollip_aid WHERE pollip_qid = %d", $pollId );
				$logEntry = $this->database->get_row( $query, ARRAY_A );

				if ( ! $logEntry ):
					continue;
				endif;

				$choices = [];
				foreach ( (array) $logEntry['choices'] as $choice ):
					$choiceUid = array_search( $choice, $choicesMap );
					if ( $choiceUid ):
						$choices[] = $choiceUid;
					endif;
				endforeach;

				$extractedLogEntries[] = [
					'ip'        => $logEntry['ip'],
					'useragent' => '',
					'user_id'   => empty( $logEntry['user_id'] ) ? 0 : (int) $logEntry['user_id'],
					'poll_id'   => $poll->getNewId(),
					'choices'   => $choices,
					'action'    => 'vote',
					'status'    => 'accepted',
					'details'   => [
						'choices' => (array) $logEntry['choices'],
					],
					'date'      => TotalPoll( 'datetime', [ $logEntry['date'] ] ),
				];

			endforeach;

		endif;

		return $extractedLogEntries;
	}

	/**
	 * @param Poll $poll
	 *
	 * @return array
	 */
	public function getSubmissions( Poll $poll ) {
		return [];
	}

	/**
	 * Get polls ids array.
	 *
	 * @return array
	 */
	private function getPollsIds() {
		$tableName = "{$this->database->prefix}pollsq";

		if ( $this->database->get_var( "SHOW TABLES LIKE '{$this->tables['questions']}'" ) == $tableName ) :
			$ids = $this->database->get_col( "SELECT pollq_id FROM {$this->tables['questions']}" );

			return array_diff( $ids, $this->getMigratedPollsIds() );
		endif;

		return [];
	}
}
