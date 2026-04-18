<?php

namespace TotalPoll\Contracts\Preset;

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
	 * @return Model[]
	 *@since 4.0.0
	 */
	public function get( $query );

	/**
	 * Get poll by id.
	 *
	 * @param $presetId
	 *
	 * @return Model|null
	 * @since 4.0.0
	 */
	public function getById( $presetId );
}