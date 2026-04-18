<?php

namespace TotalPoll\Contracts\Notification;

use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface Model
 * @package TotalPoll\Contracts\Notification
 */
interface Model extends \JsonSerializable, \ArrayAccess, Arrayable {

	/**
	 * @param $subject
	 *
	 * @return mixed
	 */
	public function setSubject( $subject );

	/**
	 * @param $body
	 *
	 * @return mixed
	 */
	public function setBody( $body );

	/**
	 * @param $from
	 *
	 * @return mixed
	 */
	public function setFrom( $from );

	/**
	 * @param $to
	 *
	 * @return mixed
	 */
	public function setTo( $to );

	/**
	 * @param $replyTo
	 *
	 * @return mixed
	 */
	public function setReplyTo( $replyTo );

	/**
	 * @return mixed
	 */
	public function getSubject();

	/**
	 * @return mixed
	 */
	public function getBody();

	/**
	 * @return mixed
	 */
	public function getFrom();

	/**
	 * @return mixed
	 */
	public function getTo();

	/**
	 * @return mixed
	 */
	public function getReplyTo();

}