<?php

namespace TotalPoll\Migrations\Polls;

use Exception;
use TotalPoll\Contracts\Migrations\Poll\Extract;
use TotalPoll\Contracts\Migrations\Poll\Load;
use TotalPoll\Contracts\Migrations\Poll\Migrator as MigratorContract;
use TotalPoll\Contracts\Migrations\Poll\Transform;
use TotalPoll\Migrations\Polls\Templates\Poll;

/**
 * Polls Migrator.
 * @package TotalPoll\Migrations\Polls
 */
abstract class Migrator implements MigratorContract {

	/**
	 * @var array $env
	 */
	protected $env;
	/**
	 * @var Extract $extract
	 */
	protected $extract;
	/**
	 * @var Transform $transform
	 */
	protected $transform;
	/**
	 * @var Load $load
	 */
	protected $load;

	/**
	 * Migrator constructor.
	 *
	 * @param array     $env
	 * @param Extract   $extract
	 * @param Transform $transform
	 * @param Load      $load
	 */
	public function __construct( $env, Extract $extract, Transform $transform, Load $load ) {
		$this->env       = $env;
		$this->extract   = $extract;
		$this->transform = $transform;
		$this->load      = $load;
	}

	/**
	 * Count polls to migrate.
	 * @return int
	 */
	public function getCount() {
		return $this->extract->getCount();
	}

	/**
	 * @return int
	 */
	public function getMigratedCount() {
		return count( $this->extract->getMigratedPollsIds() );
	}

	/**
	 * Migrate polls.
	 *
	 * @param callable $onProgress Progress callback.
	 *
	 * @return Poll[]
	 */
	public function migrate( $onProgress = null ) {
		// Disable filter temporary
		remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );

		$total       = $this->getCount();
		$oldPolls    = $this->extract->getPolls();
		$loadedPolls = [];
		foreach ( $oldPolls as $pollIndex => $poll ):
			try {
				$transformedPoll = $this->transform->transformPoll( $poll );
				$loadedPoll      = $this->load->loadPoll( $transformedPoll );
				if ( ! is_wp_error( $loadedPoll ) ):
					$loadedPolls[] = $loadedPoll;
				else:
					continue;
				endif;

				$oldLogs = $this->extract->getLogEntries( $transformedPoll );
				foreach ( $oldLogs as $logEntry ):
					$transformedLogEntry = $this->transform->transformLog( $logEntry );
//					$this->load->loadLogEntry( $transformedPoll, $transformedLogEntry );
				endforeach;

				$oldSubmissions = $this->extract->getSubmissions( $transformedPoll );
				foreach ( $oldSubmissions as $submission ):
					$transformedSubmission = $this->transform->transformSubmission( $submission );
//					$this->load->loadSubmission( $transformedPoll, $transformedSubmission );
				endforeach;

				if ( is_callable( $onProgress ) ):
					call_user_func( $onProgress, ( $pollIndex + 1 ), $total );
				endif;
			} catch ( Exception $exception ) {

			}
		endforeach;

		$oldOptions         = $this->extract->getOptions();
		$transformedOptions = $this->transform->transformOptions( $oldOptions );
//		$this->load->loadOptions( $transformedOptions );

		return $loadedPolls;
	}
}
