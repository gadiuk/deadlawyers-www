<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter\Checks;

/**
 * Class TotalPoll3Data
 * @package TotalPoll\Modules\Extensions\Troubleshooter\Checks
 */
class TotalPoll3Data extends Checkup {

	/**
	 * Get checkup name.
	 *
	 * @return string
	 */
	public function getName() {
		return esc_html__( 'TotalPoll 3.x data', 'totalpoll' );
	}

	/**
	 * Get checkup description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Check if database contains old data of TotalPoll 3.x.', 'totalpoll' );
	}

	/**
	 * @return void
	 */
	public function check() {
		/**
		 * @var $db \wpdb
		 */
		$db = TotalPoll( 'database' );
		// Count query.
		$query = "SELECT COUNT(*) AS MP_COUNTS FROM `{$db->postmeta}` WHERE `meta_key` LIKE '_mp_logs%' OR `meta_key` LIKE '_mp_submission%' OR `meta_key` LIKE '_tp_unique%'";
		$count = (int) $db->get_var( $query );

		if ( $count ):
			$this->addError(
				sprintf(
				 esc_html__( '<code>~%s</code> removable entries. <strong>Please note fixing this issue is an irreversible action.</strong>', 'totalpoll' ),
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
		/**
		 * @var $db \wpdb
		 */
		$db = TotalPoll( 'database' );
		// Delete query.
		$query = "DELETE FROM `{$db->postmeta}` WHERE `meta_key` LIKE '_mp_logs%' OR `meta_key` LIKE '_mp_submission%' OR `meta_key` LIKE '_tp_unique%'";
		// Run query.
		$db->query( $query );
		// Check again.
		$this->check();
	}
}
