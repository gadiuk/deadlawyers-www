<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter;

use TotalPoll\Modules\Extensions\Troubleshooter\Checks\CacheCompatibility;
use TotalPoll\Modules\Extensions\Troubleshooter\Checks\CachePath;
use TotalPoll\Modules\Extensions\Troubleshooter\Checks\DatabaseTables;
use TotalPoll\Modules\Extensions\Troubleshooter\Checks\MinimumRequiredVersions;
use TotalPoll\Modules\Extensions\Troubleshooter\Checks\OrphanedLogEntries;
use TotalPoll\Modules\Extensions\Troubleshooter\Checks\RecommendedExtensions;
use TotalPoll\Modules\Extensions\Troubleshooter\Checks\TotalPoll3Data;

/**
 * Class Extension
 * @package TotalPoll\Modules\Extensions\Troubleshooter
 */
class Extension extends \TotalPoll\Modules\Extension {
	protected $root = __FILE__;
	protected $tests = [
		MinimumRequiredVersions::class,
		RecommendedExtensions::class,
		DatabaseTables::class,
		CachePath::class,
		TotalPoll3Data::class,
		CacheCompatibility::class,
		OrphanedLogEntries::class,
	];

	/**
	 * Run the extension.
	 *
	 * @return void
	 */
	public function run() {
		// Menu
		add_action( 'admin_menu', [ $this, 'menu' ], 99 );

		// Init
		add_action( 'admin_init', [ $this, 'init' ] );

		// Assets
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
	}

	public function init() {
		foreach ( $this->tests as $index => $test ):
			$this->tests[ $index ] = new $test();
		endforeach;
	}

	public function menu() {
		add_submenu_page(
			'edit.php?post_type=' . TP_POLL_CPT_NAME,
		 esc_html__( 'Troubleshooter', 'totalpoll' ),
		 esc_html__( 'Troubleshooter', 'totalpoll' ),
			'manage_options',
			'troubleshooter',
			[ $this, 'content' ]
		);
	}

	public function assets() {
		if ( $GLOBALS['current_screen']->base !== 'poll_page_troubleshooter' ):
			return;
		endif;

		wp_enqueue_script(
			'totalpoll-troubleshooter',
			$this->getUrl( 'assets/scripts/troubleshooter.js' ),
			[ 'angular', 'angular-resource' ]
		);
		wp_enqueue_style(
			'totalpoll-troubleshooter',
			$this->getUrl( 'assets/styles/troubleshooter.css' ),
			[ 'totalpoll-admin-totalcore' ]
		);
		wp_localize_script( 'totalpoll-troubleshooter', 'TotalPollTests', $this->tests );
	}

	public function content() {
		include __DIR__ . '/views/page.php';
	}
}
