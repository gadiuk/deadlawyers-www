<?php

namespace TotalPoll\Migrations\Polls\YOP;

use TotalPoll\Contracts\Migrations\Poll\Extract as ExtractContract;
use TotalPoll\Contracts\Migrations\Poll\Template\Poll;

/**
 * Extract Polls.
 * @package TotalPoll\Migrations\Polls\YOP
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
			'polls'     => "{$this->database->prefix}yop2_polls",
			'questions' => "{$this->database->prefix}yop2_poll_questions",
			'answers'   => "{$this->database->prefix}yop2_poll_answers",
			'fields'    => "{$this->database->prefix}yop2_poll_custom_fields",
			'meta'      => "{$this->database->prefix}yop2_pollmeta",
		];
	}

	/**
	 * Count polls.
	 *
	 * @return int
	 */
	public function getCount() {
		$polls = $this->getPollsIds();

		return ! empty( $polls ) ? count( array_diff( $polls, $this->getMigratedPollsIds() ) ) : 0;
	}

	/**
	 * Get polls.
	 *
	 * @return array
	 */
	public function getPolls() {
		$pollsIds       = array_slice( $this->getPollsIds(), 0, 5 );
		$extractedPolls   = [];

		foreach ( $pollsIds as $pollId ):
			$query = $this->database->prepare( "SELECT poll_title, poll_start_date, poll_end_date FROM {$this->tables['polls']} WHERE ID = %d", $pollId );
			$poll  = $this->database->get_row( $query, ARRAY_A );

			if ( ! $poll ):
				continue;
			endif;

			$query = $this->database->prepare( "SELECT meta_value AS options FROM {$this->tables['meta']} WHERE yop_poll_id = %d AND meta_key = %s", $pollId, 'options' );
			$poll  = array_merge( $poll, $this->database->get_row( $query, ARRAY_A ) );

			$query            = $this->database->prepare( "SELECT question FROM {$this->tables['questions']} WHERE poll_id = %d", $pollId );
			$poll['question'] = $this->database->get_var( $query );

			$query           = $this->database->prepare( "SELECT answer, answer_status, votes, answer_date, answer_modified FROM {$this->tables['answers']} WHERE poll_id = %d ORDER BY question_order", $pollId );
			$poll['choices'] = $this->database->get_results( $query, ARRAY_A );

			$query          = $this->database->prepare( "SELECT ID, custom_field, required, status FROM {$this->tables['fields']} WHERE poll_id = %d", $pollId );
			$poll['fields'] = $this->database->get_results( $query, ARRAY_A );

			$poll['options'] = unserialize( $poll['options'] );
			$poll['id']      = $pollId;

			$extractedPolls[] = $poll;
		endforeach;

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
		return [];
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

		if ( $this->database->get_var( "SHOW TABLES LIKE '{$this->tables['polls']}'" ) == $this->tables['polls'] ) :
			$ids = $this->database->get_col( $this->database->prepare( "SELECT ID FROM {$this->tables['polls']} WHERE poll_type = %s", 'poll' ) );

			return array_diff( $ids, $this->getMigratedPollsIds() );
		endif;

		return [];
	}

	/**
	 * @return array
	 */
	public function getMigratedPollsIds() {
		return (array) get_option( 'yop_poll_migrated', [] );
	}
}