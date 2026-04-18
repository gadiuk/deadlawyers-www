<?php

namespace TotalPoll\Poll;

use TotalPoll\Contracts\Modules\Repository as ModulesRepository;
use TotalPoll\Contracts\Poll\Model as PollModel;
use TotalPoll\Modules\Template;
use TotalPollVendors\TotalCore\Contracts\Foundation\Environment;
use TotalPollVendors\TotalCore\Helpers\Html;
use TotalPollVendors\TotalCore\Helpers\Misc;
use TotalPollVendors\TotalCore\Helpers\Strings;

/**
 * Poll Renderer.
 * @package TotalPoll\Poll
 */
class Renderer {
	/**
	 * @var PollModel
	 */
	protected $poll;
	/**
	 * @var ModulesRepository
	 */
	protected $modulesRepository;
	/**
	 * @var Template
	 */
	protected $templateInstance;
	/**
	 * @var Environment
	 */
	protected $env;
	/**
	 * @var \WP_Filesystem_Base
	 */
	protected $filesystem;

	public function __construct( PollModel $poll, ModulesRepository $modulesRepository, \WP_Filesystem_Base $filesystem, Environment $env ) {
		$this->poll              = $poll;
		$this->modulesRepository = $modulesRepository;
		$this->env               = $env;
		$this->filesystem        = $filesystem;
	}


	/**
	 * Load template.
	 *
	 * @param $templateId
	 *
	 * @return Template|\TotalPoll\Modules\Templates\Basic\Template
	 */
	public function loadTemplate( $templateId ) {
		if ( $this->templateInstance === null ):
			$options = [ 'poll' => $this->poll ];
			// Theme template
			$themeTemplateFile = get_template_directory() . '/totalpoll/Template.php';
			if ( file_exists( $themeTemplateFile ) ):
				include_once $themeTemplateFile;

				$themeTemplateClass = '\\TotalPoll\\Modules\\Templates\\ThemeTemplate\\Template';
				if ( class_exists( $themeTemplateClass ) ):
					$this->templateInstance = new $themeTemplateClass( $options );
				endif;
			// Regular template
			else:
				$module = $this->modulesRepository->get( $templateId );

				if ( $module && class_exists( $module['class'] ) ):
					$this->templateInstance = new $module['class']( $options );
				else:
					$this->templateInstance = new \TotalPoll\Modules\Templates\Basic\Template( $options );
				endif;
			endif;
		endif;

		return $this->templateInstance;
	}

	/**
	 * Render poll.
	 *
	 * @return string
	 */
	public function render() {
		$template = $this->loadTemplate( $this->poll->getTemplateId() );
		/**
		 * Filters the template used for poll rendering.
		 *
		 * @param Template $template Template object.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return Template
		 * @since 4.0.0
		 */
		$template = apply_filters( 'totalpoll/filters/render/template', $template, $this->poll, $this );

		$screen = $this->poll->getScreen();
		if ( $this->poll->isWelcomeScreen() && ! $this->poll->hasWelcomeContent() ):
			$screen = 'vote';
		endif;

		if ( $this->poll->isThankYouScreen() && ! $this->poll->hasThankyouContent() ):
			$screen = 'results';
		endif;

		if ( $this->poll->isVoteScreen() && ! defined( 'DONOTCACHEPAGE' ) ):
			define( 'DONOTCACHEPAGE', true );
		endif;

		/**
		 * Filters the poll screen when rendering.
		 *
		 * @param string $screen Poll screen name.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		$screen = apply_filters( 'totalpoll/filters/render/screen', $screen, $this->poll, $this );

		$this->poll->setScreen( $screen );

		$templateVars = [
			'poll'     => $this->poll,
			'form'     => $this->poll->getForm(),
			'screen'   => $screen,
			'template' => $template,
		];
		/**
		 * Filters template variables passed to views.
		 *
		 * @param array $templateVars Template variables.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$templateVars = apply_filters( 'totalpoll/filters/render/vars', $templateVars, $this->poll, $this );

		$cssClasses   = [];
		$cssClasses[] = is_rtl() ? 'is-rtl' : 'is-ltr';
		$cssClasses[] = "is-screen-{$screen}";

		if ( function_exists( 'is_embed' ) && is_embed() ):
			$cssClasses[] = 'is-embed';
		endif;

		if ( is_preview() ):
			$cssClasses[] = 'is-preview';
		endif;

		if ( is_user_logged_in() ):
			$cssClasses[] = 'is-logged-in';
		endif;

		/**
		 * Filters css classes of poll container.
		 *
		 * @param array $cssClasses Css classes.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$cssClasses = apply_filters( 'totalpoll/filters/render/classes', $cssClasses, $this->poll, $this );

		/**
		 * Filters template markup
		 *
		 * @param string $view View.
		 * @param PollModel $poll Poll model object.
		 * @param string $screen Poll screen.
		 * @param Renderer $render Renderer object.
		 *
		 * @return string
		 * @since 4.0.1
		 */
		$view = apply_filters( 'totalpoll/filters/render/view', $template->getView( $screen, $templateVars ), $this->poll, $screen, $this );

		/**
		 * Filters template markup
		 *
		 * @param string $markup Poll wrapper markup.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		$markup = apply_filters(
			'totalpoll/filters/render/markup',
			'<div id="totalpoll" class="totalpoll-wrapper totalpoll-uid-{{uid}} {{container.classes}}" totalpoll="{{poll.id}}" totalpoll-uid="{{uid||\'none\'}}" totalpoll-screen="{{poll.screen}}" totalpoll-ajax-url="{{ajax}}">{{before}}{{config}}{{css}}{{js}}<div id="totalpoll-poll-{{poll.id}}" class="totalpoll-container">{{view}}</div>{{after}}</div>',
			$this->poll,
			$this
		);

		$before = '';
		$after  = '';

		if ( TotalPoll()->option( 'general.showCredits.enabled' ) ):
			$link = add_query_arg(
				[
					'utm_source'   => 'powered-by',
					'utm_medium'   => 'footer',
					'utm_campaign' => 'totalpoll',
				],
				$this->env['links.website']
			);

			$after .= new Html(
				'div',
				[
					'class' => 'totalpoll-credits',
					'style' => 'font-family: sans-serif; font-size: 9px; text-transform: uppercase;text-align: center; padding: 10px 0;'
				],
				sprintf(
				 esc_html__( 'Powered by %s', 'totalpoll' ),
					'TotalPoll'
				)
			);
		endif;

		/**
		 * Filters the final arguments passed to render function.
		 *
		 * @param array $args Arguments.
		 *
		 * @return array
		 * @since 4.0.1
		 */
		$args     = apply_filters( 'totalpoll/filters/render/args', [
			'uid'       => $this->poll->getPresetUid(),
			'poll'      => [
				'id'     => $this->poll->getId(),
				'screen' => $screen,
			],
			'container' => [
				'classes' => implode( ' ', $cssClasses ),
			],
			'css'       => $this->getCss(),
			'js'        => $this->getJs(),
			'config'    => $this->getConfig(),
			'view'      => Strings::template($view, $this->poll->getViewVariables()),
			'ajax'      => $this->poll->getAjaxUrl(),
			'before'    => $before,
			'after'     => $after,
		], $this->poll, $this );
		$rendered = Strings::template( $markup, $args );

		/**
		 * Fires when poll is rendering.
		 *
		 * @param string $rendered Rendered poll.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $renderer Renderer object.
		 *
		 * @since  4.0.3
		 */
		do_action( 'totalpoll/actions/render', $rendered, $this->poll, $this );

		/**
		 * Filters the rendered output.
		 *
		 * @param string $rendered Rendered poll.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/render/output', $rendered, $this->poll, $this );
	}

	/**
	 * Poll config.
	 *
	 * @return string
	 */
	public function getConfig() {
		$config = [
			'ajaxEndpoint' => add_query_arg( [ 'action' => 'totalpoll' ], admin_url( 'admin-ajax.php' ) ),
			'behaviours'   => array_merge( $this->poll->getSettingsItem( 'design.behaviours', [] ), [ 'async' => ! Misc::isDoingAjax() && defined( 'TP_ASYNC' ) && TP_ASYNC ] ),
			'effects'      => $this->poll->getSettingsItem( 'design.effects', [] ),
			'i18n'         => [
				'Previous'                                => esc_html__( 'Previous', 'totalpoll' ),
				'Next'                                    => esc_html__( 'Next', 'totalpoll' ),
				'of'                                      => esc_html__( 'of', 'totalpoll' ),
				'Something went wrong! Please try again.' => esc_html__( 'Something went wrong! Please try again.', 'totalpoll' ),
			],
		];

		/**
		 * Filters poll config that will passed to frontend controller.
		 *
		 * @param array $config Config variables.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$config = apply_filters( 'totalpoll/filters/render/config', $config, $this->poll, $this );

		return sprintf( '<script type="text/totalpoll-config" totalpoll-config="%1$d">%2$s</script>', $this->poll->getId(), json_encode( $config ) );
	}

	/**
	 * Poll css.
	 *
	 * @return string
	 */
	protected function getCss() {
		$presetUid     = $this->poll->getPresetUid();
		$cachedCssFile = "css/{$presetUid}.css";

		$css = sprintf(
			'<link rel="stylesheet" id="totalpoll-poll-%s-css"  href="%s" type="text/css" media="all" />',
			$presetUid,
			$this->env['cache']['url'] . $cachedCssFile
		);

		$inlineCss = TotalPoll()->option( 'advanced.inlineCss' );
		if ( $inlineCss || Misc::isDevelopmentMode() || ! $this->filesystem->is_readable( $this->env['cache']['path'] . $cachedCssFile ) ):
			TotalPoll( 'utils.create.cache' );
			$compileArgs = array_merge( $this->poll->getSettingsItem( 'design', [] ), [ 'uid' => $this->poll->getPresetUid() ] );

			/**
			 * Filters the arguments passed for CSS compiling.
			 *
			 * @param array $args Arguments.
			 * @param Renderer $renderer Renderer.
			 * @param Model $poll Poll model.
			 *
			 * @return array
			 * @since 4.0.1
			 */
			$compileArgs = apply_filters( 'totalpoll/filters/render/css-args', $compileArgs, $this, $this->poll );
			$compiledCss = $this->templateInstance->getCompiledCss( $compileArgs ) . $this->poll->getSettingsItem( 'design.css' );

			if ( ! $inlineCss && $this->filesystem->is_writable( $this->env['cache']['path'] . 'css/' ) ):
				$this->filesystem->put_contents( $this->env['cache']['path'] . $cachedCssFile, $compiledCss );
			else:
				$css = "<style type=\"text/css\">{$compiledCss}</style>";
			endif;
		endif;

		/**
		 * Filters poll CSS.
		 *
		 * @param string $css CSS Code.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		$css = apply_filters( 'totalpoll/filters/render/css', $css, $this->poll, $this );

		return $css;
	}

	/**
	 * Get JS.
	 */
	protected function getJs() {
		wp_enqueue_script( 'totalpoll-poll', "{$this->env['url']}assets/dist/scripts/frontend/totalpoll.js", [ 'jquery' ], $this->env['version'] );

		/**
		 * Filters poll JS.
		 *
		 * @param string $js JS code.
		 * @param PollModel $poll Poll model object.
		 * @param Renderer $render Renderer object.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/render/js', '', $this->poll, $this );
	}

	/**
	 * @return Template
	 * @since 4.0.4
	 */
	public function getTemplateInstance() {
		return $this->templateInstance;
	}

	/**
	 * Render shortcut.
	 *
	 * @return string
	 */
	public function __toString() {
		return (string) $this->render();
	}
}
