<?php

namespace TotalPoll\Contracts\Migrations\Poll\Template;

use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface Template
 * @package TotalPoll\Contracts\Migrations\Poll\Template
 */
interface Template extends \JsonSerializable, Arrayable, \ArrayAccess {
	/**
	 * @param $id
	 */
	public function setId( $id );

	/**
	 * @return mixed
	 */
	public function getId();

	/**
	 * @param $newId
	 */
	public function setNewId( $newId );

	/**
	 * @return mixed
	 */
	public function getNewId();
}