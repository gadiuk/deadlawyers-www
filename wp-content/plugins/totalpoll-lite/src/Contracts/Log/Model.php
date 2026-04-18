<?php

namespace TotalPoll\Contracts\Log;

use JsonSerializable;
use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface Model
 * @package TotalPoll\Contracts\Log
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
	 * Get log date.
	 *
	 * @return \DateTime
	 * @since 1.0.0
	 */
	public function getDate();

	/**
	 * Get log Ip.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getIp();

	/**
	 * Get user agent.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getUseragent();

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
	 * Get action.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getAction();

	/**
	 * Get status.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getStatus();

	/**
	 * Get choices.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getChoices();

	/**
	 * Get details.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getDetails();

	/**
	 * Get entry.
	 *
	 * @return \TotalPoll\Contracts\Entry\Model
	 * @since 4.0.8
	 */
	public function getEntry();
}
