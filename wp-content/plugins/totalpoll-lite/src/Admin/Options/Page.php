<?php

namespace TotalPoll\Admin\Options;

use TotalPoll\Contracts\Migrations\Poll\Migrator;
use TotalPollVendors\TotalCore\Admin\Pages\Page as AdminPageContract;
use TotalPollVendors\TotalCore\Contracts\Foundation\Environment;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Helpers\Misc;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Page
 *
 * @package TotalPoll\Admin\Options
 */
class Page extends AdminPageContract {
	/**
	 * @var Migrator[] $migrators
	 */
	protected $migrators;

	/**
	 * Options.
	 *
	 * @var array $options
	 */
	protected $options;

	/**
	 * Page constructor.
	 *
	 * @param  Request  $request
	 * @param  Environment  $env
	 * @param  Migrator[]  $migrators
	 */
	public function __construct( Request $request, $env, $migrators ) {
		parent::__construct( $request, $env );
		$this->migrators = $migrators;
		$this->options   = TotalPoll( 'options' )->getOptions();

		if ( empty( $this->options ) ) :
			$this->options = null;
		endif;
	}

	/*
	 * Get user-interface expressions.
	 */
	public function getExpressions() {
		/**
		 * Filters the list of expressions that are available through the interface to override.
		 *
		 * @param  array  $expressions  Array of expressions.
		 *
		 *
		 * @return array
		 * @since 4.0.0
		 */
		return apply_filters(
			'totalpoll/filters/admin/options/expressions',
			TotalPoll()->container( 'expressions' )
		);
	}

	/**
	 * Enqueue assets.
	 */
	public function assets() {
		// TotalPoll
		wp_enqueue_script( 'totalpoll-admin-options' );
		wp_enqueue_style( 'totalpoll-admin-options' );

		wp_localize_script( 'totalpoll-admin-options', 'TotalPollExpressions', $this->getExpressions() );
		wp_localize_script( 'totalpoll-admin-options',
		                    'TotalPollSavedExpressions',
		                    Misc::getJsonOption( 'totalpoll_expressions' ) );
		wp_localize_script( 'totalpoll-admin-options', 'TotalPollOptions', $this->options );
		wp_localize_script( 'totalpoll-admin-options', 'TotalPollDebugInformation', Misc::getDebugInfo() );
		wp_localize_script( 'totalpoll-admin-options', 'TotalPollMigrationPlugins', $this->migrators );
	}

	public function render() {
		/**
		 * Filters the list of tabs in options page.
		 *
		 * @param  array  $tabs  Array of tabs [id => [label, icon, file]].
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$tabs = apply_filters(
			'totalpoll/filters/admin/options/tabs',
			[
				'general'       => [ 'label' => esc_html__( 'General', 'totalpoll' ), 'icon' => 'admin-settings' ],
				'performance'   => [ 'label' => esc_html__( 'Performance', 'totalpoll' ), 'icon' => 'performance' ],
				'services'      => [ 'label' => esc_html__( 'Services', 'totalpoll' ), 'icon' => 'cloud' ],
				'sharing'       => [ 'label' => esc_html__( 'Sharing', 'totalpoll' ), 'icon' => 'share' ],
				'advanced'      => [ 'label' => esc_html__( 'Advanced', 'totalpoll' ), 'icon' => 'admin-generic' ],
				'notifications' => [ 'label' => esc_html__( 'Notifications', 'totalpoll' ), 'icon' => 'email' ],
				'expressions'   => [ 'label' => esc_html__( 'Expressions', 'totalpoll' ), 'icon' => 'admin-site' ],
				'migration'     => [ 'label' => esc_html__( 'Migration', 'totalpoll' ), 'icon' => 'migrate' ],
				'import-export' => [ 'label' => esc_html__( 'Import & Export', 'totalpoll' ), 'icon' => 'update' ],
				'debug'         => [ 'label' => esc_html__( 'Debug', 'totalpoll' ), 'icon' => 'info' ],
			]
		);

		include_once __DIR__ . '/views/index.php';
	}
}
