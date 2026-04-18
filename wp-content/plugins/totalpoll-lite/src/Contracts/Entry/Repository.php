<?php

namespace TotalPoll\Contracts\Entry;

/**
 * Poll repository
 * @package TotalPoll\Entry
 * @since   1.0.0
 */
interface Repository {
	/**
	 * Get entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get( $query );

	/**
	 * Get entries.
	 *
	 * @param $entryId
	 *
	 * @return Model|null
	 * @since 1.0.0
	 */
	public function getById( $entryId );

	/**
	 * Get entry by log ID.
	 *
	 * @param int $logId
	 *
	 * @return Model|null
	 * @since 4.0.8
	 */
	public function getByLogId( $logId );

	/**
	 * Create entry.
	 *
	 * @param $attributes
	 *
	 * @return array|\WP_Error
	 * @since 1.0.0
	 */
	public function create( $attributes );

	/**
	 * Delete entries.
	 *
	 * @param $query array
	 *
	 * @return bool|\WP_Error
	 * @since 1.0.0
	 */
	public function delete( $query );

	/**
	 * Count entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function count( $query );

	/**
	 * Anonymize entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function anonymize( $query );

	/**
	 * purge all entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function purgeAllEntries();
	/**
	 * purge entries of a poll.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */

	public function purge($poll);




}
