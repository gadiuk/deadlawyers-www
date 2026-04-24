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

        return [];
    }
}

if (!function_exists('dls_writing_desk_detect_user_language')) {
    function dls_writing_desk_detect_user_language($user) {
        if (function_exists('dls_na_ui_detect_user_language')) {
            return strtolower(trim((string) dls_na_ui_detect_user_language($user)));
        }

        return '';
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

        if (function_exists('dls_na_ui_detect_guest_author_language')) {
            return strtolower(trim((string) dls_na_ui_detect_guest_author_language($term)));
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

            if ($mode === 'editor') {
                if (!dls_writing_desk_user_has_role($user, ['editor', 'administrator'])) {
                    continue;
                }
            } else {
                if (!dls_writing_desk_user_has_role($user, ['author', 'editor', 'administrator'])) {
                    continue;
                }
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

        if ($mode !== 'editor' && function_exists('dls_native_authors_get_guest_author_terms')) {
            foreach ((array) dls_native_authors_get_guest_author_terms() as $term) {
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

        return wp_date('Y-m-d\TH:i', time() + 3600, wp_timezone());
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

            $user_id = 0;
            if (function_exists('dls_native_authors_extract_user_id_from_term')) {
                $user_id = absint(dls_native_authors_extract_user_id_from_term((int) $term->term_id));
            }

            if ($user_id > 0) {
                $items[] = 'user:' . $user_id;
                continue;
            }

            if (function_exists('dls_native_authors_is_guest_author_term') && dls_native_authors_is_guest_author_term($term)) {
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
            $term = function_exists('dls_native_authors_get_guest_author_term') ? dls_native_authors_get_guest_author_term((int) ($parsed['term_id'] ?? 0)) : null;
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

        return $selected;
    }
}

if (!function_exists('dls_writing_desk_recent_posts')) {
    function dls_writing_desk_recent_posts() {
        $args = [
            'post_type'      => 'post',
            'post_status'    => ['draft', 'pending', 'future', 'publish', 'private'],
            'posts_per_page' => 200,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ];

        if (!current_user_can('edit_others_posts')) {
            $args['author'] = get_current_user_id();
        }

        return get_posts($args);
    }
}

if (!function_exists('dls_writing_desk_hide_admin_notices')) {
    function dls_writing_desk_hide_admin_notices() {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if ($page !== 'dls-writing-desk') {
            return;
        }

        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
    }
}
add_action('in_admin_header', 'dls_writing_desk_hide_admin_notices', 1);

if (!function_exists('dls_writing_desk_notice_message')) {
    function dls_writing_desk_notice_message($code) {
        $code = strtolower(trim((string) $code));

        if ($code === 'saved') {
            return 'Draft saved.';
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

        $has_block_markup = preg_match('/<(p|h[1-6]|ul|ol|blockquote|figure|pre|table|div|section|article)/i', (string) $content) === 1;
        if (!$has_block_markup) {
            $content = wpautop($content);
        }

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

        echo '<style>.dls-writing-desk-frontend-intro{margin:0 0 1.8em}.dls-writing-desk-frontend-kicker{margin:0 0 .7em;color:#8a6133;font:700 12px/1.2 "Helvetica Neue",Arial,sans-serif;letter-spacing:.18em;text-transform:uppercase}.dls-writing-desk-frontend-lead{margin:0 0 1.4em;font-size:1.25em;line-height:1.7;color:#443225}</style>';
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
    }
}
add_action('admin_menu', 'dls_writing_desk_admin_menu');

if (!function_exists('dls_writing_desk_enqueue_assets')) {
    function dls_writing_desk_enqueue_assets($hook) {
        if ($hook !== dls_writing_desk_set_page_hook('')) {
            return;
        }

        wp_enqueue_media();

        wp_register_style('dls-writing-desk', false, [], '1.2.0');
        wp_enqueue_style('dls-writing-desk');
        wp_add_inline_style('dls-writing-desk', '
            body.toplevel_page_dls-writing-desk {
                background:
                    radial-gradient(circle at top left, #f7ebd1 0, #f7ebd1 14%, transparent 40%),
                    linear-gradient(180deg, #f5ecd9 0%, #efe3cd 100%);
                color: #241c15;
            }
            body.toplevel_page_dls-writing-desk #wpadminbar,
            body.toplevel_page_dls-writing-desk #adminmenumain,
            body.toplevel_page_dls-writing-desk #wpfooter,
            body.toplevel_page_dls-writing-desk #screen-meta-links,
            body.toplevel_page_dls-writing-desk .notice:not(.dls-writing-desk__notice),
            body.toplevel_page_dls-writing-desk .update-nag,
            body.toplevel_page_dls-writing-desk .error:not(.dls-writing-desk__notice),
            body.toplevel_page_dls-writing-desk .updated:not(.dls-writing-desk__notice) {
                display: none !important;
            }
            body.toplevel_page_dls-writing-desk #wpcontent,
            body.toplevel_page_dls-writing-desk #wpfooter {
                margin-left: 0;
            }
            body.toplevel_page_dls-writing-desk #wpbody-content {
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
            .dls-writing-desk__panel,
            .dls-writing-desk__editor {
                background: rgba(255,255,255,0.82);
                border: 1px solid rgba(36,28,21,0.12);
                border-radius: 22px;
                box-shadow: 0 18px 40px rgba(66, 44, 14, 0.08);
                backdrop-filter: blur(8px);
            }
            .dls-writing-desk__panel {
                padding: 18px;
                position: sticky;
                top: 24px;
            }
            .dls-writing-desk__editor {
                padding: 24px;
            }
            .dls-writing-desk__topbar {
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
            .dls-writing-desk__title {
                margin: 0;
                font-size: 34px;
                line-height: 1;
                font-weight: 700;
                color: #1f1711;
            }
            .dls-writing-desk__sub {
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
                font: 600 13px/1.3 "Helvetica Neue", Arial, sans-serif;
                text-transform: uppercase;
                letter-spacing: 0.16em;
                color: #8a6133;
                background: rgba(255,250,241,0.9);
            }
            .dls-writing-desk__input--title {
                font: 700 34px/1.1 "Iowan Old Style", "Palatino Linotype", Georgia, serif;
                border-radius: 18px;
                padding: 20px 22px;
            }
            .dls-writing-desk__textarea {
                min-height: 120px;
                resize: vertical;
            }
            .dls-writing-desk__textarea--lead {
                min-height: 110px;
                font: 400 20px/1.6 "Iowan Old Style", "Palatino Linotype", Georgia, serif;
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
            .dls-writing-desk__thumb {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .dls-writing-desk__thumb-preview {
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
            .dls-writing-desk__thumb-preview img {
                width: 100%;
                height: auto;
                display: block;
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
            #wp-dls_writing_desk_content-wrap .wp-editor-container {
                border: 1px solid rgba(49,35,25,0.16);
                border-radius: 18px;
                overflow: hidden;
            }
            #wp-dls_writing_desk_content-wrap .mce-toolbar-grp {
                background: #f7f0e4;
                border-bottom: 1px solid rgba(49,35,25,0.08);
            }
            #dls_writing_desk_content_ifr {
                min-height: 560px !important;
                background: #fffdfa;
            }
            @media (max-width: 1180px) {
                .dls-writing-desk__shell,
                .dls-writing-desk__editor-grid {
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
                .dls-writing-desk__title {
                    font-size: 28px;
                }
                .dls-writing-desk__input--title {
                    font-size: 28px;
                }
                .dls-writing-desk__textarea--lead {
                    font-size: 18px;
                }
                .dls-writing-desk__share-row {
                    flex-direction: column;
                    align-items: stretch;
                }
            }
        ');

        wp_register_script('dls-writing-desk', false, ['jquery'], '1.2.0', true);
        wp_enqueue_script('dls-writing-desk');
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

                function setThumb(url) {
                    var preview = $(".dls-writing-desk__thumb-preview");
                    if (!url) {
                        preview.html("<span>No featured image yet</span>");
                        return;
                    }

                    preview.html("<img src=\"" + url + "\" alt=\"\">");
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

                $(document).on("click", ".dls-writing-desk__select-image", function (event) {
                    event.preventDefault();

                    if (typeof wp === "undefined" || !wp.media) {
                        return;
                    }

                    var frame = wp.media({
                        title: "Choose featured image",
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
                        $("#dls-writing-desk-thumbnail-id").val(data.id ? String(data.id) : "");
                        setThumb(data.url || "");
                    });

                    frame.open();
                });

                $(document).on("click", ".dls-writing-desk__remove-image", function (event) {
                    event.preventDefault();
                    $("#dls-writing-desk-thumbnail-id").val("");
                    setThumb("");
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

                $(document).on("input", "#dls-writing-desk-title, #dls-writing-desk-lead", updateCounts);
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
                });

                if (window.tinyMCE) {
                    $(document).on("tinymce-editor-init", function (event, editor) {
                        if (editor && editor.id === "dls_writing_desk_content") {
                            editor.on("keyup change setcontent", updateCounts);
                            updateCounts();
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
        $status_action = strtolower(trim((string) ($_POST['dls_writing_desk_submit'] ?? 'draft')));
        $requested_status = $status_action === 'publish' ? 'publish' : 'draft';

        if ($post_id > 0 && !current_user_can('edit_post', $post_id)) {
            wp_die('You do not have permission to edit this post.');
        }

        $title = sanitize_text_field((string) ($_POST['dls_writing_desk_title'] ?? ''));
        $kicker = sanitize_text_field((string) ($_POST['dls_writing_desk_kicker'] ?? ''));
        $content = wp_kses_post((string) ($_POST['dls_writing_desk_content'] ?? ''));
        $lead = sanitize_textarea_field((string) ($_POST['dls_writing_desk_lead'] ?? ''));
        $language = dls_writing_desk_normalize_language($_POST['dls_writing_desk_language'] ?? '');
        $thumbnail_id = absint($_POST['dls_writing_desk_thumbnail_id'] ?? 0);
        $categories = array_values(array_filter(array_map('absint', (array) ($_POST['dls_writing_desk_categories'] ?? []))));
        $new_terms = isset($_POST['dls_writing_desk_new_terms']) ? (array) wp_unslash($_POST['dls_writing_desk_new_terms']) : [];
        $publish_at_raw = sanitize_text_field((string) ($_POST['dls_writing_desk_publish_at'] ?? ''));

        $publish_dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $publish_at_raw, wp_timezone());
        $post_date = current_time('mysql');
        $post_date_gmt = current_time('mysql', true);

        if ($publish_dt instanceof DateTimeImmutable) {
            $post_date = $publish_dt->format('Y-m-d H:i:s');
            $post_date_gmt = $publish_dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        }

        $post_status = $requested_status;
        if ($requested_status === 'publish' && $publish_dt instanceof DateTimeImmutable) {
            $now_ts = current_datetime()->getTimestamp();
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

        if ($language !== '') {
            dls_writing_desk_set_post_language($saved_post_id, $language);
        }

        if ($thumbnail_id > 0) {
            set_post_thumbnail($saved_post_id, $thumbnail_id);
        } else {
            delete_post_thumbnail($saved_post_id);
        }

        if (function_exists('dls_native_authors_save_assignments_for_post')) {
            $selected_items = [];
            $role_map = [];

            $author_value = sanitize_text_field((string) ($_POST['dls_writing_desk_author'] ?? ''));
            $editor_value = sanitize_text_field((string) ($_POST['dls_writing_desk_editor'] ?? ''));

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
        }

        $notice = 'saved';
        if ($post_status === 'future') {
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
        $view_url = dls_writing_desk_preview_link($post);
        $publish_at_value = dls_writing_desk_current_datetime_value($post);
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
                    <?php if ($post instanceof WP_Post && $view_url !== '') : ?>
                        <a class="dls-writing-desk__button dls-writing-desk__button--soft" href="<?php echo esc_url($view_url); ?>" target="_blank" rel="noopener">Preview</a>
                    <?php endif; ?>
                    <?php if ($post instanceof WP_Post) : ?>
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
                                <div class="dls-writing-desk__field">
                                    <div class="dls-writing-desk__label"><span>Kicker</span><span>Section / label</span></div>
                                    <input id="dls-writing-desk-kicker" class="dls-writing-desk__input dls-writing-desk__input--kicker" type="text" name="dls_writing_desk_kicker" value="<?php echo esc_attr($kicker); ?>" placeholder="Story label, series, case, or topic">
                                </div>

                                <div class="dls-writing-desk__field">
                                    <div class="dls-writing-desk__label"><span>Title</span><span>Headline</span></div>
                                    <input id="dls-writing-desk-title" class="dls-writing-desk__input dls-writing-desk__input--title" type="text" name="dls_writing_desk_title" value="<?php echo esc_attr($post instanceof WP_Post ? $post->post_title : ''); ?>" placeholder="Write the headline here">
                                </div>

                                <div class="dls-writing-desk__field">
                                    <div class="dls-writing-desk__label"><span>Lead</span><span>Opening lines</span></div>
                                    <textarea id="dls-writing-desk-lead" class="dls-writing-desk__textarea dls-writing-desk__textarea--lead" name="dls_writing_desk_lead" placeholder="Write the lead the way Medium does it: one strong paragraph that pulls the reader into the story."><?php echo esc_textarea($lead); ?></textarea>
                                </div>

                                <div class="dls-writing-desk__field">
                                    <div class="dls-writing-desk__label"><span>Story</span><span>Body</span></div>
                                    <?php
                                    wp_editor(
                                        $post instanceof WP_Post ? $post->post_content : '',
                                        'dls_writing_desk_content',
                                        [
                                            'textarea_name' => 'dls_writing_desk_content',
                                            'media_buttons' => true,
                                            'textarea_rows' => 24,
                                            'teeny'         => false,
                                            'quicktags'     => false,
                                            'tinymce'       => [
                                                'wp_autoresize_on' => true,
                                                'toolbar1'         => 'formatselect,bold,italic,link,blockquote,bullist,numlist,undo,redo,removeformat',
                                                'toolbar2'         => '',
                                                'block_formats'    => 'Paragraph=p;Heading 2=h2;Heading 3=h3;Quote=blockquote',
                                                'menubar'          => false,
                                                'branding'         => false,
                                                'content_style'    => 'body{max-width:740px;margin:0 auto;padding:28px 34px;font-family:Georgia,serif;font-size:21px;line-height:1.8;color:#201814;}p{margin:0 0 1.2em;}h2{margin:1.8em 0 .7em;font-size:1.8em;line-height:1.2;}h3{margin:1.6em 0 .6em;font-size:1.35em;line-height:1.3;}blockquote{margin:1.8em 0;padding:0 0 0 1.1em;border-left:3px solid #b1865a;color:#5b4432;font-style:italic;}ul,ol{margin:0 0 1.2em 1.4em;}a{color:#7d2010;}',
                                            ],
                                        ]
                                    );
                                    ?>
                                    <p class="dls-writing-desk__muted dls-writing-desk__toolbar-note">Use the format menu for paragraph, heading and quote styles. The code tab is removed here on purpose.</p>
                                </div>
                            </div>

                            <div class="dls-writing-desk__side">
                                <div class="dls-writing-desk__block">
                                    <h2>Publish</h2>
                                    <div class="dls-writing-desk__muted">This desk stays separate from the normal editor, so rollback stays simple.</div>
                                    <div class="dls-writing-desk__field" style="margin-top:14px;">
                                        <div class="dls-writing-desk__label"><span>Publish Date</span><span>Schedule</span></div>
                                        <input class="dls-writing-desk__input dls-writing-desk__input--datetime" type="datetime-local" name="dls_writing_desk_publish_at" value="<?php echo esc_attr($publish_at_value); ?>">
                                        <div class="dls-writing-desk__muted">Choose a future time, then press Publish to schedule it.</div>
                                    </div>
                                    <?php if ($post instanceof WP_Post && $view_url !== '') : ?>
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
                                                ?>
                                                <label class="dls-writing-desk__check<?php echo $depth > 0 ? ' dls-writing-desk__check--child' : ''; ?>" data-lang="<?php echo esc_attr($term_lang); ?>" data-search="<?php echo esc_attr(strtolower($term->name)); ?>">
                                                    <input type="checkbox" name="dls_writing_desk_categories[]" value="<?php echo esc_attr((string) $term->term_id); ?>" <?php checked(in_array((int) $term->term_id, $selected_categories, true)); ?>>
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

                                <?php foreach ($extra_taxonomies as $taxonomy_item) : ?>
                                    <?php
                                    $taxonomy = sanitize_key((string) ($taxonomy_item['taxonomy'] ?? ''));
                                    $label = (string) ($taxonomy_item['label'] ?? $taxonomy);
                                    $terms = dls_writing_desk_taxonomy_terms($taxonomy);
                                    $field_name = 'dls_writing_desk_tax_' . str_replace('-', '_', $taxonomy);
                                    $selected_ids = array_map('intval', (array) ($selected_taxonomies[$taxonomy] ?? []));
                                    $list_id = 'dls-writing-desk-taxonomy-' . $taxonomy;
                                    ?>
                                    <div class="dls-writing-desk__block">
                                        <h3><?php echo esc_html($label); ?></h3>
                                        <input class="dls-writing-desk__input dls-writing-desk__input--search" type="search" data-target="#<?php echo esc_attr($list_id); ?>" placeholder="Filter <?php echo esc_attr(strtolower($label)); ?>">
                                        <?php if (empty($terms)) : ?>
                                            <p class="dls-writing-desk__muted dls-writing-desk__taxonomy-empty">No <?php echo esc_html(strtolower($label)); ?> yet.</p>
                                        <?php else : ?>
                                            <div id="<?php echo esc_attr($list_id); ?>" class="dls-writing-desk__checklist" data-taxonomy-list="<?php echo esc_attr($taxonomy); ?>">
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
                                            <input class="dls-writing-desk__input dls-writing-desk__input--small" type="text" name="dls_writing_desk_new_terms[<?php echo esc_attr($taxonomy); ?>]" placeholder="Add new <?php echo esc_attr(strtolower($label)); ?>, separated by commas">
                                            <div class="dls-writing-desk__muted">New <?php echo esc_html(strtolower($label)); ?> are created in the selected language when you save.</div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

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
                                </div>
                            </div>
                        </div>

                        <div class="dls-writing-desk__footer-actions">
                            <button class="dls-writing-desk__button dls-writing-desk__button--soft" type="submit" name="dls_writing_desk_submit" value="draft">Save Draft</button>
                            <button class="dls-writing-desk__button dls-writing-desk__button--accent" type="submit" name="dls_writing_desk_submit" value="publish"><?php echo esc_html($post instanceof WP_Post && $post->post_status === 'publish' ? 'Update Post' : ($post instanceof WP_Post && $post->post_status === 'future' ? 'Update Schedule' : 'Publish')); ?></button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
        <?php
    }
}
