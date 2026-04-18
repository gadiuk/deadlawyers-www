<?php

namespace TotalPoll\Contracts\Entry;

use JsonSerializable;
use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface Model
 * @package TotalPoll\Contracts\Entry
 */
interface Model extends \ArrayAccess, Arrayable, JsonSerializable {
	/**
	 * Get poll id.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getId();

	/**
	 * Get entry date.
	 *
	 * @return \DateTime
	 * @since 1.0.0
	 */
	public function getDate();

	/**
	 * Get user ID.
	 *
	 * @return int|null
	 * @since 1.0.0
	 */
	public function getUserId();

	/**
	 * Get user.
	 *
	 * @return \WP_User
	 * @since 1.0.0
	 */
	public function getUser();

	/**
	 * Get poll ID.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getPollId();

	/**
	 * Get poll model.
	 *
	 * @return \TotalPoll\Contracts\Poll\Model
	 * @since 1.0.0
	 */
	public function getPoll();

	/**
	 * Get log ID.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getLogId();

	/**
	 * Get log model.
	 *
	 * @return \TotalPoll\Contracts\Log\Model
	 * @since 1.0.0
	 */
	public function getLog();

	/**
	 * Get fields.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getFields();

	/**
	 * Get fields.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getField( $name, $default = null );

	/**
	 * Get details.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getDetails();
}