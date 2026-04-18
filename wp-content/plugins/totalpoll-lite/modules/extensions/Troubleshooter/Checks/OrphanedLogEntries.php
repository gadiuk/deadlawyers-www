<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter\Checks;

/**
 * Class OrphanLogEntries
 * @package TotalPoll\Modules\Extensions\Troubleshooter\Checks
 */
class OrphanedLogEntries extends Checkup {

	/**
	 * Get checkup name.
	 *
	 * @return string
	 */
	public function getName() {
		return esc_html__( 'Orphaned log entries', 'totalpoll' );
	}

	/**
	 * Get checkup description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Check if database contains orphaned log entries.', 'totalpoll' );
	}

	/**
	 * @return void
	 */
	public function check() {
		$count = (int) TotalPoll( 'log.repository' )->countOrphaned();

		if ( $count ):
			$this->addError(
				sprintf(
				 esc_html__( '<code>~%s</code> orphaned entries.', 'totalpoll' ),
					number_format( $count )
				)
			);
		endif;
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
		TotalPoll( 'log.repository' )->deleteOrphaned();
		// Check again.
		$this->check();
	}
}
