<?php

namespace TotalPollVendors\TotalCore\Widgets;

use TotalPollVendors\TotalCore\Contracts\Widgets\Widget as WidgetContract;

/**
 * Widget base class
 * @package TotalPollVendors\TotalCore\Widget
 * @since   1.0.0
 */
abstract class Widget extends \WP_Widget implements WidgetContract {
	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ):
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		endif;
		echo $this->content( $args, $instance );
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// Title field
		$fields['title'] = \TotalPollVendors\TotalCore\Application::get( 'form.field.text' )->setOptions( [
			'class' => 'widefat',
			'name'  => esc_attr( $this->get_field_name( 'title' ) ),
			'label' => __( 'Title:', \TotalPollVendors\TotalCore\Application::getInstance()->env( 'slug' ) ),
		] )->setValue( $instance['title'] ?: '' );

		// Custom fields setup
		$fields = $this->fields( $fields, $instance );

		// Render all
		foreach ( $fields as $field ):
			echo '<p>' . $field->render() . '</p>';
		endforeach;
	}

	/**
	 * Widget fields.
	 *
	 * @param $fields
	 * @param $instance
	 *
	 * @return mixed
	 */
	public function fields( $fields, $instance ) {
		return $fields;
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		array_walk( $new_instance, 'strip_tags' );

		return $new_instance;
	}
}