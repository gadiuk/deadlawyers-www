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
            $options->layout = 'inline_avatar';
            $options->author_pages_layout = 'list';
            $options->author_pages_bio_layout = 'inline_avatar';

            return $options;
        }

        if (!isset($options['post_types']) || !is_array($options['post_types'])) {
            $options['post_types'] = [];
        }

        $options['post_types']['post'] = 'on';
        $options['show_editor_author_box_selection'] = 'yes';
        $options['enable_plugin_author_pages'] = 'yes';
        $options['show_author_pages_bio'] = 'yes';
        $options['layout'] = 'inline_avatar';
        $options['author_pages_layout'] = 'list';
        $options['author_pages_bio_layout'] = 'inline_avatar';

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
  grid-template-columns: 1fr;
  gap: 0;
  align-items: start;
  margin-bottom: 28px;
}
.ppma-page-title.page-title {
  display: none;
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
  max-width: 760px;
  padding: 20px 22px;
  box-shadow: 0 24px 48px rgba(24, 28, 31, .08);
  position: static;
  overflow: hidden;
}
.ppma-author-pages-author-box-wrap .pp-multiple-authors-boxes-ul {
  margin: 0;
  padding: 0;
  list-style: none;
}
.ppma-author-pages-author-box-wrap .pp-multiple-authors-boxes-li {
  display: grid !important;
  grid-template-columns: 72px minmax(0, 1fr);
  gap: 18px;
  align-items: center;
  margin: 0;
  padding: 0;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-avatar {
  width: 72px;
  min-width: 72px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-avatar .avatar {
  width: 72px !important;
  height: 72px !important;
  border-radius: 16px;
  object-fit: cover;
  display: block;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-avatar-details {
  text-align: left;
  min-width: 0;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-name {
  margin: 0 0 6px;
  font-size: 1.7rem;
  line-height: 1.02;
  letter-spacing: -.03em;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-name a,
.ppma-author-pages-author-box-wrap .pp-author-boxes-name {
  color: #111 !important;
  text-decoration: none;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-name *,
.ppma-author-pages-author-box-wrap .pp-author-boxes-name a * {
  color: #111 !important;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-description {
  margin: 0;
  color: #111;
  font-size: 15px;
  line-height: 1.6;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-description p:first-child {
  margin-top: 0;
}
.ppma-author-pages-author-box-wrap .pp-author-boxes-description p:last-child {
  margin-bottom: 0;
}
.ppma-author-pages-author-box-wrap .ppma-category-group-title {
  display: none !important;
}
.ppma-author-pages-author-box-wrap .multiple-authors-description,
.ppma-author-pages-author-box-wrap .pp-author-boxes-meta {
  display: none !important;
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
  display: grid !important;
  grid-template-columns: 220px minmax(0, 1fr) !important;
  gap: 0 !important;
  align-items: stretch !important;
  margin-bottom: 0 !important;
}
.ppma-page-content.list .article-image {
  position: relative;
  background: #e9dfd2;
  min-height: 220px;
  aspect-ratio: 1 / 1;
  overflow: hidden;
  width: 220px !important;
  min-width: 220px !important;
  max-width: 220px !important;
  flex: 0 0 220px !important;
  border-radius: 22px 0 0 22px;
}
.ppma-page-content.list .article-image img {
  position: absolute;
  inset: 0;
  width: 100% !important;
  min-width: 100% !important;
  max-width: none !important;
  height: 100% !important;
  min-height: 100% !important;
  max-height: none !important;
  object-fit: cover !important;
  object-position: center;
  display: block;
}
.ppma-page-content.list .article-content.has-no-image {
  grid-template-columns: 1fr;
}
.ppma-page-content.list .article-content.has-no-image .article-image {
  display: none;
}
.ppma-page-content.list .article-body {
  padding: 28px 30px 24px;
  min-width: 0;
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
  color: #111;
  text-decoration: none;
}
.ppma-page-content.list .article-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 14px 18px;
  margin-bottom: 16px;
  font-size: 13px;
  color: #111;
}
.ppma-page-content.list .article-meta a,
.ppma-page-content.list .article-meta time,
.ppma-page-content.list .category-link a,
.ppma-page-content.list .category-link {
  color: #111 !important;
}
.ppma-page-content.list .article-entry-excerpt {
  font-size: 16px;
  line-height: 1.7;
  color: #111;
}
.ppma-page-content.list .article-entry-excerpt p:last-child {
  margin-bottom: 0;
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
.single-post .pp-multiple-authors-boxes-wrapper,
.single .pp-multiple-authors-boxes-wrapper {
  margin-top: 42px;
  padding: 28px 30px;
  border: 1px solid rgba(34,34,34,.09);
  border-radius: 22px;
  background: linear-gradient(180deg, #f7f1ea 0%, #fffdfa 100%);
  box-shadow: 0 18px 40px rgba(24, 28, 31, .07);
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-multiple-authors-boxes-ul,
.single .pp-multiple-authors-boxes-wrapper .pp-multiple-authors-boxes-ul {
  margin: 0;
  padding: 0;
  list-style: none;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-multiple-authors-boxes-li,
.single .pp-multiple-authors-boxes-wrapper .pp-multiple-authors-boxes-li {
  display: grid !important;
  grid-template-columns: 96px minmax(0, 1fr);
  gap: 18px;
  align-items: start;
  margin: 0;
  padding: 0;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-avatar,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-avatar {
  width: 96px;
  min-width: 96px;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-avatar .avatar,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-avatar .avatar {
  width: 96px !important;
  height: 96px !important;
  border-radius: 18px;
  object-fit: cover;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-avatar-details,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-avatar-details {
  display: block;
  text-align: left;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-name,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-name {
  margin: 0 0 8px;
  font-size: 1.3rem;
  line-height: 1.05;
  letter-spacing: -.02em;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-name a,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-name a {
  color: #111 !important;
  text-decoration: none;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-description,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-description {
  margin: 0;
  font-size: 15px;
  line-height: 1.65;
  color: #3b3b3b;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-description p:first-child,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-description p:first-child {
  margin-top: 0;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-description p:last-child,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-description p:last-child {
  margin-bottom: 0;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-meta,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-meta {
  margin-top: 12px;
}
.single-post .pp-multiple-authors-boxes-wrapper .pp-author-boxes-meta a,
.single .pp-multiple-authors-boxes-wrapper .pp-author-boxes-meta a {
  color: #111;
  text-decoration: none;
  font-weight: 600;
}
.single-post .pp-multiple-authors-boxes-wrapper .ppma-category-group-title,
.single .pp-multiple-authors-boxes-wrapper .ppma-category-group-title {
  display: none !important;
}
@media (max-width: 900px) {
  .ppma-page-header,
  .ppma-page-content.list .article-content {
    grid-template-columns: 1fr;
  }
  .ppma-page-content.list .article-image {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    min-height: 220px;
    border-radius: 22px 22px 0 0;
  }
  .ppma-page-content.list .article-body {
    padding: 22px 20px 20px;
  }
  .single-post .pp-multiple-authors-boxes-wrapper,
  .single .pp-multiple-authors-boxes-wrapper {
    padding: 22px 20px;
  }
}
CSS;

    wp_register_style('dls-publishpress-author-layout', false, [], null);
    wp_enqueue_style('dls-publishpress-author-layout');
    wp_add_inline_style('dls-publishpress-author-layout', $css);
}, 30);

add_action('wp_footer', function () {
    if (!is_tax('author') && !is_author()) {
        return;
    }
    ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.ppma-page-content.list .article-content').forEach(function (card) {
    var img = card.querySelector('.article-image img');
    if (!img || !img.getAttribute('src')) {
      card.classList.add('has-no-image');
      return;
    }
    img.addEventListener('error', function () {
      card.classList.add('has-no-image');
    }, { once: true });
  });

  document.querySelectorAll('.ppma-author-pages-author-box-wrap, .single .pp-multiple-authors-boxes-wrapper, .single-post .pp-multiple-authors-boxes-wrapper').forEach(function (box) {
    box.querySelectorAll('*').forEach(function (node) {
      if ((node.textContent || '').replace(/\s+/g, ' ').trim() === '[Писанина]') {
        node.remove();
      }
    });

    box.childNodes.forEach(function (node) {
      if (node.nodeType === Node.TEXT_NODE && node.textContent.indexOf('[Писанина]') !== -1) {
        node.textContent = node.textContent.replace('[Писанина]', '').trim();
      }
    });
  });
});
</script>
    <?php
}, 40);
