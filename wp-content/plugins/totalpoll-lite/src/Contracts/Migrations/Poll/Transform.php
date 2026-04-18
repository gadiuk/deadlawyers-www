<?php

namespace TotalPoll\Contracts\Migrations\Poll;

/**
 * Interface Transform
 * @package TotalPoll\Contracts\Migrations\Poll
 */
interface Transform {
	/**
	 * Transform poll.
	 *
	 * @param $raw
	 *
	 * @return mixed
	 */
	public function transformPoll( $raw );

	/**
	 * Transform options.
	 *
	 * @param $raw
	 *
	 * @return mixed
	 */
	public function transformOptions( $raw );

	/**
	 * Transform log.
	 *
	 * @param $raw
	 *
	 * @return mixed
	 */
	public function transformLog( $raw );

	/**
	 * Transform submission.
	 *
	 * @param $raw
	 *
	 * @return mixed
	 */
	public function transformSubmission( $raw );
}