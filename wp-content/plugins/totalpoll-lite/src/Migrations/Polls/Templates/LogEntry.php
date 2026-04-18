<?php

namespace TotalPoll\Migrations\Polls\Templates;

use TotalPoll\Contracts\Migrations\Poll\Template\LogEntry as LogEntryContract;

/**
 * Log Migration Template.
 *
 * @package TotalPoll\Migrations\Polls\Templates
 */
class LogEntry extends Template implements LogEntryContract {
	/**
	 * Poll log entry data.
	 *
	 * @var array $data
	 */
	protected $data = [
		'ip'        => '',
		'useragent' => '',
		'user_id'   => '',
		'poll_id'   => '',
		'choices'   => [],
		'action'    => '',
		'status'    => '',
		'details'   => [],
		'date'      => '',
	];

	/**
	 * @param $ip
	 */
	public function setIp( $ip ) {
		$this->data['ip'] = $ip;
	}

	/**
	 * @param $useragent
	 */
	public function setUseragent( $useragent ) {
		$this->data['useragent'] = $useragent;
	}

	/**
	 * @param $userId
	 */
	public function setUserId( $userId ) {
		$this->data['user_id'] = $userId;
	}

	/**
	 * @param $pollId
	 */
	public function setPollId( $pollId ) {
		$this->data['poll_id'] = $pollId;
	}

	/**
	 * @param $action
	 */
	public function setAction( $action ) {
		$this->data['action'] = $action;
	}

	/**
	 * @param $date
	 */
	public function setDate( $date ) {
		$this->data['date'] = $date;
	}

	/**
	 * @param $status
	 */
	public function setStatus( $status ) {
		$this->data['status'] = $status;
	}

	/**
	 * @param $choiceUid
	 */
	public function addChoice( $choiceUid ) {
		$this->data['choices'][] = $choiceUid;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function addDetail( $key, $value ) {
		$this->data['details'][ $key ] = $value;
	}
}