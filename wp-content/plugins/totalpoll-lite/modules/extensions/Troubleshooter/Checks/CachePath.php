<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter\Checks;

/**
 * Class CachePath
 * @package TotalPoll\Modules\Extensions\Troubleshooter\Checks
 */
class CachePath extends Checkup {

	/**
	 * Get checkup name.
	 *
	 * @return string
	 */
	public function getName() {
		return esc_html__( 'Cache path', 'totalpoll' );
	}

	/**
	 * Get checkup description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Check if cache path is writable.', 'totalpoll' );
	}

	/**
	 * @return void
	 */
	public function check() {
		$path = TotalPoll()->env( 'cache.path' );

		if ( ! wp_is_writable( $path ) ):
			$this->addError(
				sprintf(
				 esc_html__( 'Cache path <code>%s</code> is not writable.', 'totalpoll' ),
					$path
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
		TotalPoll( 'utils.create.cache' );
		$this->check();
	}
}
