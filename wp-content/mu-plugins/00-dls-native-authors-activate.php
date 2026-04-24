<?php
/**
 * Plugin Name: DLS Native Authors Activator
 * Description: Keeps the native author MU plugins disabled by default for production recovery.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Keep the custom native-author stack off so PublishPress Authors
// remains the only live author system.
if (!defined('DLS_NATIVE_AUTHORS_ACTIVE')) {
    define('DLS_NATIVE_AUTHORS_ACTIVE', false);
}

if (!function_exists('dls_publishpress_authors_force_options')) {
    function dls_publishpress_authors_force_options($options) {
        if (is_object($options)) {
            $options = clone $options;
        } elseif (!is_array($options)) {
            $options = [];
        }

        if (is_object($options)) {
            if (!isset($options->post_types) || !is_array($options->post_types)) {
                $options->post_types = [];
            }

            $options->post_types['post'] = 'on';
            $options->show_editor_author_box_selection = 'yes';
            $options->enable_plugin_author_pages = 'yes';
            $options->show_author_pages_bio = 'yes';
            $options->author_pages_layout = 'list';

            return $options;
        }

        if (!isset($options['post_types']) || !is_array($options['post_types'])) {
            $options['post_types'] = [];
        }

        $options['post_types']['post'] = 'on';
        $options['show_editor_author_box_selection'] = 'yes';
        $options['enable_plugin_author_pages'] = 'yes';
        $options['show_author_pages_bio'] = 'yes';
        $options['author_pages_layout'] = 'list';

        return $options;
    }
}

add_filter('pp_multiple_authors_default_options', 'dls_publishpress_authors_force_options', 20);
add_filter('default_option_multiple_authors_multiple_authors_options', 'dls_publishpress_authors_force_options', 20);
add_filter('option_multiple_authors_multiple_authors_options', 'dls_publishpress_authors_force_options', 20);

add_action('add_meta_boxes', function () {
    if (!class_exists('\\MultipleAuthors\\Classes\\Post_Editor')) {
        return;
    }

    \MultipleAuthors\Classes\Post_Editor::action_add_meta_boxes_late();
}, 1000);

add_action('init', function () {
    if (!class_exists('\\MultipleAuthors\\Plugin')) {
        return;
    }

    $flush_key = 'dls_publishpress_authors_rewrite_flushed_v1';
    if (get_option($flush_key) === '1') {
        return;
    }

    flush_rewrite_rules(false);
    update_option($flush_key, '1', false);
}, 30);

if (!function_exists('dls_publishpress_restore_media_capabilities')) {
    function dls_publishpress_restore_media_capabilities() {
        foreach (['administrator', 'editor', 'author'] as $role_name) {
            $role = get_role($role_name);
            if ($role && !$role->has_cap('upload_files')) {
                $role->add_cap('upload_files');
            }
        }
    }
}

add_action('init', 'dls_publishpress_restore_media_capabilities', 40);

add_filter('user_has_cap', function ($allcaps, $caps, $args, $user) {
    if (!($user instanceof WP_User)) {
        return $allcaps;
    }

    $roles = array_map('strval', (array) $user->roles);
    if (!empty(array_intersect(['administrator', 'editor', 'author'], $roles))) {
        $allcaps['upload_files'] = true;
    }

    return $allcaps;
}, 20, 4);

add_action('wp_enqueue_scripts', function () {
    if (!class_exists('\\MultipleAuthors\\Classes\\Objects\\Author')) {
        return;
    }

    if (!is_tax('author') && !is_author()) {
        return;
    }

    $css = <<<CSS
.ppma-author-pages.site-main.alignwide.has-global-padding {
  max-width: 1180px;
  margin: 0 auto;
  padding: 48px 24px 72px;
}
.ppma-page-header {
  display: grid;
  grid-template-columns: minmax(0, 280px) minmax(0, 1fr);
  gap: 32px;
  align-items: start;
  margin-bottom: 42px;
}
.ppma-page-title.page-title {
  margin: 0 0 18px;
  font-size: clamp(2.1rem, 4vw, 4rem);
  line-height: .95;
  letter-spacing: -.04em;
  font-weight: 700;
}
.ppma-author-pages-author-box-wrap {
  background: linear-gradient(180deg, #f5efe7 0%, #fffdf9 100%);
  border: 1px solid rgba(34,34,34,.09);
  border-radius: 22px;
  padding: 24px;
  box-shadow: 0 24px 48px rgba(24, 28, 31, .08);
}
.ppma-page-content.list {
  display: grid;
  gap: 22px;
}
.ppma-page-content.list .ppma-article {
  margin: 0;
  border: 1px solid rgba(34,34,34,.09);
  border-radius: 22px;
  background: #fffdfa;
  overflow: hidden;
  box-shadow: 0 14px 32px rgba(24, 28, 31, .06);
}
.ppma-page-content.list .article-content {
  display: grid;
  grid-template-columns: minmax(0, 260px) minmax(0, 1fr);
  gap: 0;
}
.ppma-page-content.list .article-image {
  background: #e9dfd2;
  min-height: 100%;
}
.ppma-page-content.list .article-image img {
  display: block;
  width: 100%;
  height: 100%;
  min-height: 100%;
  object-fit: cover;
}
.ppma-page-content.list .article-body {
  padding: 28px 30px 24px;
}
.ppma-page-content.list .category-link {
  display: inline-block;
  margin-bottom: 10px;
  font-size: 12px;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: #8b5e3c;
}
.ppma-page-content.list .article-title {
  margin: 0 0 14px;
  font-size: clamp(1.5rem, 2vw, 2.2rem);
  line-height: 1.02;
  letter-spacing: -.03em;
}
.ppma-page-content.list .article-title a {
  color: #161616;
  text-decoration: none;
}
.ppma-page-content.list .article-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 14px 18px;
  margin-bottom: 16px;
  font-size: 13px;
  color: #666;
}
.ppma-page-content.list .article-entry-excerpt {
  font-size: 16px;
  line-height: 1.7;
  color: #2e2e2e;
}
.ppma-page-content.list .tags-links {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 18px;
}
.ppma-page-content.list .tags-links a {
  display: inline-flex;
  align-items: center;
  padding: 6px 12px;
  border-radius: 999px;
  background: #f1e7db;
  color: #6b4b32;
  text-decoration: none;
  font-size: 13px;
}
.ppma-article-pagination {
  margin-top: 14px;
}
@media (max-width: 900px) {
  .ppma-page-header,
  .ppma-page-content.list .article-content {
    grid-template-columns: 1fr;
  }
  .ppma-page-content.list .article-image {
    min-height: 220px;
  }
  .ppma-page-content.list .article-body {
    padding: 22px 20px 20px;
  }
}
CSS;

    wp_register_style('dls-publishpress-author-layout', false, [], null);
    wp_enqueue_style('dls-publishpress-author-layout');
    wp_add_inline_style('dls-publishpress-author-layout', $css);
}, 30);
