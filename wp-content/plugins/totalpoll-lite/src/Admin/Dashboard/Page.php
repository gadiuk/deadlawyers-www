<?php

namespace TotalPoll\Admin\Dashboard;

use TotalPollVendors\TotalCore\Admin\Pages\Page as TotalCoreAdminPage;
use TotalPollVendors\TotalCore\Contracts\Admin\Account;
use TotalPollVendors\TotalCore\Contracts\Admin\Activation;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Page
 * @package TotalPoll\Admin\Dashboard
 */
class Page extends TotalCoreAdminPage {
	/**
	 * @var Activation $activation
	 */
	protected $activation;
	/**
	 * @var Account $activation
	 */
	protected $account;

	/**
	 * Page constructor.
	 *
	 * @param Request    $request
	 * @param array      $env
	 * @param Activation $activation
	 * @param Account    $account
	 */
	public function __construct( Request $request, $env, Activation $activation, Account $account ) {

		$onboarding = get_option('totalpoll_onboarding', []);

//		if(Arrays::getDotNotation($onboarding, 'status', 'init') === 'init' && current_user_can('manage_options')) {
//			wp_redirect(admin_url('edit.php?post_type=poll&page=onboarding'));
//			exit();
//		}

		parent::__construct( $request, $env );
		$this->activation = $activation;
		$this->account    = $account;
	}

	/**
	 * Page assets.
	 */
	public function assets() {
		/**
		 * @asset-script totalpoll-admin-dashboard
		 */
		wp_enqueue_script( 'totalpoll-admin-dashboard' );
		/**
		 * @asset-style totalpoll-admin-dashboard
		 */
		wp_enqueue_style( 'totalpoll-admin-dashboard' );

		// Tweets preset
		$tweets = [
			'I\'m happy with #TotalPoll plugin for #WordPress! https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
			'#TotalPoll is a powerful plugin for #WordPress. https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
			'#TotalPoll is one of the best poll plugins for #WordPress out there. https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
			'You\'re looking for a poll plugin for #WordPress? You should give #TotalPoll a try. https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
			'I recommend #TotalPoll plugin for #WordPress webmasters. https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
			'Check out #TotalPoll, a powerful poll plugin for #WordPress. https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
			'Create closed contests and public polls easily with #TotalPoll for #WordPress. https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
			'Run a debate easily on your #WordPress powered website using #TotalPoll. https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
			'Boost user engagement with your website using #TotalPoll plugin for #WordPress https://totalsuite.net/product/totalpoll/?utm_source=in-app&utm_medium=twitter&utm_campaign=totalpoll',
		];
		// Support
		$support = [
			'sections' => [
				[
					'title'       => 'Basics',
					'description' => 'The basics of TotalPoll',
					'url'         => '#',
					'links'       => [
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/basics/create-first-poll-using-totalpoll-for-wordpress/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Create your first poll' ],
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/basics/adding-poll-questions-choices-totalpoll-wordpress/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Adding questions and choices' ],
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/basics/custom-fields-basics-totalpoll-wordpress/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Custom fields basics' ],
					],
				],
				[
					'title'       => 'Advanced',
					'description' => 'Do more with TotalPoll',
					'url'         => '#',
					'links'       => [
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/advanced/supported-drag-drop-operations-totalpoll/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Supported drag and drop operations' ],
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/advanced/reorganizing-poll-content/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Voting frequency' ],
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/advanced/vote-limitations-frequency-settings/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Vote limitations' ],
					],
				],
				[
					'title'       => 'Codex',
					'description' => 'For developers',
					'url'         => '#',
					'links'       => [
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/codex/filters-reference/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Filters reference' ],
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/codex/actions-reference/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Actions reference' ],
						[ 'url' => 'https://totalsuite.net/documentation/totalpoll/codex/helpers-reference/?utm_source=in-app&utm_medium=support-tab&utm_campaign=totalpoll', 'title' => 'Helpers reference' ],
					],
				],
			],
		];
		wp_localize_script( 'totalpoll-admin-dashboard', 'TotalPollPresets', [ 'tweets' => $tweets ] );
		wp_localize_script( 'totalpoll-admin-dashboard', 'TotalPollActivation', $this->activation->toArray() );
		wp_localize_script( 'totalpoll-admin-dashboard', 'TotalPollAccount', $this->account->toArray() );
		wp_localize_script( 'totalpoll-admin-dashboard', 'TotalPollSupport', $support );
	}

	/**
	 * Page content.
	 */
	public function render() {
		include __DIR__ . '/views/index.php';
	}
}
