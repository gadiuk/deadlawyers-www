<?php
/**
 * Plugin Name: DLS Category Showcase Single Category Mode
 * Description: Forces dls_category_showcase to render only one category when categories attribute is provided.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dls_category_showcase_single_category_wrapper' ) ) {
	/**
	 * Wrap showcase shortcode to enforce single explicit category mode.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 * @param string|null         $content Enclosed content.
	 * @param string              $shortcode_tag Shortcode tag.
	 * @return string
	 */
	function dls_category_showcase_single_category_wrapper( $atts = array(), $content = null, $shortcode_tag = 'dls_category_showcase' ) {
		if ( ! function_exists( 'dls_category_showcase_shortcode' ) ) {
			return '';
		}

		$atts = is_array( $atts ) ? $atts : array();

		if ( isset( $atts['categories'] ) && '' !== trim( (string) $atts['categories'] ) ) {
			$parts = array_filter( array_map( 'trim', explode( ',', (string) $atts['categories'] ) ) );
			if ( ! empty( $parts ) ) {
				$atts['categories'] = (string) reset( $parts );
			}
			$atts['sections'] = 1;
		}

		return dls_category_showcase_shortcode( $atts );
	}
}

if ( ! function_exists( 'dls_category_showcase_enable_single_category_mode' ) ) {
	/**
	 * Swap shortcode callback after MU plugins are loaded.
	 *
	 * @return void
	 */
	function dls_category_showcase_enable_single_category_mode() {
		if ( shortcode_exists( 'dls_category_showcase' ) && function_exists( 'dls_category_showcase_shortcode' ) ) {
			remove_shortcode( 'dls_category_showcase' );
			add_shortcode( 'dls_category_showcase', 'dls_category_showcase_single_category_wrapper' );
		}
	}

	add_action( 'muplugins_loaded', 'dls_category_showcase_enable_single_category_mode', 99 );
}
