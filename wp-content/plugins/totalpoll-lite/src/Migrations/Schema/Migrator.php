<?php

namespace TotalPoll\Migrations\Schema;

use TotalPollVendors\TotalCore\Contracts\Foundation\Environment;
use TotalPollVendors\TotalCore\Helpers\Strings;
use wpdb;

/**
 * Schema Migrator.
 * @package TotalPoll\Migrations\Schema
 */
class Migrator {
	/**
	 * @var Environment $env
	 */
	protected $env;
	/**
	 * @var wpdb $db
	 */
	protected $db;

	/**
	 * Migrator constructor.
	 *
	 * @param Environment $env
	 * @param wpdb        $db
	 */
	public function __construct( $env, $db ) {
		$this->env = $env;
		$this->db  = $db;
	}

	/**
	 * Migrate schema.
	 *
	 */
	public function migrate() {
		$this->migrate400();

		update_option( $this->env['db.option-key'], $this->env['db.version'] );
	}

	protected function migrate400() {
		$createLogTable     = file_get_contents( __DIR__ . '/migrations/2018_12_00_12_24_create_log_table.sql' );
		$createVotesTable   = file_get_contents( __DIR__ . '/migrations/2018_12_00_12_26_create_votes_table.sql' );
		$createEntriesTable = file_get_contents( __DIR__ . '/migrations/2018_12_00_12_25_create_entries_table.sql' );

		$createLogTable     = Strings::template( $createLogTable, [ 'db' => $this->env['db'] ] );
		$createVotesTable   = Strings::template( $createVotesTable, [ 'db' => $this->env['db'] ] );
		$createEntriesTable = Strings::template( $createEntriesTable, [ 'db' => $this->env['db'] ] );

		dbDelta( $createLogTable );
		dbDelta( $createVotesTable );
		dbDelta( $createEntriesTable );
	}
}