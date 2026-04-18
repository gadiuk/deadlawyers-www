<?php

namespace TotalPoll\Contracts\Poll;

/**
 * Poll repository
 * @package TotalPoll\Poll
 * @since   4.0.0
 */
interface Repository {
	/**
	 * Get polls.
	 *
	 * @param $query
	 *
	 * @since 4.0.0
	 * @return \TotalPoll\Contracts\Poll\Model[]
	 */
	public function get( $query );

	/**
	 * Get poll by id.
	 *
	 * @param $pollId
	 *
	 * @return \TotalPoll\Contracts\Poll\Model|null
	 * @since 4.0.0
	 */
	public function getById( $pollId );

	/**
	 * Get choice(s) votes.
	 *
	 * @param int $pollId
	 * @param string|null $choiceUid
	 *
	 * @return mixed
	 * @since 4.0.0
	 */
	public function getVotes( $pollId, $choiceUid = null );

	/**
	 * Increment choice(s) votes.
	 *
	 * @param int $pollId
	 * @param array $choices
	 *
	 * @return false|int
	 * @since 4.0.0
	 */
	public function incrementVotes( $pollId, $choices );

	/**
	 * Set choice(s) votes.
	 *
	 * @param int $pollId
	 * @param array $choicesUidsVotes
	 *
	 * @return false|int
	 * @since 4.0.0
	 */
	public function setVotes( $pollId, $choicesUidsVotes );

	/**
	 * Delete votes.
	 *
	 * @param $query array
	 *
	 * @return bool|\WP_Error
	 * @since 4.0.5
	 */
	public function deleteVotes( $query );
}