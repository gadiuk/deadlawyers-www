<?php

namespace TotalPoll;

use MO;
use TotalPoll\Admin\Plugins\UninstallFeedback;
use TotalPoll\Shortcodes\RandomPoll;
use TotalPollVendors\TotalCore\Helpers\Misc;


/**
 * TotalPoll Plugin.
 *
 * @package TotalPoll
 */
class Plugin extends \TotalPollVendors\TotalCore\Foundation\Plugin {

	public function registerProviders() {
		// Bootstrap
		$this->container->share( 'bootstrap', function () {
			return new Bootstrap();
		} );


		// Custom implementation of modules repository
		$this->container->share( 'modules.repository', function () {
			return new Modules\Repository( $this->container->get( 'env' ),
			                               $this->container->get( 'admin.activation' ),
			                               $this->container->get( 'admin.account' ) );
		} );

		// Poll post type
		$this->container->share( 'polls.cpt', function () {
			return new Poll\PostType();
		} );

		// Poll post defaults
		$this->container->add( 'polls.defaults', function () {
			return [
				'uid'           => Misc::generateUid(),
				'id'            => get_the_ID(),
				'questions'     => [],
				'fields'        => [],
				'expressions'   => [],
				'vote'          => [
					'limitations' => [
						'region' => [
							'rules' => [],
						],
					],
					'frequency'   => [
						'cookies'    => [ 'enabled' => true ],
						'ip'         => [ 'enabled' => false ],
						'user'       => [ 'enabled' => false ],
						'perSession' => 1,
						'perUser'    => 1,
						'perIP'      => 1,
						'timeout'    => 3600,
					],
				],
				'choices'       => [
					'sort' => [
						'field'     => 'position',
						'direction' => 'ASC',
					],
				],
				'results'       => [
					'sort'          => [
						'field'     => 'votes',
						'direction' => 'DESC',
					],
					'visibility'    => 'all',
					'untilReaching' => null,
					'format'        => '{{votesPercentage}}',
				],
				'design'        => [
					'template'   => 'basic-template',
					'text'       => [
						'fontFamily' => 'inherit',
						'fontWeight' => 'inherit',
						'fontSize'   => 'inherit',
						'lineHeight' => 'inherit',
						'align'      => 'inherit',
						'transform'  => 'none',
					],
					'colors'     => [
						'primary'           => '#2196f3',
						'primaryContrast'   => '#ffffff',
						'primaryLighter'    => '#64b5f6',
						'primaryLight'      => '#42a5f5',
						'primaryDark'       => '#1e88e5',
						'primaryDarker'     => '#1976d2',
						'secondary'         => '#4caf50',
						'secondaryContrast' => '#ffffff',
						'secondaryLighter'  => '#a5d6a7',
						'secondaryLight'    => '#81c784',
						'secondaryDark'     => '#43a047',
						'secondaryDarker'   => '#388e3c',
						'accent'            => '#ffc107',
						'accentContrast'    => '#ffffff',
						'accentLighter'     => '#ffd54f',
						'accentLight'       => '#ffca28',
						'accentDark'        => '#ffb300',
						'accentDarker'      => '#ffa000',
						'dark'              => '#333333',
						'gray'              => '#dddddd',
						'grayContrast'      => '#333333',
						'grayLighter'       => '#fafafa',
						'grayLight'         => '#eeeeee',
						'grayDark'          => '#aaaaaa',
						'grayDarker'        => '#999999',
					],
					'layout'     => [
						'choicesPerRow'   => 1,
						'questionsPerRow' => 1,
						'maxWidth'        => '100%',
						'gutter'          => '1em',
						'radius'          => '0',
					],
					'custom'     => null,
					'behaviours' => [
						'ajax'     => true,
						'scrollUp' => true,
					],
					'effects'    => [
						'transition' => 'fade',
						'duration'   => '500',
					],
				],
				'notifications' => [
					'email' => [ 'recipient' => (string) get_option( 'admin_email' ), 'on' => [ 'newVote' => false ] ],
				],
				'meta'          => [
					'schema' => '1.0',
				],
			];
		} );

		// Poll shortcode
		$this->container->add( 'polls.shortcode', function ( $attributes, $content = null ) {
			return new Shortcodes\Poll( $attributes, $content );
		} );

		// Poll form
		$this->container->add( 'polls.form', function ( $poll ) {
			return new Poll\Form( $this->container->get( 'http.request' ),
			                      $this->container->get( 'form.factory' ),
			                      $poll );
		} );

		// Poll repository
		$this->container->share( 'polls.repository', function () {
			return new Poll\Repository( $this->container->get( 'http.request' ),
			                            $this->container->get( 'database' ),
			                            $this->container->get( 'env' ) );
		} );

		// Poll controller
		$this->container->share( 'poll.controller', function () {
			return new Poll\Controller( $this->container->get( 'http.request' ),
			                            $this->container->get( 'polls.repository' ) );
		} );

		// Poll render
		$this->container->add( 'polls.renderer', function ( $poll ) {
			return new Poll\Renderer( $poll,
			                          $this->container->get( 'modules.repository' ),
			                          $this->container->get( 'filesystem' ),
			                          $this->container->get( 'env' ) );
		} );

		// Poll commands - count vote
		$this->container->add( 'polls.commands.vote.count', function ( $poll ) {
			return new Poll\Commands\CountVote( $poll,
			                                    $this->container->get( 'http.request' ),
			                                    $this->container->get( 'polls.repository' ) );
		} );

		// Poll commands - log vote
		$this->container->add( 'polls.commands.vote.log', function ( $poll ) {
			return new Poll\Commands\LogVote( $poll, $this->container->get( 'log.repository' ) );
		} );

		// Poll commands - save entry
		$this->container->add( 'polls.commands.vote.entry', function ( $poll ) {
			return new Poll\Commands\SaveEntry( $poll, $this->container->get( 'entries.repository' ) );
		} );

		// Poll commands - notify
		$this->container->add( 'polls.commands.vote.notify.newVote', function ( $poll ) {
			return new Poll\Commands\SendNotificationsOnNewVote( $poll );
		} );


		$this->container->add( 'polls.commands.vote.notify.newChoice', function ( $poll ) {
			return new Poll\Commands\SendNotificationsOnNewChoice( $poll );
		} );
		// Log repository
		$this->container->share( 'log.repository', function () {
			return new Log\Repository( $this->container->get( 'http.request' ),
			                           $this->container->get( 'database' ),
			                           $this->container->get( 'env' ) );
		} );

		// Entry repository
		$this->container->share( 'entries.repository', function () {
			return new Entry\Repository( $this->container->get( 'http.request' ),
			                             $this->container->get( 'database' ),
			                             $this->container->get( 'env' ) );
		} );

		// Admin bootstrap
		$this->container->share( 'admin.bootstrap', function () {
			return new Admin\Bootstrap( $this->container->get( 'http.request' ), $this->container->get( 'env' ) );
		} );

		// Admin ajax bootstrap
		$this->container->share( 'admin.ajax', function () {
			return new Admin\Ajax\Bootstrap();
		} );

		// Admin ajax (dashboard)
		$this->container->share( 'admin.ajax.dashboard', function () {
			return new Admin\Ajax\Dashboard( $this->container->get( 'http.request' ),
			                                 $this->container->get( 'admin.activation' ),
			                                 $this->container->get( 'admin.account' ),
			                                 $this->container->get( 'polls.repository' ),
			                                 $this->container->get( 'entries.repository' ) );
		} );

		// Admin ajax (log)
		$this->container->share( 'admin.ajax.log', function () {
			return new Admin\Ajax\Log( $this->container->get( 'http.request' ),
			                           $this->container->get( 'log.repository' ) );
		} );

		// Admin ajax (entries)
		$this->container->share( 'admin.ajax.entries', function () {
			return new Admin\Ajax\Entries( $this->container->get( 'http.request' ),
			                               $this->container->get( 'entries.repository' ) );
		} );

		// Admin ajax (insights)
		$this->container->share( 'admin.ajax.insights', function () {
			return new Admin\Ajax\Insights( $this->container->get( 'http.request' ),
			                                $this->container->get( 'log.repository' ),
			                                $this->container->get( 'polls.repository' ) );
		} );

		// Admin ajax (modules)
		$this->container->share( 'admin.ajax.modules', function () {
			return new Admin\Ajax\Modules( $this->container->get( 'http.request' ),
			                               $this->container->get( 'modules.manager' ) );
		} );

		// Admin ajax (options)
		$this->container->share( 'admin.ajax.options', function () {
			return new Admin\Ajax\Options( $this->container->get( 'http.request' ),
			                               $this->container->get( 'migrations.migrators' ) );
		} );

		// Admin ajax (polls)
		$this->container->share( 'admin.ajax.polls', function () {
			return new Admin\Ajax\Polls( $this->container->get( 'http.request' ) );
		} );

		// Admin ajax (templates)
		$this->container->share( 'admin.ajax.templates', function () {
			return new Admin\Ajax\Templates( $this->container->get( 'http.request' ),
			                                 $this->container->get( 'modules.repository' ) );
		} );

		// Poll editor
		$this->container->share( 'admin.poll.editor', function () {
			return new Admin\Poll\Editor(
				$this->container->get( 'env' ),
				$this->container->get( 'filesystem' ),
				$this->container->get( 'polls.repository' ),
				$this->container->get( 'modules.repository' )
			);
		} );


		// Poll listing
		$this->container->share( 'admin.poll.listing', function () {
			return new Admin\Poll\Listing( $this->container->get( 'polls.repository' ),
			                               $this->container->get( 'entries.repository' ),
			                               $this->container->get( 'log.repository' ),
			                               $this->container->get( 'env' ) );
		} );

		// Entries
		$this->container->share( 'admin.pages.entries', function () {
			return new Admin\Entries\Page( $this->container->get( 'http.request' ), $this->container->get( 'env' ) );
		} );

		// Insights
		$this->container->share( 'admin.pages.insights', function () {
			return new Admin\Insights\Page( $this->container->get( 'http.request' ), $this->container->get( 'env' ) );
		} );

		// Log
		$this->container->share( 'admin.pages.log', function () {
			return new Admin\Log\Page( $this->container->get( 'http.request' ), $this->container->get( 'env' ) );
		} );

		// Modules
		$this->container->share( 'admin.pages.modules', function () {
			return new Admin\Modules\Page( $this->container->get( 'http.request' ), $this->container->get( 'env' ) );
		} );

		// Templates
		$this->container->share( 'admin.pages.templates', function () {
			return new Admin\Modules\Templates\Page( $this->container->get( 'http.request' ),
			                                         $this->container->get( 'env' ) );
		} );

		// Extensions
		$this->container->share( 'admin.pages.extensions', function () {
			return new Admin\Modules\Extensions\Page( $this->container->get( 'http.request' ),
			                                          $this->container->get( 'env' ) );
		} );

		// Options
		$this->container->share( 'admin.pages.options', function () {
			return new Admin\Options\Page( $this->container->get( 'http.request' ),
			                               $this->container->get( 'env' ),
			                               $this->container->get( 'migrations.migrators' ) );
		} );

		// Onboarding
		$this->container->share( 'admin.pages.onboarding', function () {
			return new Admin\Onboarding\Page( $this->container->get( 'http.request' ), $this->container->get( 'env' ) );
		} );

		// Dashboard
		$this->container->share( 'admin.pages.dashboard', function () {
			return new Admin\Dashboard\Page( $this->container->get( 'http.request' ),
			                                 $this->container->get( 'env' ),
			                                 $this->container->get( 'admin.activation' ),
			                                 $this->container->get( 'admin.account' ) );
		} );

		// Presets Upgrade
		$this->container->share( 'admin.pages.presets', function () {
			return new Admin\Presets\Page( $this->container->get( 'http.request' ), $this->container->get( 'env' ) );
		} );

		$this->container->share( 'admin.privacy', function () {
			return new Admin\Privacy\Policy( $this->container->get( 'env' ),
			                                 $this->container->get( 'entries.repository' ),
			                                 $this->container->get( 'log.repository' ) );
		} );

		// Schema migration
		$this->container->share( 'migrations.schema', function () {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			return new Migrations\Schema\Migrator( $this->container->get( 'env' ),
			                                       $this->container->get( 'database' ) );
		} );

		// Polls migration
		$this->container->share( 'migrations.totalpoll', function () {
			return new Migrations\Polls\TotalPoll\Migrator( $this->container->get( 'env' ) );
		} );

		$this->container->share( 'migrations.yop', function () {
			return new Migrations\Polls\YOP\Migrator( $this->container->get( 'env' ) );
		} );

		$this->container->share( 'migrations.wp-polls', function () {
			return new Migrations\Polls\WPPolls\Migrator( $this->container->get( 'env' ) );
		} );

		$this->container->share( 'migrations.migrators', function () {
			return [
				'totalpoll-3' => $this->container->get( 'migrations.totalpoll' ),
				'yop'         => $this->container->get( 'migrations.yop' ),
				'wp-polls'    => $this->container->get( 'migrations.wp-polls' ),
			];
		} );

		$this->container->share( 'decorators.structuredData', function () {
			return new Decorators\StructuredData();
		} );

		$this->container->add( 'utils.create.cache', function () {
			wp_mkdir_p( $this->application->env( 'cache.path' ) . 'css/' );
		} );

		$this->container->add( 'utils.purge.cache', function () {
			$this->container->get( 'filesystem' )->rmdir( $this->application->env( 'cache.path' ), true );
		} );

		$this->container->add( 'utils.purge.store', function () {
			delete_transient( $this->application->env( 'slug' ) . '_modules_store_response' );
		} );

		$this->container->add( 'utils.create.exports', function () {
			wp_mkdir_p( $this->application->env( 'exports.path' ) );

			if ( ! file_exists( $this->application->env( 'exports.path' ) . 'index.html' ) ) {
				$this->container->get( 'filesystem' )
				                ->touch( $this->application->env( 'exports.path' ) . 'index.html' );
			}
		} );

		// Uninstall feedback
		$this->container->add( 'uninstall', new UninstallFeedback() );


		// Expressions array
		$this->container->share( 'expressions', function () {
			return [
				'votes'       => [
					'label'       => esc_html__( 'Votes', 'totalpoll' ),
					'expressions' =>
						[
							'%s Vote' => [
								'translations' => [
									esc_html__( '%s Vote', 'totalpoll' ),
									esc_html__( '%s Votes', 'totalpoll' ),
								],
							],
						],
				],
				'buttons'     => [
					'label'       => esc_html__( 'Buttons', 'totalpoll' ),
					'expressions' =>
						[
							'Previous'            => [
								'translations' => [
									esc_html__( 'Previous', 'totalpoll' ),
								],
							],
							'Next'                => [
								'translations' => [
									esc_html__( 'Next', 'totalpoll' ),
								],
							],
							'Results'             => [
								'translations' => [
									esc_html__( 'Results', 'totalpoll' ),
								],
							],
							'Vote'                => [
								'translations' => [
									esc_html__( 'Vote', 'totalpoll' ),
								],
							],
							'Back to vote'        => [
								'translations' => [
									esc_html__( 'Back to vote', 'totalpoll' ),
								],
							],
							'Continue to vote'    => [
								'translations' => [
									esc_html__( 'Continue to vote', 'totalpoll' ),
								],
							],
							'Continue to results' => [
								'translations' => [
									esc_html__( 'Continue to results', 'totalpoll' ),
								],
							],
						],
				],
				'fields'      => [
					'label'       => esc_html__( 'Fields', 'totalpoll' ),
					'expressions' =>
						[
							'Other' => [
								'translations' => [
									esc_html__( 'Other', 'totalpoll' ),
								],
							],
						],
				],
				'errors'      => [
					'label'       => esc_html__( 'Errors', 'totalpoll' ),
					'expressions' =>
						[
							'You cannot vote again.'                                                                                 =>
								[
									'translations' => [
										esc_html__( 'You cannot vote again.', 'totalpoll' ),
									],
								],
							'You have to vote for at least one choice.'                                                              =>
								[
									'translations' => [
										esc_html__( 'You have to vote for at least one choice.', 'totalpoll' ),
										esc_html__( 'You have to vote for at least %d choices.', 'totalpoll' ),
									],
								],
							'You cannot vote for more than one choice.'                                                              =>
								[
									'translations' => [
										esc_html__( 'You cannot vote for more than one choice.', 'totalpoll' ),
										esc_html__( 'You cannot vote for more than %d choices.', 'totalpoll' ),
									],
								],
							'You have entered an invalid captcha code.'                                                              =>
								[
									'translations' => [
										esc_html__( 'You have entered an invalid captcha code.', 'totalpoll' ),
									],
								],
							'You cannot vote because the quota has been exceeded.'                                                   =>
								[
									'translations' => [
										esc_html__( 'You cannot vote because the quota has been exceeded.',
										            'totalpoll' ),
									],
								],
							'You cannot see results before voting.'                                                                  =>
								[
									'translations' => [
										esc_html__( 'You cannot see results before voting.', 'totalpoll' ),
									],
								],
							'You cannot vote because this poll has not started yet.'                                                 =>
								[
									'translations' => [
										esc_html__( 'You cannot vote because this poll has not started yet.',
										            'totalpoll' ),
									],
								],
							'You cannot vote because this poll has expired.'                                                         =>
								[
									'translations' => [
										esc_html__( 'You cannot vote because this poll has expired.', 'totalpoll' ),
									],
								],
							'You cannot vote because this poll is not available in your region.'                                     =>
								[
									'translations' => [
										esc_html__( 'You cannot vote because this poll is not available in your region.',
										            'totalpoll' ),
									],
								],
							'You cannot vote because you have insufficient rights.'                                                  =>
								[
									'translations' => [
										esc_html__( 'You cannot vote because you have insufficient rights.',
										            'totalpoll' ),
									],
								],
							'You cannot vote because you are a guest, please <a href="%s">sign in</a> or <a href="%s">register</a>.' =>
								[
									'translations' => [
										wp_kses( __( 'You cannot vote because you are a guest, please <a href="%s">sign in</a> or <a href="%s">register</a>.',
										             'totalpoll' ), [ 'a' => [ 'href' => [] ] ] ),
									],
								],
							'Voting via links has been disabled for this poll.'                                                      =>
								[
									'translations' => [
										esc_html__( 'Voting via links has been disabled for this poll.', 'totalpoll' ),
									],
								],
							'To continue, you must be a part of these roles: %s.'                                                    =>
								[
									'translations' => [
										esc_html__( 'To continue, you must be a part of these roles: %s.',
										            'totalpoll' ),
									],
								],
							'Something went wrong. Please try again.'                                                                =>
								[
									'translations' => [
										esc_html__( 'Something went wrong. Please try again.', 'totalpoll' ),
									],
								],
							'This poll has not started yet (%s left).'                                                               =>
								[
									'translations' => [
										esc_html__( 'This poll has not started yet (%s left).', 'totalpoll' ),
									],
								],
							'This poll has ended (since %s).'                                                                        =>
								[
									'translations' => [
										esc_html__( 'This poll has ended (since %s).', 'totalpoll' ),
									],
								],
						],
				],
				'validations' => [
					'label'       => esc_html__( 'Validations', 'totalpoll' ),
					'expressions' =>
						[
							'{{label}} must be a valid email address.'       => [
								'translations' => [
									esc_html__( '{{label}} must be a valid email address.', 'totalpoll' ),
								],
							],
							'{{label}} must be filled.'                      => [
								'translations' => [
									esc_html__( '{{label}} must be filled.', 'totalpoll' ),
								],
							],
							'{{label}} is not within the supported range.'   => [
								'translations' => [
									esc_html__( '{{label}} is not within the supported range.', 'totalpoll' ),
								],
							],
							'{{label}} has been used before.'                => [
								'translations' => [
									esc_html__( '{{label}} has been used before.', 'totalpoll' ),
								],
							],
							'{{label}} is not accepted.'                     => [
								'translations' => [
									esc_html__( '{{label}} is not accepted.', 'totalpoll' ),
								],
							],
							'{{label}} does not allow this value.'           => [
								'translations' => [
									esc_html__( '{{label}} does not allow this value.', 'totalpoll' ),
								],
							],
							'You must vote for at least {{minimum}} choice.' => [
								'translations' => [
									esc_html__( 'You must vote for at least {{minimum}} choice.', 'totalpoll' ),
									esc_html__( 'You must vote for at least {{minimum}} choices.', 'totalpoll' ),
								],
							],
							'You can vote for up to {{maximum}} choice.'     => [
								'translations' => [
									esc_html__( 'You can vote for up to {{maximum}} choice.', 'totalpoll' ),
									esc_html__( 'You can vote for up to {{maximum}} choices.', 'totalpoll' ),
								],
							],
						],
				],
			];
		} );

		
		// Upgrade
		$this->container->share( 'admin.pages.upgrade-to-pro', function () {
			return new Admin\Upgrade\Page( $this->container->get( 'http.request' ), $this->container->get( 'env' ) );
		} );

		$this->container->add( 'upgrade-to-pro', function () {
			$url     = esc_attr( TotalPoll()->env( 'links.upgrade-to-pro' ) );
			$tooltip = esc_html__( 'This feature is available in Pro version.', 'totalpoll' );

			echo "<a href=\"{$url}\" target=\"_blank\" class=\"totalpoll-pro-badge\" tooltip=\"${tooltip}\">Pro</a>";
		} );
		
	}

	public function registerWidgets() {
		register_widget( '\TotalPoll\Widgets\Poll' );
		register_widget( '\TotalPoll\Widgets\LatestPoll' );
		register_widget( '\TotalPoll\Widgets\RandomPoll' );
	}

	public function registerShortCodes() {
		$callback = function ( $attributes, $content = null ) {
			return (string) $this->container->get( 'polls.shortcode', [ $attributes, $content ] );
		};

		add_shortcode( 'tp-poll', $callback );
		add_shortcode( 'totalpoll', $callback );

		add_shortcode( 'totalpoll-random', function ( $attributes, $content = null ) {
			return new RandomPoll( $attributes, $content );
		} );
	}

	public function registerCustomPostTypes() {
		$this->container->get( 'polls.cpt' );
	}

	public function registerTaxonomies() {
	}


	public function loadTextDomain() {
		$expressions = (array) $this->application->option( 'expressions', [] );
		if ( ! is_admin() || Misc::isDoingAjax() ) {
			add_filter(
				'gettext_totalpoll',
				function ( $translation, $text, $domain ) use ( &$expressions ) {
					if ( ! empty( $expressions[ $text ]['translations'][0] ) ) {
						return $expressions[ $text ]['translations'][0];
					}

					return $translation;
				},
				10,
				3
			);

			add_filter(
				'ngettext_totalpoll',
				function ( $translation, $single, $plural, $number, $domain ) use ( &$expressions ) {
					if ( ! empty( $expressions[ $single ]['translations'][ $number < 2 ? 0 : 1 ] ) ) {
						return $expressions[ $single ]['translations'][ $number < 2 ? 0 : 1 ];
					}

					return $translation;
				},
				10,
				5
			);
		}

		$locale         = is_admin() && ! Misc::isDoingAjax() ? get_user_locale() : get_locale();
		$localeFallback = substr( $locale, 0, 2 );
		$mofile         = "totalpoll-{$locale}.mo";
		$mofileFallback = "totalpoll-{$localeFallback}.mo";
		$path           = $this->application->env( 'path' );
		$loaded         = load_textdomain( 'totalpoll', "{$path}languages/{$mofile}" );
		if ( ! $loaded ):
			load_textdomain( 'totalpoll', "{$path}languages/{$mofileFallback}" );
		endif;
	}

	public function onActivation( $networkWide ) {
		$sites = is_multisite() && $networkWide ? get_sites() : [ get_current_blog_id() ];

		foreach ( $sites as $site ):
			if ( is_multisite() ):
				switch_to_blog( $site->blog_id );
			endif;

			// Migrate the database
			$this->container->get( 'migrations.schema' )->migrate();

			// Register post types  & flush rewrite rules
			$this->registerCustomPostTypes();

			// Purge previous cache
			$this->container->get( 'utils.purge.cache' );
			$this->container->get( 'utils.purge.store' );

			// Create cache directories
			$this->container->get( 'utils.create.cache' );
			$this->container->get( 'utils.create.exports' );

			// Reactivate current license, if any
			$this->container->get( 'admin.activation' )->reactivateLicense();

			// Trigger action
			do_action( 'totalpoll/actions/activated', $networkWide );

			if ( is_multisite() ):
				restore_current_blog();
			endif;
		endforeach;
	}

	public function onDeactivation( $networkWide ) {
		// Flush rewrite rules
		flush_rewrite_rules();

		// Flush cache
		wp_cache_flush();

		$this->container->get( 'scheduler' )->unregister();

		// Trigger action
		do_action( 'totalpoll/actions/deactivated', $networkWide );
	}

	public static function onUninstall() {
		// Flush rewrite rules
		flush_rewrite_rules();

		$sites = is_multisite() ? get_sites() : [ get_current_blog_id() ];

		foreach ( $sites as $site ):
			if ( is_multisite() ):
				switch_to_blog( $site->blog_id );
			endif;

			$userConsent = TotalPoll()->option( 'advanced.uninstallAll' );

			if ( $userConsent ):
				// Delete tables
				$tables = array_keys( TotalPoll()->env( 'db.tables' ) );
				foreach ( $tables as $tableName ):
					$table = TotalPoll()->env( "db.tables.{$tableName}" );
					$query = "DROP TABLE IF EXISTS {$table}";
					TotalPoll( 'database' )->query( $query );
				endforeach;
				// Delete polls
				$query = new \WP_Query( [
					                        'post_type'   => [ 'poll', 'poll_preset' ],
					                        'post_status' => 'any',
					                        'fields'      => 'ids',
				                        ] );
				$polls = $query->get_posts();
				foreach ( $polls as $id ) {
					wp_delete_post( $id, true );
				}
				// Delete files
				TotalPoll( 'utils.purge.cache' );
				TotalPoll( 'utils.purge.store' );
				// Delete options
				TotalPoll( 'options' )->deleteOptions();
				delete_option( 'totalpoll_default_preset' );
			endif;

			if ( is_multisite() ):
				restore_current_blog();
			endif;
		endforeach;
		// Trigger action
		do_action( 'totalpoll/actions/uninstalled', $userConsent );
	}

	/**
	 * Bootstrap plugin.
	 */
	public function bootstrap() {
		/**
		 * Fires before bootstrapping TotalPoll.
		 *
		 * @param  Plugin  $this  Plugin instance.
		 *
		 * @since 4.0.0
		 * @order 3
		 */
		do_action( 'totalpoll/actions/before/bootstrap', $this );

		$this->container->get( 'bootstrap' );

		/**
		 * Fires after bootstrapping TotalPoll.
		 *
		 * @param  Plugin  $this  Plugin instance.
		 *
		 * @since 4.0.0
		 * @order 5
		 */
		do_action( 'totalpoll/actions/after/bootstrap', $this );

		$this->container->get( 'scheduler' )->register();
	}

	/**
	 * Bootstrap AJAX.
	 */
	public function bootstrapAjax() {
		/**
		 * Fires before bootstrapping AJAX handler.
		 *
		 * @param  Plugin  $this  Plugin instance.
		 *
		 * @since 4.0.0
		 * @order 6
		 */
		do_action( 'totalpoll/actions/before/bootstrap-ajax', $this );

		$this->container->get( 'admin.ajax' );

		/**
		 * Fires after bootstrapping AJAX handler.
		 *
		 * @param  Plugin  $this  Plugin instance.
		 *
		 * @since 4.0.0
		 * @order 8
		 */
		do_action( 'totalpoll/actions/after/bootstrap-ajax', $this );
	}

	/**
	 * Bootstrap admin.
	 */
	public function bootstrapAdmin() {
		/**
		 * Fires before bootstrapping admin.
		 *
		 * @param  Plugin  $this  Plugin instance.
		 *
		 * @since 4.0.0
		 * @order 9
		 */
		do_action( 'totalpoll/actions/before/bootstrap-admin', $this );

		$this->container->get( 'admin.bootstrap' );

		/**
		 * Fires after bootstrapping admin.
		 *
		 * @param  Plugin  $this  Plugin instance.
		 *
		 * @since 4.0.0
		 * @order 11
		 */
		do_action( 'totalpoll/actions/after/bootstrap-admin', $this );
	}

	/**
	 * Bootstrap extensions.
	 *
	 * @throws \Exception
	 */
	public function bootstrapExtensions() {
		/**
		 * Fires before bootstrapping extensions.
		 *
		 * @param  Plugin  $this  Plugin instance.
		 *
		 * @since 4.0.0
		 * @order 1
		 */
		do_action( 'totalpoll/actions/before/bootstrap-extensions', $this );

		$activatedExtension = $this->container->get( 'modules.repository' )
		                                      ->getActiveWhere( [ 'type' => 'extension' ] );
		/**
		 * Filters the list of activated extensions.
		 *
		 * @param  array  $activatedExtension  Array of extensions information.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$activatedExtension = apply_filters( 'totalpoll/filters/extensions/activated', $activatedExtension );

		// @TODO: Improve error reporting for this part.
		try {
			foreach ( $activatedExtension as $extension ):
				if ( $this->container->get( 'filesystem' )
				                     ->exists( $extension['dirName'] . '/Extension.php' ) && class_exists( $extension['class'] ) ):
					( new $extension['class'] )->run();
				else:
					throw new \RuntimeException( "Please check that \"{$extension['id']}\" extension file uses the correct namespace." );
				endif;
			endforeach;
		} catch ( \Exception $exception ) {
			if ( Misc::isDevelopmentMode() ):
				trigger_error( $exception->getMessage(), E_USER_WARNING );
			else:
				TotalPoll( 'modules.repository' )->setInactive( $extension['id'] );
			endif;
		}

		/**
		 * Fires after bootstrapping extensions.
		 *
		 * @param  Plugin  $this  Plugin instance.
		 *
		 * @since 4.0.0
		 * @order 2
		 */
		do_action( 'totalpoll/actions/after/bootstrap-extensions', $this );
	}

	/**
	 * @return array
	 */
	public function objectsCount() {
		return [
			'polls'   => TotalPoll( 'polls.repository' )->count(),
			'entries' => TotalPoll( 'entries.repository' )->count( [] ),
			'log'     => TotalPoll( 'log.repository' )->count( [] ),
			'votes'   => TotalPoll( 'polls.repository' )->countVotes(),
		];
	}


}
