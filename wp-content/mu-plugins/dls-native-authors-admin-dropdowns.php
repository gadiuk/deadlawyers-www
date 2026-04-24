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

if (!function_exists('dls_na_ui_detect_guest_author_language')) {
    function dls_na_ui_detect_guest_author_language($term) {
        if (!($term instanceof WP_Term)) {
            return '';
        }

        $slug = strtolower((string) $term->slug);
        $name = (string) $term->name;

        if ($slug !== '') {
            if (preg_match('/(?:^|[-_])en(?:$|[-_0-9])/', $slug)) {
                return 'en';
            }

            if (preg_match('/(?:^|[-_])uk(?:$|[-_0-9])/', $slug)) {
                return 'uk';
            }
        }

        if ($name !== '' && preg_match('/[А-Яа-яЇїІіЄєҐґ]/u', $name)) {
            return 'uk';
        }

        if ($name !== '' && preg_match('/[A-Za-z]/', $name)) {
            return 'en';
        }

        return '';
    }
}

if (!function_exists('dls_na_ui_filter_options_by_language')) {
    function dls_na_ui_filter_options_by_language($items, $lang) {
        $lang = strtolower(trim((string) $lang));
        if (!in_array($lang, ['uk', 'en'], true)) {
            return $items;
        }

        $filtered = [];

        foreach ((array) $items as $item) {
            $item_lang = strtolower(trim((string) ($item['lang'] ?? '')));
            if ($item_lang === $lang) {
                $filtered[] = $item;
            }
        }

        return !empty($filtered) ? $filtered : $items;
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

if (!function_exists('dls_na_ui_base_guest_authors')) {
    function dls_na_ui_base_guest_authors() {
        if (function_exists('dls_native_authors_get_guest_author_terms')) {
            $terms = dls_native_authors_get_guest_author_terms();
            if (is_array($terms)) {
                return $terms;
            }
        }

        return [];
    }
}

if (!function_exists('dls_na_ui_build_selection_value')) {
    function dls_na_ui_build_selection_value($author_type, $id) {
        $author_type = strtolower(trim((string) $author_type));
        $id = absint($id);

        if ($id < 1) {
            return '';
        }

        return ($author_type === 'guest' ? 'guest:' : 'user:') . $id;
    }
}

if (!function_exists('dls_na_ui_parse_selection_value')) {
    function dls_na_ui_parse_selection_value($value) {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return [];
        }

        $parts = explode(':', $value, 2);
        if (count($parts) !== 2) {
            return [];
        }

        $author_type = $parts[0] === 'guest' ? 'guest' : 'user';
        $id = absint($parts[1]);

        if ($id < 1) {
            return [];
        }

        if ($author_type === 'guest') {
            if (!function_exists('dls_native_authors_get_guest_author_term')) {
                return [];
            }

            $term = dls_native_authors_get_guest_author_term($id);
            if (!($term instanceof WP_Term)) {
                return [];
            }

            return [
                'author_type' => 'guest',
                'term_id'     => $id,
                'user_id'     => 0,
                'label'       => (string) $term->name,
            ];
        }

        $user = get_userdata($id);
        if (!($user instanceof WP_User)) {
            return [];
        }

        return [
            'author_type' => 'user',
            'term_id'     => 0,
            'user_id'     => $id,
            'label'       => (string) $user->display_name,
        ];
    }
}

if (!function_exists('dls_na_ui_dropdown_options')) {
    function dls_na_ui_dropdown_options($post_id, $mode = 'author', $ensure_value = '') {
        $mode = strtolower(trim((string) $mode));
        $items = [];

        foreach ((array) dls_na_ui_base_users() as $user) {
            if (!($user instanceof WP_User)) {
                continue;
            }

            if ($mode === 'editor') {
                if (!dls_na_ui_user_has_role($user, ['editor', 'administrator'])) {
                    continue;
                }
            } else {
                if (!dls_na_ui_user_has_role($user, ['author', 'editor', 'administrator'])) {
                    continue;
                }
            }

            $role_badge = '';
            if (dls_na_ui_user_has_role($user, ['administrator'])) {
                $role_badge = ' (administrator)';
            } elseif (dls_na_ui_user_has_role($user, ['editor'])) {
                $role_badge = ' (editor)';
            }

            $items[] = [
                'value' => dls_na_ui_build_selection_value('user', (int) $user->ID),
                'label' => (string) $user->display_name . $role_badge,
                'lang'  => dls_na_ui_detect_user_language($user),
                'sort'  => (string) $user->display_name,
            ];
        }

        if ($mode !== 'editor') {
            foreach ((array) dls_na_ui_base_guest_authors() as $term) {
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $items[] = [
                    'value' => dls_na_ui_build_selection_value('guest', (int) $term->term_id),
                    'label' => (string) $term->name . ' (guest)',
                    'lang'  => dls_na_ui_detect_guest_author_language($term),
                    'sort'  => (string) $term->name,
                ];
            }
        }

        $lang = dls_na_ui_detect_post_language($post_id);
        $filtered = dls_na_ui_filter_options_by_language($items, $lang);

        if ($ensure_value !== '') {
            $known_values = array_column($filtered, 'value');
            if (!in_array($ensure_value, $known_values, true)) {
                $extra = dls_na_ui_parse_selection_value($ensure_value);
                if (!empty($extra)) {
                    $extra_label = trim((string) ($extra['label'] ?? ''));
                    if (($extra['author_type'] ?? 'user') === 'guest') {
                        $extra_label .= ' (guest)';
                    }

                    if ($extra_label !== '') {
                        $filtered[] = [
                            'value' => $ensure_value,
                            'label' => $extra_label,
                            'lang'  => '',
                            'sort'  => (string) ($extra['label'] ?? ''),
                        ];
                    }
                }
            }
        }

        usort($filtered, static function ($a, $b) {
            return strnatcasecmp((string) ($a['sort'] ?? $a['label'] ?? ''), (string) ($b['sort'] ?? $b['label'] ?? ''));
        });

        return $filtered;
    }
}

if (!function_exists('dls_na_ui_assignment_value')) {
    function dls_na_ui_assignment_value($row) {
        if (!is_array($row)) {
            return '';
        }

        $author_type = strtolower(trim((string) ($row['author_type'] ?? 'user')));
        if ($author_type === 'guest') {
            return dls_na_ui_build_selection_value('guest', absint($row['term_id'] ?? 0));
        }

        return dls_na_ui_build_selection_value('user', absint($row['user_id'] ?? 0));
    }
}

if (!function_exists('dls_na_ui_role_badge_for_label')) {
    function dls_na_ui_role_badge_for_label($value) {
        $parsed = dls_na_ui_parse_selection_value($value);
        if (empty($parsed) || ($parsed['author_type'] ?? 'user') !== 'user') {
            return '';
        }

        $user = get_userdata((int) ($parsed['user_id'] ?? 0));
        if (!($user instanceof WP_User)) {
            return '';
        }

        if (dls_na_ui_user_has_role($user, ['administrator'])) {
            return ' (administrator)';
        }

        if (dls_na_ui_user_has_role($user, ['editor'])) {
            return ' (editor)';
        }

        return '';
    }
}

if (!function_exists('dls_na_ui_validate_editor_selection')) {
    function dls_na_ui_validate_editor_selection($selection) {
        if (!is_array($selection) || ($selection['author_type'] ?? 'user') !== 'user') {
            return [];
        }

        $user = get_userdata((int) ($selection['user_id'] ?? 0));
        if (!($user instanceof WP_User) || !dls_na_ui_user_has_role($user, ['editor', 'administrator'])) {
            return [];
        }

        return $selection;
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

        $selected_author = '';
        $selected_editor = '';

        foreach (dls_na_ui_get_post_assignments($post->ID) as $row) {
            $post_role = strtolower(trim((string) ($row['post_role'] ?? 'author')));
            $selection_value = dls_na_ui_assignment_value($row);

            if ($selection_value === '') {
                continue;
            }

            if ($post_role === 'editor' && $selected_editor === '') {
                $selected_editor = $selection_value;
                continue;
            }

            if ($post_role !== 'editor' && $selected_author === '') {
                $selected_author = $selection_value;
            }
        }

        $author_options = dls_na_ui_dropdown_options($post->ID, 'author', $selected_author);
        $editor_options = dls_na_ui_dropdown_options($post->ID, 'editor', $selected_editor);

        $lang = dls_na_ui_detect_post_language($post->ID);
        $lang_label = $lang === 'uk' ? 'UK' : ($lang === 'en' ? 'EN' : 'All');

        echo '<p style="margin:0 0 10px">Choose one author and one editor. Language filter: ' . esc_html($lang_label) . '.</p>';

        echo '<p style="margin:0 0 8px"><label for="dls-primary-author"><strong>Author</strong></label></p>';
        echo '<select id="dls-primary-author" name="dls_primary_author" style="width:100%; margin:0 0 12px">';
        echo '<option value="">— Not set —</option>';
        foreach ($author_options as $item) {
            $value = (string) ($item['value'] ?? '');
            $label = (string) ($item['label'] ?? '');
            if ($value === '' || $label === '') {
                continue;
            }

            echo '<option value="' . esc_attr($value) . '"' . selected($selected_author, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';

        echo '<p style="margin:0 0 8px"><label for="dls-primary-editor"><strong>Editor</strong></label></p>';
        echo '<select id="dls-primary-editor" name="dls_primary_editor" style="width:100%; margin:0">';
        echo '<option value="">— Not set —</option>';
        foreach ($editor_options as $item) {
            $value = (string) ($item['value'] ?? '');
            $label = (string) ($item['label'] ?? '');
            if ($value === '' || $label === '') {
                continue;
            }

            echo '<option value="' . esc_attr($value) . '"' . selected($selected_editor, $value, false) . '>' . esc_html($label) . '</option>';
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

    $author_value = isset($_POST['dls_primary_author']) ? sanitize_text_field(wp_unslash($_POST['dls_primary_author'])) : '';
    $editor_value = isset($_POST['dls_primary_editor']) ? sanitize_text_field(wp_unslash($_POST['dls_primary_editor'])) : '';

    $selected_items = [];
    $role_map = [];

    $author_selection = dls_na_ui_parse_selection_value($author_value);
    if (!empty($author_selection)) {
        $selected_items[] = $author_selection;
        $role_map[$author_value] = 'author';
    }

    $editor_selection = dls_na_ui_validate_editor_selection(dls_na_ui_parse_selection_value($editor_value));
    if (!empty($editor_selection)) {
        $selected_items[] = $editor_selection;
        $role_map[$editor_value] = 'editor';
    }

    if (function_exists('dls_native_authors_save_assignments_for_post')) {
        dls_native_authors_save_assignments_for_post($post_id, $selected_items, $role_map);
        return;
    }
}, 999, 2);
