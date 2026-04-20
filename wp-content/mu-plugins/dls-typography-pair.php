<?php
/**
 * Plugin Name: DLS Typography Pair
 * Description: Applies PT Serif + IBM Plex Sans typography with readable heading and body rhythm.
 * Version: 1.1.0
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

body {
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
.wp-block-post-title,
.entry-title,
.entry-hero h1,
.entry-hero .entry-title,
.site-title {
  font-family: var(--dls-font-heading) !important;
  letter-spacing: -0.01em;
  text-wrap: balance;
}

h1,
.entry-title,
.wp-block-post-title {
  font-weight: 700 !important;
  line-height: 1.12;
}

h2 {
  font-weight: 700 !important;
  line-height: 1.2;
}

h3 {
  font-weight: 600 !important;
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

.entry-content p a,
.entry-content li a,
.entry-content blockquote a,
.entry-content td a,
.single-content p a,
.single-content li a,
.single-content blockquote a,
.single-content td a,
.wp-block-post-content p a,
.wp-block-post-content li a,
.wp-block-post-content blockquote a,
.wp-block-post-content td a {
  background-image: linear-gradient(120deg, rgba(230, 194, 109, 0.18), rgba(230, 194, 109, 0.34));
  background-position: 0 92%;
  background-repeat: no-repeat;
  background-size: 100% .48em;
  box-shadow: inset 0 -1px 0 rgba(17, 17, 17, 0.36);
  color: #111;
  font-weight: 600;
  text-decoration: none;
  transition: background-size .18s ease, box-shadow .18s ease, color .18s ease;
}

.entry-content p a:hover,
.entry-content li a:hover,
.entry-content blockquote a:hover,
.entry-content td a:hover,
.single-content p a:hover,
.single-content li a:hover,
.single-content blockquote a:hover,
.single-content td a:hover,
.wp-block-post-content p a:hover,
.wp-block-post-content li a:hover,
.wp-block-post-content blockquote a:hover,
.wp-block-post-content td a:hover {
  background-size: 100% 100%;
  box-shadow: inset 0 -2px 0 rgba(17, 17, 17, 0.72);
  color: #000;
}

.entry-content p a:visited,
.entry-content li a:visited,
.entry-content blockquote a:visited,
.entry-content td a:visited,
.single-content p a:visited,
.single-content li a:visited,
.single-content blockquote a:visited,
.single-content td a:visited,
.wp-block-post-content p a:visited,
.wp-block-post-content li a:visited,
.wp-block-post-content blockquote a:visited,
.wp-block-post-content td a:visited {
  color: #111;
}

body.single-post .dls-post-people {
  border-top: 1px solid rgba(17, 17, 17, 0.1);
  margin-top: 2rem;
  padding-top: 1.35rem;
}

body.single-post .dls-post-people__group {
  background: transparent !important;
  border: 0 !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  margin: 0;
  padding: 0;
}

body.single-post .dls-post-people__group + .dls-post-people__group {
  margin-top: 1.2rem;
}

body.single-post .dls-post-people__group-title {
  display: none !important;
}

body.single-post .dls-post-people__list {
  display: grid;
  gap: 1rem;
}

body.single-post .dls-post-person {
  align-items: start;
  column-gap: .9rem;
  display: grid;
  grid-template-columns: 56px minmax(0, 1fr);
}

body.single-post .dls-post-person__avatar {
  border-radius: 999px;
  display: block;
  height: 56px;
  overflow: hidden;
  width: 56px;
}

body.single-post .dls-post-person__avatar-img {
  display: block;
  height: 100%;
  object-fit: cover;
  width: 100%;
}

body.single-post .dls-post-person__name {
  align-self: end;
  color: #111 !important;
  font-family: var(--dls-font-ui);
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
  margin: 0;
  text-decoration: none;
}

body.single-post .dls-post-person__name:hover {
  color: #000 !important;
  opacity: .78;
}

body.single-post .dls-post-person__bio {
  color: rgba(17, 17, 17, 0.72);
  grid-column: 2;
  line-height: 1.55;
  margin: .22rem 0 0;
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
  body {
    font-size: 1.0625rem;
  }

  h1,
  .entry-title,
  .wp-block-post-title {
    line-height: 1.15;
  }

  h2 {
    line-height: 1.22;
  }
}
CSS;
}

function dls_typography_pair_js_www() {
	return <<<'JS'
(function () {
  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, ' ').trim();
  }

  function isEmptyMentionCard(card) {
    var title = normalizeText((card.querySelector('h1, h2, h3, h4, h5, h6, .widget-title') || {}).textContent);
    var body = normalizeText(card.textContent);

    var checks = [
      {
        title: /Companies mentioned/i,
        empty: /No companies tagged in this story yet\./i
      },
      {
        title: /Individuals mentioned/i,
        empty: /No individuals tagged in this story yet\./i
      },
      {
        title: /Компанії|Згадані компанії/i,
        empty: /Немає позначених компаній|Компанії .* ще не позначені/i
      },
      {
        title: /Особи|Люди|Згадані особи/i,
        empty: /Немає позначених осіб|Особи .* ще не позначені/i
      }
    ];

    return checks.some(function (check) {
      return check.title.test(title) && check.empty.test(body);
    });
  }

  function hideEmptyMentionCards() {
    if (!document.body || !document.body.classList.contains('single-post')) {
      return;
    }

    var cards = document.querySelectorAll('aside section, aside .widget, aside .wp-block-group');
    cards.forEach(function (card) {
      if (isEmptyMentionCard(card)) {
        card.style.display = 'none';
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hideEmptyMentionCards);
  } else {
    hideEmptyMentionCards();
  }

  var observer = new MutationObserver(function () {
    hideEmptyMentionCards();
  });

  observer.observe(document.documentElement, {
    childList: true,
    subtree: true
  });
})();
JS;
}

function dls_typography_pair_enqueue_frontend_www() {
	$handle = 'dls-typography-pair';
	wp_enqueue_style( $handle, dls_typography_pair_fonts_url(), array(), null );
	wp_add_inline_style( $handle, dls_typography_pair_css_www() );

	$script_handle = 'dls-typography-pair-js';
	wp_register_script( $script_handle, false, array(), '1.1.0', true );
	wp_enqueue_script( $script_handle );
	wp_add_inline_script( $script_handle, dls_typography_pair_js_www() );
}
add_action( 'wp_enqueue_scripts', 'dls_typography_pair_enqueue_frontend_www', 30 );
