<?php

namespace TotalPoll\Migrations\Polls\Templates;

use TotalPoll\Contracts\Migrations\Poll\Template\Submission as SubmissionContract;

/**
 * Submission Migration Template.
 *
 * @package TotalPoll\Migrations\Polls\Templates
 */
class Submission extends Template implements SubmissionContract {
	/**
	 * Poll log entry data.
	 *
	 * @var array $data
	 */
	protected $data = [
		'poll_id' => '',
		'log_id'  => '',
		'user_id' => '',
		'date'    => '',
		'fields'  => [],
		'details' => [],
	];

	/**
	 * @param $pollId
	 */
	public function setPollId( $pollId ) {
		$this->data['poll_id'] = $pollId;
	}

	/**
	 * @param $logId
	 */
	public function setLogId( $logId ) {
		$this->data['log_id'] = $logId;
	}

	/**
	 * @param $userId
	 */
	public function setUserId( $userId ) {
		$this->data['user_id'] = $userId;
	}

	/**
	 * @param $date
	 */
	public function setDate( $date ) {
		$this->data['date'] = $date;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function addField( $key, $value ) {
		$this->data['fields'][ $key ] = $value;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function addDetail( $key, $value ) {
		$this->data['details'][ $key ] = $value;
	}
}