<?php

namespace TotalPoll\Shortcodes;

use TotalPoll\Poll\Model;
use TotalPollVendors\TotalCore\Form\Page;
use WP_Error;

/**
 * Poll Shortcode.
 * @package TotalPoll\Shortcode
 * @since   1.0.0
 */
class Poll extends \TotalPollVendors\TotalCore\Shortcodes\Shortcode {

	/**
	 * Render shortcode.
	 *
	 * @return mixed|string
	 */
	public function handle() {
		/**
		 * @var Model|null $poll
		 */
		$poll = TotalPoll( 'polls.repository' )->getById( $this->getAttribute( 'id' ) );
		if ( ! $poll ):
			return esc_html__( 'Could not load the poll.', 'totalpoll' );
		endif;

		$screen = $this->getAttribute( 'screen', $this->getAttribute( 'fragment' ) );

		if ( $screen ):
			// Override screen when rendering
			add_filter( 'totalpoll/filters/render/screen', function ( $currentScreen, $renderedPoll ) use ( $poll, $screen ) {
				if ( $renderedPoll->getId() === $poll->getId() ) :

					if ( $screen === Model::VOTE_SCREEN && $poll->hasVoted() ) {
						$poll->setError( new WP_Error( 'voted_before', esc_html__( 'You cannot vote again.', 'totalpoll' ) ) );
					} else {
						$poll->setError( null );
					}

					return $screen;
				endif;

				return $currentScreen;
			}, 10, 2 );

			// Hide fields when vote is not allowed
			add_filter( 'totalpoll/filters/form/pages', function ( $pages, $renderedPoll ) use ( $screen, $poll ) {
				if ( $renderedPoll->getId() === $poll->getId() && $screen === Model::VOTE_SCREEN && ! $poll->isAcceptingVotes() ) :
					$pages['fields'] = new Page();
				endif;

				return $pages;
			}, 10, 2 );

			// Override results visibility
			add_filter(
				'totalpoll/filters/poll/results-hidden',
				function ( $hidden, $renderedPoll ) use ( $poll ) {
					if ( $renderedPoll->getId() === $poll->getId() ) :
						return false;
					endif;

					return $hidden;
				},
				10, 2
			);

			// Hide buttons
			add_filter( 'totalpoll/filters/form/buttons', function ( $buttons, $renderedPoll ) use ( $screen, $poll ) {
				if ( $renderedPoll->getId() === $poll->getId() && ( $screen !== Model::VOTE_SCREEN || ! $poll->isAcceptingVotes() ) ) :
					return [];
				endif;

				return $buttons;
			}, 10, 2 );

		endif;

		return $poll->render();
	}


}
