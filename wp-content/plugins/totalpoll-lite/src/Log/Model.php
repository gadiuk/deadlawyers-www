<?php

namespace TotalPoll\Log;

use TotalPoll\Contracts\Log\Model as ModelContract;

/**
 * Log Model.
 * @package TotalPoll\Log
 * @since   1.0.0
 */
class Model implements ModelContract {
	/**
	 * Log ID.
	 *
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $id = null;

	/**
	 * Log date.
	 *
	 * @var \DateTime
	 * @since 1.0.0
	 */
	protected $date = null;

	/**
	 * Log IP.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $ip = null;

	/**
	 * Log user agent.
	 * @var string
	 * @since 1.0.0
	 */
	protected $useragent = null;

	/**
	 * Log user ID.
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $userId = null;

	/**
	 * Log user.
	 * @var \WP_User
	 * @since 1.0.0
	 */
	protected $user = null;

	/**
	 * Log poll ID.
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $pollId = null;

	/**
	 * Log poll.
	 * @var \TotalPoll\Contracts\Poll\Model
	 * @since 1.0.0
	 */
	protected $poll = null;

	/**
	 * Log action.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $action = null;

	/**
	 * Log status.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $status = null;
	/**
	 * Log choices.
	 * @var array
	 * @since 1.0.0
	 */
	protected $choices = [];

	/**
	 * Log details.
	 * @var array
	 * @since 1.0.0
	 */
	protected $details = [];

	/**
	 * Log entry.
	 * @var \TotalPoll\Contracts\Entry\Model
	 * @since 4.0.8
	 */
	protected $entry = null;

	/**
	 * Model constructor.
	 *
	 * @param $log array Log attributes.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $log ) {
		if ( is_array( $log ) ):
			$this->id        = (int) $log['id'];
			$this->ip        = (string) $log['ip'];
			$this->useragent = trim( $log['useragent'] );
			$this->userId    = (int) $log['user_id'];
			$this->pollId    = (int) $log['poll_id'];
			$this->action    = (string) $log['action'];
			$this->status    = (string) $log['status'];
			$this->choices   = (array) json_decode( $log['choices'], true, 512, JSON_UNESCAPED_UNICODE );
			$this->details   = (array) json_decode( $log['details'], true, 512, JSON_UNESCAPED_UNICODE );
			$this->date      = TotalPoll( 'datetime', [ $log['date'] ] );
			$this->user      = new \WP_User( $this->userId );
			$this->poll      = TotalPoll( 'polls.repository' )->getById( $this->pollId );
			$this->entry     = TotalPoll( 'entries.repository' )->getByLogId( $this->id );
		endif;
	}

	/**
	 * Get poll id.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getId() {
		return (int) $this->id;
	}

	/**
	 * Get log date.
	 *
	 * @return \DateTime
	 * @since 1.0.0
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Get log Ip.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getIp() {
		return $this->ip;
	}

	/**
	 * Get user agent.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getUseragent() {
		return $this->useragent;
	}

	/**
	 * Get user ID.
	 *
	 * @return int|null
	 * @since 1.0.0
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * Get user.
	 *
	 * @return \WP_User
	 * @since 1.0.0
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Get poll ID.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getPollId() {
		return $this->pollId;
	}

	/**
	 * Get poll model.
	 *
	 * @return \TotalPoll\Contracts\Poll\Model
	 * @since 1.0.0
	 */
	public function getPoll() {
		return $this->poll;
	}

	/**
	 * Get action.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Get status.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Get choices.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getChoices() {
		return $this->choices;
	}

	/**
	 * Get details.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getDetails() {
		return $this->details;
	}

	/**
	 * Get entry.
	 *
	 * @return array
	 * @since 4.0.8
	 */
	public function getEntry() {
		return $this->entry;
	}

	/**
	 * Get Serializable JSON of this instance
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'id'        => $this->id,
			'ip'        => $this->ip,
			'useragent' => $this->useragent,
			'user'      => $this->userId ? [
				'id'    => $this->userId,
				'login' => $this->user->user_login,
				'name'  => $this->user->display_name,
				'email' => $this->user->user_email,
			] : [ 'id' => $this->userId ],
			'poll'      => $this->getPoll(),
			'choices'   => $this->choices,
			'entry'     => $this->getEntry(),
			'action'    => ucfirst( $this->action ),
			'status'    => ucfirst( $this->status ),
			'details'   => $this->details,
			'date'      => $this->date->format( DATE_ATOM ),
		];
	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->{$offset} );
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->{$offset} ) ? $this->{$offset} : null;
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {

	}

	/**
	 * @param mixed $offset
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {

	}
}
