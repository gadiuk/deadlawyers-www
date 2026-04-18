<?php

namespace TotalPoll\Widgets;

use TotalPollVendors\TotalCore\Widgets\Widget;

/**
 * Poll Widget.
 * @package TotalPoll\Widgets
 */
class Poll extends Widget {
	/**
	 * Poll constructor.
	 */
	public function __construct() {
		$widgetOptions = array(
			'classname'   => 'totalpoll-widget-poll',
			'description' => esc_html__( 'TotalPoll poll widget', 'totalpoll' ),
		);
		parent::__construct( 'totalpoll_poll', esc_html__( '[TotalPoll] Poll', 'totalpoll' ), $widgetOptions );
	}

	/**
	 * Widget content.
	 *
	 * @param $args
	 * @param $instance
	 */
	public function content( $args, $instance ) {
		if ( ! empty( $instance['poll'] ) ):
			$screen = $instance['screen'] ?: 'vote';
			$poll   = TotalPoll( 'polls.repository' )->getById( $instance['poll'] )->setScreen( $screen );

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
		$instance = wp_parse_args( $instance, [ 'poll' => null, 'title' => null, 'screen' => null ] );
		parent::form( $instance );
	}

	/**
	 * Widget form fields.
	 *
	 * @param $fields
	 * @param $instance
	 *
	 * @return mixed
	 */
	public function fields( $fields, $instance ) {
		// Poll field
		$polls      = [];
		$pollsPosts = (array) get_posts( [ 'post_type' => TP_POLL_CPT_NAME, 'posts_per_page' => - 1 ] );

		foreach ( $pollsPosts as $poll ):
			$polls[ $poll->ID ] = $poll->post_title;
		endforeach;

		$pollsListFieldOptions = [
			'class'   => 'widefat',
			'name'    => esc_attr( $this->get_field_name( 'poll' ) ),
			'label'   => esc_html__( 'Poll:', 'totalpoll' ),
			'options' => $polls,
		];

		$fields['poll'] = TotalPoll( 'form.field.select' )->setOptions( $pollsListFieldOptions )->setValue( $instance['poll'] ?: '' );

		// Screen
		$screen                = $instance['screen'] ?: 'vote';
		$pollScreenFieldOption = [
			'class'   => 'widefat totalpoll-page-selector',
			'name'    => esc_attr( $this->get_field_name( 'screen' ) ),
			'label'   => esc_html__( 'Screen:', 'totalpoll' ),
			'options' => [
				'vote'    => esc_html__( 'Vote', 'totalpoll' ),
				'results' => esc_html__( 'Results', 'totalpoll' ),
			],
		];
		$fields['screen']      = TotalPoll( 'form.field.select' )->setOptions( $pollScreenFieldOption )->setValue( $screen );

		return $fields;
	}
}
