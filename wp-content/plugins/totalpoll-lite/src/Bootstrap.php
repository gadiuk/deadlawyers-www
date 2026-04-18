<?php

namespace TotalPoll;

use TotalPoll\Contracts\Poll\Model;
use TotalPoll\Log\Export;
use TotalPollVendors\TotalCore\Helpers\Misc;

/**
 * Bootstrap TotalPoll.
 *
 * @package TotalPoll
 */
class Bootstrap {

	public function __construct() {
		// Define asynchronous loading state (enbaled | disabled)
		define( 'TP_ASYNC', (bool) TotalPoll()->option( 'performance.async.enabled' ) );

		// Process CPT related options (disabled archive for example).
		if ( ! is_admin() && TotalPoll()->option( 'advanced.disableArchive' ) ):
			add_action( 'pre_get_posts', [ $this, 'disableArchive' ] );
		endif;

		// Listen to flush rewrite rules requests
		add_action( 'totalpoll/actions/urls/flush', 'flush_rewrite_rules' );

		// Structured Data
		if ( TotalPoll()->option( 'general.structuredData.enabled' ) ):
			TotalPoll( 'decorators.structuredData' );
		endif;

		// Requests
		if ( isset( $_REQUEST['totalpoll']['action'] ) ):
			// Capture actions
			add_action( 'wp', [ $this, 'route' ], 11 );
			add_action( 'wp_ajax_totalpoll', [ $this, 'route' ] );
			add_action( 'wp_ajax_nopriv_totalpoll', [ $this, 'route' ] );
		endif;

		// Prepare posts and shortcodes
		add_action( 'wp', [ $this, 'preparePoll' ] );

		// Privacy
		TotalPoll( 'admin.privacy' );

		// Export
		add_action( Export::ACTION_NAME, [ Export::class, 'process' ] );

		/**
		 * Fires when TotalPoll complete the bootstrap phase.
		 *
		 * @since 4.0.0
		 * @order 4
		 */
		do_action( 'totalpoll/actions/bootstrap' );
	}

	/**
	 * Process requests.
	 *
	 * @since 1.0.0
	 */
	public function route() {
		TotalPoll( 'poll.controller' );

		$action = stripslashes( (string) $_REQUEST['totalpoll']['action'] );
		$method = strtolower( filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) ?: filter_var( $_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_STRING ) );
		/**
		 * Fires before processing a request.
		 *
		 * @param string $method HTTP method.
		 * @param string $action Action name.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/request', $action, $method );
		/**
		 * Fires when TotalPoll receives a request.
		 *
		 * @param string $method HTTP method.
		 * @param string $action Action name.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/request', $action, $method );
		/**
		 * Fires when TotalPoll receives a request (specific HTTP method and action name).
		 *
		 * @param string $method HTTP method.
		 * @param string $action Action name.
		 *
		 * @since 4.0.0
		 */
		do_action( "totalpoll/actions/request/{$method}/{$action}", $action, $method );
		/**
		 * Fires when TotalPoll receives a request (specific action name).
		 *
		 * @param string $method HTTP method.
		 * @param string $action Action name.
		 *
		 * @since 4.0.0
		 */
		do_action( "totalpoll/actions/request/{$action}", $action, $method );
		/**
		 * Fires after processing a request.
		 *
		 * @param string $method HTTP method.
		 * @param string $action Action name.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/request', $action, $method );

		if ( Misc::isDoingAjax() ):
			/**
			 * Fires when TotalPoll receives an AJAX request.
			 *
			 * @param string $method HTTP method.
			 * @param string $action Action name.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/ajax-request', $action, $method );

			/**
			 * Fires when TotalPoll receives an AJAX request (specific HTTP method and action name).
			 *
			 * @param string $method HTTP method.
			 * @param string $action Action name.
			 *
			 * @since 4.0.0
			 */
			do_action( "totalpoll/actions/ajax-request/{$method}/{$action}", $action, $method );

			/**
			 * Fires when TotalPoll receives an AJAX request (specific action name).
			 *
			 * @param string $method HTTP method.
			 * @param string $action Action name.
			 *
			 * @since 4.0.0
			 */
			do_action( "totalpoll/actions/ajax-request/{$action}", $action, $method );
		endif;
	}

	/**
	 * @param \WP_Query $query
	 *
	 * @return mixed
	 */
	public function disableArchive( $query ) {
		if ( $query->is_main_query() && $query->is_post_type_archive( TP_POLL_CPT_NAME ) ) :
			$query->set_404();
			remove_action( 'pre_get_posts', [ $this, 'disableArchive' ] );
		endif;
	}

	/**
	 * Prepare poll.
	 * @since 1.0.0
	 */
	public function preparePoll() {
		// Check whether is an archive page, search or singular.
		if ( is_single() || is_archive() || is_search() ):
			// Get current post type.
			$currentPostType = get_post_type();

			// Check current post type is poll
			if ( $currentPostType === TP_POLL_CPT_NAME ):
				$callback = [ $this, defined( 'TP_ASYNC' ) && TP_ASYNC ? 'pollPostAsync' : 'pollPost' ];
				// Hide content when is archive, otherwise call the appropriate callback.
				if ( ( is_archive() || is_search() ) && ! TotalPoll()->option( 'advanced.renderPollsInArchive' ) ):
					$callback = function () {
						return $GLOBALS['post']->post_excerpt;
					};
				endif;

				// Content
				add_filter( 'the_content', $callback, 99 );
				// Meta tags
				add_action( 'wp_head', [ $this, 'pollHeadSection' ], 0 );
				// Title
				add_filter( 'wp_title', [ $this, 'pollTitle' ], 0 );

				// We need to take care of the output when It's embedded
				if ( function_exists( 'is_embed' ) && is_embed() ):
					add_action( 'embed_content', function () use ( $callback ) {
						echo $callback( '' );
					}, 99 );
					remove_all_filters( 'get_the_excerpt' );
					add_filter( 'the_excerpt_embed', '__return_empty_string' );
					add_filter( 'embed_site_title_html', '__return_empty_string' );
					remove_all_actions( 'embed_content_meta' );
				endif;
			endif;
		endif;
	}

	/**
	 * Prepare poll post.
	 *
	 * @param $content Content
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function pollPost( $content ) {
		$poll = TotalPoll( 'polls.repository' )->getById( $GLOBALS['post']->ID );

		return $poll ? $poll->render() : $content;
	}

	/**
	 * Prepare poll post for async loading.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function pollPostAsync( $content ) {
		return $this->pollPost( '' );
	}

	/**
	 * Poll title.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function pollTitle( $title ) {
		$poll = TotalPoll( 'polls.repository' )->getById( $GLOBALS['post']->ID );
		if ( $poll ):
			$seo   = $poll->getSeoAttributes();
			$title = $seo['title'];
		endif;

		return $title;
	}

	/**
	 * Poll head section.
	 */
	public function pollHeadSection() {
		$poll = TotalPoll( 'polls.repository' )->getById( $GLOBALS['post']->ID );

		if ( $poll ):
			$seo = $poll->getSeoAttributes();
			$this->printMetaTags( $seo['title'], $seo['description'], $poll->getThumbnail() );
		endif;
		/**
		 * Fires after printing poll meta tags.
		 *
		 * @param Model $poll Poll object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/poll/after/head', $poll );
	}

	/**
	 * Print meta tags.
	 *
	 * @param      $title
	 * @param      $description
	 * @param null $image
	 */
	protected function printMetaTags( $title, $description, $image = null ) {
		if ( $title ):
			printf( '<meta property="og:title" content="%s" />' . PHP_EOL, esc_attr( $title ) );
		endif;

		if ( $description ):
			printf( '<meta property="og:description" content="%s" />' . PHP_EOL, esc_attr( $description ) );
			printf( '<meta property="description" content="%s" />' . PHP_EOL, esc_attr( $description ) );
		endif;

		if ( $image ):
			printf( '<meta property="og:image" content="%s" />' . PHP_EOL, esc_attr( $image ) );
		endif;
	}
}
