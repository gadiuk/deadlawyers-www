<?php

namespace TotalPoll\Admin\Ajax;

use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Contracts\Modules\Manager;

/**
 * Class Modules
 * @package TotalPoll\Admin\Ajax
 * @since   1.0.0
 */
class Modules {
	/**
	 * @var array $module
	 */
	protected $module = [ 'id' => null, 'type' => null ];
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var Manager $manager
	 */
	protected $manager;

	/**
	 * Modules constructor.
	 *
	 * @param Request $request
	 * @param Manager $manager
	 */
	public function __construct( Request $request, Manager $manager ) {
		$this->request = $request;
		$this->manager = $manager;

		$this->module['id']   = $this->request->post( 'id' );
		$this->module['type'] = $this->request->post( 'type' );

		if ( $this->module['id'] && $this->module['type'] && ! in_array( $this->module['type'], [ 'extension', 'template' ] ) ):
			wp_send_json_error( new \WP_Error( 'unknown_module_type', 'Unknown module type.' ) );
		endif;
	}

	/**
	 * Install from file AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_modules_install_from_file
	 */
	public function installFromFile() {
		$result = $this->manager->install( $this->request->file( 'module' ) );

		if ( is_wp_error( $result ) ):
			wp_send_json_error( $result->get_error_message() );
		else:
			wp_send_json_success( esc_html__( 'Module installed.', 'totalpoll' ) );
		endif;
	}

	/**
	 * Install from store endpoint AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_modules_install_from_store
	 */
	public function installFromStore() {
		$result = $this->manager->installFromStore( $this->module['id'] );

		if ( is_wp_error( $result ) ):
			wp_send_json_error( $result->get_error_message() );
		else:
			wp_send_json_success( esc_html__( 'Module downloaded and installed.', 'totalpoll' ) );
		endif;
	}

	/**
	 * Get AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_modules_list
	 */
	public function fetch() {
		$hard = $this->request->request( 'hard', false );
		if ( ! empty( $hard ) ):
			TotalPoll( 'utils.purge.store' );
		endif;

		$modules = array_values( $this->manager->fetch() );

		/**
		 * Filters modules sent to modules manager interface.
		 *
		 * @param \TotalPollVendors\TotalCore\Modules\Module[] $modules Array of modules.
		 * @param Manager                                      $manager Modules manager.
		 * @param Request                                      $request HTTP Request.
		 *
		 * @since 4.0.2
		 * @return array
		 */
		$modules = apply_filters( 'totalpoll/filters/admin/modules/fetch', $modules, $this->manager, $this->request );

		wp_send_json( $modules );
	}

	/**
	 * Update AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_modules_update
	 */
	public function update() {
		$result = $this->manager->update( $this->module['id'] );

		if ( is_wp_error( $result ) ):
			wp_send_json_error( $result->get_error_message() );
		else:
			wp_send_json_success( esc_html__( 'Module updated.', 'totalpoll' ) );
		endif;
	}

	/**
	 * Uninstall AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_modules_uninstall
	 */
	public function uninstall() {
		$uninstalled = $this->manager->uninstall( $this->module['id'] );

		if ( is_wp_error( $uninstalled ) ):
			wp_send_json_error( $uninstalled->get_error_message() );
		else:
			wp_send_json_success( esc_html__( 'Module uninstalled.', 'totalpoll' ) );
		endif;
	}

	/**
	 * Activate AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_modules_activate
	 */
	public function activate() {
		$activated = $this->manager->activate( $this->module['id'] );

		if ( is_wp_error( $activated ) ):
			wp_send_json_error( $activated->get_error_message() );
		else:
			wp_send_json_success( esc_html__( 'Module activated.', 'totalpoll' ) );
		endif;
	}

	/**
	 * Deactivate AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_modules_deactivate
	 */
	public function deactivate() {
		$deactivated = $this->manager->deactivate( $this->module['id'] );

		if ( is_wp_error( $deactivated ) ):
			wp_send_json_error( $deactivated->get_error_message() );
		else:
			wp_send_json_success( esc_html__( 'Module deactivated.', 'totalpoll' ) );
		endif;
	}
}
