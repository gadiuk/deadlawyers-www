<?php
/**
 * Plugin Name: DLS Native Authors Admin Dropdowns
 * Description: Language-aware Author/Editor dropdown UI for DLS Native Authors.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Emergency fail-safe: keep this MU plugin disabled unless explicitly enabled.
// Add in wp-config.php to re-enable:
// define('DLS_NATIVE_AUTHORS_ACTIVE', true);
if (!defined('DLS_NATIVE_AUTHORS_ACTIVE') || DLS_NATIVE_AUTHORS_ACTIVE !== true) {
    return;
}

if (!function_exists('dls_na_ui_user_has_role')) {
    function dls_na_ui_user_has_role($user, $roles) {
        if (!($user instanceof WP_User)) {
            return false;
        }

        $roles = array_filter(array_map('strtolower', array_map('strval', (array) $roles)));
        if (empty($roles)) {
            return false;
        }

        $user_roles = array_map('strtolower', array_map('strval', (array) $user->roles));

        return !empty(array_intersect($roles, $user_roles));
    }
}

if (!function_exists('dls_na_ui_detect_post_language')) {
    function dls_na_ui_detect_post_language($post_id) {
        $post_id = absint($post_id);

        if ($post_id < 1) {
            return '';
        }

        $lang = '';

        if (function_exists('pll_get_post_language')) {
            $lang = (string) pll_get_post_language($post_id, 'slug');
        }

        if ($lang === '' && taxonomy_exists('language')) {
            $slugs = wp_get_post_terms($post_id, 'language', ['fields' => 'slugs']);
            if (!empty($slugs) && !is_wp_error($slugs)) {
                $lang = (string) reset($slugs);
            }
        }

        $lang = strtolower(trim($lang));

        return in_array($lang, ['uk', 'en'], true) ? $lang : '';
    }
}

if (!function_exists('dls_na_ui_detect_user_language')) {
    function dls_na_ui_detect_user_language($user) {
        if (!($user instanceof WP_User)) {
            return '';
        }

        $login = strtolower((string) $user->user_login);
        $display = (string) $user->display_name;

        if ($login !== '') {
            if (preg_match('/(?:^|[-_])en(?:$|[-_0-9])/', $login)) {
                return 'en';
            }

            if (preg_match('/(?:^|[-_])uk(?:$|[-_0-9])/', $login)) {
                return 'uk';
            }
        }

        if ($display !== '' && preg_match('/[А-Яа-яЇїІіЄєҐґ]/u', $display)) {
            return 'uk';
        }

        if ($display !== '' && preg_match('/[A-Za-z]/', $display)) {
            return 'en';
        }

        return '';
    }
}

if (!function_exists('dls_na_ui_filter_users_by_language')) {
    function dls_na_ui_filter_users_by_language($users, $lang) {
        $lang = strtolower(trim((string) $lang));
        if (!in_array($lang, ['uk', 'en'], true)) {
            return $users;
        }

        $filtered = [];

        foreach ((array) $users as $user) {
            if (!($user instanceof WP_User)) {
                continue;
            }

            if (dls_na_ui_detect_user_language($user) === $lang) {
                $filtered[] = $user;
            }
        }

        return !empty($filtered) ? $filtered : $users;
    }
}

if (!function_exists('dls_na_ui_base_users')) {
    function dls_na_ui_base_users() {
        if (function_exists('dls_native_authors_get_users')) {
            $users = dls_native_authors_get_users();
            if (is_array($users)) {
                return $users;
            }
        }

        return get_users([
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'number'  => 5000,
        ]);
    }
}

if (!function_exists('dls_na_ui_dropdown_users')) {
    function dls_na_ui_dropdown_users($post_id, $mode = 'author', $ensure_user_id = 0) {
        $mode = strtolower(trim((string) $mode));
        $users = dls_na_ui_base_users();
        $allowed = [];

        foreach ((array) $users as $user) {
            if (!($user instanceof WP_User)) {
                continue;
            }

            if (dls_na_ui_user_has_role($user, ['administrator'])) {
                continue;
            }

            if ($mode === 'editor') {
                if (!dls_na_ui_user_has_role($user, ['editor'])) {
                    continue;
                }
            } else {
                if (!dls_na_ui_user_has_role($user, ['author', 'editor'])) {
                    continue;
                }
            }

            $allowed[$user->ID] = $user;
        }

        $lang = dls_na_ui_detect_post_language($post_id);
        $filtered = dls_na_ui_filter_users_by_language(array_values($allowed), $lang);

        if ($ensure_user_id > 0 && !isset($allowed[$ensure_user_id])) {
            $extra = get_userdata($ensure_user_id);
            if ($extra instanceof WP_User) {
                $filtered[] = $extra;
            }
        }

        usort($filtered, static function ($a, $b) {
            return strnatcasecmp((string) $a->display_name, (string) $b->display_name);
        });

        return $filtered;
    }
}

if (!function_exists('dls_na_ui_get_post_assignments')) {
    function dls_na_ui_get_post_assignments($post_id) {
        if (function_exists('dls_native_authors_get_post_assignments')) {
            return (array) dls_native_authors_get_post_assignments($post_id);
        }

        $stored = get_post_meta($post_id, '_dls_post_author_assignments', true);
        return is_array($stored) ? $stored : [];
    }
}

if (!function_exists('dls_na_ui_render_metabox')) {
    function dls_na_ui_render_metabox($post) {
        if (!($post instanceof WP_Post)) {
            return;
        }

        wp_nonce_field('dls_na_ui_save_authors', 'dls_na_ui_nonce');

        $selected_author = 0;
        $selected_editor = 0;

        foreach (dls_na_ui_get_post_assignments($post->ID) as $row) {
            $user_id = absint($row['user_id'] ?? 0);
            $post_role = strtolower(trim((string) ($row['post_role'] ?? 'author')));

            if ($user_id < 1) {
                continue;
            }

            if ($post_role === 'editor' && $selected_editor < 1) {
                $selected_editor = $user_id;
                continue;
            }

            if ($post_role !== 'editor' && $selected_author < 1) {
                $selected_author = $user_id;
            }
        }

        $author_users = dls_na_ui_dropdown_users($post->ID, 'author', $selected_author);
        $editor_users = dls_na_ui_dropdown_users($post->ID, 'editor', $selected_editor);

        $lang = dls_na_ui_detect_post_language($post->ID);
        $lang_label = $lang === 'uk' ? 'UK' : ($lang === 'en' ? 'EN' : 'All');

        echo '<p style="margin:0 0 10px">Choose one author and one editor. Language filter: ' . esc_html($lang_label) . '.</p>';

        echo '<p style="margin:0 0 8px"><label for="dls-primary-author"><strong>Author</strong></label></p>';
        echo '<select id="dls-primary-author" name="dls_primary_author" style="width:100%; margin:0 0 12px">';
        echo '<option value="">— Not set —</option>';
        foreach ($author_users as $user) {
            $user_id = (int) $user->ID;
            $role_badge = dls_na_ui_user_has_role($user, ['editor']) ? ' (editor)' : '';
            echo '<option value="' . esc_attr($user_id) . '"' . selected($selected_author, $user_id, false) . '>' . esc_html($user->display_name . $role_badge) . '</option>';
        }
        echo '</select>';

        echo '<p style="margin:0 0 8px"><label for="dls-primary-editor"><strong>Editor</strong></label></p>';
        echo '<select id="dls-primary-editor" name="dls_primary_editor" style="width:100%; margin:0">';
        echo '<option value="">— Not set —</option>';
        foreach ($editor_users as $user) {
            $user_id = (int) $user->ID;
            echo '<option value="' . esc_attr($user_id) . '"' . selected($selected_editor, $user_id, false) . '>' . esc_html($user->display_name) . '</option>';
        }
        echo '</select>';
    }
}

add_action('add_meta_boxes', static function () {
    if (!function_exists('dls_native_authors_meta_box_post_types')) {
        return;
    }

    foreach ((array) dls_native_authors_meta_box_post_types() as $post_type) {
        remove_meta_box('dls-native-authors-box', $post_type, 'side');

        add_meta_box(
            'dls-native-authors-box',
            'DLS Authors',
            'dls_na_ui_render_metabox',
            $post_type,
            'side',
            'high'
        );
    }
}, 999);

add_action('save_post', static function ($post_id, $post) {
    if (!is_admin() || !($post instanceof WP_Post)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!isset($_POST['dls_na_ui_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dls_na_ui_nonce'])), 'dls_na_ui_save_authors')) {
        return;
    }

    $author_id = isset($_POST['dls_primary_author']) ? absint(wp_unslash($_POST['dls_primary_author'])) : 0;
    $editor_id = isset($_POST['dls_primary_editor']) ? absint(wp_unslash($_POST['dls_primary_editor'])) : 0;

    $selected_ids = [];
    $role_map = [];

    if ($author_id > 0 && get_userdata($author_id)) {
        $selected_ids[] = $author_id;
        $role_map[$author_id] = 'author';
    }

    if ($editor_id > 0 && get_userdata($editor_id)) {
        $editor_user = get_userdata($editor_id);

        if (dls_na_ui_user_has_role($editor_user, ['editor'])) {
            $selected_ids[] = $editor_id;
            $role_map[$editor_id] = 'editor';
        }
    }

    $selected_ids = array_values(array_unique(array_map('absint', $selected_ids)));

    $assignments = [];
    foreach ($selected_ids as $user_id) {
        $assignments[] = [
            'user_id'   => $user_id,
            'post_role' => $role_map[$user_id] ?? 'author',
        ];
    }

    if (empty($assignments)) {
        delete_post_meta($post_id, '_dls_post_author_assignments');
        delete_post_meta($post_id, '_dls_post_authors');
        delete_post_meta($post_id, '_dls_post_author');
        return;
    }

    update_post_meta($post_id, '_dls_post_author_assignments', $assignments);
    update_post_meta($post_id, '_dls_post_authors', $selected_ids);

    delete_post_meta($post_id, '_dls_post_author');
    foreach ($selected_ids as $user_id) {
        add_post_meta($post_id, '_dls_post_author', $user_id, false);
    }
}, 999, 2);
