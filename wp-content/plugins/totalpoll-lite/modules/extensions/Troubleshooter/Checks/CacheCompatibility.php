<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter\Checks;

/**
 * Class CacheCompatibility
 * @package TotalPoll\Modules\Extensions\Troubleshooter\Checks
 */
class CacheCompatibility extends Checkup {

	/**
	 * Get checkup name.
	 *
	 * @return string
	 */
	public function getName() {
		return esc_html__( 'Cache compatibility', 'totalpoll' );
	}

	/**
	 * Get checkup description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Check if TotalPoll is operating under cache mechanism.', 'totalpoll' );
	}

	/**
	 * @return void
	 */
	public function check() {
		if ( defined( 'WP_CACHE' ) && WP_CACHE && ! TP_ASYNC ):
			$this->addWarning(
				wp_kses(
					sprintf(
						__( 'It seems like your website is using a cache mechanism. If you facing issues, please consider enabling <a target="_blank" href="%s">asynchronous loading</a>.', 'totalpoll' ),
						admin_url( 'edit.php?post_type=poll&page=options&tab=options>performance' )
					),
					[ 'a' => [ 'href' => [] ] ]
				)

			);
		endif;
	}

}
