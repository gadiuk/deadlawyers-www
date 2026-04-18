<?php

namespace TotalPoll\Admin\Ajax;

! defined( 'ABSPATH' ) && exit();


use TotalPoll\Contracts\Migrations\Poll\Migrator;
use TotalPollVendors\TotalCore\Contracts\Http\Request;

/**
 * Class Options
 * @package TotalPoll\Admin\Ajax
 */
class Options {
	/**
	 * @var Migrator[] $migrators
	 */
	protected $migrators;
	/**
	 * @var Request $request
	 */
	protected $request;

	/**
	 * Page constructor.
	 *
	 * @param Request    $request
	 * @param Migrator[] $migrators
	 */
	public function __construct( Request $request, $migrators ) {
		$this->request   = $request;
		$this->migrators = $migrators;
	}

	/**
	 * Save options.
	 */
	public function saveOptions() {
		$options = json_decode( $this->request->post( 'options', '{}' ), true );
		if ( ! empty( $options ) ):
			TotalPoll( 'options' )->setOptions( $options, true );
			wp_schedule_single_event( time(), 'totalpoll/actions/urls/flush' );
		endif;
		wp_send_json_success( esc_html__( 'Saved.', 'totalpoll' ) );
	}

	/**
	 * Purge.
	 */
	public function purge() {
		$type = $this->request->request( 'type', 'cache' );
		if ( $type === 'cache' ):
			TotalPoll( 'utils.purge.cache' );
			TotalPoll( 'utils.purge.store' );
		endif;
		wp_send_json_success( esc_html__( 'Purged.', 'totalpoll' ) );
	}

	/**
	 * Migrate polls AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_options_migrate_polls
	 */
	public function migratePolls() {
		$plugin = $this->request->post( 'plugin' );

		if ( ! isset( $this->migrators[ $plugin ] ) ):
			wp_send_json_error( esc_html__( 'Plugin is not supported.', 'totalpoll' ) );
		endif;

		$migrator   = $this->migrators[ $plugin ];
		$pollsCount = $migrator->getCount();

		$migrator->migrate();

		wp_send_json_success( [ 'done' => $migrator->getMigratedCount(), 'of' => $pollsCount ] );
	}

}
