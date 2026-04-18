<?php

namespace TotalPoll\Admin\Onboarding;

use TotalPollVendors\TotalCore\Admin\Pages\Page as TotalCoreAdminPage;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Helpers\Arrays;

/**
 * Class Page
 *
 * @package TotalPoll\Admin\Onbarding
 */
class Page extends TotalCoreAdminPage {

	protected $content = [];

	/**
	 * Page constructor.
	 *
	 * @param  Request  $request
	 * @param  array  $env
	 */
	public function __construct( Request $request, $env ) {
		$onboarding = get_option( 'totalpoll_onboarding', [] );

		if ( Arrays::getDotNotation( $onboarding,
		                             'status',
		                             'init' ) !== 'init' && current_user_can( 'manage_options' ) ) {
			wp_redirect( admin_url( 'edit.php?post_type=poll&page=dashboard' ) );
			exit();
		}
		parent::__construct( $request, $env );

		$this->content = [
			'welcome' => [
				'title'       => esc_html__( 'Hey mate!', 'totalpoll' ),
				'description' => esc_html__( 'We are delighted to see you started using TotalPoll. <br> TotalPoll will impress you, we promise!',
				                             'totalpoll' ),
				'benefits'    => [
					[
						'icon'        => 'touch_app',
						'title'       => esc_html__( 'User Friendly', 'totalpoll' ),
						'description' => esc_html__( 'The user-friendly interface enables you to create polls in no time.',
						                             'totalpoll' ),
					],
					[
						'icon'        => 'style',
						'title'       => esc_html__( 'Elegant Design', 'totalpoll' ),
						'description' => esc_html__( 'A good-looking poll could help you achieve a better response rate.',
						                             'totalpoll' ),
					],
					[
						'icon'        => 'power',
						'title'       => esc_html__( 'Flexibility & Extensibility', 'totalpoll' ),
						'description' => esc_html__( 'Whether text-based or media-based polls, TotalPoll got you covered.',
						                             'totalpoll' ),
					],
				],
			],
			'start'   => [
				'title'       => esc_html__( '🎓<br>Get started', 'totalpoll' ),
				'description' => esc_html__( 'We\'ve prepared some materials for you to ease your learning curve.',
				                             'totalpoll' ),
				'posts'       => [
					[
						'thumbnail'   => esc_attr( $this->env['url'] ) . 'assets/dist/images/onboarding/create.svg',
						'title'       => esc_html__( 'How to create a poll', 'totalpoll' ),
						'description' => esc_html__( 'Learn how to create your first poll using TotalPoll.',
						                             'totalpoll' ),
						'url'         => 'https://totalsuite.net/documentation/totalpoll/basics/create-first-poll-using-totalpoll-for-wordpress/',
					],
					[
						'thumbnail'   => esc_attr( $this->env['url'] ) . 'assets/dist/images/onboarding/integrate.svg',
						'title'       => esc_html__( 'How to integrate a poll', 'totalpoll' ),
						'description' => esc_html__( 'Lean how to integrate a poll into your site using different methods.',
						                             'totalpoll' ),
						'url'         => 'https://totalsuite.net/documentation/totalpoll/basics/publishing-poll-using-totalpoll-wordpress/',
					],
					[
						'thumbnail'   => esc_attr( $this->env['url'] ) . 'assets/dist/images/onboarding/customize.svg',
						'title'       => esc_html__( 'How to customize the appearance of a poll', 'totalpoll' ),
						'description' => esc_html__( 'Learn how to customize the appearance of a poll to match your brand.',
						                             'totalpoll' ),
						'url'         => 'https://totalsuite.net/documentation/totalpoll/basics/design-customization-totalpoll-wordpress/',
					],
				],
			],
			'connect' => [
				'title'       => esc_html__( '🤝 <br> Happy to e-meet you!', 'totalpoll' ),
				'description' => esc_html__( "Let's go beyond business, let's be friends!", 'totalpoll' ),
			],
			'addons'  => [
				'title'       => esc_html__( '🍒 <br> Featured add-ons', 'totalpoll' ),
				'description' => esc_html__( "Do even more with a set of powerful add-ons for TotalPoll.",
				                             'totalpoll' ),
			],
			'finish'  => [
				'title'       => esc_html__( '🙌<br>Almost done!', 'totalpoll' ),
				'description' => esc_html__( "We'd like to collect some anonymous usage information that will help us shape up TotalPoll.",
				                             'totalpoll' ),
			],
		];
	}

	/**
	 * Page assets.
	 */
	public function assets() {
		/**
		 * @asset-script totalpoll-admin-onboarding
		 */
		wp_enqueue_script( 'totalpoll-admin-onboarding' );

		wp_enqueue_style( 'material-font', 'https://fonts.googleapis.com/icon?family=Material+Icons' );

		wp_localize_script( 'totalpoll-admin-onboarding',
		                    'TotalpollFeaturedModules',
		                    TotalPoll( 'modules.repository' )->getAllStore() );
		wp_localize_script( 'totalpoll-admin-onboarding',
		                    'TotalpollDashboard',
		                    [ 'url' => admin_url( 'edit.php?post_type=poll&page=dashboard' ) ] );
	}

	protected function getContent( $key, $default = null ) {
		return Arrays::getDotNotation( $this->content, $key, $default );
	}

	/**
	 * Page content.
	 */
	public function render() {
		include __DIR__ . '/views/index.php';
	}
}
