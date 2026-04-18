<?php

namespace TotalPoll\Widgets;

use TotalPollVendors\TotalCore\Widgets\Widget;

/**
 * Poll Widget.
 * @package TotalPoll\Widgets
 */
class RandomPoll extends Widget {
	/**
	 * Poll constructor.
	 */
	public function __construct() {
		$widgetOptions = array(
			'classname'   => 'totalpoll-widget-random-poll',
			'description' => esc_html__( 'TotalPoll random poll widget', 'totalpoll' ),
		);
		parent::__construct( 'totalpoll_random_poll', esc_html__( '[TotalPoll] Random Poll', 'totalpoll' ), $widgetOptions );
	}

	/**
	 * Widget content.
	 *
	 * @param $args
	 * @param $instance
	 */
	public function content( $args, $instance ) {
        $screen = $instance['screen'] ?: 'vote';
        echo do_shortcode(sprintf('[totalpoll-random screen="%s"]', $screen));
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

		// Screen
		$screen  = $instance['screen'] ?: 'vote';

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
