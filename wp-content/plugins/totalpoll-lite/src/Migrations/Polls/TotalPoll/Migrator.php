<?php

namespace TotalPoll\Migrations\Polls\TotalPoll;

use TotalPoll\Migrations\Polls\Load;

/**
 * TotalPoll 3 Migrator.
 * @package TotalPoll\Migrations\Polls\TotalPoll
 */
class Migrator extends \TotalPoll\Migrations\Polls\Migrator {
	/**
	 * Migrator constructor.
	 *
	 * @param array $env
	 */
	public function __construct( $env ) {
		parent::__construct( $env, new Extract(), new Transform(), new Load() );
	}

	/**
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'name'  => 'TotalPoll 3.0',
			'image' => $this->env['url'] . 'assets/dist/images/migration/totalpoll-3.png',
			'done'  => $this->getMigratedCount(),
			'total' => $this->getCount(),
		];
	}
}
