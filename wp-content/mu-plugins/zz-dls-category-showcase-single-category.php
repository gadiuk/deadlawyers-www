<?php
/**
 * Plugin Name: DLS Category Showcase Single Category Mode
 * Description: Forces dls_category_showcase to render one explicit category and hides duplicate internal sidebar.
 * Version: 1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dls_category_showcase_strip_internal_sidebar' ) ) {
	/**
	 * Remove shortcode-internal sidebar and collapse shell to one column.
	 *
	 * @param string $html Shortcode HTML.
	 * @return string
	 */
	function dls_category_showcase_strip_internal_sidebar( $html ) {
		$html = (string) $html;
		if ( '' === trim( $html ) ) {
			return '';
		}

		$token_pos = strpos( $html, 'dls-category-showcase__sidebar' );
		if ( false !== $token_pos ) {
			$before      = substr( $html, 0, $token_pos );
			$aside_start = strrpos( $before, '<aside' );
			$aside_end   = strpos( $html, '</aside>', $token_pos );

			if ( false !== $aside_start && false !== $aside_end ) {
				$aside_end = $aside_end + strlen( '</aside>' );
				$html      = substr( $html, 0, $aside_start ) . substr( $html, $aside_end );
			}
		}

		$html = preg_replace(
			'/\bdls-category-showcase__shell\b/',
			'dls-category-showcase__shell dls-category-showcase__shell--no-sidebar',
			$html,
			1
		);

		$inline_style = '<style>.dls-category-showcase__shell.dls-category-showcase__shell--no-sidebar{grid-template-columns:minmax(0,1fr)!important;}</style>';

		return $inline_style . $html;
	}
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

		$output = dls_category_showcase_shortcode( $atts );

		return dls_category_showcase_strip_internal_sidebar( $output );
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
