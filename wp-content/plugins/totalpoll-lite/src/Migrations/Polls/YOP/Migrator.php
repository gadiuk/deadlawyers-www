<?php

namespace TotalPoll\Migrations\Polls\YOP;

use TotalPoll\Migrations\Polls\Load;

/**
 * YOP Poll Migrator.
 * @package TotalPoll\Migrations\Polls\YOP
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
			'name'  => 'YOP Poll',
			'image' => $this->env['url'] . 'assets/dist/images/migration/yop-poll.png',
			'done'  => $this->getMigratedCount(),
			'total' => $this->getCount(),
		];
	}


	public function migrate( $onProgress = null ) {
		$polls = parent::migrate( $onProgress );

		$ids = $this->extract->getMigratedPollsIds();

		foreach ( $polls as $poll ):
			$ids[] = $poll->getId();
		endforeach;

		update_option( 'yop_poll_migrated', $ids );

		return $polls;
	}
}
