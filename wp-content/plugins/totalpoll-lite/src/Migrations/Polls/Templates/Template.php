<?php

namespace TotalPoll\Migrations\Polls\Templates;

use TotalPoll\Contracts\Migrations\Poll\Template\Template as TemplateContract;

/**
 * Template.
 * @package TotalPoll\Migrations\Polls\Templates
 */
class Template implements TemplateContract {
	protected $id;
	protected $newId;
	protected $data = [];

	/**
	 * @param $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param $newId
	 */
	public function setNewId( $newId ) {
		$this->newId = $newId;
	}

	/**
	 * @return mixed
	 */
	public function getNewId() {
		return $this->newId;
	}

	/**
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
		return array_merge( $this->data, [ 'id' => $this->getId(), 'newId' => $this->getNewId() ] );
	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->data[ $offset ] );
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed|null
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->data[ $offset ] ) ? $this->data[ $offset ] : null;
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->data[ $offset ] = $value;
	}

	/**
	 * @param mixed $offset
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		unset( $this->data[ $offset ] );
	}
}
