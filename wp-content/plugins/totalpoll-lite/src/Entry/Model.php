<?php

namespace TotalPoll\Entry;

use TotalPoll\Contracts\Entry\Model as ModelContract;
use TotalPollVendors\TotalCore\Helpers\Arrays;

/**
 * Entry Model.
 * @package TotalPoll\Entry
 * @since   1.0.0
 */
class Model implements ModelContract {
	/**
	 * Entry ID.
	 *
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $id = null;

	/**
	 * Entry date.
	 *
	 * @var \DateTime
	 * @since 1.0.0
	 */
	protected $date = null;

	/**
	 * Entry user ID.
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $userId = null;

	/**
	 * Entry user.
	 * @var \WP_User
	 * @since 1.0.0
	 */
	protected $user = null;

	/**
	 * Entry log ID.
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $logId = null;

	/**
	 * Entry log.
	 * @var \TotalPoll\Contracts\Log\Model|null
	 * @since 1.0.0
	 */
	protected $log = null;

	/**
	 * Entry poll ID.
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $pollId = null;

	/**
	 * Entry poll.
	 * @var \TotalPoll\Contracts\Poll\Model
	 * @since 1.0.0
	 */
	protected $poll = null;

	/**
	 * Entry fields.
	 * @var array
	 * @since 1.0.0
	 */
	protected $fields = [];

	/**
	 * Entry details.
	 * @var array
	 * @since 1.0.0
	 */
	protected $details = [];

	/**
	 * Model constructor.
	 *
	 * @param $entry array Entry attributes.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $entry ) {
		if ( is_array( $entry ) ):
			$this->id      = (int) $entry['id'];
			$this->userId  = (int) $entry['user_id'];
			$this->pollId  = (int) $entry['poll_id'];
			$this->logId   = (int) $entry['log_id'];
			$this->fields  = array_map(function ($value){
				return is_array($value) ? implode(', ', Arrays::apply($value, 'esc_html')) : esc_html($value);
			},  (array) json_decode( $entry['fields'], true, 512, JSON_UNESCAPED_UNICODE ));
			$this->details = (array) json_decode( $entry['details'], true, 512, JSON_UNESCAPED_UNICODE );
			$this->date    = TotalPoll( 'datetime', [ $entry['date'] ] );
			$this->user    = new \WP_User( $this->userId );
		endif;
	}

	/**
	 * Get poll id.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get entry date.
	 *
	 * @return \DateTime
	 * @since 1.0.0
	 */
	public function getDate() {
		return $this->date;
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
		if ( $this->poll === null ) {
			$this->poll = TotalPoll( 'polls.repository' )->getById( $this->pollId );
		}

		return $this->poll;
	}

	/**
	 * Get log ID.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getLogId() {
		return $this->logId;
	}

	/**
	 * Get log model.
	 *
	 * @return \TotalPoll\Contracts\Log\Model
	 * @since 1.0.0
	 */
	public function getLog() {
		if ( $this->log === null ) {
			$this->log = TotalPoll( 'log.repository' )->getById( $this->logId );
		}

		return $this->log;
	}

	/**
	 * Get fields.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Get fields.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getField( $name, $default = null ) {
		return Arrays::getDeep( $this->fields, [ $name ], $default );
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
			'id'      => $this->id,
			'user'    => $this->userId ? [
				'id'    => $this->userId,
				'login' => $this->user->user_login,
				'name'  => $this->user->display_name,
				'email' => $this->user->user_email,
			] : [ 'id' => $this->userId ],
			'poll_id' => $this->pollId,
			'log_id'  => $this->logId,
			'fields'  => $this->fields,
			'details' => $this->details,
			'date'    => $this->date->format( DATE_ATOM ),
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
