<?php

namespace TotalPoll\Contracts\Log;

/**
 * Poll repository
 * @package TotalPoll\Log
 * @since   4.0.0
 */
interface Repository {
	/**
	 * Get log entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 4.0.0
	 */
	public function get( $query );

	/**
	 * Get log entry by id.
	 *
	 * @param $logId
	 *
	 * @return \TotalPoll\Contracts\Log\Model|null
	 * @since 4.0.0
	 */
	public function getById( $logId );

	/**
	 * Create log entry.
	 *
	 * @param $attributes
	 *
	 * @return Model|\WP_Error
	 * @since 4.0.0
	 */
	public function create( $attributes );

	/**
	 * Delete log entries.
	 *
	 * @param $query array
	 *
	 * @return bool|\WP_Error
	 * @since 4.0.0
	 */
	public function delete( $query );

	/**
	 * Count log entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 4.0.0
	 */
	public function count( $query );

	/**
	 * Anonymize log entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 4.0.0
	 */
	public function anonymize( $query );

	/**
	 * @param $query
	 *
	 * @return array
	 * @since 4.0.0
	 */
	public function countVotesPerPeriod( $query );

	/**
	 * @param $query
	 *
	 * @return array
	 * @since 4.0.0
	 */
	public function countVotesPerChoices( $query );

	/**
	 * @param $query
	 *
	 * @return array
	 * @since 4.0.0
	 */
	public function countVotesPerUserAgent( $query );

	/**
	 * Count orphaned log entries.
	 *
	 * @return int
	 * @since 4.0.5
	 */
	public function countOrphaned();

	/**
	 * Delete orphaned log entries.
	 * @return int
	 * @since 4.0.5
	 */
	public function deleteOrphaned();

}