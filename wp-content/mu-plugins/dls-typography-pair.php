<?php
/**
 * Plugin Name: DLS Typography Pair
 * Description: Applies PT Serif + IBM Plex Sans typography with readable heading and body rhythm.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dls_typography_pair_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap';
}

function dls_typography_pair_css_www() {
	return <<<'CSS'
:root {
  --dls-font-body: 'PT Serif', Georgia, 'Times New Roman', serif;
  --dls-font-heading: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --dls-font-ui: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --global-body-font-family: var(--dls-font-body);
  --global-heading-font-family: var(--dls-font-heading);
}

body,
.editor-styles-wrapper {
  font-family: var(--dls-font-body);
  font-size: clamp(1rem, 0.94rem + 0.35vw, 1.125rem);
  line-height: 1.72;
  text-rendering: optimizeLegibility;
}

.entry-content,
.single-content,
.wp-block-post-content {
  max-width: 72ch;
}

p,
li,
blockquote,
td,
th {
  line-height: 1.72;
}

.entry-content > p:first-child,
.single-content > p:first-child,
.wp-block-post-content > p:first-child {
  font-size: clamp(1.0625rem, 1rem + 0.32vw, 1.25rem);
  line-height: 1.52;
}

h1,
h2,
h3,
h4,
h5,
h6,
.wp-block-heading,
.entry-title,
.site-title {
  font-family: var(--dls-font-heading);
  letter-spacing: -0.01em;
  text-wrap: balance;
}

h1,
.entry-title {
  line-height: 1.12;
}

h2 {
  line-height: 1.2;
}

h3 {
  line-height: 1.26;
}

h4,
h5,
h6 {
  line-height: 1.3;
}

button,
input,
select,
textarea,
label,
.site-description,
.entry-meta,
.entry-taxonomies,
.kadence-breadcrumbs,
.kb-advanced-heading-subtitle {
  font-family: var(--dls-font-ui);
  line-height: 1.45;
}

@media (min-width: 1025px) {
  body.single-post.has-sidebar:not(.has-left-sidebar) .content-container {
    grid-template-columns: minmax(0, 1fr) minmax(320px, 26%);
    column-gap: clamp(1.5rem, 2vw, 2.25rem);
  }

  body.single-post .entry-content,
  body.single-post .single-content,
  body.single-post .wp-block-post-content {
    max-width: 78ch;
  }
}

@media (max-width: 782px) {
  body,
  .editor-styles-wrapper {
    font-size: 1.0625rem;
  }

  h1,
  .entry-title {
    line-height: 1.15;
  }

  h2 {
    line-height: 1.22;
  }
}
CSS;
}

function dls_typography_pair_enqueue_frontend_www() {
	$handle = 'dls-typography-pair';
	wp_enqueue_style( $handle, dls_typography_pair_fonts_url(), array(), null );
	wp_add_inline_style( $handle, dls_typography_pair_css_www() );
}
add_action( 'wp_enqueue_scripts', 'dls_typography_pair_enqueue_frontend_www', 30 );

function dls_typography_pair_enqueue_editor_www() {
	$handle = 'dls-typography-pair-editor';
	wp_enqueue_style( $handle, dls_typography_pair_fonts_url(), array(), null );
	wp_add_inline_style( $handle, dls_typography_pair_css_www() );
}
add_action( 'enqueue_block_editor_assets', 'dls_typography_pair_enqueue_editor_www' );
