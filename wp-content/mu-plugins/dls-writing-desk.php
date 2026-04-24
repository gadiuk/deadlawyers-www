<?php
/**
 * Plugin Name: DLS Writing Desk
 * Description: Clean writing interface for posts.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_writing_desk_page_hook')) {
    function dls_writing_desk_page_hook() {
        static $hook = '';

        return $hook;
    }
}

if (!function_exists('dls_writing_desk_set_page_hook')) {
    function dls_writing_desk_set_page_hook($hook) {
        static $stored = '';

        if (is_string($hook) && $hook !== '') {
            $stored = $hook;
        }

        return $stored;
    }
}

if (!function_exists('dls_writing_desk_languages')) {
    function dls_writing_desk_languages() {
        $languages = [
            'uk' => 'UK',
            'en' => 'EN',
        ];

        if (function_exists('pll_languages_list')) {
            $slugs = (array) pll_languages_list(['fields' => 'slug']);
            $names = (array) pll_languages_list(['fields' => 'name']);

            if (!empty($slugs)) {
                $languages = [];

                foreach ($slugs as $index => $slug) {
                    $slug = strtolower(trim((string) $slug));
                    if ($slug === '') {
                        continue;
                    }

                    $languages[$slug] = isset($names[$index]) && is_string($names[$index]) && $names[$index] !== ''
                        ? $names[$index]
                        : strtoupper($slug);
                }
            }
        }

        return $languages;
    }
}

if (!function_exists('dls_writing_desk_normalize_language')) {
    function dls_writing_desk_normalize_language($lang) {
        $lang = strtolower(trim((string) $lang));
        $languages = dls_writing_desk_languages();

        return isset($languages[$lang]) ? $lang : '';
    }
}

if (!function_exists('dls_writing_desk_wp_timezone')) {
    function dls_writing_desk_wp_timezone() {
        if (function_exists('wp_timezone')) {
            return wp_timezone();
        }

        $timezone_string = trim((string) get_option('timezone_string'));
        if ($timezone_string !== '') {
            try {
                return new DateTimeZone($timezone_string);
            } catch (Exception $exception) {
            }
        }

        $offset = (float) get_option('gmt_offset', 0);
        $hours = (int) $offset;
        $minutes = (int) round(abs($offset - $hours) * 60);
        $sign = $offset < 0 ? '-' : '+';

        return new DateTimeZone(sprintf('%s%02d:%02d', $sign, abs($hours), $minutes));
    }
}

if (!function_exists('dls_writing_desk_current_timestamp')) {
    function dls_writing_desk_current_timestamp() {
        if (function_exists('current_datetime')) {
            return current_datetime()->getTimestamp();
        }

        return current_time('timestamp');
    }
}

if (!function_exists('dls_writing_desk_wp_date')) {
    function dls_writing_desk_wp_date($format, $timestamp = null) {
        $timestamp = is_numeric($timestamp) ? (int) $timestamp : time();

        if (function_exists('wp_date')) {
            return wp_date($format, $timestamp, dls_writing_desk_wp_timezone());
        }

        $date = new DateTime('@' . $timestamp);
        $date->setTimezone(dls_writing_desk_wp_timezone());

        return $date->format($format);
    }
}

if (!function_exists('dls_writing_desk_get_post_language')) {
    function dls_writing_desk_get_post_language($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        if (function_exists('pll_get_post_language')) {
            $lang = dls_writing_desk_normalize_language(pll_get_post_language($post_id, 'slug'));
            if ($lang !== '') {
                return $lang;
            }
        }

        if (taxonomy_exists('language')) {
            $slugs = wp_get_post_terms($post_id, 'language', ['fields' => 'slugs']);
            if (is_array($slugs) && !empty($slugs)) {
                $lang = dls_writing_desk_normalize_language(reset($slugs));
                if ($lang !== '') {
                    return $lang;
                }
            }
        }

        return '';
    }
}

if (!function_exists('dls_writing_desk_set_post_language')) {
    function dls_writing_desk_set_post_language($post_id, $lang) {
        $post_id = absint($post_id);
        $lang = dls_writing_desk_normalize_language($lang);

        if ($post_id < 1 || $lang === '') {
            return;
        }

        if (function_exists('pll_set_post_language')) {
            pll_set_post_language($post_id, $lang);
            return;
        }

        if (taxonomy_exists('language')) {
            wp_set_post_terms($post_id, [$lang], 'language', false);
        }
    }
}

if (!function_exists('dls_writing_desk_parse_selection')) {
    function dls_writing_desk_parse_selection($value) {
        if (function_exists('dls_na_ui_parse_selection_value')) {
            return (array) dls_na_ui_parse_selection_value($value);
        }

        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        if (preg_match('/^(user|guest):(\d+)$/', $value, $matches) !== 1) {
            return [];
        }

        $type = strtolower((string) $matches[1]);
        $id = absint($matches[2]);
        if ($id < 1) {
            return [];
        }

        if ($type === 'guest') {
            $term = dls_writing_desk_get_guest_author_term($id);
            if (!($term instanceof WP_Term)) {
                return [];
            }

            return [
                'author_type' => 'guest',
                'term_id'     => (int) $term->term_id,
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
            'user_id'     => (int) $user->ID,
            'label'       => (string) $user->display_name,
        ];
    }
}

if (!function_exists('dls_writing_desk_detect_user_language')) {
    function dls_writing_desk_detect_user_language($user) {
        if (function_exists('dls_na_ui_detect_user_language')) {
            return strtolower(trim((string) dls_na_ui_detect_user_language($user)));
        }

        if (!($user instanceof WP_User)) {
            return '';
        }

        if (user_can($user, 'manage_options') || user_can($user, 'edit_others_posts')) {
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

if (!function_exists('dls_writing_desk_user_matches_mode')) {
    function dls_writing_desk_user_matches_mode($user, $mode) {
        if (!($user instanceof WP_User)) {
            return false;
        }

        $mode = strtolower(trim((string) $mode));

        if ($mode === 'editor') {
            return dls_writing_desk_user_has_role($user, ['editor', 'administrator'])
                || user_can($user, 'edit_others_posts')
                || user_can($user, 'manage_options');
        }

        return dls_writing_desk_user_has_role($user, ['author', 'editor', 'administrator'])
            || user_can($user, 'edit_posts')
            || user_can($user, 'manage_options');
    }
}

if (!function_exists('dls_writing_desk_detect_guest_language')) {
    function dls_writing_desk_detect_guest_language($term) {
        if (!($term instanceof WP_Term)) {
            return '';
        }

        if (function_exists('dls_native_authors_get_guest_author_language')) {
            $stored = strtolower(trim((string) dls_native_authors_get_guest_author_language((int) $term->term_id)));
            if (in_array($stored, ['uk', 'en'], true)) {
                return $stored;
            }
        }

        $stored = dls_writing_desk_normalize_language(get_term_meta((int) $term->term_id, '_dls_guest_author_language', true));
        if ($stored !== '') {
            return $stored;
        }

        $stored = dls_writing_desk_normalize_language(get_term_meta((int) $term->term_id, '_dls_author_language', true));
        if ($stored !== '') {
            return $stored;
        }

        if (function_exists('dls_na_ui_detect_guest_author_language')) {
            return strtolower(trim((string) dls_na_ui_detect_guest_author_language($term)));
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

if (!function_exists('dls_writing_desk_user_has_role')) {
    function dls_writing_desk_user_has_role($user, $roles) {
        if (function_exists('dls_na_ui_user_has_role')) {
            return (bool) dls_na_ui_user_has_role($user, $roles);
        }

        if (!($user instanceof WP_User)) {
            return false;
        }

        $roles = array_filter(array_map('strtolower', array_map('strval', (array) $roles)));
        $user_roles = array_map('strtolower', array_map('strval', (array) $user->roles));

        return !empty(array_intersect($roles, $user_roles));
    }
}

if (!function_exists('dls_writing_desk_extract_user_id_from_author_term')) {
    function dls_writing_desk_extract_user_id_from_author_term($term_id) {
        $term_id = absint($term_id);
        if ($term_id < 1) {
            return 0;
        }

        $user_id = absint(get_term_meta($term_id, 'user_id', true));
        if ($user_id > 0 && get_userdata($user_id)) {
            return $user_id;
        }

        $all_meta = get_term_meta($term_id);
        if (!is_array($all_meta)) {
            return 0;
        }

        foreach (array_keys($all_meta) as $meta_key) {
            if (strpos((string) $meta_key, 'user_id_') !== 0) {
                continue;
            }

            $candidate = absint(substr((string) $meta_key, 8));
            if ($candidate > 0 && get_userdata($candidate)) {
                return $candidate;
            }
        }

        return 0;
    }
}

if (!function_exists('dls_writing_desk_get_author_term_id_for_user')) {
    function dls_writing_desk_get_author_term_id_for_user($user_id) {
        $user_id = absint($user_id);
        if ($user_id < 1 || !taxonomy_exists('author')) {
            return 0;
        }

        $direct = get_terms([
            'taxonomy'   => 'author',
            'hide_empty' => false,
            'number'     => 1,
            'fields'     => 'ids',
            'meta_query' => [[
                'key'   => 'user_id',
                'value' => $user_id,
            ]],
        ]);

        if (is_array($direct) && !empty($direct)) {
            return absint(reset($direct));
        }

        $legacy = get_terms([
            'taxonomy'   => 'author',
            'hide_empty' => false,
            'number'     => 1,
            'fields'     => 'ids',
            'meta_query' => [[
                'key'     => 'user_id_' . $user_id,
                'compare' => 'EXISTS',
            ]],
        ]);

        if (is_array($legacy) && !empty($legacy)) {
            $term_id = absint(reset($legacy));
            if ($term_id > 0) {
                update_term_meta($term_id, 'user_id', $user_id);
            }

            return $term_id;
        }

        return 0;
    }
}

if (!function_exists('dls_writing_desk_get_guest_author_term')) {
    function dls_writing_desk_get_guest_author_term($term_id) {
        $term_id = absint($term_id);
        if ($term_id < 1 || !taxonomy_exists('author')) {
            return null;
        }

        $term = get_term($term_id, 'author');

        return $term instanceof WP_Term ? $term : null;
    }
}

if (!function_exists('dls_writing_desk_is_guest_author_term')) {
    function dls_writing_desk_is_guest_author_term($term) {
        if (!($term instanceof WP_Term) || $term->taxonomy !== 'author') {
            return false;
        }

        return dls_writing_desk_extract_user_id_from_author_term((int) $term->term_id) < 1;
    }
}

if (!function_exists('dls_writing_desk_get_guest_author_terms')) {
    function dls_writing_desk_get_guest_author_terms() {
        if (!taxonomy_exists('author')) {
            return [];
        }

        $terms = get_terms([
            'taxonomy'   => 'author',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
            'number'     => 5000,
        ]);

        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }

        $guest_terms = [];
        foreach ($terms as $term) {
            if ($term instanceof WP_Term && dls_writing_desk_is_guest_author_term($term)) {
                $guest_terms[] = $term;
            }
        }

        return $guest_terms;
    }
}

if (!function_exists('dls_writing_desk_stored_selection')) {
    function dls_writing_desk_stored_selection($post_id, $role) {
        $post_id = absint($post_id);
        $role = strtolower(trim((string) $role));
        if ($post_id < 1 || !in_array($role, ['author', 'editor'], true)) {
            return '';
        }

        return trim((string) get_post_meta($post_id, '_dls_writing_desk_' . $role . '_selection', true));
    }
}

if (!function_exists('dls_writing_desk_update_stored_selection')) {
    function dls_writing_desk_update_stored_selection($post_id, $role, $value) {
        $post_id = absint($post_id);
        $role = strtolower(trim((string) $role));
        $value = sanitize_text_field((string) $value);

        if ($post_id < 1 || !in_array($role, ['author', 'editor'], true)) {
            return;
        }

        if ($value === '') {
            delete_post_meta($post_id, '_dls_writing_desk_' . $role . '_selection');
            return;
        }

        update_post_meta($post_id, '_dls_writing_desk_' . $role . '_selection', $value);
    }
}

if (!function_exists('dls_writing_desk_save_publishpress_assignments')) {
    function dls_writing_desk_save_publishpress_assignments($post_id, $author_value, $editor_value) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return;
        }

        dls_writing_desk_update_stored_selection($post_id, 'author', $author_value);
        dls_writing_desk_update_stored_selection($post_id, 'editor', $editor_value);

        if (!taxonomy_exists('author')) {
            return;
        }

        $term_ids = [];
        $author_selection = dls_writing_desk_parse_selection($author_value);
        if (!empty($author_selection)) {
            if (($author_selection['author_type'] ?? 'user') === 'guest') {
                $term_id = absint($author_selection['term_id'] ?? 0);
                if ($term_id > 0) {
                    $term_ids[] = $term_id;
                }
            } else {
                $user_id = absint($author_selection['user_id'] ?? 0);
                $term_id = function_exists('dls_native_authors_get_author_term_id_for_user')
                    ? absint(dls_native_authors_get_author_term_id_for_user($user_id))
                    : dls_writing_desk_get_author_term_id_for_user($user_id);
                if ($term_id > 0) {
                    $term_ids[] = $term_id;
                }
                if ($user_id > 0) {
                    wp_update_post([
                        'ID'          => $post_id,
                        'post_author' => $user_id,
                    ]);
                }
            }
        }

        wp_set_post_terms($post_id, array_values(array_unique(array_map('absint', $term_ids))), 'author', false);
    }
}

if (!function_exists('dls_writing_desk_dropdown_options')) {
    function dls_writing_desk_dropdown_options($mode, $language = '', $ensure_value = '') {
        $mode = strtolower(trim((string) $mode));
        $language = dls_writing_desk_normalize_language($language);
        $items = [];

        if (function_exists('dls_native_authors_get_users')) {
            $users = (array) dls_native_authors_get_users();
        } else {
            $users = get_users([
                'orderby' => 'display_name',
                'order'   => 'ASC',
                'number'  => 5000,
            ]);
        }

        foreach ($users as $user) {
            if (!($user instanceof WP_User)) {
                continue;
            }

            if (!dls_writing_desk_user_matches_mode($user, $mode)) {
                continue;
            }

            $item_lang = dls_writing_desk_detect_user_language($user);
            if ($language !== '' && $item_lang !== '' && $item_lang !== $language) {
                continue;
            }

            $role_badge = '';
            if (dls_writing_desk_user_has_role($user, ['administrator'])) {
                $role_badge = ' (administrator)';
            } elseif (dls_writing_desk_user_has_role($user, ['editor'])) {
                $role_badge = ' (editor)';
            }

            $items[] = [
                'value' => 'user:' . (int) $user->ID,
                'label' => (string) $user->display_name . $role_badge,
                'lang'  => $item_lang,
            ];
        }

        if ($mode !== 'editor') {
            $guest_terms = function_exists('dls_native_authors_get_guest_author_terms')
                ? (array) dls_native_authors_get_guest_author_terms()
                : dls_writing_desk_get_guest_author_terms();

            foreach ($guest_terms as $term) {
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $item_lang = dls_writing_desk_detect_guest_language($term);
                if ($language !== '' && $item_lang !== '' && $item_lang !== $language) {
                    continue;
                }

                $items[] = [
                    'value' => 'guest:' . (int) $term->term_id,
                    'label' => (string) $term->name . ' (guest)',
                    'lang'  => $item_lang,
                ];
            }
        }

        if ($ensure_value !== '') {
            $known_values = array_column($items, 'value');
            if (!in_array($ensure_value, $known_values, true)) {
                $parsed = dls_writing_desk_parse_selection($ensure_value);
                if (!empty($parsed)) {
                    $extra_label = trim((string) ($parsed['label'] ?? ''));
                    if (($parsed['author_type'] ?? 'user') === 'guest') {
                        $extra_label .= ' (guest)';
                    }

                    if ($extra_label !== '') {
                        $items[] = [
                            'value' => $ensure_value,
                            'label' => $extra_label,
                            'lang'  => '',
                        ];
                    }
                }
            }
        }

        usort($items, static function ($a, $b) {
            return strnatcasecmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });

        return $items;
    }
}

if (!function_exists('dls_writing_desk_get_hierarchical_terms')) {
    function dls_writing_desk_get_hierarchical_terms($taxonomy) {
        $taxonomy = sanitize_key((string) $taxonomy);
        if ($taxonomy === '' || !taxonomy_exists($taxonomy)) {
            return [];
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }

        $by_parent = [];
        foreach ($terms as $term) {
            if (!($term instanceof WP_Term)) {
                continue;
            }

            $parent_id = (int) $term->parent;
            if (!isset($by_parent[$parent_id])) {
                $by_parent[$parent_id] = [];
            }
            $by_parent[$parent_id][] = $term;
        }

        $ordered = [];
        $walk = static function ($parent_id, $depth) use (&$walk, &$ordered, $by_parent) {
            if (empty($by_parent[$parent_id])) {
                return;
            }

            foreach ($by_parent[$parent_id] as $term) {
                $ordered[] = [
                    'term'  => $term,
                    'depth' => $depth,
                ];

                $walk((int) $term->term_id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $ordered;
    }
}

if (!function_exists('dls_writing_desk_extra_taxonomies')) {
    function dls_writing_desk_extra_taxonomies() {
        $candidates = [
            'companies'   => 'Companies',
            'company'     => 'Companies',
            'individuals' => 'Individuals',
            'individual'  => 'Individuals',
            'people'      => 'Individuals',
            'person'      => 'Individuals',
            'post_tag'    => 'Tags',
        ];

        $items = [];

        foreach ($candidates as $taxonomy => $label) {
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }

            if (isset($items[$taxonomy])) {
                continue;
            }

            $obj = get_taxonomy($taxonomy);
            if (!$obj || !is_object_in_taxonomy('post', $taxonomy)) {
                continue;
            }

            $items[$taxonomy] = [
                'taxonomy'     => $taxonomy,
                'label'        => $label,
                'hierarchical' => !empty($obj->hierarchical),
            ];
        }

        return array_values($items);
    }
}

if (!function_exists('dls_writing_desk_taxonomy_terms')) {
    function dls_writing_desk_taxonomy_terms($taxonomy) {
        $taxonomy = sanitize_key((string) $taxonomy);
        if ($taxonomy === '' || !taxonomy_exists($taxonomy)) {
            return [];
        }

        $taxonomy_object = get_taxonomy($taxonomy);
        if (!$taxonomy_object) {
            return [];
        }

        if (!empty($taxonomy_object->hierarchical)) {
            $items = dls_writing_desk_get_hierarchical_terms($taxonomy);
        } else {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);

            if (is_wp_error($terms) || !is_array($terms)) {
                return [];
            }

            $items = [];
            foreach ($terms as $term) {
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $items[] = [
                    'term'  => $term,
                    'depth' => 0,
                ];
            }
        }

        foreach ($items as $index => $item) {
            $term = $item['term'] ?? null;
            $items[$index]['lang'] = dls_writing_desk_detect_term_language($term);
        }

        return $items;
    }
}

if (!function_exists('dls_writing_desk_detect_term_language')) {
    function dls_writing_desk_detect_term_language($term) {
        if (!($term instanceof WP_Term)) {
            return '';
        }

        if (function_exists('pll_get_term_language')) {
            $lang = dls_writing_desk_normalize_language(pll_get_term_language((int) $term->term_id, 'slug'));
            if ($lang !== '') {
                return $lang;
            }
        }

        $stored = dls_writing_desk_normalize_language(get_term_meta((int) $term->term_id, '_dls_term_language', true));
        if ($stored !== '') {
            return $stored;
        }

        return '';
    }
}

if (!function_exists('dls_writing_desk_apply_term_language')) {
    function dls_writing_desk_apply_term_language($term_id, $language) {
        $term_id = absint($term_id);
        $language = dls_writing_desk_normalize_language($language);

        if ($term_id < 1 || $language === '') {
            return;
        }

        if (function_exists('pll_set_term_language')) {
            pll_set_term_language($term_id, $language);
        }

        update_term_meta($term_id, '_dls_term_language', $language);
    }
}

if (!function_exists('dls_writing_desk_find_existing_term')) {
    function dls_writing_desk_find_existing_term($taxonomy, $name, $language = '') {
        $taxonomy = sanitize_key((string) $taxonomy);
        $name = trim((string) $name);
        $language = dls_writing_desk_normalize_language($language);

        if ($taxonomy === '' || $name === '' || !taxonomy_exists($taxonomy)) {
            return null;
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'name'       => $name,
        ]);

        if (is_wp_error($terms) || !is_array($terms)) {
            return null;
        }

        foreach ($terms as $term) {
            if (!($term instanceof WP_Term)) {
                continue;
            }

            $term_language = dls_writing_desk_detect_term_language($term);
            if ($language === '' || $term_language === '' || $term_language === $language) {
                return $term;
            }
        }

        return null;
    }
}

if (!function_exists('dls_writing_desk_create_terms_from_input')) {
    function dls_writing_desk_create_terms_from_input($taxonomy, $raw_value, $language = '') {
        $taxonomy = sanitize_key((string) $taxonomy);
        $language = dls_writing_desk_normalize_language($language);

        if ($taxonomy === '' || !taxonomy_exists($taxonomy)) {
            return [];
        }

        $names = preg_split('/[\r\n,]+/', (string) $raw_value);
        if (!is_array($names)) {
            return [];
        }

        $term_ids = [];

        foreach ($names as $name) {
            $name = sanitize_text_field((string) $name);
            if ($name === '') {
                continue;
            }

            $existing = dls_writing_desk_find_existing_term($taxonomy, $name, $language);
            if ($existing instanceof WP_Term) {
                $term_ids[] = (int) $existing->term_id;
                continue;
            }

            $insert_args = [];
            if ($language !== '') {
                $insert_args['slug'] = sanitize_title($name . '-' . $language);
            }

            $created = wp_insert_term($name, $taxonomy, $insert_args);
            if (is_wp_error($created)) {
                $created = wp_insert_term($name, $taxonomy);
            }
            if (is_wp_error($created)) {
                continue;
            }

            $term_id = absint($created['term_id'] ?? 0);
            if ($term_id < 1) {
                continue;
            }

            dls_writing_desk_apply_term_language($term_id, $language);
            $term_ids[] = $term_id;
        }

        return array_values(array_unique(array_map('absint', $term_ids)));
    }
}

if (!function_exists('dls_writing_desk_current_datetime_value')) {
    function dls_writing_desk_current_datetime_value($post = null) {
        if ($post instanceof WP_Post) {
            $value = get_post_time('Y-m-d\TH:i', false, $post, true);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return dls_writing_desk_wp_date('Y-m-d\TH:i', time() + 3600);
    }
}

if (!function_exists('dls_writing_desk_preview_link')) {
    function dls_writing_desk_preview_link($post) {
        if (!($post instanceof WP_Post)) {
            return '';
        }

        if ($post->post_status === 'publish') {
            return (string) get_permalink($post->ID);
        }

        return (string) get_preview_post_link($post->ID);
    }
}

if (!function_exists('dls_writing_desk_legacy_people_from_terms')) {
    function dls_writing_desk_legacy_people_from_terms($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1 || !taxonomy_exists('author')) {
            return [];
        }

        $terms = wp_get_post_terms($post_id, 'author');
        if (empty($terms) || is_wp_error($terms)) {
            return [];
        }

        $items = [];
        foreach ($terms as $term) {
            if (!($term instanceof WP_Term)) {
                continue;
            }

            $user_id = function_exists('dls_native_authors_extract_user_id_from_term')
                ? absint(dls_native_authors_extract_user_id_from_term((int) $term->term_id))
                : dls_writing_desk_extract_user_id_from_author_term((int) $term->term_id);

            if ($user_id > 0) {
                $items[] = 'user:' . $user_id;
                continue;
            }

            $is_guest = function_exists('dls_native_authors_is_guest_author_term')
                ? dls_native_authors_is_guest_author_term($term)
                : dls_writing_desk_is_guest_author_term($term);
            if ($is_guest) {
                $items[] = 'guest:' . (int) $term->term_id;
            }
        }

        return array_values(array_unique(array_filter($items)));
    }
}

if (!function_exists('dls_writing_desk_selection_language')) {
    function dls_writing_desk_selection_language($value) {
        $parsed = dls_writing_desk_parse_selection($value);
        if (empty($parsed)) {
            return '';
        }

        if (($parsed['author_type'] ?? 'user') === 'guest') {
            $term = function_exists('dls_native_authors_get_guest_author_term')
                ? dls_native_authors_get_guest_author_term((int) ($parsed['term_id'] ?? 0))
                : dls_writing_desk_get_guest_author_term((int) ($parsed['term_id'] ?? 0));
            return dls_writing_desk_detect_guest_language($term);
        }

        $user = get_userdata((int) ($parsed['user_id'] ?? 0));
        return dls_writing_desk_detect_user_language($user);
    }
}

if (!function_exists('dls_writing_desk_get_post_kicker')) {
    function dls_writing_desk_get_post_kicker($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        return (string) get_post_meta($post_id, '_dls_writing_desk_kicker', true);
    }
}

if (!function_exists('dls_writing_desk_update_post_kicker')) {
    function dls_writing_desk_update_post_kicker($post_id, $value) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return;
        }

        $value = sanitize_text_field((string) $value);

        if ($value === '') {
            delete_post_meta($post_id, '_dls_writing_desk_kicker');
            return;
        }

        update_post_meta($post_id, '_dls_writing_desk_kicker', $value);
    }
}

if (!function_exists('dls_writing_desk_validate_editor_selection')) {


    function dls_writing_desk_validate_editor_selection($selection) {
        if (function_exists('dls_na_ui_validate_editor_selection')) {
            return (array) dls_na_ui_validate_editor_selection($selection);
        }

        if (!is_array($selection) || ($selection['author_type'] ?? 'user') !== 'user') {
            return [];
        }

        $user = get_userdata((int) ($selection['user_id'] ?? 0));
        if (!($user instanceof WP_User)) {
            return [];
        }

        if (!user_can($user, 'edit_others_posts') && !user_can($user, 'manage_options')) {
            return [];
        }

        return $selection;
    }
}

if (!function_exists('dls_writing_desk_get_selected_people')) {
    function dls_writing_desk_get_selected_people($post_id) {
        $selected = [
            'author' => '',
            'editor' => '',
        ];

        $stored_author = dls_writing_desk_stored_selection($post_id, 'author');
        if ($stored_author !== '') {
            $selected['author'] = $stored_author;
        }

        $stored_editor = dls_writing_desk_stored_selection($post_id, 'editor');
        if ($stored_editor !== '') {
            $selected['editor'] = $stored_editor;
        }

        if (function_exists('dls_native_authors_get_post_assignments')) {
            foreach ((array) dls_native_authors_get_post_assignments($post_id) as $row) {
                $role = strtolower(trim((string) ($row['post_role'] ?? 'author')));
                $type = strtolower(trim((string) ($row['author_type'] ?? 'user')));

                if ($type === 'guest') {
                    $value = 'guest:' . absint($row['term_id'] ?? 0);
                } else {
                    $value = 'user:' . absint($row['user_id'] ?? 0);
                }

                if ($value === 'guest:0' || $value === 'user:0') {
                    continue;
                }

                if ($role === 'editor' && $selected['editor'] === '') {
                    $selected['editor'] = $value;
                }

                if ($role !== 'editor' && $selected['author'] === '') {
                    $selected['author'] = $value;
                }
            }
        }

        if ($selected['author'] === '') {
            $legacy_items = dls_writing_desk_legacy_people_from_terms($post_id);
            if (!empty($legacy_items)) {
                $selected['author'] = (string) reset($legacy_items);
            }
        }

        if ($selected['author'] === '') {
            $post = get_post($post_id);
            if ($post instanceof WP_Post && (int) $post->post_author > 0) {
                $selected['author'] = 'user:' . (int) $post->post_author;
            }
        }

        return $selected;
    }
}

if (!function_exists('dls_writing_desk_recent_posts')) {
    function dls_writing_desk_recent_posts() {
        $args = [
            'post_type'      => 'post',
            'post_status'    => ['draft', 'pending', 'future', 'publish', 'private'],
            'posts_per_page' => -1,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ];

        if (!current_user_can('edit_others_posts')) {
            $args['author'] = get_current_user_id();
        }

        return get_posts($args);
    }
}

if (!function_exists('dls_writing_desk_destination_platforms')) {
    function dls_writing_desk_destination_platforms() {
        return [
            'facebook' => 'Facebook Page',
            'linkedin' => 'LinkedIn Page',
            'telegram' => 'Telegram Channel',
        ];
    }
}

if (!function_exists('dls_writing_desk_get_social_destinations')) {
    function dls_writing_desk_get_social_destinations() {
        $platforms = dls_writing_desk_destination_platforms();
        $stored = get_option('dls_writing_desk_destinations', []);
        if (!is_array($stored)) {
            return [];
        }

        $items = [];
        $seen = [];

        foreach ($stored as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $platform = sanitize_key((string) ($row['platform'] ?? ''));
            if (!isset($platforms[$platform])) {
                continue;
            }

            $name = sanitize_text_field((string) ($row['name'] ?? ''));
            $destination = sanitize_text_field((string) ($row['destination'] ?? ''));
            $token = sanitize_text_field((string) ($row['token'] ?? ''));
            $key = sanitize_key((string) ($row['key'] ?? ''));
            if ($key === '') {
                $base = sanitize_title($platform . '-' . ($name !== '' ? $name : ($destination !== '' ? $destination : ('destination-' . $index))));
                $key = sanitize_key($base !== '' ? $base : ($platform . '_' . $index));
            }

            if ($name === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $items[] = [
                'key'         => $key,
                'platform'    => $platform,
                'platform_ui' => $platforms[$platform],
                'name'        => $name,
                'destination' => $destination,
                'token'       => $token,
                'active'      => !empty($row['active']),
            ];
        }

        return $items;
    }
}

if (!function_exists('dls_writing_desk_get_post_social_settings')) {
    function dls_writing_desk_get_post_social_settings($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return [];
        }

        $stored = get_post_meta($post_id, '_dls_writing_desk_social_settings', true);
        if (!is_array($stored)) {
            return [];
        }

        $items = [];
        foreach ($stored as $key => $row) {
            if (!is_array($row)) {
                continue;
            }

            $clean_key = sanitize_key((string) $key);
            if ($clean_key === '') {
                continue;
            }

            $items[$clean_key] = [
                'enabled'     => !empty($row['enabled']),
                'description' => sanitize_textarea_field((string) ($row['description'] ?? '')),
                'image_id'    => absint($row['image_id'] ?? 0),
                'button_text' => sanitize_text_field((string) ($row['button_text'] ?? '')),
                'button_url'  => esc_url_raw((string) ($row['button_url'] ?? '')),
                'buttons'     => dls_writing_desk_sanitize_telegram_buttons($row['buttons'] ?? [], (string) ($row['button_text'] ?? ''), (string) ($row['button_url'] ?? '')),
                'silent'      => !empty($row['silent']),
                'pin'         => !empty($row['pin']),
                'auto_delete' => absint($row['auto_delete'] ?? 0),
            ];
        }

        return $items;
    }
}

if (!function_exists('dls_writing_desk_sanitize_telegram_buttons')) {
    function dls_writing_desk_sanitize_telegram_buttons($buttons, $legacy_text = '', $legacy_url = '') {
        $items = [];

        foreach ((array) $buttons as $button) {
            if (!is_array($button)) {
                continue;
            }

            $text = sanitize_text_field((string) ($button['text'] ?? ''));
            $url = esc_url_raw((string) ($button['url'] ?? ''));
            if ($text === '') {
                continue;
            }

            $items[] = [
                'text' => $text,
                'url'  => $url,
            ];
        }

        if (empty($items)) {
            $legacy_text = sanitize_text_field((string) $legacy_text);
            $legacy_url = esc_url_raw((string) $legacy_url);
            if ($legacy_text !== '') {
                $items[] = [
                    'text' => $legacy_text,
                    'url'  => $legacy_url,
                ];
            }
        }

        return $items;
    }
}

if (!function_exists('dls_writing_desk_get_telegram_log')) {
    function dls_writing_desk_get_telegram_log($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return [];
        }

        $stored = get_post_meta($post_id, '_dls_writing_desk_telegram_log', true);

        return is_array($stored) ? $stored : [];
    }
}

if (!function_exists('dls_writing_desk_update_telegram_log')) {
    function dls_writing_desk_update_telegram_log($post_id, $destination_key, $row) {
        $post_id = absint($post_id);
        $destination_key = sanitize_key((string) $destination_key);
        if ($post_id < 1 || $destination_key === '' || !is_array($row)) {
            return;
        }

        $log = dls_writing_desk_get_telegram_log($post_id);
        $log[$destination_key] = [
            'status'     => sanitize_key((string) ($row['status'] ?? '')),
            'message'    => sanitize_text_field((string) ($row['message'] ?? '')),
            'message_id' => absint($row['message_id'] ?? 0),
            'sent_at'    => current_time('mysql'),
        ];

        update_post_meta($post_id, '_dls_writing_desk_telegram_log', $log);
    }
}

if (!function_exists('dls_writing_desk_telegram_reply_markup')) {
    function dls_writing_desk_telegram_reply_markup($settings, $post_id, $destination_key) {
        $buttons = dls_writing_desk_sanitize_telegram_buttons($settings['buttons'] ?? [], (string) ($settings['button_text'] ?? ''), (string) ($settings['button_url'] ?? ''));
        if (empty($buttons)) {
            return '';
        }

        $rows = [];
        foreach ($buttons as $index => $button) {
            $text = (string) ($button['text'] ?? '');
            $url = esc_url_raw((string) ($button['url'] ?? ''));
            if ($text === '') {
                continue;
            }

            $item = ['text' => $text];
            if ($url !== '') {
                $item['url'] = $url;
            } else {
                $item['callback_data'] = substr('dls_' . md5((string) $post_id . '|' . (string) $destination_key . '|' . (string) $index . '|' . $text), 0, 32);
            }

            $rows[] = [$item];
        }

        return empty($rows) ? '' : (string) wp_json_encode(['inline_keyboard' => $rows]);
    }
}

if (!function_exists('dls_writing_desk_telegram_text')) {
    function dls_writing_desk_telegram_text($post_id, $settings) {
        $post_id = absint($post_id);
        $description = trim((string) ($settings['description'] ?? ''));
        if ($description !== '') {
            return $description;
        }

        $parts = [];
        $title = get_the_title($post_id);
        if ($title !== '') {
            $parts[] = $title;
        }

        $lead = trim((string) get_post_field('post_excerpt', $post_id));
        if ($lead !== '') {
            $parts[] = $lead;
        }

        $url = get_permalink($post_id);
        if (is_string($url) && $url !== '') {
            $parts[] = $url;
        }

        return implode("\n\n", array_filter($parts));
    }
}

if (!function_exists('dls_writing_desk_limit_text')) {
    function dls_writing_desk_limit_text($text, $limit) {
        $text = (string) $text;
        $limit = absint($limit);

        if ($limit < 1 || strlen($text) <= $limit) {
            return $text;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $limit);
        }

        return substr($text, 0, $limit);
    }
}

if (!function_exists('dls_writing_desk_telegram_api')) {
    function dls_writing_desk_telegram_api($token, $method, $body) {
        $token = trim((string) $token);
        $method = preg_replace('/[^A-Za-z]/', '', (string) $method);
        if ($token === '' || $method === '') {
            return new WP_Error('telegram_missing_config', 'Missing Telegram bot token.');
        }

        $response = wp_remote_post('https://api.telegram.org/bot' . $token . '/' . $method, [
            'timeout' => 20,
            'body'    => $body,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($decoded) || empty($decoded['ok'])) {
            $message = is_array($decoded) && !empty($decoded['description'])
                ? (string) $decoded['description']
                : 'Telegram request failed.';

            return new WP_Error('telegram_api_error', $message);
        }

        return $decoded['result'] ?? [];
    }
}

if (!function_exists('dls_writing_desk_send_telegram_destination')) {
    function dls_writing_desk_send_telegram_destination($post_id, $destination, $settings) {
        $post_id = absint($post_id);
        $destination_key = sanitize_key((string) ($destination['key'] ?? ''));
        $chat_id = trim((string) ($destination['destination'] ?? ''));
        $token = trim((string) ($destination['token'] ?? ''));
        $text = trim(dls_writing_desk_telegram_text($post_id, $settings));

        if ($post_id < 1 || $chat_id === '' || $token === '') {
            return new WP_Error('telegram_missing_config', 'Telegram channel or token is missing.');
        }

        if ($text === '') {
            return new WP_Error('telegram_empty_message', 'Telegram message is empty.');
        }

        $reply_markup = dls_writing_desk_telegram_reply_markup($settings, $post_id, $destination_key);

        $image_id = absint($settings['image_id'] ?? 0);
        if ($image_id < 1) {
            $image_id = absint(get_post_thumbnail_id($post_id));
        }

        $image_url = $image_id > 0 ? (string) wp_get_attachment_image_url($image_id, 'large') : '';
        $body = [
            'chat_id'              => $chat_id,
            'disable_notification' => !empty($settings['silent']) ? 'true' : 'false',
        ];

        if ($reply_markup !== '') {
            $body['reply_markup'] = $reply_markup;
        }

        if ($image_url !== '') {
            $body['photo'] = $image_url;
            $body['caption'] = dls_writing_desk_limit_text($text, 1000);
            $result = dls_writing_desk_telegram_api($token, 'sendPhoto', $body);
        } else {
            $body['text'] = dls_writing_desk_limit_text($text, 3900);
            $result = dls_writing_desk_telegram_api($token, 'sendMessage', $body);
        }

        if (is_wp_error($result)) {
            return $result;
        }

        $message_id = absint($result['message_id'] ?? 0);
        if (!empty($settings['pin']) && $message_id > 0) {
            dls_writing_desk_telegram_api($token, 'pinChatMessage', [
                'chat_id'              => $chat_id,
                'message_id'           => $message_id,
                'disable_notification' => !empty($settings['silent']) ? 'true' : 'false',
            ]);
        }

        $auto_delete = absint($settings['auto_delete'] ?? 0);
        if ($auto_delete > 0 && $message_id > 0) {
            wp_schedule_single_event(time() + ($auto_delete * MINUTE_IN_SECONDS), 'dls_writing_desk_telegram_delete_message', [
                $token,
                $chat_id,
                $message_id,
            ]);
        }

        return [
            'message_id' => $message_id,
        ];
    }
}

if (!function_exists('dls_writing_desk_send_enabled_telegram')) {
    function dls_writing_desk_send_enabled_telegram($post_id, $destinations, $social_settings) {
        $sent = 0;
        $failed = 0;

        foreach ((array) $destinations as $destination) {
            $key = sanitize_key((string) ($destination['key'] ?? ''));
            if ($key === '' || ($destination['platform'] ?? '') !== 'telegram' || empty($destination['active'])) {
                continue;
            }

            $settings = is_array($social_settings[$key] ?? null) ? $social_settings[$key] : [];
            if (empty($settings['enabled'])) {
                continue;
            }

            $result = dls_writing_desk_send_telegram_destination($post_id, $destination, $settings);
            if (is_wp_error($result)) {
                $failed++;
                dls_writing_desk_update_telegram_log($post_id, $key, [
                    'status'  => 'failed',
                    'message' => $result->get_error_message(),
                ]);
                continue;
            }

            $sent++;
            dls_writing_desk_update_telegram_log($post_id, $key, [
                'status'     => 'sent',
                'message'    => 'Sent to Telegram.',
                'message_id' => absint($result['message_id'] ?? 0),
            ]);
        }

        return [
            'sent'   => $sent,
            'failed' => $failed,
        ];
    }
}

if (!function_exists('dls_writing_desk_telegram_delete_message')) {
    function dls_writing_desk_telegram_delete_message($token, $chat_id, $message_id) {
        dls_writing_desk_telegram_api($token, 'deleteMessage', [
            'chat_id'    => $chat_id,
            'message_id' => absint($message_id),
        ]);
    }
}
add_action('dls_writing_desk_telegram_delete_message', 'dls_writing_desk_telegram_delete_message', 10, 3);

if (!function_exists('dls_writing_desk_send_scheduled_telegram')) {
    function dls_writing_desk_send_scheduled_telegram($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return;
        }

        $destinations = dls_writing_desk_get_social_destinations();
        $settings = dls_writing_desk_get_post_social_settings($post_id);
        dls_writing_desk_send_enabled_telegram($post_id, $destinations, $settings);
    }
}
add_action('dls_writing_desk_telegram_scheduled_send', 'dls_writing_desk_send_scheduled_telegram', 10, 1);

if (!function_exists('dls_writing_desk_save_social_destinations')) {
    function dls_writing_desk_save_social_destinations() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to manage social destinations.');
        }

        check_admin_referer('dls_writing_desk_destinations_save', 'dls_writing_desk_destinations_nonce');

        $platforms = dls_writing_desk_destination_platforms();
        $rows = isset($_POST['dls_writing_desk_destinations']) ? (array) wp_unslash($_POST['dls_writing_desk_destinations']) : [];
        $items = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $platform = sanitize_key((string) ($row['platform'] ?? ''));
            if (!isset($platforms[$platform])) {
                continue;
            }

            $name = sanitize_text_field((string) ($row['name'] ?? ''));
            $destination = sanitize_text_field((string) ($row['destination'] ?? ''));
            $token = sanitize_text_field((string) ($row['token'] ?? ''));
            $key = sanitize_key((string) ($row['key'] ?? ''));
            if ($key === '') {
                $base = sanitize_title($platform . '-' . ($name !== '' ? $name : ($destination !== '' ? $destination : ('destination-' . $index))));
                $key = sanitize_key($base !== '' ? $base : ($platform . '_' . $index));
            }

            if ($name === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $items[] = [
                'key'         => $key,
                'platform'    => $platform,
                'name'        => $name,
                'destination' => $destination,
                'token'       => $token,
                'active'      => !empty($row['active']) ? 1 : 0,
            ];
        }

        update_option('dls_writing_desk_destinations', $items, false);

        $redirect_url = add_query_arg([
            'page'        => 'dls-writing-desk-destinations',
            'desk_notice' => 'destinations_saved',
        ], admin_url('admin.php'));

        wp_safe_redirect($redirect_url);
        exit;
    }
}
add_action('admin_post_dls_writing_desk_destinations_save', 'dls_writing_desk_save_social_destinations');

if (!function_exists('dls_writing_desk_hide_admin_notices')) {
    function dls_writing_desk_hide_admin_notices() {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if (!in_array($page, ['dls-writing-desk', 'dls-writing-desk-destinations', 'dls-writing-desk-access'], true)) {
            return;
        }

        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
    }
}
add_action('in_admin_header', 'dls_writing_desk_hide_admin_notices', 1);

if (!function_exists('dls_writing_desk_access_elements')) {
    function dls_writing_desk_access_elements() {
        return [
            'topbar_telegram' => 'Telegram Broadcast link',
            'topbar_social'   => 'Social Destinations link',
            'topbar_preview'  => 'Preview button',
            'wp_editor_link'  => 'WordPress Editor link',
            'publish_box'     => 'Publish / schedule box',
            'share_draft'     => 'Share draft link',
            'language'        => 'Language selector',
            'people'          => 'Author / editor selector',
            'categories'      => 'Categories',
            'taxonomies'      => 'Companies, individuals and tags',
            'social'          => 'Facebook / LinkedIn preparation',
            'featured_image'  => 'Featured image',
        ];
    }
}

if (!function_exists('dls_writing_desk_access_roles')) {
    function dls_writing_desk_access_roles() {
        return [
            'administrator' => 'Administrators',
            'editor'        => 'Editors',
            'author'        => 'Authors',
            'contributor'   => 'Contributors',
        ];
    }
}

if (!function_exists('dls_writing_desk_default_access_settings')) {
    function dls_writing_desk_default_access_settings() {
        $settings = [];
        $elements = array_keys(dls_writing_desk_access_elements());

        foreach (array_keys(dls_writing_desk_access_roles()) as $role) {
            $settings[$role] = array_fill_keys($elements, 1);
        }

        return $settings;
    }
}

if (!function_exists('dls_writing_desk_get_access_settings')) {
    function dls_writing_desk_get_access_settings() {
        $defaults = dls_writing_desk_default_access_settings();
        $stored = get_option('dls_writing_desk_access_rights', []);
        if (!is_array($stored)) {
            return $defaults;
        }

        foreach ($defaults as $role => $elements) {
            foreach ($elements as $element => $enabled) {
                if (isset($stored[$role]) && is_array($stored[$role]) && array_key_exists($element, $stored[$role])) {
                    $defaults[$role][$element] = !empty($stored[$role][$element]) ? 1 : 0;
                }
            }
        }

        return $defaults;
    }
}

if (!function_exists('dls_writing_desk_current_access_role')) {
    function dls_writing_desk_current_access_role() {
        $user = wp_get_current_user();
        $roles = is_array($user->roles ?? null) ? array_map('sanitize_key', $user->roles) : [];

        foreach (['administrator', 'editor', 'author', 'contributor'] as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        if (current_user_can('manage_options')) {
            return 'administrator';
        }

        if (current_user_can('edit_others_posts')) {
            return 'editor';
        }

        return 'author';
    }
}

if (!function_exists('dls_writing_desk_can_show_element')) {
    function dls_writing_desk_can_show_element($element) {
        if (current_user_can('manage_options')) {
            return true;
        }

        $element = sanitize_key((string) $element);
        $settings = dls_writing_desk_get_access_settings();
        $role = dls_writing_desk_current_access_role();

        return !isset($settings[$role][$element]) || !empty($settings[$role][$element]);
    }
}

if (!function_exists('dls_writing_desk_save_access_rights')) {
    function dls_writing_desk_save_access_rights() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to manage access rights.');
        }

        check_admin_referer('dls_writing_desk_access_save', 'dls_writing_desk_access_nonce');

        $payload = isset($_POST['dls_writing_desk_access']) ? (array) wp_unslash($_POST['dls_writing_desk_access']) : [];
        $settings = [];
        foreach (dls_writing_desk_access_roles() as $role => $role_label) {
            foreach (dls_writing_desk_access_elements() as $element => $element_label) {
                $settings[$role][$element] = !empty($payload[$role][$element]) ? 1 : 0;
            }
        }

        update_option('dls_writing_desk_access_rights', $settings, false);
        wp_safe_redirect(add_query_arg([
            'page'        => 'dls-writing-desk-access',
            'desk_notice' => 'access_saved',
        ], admin_url('admin.php')));
        exit;
    }
}
add_action('admin_post_dls_writing_desk_access_save', 'dls_writing_desk_save_access_rights');

if (!function_exists('dls_writing_desk_notice_message')) {
    function dls_writing_desk_notice_message($code) {
        $code = strtolower(trim((string) $code));

        if ($code === 'saved') {
            return 'Draft saved.';
        }

        if ($code === 'destinations_saved') {
            return 'Destinations saved.';
        }

        if ($code === 'access_saved') {
            return 'Access rights saved.';
        }

        if ($code === 'checklist_blocked') {
            return 'Saved. Complete the publishing checklist before publishing.';
        }

        if ($code === 'published') {
            return 'Post published.';
        }

        if ($code === 'updated') {
            return 'Post updated.';
        }

        if ($code === 'scheduled') {
            return 'Post scheduled.';
        }

        if ($code === 'telegram_sent') {
            return 'Telegram post sent.';
        }

        if ($code === 'telegram_partial') {
            return 'Telegram post sent to some channels. Check delivery status.';
        }

        if ($code === 'telegram_failed') {
            return 'Telegram sending failed. Check delivery status.';
        }

        if ($code === 'telegram_empty') {
            return 'No Telegram channels were enabled for sending.';
        }

        if ($code === 'telegram_scheduled') {
            return 'Telegram broadcast scheduled.';
        }

        return '';
    }
}

if (!function_exists('dls_writing_desk_render_frontend_intro')) {
    function dls_writing_desk_render_frontend_intro($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        $kicker = trim((string) dls_writing_desk_get_post_kicker($post_id));
        $lead = trim((string) get_post_field('post_excerpt', $post_id));

        if ($kicker === '' && $lead === '') {
            return '';
        }

        $html = '<div class="dls-writing-desk-frontend-intro">';
        if ($kicker !== '') {
            $html .= '<div class="dls-writing-desk-frontend-kicker">' . esc_html($kicker) . '</div>';
        }
        if ($lead !== '') {
            $html .= '<p class="dls-writing-desk-frontend-lead">' . esc_html($lead) . '</p>';
        }
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('dls_writing_desk_normalize_saved_content')) {
    function dls_writing_desk_normalize_saved_content($content) {
        $content = trim(str_replace(["\r\n", "\r"], "\n", (string) $content));
        if ($content === '') {
            return '';
        }

        $has_paragraphs = preg_match('/<p\b/i', $content) === 1;
        $has_block_editor_markup = function_exists('has_blocks') && has_blocks($content);
        if ($has_paragraphs) {
            $content = preg_replace_callback('/<p\b([^>]*)>(.*?)<\/p>/is', static function ($matches) {
                $inner = (string) ($matches[2] ?? '');
                if (stripos($inner, '<br') === false && strpos($inner, "\n") === false) {
                    return (string) ($matches[0] ?? '');
                }

                $parts = preg_split('/(?:<br\s*\/?>\s*)+|\n+/i', $inner);
                $parts = array_values(array_filter(array_map('trim', (array) $parts)));
                if (count($parts) < 2) {
                    return (string) ($matches[0] ?? '');
                }

                return '<p>' . implode('</p><p>', array_map('wp_kses_post', $parts)) . '</p>';
            }, $content);

            return wp_kses_post($content);
        }

        if ($has_block_editor_markup) {
            return wp_kses_post($content);
        }

        $has_complex_html = preg_match('/<(ul|ol|table|pre|figure)\b/i', $content) === 1;
        if (!$has_complex_html && substr_count($content, "\n") > 0 && strpos($content, "\n\n") === false) {
            $content = preg_replace('/[ \t]*\n[ \t]*/', "\n\n", $content);
        }

        return wp_kses_post(wpautop($content));
    }
}

if (!function_exists('dls_writing_desk_compare_text')) {
    function dls_writing_desk_compare_text($text) {
        $text = html_entity_decode(wp_strip_all_tags((string) $text), ENT_QUOTES, get_bloginfo('charset'));
        $text = preg_replace('/\s+/u', ' ', trim($text));

        return function_exists('mb_strtolower') ? mb_strtolower((string) $text) : strtolower((string) $text);
    }
}

if (!function_exists('dls_writing_desk_remove_duplicate_lead')) {
    function dls_writing_desk_remove_duplicate_lead($content, $post_id) {
        $lead = trim((string) get_post_field('post_excerpt', $post_id));
        if ($lead === '' || $content === '') {
            return $content;
        }

        $lead_text = dls_writing_desk_compare_text($lead);
        $content = (string) $content;
        $prefix_pattern = '(?:\s*<!--\s*\/?wp:[\s\S]*?-->\s*)*';
        $block_pattern = '/^(' . $prefix_pattern . ')<(p|h[1-6]|blockquote|div)\b[^>]*>(.*?)<\/\2>\s*(' . $prefix_pattern . ')/is';

        if (preg_match($block_pattern, $content, $matches) === 1) {
            if (dls_writing_desk_compare_text($matches[3] ?? '') === $lead_text) {
                return preg_replace($block_pattern, '', $content, 1);
            }

            return $content;
        }

        $plain_content = dls_writing_desk_compare_text($content);
        if ($plain_content !== '' && strpos($plain_content, $lead_text) === 0) {
            $lead_length = strlen((string) $lead);
            return ltrim(substr($content, $lead_length));
        }

        return $content;
    }
}

if (!function_exists('dls_writing_desk_lower_text')) {
    function dls_writing_desk_lower_text($text) {
        $text = trim(wp_strip_all_tags((string) $text));

        return function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    }
}

if (!function_exists('dls_writing_desk_category_term_groups')) {
    function dls_writing_desk_category_term_groups($term_id) {
        $term_id = absint($term_id);
        if ($term_id < 1) {
            return [];
        }

        $names = [];
        $term = get_term($term_id, 'category');
        if ($term instanceof WP_Term) {
            $names[] = $term->name;
            $names[] = $term->slug;
        }

        foreach (get_ancestors($term_id, 'category', 'taxonomy') as $ancestor_id) {
            $ancestor = get_term((int) $ancestor_id, 'category');
            if ($ancestor instanceof WP_Term) {
                $names[] = $ancestor->name;
                $names[] = $ancestor->slug;
            }
        }

        $groups = [];
        $jurisdiction_needles = ['jurisdiction', 'jurisdictions', 'юрисдикц'];
        $topic_needles = ['topic', 'topics', 'tema', 'тема', 'теми', 'темы'];

        foreach ($names as $name) {
            $name = dls_writing_desk_lower_text($name);
            foreach ($jurisdiction_needles as $needle) {
                if ($needle !== '' && strpos($name, $needle) !== false) {
                    $groups[] = 'jurisdiction';
                    break;
                }
            }
            foreach ($topic_needles as $needle) {
                if ($needle !== '' && strpos($name, $needle) !== false) {
                    $groups[] = 'topic';
                    break;
                }
            }
        }

        return array_values(array_unique($groups));
    }
}

if (!function_exists('dls_writing_desk_has_category_group')) {
    function dls_writing_desk_has_category_group($category_ids, $group) {
        $group = sanitize_key((string) $group);
        foreach ((array) $category_ids as $term_id) {
            if (in_array($group, dls_writing_desk_category_term_groups((int) $term_id), true)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('dls_writing_desk_link_state')) {
    function dls_writing_desk_link_state($content) {
        $content = (string) $content;
        $home_host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
        $urls = [];

        if (preg_match_all('/<a\s[^>]*href=["\']([^"\']+)["\']/i', $content, $matches) === 1) {
            $urls = array_merge($urls, (array) ($matches[1] ?? []));
        }

        if (function_exists('wp_extract_urls')) {
            $urls = array_merge($urls, wp_extract_urls(wp_strip_all_tags($content)));
        }

        $state = [
            'internal' => false,
            'external' => false,
        ];

        foreach (array_unique(array_filter(array_map('trim', $urls))) as $url) {
            if (strpos($url, '#') === 0 || stripos($url, 'mailto:') === 0 || stripos($url, 'tel:') === 0) {
                continue;
            }

            $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
            if ($host === '' && strpos($url, '/') === 0) {
                $state['internal'] = true;
                continue;
            }

            if ($host !== '' && $home_host !== '' && ($host === $home_host || substr($host, -1 * (strlen($home_host) + 1)) === '.' . $home_host)) {
                $state['internal'] = true;
                continue;
            }

            if ($host !== '') {
                $state['external'] = true;
            }
        }

        return $state;
    }
}

if (!function_exists('dls_writing_desk_taxonomy_has_selection')) {
    function dls_writing_desk_taxonomy_has_selection($selected_taxonomies, $taxonomy_keys) {
        foreach ((array) $taxonomy_keys as $taxonomy) {
            $taxonomy = sanitize_key((string) $taxonomy);
            if ($taxonomy !== '' && !empty($selected_taxonomies[$taxonomy])) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('dls_writing_desk_checklist_items')) {
    function dls_writing_desk_checklist_items() {
        return [
            'featured_image' => [
                'label' => 'Featured image',
                'note' => 'Choose the main image for the post.',
                'required' => true,
            ],
            'external_link' => [
                'label' => 'External link',
                'note' => 'Add at least one link to an outside source.',
                'required' => true,
            ],
            'internal_link' => [
                'label' => 'Internal link',
                'note' => 'Add at least one link to another Dead Lawyers page.',
                'required' => true,
            ],
            'category_jurisdiction' => [
                'label' => 'Jurisdiction category',
                'note' => 'Choose a category under Jurisdiction.',
                'required' => true,
            ],
            'category_topic' => [
                'label' => 'Тема category',
                'note' => 'Choose a category under Тема / Topic.',
                'required' => true,
            ],
            'author' => [
                'label' => 'Author selected',
                'note' => 'Choose the byline author before publishing.',
                'required' => true,
            ],
            'company' => [
                'label' => 'Company tagged',
                'note' => 'Optional, but useful when the story mentions a company.',
                'required' => false,
            ],
            'individual' => [
                'label' => 'Individual tagged',
                'note' => 'Optional, but useful when the story mentions a person.',
                'required' => false,
            ],
            'tags' => [
                'label' => 'Tags added',
                'note' => 'Optional search tags for discovery.',
                'required' => false,
            ],
        ];
    }
}

if (!function_exists('dls_writing_desk_checklist_state')) {
    function dls_writing_desk_checklist_state($content, $thumbnail_id, $category_ids, $author_value, $selected_taxonomies) {
        $links = dls_writing_desk_link_state($content);

        return [
            'featured_image' => absint($thumbnail_id) > 0,
            'external_link' => !empty($links['external']),
            'internal_link' => !empty($links['internal']),
            'category_jurisdiction' => dls_writing_desk_has_category_group($category_ids, 'jurisdiction'),
            'category_topic' => dls_writing_desk_has_category_group($category_ids, 'topic'),
            'author' => trim((string) $author_value) !== '',
            'company' => dls_writing_desk_taxonomy_has_selection($selected_taxonomies, ['companies', 'company']),
            'individual' => dls_writing_desk_taxonomy_has_selection($selected_taxonomies, ['individuals', 'individual', 'people', 'person']),
            'tags' => dls_writing_desk_taxonomy_has_selection($selected_taxonomies, ['post_tag']),
        ];
    }
}

if (!function_exists('dls_writing_desk_missing_required_checklist')) {
    function dls_writing_desk_missing_required_checklist($state, $checked) {
        $missing = [];
        $checked = array_map('sanitize_key', (array) $checked);

        foreach (dls_writing_desk_checklist_items() as $key => $item) {
            if (empty($item['required'])) {
                continue;
            }

            if (empty($state[$key]) || !in_array($key, $checked, true)) {
                $missing[] = $key;
            }
        }

        return $missing;
    }
}

if (!function_exists('dls_writing_desk_filter_frontend_content')) {
    function dls_writing_desk_filter_frontend_content($content) {
        if (is_admin() || !is_singular('post') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $post_id = get_the_ID();
        if ($post_id < 1) {
            return $content;
        }

        $intro = dls_writing_desk_render_frontend_intro($post_id);

        $content = dls_writing_desk_normalize_saved_content($content);
        $content = dls_writing_desk_remove_duplicate_lead($content, $post_id);

        if ($intro !== '' && strpos((string) $content, 'dls-writing-desk-frontend-intro') === false) {
            $content = $intro . $content;
        }

        return $content;
    }
}
add_filter('the_content', 'dls_writing_desk_filter_frontend_content', 8);

if (!function_exists('dls_writing_desk_frontend_styles')) {
    function dls_writing_desk_frontend_styles() {
        if (!is_singular('post')) {
            return;
        }

        echo '<style>.dls-writing-desk-frontend-intro{margin:0 0 1.8em}.dls-writing-desk-frontend-kicker{margin:0 0 .7em;color:#8a6133;font:700 12px/1.2 "Helvetica Neue",Arial,sans-serif;letter-spacing:.18em;text-transform:uppercase}.dls-writing-desk-frontend-lead{margin:0 0 1.4em;font-size:1.25em;line-height:1.7;color:#443225}.single-post .entry-content>p,.single-post .single-content>p,.single-post .wp-block-post-content>p{margin-top:0;margin-bottom:1.45em}.single-post .entry-content>p:last-child,.single-post .single-content>p:last-child,.single-post .wp-block-post-content>p:last-child{margin-bottom:0}</style>';
    }
}
add_action('wp_head', 'dls_writing_desk_frontend_styles');

if (!function_exists('dls_writing_desk_admin_menu')) {
    function dls_writing_desk_admin_menu() {
        $hook = add_menu_page(
            'Writing Desk',
            'Writing Desk',
            'edit_posts',
            'dls-writing-desk',
            'dls_writing_desk_render_page',
            'dashicons-edit-large',
            3
        );

        dls_writing_desk_set_page_hook($hook);

        add_submenu_page(
            'dls-writing-desk',
            'Telegram Broadcast',
            'Telegram Broadcast',
            'edit_posts',
            'dls-writing-desk-telegram',
            'dls_writing_desk_render_telegram_page'
        );

        add_submenu_page(
            'dls-writing-desk',
            'Social Destinations',
            'Social Destinations',
            'manage_options',
            'dls-writing-desk-destinations',
            'dls_writing_desk_render_destinations_page'
        );

        add_submenu_page(
            'dls-writing-desk',
            'Access Rights',
            'Access Rights',
            'manage_options',
            'dls-writing-desk-access',
            'dls_writing_desk_render_access_page'
        );
    }
}
add_action('admin_menu', 'dls_writing_desk_admin_menu');

if (!function_exists('dls_writing_desk_enqueue_assets')) {
    function dls_writing_desk_enqueue_assets($hook) {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if (!in_array($page, ['dls-writing-desk', 'dls-writing-desk-telegram', 'dls-writing-desk-destinations', 'dls-writing-desk-access'], true)) {
            return;
        }

        wp_enqueue_media();

        wp_register_style('dls-writing-desk', false, [], '1.2.0');
        wp_enqueue_style('dls-writing-desk');
        wp_add_inline_style('dls-writing-desk', '
            body.toplevel_page_dls-writing-desk,
            body.writing-desk_page_dls-writing-desk-telegram,
            body.writing-desk_page_dls-writing-desk-destinations,
            body.writing-desk_page_dls-writing-desk-access {
                background:
                    radial-gradient(circle at top left, #f7ebd1 0, #f7ebd1 14%, transparent 40%),
                    linear-gradient(180deg, #f5ecd9 0%, #efe3cd 100%);
                color: #241c15;
            }
            body.toplevel_page_dls-writing-desk #wpadminbar,
            body.toplevel_page_dls-writing-desk #adminmenumain,
            body.toplevel_page_dls-writing-desk #wpfooter,
            body.toplevel_page_dls-writing-desk #screen-meta-links,
            body.writing-desk_page_dls-writing-desk-telegram #wpadminbar,
            body.writing-desk_page_dls-writing-desk-telegram #adminmenumain,
            body.writing-desk_page_dls-writing-desk-telegram #wpfooter,
            body.writing-desk_page_dls-writing-desk-telegram #screen-meta-links,
            body.toplevel_page_dls-writing-desk .notice:not(.dls-writing-desk__notice),
            body.toplevel_page_dls-writing-desk .update-nag,
            body.toplevel_page_dls-writing-desk .error:not(.dls-writing-desk__notice),
            body.toplevel_page_dls-writing-desk .updated:not(.dls-writing-desk__notice),
            body.writing-desk_page_dls-writing-desk-telegram .notice:not(.dls-writing-desk__notice),
            body.writing-desk_page_dls-writing-desk-telegram .update-nag,
            body.writing-desk_page_dls-writing-desk-telegram .error:not(.dls-writing-desk__notice),
            body.writing-desk_page_dls-writing-desk-telegram .updated:not(.dls-writing-desk__notice) {
                display: none !important;
            }
            body.toplevel_page_dls-writing-desk #wpcontent,
            body.writing-desk_page_dls-writing-desk-telegram #wpcontent,
            body.toplevel_page_dls-writing-desk #wpfooter {
                margin-left: 0;
            }
            body.toplevel_page_dls-writing-desk #wpbody-content {
                padding-bottom: 0;
            }
            body.writing-desk_page_dls-writing-desk-telegram #wpbody-content {
                padding-bottom: 0;
            }
            .dls-writing-desk {
                min-height: 100vh;
                padding: 26px 24px 48px;
                box-sizing: border-box;
                font-family: "Iowan Old Style", "Palatino Linotype", "Book Antiqua", Georgia, serif;
            }
            .dls-writing-desk__shell {
                display: grid;
                grid-template-columns: 340px minmax(0, 1fr);
                gap: 22px;
                align-items: start;
            }
            .dls-writing-desk__shell--telegram {
                grid-template-columns: 360px minmax(0, 1fr);
            }
            .dls-writing-desk__panel,
            .dls-writing-desk__editor,
            .dls-writing-desk__admin-card {
                background: rgba(255,255,255,0.82);
                border: 1px solid rgba(36,28,21,0.12);
                border-radius: 22px;
                box-shadow: 0 18px 40px rgba(66, 44, 14, 0.08);
                backdrop-filter: blur(8px);
            }
            .dls-writing-desk__panel,
            .dls-writing-desk__admin-card {
                padding: 18px;
            }
            .dls-writing-desk__panel {
                position: sticky;
                top: 24px;
            }
            .dls-writing-desk__editor {
                padding: 24px;
            }
            .dls-writing-desk__topbar,
            .dls-writing-desk__admin-head {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: center;
                margin-bottom: 18px;
            }
            .dls-writing-desk__brand {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .dls-writing-desk__eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font: 600 11px/1.2 "Helvetica Neue", Arial, sans-serif;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: #8a6133;
            }
            .dls-writing-desk__title,
            .dls-writing-desk__admin-title {
                margin: 0;
                font-size: 34px;
                line-height: 1;
                font-weight: 700;
                color: #1f1711;
            }
            .dls-writing-desk__sub,
            .dls-writing-desk__admin-sub {
                margin: 0;
                color: #725743;
                font: 500 14px/1.5 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__actions,
            .dls-writing-desk__footer-actions,
            .dls-writing-desk__panel-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }
            .dls-writing-desk__button,
            .dls-writing-desk__button:visited {
                border: 0;
                border-radius: 999px;
                padding: 12px 18px;
                background: #2d2117;
                color: #fff;
                text-decoration: none;
                cursor: pointer;
                font: 600 13px/1 "Helvetica Neue", Arial, sans-serif;
                letter-spacing: 0.02em;
            }
            .dls-writing-desk__button--soft,
            .dls-writing-desk__button--soft:visited {
                background: #e8d9be;
                color: #2f251d;
            }
            .dls-writing-desk__button--accent,
            .dls-writing-desk__button--accent:visited {
                background: linear-gradient(135deg, #af3d22, #7d2010);
            }
            .dls-writing-desk__button--copy,
            .dls-writing-desk__button--copy:visited {
                background: #d9c7a9;
                color: #2f251d;
            }
            .dls-writing-desk__button--disabled,
            .dls-writing-desk__button--disabled:visited {
                background: #efe6d7;
                color: #8a7a67;
                cursor: default;
                pointer-events: none;
            }
            .dls-writing-desk__button:disabled {
                opacity: 0.48;
                cursor: not-allowed;
            }
            .dls-writing-desk__notice {
                margin: 0 0 16px;
                border: 0;
                border-radius: 16px;
                background: #ecf7ee;
                color: #17351f;
                box-shadow: none;
            }
            .dls-writing-desk__editor-grid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 340px;
                gap: 22px;
            }
            .dls-writing-desk__main {
                min-width: 0;
            }
            .dls-writing-desk__story-sheet {
                background: #fffdfa;
                border: 1px solid rgba(36,28,21,0.08);
                border-radius: 26px;
                box-shadow: inset 0 1px 0 rgba(255,255,255,0.5);
                padding: 34px 38px 22px;
            }
            .dls-writing-desk__story-head {
                margin-bottom: 18px;
            }
            .dls-writing-desk__story-head > * + * {
                margin-top: 10px;
            }
            .dls-writing-desk__story-editor {
                margin-top: 10px;
            }
            .dls-writing-desk__side {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .dls-writing-desk__block {
                border: 1px solid rgba(36,28,21,0.1);
                background: rgba(255,255,255,0.72);
                border-radius: 18px;
                padding: 16px;
            }
            .dls-writing-desk__block h2,
            .dls-writing-desk__block h3 {
                margin: 0 0 12px;
                font-size: 15px;
                line-height: 1.3;
                font-family: "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__field {
                margin-bottom: 18px;
            }
            .dls-writing-desk__field:last-child {
                margin-bottom: 0;
            }
            .dls-writing-desk__field--story {
                margin-bottom: 0;
            }
            .dls-writing-desk__label {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 8px;
                color: #594333;
                font: 600 12px/1.2 "Helvetica Neue", Arial, sans-serif;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }
            .dls-writing-desk__input,
            .dls-writing-desk__textarea,
            .dls-writing-desk__select {
                width: 100%;
                border: 1px solid rgba(49,35,25,0.16);
                border-radius: 14px;
                padding: 14px 16px;
                box-sizing: border-box;
                background: #fffdf8;
                color: #201814;
                font: 400 16px/1.5 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__input--kicker {
                border: 0;
                border-radius: 0;
                padding: 0;
                font: 600 12px/1.3 "Helvetica Neue", Arial, sans-serif;
                text-transform: uppercase;
                letter-spacing: 0.18em;
                color: #8a6133;
                background: transparent;
                box-shadow: none;
            }
            .dls-writing-desk__input--title {
                border: 0;
                border-radius: 0;
                padding: 0;
                background: transparent;
                box-shadow: none;
                font: 700 52px/1.04 "Iowan Old Style", "Palatino Linotype", Georgia, serif;
                letter-spacing: -0.03em;
            }
            .dls-writing-desk__textarea {
                min-height: 120px;
                resize: vertical;
            }
            .dls-writing-desk__textarea--lead {
                min-height: 92px;
                border: 0;
                border-radius: 0;
                padding: 0;
                background: transparent;
                box-shadow: none;
                font: 400 28px/1.42 "Iowan Old Style", "Palatino Linotype", Georgia, serif;
                color: #3f3024;
            }
            .dls-writing-desk__input--small,
            .dls-writing-desk__input--search,
            .dls-writing-desk__input--datetime {
                padding: 12px 14px;
                font-size: 14px;
            }
            .dls-writing-desk__muted {
                color: #7b6552;
                font: 500 12px/1.45 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__posts {
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-height: calc(100vh - 300px);
                overflow: auto;
                padding-right: 4px;
            }
            .dls-writing-desk__post-link {
                display: block;
                padding: 12px 14px;
                border-radius: 16px;
                text-decoration: none;
                background: rgba(255,255,255,0.82);
                color: #231a14;
                border: 1px solid rgba(36,28,21,0.08);
            }
            .dls-writing-desk__post-link.is-active {
                background: linear-gradient(135deg, #2b1f17, #4a3324);
                color: #fff8f0;
            }
            .dls-writing-desk__post-title {
                display: block;
                font-weight: 700;
                margin-bottom: 4px;
                line-height: 1.35;
            }
            .dls-writing-desk__post-meta {
                display: block;
                opacity: 0.72;
                font: 500 12px/1.4 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__checklist {
                display: grid;
                gap: 10px;
                max-height: 260px;
                overflow: auto;
                padding-right: 4px;
            }
            .dls-writing-desk__check {
                display: flex;
                gap: 12px;
                align-items: flex-start;
                padding: 10px 12px;
                border-radius: 14px;
                background: rgba(255,255,255,0.86);
                border: 1px solid rgba(36,28,21,0.08);
                font: 500 14px/1.4 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__check.is-hidden,
            .dls-writing-desk__post-link.is-hidden {
                display: none !important;
            }
            .dls-writing-desk__check--child {
                margin-left: 18px;
            }
            .dls-writing-desk__check-note {
                display: block;
                margin-top: 2px;
                color: #826956;
                font-size: 12px;
            }
            .dls-writing-desk__publish-checklist {
                background:
                    radial-gradient(circle at 15% 15%, rgba(175,61,34,0.11), transparent 28%),
                    rgba(255,253,248,0.88);
                border-color: rgba(175,61,34,0.18);
            }
            .dls-writing-desk__checklist-head {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
                margin-bottom: 12px;
            }
            .dls-writing-desk__checklist-score {
                border-radius: 999px;
                background: #2d2117;
                color: #fff8f0;
                padding: 7px 10px;
                white-space: nowrap;
                font: 700 12px/1 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__checklist-bar {
                height: 8px;
                border-radius: 999px;
                overflow: hidden;
                background: #eadcc7;
                margin-bottom: 14px;
            }
            .dls-writing-desk__checklist-fill {
                display: block;
                height: 100%;
                width: 0;
                border-radius: inherit;
                background: linear-gradient(90deg, #7b8b3f, #2f6f49);
                transition: width 180ms ease;
            }
            .dls-writing-desk__task-list {
                display: grid;
                gap: 9px;
            }
            .dls-writing-desk__task {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr) auto;
                gap: 10px;
                align-items: start;
                padding: 11px 12px;
                border-radius: 15px;
                border: 1px solid rgba(36,28,21,0.08);
                background: rgba(255,255,255,0.82);
                font-family: "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__task input {
                margin-top: 3px;
            }
            .dls-writing-desk__task strong {
                display: block;
                color: #241c15;
                font-size: 13px;
                line-height: 1.3;
            }
            .dls-writing-desk__task em {
                display: block;
                margin-top: 2px;
                color: #806a55;
                font-size: 12px;
                line-height: 1.35;
                font-style: normal;
            }
            .dls-writing-desk__task-status {
                border-radius: 999px;
                padding: 5px 8px;
                background: #efe3d1;
                color: #765338;
                white-space: nowrap;
                font: 700 11px/1 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__task.is-ready .dls-writing-desk__task-status {
                background: #e5f1df;
                color: #315b2b;
            }
            .dls-writing-desk__task.is-done {
                border-color: rgba(49,91,43,0.24);
            }
            .dls-writing-desk__task.is-missing {
                border-color: rgba(175,61,34,0.28);
                background: rgba(255,247,240,0.9);
            }
            .dls-writing-desk__task--optional {
                opacity: 0.82;
            }
            .dls-writing-desk__publish-warning {
                display: none;
                margin: 12px 0 0;
                border-radius: 13px;
                padding: 10px 12px;
                background: #fff1df;
                color: #6a321d;
                font: 700 12px/1.4 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__publish-warning.is-visible {
                display: block;
            }
            .dls-writing-desk__thumb {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .dls-writing-desk__thumb-preview,
            .dls-writing-desk__social-preview {
                min-height: 148px;
                border-radius: 16px;
                background: linear-gradient(135deg, #e9dec9, #f8f2e7);
                border: 1px dashed rgba(36,28,21,0.14);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                color: #81654d;
                text-align: center;
                padding: 12px;
            }
            .dls-writing-desk__thumb-preview img,
            .dls-writing-desk__social-preview img {
                width: 100%;
                height: auto;
                display: block;
            }
            .dls-writing-desk__admin-table {
                width: 100%;
                border-collapse: collapse;
            }
            .dls-writing-desk__admin-table th,
            .dls-writing-desk__admin-table td {
                padding: 10px;
                border-bottom: 1px solid rgba(36,28,21,0.08);
                vertical-align: top;
            }
            .dls-writing-desk__social-card {
                border-top: 1px solid rgba(36,28,21,0.08);
                margin-top: 14px;
                padding-top: 14px;
            }
            .dls-writing-desk__social-card--telegram {
                border: 1px solid rgba(23,86,130,0.22);
                border-radius: 14px;
                padding: 14px;
                background: rgba(240,248,255,0.72);
            }
            .dls-writing-desk__telegram-options {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
                margin-top: 12px;
                font: 600 12px/1.3 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__telegram-options label {
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .dls-writing-desk__input--mini {
                width: 64px;
                padding: 6px 8px;
                font-size: 12px;
            }
            .dls-writing-desk__delivery-status {
                margin: 0 0 10px;
                border-radius: 10px;
                padding: 8px 10px;
                background: #eef3ed;
                color: #254022;
                font: 600 12px/1.4 "Helvetica Neue", Arial, sans-serif;
            }
            .dls-writing-desk__delivery-status--failed {
                background: #fae8e2;
                color: #6f2418;
            }
            .dls-writing-desk__delivery-status span {
                opacity: 0.7;
                margin-left: 6px;
            }
            .dls-writing-desk__delivery-status--scheduled {
                background: #f3ecd8;
                color: #694f1e;
            }
            .dls-writing-desk__textarea--telegram-message {
                min-height: 260px;
                font-size: 18px;
                line-height: 1.6;
            }
            .dls-writing-desk__telegram-button-list {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin-top: 10px;
            }
            .dls-writing-desk__telegram-button-row {
                display: grid;
                grid-template-columns: minmax(120px, 0.9fr) minmax(160px, 1.2fr) auto;
                gap: 8px;
                align-items: center;
            }
            .dls-writing-desk__telegram-preview {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 320px;
                gap: 18px;
                align-items: start;
            }
            .dls-writing-desk__post-preview {
                max-height: 520px;
                overflow: auto;
                padding: 20px;
                border-radius: 18px;
                background: #fffdfa;
                border: 1px solid rgba(36,28,21,0.1);
                font: 400 17px/1.7 Georgia, serif;
            }
            .dls-writing-desk__post-preview h1 {
                margin: 0 0 12px;
                font-size: 30px;
                line-height: 1.12;
            }
            .dls-writing-desk__post-preview-lead {
                margin: 0 0 18px;
                color: #604939;
                font-size: 20px;
                line-height: 1.5;
            }
            .dls-writing-desk__statline {
                display: flex;
                gap: 16px;
                flex-wrap: wrap;
                margin: 0 0 16px;
                color: #6b5441;
                font: 600 12px/1.4 "Helvetica Neue", Arial, sans-serif;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .dls-writing-desk__footer-actions {
                margin-top: 22px;
                padding-top: 18px;
                border-top: 1px solid rgba(36,28,21,0.08);
            }
            .dls-writing-desk__toolbar-note,
            .dls-writing-desk__taxonomy-empty {
                margin-top: 8px;
            }
            .dls-writing-desk__share-row {
                display: flex;
                gap: 8px;
                align-items: center;
            }
            .dls-writing-desk__share-row .dls-writing-desk__input {
                flex: 1 1 auto;
            }
            #wp-dls_writing_desk_content-wrap {
                margin-top: 8px;
            }
            #wp-dls_writing_desk_content-wrap .wp-editor-container {
                border: 0;
                border-radius: 0;
                overflow: visible;
                background: transparent;
            }
            #wp-dls_writing_desk_content-wrap .quicktags-toolbar,
            #wp-dls_writing_desk_content-wrap .mce-toolbar-grp {
                background: transparent;
                border: 0;
                box-shadow: none;
                padding-left: 0;
                padding-right: 0;
            }
            #wp-dls_writing_desk_content-wrap .wp-editor-tabs {
                display: none;
            }
            #wp-dls_writing_desk_content-wrap .mce-tinymce {
                border: 0;
                box-shadow: none;
            }
            #dls_writing_desk_content_ifr {
                min-height: 620px !important;
                background: #fffdfa;
            }
	            @media (max-width: 1180px) {
	                .dls-writing-desk__shell,
	                .dls-writing-desk__editor-grid,
	                .dls-writing-desk__telegram-preview {
	                    grid-template-columns: 1fr;
	                }
                .dls-writing-desk__panel {
                    position: static;
                }
            }
            @media (max-width: 782px) {
                .dls-writing-desk {
                    padding: 16px 12px 32px;
                }
                .dls-writing-desk__title,
                .dls-writing-desk__admin-title {
                    font-size: 28px;
                }
                .dls-writing-desk__story-sheet {
                    padding: 24px 20px 18px;
                }
                .dls-writing-desk__input--title {
                    font-size: 34px;
                }
                .dls-writing-desk__textarea--lead {
                    font-size: 22px;
                }
	                .dls-writing-desk__share-row,
	                .dls-writing-desk__telegram-button-row,
	                .dls-writing-desk__admin-head {
	                    display: flex;
	                    flex-direction: column;
	                    align-items: stretch;
	                }
            }
        ');

        wp_register_script('dls-writing-desk', false, ['jquery'], '1.2.0', true);
        wp_enqueue_script('dls-writing-desk');
        wp_add_inline_script('dls-writing-desk', 'window.DLSWritingDesk = ' . wp_json_encode([
            'homeHost' => strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST)),
            'canOverrideChecklist' => current_user_can('manage_options'),
        ]) . ';', 'before');
        wp_add_inline_script('dls-writing-desk', '
            (function ($) {
                function stripHtml(value) {
                    return String(value || "").replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
                }

                function updateCounts() {
                    var title = $("#dls-writing-desk-title").val() || "";
                    $(".dls-writing-desk__title-count").text(title.length + " chars");

                    var lead = $("#dls-writing-desk-lead").val() || "";
                    $(".dls-writing-desk__lead-count").text(lead.length + " chars");

                    var editorText = "";
                    if (window.tinyMCE && tinyMCE.get("dls_writing_desk_content")) {
                        editorText = tinyMCE.get("dls_writing_desk_content").getContent({ format: "text" });
                    } else {
                        editorText = $("#dls_writing_desk_content").val() || "";
                    }

                    editorText = stripHtml(editorText);
                    var words = editorText === "" ? 0 : editorText.split(/\s+/).length;
                    $(".dls-writing-desk__word-count").text(words + " words");
                }

                function setPreview(previewSelector, url, placeholder) {
                    var preview = $(previewSelector);
                    if (!preview.length) {
                        return;
                    }
                    if (!url) {
                        preview.html("<span>" + placeholder + "</span>");
                        return;
                    }

                    preview.html("<img src=\"" + url + "\" alt=\"\">");
                }

                function setThumb(url) {
                    setPreview(".dls-writing-desk__thumb-preview", url, "No featured image yet");
                }

                function openMedia(targetInput, targetPreview, placeholder) {
                    if (typeof wp === "undefined" || !wp.media) {
                        return;
                    }

                    var frame = wp.media({
                        title: "Choose image",
                        library: { type: "image" },
                        button: { text: "Use image" },
                        multiple: false
                    });

                    frame.on("select", function () {
                        var item = frame.state().get("selection").first();
                        if (!item) {
                            return;
                        }

                        var data = item.toJSON();
                        $(targetInput).val(data.id ? String(data.id) : "");
                        setPreview(targetPreview, data.url || "", placeholder);
                        updateChecklist();
                    });

                    frame.open();
                }

                function addDestinationRow() {
                    var table = $("#dls-writing-desk-destination-table tbody");
                    var template = $("#tmpl-dls-writing-desk-destination-row").html() || "";
                    if (!table.length || !template) {
                        return;
                    }
                    var index = table.find("tr").length;
                    table.append(template.replace(/__index__/g, String(index)));
                }

                function addTelegramButtonRow(button) {
                    var target = $($(button).data("target") || "");
                    var template = $($(button).data("template") || "").html() || "";
                    if (!target.length || !template) {
                        return;
                    }

                    var index = parseInt(target.attr("data-next-index") || target.find(".dls-writing-desk__telegram-button-row").length, 10);
                    if (isNaN(index) || index < 0) {
                        index = target.find(".dls-writing-desk__telegram-button-row").length;
                    }
                    target.attr("data-next-index", String(index + 1));
                    target.append(template.replace(/__index__/g, String(index)));
                }

                function filterSelect(selector, language) {
                    var select = $(selector);
                    if (!select.length) {
                        return;
                    }

                    var current = select.val() || "";
                    var keepCurrent = false;

                    select.find("option").each(function () {
                        var option = $(this);
                        var optionValue = option.attr("value") || "";
                        var optionLanguage = (option.data("lang") || "").toString().toLowerCase();
                        var shouldHide = optionValue !== "" && optionLanguage !== "" && language !== "" && optionLanguage !== language;

                        this.hidden = shouldHide;
                        this.disabled = shouldHide;

                        if (!shouldHide && optionValue === current) {
                            keepCurrent = true;
                        }
                    });

                    if (current && !keepCurrent) {
                        select.val("");
                    }
                }

                function filterChecklist(container) {
                    var list = $(container);
                    if (!list.length) {
                        return;
                    }

                    var language = ($("#dls-writing-desk-language").val() || "").toString().toLowerCase();
                    var query = (list.prevAll(".dls-writing-desk__input--search:first").val() || "").toString().toLowerCase();

                    list.find(".dls-writing-desk__check").each(function () {
                        var item = $(this);
                        var itemLanguage = (item.data("lang") || "").toString().toLowerCase();
                        var haystack = (item.data("search") || item.text() || "").toString().toLowerCase();
                        var languageHidden = itemLanguage !== "" && language !== "" && itemLanguage !== language;
                        var queryHidden = query !== "" && haystack.indexOf(query) === -1;

                        item.toggleClass("is-hidden", languageHidden || queryHidden);
                    });
                }

                function applyLanguageFilter() {
                    var language = ($("#dls-writing-desk-language").val() || "").toString().toLowerCase();
                    filterSelect("#dls-writing-desk-author", language);
                    filterSelect("#dls-writing-desk-editor", language);
                    $(".dls-writing-desk__checklist").each(function () {
                        filterChecklist(this);
                    });
                }

                function filterRecentPosts() {
                    var query = ($("#dls-writing-desk-post-search").val() || "").toString().toLowerCase();
                    $(".dls-writing-desk__post-link").each(function () {
                        var item = $(this);
                        var haystack = (item.data("search") || item.text() || "").toString().toLowerCase();
                        item.toggleClass("is-hidden", query !== "" && haystack.indexOf(query) === -1);
                    });
                }

                function getEditorHtml() {
                    if (window.tinyMCE && tinyMCE.get("dls_writing_desk_content")) {
                        return tinyMCE.get("dls_writing_desk_content").getContent() || "";
                    }

                    return $("#dls_writing_desk_content").val() || "";
                }

                function getLinkState() {
                    var html = getEditorHtml();
                    var holder = $("<div>").html(html);
                    var homeHost = ((window.DLSWritingDesk || {}).homeHost || "").toString().toLowerCase();
                    var state = { internal: false, external: false };

                    holder.find("a[href]").each(function () {
                        var href = ($(this).attr("href") || "").toString().trim();
                        if (!href || href.indexOf("#") === 0 || /^mailto:/i.test(href) || /^tel:/i.test(href)) {
                            return;
                        }

                        if (href.indexOf("/") === 0) {
                            state.internal = true;
                            return;
                        }

                        try {
                            var link = new URL(href, window.location.origin);
                            var host = (link.hostname || "").toLowerCase();
                            if (host && homeHost && (host === homeHost || host.slice(-1 * (homeHost.length + 1)) === "." + homeHost)) {
                                state.internal = true;
                            } else if (host) {
                                state.external = true;
                            }
                        } catch (e) {}
                    });

                    (holder.text().match(/https?:\/\/[^\s<>"\']+/gi) || []).forEach(function (href) {
                        try {
                            var link = new URL(href);
                            var host = (link.hostname || "").toLowerCase();
                            if (host && homeHost && (host === homeHost || host.slice(-1 * (homeHost.length + 1)) === "." + homeHost)) {
                                state.internal = true;
                            } else if (host) {
                                state.external = true;
                            }
                        } catch (e) {}
                    });

                    return state;
                }

                function checkedCategoryHasGroup(group) {
                    var found = false;
                    $("input[name=\'dls_writing_desk_categories[]\']:checked").each(function () {
                        var groups = ($(this).data("groups") || "").toString().split(/\s+/);
                        if (groups.indexOf(group) !== -1) {
                            found = true;
                            return false;
                        }
                    });

                    return found;
                }

                function taxonomyHasSelection(group) {
                    var found = false;
                    $(".dls-writing-desk__checklist[data-checklist-group=\'" + group + "\'] input:checked").each(function () {
                        found = true;
                        return false;
                    });

                    if (found) {
                        return true;
                    }

                    $(".dls-writing-desk__new-taxonomy[data-checklist-group=\'" + group + "\']").each(function () {
                        if (($(this).val() || "").toString().trim() !== "") {
                            found = true;
                            return false;
                        }
                    });

                    return found;
                }

                function checklistReadiness(task) {
                    var links = getLinkState();
                    var readers = {
                        featured_image: function () { return ($("#dls-writing-desk-thumbnail-id").val() || "") !== ""; },
                        external_link: function () { return links.external; },
                        internal_link: function () { return links.internal; },
                        category_jurisdiction: function () { return checkedCategoryHasGroup("jurisdiction"); },
                        category_topic: function () { return checkedCategoryHasGroup("topic"); },
                        author: function () { return ($("#dls-writing-desk-author").val() || "") !== ""; },
                        company: function () { return taxonomyHasSelection("company"); },
                        individual: function () { return taxonomyHasSelection("individual"); },
                        tags: function () { return taxonomyHasSelection("tags"); }
                    };

                    return readers[task] ? readers[task]() : false;
                }

                function updateChecklist() {
                    var requiredTotal = 0;
                    var requiredDone = 0;
                    var missingRequired = [];
                    var canOverride = !!((window.DLSWritingDesk || {}).canOverrideChecklist);

                    $(".dls-writing-desk__task").each(function () {
                        var item = $(this);
                        var task = (item.data("check-task") || "").toString();
                        var required = item.data("required") === 1 || item.data("required") === "1";
                        var ready = checklistReadiness(task);
                        var checked = item.find("input[type=\'checkbox\']").is(":checked");
                        var status = item.find(".dls-writing-desk__task-status");

                        item.toggleClass("is-ready", ready);
                        item.toggleClass("is-done", ready && checked);
                        item.toggleClass("is-missing", required && (!ready || !checked));

                        if (required) {
                            requiredTotal += 1;
                            if (ready && checked) {
                                requiredDone += 1;
                            } else {
                                missingRequired.push(task);
                            }
                        }

                        if (ready && checked) {
                            status.text("Done");
                        } else if (ready) {
                            status.text("Ready");
                        } else if (required) {
                            status.text("Needed");
                        } else {
                            status.text("Optional");
                        }
                    });

                    var percent = requiredTotal > 0 ? Math.round((requiredDone / requiredTotal) * 100) : 100;
                    $(".dls-writing-desk__checklist-fill").css("width", percent + "%");
                    $(".dls-writing-desk__checklist-score").text(requiredDone + "/" + requiredTotal);
                    $(".dls-writing-desk__publish-warning").toggleClass("is-visible", missingRequired.length > 0 && !canOverride);
                    $("button[name=\'dls_writing_desk_submit\'][value=\'publish\']").prop("disabled", missingRequired.length > 0 && !canOverride);
                }

                $(document).on("click", ".dls-writing-desk__select-image", function (event) {
                    event.preventDefault();
                    openMedia("#dls-writing-desk-thumbnail-id", ".dls-writing-desk__thumb-preview", "No featured image yet");
                    window.setTimeout(updateChecklist, 300);
                });

                $(document).on("click", ".dls-writing-desk__remove-image", function (event) {
                    event.preventDefault();
                    $("#dls-writing-desk-thumbnail-id").val("");
                    setThumb("");
                    updateChecklist();
                });

                $(document).on("click", ".dls-writing-desk__select-media", function (event) {
                    event.preventDefault();
                    openMedia($(this).data("target"), $(this).data("preview"), $(this).data("placeholder") || "No image selected");
                });

                $(document).on("click", ".dls-writing-desk__remove-media", function (event) {
                    event.preventDefault();
                    var target = $(this).data("target") || "";
                    var preview = $(this).data("preview") || "";
                    var placeholder = $(this).data("placeholder") || "No image selected";
                    if (target) {
                        $(target).val("");
                    }
                    if (preview) {
                        setPreview(preview, "", placeholder);
                    }
                });

                $(document).on("click", ".dls-writing-desk__copy-link", function (event) {
                    event.preventDefault();
                    var target = $(this).data("target") || "";
                    var input = $(target);
                    if (!input.length) {
                        return;
                    }
                    input.trigger("focus").trigger("select");
                    if (navigator.clipboard && input.val()) {
                        navigator.clipboard.writeText(String(input.val()));
                    }
                });

                $(document).on("click", ".dls-writing-desk__add-destination", function (event) {
                    event.preventDefault();
                    addDestinationRow();
                });

                $(document).on("click", ".dls-writing-desk__add-telegram-button", function (event) {
                    event.preventDefault();
                    addTelegramButtonRow(this);
                });

                $(document).on("click", ".dls-writing-desk__remove-telegram-button", function (event) {
                    event.preventDefault();
                    $(this).closest(".dls-writing-desk__telegram-button-row").remove();
                });

                $(document).on("input", "#dls-writing-desk-title, #dls-writing-desk-lead", updateCounts);
                $(document).on("input change", "#dls-writing-desk-author, #dls-writing-desk-thumbnail-id, input[name=\'dls_writing_desk_categories[]\'], .dls-writing-desk__checklist input, .dls-writing-desk__new-taxonomy, .dls-writing-desk__task input", updateChecklist);
                $(document).on("change", "#dls-writing-desk-language", applyLanguageFilter);
                $(document).on("input", ".dls-writing-desk__input--search", function () {
                    var target = $(this).data("target") || "";
                    if (target === "#dls-writing-desk-post-list") {
                        filterRecentPosts();
                        return;
                    }
                    if (target) {
                        filterChecklist(target);
                    }
                });
                $(document).ready(function () {
                    updateCounts();
                    applyLanguageFilter();
                    filterRecentPosts();
                    updateChecklist();
                });

                $(document).on("submit", "form[action*=\"admin-post.php\"]", function () {
                    if (window.tinyMCE && typeof tinyMCE.triggerSave === "function") {
                        tinyMCE.triggerSave();
                    }
                });

                if (window.tinyMCE) {
                    $(document).on("tinymce-editor-init", function (event, editor) {
                        if (editor && editor.id === "dls_writing_desk_content") {
                            editor.on("keyup change setcontent", updateCounts);
                            editor.on("keyup change setcontent", updateChecklist);
                            updateCounts();
                            updateChecklist();
                        }
                    });
                }
            })(jQuery);
        ');
    }
}
add_action('admin_enqueue_scripts', 'dls_writing_desk_enqueue_assets');

if (!function_exists('dls_writing_desk_save_post')) {
    function dls_writing_desk_save_post() {
        if (!current_user_can('edit_posts')) {
            wp_die('You do not have permission to write posts.');
        }

        check_admin_referer('dls_writing_desk_save', 'dls_writing_desk_nonce');

        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? 0);
        $existing_status = $post_id > 0 ? (string) get_post_status($post_id) : '';
        $status_action = strtolower(trim((string) ($_POST['dls_writing_desk_submit'] ?? 'draft')));
        if (!dls_writing_desk_can_show_element('publish_box') && $status_action === 'publish') {
            $status_action = 'draft';
        }
        $requested_status = $status_action === 'publish' ? 'publish' : 'draft';

        if ($post_id > 0 && !current_user_can('edit_post', $post_id)) {
            wp_die('You do not have permission to edit this post.');
        }

        $title = sanitize_text_field((string) ($_POST['dls_writing_desk_title'] ?? ''));
        $kicker = sanitize_text_field((string) ($_POST['dls_writing_desk_kicker'] ?? ''));
        $content = dls_writing_desk_normalize_saved_content(wp_unslash($_POST['dls_writing_desk_content'] ?? ''));
        $lead = sanitize_textarea_field((string) ($_POST['dls_writing_desk_lead'] ?? ''));
        $language = dls_writing_desk_can_show_element('language')
            ? dls_writing_desk_normalize_language($_POST['dls_writing_desk_language'] ?? '')
            : ($post_id > 0 ? dls_writing_desk_get_post_language($post_id) : '');
        $thumbnail_id = dls_writing_desk_can_show_element('featured_image')
            ? absint($_POST['dls_writing_desk_thumbnail_id'] ?? 0)
            : ($post_id > 0 ? absint(get_post_thumbnail_id($post_id)) : 0);
        $categories = dls_writing_desk_can_show_element('categories')
            ? array_values(array_filter(array_map('absint', (array) ($_POST['dls_writing_desk_categories'] ?? []))))
            : ($post_id > 0 ? array_map('intval', (array) wp_get_post_categories($post_id)) : []);
        $new_terms = isset($_POST['dls_writing_desk_new_terms']) ? (array) wp_unslash($_POST['dls_writing_desk_new_terms']) : [];
        $publish_at_raw = dls_writing_desk_can_show_element('publish_box')
            ? sanitize_text_field((string) ($_POST['dls_writing_desk_publish_at'] ?? ''))
            : '';
        if (dls_writing_desk_can_show_element('people')) {
            $author_value = sanitize_text_field((string) ($_POST['dls_writing_desk_author'] ?? ''));
            $editor_value = sanitize_text_field((string) ($_POST['dls_writing_desk_editor'] ?? ''));
        } else {
            $selected_people = $post_id > 0 ? dls_writing_desk_get_selected_people($post_id) : ['author' => '', 'editor' => ''];
            $author_value = (string) ($selected_people['author'] ?? '');
            $editor_value = (string) ($selected_people['editor'] ?? '');
        }
        $submitted_taxonomies = [];
        if (dls_writing_desk_can_show_element('taxonomies')) {
            foreach (dls_writing_desk_extra_taxonomies() as $taxonomy_item) {
                $taxonomy = sanitize_key((string) ($taxonomy_item['taxonomy'] ?? ''));
                if ($taxonomy === '') {
                    continue;
                }

                $field_name = 'dls_writing_desk_tax_' . str_replace('-', '_', $taxonomy);
                $submitted_taxonomies[$taxonomy] = array_values(array_filter(array_map('absint', (array) ($_POST[$field_name] ?? []))));
                if (trim((string) ($new_terms[$taxonomy] ?? '')) !== '') {
                    $submitted_taxonomies[$taxonomy][] = -1;
                }
            }
        } elseif ($post_id > 0) {
            foreach (dls_writing_desk_extra_taxonomies() as $taxonomy_item) {
                $taxonomy = sanitize_key((string) ($taxonomy_item['taxonomy'] ?? ''));
                if ($taxonomy !== '') {
                    $submitted_taxonomies[$taxonomy] = array_map('intval', (array) wp_get_post_terms($post_id, $taxonomy, ['fields' => 'ids']));
                }
            }
        }

        $checklist_done = array_values(array_unique(array_map('sanitize_key', (array) ($_POST['dls_writing_desk_checklist'] ?? []))));
        $checklist_state = dls_writing_desk_checklist_state($content, $thumbnail_id, $categories, $author_value, $submitted_taxonomies);
        $checklist_missing = dls_writing_desk_missing_required_checklist($checklist_state, $checklist_done);
        $checklist_blocked_publish = $requested_status === 'publish' && !current_user_can('manage_options') && !empty($checklist_missing);
        if ($checklist_blocked_publish) {
            $requested_status = 'draft';
        }

        $publish_dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $publish_at_raw, dls_writing_desk_wp_timezone());
        $post_date = current_time('mysql');
        $post_date_gmt = current_time('mysql', true);

        if ($publish_dt instanceof DateTimeImmutable) {
            $post_date = $publish_dt->format('Y-m-d H:i:s');
            $post_date_gmt = $publish_dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        }

        $post_status = $requested_status;
        if ($checklist_blocked_publish && in_array($existing_status, ['publish', 'future'], true)) {
            $post_status = $existing_status;
        }
        if ($requested_status === 'publish' && $publish_dt instanceof DateTimeImmutable) {
            $now_ts = dls_writing_desk_current_timestamp();
            if ($publish_dt->getTimestamp() > ($now_ts + 60)) {
                $post_status = 'future';
            }
        }

        $postarr = [
            'post_type'     => 'post',
            'post_title'    => $title !== '' ? $title : 'Untitled Draft',
            'post_content'  => $content,
            'post_excerpt'  => $lead,
            'post_status'   => $post_status,
            'post_date'     => $post_date,
            'post_date_gmt' => $post_date_gmt,
        ];

        if ($post_id > 0) {
            $postarr['ID'] = $post_id;
            $postarr['edit_date'] = true;
        } else {
            $postarr['post_author'] = get_current_user_id();
        }

        $saved_post_id = wp_insert_post(wp_slash($postarr), true);
        if (is_wp_error($saved_post_id)) {
            wp_die(esc_html($saved_post_id->get_error_message()));
        }

        dls_writing_desk_update_post_kicker($saved_post_id, $kicker);

        $created_category_ids = dls_writing_desk_create_terms_from_input('category', $new_terms['category'] ?? '', $language);
        wp_set_post_terms($saved_post_id, array_values(array_unique(array_merge($categories, $created_category_ids))), 'category', false);

        if (dls_writing_desk_can_show_element('taxonomies')) {
            foreach (dls_writing_desk_extra_taxonomies() as $taxonomy_item) {
                $taxonomy = sanitize_key((string) ($taxonomy_item['taxonomy'] ?? ''));
                if ($taxonomy === '') {
                    continue;
                }

                $field_name = 'dls_writing_desk_tax_' . str_replace('-', '_', $taxonomy);
                $term_ids = array_values(array_filter(array_map('absint', (array) ($_POST[$field_name] ?? []))));
                $created_ids = dls_writing_desk_create_terms_from_input($taxonomy, $new_terms[$taxonomy] ?? '', $language);
                wp_set_post_terms($saved_post_id, array_values(array_unique(array_merge($term_ids, $created_ids))), $taxonomy, false);
            }
        }

        if ($language !== '') {
            dls_writing_desk_set_post_language($saved_post_id, $language);
        }

        if ($thumbnail_id > 0) {
            set_post_thumbnail($saved_post_id, $thumbnail_id);
        } else {
            delete_post_thumbnail($saved_post_id);
        }

        if (dls_writing_desk_can_show_element('people')) {
            $author_value = sanitize_text_field((string) ($_POST['dls_writing_desk_author'] ?? ''));
            $editor_value = sanitize_text_field((string) ($_POST['dls_writing_desk_editor'] ?? ''));
        } else {
            $selected_people = $post_id > 0 ? dls_writing_desk_get_selected_people($post_id) : ['author' => '', 'editor' => ''];
            $author_value = (string) ($selected_people['author'] ?? '');
            $editor_value = (string) ($selected_people['editor'] ?? '');
        }

        if (function_exists('dls_native_authors_save_assignments_for_post')) {
            $selected_items = [];
            $role_map = [];

            $author_selection = dls_writing_desk_parse_selection($author_value);
            if (!empty($author_selection)) {
                $selected_items[] = $author_selection;
                $role_map[$author_value] = 'author';
            }

            $editor_selection = dls_writing_desk_validate_editor_selection(dls_writing_desk_parse_selection($editor_value));
            if (!empty($editor_selection)) {
                $selected_items[] = $editor_selection;
                $role_map[$editor_value] = 'editor';
            }

            dls_native_authors_save_assignments_for_post($saved_post_id, $selected_items, $role_map);
            dls_writing_desk_update_stored_selection($saved_post_id, 'author', $author_value);
            dls_writing_desk_update_stored_selection($saved_post_id, 'editor', $editor_value);
        } else {
            dls_writing_desk_save_publishpress_assignments($saved_post_id, $author_value, $editor_value);
        }

        $configured_destinations = dls_writing_desk_get_social_destinations();
        $social_settings = dls_writing_desk_get_post_social_settings($saved_post_id);
        if (dls_writing_desk_can_show_element('social')) {
            $social_payload = isset($_POST['dls_writing_desk_social']) ? (array) wp_unslash($_POST['dls_writing_desk_social']) : [];
            foreach ($configured_destinations as $destination) {
                $key = (string) ($destination['key'] ?? '');
                if ($key === '' || !isset($social_payload[$key]) || !is_array($social_payload[$key])) {
                    continue;
                }

                $row = $social_payload[$key];
                $social_settings[$key] = [
                    'enabled'     => !empty($row['enabled']) ? 1 : 0,
                    'description' => sanitize_textarea_field((string) ($row['description'] ?? '')),
                    'image_id'    => absint($row['image_id'] ?? 0),
                    'button_text' => sanitize_text_field((string) ($row['button_text'] ?? '')),
                    'button_url'  => esc_url_raw((string) ($row['button_url'] ?? '')),
                    'buttons'     => dls_writing_desk_sanitize_telegram_buttons($row['buttons'] ?? [], (string) ($row['button_text'] ?? ''), (string) ($row['button_url'] ?? '')),
                    'silent'      => !empty($row['silent']) ? 1 : 0,
                    'pin'         => !empty($row['pin']) ? 1 : 0,
                    'auto_delete' => absint($row['auto_delete'] ?? 0),
                ];
            }
        }
        update_post_meta($saved_post_id, '_dls_writing_desk_social_settings', $social_settings);
        update_post_meta($saved_post_id, '_dls_writing_desk_checklist_done', $checklist_done);

        $notice = 'saved';
        if ($checklist_blocked_publish) {
            $notice = 'checklist_blocked';
        } elseif ($post_status === 'future') {
            $notice = 'scheduled';
        } elseif ($requested_status === 'publish') {
            $notice = $post_id > 0 ? 'updated' : 'published';
        }

        $redirect_url = add_query_arg([
            'page'        => 'dls-writing-desk',
            'desk_post'   => $saved_post_id,
            'desk_notice' => $notice,
        ], admin_url('admin.php'));

        wp_safe_redirect($redirect_url);
        exit;
    }
}
add_action('admin_post_dls_writing_desk_save', 'dls_writing_desk_save_post');

if (!function_exists('dls_writing_desk_save_telegram_broadcast')) {
    function dls_writing_desk_save_telegram_broadcast() {
        if (!current_user_can('edit_posts')) {
            wp_die('You do not have permission to send Telegram broadcasts.');
        }

        check_admin_referer('dls_writing_desk_telegram_save', 'dls_writing_desk_telegram_nonce');

        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? 0);
        $post = $post_id > 0 ? get_post($post_id) : null;
        if (!($post instanceof WP_Post) || $post->post_type !== 'post' || !current_user_can('edit_post', $post->ID)) {
            wp_die('Choose a valid post first.');
        }

        $configured_destinations = dls_writing_desk_get_social_destinations();
        $social_payload = isset($_POST['dls_writing_desk_social']) ? (array) wp_unslash($_POST['dls_writing_desk_social']) : [];
        $social_settings = dls_writing_desk_get_post_social_settings($post_id);

        foreach ($configured_destinations as $destination) {
            $key = (string) ($destination['key'] ?? '');
            if ($key === '' || ($destination['platform'] ?? '') !== 'telegram') {
                continue;
            }

            $row = isset($social_payload[$key]) && is_array($social_payload[$key]) ? $social_payload[$key] : [];
            $social_settings[$key] = [
                'enabled'     => !empty($row['enabled']) ? 1 : 0,
                'description' => sanitize_textarea_field((string) ($row['description'] ?? '')),
                'image_id'    => absint($row['image_id'] ?? 0),
                'button_text' => sanitize_text_field((string) ($row['button_text'] ?? '')),
                'button_url'  => esc_url_raw((string) ($row['button_url'] ?? '')),
                'buttons'     => dls_writing_desk_sanitize_telegram_buttons($row['buttons'] ?? [], (string) ($row['button_text'] ?? ''), (string) ($row['button_url'] ?? '')),
                'silent'      => !empty($row['silent']) ? 1 : 0,
                'pin'         => !empty($row['pin']) ? 1 : 0,
                'auto_delete' => absint($row['auto_delete'] ?? 0),
            ];
        }

        update_post_meta($post_id, '_dls_writing_desk_social_settings', $social_settings);

	        $publish_at_raw = sanitize_text_field((string) ($_POST['dls_writing_desk_telegram_publish_at'] ?? ''));
	        $publish_dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $publish_at_raw, dls_writing_desk_wp_timezone());
	        $enabled_telegram = 0;
	        wp_clear_scheduled_hook('dls_writing_desk_telegram_scheduled_send', [$post_id]);

        foreach ($configured_destinations as $destination) {
            $key = sanitize_key((string) ($destination['key'] ?? ''));
            if ($key === '' || ($destination['platform'] ?? '') !== 'telegram' || empty($destination['active'])) {
                continue;
            }

            if (!empty($social_settings[$key]['enabled'])) {
                $enabled_telegram++;
            }
        }

        if ($enabled_telegram < 1) {
            $notice = 'telegram_empty';
            wp_safe_redirect(add_query_arg([
                'page'        => 'dls-writing-desk-telegram',
                'desk_post'   => $post_id,
                'desk_notice' => $notice,
            ], admin_url('admin.php')));
            exit;
        }

	        if ($publish_dt instanceof DateTimeImmutable && $publish_dt->getTimestamp() > (dls_writing_desk_current_timestamp() + 60)) {
            wp_schedule_single_event($publish_dt->getTimestamp(), 'dls_writing_desk_telegram_scheduled_send', [$post_id]);

            foreach ($configured_destinations as $destination) {
                $key = sanitize_key((string) ($destination['key'] ?? ''));
                if ($key === '' || ($destination['platform'] ?? '') !== 'telegram' || empty($destination['active']) || empty($social_settings[$key]['enabled'])) {
                    continue;
                }

                dls_writing_desk_update_telegram_log($post_id, $key, [
                    'status'  => 'scheduled',
                    'message' => 'Scheduled for ' . $publish_dt->format('Y-m-d H:i'),
                ]);
            }

            wp_safe_redirect(add_query_arg([
                'page'        => 'dls-writing-desk-telegram',
                'desk_post'   => $post_id,
                'desk_notice' => 'telegram_scheduled',
            ], admin_url('admin.php')));
            exit;
        }

        $telegram_result = dls_writing_desk_send_enabled_telegram($post_id, $configured_destinations, $social_settings);
        if ($telegram_result['sent'] > 0 && $telegram_result['failed'] > 0) {
            $notice = 'telegram_partial';
        } elseif ($telegram_result['sent'] > 0) {
            $notice = 'telegram_sent';
        } elseif ($telegram_result['failed'] > 0) {
            $notice = 'telegram_failed';
        } else {
            $notice = 'telegram_empty';
        }

        wp_safe_redirect(add_query_arg([
            'page'        => 'dls-writing-desk-telegram',
            'desk_post'   => $post_id,
            'desk_notice' => $notice,
        ], admin_url('admin.php')));
        exit;
    }
}
add_action('admin_post_dls_writing_desk_telegram_save', 'dls_writing_desk_save_telegram_broadcast');

if (!function_exists('dls_writing_desk_render_destinations_page')) {
    function dls_writing_desk_render_destinations_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $destinations = dls_writing_desk_get_social_destinations();
        $platforms = dls_writing_desk_destination_platforms();
        $notice_message = dls_writing_desk_notice_message($_GET['desk_notice'] ?? '');
        ?>
        <div class="wrap dls-writing-desk">
            <div class="dls-writing-desk__admin-card">
                <div class="dls-writing-desk__admin-head">
                    <div>
                        <div class="dls-writing-desk__eyebrow">Dead Lawyers Society / Social Destinations</div>
                        <h1 class="dls-writing-desk__admin-title">Connect pages and channels</h1>
                        <p class="dls-writing-desk__admin-sub">Add each Facebook page, LinkedIn page, or Telegram channel here. These connections feed the Writing Desk.</p>
                    </div>
                    <div class="dls-writing-desk__actions">
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('admin.php?page=dls-writing-desk')); ?>">Back to Writing Desk</a>
                    </div>
                </div>

                <?php if ($notice_message !== '') : ?>
                    <div class="notice dls-writing-desk__notice"><p><?php echo esc_html($notice_message); ?></p></div>
                <?php endif; ?>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('dls_writing_desk_destinations_save', 'dls_writing_desk_destinations_nonce'); ?>
                    <input type="hidden" name="action" value="dls_writing_desk_destinations_save">

                    <table id="dls-writing-desk-destination-table" class="dls-writing-desk__admin-table widefat striped">
                        <thead>
                            <tr>
                                <th>Platform</th>
                                <th>Name</th>
                                <th>Page / Channel</th>
                                <th>Access Token / Bot Token</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($destinations as $index => $destination) : ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="dls_writing_desk_destinations[<?php echo esc_attr((string) $index); ?>][key]" value="<?php echo esc_attr((string) $destination['key']); ?>">
                                        <select class="dls-writing-desk__select" name="dls_writing_desk_destinations[<?php echo esc_attr((string) $index); ?>][platform]">
                                            <?php foreach ($platforms as $platform => $label) : ?>
                                                <option value="<?php echo esc_attr($platform); ?>" <?php selected($destination['platform'], $platform); ?>><?php echo esc_html($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input class="dls-writing-desk__input" type="text" name="dls_writing_desk_destinations[<?php echo esc_attr((string) $index); ?>][name]" value="<?php echo esc_attr((string) $destination['name']); ?>" placeholder="Public label"></td>
                                    <td><input class="dls-writing-desk__input" type="text" name="dls_writing_desk_destinations[<?php echo esc_attr((string) $index); ?>][destination]" value="<?php echo esc_attr((string) $destination['destination']); ?>" placeholder="Page ID, URL, or @channel"></td>
                                    <td><input class="dls-writing-desk__input" type="text" name="dls_writing_desk_destinations[<?php echo esc_attr((string) $index); ?>][token]" value="<?php echo esc_attr((string) $destination['token']); ?>" placeholder="Access token or bot token"></td>
                                    <td><label><input type="checkbox" name="dls_writing_desk_destinations[<?php echo esc_attr((string) $index); ?>][active]" value="1" <?php checked(!empty($destination['active'])); ?>> Active</label></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <script type="text/html" id="tmpl-dls-writing-desk-destination-row">
                        <tr>
                            <td>
                                <input type="hidden" name="dls_writing_desk_destinations[__index__][key]" value="">
                                <select class="dls-writing-desk__select" name="dls_writing_desk_destinations[__index__][platform]">
                                    <?php foreach ($platforms as $platform => $label) : ?>
                                        <option value="<?php echo esc_attr($platform); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input class="dls-writing-desk__input" type="text" name="dls_writing_desk_destinations[__index__][name]" value="" placeholder="Public label"></td>
                            <td><input class="dls-writing-desk__input" type="text" name="dls_writing_desk_destinations[__index__][destination]" value="" placeholder="Page ID, URL, or @channel"></td>
                            <td><input class="dls-writing-desk__input" type="text" name="dls_writing_desk_destinations[__index__][token]" value="" placeholder="Access token or bot token"></td>
                            <td><label><input type="checkbox" name="dls_writing_desk_destinations[__index__][active]" value="1" checked> Active</label></td>
                        </tr>
                    </script>

                    <div class="dls-writing-desk__panel-actions" style="margin-top:16px;">
                        <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__add-destination" type="button">Add Destination</button>
                        <button class="dls-writing-desk__button dls-writing-desk__button--accent" type="submit">Save Destinations</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('dls_writing_desk_render_access_page')) {
    function dls_writing_desk_render_access_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = dls_writing_desk_get_access_settings();
        $roles = dls_writing_desk_access_roles();
        $elements = dls_writing_desk_access_elements();
        $notice_message = dls_writing_desk_notice_message($_GET['desk_notice'] ?? '');
        ?>
        <div class="wrap dls-writing-desk">
            <div class="dls-writing-desk__admin-card">
                <div class="dls-writing-desk__admin-head">
                    <div>
                        <div class="dls-writing-desk__eyebrow">Dead Lawyers Society / Access Rights</div>
                        <h1 class="dls-writing-desk__admin-title">Control Writing Desk elements</h1>
                        <p class="dls-writing-desk__admin-sub">Switch interface elements on or off for each author category. Administrators always keep full access.</p>
                    </div>
                    <div class="dls-writing-desk__actions">
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('admin.php?page=dls-writing-desk')); ?>">Back to Writing Desk</a>
                    </div>
                </div>

                <?php if ($notice_message !== '') : ?>
                    <div class="notice dls-writing-desk__notice"><p><?php echo esc_html($notice_message); ?></p></div>
                <?php endif; ?>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('dls_writing_desk_access_save', 'dls_writing_desk_access_nonce'); ?>
                    <input type="hidden" name="action" value="dls_writing_desk_access_save">

                    <table class="dls-writing-desk__admin-table widefat striped">
                        <thead>
                            <tr>
                                <th>Interface Element</th>
                                <?php foreach ($roles as $role_label) : ?>
                                    <th><?php echo esc_html($role_label); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($elements as $element => $element_label) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($element_label); ?></strong></td>
                                    <?php foreach ($roles as $role => $role_label) : ?>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="dls_writing_desk_access[<?php echo esc_attr($role); ?>][<?php echo esc_attr($element); ?>]" value="1" <?php checked(!empty($settings[$role][$element])); ?> <?php disabled($role === 'administrator'); ?>>
                                                On
                                            </label>
                                            <?php if ($role === 'administrator') : ?>
                                                <input type="hidden" name="dls_writing_desk_access[<?php echo esc_attr($role); ?>][<?php echo esc_attr($element); ?>]" value="1">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="dls-writing-desk__panel-actions" style="margin-top:16px;">
                        <button class="dls-writing-desk__button dls-writing-desk__button--accent" type="submit">Save Access Rights</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('dls_writing_desk_render_telegram_page')) {
    function dls_writing_desk_render_telegram_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $post_id = absint($_GET['desk_post'] ?? 0);
        $post = $post_id > 0 ? get_post($post_id) : null;
        if (!($post instanceof WP_Post) || $post->post_type !== 'post' || !current_user_can('edit_post', $post->ID)) {
            $post = null;
            $post_id = 0;
        }

        $recent_posts = dls_writing_desk_recent_posts();
        $notice_message = dls_writing_desk_notice_message($_GET['desk_notice'] ?? '');
        $view_url = dls_writing_desk_preview_link($post);
        $social_destinations = array_values(array_filter(
            dls_writing_desk_get_social_destinations(),
            static function ($destination) {
                return !empty($destination['active']) && ($destination['platform'] ?? '') === 'telegram';
            }
        ));
        $social_settings = $post ? dls_writing_desk_get_post_social_settings($post->ID) : [];
        $telegram_log = $post ? dls_writing_desk_get_telegram_log($post->ID) : [];
        $scheduled_timestamp = $post ? wp_next_scheduled('dls_writing_desk_telegram_scheduled_send', [$post->ID]) : false;
        $telegram_publish_at_value = $scheduled_timestamp ? dls_writing_desk_wp_date('Y-m-d\TH:i', (int) $scheduled_timestamp) : '';
        $post_preview_content = '';
        if ($post instanceof WP_Post) {
            $raw_preview_content = (string) $post->post_content;
            $post_preview_content = function_exists('has_blocks') && function_exists('do_blocks') && has_blocks($raw_preview_content)
                ? do_blocks($raw_preview_content)
                : wpautop($raw_preview_content);
        }
        ?>
        <div class="wrap dls-writing-desk">
            <div class="dls-writing-desk__topbar">
                <div class="dls-writing-desk__brand">
                    <span class="dls-writing-desk__eyebrow">Dead Lawyers Society / Telegram Broadcast</span>
                    <h1 class="dls-writing-desk__title">Prepare Telegram separately from writing.</h1>
                    <p class="dls-writing-desk__sub">Choose a post, then prepare channel-specific messages, images and Telegram options.</p>
                </div>
                <div class="dls-writing-desk__actions">
                    <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('admin.php?page=dls-writing-desk')); ?>">Writing Desk</a>
                    <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('admin.php?page=dls-writing-desk-destinations')); ?>">Social Destinations</a>
                    <?php if ($view_url !== '') : ?>
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url($view_url); ?>" target="_blank" rel="noopener">Preview Post</a>
                    <?php endif; ?>
                    <?php if ($post instanceof WP_Post) : ?>
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(get_edit_post_link($post->ID, '')); ?>">WordPress Editor</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($notice_message !== '') : ?>
                <div class="notice dls-writing-desk__notice"><p><?php echo esc_html($notice_message); ?></p></div>
            <?php endif; ?>

            <div class="dls-writing-desk__shell dls-writing-desk__shell--telegram">
                <aside class="dls-writing-desk__panel">
                    <div class="dls-writing-desk__field">
                        <div class="dls-writing-desk__label"><span>Posts</span><span><?php echo esc_html((string) count($recent_posts)); ?></span></div>
                        <input id="dls-writing-desk-post-search" class="dls-writing-desk__input dls-writing-desk__input--search" type="search" data-target="#dls-writing-desk-post-list" placeholder="Search posts">
                    </div>
                    <div id="dls-writing-desk-post-list" class="dls-writing-desk__posts">
                        <?php foreach ($recent_posts as $recent_post) : ?>
                            <?php if (!($recent_post instanceof WP_Post)) { continue; } ?>
                            <?php $is_active = $post instanceof WP_Post && $post->ID === $recent_post->ID; ?>
                            <a class="dls-writing-desk__post-link<?php echo $is_active ? ' is-active' : ''; ?>" data-search="<?php echo esc_attr(strtolower(get_the_title($recent_post->ID) . ' ' . $recent_post->post_status)); ?>" href="<?php echo esc_url(add_query_arg(['page' => 'dls-writing-desk-telegram', 'desk_post' => $recent_post->ID], admin_url('admin.php'))); ?>">
                                <span class="dls-writing-desk__post-title"><?php echo esc_html(get_the_title($recent_post->ID) !== '' ? get_the_title($recent_post->ID) : 'Untitled Draft'); ?></span>
                                <span class="dls-writing-desk__post-meta"><?php echo esc_html(ucfirst((string) $recent_post->post_status)); ?> / <?php echo esc_html(get_the_modified_date('Y-m-d', $recent_post->ID)); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </aside>

                <section class="dls-writing-desk__editor">
                    <?php if (!($post instanceof WP_Post)) : ?>
                        <div class="dls-writing-desk__admin-card">
                            <h2 class="dls-writing-desk__admin-title">Choose a post</h2>
                            <p class="dls-writing-desk__admin-sub">Select a post from the left panel before preparing a Telegram broadcast.</p>
                        </div>
                    <?php else : ?>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('dls_writing_desk_telegram_save', 'dls_writing_desk_telegram_nonce'); ?>
                            <input type="hidden" name="action" value="dls_writing_desk_telegram_save">
                            <input type="hidden" name="dls_writing_desk_post_id" value="<?php echo esc_attr((string) $post->ID); ?>">

                            <div class="dls-writing-desk__story-sheet">
	                                <div class="dls-writing-desk__block">
	                                    <div class="dls-writing-desk__label"><span>Selected Post</span><span><?php echo esc_html(ucfirst((string) $post->post_status)); ?></span></div>
	                                    <h2 style="margin:0;"><?php echo esc_html(get_the_title($post->ID) !== '' ? get_the_title($post->ID) : 'Untitled Draft'); ?></h2>
	                                    <?php if (trim((string) $post->post_excerpt) !== '') : ?>
	                                        <p class="dls-writing-desk__muted"><?php echo esc_html(wp_trim_words((string) $post->post_excerpt, 34)); ?></p>
	                                    <?php endif; ?>
	                                </div>

	                                <div class="dls-writing-desk__telegram-preview">
	                                    <div class="dls-writing-desk__block">
	                                        <h3>Post Preview</h3>
	                                        <div class="dls-writing-desk__post-preview">
	                                            <h1><?php echo esc_html(get_the_title($post->ID) !== '' ? get_the_title($post->ID) : 'Untitled Draft'); ?></h1>
	                                            <?php if (trim((string) $post->post_excerpt) !== '') : ?>
	                                                <p class="dls-writing-desk__post-preview-lead"><?php echo esc_html((string) $post->post_excerpt); ?></p>
	                                            <?php endif; ?>
	                                            <?php echo wp_kses_post($post_preview_content); ?>
	                                        </div>
	                                    </div>
	                                    <div class="dls-writing-desk__block">
	                                        <h3>Schedule</h3>
	                                        <input class="dls-writing-desk__input dls-writing-desk__input--datetime" type="datetime-local" name="dls_writing_desk_telegram_publish_at" value="<?php echo esc_attr($telegram_publish_at_value); ?>">
	                                        <p class="dls-writing-desk__muted">Leave empty to send now. Choose a future date and time to schedule Telegram posting.</p>
	                                    </div>
	                                </div>

	                                <div class="dls-writing-desk__block">
                                    <h3>Telegram Channels</h3>
                                    <?php if (empty($social_destinations)) : ?>
                                        <p class="dls-writing-desk__muted">No Telegram channels yet. Add them on the Social Destinations page.</p>
                                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('admin.php?page=dls-writing-desk-destinations')); ?>">Open Social Destinations</a>
                                    <?php else : ?>
                                        <?php foreach ($social_destinations as $destination) : ?>
                                            <?php
                                            $key = (string) ($destination['key'] ?? '');
                                            $settings = is_array($social_settings[$key] ?? null) ? $social_settings[$key] : [];
                                            $telegram_buttons = dls_writing_desk_sanitize_telegram_buttons($settings['buttons'] ?? [], (string) ($settings['button_text'] ?? ''), (string) ($settings['button_url'] ?? ''));
                                            $social_image_id = absint($settings['image_id'] ?? 0);
                                            $social_image_url = $social_image_id > 0 ? (string) wp_get_attachment_image_url($social_image_id, 'medium') : '';
                                            $preview_id = 'dls-writing-desk-social-preview-' . $key;
                                            $input_id = 'dls-writing-desk-social-image-' . $key;
                                            $buttons_list_id = 'dls-writing-desk-telegram-buttons-' . $key;
                                            $buttons_template_id = 'tmpl-dls-writing-desk-telegram-button-' . $key;
                                            $telegram_status = is_array($telegram_log[$key] ?? null) ? $telegram_log[$key] : [];
                                            ?>
                                            <div class="dls-writing-desk__social-card dls-writing-desk__social-card--telegram">
                                                <div class="dls-writing-desk__label"><span><?php echo esc_html((string) $destination['name']); ?></span><span><?php echo esc_html((string) $destination['destination']); ?></span></div>
                                                <?php if (!empty($telegram_status)) : ?>
                                                    <div class="dls-writing-desk__delivery-status dls-writing-desk__delivery-status--<?php echo esc_attr((string) ($telegram_status['status'] ?? '')); ?>">
                                                        <?php echo esc_html((string) ($telegram_status['message'] ?? '')); ?>
                                                        <?php if (!empty($telegram_status['message_id'])) : ?>
                                                            <span>#<?php echo esc_html((string) absint($telegram_status['message_id'])); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <label class="dls-writing-desk__check" style="margin-bottom:12px;">
                                                    <input type="checkbox" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?>>
                                                    <span>Send to this Telegram channel</span>
                                                </label>
                                                <textarea class="dls-writing-desk__textarea dls-writing-desk__textarea--telegram-message" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][description]" placeholder="Telegram message for this channel. If empty, the post title, lead and link will be used."><?php echo esc_textarea((string) ($settings['description'] ?? '')); ?></textarea>
                                                <div class="dls-writing-desk__field" style="margin-top:12px;">
                                                    <div class="dls-writing-desk__label"><span>Buttons</span><span>Text only or link</span></div>
                                                    <div id="<?php echo esc_attr($buttons_list_id); ?>" class="dls-writing-desk__telegram-button-list" data-next-index="<?php echo esc_attr((string) count($telegram_buttons)); ?>">
                                                        <?php foreach ($telegram_buttons as $button_index => $button) : ?>
                                                            <div class="dls-writing-desk__telegram-button-row">
                                                                <input class="dls-writing-desk__input dls-writing-desk__input--small" type="text" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][buttons][<?php echo esc_attr((string) $button_index); ?>][text]" value="<?php echo esc_attr((string) ($button['text'] ?? '')); ?>" placeholder="Button text">
                                                                <input class="dls-writing-desk__input dls-writing-desk__input--small" type="url" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][buttons][<?php echo esc_attr((string) $button_index); ?>][url]" value="<?php echo esc_attr((string) ($button['url'] ?? '')); ?>" placeholder="Optional URL">
                                                                <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__remove-telegram-button" type="button">Remove</button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <script type="text/html" id="<?php echo esc_attr($buttons_template_id); ?>">
                                                        <div class="dls-writing-desk__telegram-button-row">
                                                            <input class="dls-writing-desk__input dls-writing-desk__input--small" type="text" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][buttons][__index__][text]" value="" placeholder="Button text">
                                                            <input class="dls-writing-desk__input dls-writing-desk__input--small" type="url" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][buttons][__index__][url]" value="" placeholder="Optional URL">
                                                            <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__remove-telegram-button" type="button">Remove</button>
                                                        </div>
                                                    </script>
                                                    <div class="dls-writing-desk__panel-actions" style="margin-top:10px;">
                                                        <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__add-telegram-button" type="button" data-target="#<?php echo esc_attr($buttons_list_id); ?>" data-template="#<?php echo esc_attr($buttons_template_id); ?>">Add Button</button>
                                                    </div>
                                                    <p class="dls-writing-desk__muted">A button can be just text, or text with a URL.</p>
                                                </div>
                                                <div class="dls-writing-desk__telegram-options">
                                                    <label><input type="checkbox" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][silent]" value="1" <?php checked(!empty($settings['silent'])); ?>> Silent</label>
                                                    <label><input type="checkbox" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][pin]" value="1" <?php checked(!empty($settings['pin'])); ?>> Pin</label>
                                                    <label>Auto-delete <input class="dls-writing-desk__input dls-writing-desk__input--mini" type="number" min="0" step="1" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][auto_delete]" value="<?php echo esc_attr((string) absint($settings['auto_delete'] ?? 0)); ?>"> min</label>
                                                </div>
                                                <div class="dls-writing-desk__thumb" style="margin-top:12px;">
                                                    <input id="<?php echo esc_attr($input_id); ?>" type="hidden" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][image_id]" value="<?php echo esc_attr((string) $social_image_id); ?>">
                                                    <div id="<?php echo esc_attr($preview_id); ?>" class="dls-writing-desk__social-preview">
                                                        <?php if ($social_image_url !== '') : ?>
                                                            <img src="<?php echo esc_url($social_image_url); ?>" alt="">
                                                        <?php else : ?>
                                                            <span>No custom Telegram image</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="dls-writing-desk__actions">
                                                        <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__select-media" type="button" data-target="#<?php echo esc_attr($input_id); ?>" data-preview="#<?php echo esc_attr($preview_id); ?>" data-placeholder="No custom Telegram image">Choose Image</button>
                                                        <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__remove-media" type="button" data-target="#<?php echo esc_attr($input_id); ?>" data-preview="#<?php echo esc_attr($preview_id); ?>" data-placeholder="No custom Telegram image">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

	                                <div class="dls-writing-desk__footer-actions">
	                                    <button class="dls-writing-desk__button dls-writing-desk__button--accent" type="submit">Send / Schedule Telegram Broadcast</button>
	                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('dls_writing_desk_render_page')) {
    function dls_writing_desk_render_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $post_id = absint($_GET['desk_post'] ?? 0);
        $post = $post_id > 0 ? get_post($post_id) : null;

        if (!($post instanceof WP_Post) || $post->post_type !== 'post' || !current_user_can('edit_post', $post->ID)) {
            $post = null;
            $post_id = 0;
        }

        $selected_people = $post ? dls_writing_desk_get_selected_people($post->ID) : ['author' => '', 'editor' => ''];
        $author_options = dls_writing_desk_dropdown_options('author', '', $selected_people['author']);
        $editor_options = dls_writing_desk_dropdown_options('editor', '', $selected_people['editor']);
        $languages = dls_writing_desk_languages();
        $current_language = $post ? dls_writing_desk_get_post_language($post->ID) : '';
        if ($current_language === '') {
            $current_language = dls_writing_desk_selection_language($selected_people['author']);
        }
        if ($current_language === '' && isset($languages['uk'])) {
            $current_language = 'uk';
        } elseif ($current_language === '') {
            $language_keys = array_keys($languages);
            $current_language = !empty($language_keys) ? (string) reset($language_keys) : '';
        }

        $selected_categories = $post ? wp_get_post_categories($post->ID) : [];
        $selected_categories = array_map('intval', (array) $selected_categories);
        $category_terms = dls_writing_desk_taxonomy_terms('category');
        $extra_taxonomies = dls_writing_desk_extra_taxonomies();
        $selected_taxonomies = [];
        foreach ($extra_taxonomies as $taxonomy_item) {
            $taxonomy = sanitize_key((string) ($taxonomy_item['taxonomy'] ?? ''));
            $selected_taxonomies[$taxonomy] = $post ? array_map('intval', (array) wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'ids'])) : [];
        }

        $recent_posts = dls_writing_desk_recent_posts();
        $notice_message = dls_writing_desk_notice_message($_GET['desk_notice'] ?? '');
        $thumbnail_id = $post ? (int) get_post_thumbnail_id($post->ID) : 0;
        $thumbnail_url = $thumbnail_id > 0 ? (string) wp_get_attachment_image_url($thumbnail_id, 'medium_large') : '';
        $kicker = $post ? dls_writing_desk_get_post_kicker($post->ID) : '';
        $lead = $post instanceof WP_Post ? $post->post_excerpt : '';
        $post_content = $post instanceof WP_Post ? $post->post_content : '';
        $checklist_done = $post instanceof WP_Post ? (array) get_post_meta($post->ID, '_dls_writing_desk_checklist_done', true) : [];
        $checklist_done = array_values(array_unique(array_map('sanitize_key', $checklist_done)));
        $checklist_state = dls_writing_desk_checklist_state($post_content, $thumbnail_id, $selected_categories, $selected_people['author'], $selected_taxonomies);
        $view_url = dls_writing_desk_preview_link($post);
        $publish_at_value = dls_writing_desk_current_datetime_value($post);
        $social_destinations = array_values(array_filter(
            dls_writing_desk_get_social_destinations(),
            static function ($destination) {
                return !empty($destination['active']);
            }
        ));
        $social_settings = $post ? dls_writing_desk_get_post_social_settings($post->ID) : [];
        $secondary_social_destinations = array_values(array_filter($social_destinations, static function ($destination) {
            return ($destination['platform'] ?? '') !== 'telegram';
        }));
        $show = [
            'topbar_telegram' => dls_writing_desk_can_show_element('topbar_telegram'),
            'topbar_social'   => dls_writing_desk_can_show_element('topbar_social'),
            'topbar_preview'  => dls_writing_desk_can_show_element('topbar_preview'),
            'wp_editor_link'  => dls_writing_desk_can_show_element('wp_editor_link'),
            'publish_box'     => dls_writing_desk_can_show_element('publish_box'),
            'share_draft'     => dls_writing_desk_can_show_element('share_draft'),
            'language'        => dls_writing_desk_can_show_element('language'),
            'people'          => dls_writing_desk_can_show_element('people'),
            'categories'      => dls_writing_desk_can_show_element('categories'),
            'taxonomies'      => dls_writing_desk_can_show_element('taxonomies'),
            'social'          => dls_writing_desk_can_show_element('social'),
            'featured_image'  => dls_writing_desk_can_show_element('featured_image'),
        ];
        ?>
        <div class="wrap dls-writing-desk">
            <div class="dls-writing-desk__topbar">
                <div class="dls-writing-desk__brand">
                    <span class="dls-writing-desk__eyebrow">Dead Lawyers Society / Writing Desk</span>
                    <h1 class="dls-writing-desk__title">Write the story. Keep the rest quiet.</h1>
                    <p class="dls-writing-desk__sub">Kicker, headline, lead, headings and quotes first. Language, people, time and taxonomy details stay in the side rail.</p>
                </div>
                <div class="dls-writing-desk__actions">
                    <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(add_query_arg(['page' => 'dls-writing-desk'], admin_url('admin.php'))); ?>">New Draft</a>
                    <?php if ($show['topbar_telegram']) : ?>
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('admin.php?page=dls-writing-desk-telegram' . ($post instanceof WP_Post ? '&desk_post=' . $post->ID : ''))); ?>">Telegram Broadcast</a>
                    <?php endif; ?>
                    <?php if ($show['topbar_social']) : ?>
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('admin.php?page=dls-writing-desk-destinations')); ?>">Social Destinations</a>
                    <?php endif; ?>
                    <?php if (current_user_can('manage_options')) : ?>
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('admin.php?page=dls-writing-desk-access')); ?>">Access Rights</a>
                    <?php endif; ?>
                    <?php if ($show['topbar_preview']) : ?>
                        <?php if ($view_url !== '') : ?>
                            <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url($view_url); ?>" target="_blank" rel="noopener">Preview</a>
                        <?php else : ?>
                            <span class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__button--disabled">Preview</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($show['wp_editor_link'] && $post instanceof WP_Post) : ?>
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(get_edit_post_link($post->ID, '')); ?>">Open WordPress Editor</a>
                    <?php endif; ?>
                    <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url()); ?>">Dashboard</a>
                </div>
            </div>

            <?php if ($notice_message !== '') : ?>
                <div class="notice dls-writing-desk__notice"><p><?php echo esc_html($notice_message); ?></p></div>
            <?php endif; ?>

            <div class="dls-writing-desk__shell">
                <aside class="dls-writing-desk__panel">
                    <div class="dls-writing-desk__field">
                        <div class="dls-writing-desk__label"><span>Recent Posts</span><span><?php echo esc_html((string) count($recent_posts)); ?></span></div>
                        <input id="dls-writing-desk-post-search" class="dls-writing-desk__input dls-writing-desk__input--search" type="search" data-target="#dls-writing-desk-post-list" placeholder="Search posts by title or status">
                        <div class="dls-writing-desk__panel-actions" style="margin-top:10px; margin-bottom:12px;">
                            <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url(admin_url('edit.php')); ?>">All Posts</a>
                        </div>
                        <div id="dls-writing-desk-post-list" class="dls-writing-desk__posts">
                            <?php foreach ($recent_posts as $recent_post) : ?>
                                <?php if (!($recent_post instanceof WP_Post)) { continue; } ?>
                                <?php $is_active = $post instanceof WP_Post && $post->ID === $recent_post->ID; ?>
                                <a class="dls-writing-desk__post-link<?php echo $is_active ? ' is-active' : ''; ?>" data-search="<?php echo esc_attr(strtolower(get_the_title($recent_post->ID) . ' ' . $recent_post->post_status)); ?>" href="<?php echo esc_url(add_query_arg(['page' => 'dls-writing-desk', 'desk_post' => $recent_post->ID], admin_url('admin.php'))); ?>">
                                    <span class="dls-writing-desk__post-title"><?php echo esc_html(get_the_title($recent_post->ID) !== '' ? get_the_title($recent_post->ID) : 'Untitled Draft'); ?></span>
                                    <span class="dls-writing-desk__post-meta"><?php echo esc_html(ucfirst((string) $recent_post->post_status)); ?> / <?php echo esc_html(get_the_modified_date('Y-m-d', $recent_post->ID)); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>

                <section class="dls-writing-desk__editor">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('dls_writing_desk_save', 'dls_writing_desk_nonce'); ?>
                        <input type="hidden" name="action" value="dls_writing_desk_save">
                        <input type="hidden" name="dls_writing_desk_post_id" value="<?php echo esc_attr((string) $post_id); ?>">

                        <div class="dls-writing-desk__statline">
                            <span class="dls-writing-desk__word-count">0 words</span>
                            <span class="dls-writing-desk__title-count">0 chars</span>
                            <span class="dls-writing-desk__lead-count">0 chars</span>
                        </div>

                        <div class="dls-writing-desk__editor-grid">
                            <div class="dls-writing-desk__main">
                                <div class="dls-writing-desk__story-sheet">
                                    <div class="dls-writing-desk__story-head">
                                        <input id="dls-writing-desk-kicker" class="dls-writing-desk__input dls-writing-desk__input--kicker" type="text" name="dls_writing_desk_kicker" value="<?php echo esc_attr($kicker); ?>" placeholder="Kicker">
                                        <input id="dls-writing-desk-title" class="dls-writing-desk__input dls-writing-desk__input--title" type="text" name="dls_writing_desk_title" value="<?php echo esc_attr($post instanceof WP_Post ? $post->post_title : ''); ?>" placeholder="Title">
                                        <textarea id="dls-writing-desk-lead" class="dls-writing-desk__textarea dls-writing-desk__textarea--lead" name="dls_writing_desk_lead" placeholder="Lead"><?php echo esc_textarea($lead); ?></textarea>
                                    </div>

                                    <div class="dls-writing-desk__field dls-writing-desk__field--story">
                                        <div class="dls-writing-desk__story-editor">
                                            <?php
                                            wp_editor(
                                                $post_content,
                                                'dls_writing_desk_content',
                                                [
                                                    'textarea_name' => 'dls_writing_desk_content',
                                                    'media_buttons' => true,
                                                    'textarea_rows' => 24,
                                                    'teeny'         => false,
                                                    'quicktags'     => false,
                                                    'tinymce'       => [
                                                        'wpautop'           => true,
                                                        'wp_autoresize_on'  => true,
                                                        'toolbar1'          => 'formatselect,bold,italic,link,blockquote,bullist,numlist,undo,redo,removeformat',
                                                        'toolbar2'         => '',
                                                        'block_formats'    => 'Paragraph=p;Heading 2=h2;Heading 3=h3;Quote=blockquote',
                                                        'menubar'          => false,
                                                        'branding'         => false,
                                                        'content_style'    => 'body{max-width:740px;margin:0 auto;padding:0;font-family:Georgia,serif;font-size:21px;line-height:1.8;color:#201814;}p{margin:0 0 1.2em;}h2{margin:1.8em 0 .7em;font-size:1.8em;line-height:1.2;}h3{margin:1.6em 0 .6em;font-size:1.35em;line-height:1.3;}blockquote{margin:1.8em 0;padding:0 0 0 1.1em;border-left:3px solid #b1865a;color:#5b4432;font-style:italic;}ul,ol{margin:0 0 1.2em 1.4em;}a{color:#7d2010;}',
                                                    ],
                                                ]
                                            );
                                            ?>
                                        </div>
                                        <p class="dls-writing-desk__muted dls-writing-desk__toolbar-note">Use the format menu for paragraph, heading and quote styles.</p>
                                    </div>
                                </div>
                            </div>

	                            <div class="dls-writing-desk__side">
	                                <?php if ($show['publish_box']) : ?>
	                                <div class="dls-writing-desk__block">
	                                    <h2>Publish</h2>
	                                    <div class="dls-writing-desk__muted">This desk stays separate from the normal editor, so rollback stays simple.</div>
                                    <div class="dls-writing-desk__field" style="margin-top:14px;">
                                        <div class="dls-writing-desk__label"><span>Publish Date</span><span>Schedule</span></div>
                                        <input class="dls-writing-desk__input dls-writing-desk__input--datetime" type="datetime-local" name="dls_writing_desk_publish_at" value="<?php echo esc_attr($publish_at_value); ?>">
                                        <div class="dls-writing-desk__muted">Choose a future time, then press Publish to schedule it.</div>
                                    </div>
	                                    <?php if ($show['share_draft'] && $post instanceof WP_Post && $view_url !== '') : ?>
	                                        <div class="dls-writing-desk__field">
                                            <div class="dls-writing-desk__label"><span>Share Draft Link</span><span>Preview</span></div>
                                            <div class="dls-writing-desk__share-row">
                                                <input id="dls-writing-desk-share-link" class="dls-writing-desk__input dls-writing-desk__input--small" type="text" readonly value="<?php echo esc_attr($view_url); ?>">
                                                <button class="dls-writing-desk__button dls-writing-desk__button--copy dls-writing-desk__copy-link" type="button" data-target="#dls-writing-desk-share-link">Copy</button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="dls-writing-desk__footer-actions">
                                        <button class="dls-writing-desk__button dls-writing-desk__button--soft" type="submit" name="dls_writing_desk_submit" value="draft">Save Draft</button>
                                        <button class="dls-writing-desk__button dls-writing-desk__button--accent" type="submit" name="dls_writing_desk_submit" value="publish"><?php echo esc_html($post instanceof WP_Post && $post->post_status === 'publish' ? 'Update Post' : 'Publish'); ?></button>
	                                    </div>
	                                </div>
	                                <?php endif; ?>

                                    <?php
                                    $required_total = 0;
                                    $required_done = 0;
                                    foreach (dls_writing_desk_checklist_items() as $task_key => $task_item) {
                                        if (empty($task_item['required'])) {
                                            continue;
                                        }
                                        $required_total++;
                                        if (!empty($checklist_state[$task_key]) && in_array($task_key, $checklist_done, true)) {
                                            $required_done++;
                                        }
                                    }
                                    $required_percent = $required_total > 0 ? (int) round(($required_done / $required_total) * 100) : 100;
                                    ?>
                                    <div class="dls-writing-desk__block dls-writing-desk__publish-checklist">
                                        <div class="dls-writing-desk__checklist-head">
                                            <div>
                                                <h3>Publishing checklist</h3>
                                                <div class="dls-writing-desk__muted">Required for publishing. Save Draft still works.</div>
                                            </div>
                                            <span class="dls-writing-desk__checklist-score"><?php echo esc_html($required_done . '/' . $required_total); ?></span>
                                        </div>
                                        <div class="dls-writing-desk__checklist-bar"><span class="dls-writing-desk__checklist-fill" style="width:<?php echo esc_attr((string) $required_percent); ?>%;"></span></div>
                                        <div class="dls-writing-desk__task-list">
                                            <?php foreach (dls_writing_desk_checklist_items() as $task_key => $task_item) : ?>
                                                <?php
                                                $task_key = sanitize_key((string) $task_key);
                                                $is_required = !empty($task_item['required']);
                                                $is_ready = !empty($checklist_state[$task_key]);
                                                $is_checked = in_array($task_key, $checklist_done, true);
                                                $task_classes = 'dls-writing-desk__task';
                                                $task_classes .= $is_required ? '' : ' dls-writing-desk__task--optional';
                                                $task_classes .= $is_ready ? ' is-ready' : '';
                                                $task_classes .= ($is_ready && $is_checked) ? ' is-done' : '';
                                                $task_classes .= ($is_required && (!$is_ready || !$is_checked)) ? ' is-missing' : '';
                                                $status = ($is_ready && $is_checked) ? 'Done' : ($is_ready ? 'Ready' : ($is_required ? 'Needed' : 'Optional'));
                                                ?>
                                                <label class="<?php echo esc_attr($task_classes); ?>" data-check-task="<?php echo esc_attr($task_key); ?>" data-required="<?php echo esc_attr($is_required ? '1' : '0'); ?>">
                                                    <input type="checkbox" name="dls_writing_desk_checklist[]" value="<?php echo esc_attr($task_key); ?>" <?php checked($is_checked); ?>>
                                                    <span>
                                                        <strong><?php echo esc_html((string) ($task_item['label'] ?? 'Checklist item')); ?><?php echo $is_required ? '' : ' · optional'; ?></strong>
                                                        <em><?php echo esc_html((string) ($task_item['note'] ?? '')); ?></em>
                                                    </span>
                                                    <span class="dls-writing-desk__task-status"><?php echo esc_html($status); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (current_user_can('manage_options')) : ?>
                                            <p class="dls-writing-desk__muted" style="margin:12px 0 0;">Admin override is active: you can publish even if this is incomplete.</p>
                                        <?php endif; ?>
                                        <p class="dls-writing-desk__publish-warning">Complete and tick all required checklist items before publishing.</p>
                                    </div>

	                                <?php if ($show['language']) : ?>
	                                <div class="dls-writing-desk__block">
                                    <h3>Language</h3>
                                    <div class="dls-writing-desk__field">
                                        <select id="dls-writing-desk-language" class="dls-writing-desk__select" name="dls_writing_desk_language">
                                            <?php foreach ($languages as $slug => $label) : ?>
                                                <option value="<?php echo esc_attr($slug); ?>" <?php selected($current_language, $slug); ?>><?php echo esc_html($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
	                                    </div>
	                                </div>
	                                <?php else : ?>
	                                    <input type="hidden" name="dls_writing_desk_language" value="<?php echo esc_attr($current_language); ?>">
	                                <?php endif; ?>

	                                <?php if ($show['people']) : ?>
	                                <div class="dls-writing-desk__block">
                                    <h3>People</h3>
                                    <div class="dls-writing-desk__field">
                                        <div class="dls-writing-desk__label"><span>Author</span><span>Byline</span></div>
                                        <select id="dls-writing-desk-author" class="dls-writing-desk__select" name="dls_writing_desk_author">
                                            <option value="">Select author</option>
                                            <?php foreach ($author_options as $item) : ?>
                                                <?php
                                                $value = (string) ($item['value'] ?? '');
                                                $label = (string) ($item['label'] ?? '');
                                                $item_language = strtolower(trim((string) ($item['lang'] ?? '')));
                                                if ($value === '' || $label === '') {
                                                    continue;
                                                }
                                                ?>
                                                <option value="<?php echo esc_attr($value); ?>" data-lang="<?php echo esc_attr($item_language); ?>" <?php selected($selected_people['author'], $value); ?>><?php echo esc_html($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="dls-writing-desk__muted">Shown for the selected language, with guest authors included.</div>
                                    </div>
                                    <div class="dls-writing-desk__field">
                                        <div class="dls-writing-desk__label"><span>Editor</span><span>Credit</span></div>
                                        <select id="dls-writing-desk-editor" class="dls-writing-desk__select" name="dls_writing_desk_editor">
                                            <option value="">Select editor</option>
                                            <?php foreach ($editor_options as $item) : ?>
                                                <?php
                                                $value = (string) ($item['value'] ?? '');
                                                $label = (string) ($item['label'] ?? '');
                                                $item_language = strtolower(trim((string) ($item['lang'] ?? '')));
                                                if ($value === '' || $label === '') {
                                                    continue;
                                                }
                                                ?>
                                                <option value="<?php echo esc_attr($value); ?>" data-lang="<?php echo esc_attr($item_language); ?>" <?php selected($selected_people['editor'], $value); ?>><?php echo esc_html($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="dls-writing-desk__muted">Editors and administrators are both available here.</div>
	                                    </div>
	                                </div>
	                                <?php else : ?>
	                                    <input type="hidden" name="dls_writing_desk_author" value="<?php echo esc_attr($selected_people['author']); ?>">
	                                    <input type="hidden" name="dls_writing_desk_editor" value="<?php echo esc_attr($selected_people['editor']); ?>">
	                                <?php endif; ?>

	                                <?php if ($show['categories']) : ?>
	                                <div class="dls-writing-desk__block">
                                    <h3>Categories</h3>
                                    <input class="dls-writing-desk__input dls-writing-desk__input--search" type="search" data-target="#dls-writing-desk-category-list" placeholder="Filter categories">
                                    <?php if (empty($category_terms)) : ?>
                                        <p class="dls-writing-desk__muted dls-writing-desk__taxonomy-empty">No categories yet.</p>
                                    <?php else : ?>
                                        <div id="dls-writing-desk-category-list" class="dls-writing-desk__checklist" data-taxonomy-list="category">
                                            <?php foreach ($category_terms as $entry) : ?>
                                                <?php
                                                $term = $entry['term'] ?? null;
                                                $depth = (int) ($entry['depth'] ?? 0);
                                                $term_lang = strtolower(trim((string) ($entry['lang'] ?? '')));
                                                if (!($term instanceof WP_Term)) {
                                                    continue;
                                                }
                                                $category_groups = implode(' ', dls_writing_desk_category_term_groups((int) $term->term_id));
                                                ?>
                                                <label class="dls-writing-desk__check<?php echo $depth > 0 ? ' dls-writing-desk__check--child' : ''; ?>" data-lang="<?php echo esc_attr($term_lang); ?>" data-search="<?php echo esc_attr(strtolower($term->name)); ?>">
                                                    <input type="checkbox" name="dls_writing_desk_categories[]" value="<?php echo esc_attr((string) $term->term_id); ?>" data-groups="<?php echo esc_attr($category_groups); ?>" <?php checked(in_array((int) $term->term_id, $selected_categories, true)); ?>>
                                                    <span><?php echo esc_html($term->name); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="dls-writing-desk__field" style="margin-top:12px;">
                                        <input class="dls-writing-desk__input dls-writing-desk__input--small" type="text" name="dls_writing_desk_new_terms[category]" placeholder="Add new categories, separated by commas">
                                        <div class="dls-writing-desk__muted">New categories are created in the selected language when you save.</div>
	                                    </div>
	                                </div>
	                                <?php endif; ?>

	                                <?php if ($show['taxonomies']) : ?>
	                                <?php foreach ($extra_taxonomies as $taxonomy_item) : ?>
                                    <?php
                                    $taxonomy = sanitize_key((string) ($taxonomy_item['taxonomy'] ?? ''));
                                    $label = (string) ($taxonomy_item['label'] ?? $taxonomy);
                                    $terms = dls_writing_desk_taxonomy_terms($taxonomy);
                                    $field_name = 'dls_writing_desk_tax_' . str_replace('-', '_', $taxonomy);
                                    $selected_ids = array_map('intval', (array) ($selected_taxonomies[$taxonomy] ?? []));
                                    $list_id = 'dls-writing-desk-taxonomy-' . $taxonomy;
                                    $checklist_group = '';
                                    if (in_array($taxonomy, ['companies', 'company'], true)) {
                                        $checklist_group = 'company';
                                    } elseif (in_array($taxonomy, ['individuals', 'individual', 'people', 'person'], true)) {
                                        $checklist_group = 'individual';
                                    } elseif ($taxonomy === 'post_tag') {
                                        $checklist_group = 'tags';
                                    }
                                    ?>
                                    <div class="dls-writing-desk__block">
                                        <h3><?php echo esc_html($label); ?></h3>
                                        <input class="dls-writing-desk__input dls-writing-desk__input--search" type="search" data-target="#<?php echo esc_attr($list_id); ?>" placeholder="Filter <?php echo esc_attr(strtolower($label)); ?>">
                                        <?php if (empty($terms)) : ?>
                                            <p class="dls-writing-desk__muted dls-writing-desk__taxonomy-empty">No <?php echo esc_html(strtolower($label)); ?> yet.</p>
                                        <?php else : ?>
                                            <div id="<?php echo esc_attr($list_id); ?>" class="dls-writing-desk__checklist" data-taxonomy-list="<?php echo esc_attr($taxonomy); ?>" data-checklist-group="<?php echo esc_attr($checklist_group); ?>">
                                                <?php foreach ($terms as $entry) : ?>
                                                    <?php
                                                    $term = $entry['term'] ?? null;
                                                    $depth = (int) ($entry['depth'] ?? 0);
                                                    $term_lang = strtolower(trim((string) ($entry['lang'] ?? '')));
                                                    if (!($term instanceof WP_Term)) {
                                                        continue;
                                                    }
                                                    ?>
                                                    <label class="dls-writing-desk__check<?php echo $depth > 0 ? ' dls-writing-desk__check--child' : ''; ?>" data-lang="<?php echo esc_attr($term_lang); ?>" data-search="<?php echo esc_attr(strtolower($term->name . ' ' . $term->description)); ?>">
                                                        <input type="checkbox" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr((string) $term->term_id); ?>" <?php checked(in_array((int) $term->term_id, $selected_ids, true)); ?>>
                                                        <span>
                                                            <?php echo esc_html($term->name); ?>
                                                            <?php if (!empty($term->description)) : ?>
                                                                <span class="dls-writing-desk__check-note"><?php echo esc_html(wp_trim_words((string) $term->description, 12)); ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="dls-writing-desk__field" style="margin-top:12px;">
                                            <input class="dls-writing-desk__input dls-writing-desk__input--small dls-writing-desk__new-taxonomy" data-checklist-group="<?php echo esc_attr($checklist_group); ?>" type="text" name="dls_writing_desk_new_terms[<?php echo esc_attr($taxonomy); ?>]" placeholder="Add new <?php echo esc_attr(strtolower($label)); ?>, separated by commas">
                                            <div class="dls-writing-desk__muted">New <?php echo esc_html(strtolower($label)); ?> are created in the selected language when you save.</div>
                                        </div>
	                                    </div>
	                                <?php endforeach; ?>
	                                <?php endif; ?>

	                                <?php if ($show['social'] && !empty($secondary_social_destinations)) : ?>
                                    <div class="dls-writing-desk__block">
                                        <h3>Facebook / LinkedIn</h3>
                                        <?php foreach ($secondary_social_destinations as $destination) : ?>
                                            <?php
                                            $key = (string) ($destination['key'] ?? '');
                                            $settings = is_array($social_settings[$key] ?? null) ? $social_settings[$key] : [];
                                            $social_image_id = absint($settings['image_id'] ?? 0);
                                            $social_image_url = $social_image_id > 0 ? (string) wp_get_attachment_image_url($social_image_id, 'medium') : '';
                                            $preview_id = 'dls-writing-desk-social-preview-' . $key;
                                            $input_id = 'dls-writing-desk-social-image-' . $key;
                                            ?>
                                            <div class="dls-writing-desk__social-card">
                                                <div class="dls-writing-desk__label"><span><?php echo esc_html((string) $destination['name']); ?></span><span><?php echo esc_html((string) $destination['platform_ui']); ?></span></div>
                                                <label class="dls-writing-desk__check" style="margin-bottom:12px;">
                                                    <input type="checkbox" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?>>
                                                    <span>Prepare this destination for publishing</span>
                                                </label>
                                                <textarea class="dls-writing-desk__textarea" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][description]" placeholder="Description for this page only"><?php echo esc_textarea((string) ($settings['description'] ?? '')); ?></textarea>
                                                <div class="dls-writing-desk__thumb" style="margin-top:12px;">
                                                    <input id="<?php echo esc_attr($input_id); ?>" type="hidden" name="dls_writing_desk_social[<?php echo esc_attr($key); ?>][image_id]" value="<?php echo esc_attr((string) $social_image_id); ?>">
                                                    <div id="<?php echo esc_attr($preview_id); ?>" class="dls-writing-desk__social-preview">
                                                        <?php if ($social_image_url !== '') : ?>
                                                            <img src="<?php echo esc_url($social_image_url); ?>" alt="">
                                                        <?php else : ?>
                                                            <span>No custom social image</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="dls-writing-desk__actions">
                                                        <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__select-media" type="button" data-target="#<?php echo esc_attr($input_id); ?>" data-preview="#<?php echo esc_attr($preview_id); ?>" data-placeholder="No custom social image">Choose Image</button>
                                                        <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__remove-media" type="button" data-target="#<?php echo esc_attr($input_id); ?>" data-preview="#<?php echo esc_attr($preview_id); ?>" data-placeholder="No custom social image">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
	                                <?php endif; ?>

	                                <?php if ($show['featured_image']) : ?>
	                                <div class="dls-writing-desk__block">
                                    <h3>Featured Image</h3>
                                    <div class="dls-writing-desk__thumb">
                                        <input id="dls-writing-desk-thumbnail-id" type="hidden" name="dls_writing_desk_thumbnail_id" value="<?php echo esc_attr((string) $thumbnail_id); ?>">
                                        <div class="dls-writing-desk__thumb-preview">
                                            <?php if ($thumbnail_url !== '') : ?>
                                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="">
                                            <?php else : ?>
                                                <span>No featured image yet</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="dls-writing-desk__actions">
                                            <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__select-image" type="button">Choose Image</button>
                                            <button class="dls-writing-desk__button dls-writing-desk__button--soft dls-writing-desk__remove-image" type="button">Remove</button>
	                                    </div>
	                                </div>
	                                <?php else : ?>
	                                    <input type="hidden" name="dls_writing_desk_thumbnail_id" value="<?php echo esc_attr((string) $thumbnail_id); ?>">
	                                <?php endif; ?>
                                </div>
                            </div>
                        </div>

	                        <div class="dls-writing-desk__footer-actions">
	                            <button class="dls-writing-desk__button dls-writing-desk__button--soft" type="submit" name="dls_writing_desk_submit" value="draft">Save Draft</button>
	                            <?php if ($show['publish_box']) : ?>
	                                <button class="dls-writing-desk__button dls-writing-desk__button--accent" type="submit" name="dls_writing_desk_submit" value="publish"><?php echo esc_html($post instanceof WP_Post && $post->post_status === 'publish' ? 'Update Post' : ($post instanceof WP_Post && $post->post_status === 'future' ? 'Update Schedule' : 'Publish')); ?></button>
	                            <?php endif; ?>
	                        </div>
                    </form>
                </section>
            </div>
        </div>
        <?php
    }
}
