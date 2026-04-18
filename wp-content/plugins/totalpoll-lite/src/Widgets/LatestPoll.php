<?php

namespace TotalPoll\Widgets;

use TotalPollVendors\TotalCore\Widgets\Widget;

/**
 * Poll Widget.
 * @package TotalPoll\Widgets
 */
class LatestPoll extends Widget {
	/**
	 * Poll constructor.
	 */
	public function __construct() {
		$widgetOptions = array(
			'classname'   => 'totalpoll-widget-latest-poll',
			'description' => esc_html__( 'TotalPoll latest poll widget', 'totalpoll' ),
		);
		parent::__construct( 'totalpoll_latest_poll', esc_html__( '[TotalPoll] Latest Poll', 'totalpoll' ), $widgetOptions );
	}

	/**
	 * Widget content.
	 *
	 * @param $args
	 * @param $instance
	 */
	public function content( $args, $instance ) {
		$poll = current( TotalPoll( 'polls.repository' )->get( [ 'perPage' => 1 ] ) );
		if ( ! empty( $poll ) ):
			echo $poll->render();
		endif;
	}

	/**
	 * Widget form.
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, [ 'title' => null ] );
		parent::form( $instance );
	}
}