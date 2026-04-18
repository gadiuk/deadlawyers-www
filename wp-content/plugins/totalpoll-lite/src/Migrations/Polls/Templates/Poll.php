<?php

namespace TotalPoll\Migrations\Polls\Templates;

use TotalPoll\Contracts\Migrations\Poll\Template\Poll as PollContract;
use TotalPollVendors\TotalCore\Helpers\Arrays;

/**
 * Poll Migration Template.
 * @package TotalPoll\Migrations\Polls\Templates
 */
class Poll extends Template implements PollContract {
	/**
	 * Poll's data.
	 *
	 * @var array $data
	 */
	protected $data = [];

	/**
	 * Poll constructor.
	 */
	public function __construct() {
		$this->data = TotalPoll( 'polls.defaults' ) ?: [];
	}

	/**
	 * Set poll title.
	 *
	 * @param $title
	 */
	public function setTitle( $title ) {
		$this->data['title'] = $title;
	}

	/**
	 * Get poll title.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->data['title'] ?: '';
	}

	/**
	 * Add question to poll.
	 *
	 * @param $question
	 */
	public function addQuestion( $question ) {
		$this->data['questions'] = Arrays::setDotNotation( $this->data['questions'], count( $this->data['questions'] ), $question );
	}

	/**
	 * Add custom field to poll.
	 *
	 * @param $field
	 */
	public function addField( $field ) {
		$this->data['fields'] = Arrays::setDotNotation( $this->data['fields'], count( $this->data['fields'] ), $field );
	}

	/**
	 * Add settings section to poll.
	 *
	 * @param $section
	 * @param $value
	 */
	public function addSettings( $section, $value ) {
		$this->data = Arrays::setDotNotation( $this->data, $section, $value );
	}
}