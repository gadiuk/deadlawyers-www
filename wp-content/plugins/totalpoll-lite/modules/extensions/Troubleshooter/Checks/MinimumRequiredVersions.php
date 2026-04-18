<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter\Checks;

/**
 * Class MinimumRequiredVersions
 * @package TotalPoll\Modules\Extensions\Troubleshooter\Checks
 */
class MinimumRequiredVersions extends Checkup {

	/**
	 * Get checkup name.
	 *
	 * @return string
	 */
	public function getName() {
		return esc_html__( 'Minimum required versions', 'totalpoll' );
	}

	/**
	 * Get checkup description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Check minimum required versions.', 'totalpoll' );
	}

	/**
	 * @return void
	 */
	public function check() {
		$php                   = TotalPoll()->env( 'versions.php' );
		$minimumPHPVersion     = TotalPoll()->env( 'requirements.php' );
		$recommendedPHPVersion = TotalPoll()->env( 'recommended.php' );
		if ( ! version_compare( $php, $minimumPHPVersion, '>=' ) ):
			$this->addError(
				sprintf(
				 esc_html__( 'You are running PHP version %s, the minimum required version is %s.', 'totalpoll' ),
					$php, $minimumPHPVersion
				)
			);
		elseif ( ! version_compare( $php, $recommendedPHPVersion, '>=' ) ):
			$this->addWarning(
				sprintf(
				 esc_html__( 'You are running PHP version %s, the recommended version is %s.', 'totalpoll' ),
					$php, $recommendedPHPVersion
				)
			);
		endif;

		$mysql                   = TotalPoll()->env( 'versions.mysql' );
		$minimumMySqlVersion     = TotalPoll()->env( 'requirements.mysql' );
		$recommendedMySqlVersion = TotalPoll()->env( 'recommended.mysql' );

		// Ignore MariaDB
		if ( strpos( $mysql, 'maria' ) === false ) {
			if ( ! version_compare( $mysql, $minimumMySqlVersion, '>=' ) ):
				$this->addError(
					sprintf(
					 esc_html__( 'You are running MySql version %s, the minimum required version is %s.', 'totalpoll' ),
						$mysql, $minimumMySqlVersion
					)
				);
			elseif ( ! version_compare( $mysql, $recommendedMySqlVersion, '>=' ) ):
				$this->addWarning(
					sprintf(
					 esc_html__( 'You are running MySql version %s, the recommended version is %s.', 'totalpoll' ),
						$mysql, $recommendedMySqlVersion
					)
				);
			endif;
		}

		$wp                   = TotalPoll()->env( 'versions.wp' );
		$minimumWPVersion     = TotalPoll()->env( 'requirements.wp' );
		$recommendedWPVersion = TotalPoll()->env( 'recommended.wp' );

		if ( ! version_compare( $wp, $minimumWPVersion, '>=' ) ):
			$this->addError(
				sprintf(
				 esc_html__( 'You are running WordPress version %s, the minimum required version is %s.', 'totalpoll' ),
					$wp, $minimumWPVersion
				)
			);
		elseif ( ! version_compare( $wp, $recommendedWPVersion, '>=' ) ):
			$this->addWarning(
				sprintf(
				 esc_html__( 'You are running WordPress version %s, the recommended version is %s.', 'totalpoll' ),
					$wp, $recommendedWPVersion
				)
			);
		endif;

	}
}
