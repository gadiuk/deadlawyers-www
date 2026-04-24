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

            return $options;
        }

        if (!isset($options['post_types']) || !is_array($options['post_types'])) {
            $options['post_types'] = [];
        }

        $options['post_types']['post'] = 'on';
        $options['show_editor_author_box_selection'] = 'yes';
        $options['enable_plugin_author_pages'] = 'yes';
        $options['show_author_pages_bio'] = 'yes';

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
