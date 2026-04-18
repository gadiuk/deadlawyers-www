<?php

namespace TotalPoll\Contracts\Migrations\Poll\Template;

/**
 * Interface Poll
 * @package TotalPoll\Contracts\Migrations\Poll\Template
 */
interface Poll extends Template {
	/**
	 * Add question.
	 *
	 * @param $question
	 *
	 * @return mixed
	 */
	public function addQuestion( $question );

	/**
	 * Add field.
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function addField( $field );

	/**
	 * Add settings.
	 *
	 * @param $section
	 * @param $value
	 *
	 * @return mixed
	 */
	public function addSettings( $section, $value );

	/**
	 * Set poll title.
	 *
	 * @param $title
	 *
	 * @return void
	 */
	public function setTitle( $title );

	/**
	 * Get poll title.
	 *
	 * @return string
	 */
	public function getTitle();

}