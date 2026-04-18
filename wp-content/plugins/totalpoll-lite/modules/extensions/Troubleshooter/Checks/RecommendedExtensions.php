<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter\Checks;

/**
 * Class RecommendedExtensions
 * @package TotalPoll\Modules\Extensions\Troubleshooter\Checks
 */
class RecommendedExtensions extends Checkup {

	/**
	 * Get checkup name.
	 *
	 * @return string
	 */
	public function getName() {
		return esc_html__( 'Recommended extensions', 'totalpoll' );
	}

	/**
	 * Get checkup description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Check if recommended PHP extensions are installed.', 'totalpoll' );
	}

	/**
	 * @return void
	 */
	public function check() {
		if ( ! extension_loaded( 'mbstring' ) ):
			$this->addWarning(
			 esc_html__( 'mbstring is not enabled. A polyfill will be used instead.', 'totalpoll' )
			);
		endif;

		if ( ! function_exists( 'curl_init' ) ):
			$this->addWarning(
			 esc_html__( 'curl is not enabled. A polyfill will be used instead.', 'totalpoll' )
			);
		endif;

		if ( ! extension_loaded( 'json' ) ):
			$this->addWarning(
			 esc_html__( 'json is not enabled. A polyfill will be used instead.', 'totalpoll' )
			);
		endif;

		if ( ! extension_loaded( 'json' ) ):
			$this->addWarning(
			 esc_html__( 'json is not enabled. A polyfill will be used instead.', 'totalpoll' )
			);
		endif;
	}

}
