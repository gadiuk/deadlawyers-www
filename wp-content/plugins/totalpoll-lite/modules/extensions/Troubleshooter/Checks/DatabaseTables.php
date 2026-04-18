<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter\Checks;

/**
 * Class CheckDatabase
 * @package TotalPoll\Modules\Extensions\Troubleshooter\Checks
 */
class DatabaseTables extends Checkup {

	/**
	 * Get checkup name.
	 *
	 * @return string
	 */
	public function getName() {
		return esc_html__( 'Database tables', 'totalpoll' );
	}

	/**
	 * Get checkup description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Check if database tables are created and operating.', 'totalpoll' );
	}

	/**
	 * @return void
	 */
	public function check() {
		// Get existing tables.
		$existingTables = TotalPoll( 'database' )->get_results( 'SHOW TABLE STATUS', ARRAY_A );
		$existingTables = array_map(
			function ( $table ) {
				return $table['Name'];
			},
			$existingTables
		);

		// Get TotalPoll tables.
		$tables = TotalPoll()->env( 'db.tables' );

		// Check existence.
		foreach ( $tables as $tableName ):
			if ( ! in_array( $tableName, $existingTables ) ):
				$this->addError(
					sprintf(
					 esc_html__( '<code>%s</code> table is not created.', 'totalpoll' ),
						$tableName
					)
				);
			endif;
		endforeach;
	}

	/**
	 * @return bool
	 */
	public function isFixable() {
		return true;
	}

	/**
	 * @return void
	 */
	public function fix() {
		// Run database migration again.
		TotalPoll( 'migrations.schema' )->migrate();
		// Check again.
		$this->check();
	}
}
