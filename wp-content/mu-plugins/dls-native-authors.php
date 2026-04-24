<?php
/**
 * Plugin Name: DLS Native Authors
 * Description: Native multi-author replacement with migration from PublishPress Authors.
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

// Emergency guard: skip heavy author-bridge logic on WordPress user deletion requests.
if (is_admin()) {
    $script = isset($_SERVER['SCRIPT_NAME']) ? basename((string) $_SERVER['SCRIPT_NAME']) : '';
    $action = isset($_REQUEST['action']) ? sanitize_key((string) $_REQUEST['action']) : '';

    if ($script === 'users.php' && in_array($action, ['delete', 'delete-selected', 'dodelete'], true)) {
        return;
    }
}

if (!function_exists('dls_native_authors_allowed_roles')) {
    function dls_native_authors_allowed_roles() {
        return apply_filters('dls_native_authors_allowed_roles', ['administrator', 'editor', 'author', 'contributor']);
    }
}

if (!function_exists('dls_native_authors_publishpress_available')) {
    function dls_native_authors_publishpress_available() {
        return class_exists('\\MultipleAuthors\\Classes\\Objects\\Author');
    }
}

if (!function_exists('dls_native_authors_get_known_author_user_ids')) {
    function dls_native_authors_get_known_author_user_ids() {
        global $wpdb;

        $cache_key = 'dls_native_authors_known_user_ids_v1';
        $cached = wp_cache_get($cache_key, 'dls_native_authors');

        if (is_array($cached)) {
            return array_values(array_unique(array_map('absint', $cached)));
        }

        $ids = [];

        $meta_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
                '_dls_post_author'
            )
        );

        if (is_array($meta_ids)) {
            foreach ($meta_ids as $value) {
                $id = absint($value);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $term_user_ids = $wpdb->get_col(
            "
            SELECT DISTINCT tm.meta_value
            FROM {$wpdb->term_taxonomy} tt
            INNER JOIN {$wpdb->termmeta} tm
                ON tm.term_id = tt.term_id
            WHERE tt.taxonomy = 'author'
              AND tm.meta_key = 'user_id'
            "
        );

        if (is_array($term_user_ids)) {
            foreach ($term_user_ids as $value) {
                $id = absint($value);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $legacy_meta_keys = $wpdb->get_results(
            "
            SELECT DISTINCT tm.term_id, tm.meta_key
            FROM {$wpdb->term_taxonomy} tt
            INNER JOIN {$wpdb->termmeta} tm
                ON tm.term_id = tt.term_id
            WHERE tt.taxonomy = 'author'
              AND tm.meta_key LIKE 'user_id_%'
            "
        );

        if (is_array($legacy_meta_keys)) {
            foreach ($legacy_meta_keys as $row) {
                $meta_key = isset($row->meta_key) ? (string) $row->meta_key : '';
                if (strpos($meta_key, 'user_id_') !== 0) {
                    continue;
                }

                $id = absint(substr($meta_key, 8));
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $ids = array_values(array_unique(array_map('absint', $ids)));

        wp_cache_set($cache_key, $ids, 'dls_native_authors', 300);

        return $ids;
    }
}

if (!function_exists('dls_native_authors_get_users')) {
    function dls_native_authors_get_users() {
        $users = get_users([
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'number'  => 5000,
        ]);

        if (!is_array($users)) {
            return [];
        }

        $allowed_roles = dls_native_authors_allowed_roles();
        $allowed_roles = is_array($allowed_roles) ? array_map('strval', $allowed_roles) : [];

        if (!in_array('ppma_guest_author', $allowed_roles, true)) {
            $allowed_roles[] = 'ppma_guest_author';
        }

        $known_user_ids = dls_native_authors_get_known_author_user_ids();
        $known_lookup = array_fill_keys(array_map('absint', $known_user_ids), true);

        $filtered = [];

        foreach ($users as $user) {
            if (!($user instanceof WP_User)) {
                $user_id = absint(is_object($user) ? ($user->ID ?? 0) : 0);
                $user = $user_id > 0 ? get_userdata($user_id) : false;
            }

            if (!($user instanceof WP_User)) {
                continue;
            }

            $user_id = (int) $user->ID;
            $roles = (array) $user->roles;

            $has_allowed_role = empty($allowed_roles) ? true : !empty(array_intersect($allowed_roles, $roles));
            $can_edit = user_can($user, 'edit_posts');
            $can_manage = user_can($user, 'manage_options') || user_can($user, 'edit_others_posts');
            $is_known_author = isset($known_lookup[$user_id]);

            if (!$can_edit && !$can_manage && !$has_allowed_role && !$is_known_author) {
                continue;
            }

            $filtered[] = $user;
        }

        return $filtered;
    }
}

if (!function_exists('dls_native_authors_sanitize_ids')) {
    function dls_native_authors_sanitize_ids($ids) {
        $ids = array_values(array_filter(array_map('absint', (array) $ids)));
        $out = [];

        foreach ($ids as $id) {
            if ($id < 1 || !get_userdata($id)) {
                continue;
            }
            if (!in_array($id, $out, true)) {
                $out[] = $id;
            }
        }

        return $out;
    }
}

if (!function_exists('dls_native_authors_get_stored_ids')) {
    function dls_native_authors_get_stored_ids($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return [];
        }

        $stored = get_post_meta($post_id, '_dls_post_authors', true);
        if (is_array($stored)) {
            return dls_native_authors_sanitize_ids($stored);
        }

        $stored_multi = get_post_meta($post_id, '_dls_post_author', false);
        if (!empty($stored_multi) && is_array($stored_multi)) {
            return dls_native_authors_sanitize_ids($stored_multi);
        }

        return [];
    }
}

if (!function_exists('dls_native_authors_store_ids')) {
    function dls_native_authors_store_ids($post_id, $ids) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return;
        }

        $ids = dls_native_authors_sanitize_ids($ids);

        if (empty($ids)) {
            delete_post_meta($post_id, '_dls_post_authors');
            delete_post_meta($post_id, '_dls_post_author');
            return;
        }

        update_post_meta($post_id, '_dls_post_authors', $ids);
        delete_post_meta($post_id, '_dls_post_author');

        foreach ($ids as $id) {
            add_post_meta($post_id, '_dls_post_author', $id, false);
        }
    }
}

if (!function_exists('dls_native_authors_get_post_ids')) {
    function dls_native_authors_get_post_ids($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return [];
        }

        $stored = dls_native_authors_get_stored_ids($post_id);
        if (!empty($stored)) {
            return $stored;
        }

        return [];
    }
}

if (!function_exists('dls_native_authors_get_post_users')) {
    function dls_native_authors_get_post_users($post_id) {
        $users = [];

        foreach (dls_native_authors_get_post_ids($post_id) as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $users[] = $user;
            }
        }

        return $users;
    }
}

if (!function_exists('dls_native_authors_render_links')) {
    function dls_native_authors_render_links($post_id, $separator = ', ') {
        $links = [];

        if (function_exists('dls_native_authors_get_post_people_with_roles')) {
            foreach (dls_native_authors_get_post_people_with_roles($post_id) as $item) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $url = trim((string) ($item['url'] ?? ''));
                if ($url !== '') {
                    $links[] = sprintf(
                        '<a class="url fn n" href="%1$s">%2$s</a>',
                        esc_url($url),
                        esc_html($name)
                    );
                    continue;
                }

                $links[] = esc_html($name);
            }

            return implode($separator, $links);
        }

        foreach (dls_native_authors_get_post_users($post_id) as $user) {
            $links[] = sprintf(
                '<a class="url fn n" href="%1$s">%2$s</a>',
                esc_url(get_author_posts_url($user->ID)),
                esc_html($user->display_name)
            );
        }

        return implode($separator, $links);
    }
}

if (!function_exists('dls_native_authors_render_plain_names')) {
    function dls_native_authors_render_plain_names($post_id, $separator = ', ') {
        $names = [];

        if (function_exists('dls_native_authors_get_post_people_with_roles')) {
            foreach (dls_native_authors_get_post_people_with_roles($post_id) as $item) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name !== '') {
                    $names[] = $name;
                }
            }

            return implode($separator, $names);
        }

        foreach (dls_native_authors_get_post_users($post_id) as $user) {
            $names[] = $user->display_name;
        }

        return implode($separator, $names);
    }
}

if (!function_exists('dls_native_authors_meta_box_post_types')) {
    function dls_native_authors_meta_box_post_types() {
        $types = [];

        foreach (get_post_types(['public' => true], 'names') as $post_type) {
            if (!post_type_supports($post_type, 'author')) {
                continue;
            }

            if (in_array($post_type, ['attachment', 'revision', 'nav_menu_item'], true)) {
                continue;
            }

            $types[] = $post_type;
        }

        return $types;
    }
}

if (!function_exists('dls_native_authors_render_metabox')) {
    function dls_native_authors_render_metabox($post) {
        if (!($post instanceof WP_Post)) {
            return;
        }

        wp_nonce_field('dls_native_authors_save', 'dls_native_authors_nonce');

        $selected = dls_native_authors_get_stored_ids($post->ID);
        $primary_id = absint(get_post_field('post_author', $post->ID));

        if ($primary_id > 0 && !in_array($primary_id, $selected, true)) {
            array_unshift($selected, $primary_id);
        }

        $users = dls_native_authors_get_users();

        echo '<p style="margin:0 0 10px">Choose one or more authors/editors for this post. Each selected user gets a linked author page.</p>';
        echo '<div style="max-height:260px; overflow:auto; border:1px solid #dcdcde; padding:8px; background:#fff">';

        foreach ($users as $user) {
            $role = !empty($user->roles[0]) ? (string) $user->roles[0] : 'user';
            $is_checked = in_array((int) $user->ID, $selected, true) ? ' checked' : '';

            echo '<label style="display:flex; align-items:center; gap:8px; margin:0 0 7px">';
            echo '<input type="checkbox" name="dls_post_authors[]" value="' . esc_attr((int) $user->ID) . '"' . $is_checked . '>';
            echo '<span>' . esc_html($user->display_name) . '</span>';
            echo '<code style="opacity:.7">' . esc_html($role) . '</code>';
            echo '</label>';
        }

        echo '</div>';
    }
}

add_action('add_meta_boxes', function () {
    // Keep the legacy checkbox UI dormant once the newer metabox exists.
    if (function_exists('dls_native_authors_render_metabox_v2')) {
        return;
    }

    foreach (dls_native_authors_meta_box_post_types() as $post_type) {
        add_meta_box(
            'dls-native-authors-box',
            'DLS Authors',
            'dls_native_authors_render_metabox',
            $post_type,
            'side',
            'high'
        );
    }
});

add_action('save_post', function ($post_id, $post) {
    // Legacy saver for the old checkbox UI; newer handlers take over below.
    if (function_exists('dls_native_authors_render_metabox_v2')) {
        return;
    }

    if (!($post instanceof WP_Post)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    if (!isset($_POST['dls_native_authors_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dls_native_authors_nonce'])), 'dls_native_authors_save')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $selected = isset($_POST['dls_post_authors']) ? dls_native_authors_sanitize_ids(wp_unslash((array) $_POST['dls_post_authors'])) : [];
    dls_native_authors_store_ids($post_id, $selected);
}, 20, 2);

add_filter('kadence_author_meta_output', function ($output) {
    $post_id = get_the_ID();

    if (!$post_id || get_post_type($post_id) !== 'post') {
        return $output;
    }

    $links = dls_native_authors_render_links($post_id);
    if ($links === '') {
        return $output;
    }

    return '<span class="posted-by"><span class="meta-label">By</span><span class="author vcard">' . $links . '</span></span>';
}, 999);

add_filter('the_author_posts_link', function ($author_link) {
    if (is_admin()) {
        return $author_link;
    }

    $post = get_post();
    if (!($post instanceof WP_Post)) {
        return $author_link;
    }

    if ($post->post_type !== 'post') {
        return $author_link;
    }

    $links = dls_native_authors_render_links($post->ID);
    return $links !== '' ? $links : $author_link;
}, 999);

add_filter('the_author', function ($display_name) {
    if (is_admin()) {
        return $display_name;
    }

    $post = get_post();
    if (!($post instanceof WP_Post) || $post->post_type !== 'post') {
        return $display_name;
    }

    $names = dls_native_authors_render_plain_names($post->ID);
    return $names !== '' ? $names : $display_name;
}, 999);

add_action('pre_get_posts', function ($query) {
    if (!($query instanceof WP_Query) || is_admin() || !$query->is_main_query() || !$query->is_author()) {
        return;
    }

    if (dls_native_authors_publishpress_available()) {
        return;
    }

    $author_id = absint($query->get('author'));
    $author_slug = '';
    $author_display_name = '';

    if ($author_id < 1) {
        $slug = (string) $query->get('author_name');
        if ($slug !== '') {
            $author_slug = sanitize_title($slug);
            $user = get_user_by('slug', $author_slug);
            if ($user instanceof WP_User) {
                $author_id = (int) $user->ID;
                $author_display_name = (string) $user->display_name;
                $author_slug = (string) $user->user_nicename;
            }
        }
    } else {
        $user = get_userdata($author_id);
        if ($user instanceof WP_User) {
            $author_display_name = (string) $user->display_name;
            $author_slug = (string) $user->user_nicename;
        }
    }

    if ($author_id < 1) {
        return;
    }

    global $wpdb;

    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "
            SELECT DISTINCT p.ID
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm
              ON pm.post_id = p.ID
             AND pm.meta_key = %s
            WHERE p.post_type = %s
              AND p.post_status = %s
              AND (
                    p.post_author = %d
                 OR pm.meta_value = %d
              )
            ",
            '_dls_post_author',
            'post',
            'publish',
            $author_id,
            $author_id
        )
    );

    // Preserve resolved author context for custom template.
    $query->set('dls_author_user_id', $author_id);
    if ($author_slug !== '') {
        $query->set('dls_author_slug', $author_slug);
    }
    if ($author_display_name !== '') {
        $query->set('dls_author_display_name', $author_display_name);
    }

    $query->set('author', 0);
    $query->set('author_name', '');
    $query->set('post_type', 'post');
    $query->set('post__in', !empty($ids) ? array_map('absint', $ids) : [0]);
    $query->set('orderby', 'date');
    $query->set('order', 'DESC');
}, 99);

add_action('wp', function () {
    if (is_admin()) {
        return;
    }

    if (dls_native_authors_publishpress_available()) {
        return;
    }

    global $wp_query, $authordata;

    if (!($wp_query instanceof WP_Query) || !$wp_query->is_main_query()) {
        return;
    }

    $author_id = absint($wp_query->get('dls_author_user_id'));
    if ($author_id < 1) {
        $author_id = absint($wp_query->get('author'));
    }

    if ($author_id < 1) {
        return;
    }

    $user = get_userdata($author_id);
    if (!($user instanceof WP_User)) {
        return;
    }

    $wp_query->queried_object = $user;
    $wp_query->queried_object_id = (int) $user->ID;
    $wp_query->is_author = true;
    $wp_query->is_archive = true;
    $wp_query->is_home = false;
    $wp_query->is_404 = false;
    $wp_query->set('author', (int) $user->ID);
    $wp_query->set('author_name', (string) $user->user_nicename);

    $authordata = $user;
}, 20);

if (!function_exists('dls_native_authors_extract_user_id_from_term')) {
    function dls_native_authors_extract_user_id_from_term($term_id) {
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

if (!function_exists('dls_native_authors_create_user_from_term')) {
    function dls_native_authors_create_user_from_term($term) {
        if (!($term instanceof WP_Term)) {
            return 0;
        }

        $base_login = sanitize_user($term->slug ?: $term->name, true);
        if ($base_login === '') {
            $base_login = 'author_' . (int) $term->term_id;
        }

        $login = $base_login;
        $suffix = 1;
        while (username_exists($login)) {
            $login = $base_login . '_' . $suffix;
            $suffix++;
        }

        $term_email = sanitize_email((string) get_term_meta($term->term_id, 'user_email', true));
        if ($term_email !== '' && email_exists($term_email)) {
            $existing = get_user_by('email', $term_email);
            if ($existing instanceof WP_User) {
                update_term_meta($term->term_id, 'user_id', (int) $existing->ID);
                return (int) $existing->ID;
            }
        }

        if ($term_email === '' || email_exists($term_email)) {
            $term_email = $login . '@deadlawyers.local';
        }

        $display_name = trim((string) $term->name) !== '' ? (string) $term->name : $login;

        $new_user_id = wp_insert_user([
            'user_login'   => $login,
            'user_pass'    => wp_generate_password(24, true, true),
            'display_name' => $display_name,
            'nickname'     => $display_name,
            'first_name'   => (string) get_term_meta($term->term_id, 'first_name', true),
            'last_name'    => (string) get_term_meta($term->term_id, 'last_name', true),
            'description'  => (string) get_term_meta($term->term_id, 'description', true),
            'role'         => 'author',
            'user_email'   => $term_email,
            'user_url'     => (string) get_term_meta($term->term_id, 'user_url', true),
        ]);

        if (is_wp_error($new_user_id)) {
            return 0;
        }

        update_term_meta($term->term_id, 'user_id', (int) $new_user_id);

        return (int) $new_user_id;
    }
}

if (!function_exists('dls_native_authors_collect_pp_user_ids_for_post')) {
    function dls_native_authors_collect_pp_user_ids_for_post($post_id, &$created_users = 0, &$unmapped_terms = []) {
        $post_id = absint($post_id);
        if ($post_id < 1 || !taxonomy_exists('author')) {
            return [];
        }

        $terms = wp_get_post_terms($post_id, 'author');
        if (empty($terms) || is_wp_error($terms)) {
            return [];
        }

        $ids = [];

        foreach ($terms as $term) {
            if (!($term instanceof WP_Term)) {
                continue;
            }

            $user_id = dls_native_authors_extract_user_id_from_term($term->term_id);

            if ($user_id < 1) {
                $user_id = dls_native_authors_create_user_from_term($term);
                if ($user_id > 0) {
                    $created_users++;
                }
            }

            if ($user_id > 0) {
                $ids[] = $user_id;
            } else {
                $unmapped_terms[] = $term->name . ' (#' . (int) $term->term_id . ')';
            }
        }

        return dls_native_authors_sanitize_ids($ids);
    }
}

if (!function_exists('dls_native_authors_run_publishpress_migration')) {
    function dls_native_authors_run_publishpress_migration($force = false) {
        $already = get_option('dls_native_authors_migrated_v1', []);
        if (!$force && !empty($already['completed_at'])) {
            return $already;
        }

        global $wpdb;

        $stats = [
            'scanned_posts'  => 0,
            'updated_posts'  => 0,
            'created_users'  => 0,
            'unmapped_terms' => [],
            'completed_at'   => current_time('mysql'),
        ];

        if (!taxonomy_exists('author')) {
            $stats['note'] = 'author taxonomy not found';
            update_option('dls_native_authors_migrated_v1', $stats, false);
            return $stats;
        }

        $post_ids = $wpdb->get_col(
            "
            SELECT DISTINCT tr.object_id
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt
              ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tt.taxonomy = 'author'
            "
        );

        if (empty($post_ids)) {
            update_option('dls_native_authors_migrated_v1', $stats, false);
            return $stats;
        }

        foreach ($post_ids as $post_id_raw) {
            $post_id = absint($post_id_raw);
            if ($post_id < 1 || get_post_type($post_id) !== 'post') {
                continue;
            }

            $stats['scanned_posts']++;

            $collected = dls_native_authors_collect_pp_user_ids_for_post($post_id, $stats['created_users'], $stats['unmapped_terms']);
            $existing = dls_native_authors_get_stored_ids($post_id);
            $merged = dls_native_authors_sanitize_ids(array_merge($existing, $collected));

            if (!empty($merged) && $merged !== $existing) {
                dls_native_authors_store_ids($post_id, $merged);
                $stats['updated_posts']++;
            }
        }

        $stats['unmapped_terms'] = array_values(array_unique($stats['unmapped_terms']));
        update_option('dls_native_authors_migrated_v1', $stats, false);

        return $stats;
    }
}

add_action('admin_init', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['dls_native_authors_migrate']) && $_GET['dls_native_authors_migrate'] === '1') {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'dls_native_authors_migrate')) {
            return;
        }

        dls_native_authors_run_publishpress_migration(true);
        wp_safe_redirect(remove_query_arg(['dls_native_authors_migrate', '_wpnonce']));
        exit;
    }

    dls_native_authors_run_publishpress_migration(false);
});

add_action('admin_menu', function () {
    add_management_page(
        'DLS Native Authors',
        'DLS Native Authors',
        'manage_options',
        'dls-native-authors',
        function () {
            if (!current_user_can('manage_options')) {
                return;
            }

            $stats = get_option('dls_native_authors_migrated_v1', []);
            $rerun_url = wp_nonce_url(
                add_query_arg([
                    'page' => 'dls-native-authors',
                    'dls_native_authors_migrate' => '1',
                ], admin_url('tools.php')),
                'dls_native_authors_migrate'
            );

            echo '<div class="wrap">';
            echo '<h1>DLS Native Authors</h1>';
            echo '<p>PublishPress Authors migration and native multi-author controls.</p>';
            echo '<p><a class="button button-primary" href="' . esc_url($rerun_url) . '">Run Migration Again</a></p>';

            echo '<table class="widefat striped" style="max-width:860px">';
            echo '<tbody>';
            echo '<tr><th>Completed</th><td>' . esc_html((string) ($stats['completed_at'] ?? 'not yet')) . '</td></tr>';
            echo '<tr><th>Scanned posts</th><td>' . esc_html((string) ($stats['scanned_posts'] ?? 0)) . '</td></tr>';
            echo '<tr><th>Updated posts</th><td>' . esc_html((string) ($stats['updated_posts'] ?? 0)) . '</td></tr>';
            echo '<tr><th>Created users</th><td>' . esc_html((string) ($stats['created_users'] ?? 0)) . '</td></tr>';
            echo '</tbody>';
            echo '</table>';

            if (!empty($stats['unmapped_terms']) && is_array($stats['unmapped_terms'])) {
                echo '<h2 style="margin-top:24px">Unmapped Terms (need manual cleanup)</h2>';
                echo '<ul style="max-width:860px">';
                foreach (array_slice($stats['unmapped_terms'], 0, 80) as $term_label) {
                    echo '<li>' . esc_html((string) $term_label) . '</li>';
                }
                echo '</ul>';
            }

            echo '</div>';
        }
    );
});

if (!function_exists('dls_native_authors_is_author_request')) {
    function dls_native_authors_is_author_request() {
        return is_author() || (bool) get_query_var('author_name') || (int) get_query_var('author') > 0;
    }
}

add_action('wp_enqueue_scripts', function () {
    if (!dls_native_authors_is_author_request()) {
        return;
    }

    $css_path = WP_CONTENT_DIR . '/themes/kadence-dls-child/assets/css/dls-core-shell.css';
    $css_url  = content_url('/themes/kadence-dls-child/assets/css/dls-core-shell.css');

    if (!file_exists($css_path)) {
        return;
    }

    wp_enqueue_style('dls-native-authors-shell', $css_url, [], filemtime($css_path));
}, 999);

add_filter('template_include', function ($template) {
    if (!dls_native_authors_is_author_request()) {
        return $template;
    }

    $forced = WP_CONTENT_DIR . '/themes/kadence-dls-child/author.php';

    if (file_exists($forced)) {
        return $forced;
    }

    return $template;
}, PHP_INT_MAX);


if (!function_exists('dls_native_authors_get_legacy_term_row_by_id')) {
    function dls_native_authors_get_legacy_term_row_by_id($term_id) {
        global $wpdb;

        $term_id = absint($term_id);
        if ($term_id < 1) {
            return null;
        }

        $sql = $wpdb->prepare(
            "
            SELECT t.term_id, t.slug, t.name
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt
                ON tt.term_id = t.term_id
            WHERE tt.taxonomy = %s
              AND t.term_id = %d
            LIMIT 1
            ",
            'author',
            $term_id
        );

        $row = $wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }
}
if (!function_exists('dls_native_authors_get_legacy_term_row_by_slug')) {
    function dls_native_authors_get_legacy_term_row_by_slug($slug) {
        global $wpdb;

        $slug = sanitize_title((string) $slug);
        if ($slug === '') {
            return null;
        }

        $sql = $wpdb->prepare(
            "
            SELECT t.term_id, t.slug, t.name
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt
                ON tt.term_id = t.term_id
            WHERE tt.taxonomy = %s
              AND t.slug = %s
            LIMIT 1
            ",
            'author',
            $slug
        );

        $row = $wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }
}

if (!function_exists('dls_native_authors_get_legacy_term_rows_for_post')) {
    function dls_native_authors_get_legacy_term_rows_for_post($post_id) {
        global $wpdb;

        $post_id = absint($post_id);
        if ($post_id < 1) {
            return [];
        }

        $sql = $wpdb->prepare(
            "
            SELECT t.term_id, t.slug, t.name
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt
                ON tt.term_taxonomy_id = tr.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t
                ON t.term_id = tt.term_id
            WHERE tt.taxonomy = %s
              AND tr.object_id = %d
            ",
            'author',
            $post_id
        );

        $rows = $wpdb->get_results($sql, ARRAY_A);

        return is_array($rows) ? $rows : [];
    }
}

if (!function_exists('dls_native_authors_extract_user_id_from_legacy_term')) {
    function dls_native_authors_extract_user_id_from_legacy_term($term_id) {
        global $wpdb;

        $term_id = absint($term_id);
        if ($term_id < 1) {
            return 0;
        }

        $direct = absint(get_term_meta($term_id, 'user_id', true));
        if ($direct > 0 && get_userdata($direct)) {
            return $direct;
        }

        $meta_key = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT meta_key
                FROM {$wpdb->termmeta}
                WHERE term_id = %d
                  AND meta_key LIKE %s
                ORDER BY meta_id ASC
                LIMIT 1
                ",
                $term_id,
                'user_id_%'
            )
        );

        if (is_string($meta_key) && strpos($meta_key, 'user_id_') === 0) {
            $candidate = absint(substr($meta_key, 8));
            if ($candidate > 0 && get_userdata($candidate)) {
                update_term_meta($term_id, 'user_id', $candidate);
                return $candidate;
            }
        }

        return 0;
    }
}

if (!function_exists('dls_native_authors_create_user_from_legacy_term')) {
    function dls_native_authors_create_user_from_legacy_term($term_row) {
        if (!is_array($term_row)) {
            return 0;
        }

        $term_id = absint($term_row['term_id'] ?? 0);
        if ($term_id < 1) {
            return 0;
        }

        $slug = sanitize_title((string) ($term_row['slug'] ?? ''));
        $name = trim((string) ($term_row['name'] ?? ''));

        if ($slug !== '') {
            $by_slug = get_user_by('slug', $slug);
            if ($by_slug instanceof WP_User) {
                update_term_meta($term_id, 'user_id', (int) $by_slug->ID);
                return (int) $by_slug->ID;
            }
        }

        $base_login = sanitize_user($slug !== '' ? $slug : $name, true);
        if ($base_login === '') {
            $base_login = 'author_' . $term_id;
        }

        $login = $base_login;
        $suffix = 1;

        while (username_exists($login)) {
            $login = $base_login . '_' . $suffix;
            $suffix++;
        }

        $term_email = sanitize_email((string) get_term_meta($term_id, 'user_email', true));
        if ($term_email !== '' && email_exists($term_email)) {
            $existing = get_user_by('email', $term_email);
            if ($existing instanceof WP_User) {
                update_term_meta($term_id, 'user_id', (int) $existing->ID);
                return (int) $existing->ID;
            }
        }

        if ($term_email === '' || email_exists($term_email)) {
            $term_email = $login . '@deadlawyers.local';
        }

        $display_name = $name !== '' ? $name : $login;

        $user_data = [
            'user_login'   => $login,
            'user_pass'    => wp_generate_password(24, true, true),
            'display_name' => $display_name,
            'nickname'     => $display_name,
            'first_name'   => (string) get_term_meta($term_id, 'first_name', true),
            'last_name'    => (string) get_term_meta($term_id, 'last_name', true),
            'description'  => (string) get_term_meta($term_id, 'description', true),
            'role'         => 'author',
            'user_email'   => $term_email,
            'user_url'     => (string) get_term_meta($term_id, 'user_url', true),
        ];

        if ($slug !== '') {
            $user_data['user_nicename'] = $slug;
        }

        $new_user_id = wp_insert_user($user_data);

        if (is_wp_error($new_user_id)) {
            return 0;
        }

        update_term_meta($term_id, 'user_id', (int) $new_user_id);

        return (int) $new_user_id;
    }
}

if (!function_exists('dls_native_authors_resolve_legacy_term_user_id')) {
    function dls_native_authors_resolve_legacy_term_user_id($term_row, &$created_users = 0) {
        $term_id = absint(is_array($term_row) ? ($term_row['term_id'] ?? 0) : 0);
        if ($term_id < 1) {
            return 0;
        }

        $user_id = dls_native_authors_extract_user_id_from_legacy_term($term_id);

        if ($user_id < 1) {
            $user_id = dls_native_authors_create_user_from_legacy_term($term_row);
            if ($user_id > 0) {
                $created_users++;
            }
        }

        return $user_id;
    }
}

if (!function_exists('dls_native_authors_collect_legacy_user_ids_for_post')) {
    function dls_native_authors_collect_legacy_user_ids_for_post($post_id, &$created_users = 0, &$unmapped_terms = []) {
        $rows = dls_native_authors_get_legacy_term_rows_for_post($post_id);
        if (empty($rows)) {
            return [];
        }

        $ids = [];

        foreach ($rows as $row) {
            $user_id = dls_native_authors_resolve_legacy_term_user_id($row, $created_users);
            if ($user_id > 0) {
                $ids[] = $user_id;
            } else {
                $label = trim((string) ($row['name'] ?? 'Unknown term'));
                $unmapped_terms[] = $label . ' (#' . absint($row['term_id'] ?? 0) . ')';
            }
        }

        return dls_native_authors_sanitize_ids($ids);
    }
}

if (!function_exists('dls_native_authors_run_legacy_sql_migration')) {
    function dls_native_authors_run_legacy_sql_migration($force = false) {
        $already = get_option('dls_native_authors_migrated_v2', []);
        if (!$force && !empty($already['completed_at'])) {
            return $already;
        }

        global $wpdb;

        $stats = [
            'scanned_posts'  => 0,
            'updated_posts'  => 0,
            'created_users'  => 0,
            'unmapped_terms' => [],
            'completed_at'   => current_time('mysql'),
        ];

        $post_ids = $wpdb->get_col(
            "
            SELECT DISTINCT tr.object_id
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt
                ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tt.taxonomy = 'author'
            "
        );

        if (empty($post_ids)) {
            $stats['note'] = 'no legacy author term relationships found';
            update_option('dls_native_authors_migrated_v2', $stats, false);
            return $stats;
        }

        foreach ($post_ids as $post_id_raw) {
            $post_id = absint($post_id_raw);
            if ($post_id < 1 || get_post_type($post_id) !== 'post') {
                continue;
            }

            $stats['scanned_posts']++;

            $collected = dls_native_authors_collect_legacy_user_ids_for_post($post_id, $stats['created_users'], $stats['unmapped_terms']);
            $existing = dls_native_authors_get_stored_ids($post_id);
            $merged = dls_native_authors_sanitize_ids(array_merge($existing, $collected));

            if (!empty($merged) && $merged !== $existing) {
                dls_native_authors_store_ids($post_id, $merged);
                $stats['updated_posts']++;
            }
        }

        $stats['unmapped_terms'] = array_values(array_unique($stats['unmapped_terms']));
        update_option('dls_native_authors_migrated_v2', $stats, false);

        return $stats;
    }
}

add_action('init', function () {
    dls_native_authors_run_legacy_sql_migration(false);
}, 25);

add_filter('request', function ($query_vars) {
    if (is_admin() || !is_array($query_vars)) {
        return $query_vars;
    }

    if (dls_native_authors_publishpress_available()) {
        return $query_vars;
    }

    if (empty($query_vars['author_name']) || !empty($query_vars['author'])) {
        return $query_vars;
    }

    $author_slug = sanitize_title((string) $query_vars['author_name']);
    if ($author_slug === '') {
        return $query_vars;
    }

    $existing_user = get_user_by('slug', $author_slug);
    if ($existing_user instanceof WP_User) {
        $query_vars['author'] = (int) $existing_user->ID;
        $query_vars['author_name'] = (string) $existing_user->user_nicename;
        return $query_vars;
    }

    $term_row = dls_native_authors_get_legacy_term_row_by_slug($author_slug);
    if (!is_array($term_row)) {
        return $query_vars;
    }

    $created_users = 0;
    $user_id = dls_native_authors_resolve_legacy_term_user_id($term_row, $created_users);

    if ($user_id < 1) {
        return $query_vars;
    }

    $user = get_userdata($user_id);
    if (!($user instanceof WP_User)) {
        return $query_vars;
    }

    $query_vars['author'] = (int) $user->ID;
    $query_vars['author_name'] = (string) $user->user_nicename;

    return $query_vars;
}, 20);

add_action('admin_init', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!isset($_GET['dls_native_authors_migrate_v2']) || $_GET['dls_native_authors_migrate_v2'] !== '1') {
        return;
    }

    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'dls_native_authors_migrate_v2')) {
        return;
    }

    dls_native_authors_run_legacy_sql_migration(true);

    wp_safe_redirect(remove_query_arg(['dls_native_authors_migrate_v2', '_wpnonce']));
    exit;
});

/**
 * -----------------------------------------------------------------
 * DLS Native Authors: role-aware assignments + profile UI + post cards.
 * -----------------------------------------------------------------
 */
if (!function_exists('dls_native_authors_normalize_post_role')) {
    function dls_native_authors_normalize_post_role($role) {
        $role = strtolower(trim((string) $role));

        return in_array($role, ['author', 'editor'], true) ? $role : 'author';
    }
}

if (!function_exists('dls_native_authors_normalize_language_code')) {
    function dls_native_authors_normalize_language_code($lang) {
        $lang = strtolower(trim((string) $lang));

        return in_array($lang, ['uk', 'en'], true) ? $lang : '';
    }
}

if (!function_exists('dls_native_authors_get_guest_author_language')) {
    function dls_native_authors_get_guest_author_language($term_id) {
        $term_id = absint($term_id);
        if ($term_id < 1) {
            return '';
        }

        $stored = dls_native_authors_normalize_language_code(get_term_meta($term_id, '_dls_guest_author_language', true));
        if ($stored !== '') {
            return $stored;
        }

        return dls_native_authors_normalize_language_code(get_term_meta($term_id, '_dls_author_language', true));
    }
}

if (!function_exists('dls_native_authors_get_guest_author_term')) {
    function dls_native_authors_get_guest_author_term($term_id) {
        $term_id = absint($term_id);
        if ($term_id < 1 || !taxonomy_exists('author')) {
            return null;
        }

        $term = get_term($term_id, 'author');

        return $term instanceof WP_Term ? $term : null;
    }
}

if (!function_exists('dls_native_authors_is_guest_author_term')) {
    function dls_native_authors_is_guest_author_term($term) {
        if (!($term instanceof WP_Term) || $term->taxonomy !== 'author') {
            return false;
        }

        return dls_native_authors_extract_user_id_from_term((int) $term->term_id) < 1;
    }
}

if (!function_exists('dls_native_authors_get_guest_author_terms')) {
    function dls_native_authors_get_guest_author_terms() {
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
            if ($term instanceof WP_Term && dls_native_authors_extract_user_id_from_term((int) $term->term_id) < 1) {
                $guest_terms[] = $term;
            }
        }

        return $guest_terms;
    }
}

if (!function_exists('dls_native_authors_get_guest_author_name')) {
    function dls_native_authors_get_guest_author_name($term_id) {
        $term = dls_native_authors_get_guest_author_term($term_id);

        return $term instanceof WP_Term ? trim((string) $term->name) : '';
    }
}

if (!function_exists('dls_native_authors_get_guest_author_bio')) {
    function dls_native_authors_get_guest_author_bio($term_id) {
        $term_id = absint($term_id);
        if ($term_id < 1) {
            return '';
        }

        $custom = trim((string) get_term_meta($term_id, '_dls_author_short_bio', true));
        if ($custom !== '') {
            return $custom;
        }

        $custom = trim((string) get_term_meta($term_id, '_dls_guest_author_short_bio', true));
        if ($custom !== '') {
            return $custom;
        }

        $term = dls_native_authors_get_guest_author_term($term_id);

        return $term instanceof WP_Term ? trim((string) $term->description) : '';
    }
}

if (!function_exists('dls_native_authors_get_guest_author_avatar_url')) {
    function dls_native_authors_get_guest_author_avatar_url($term_id, $size = 72) {
        $term_id = absint($term_id);
        $size = max(24, absint($size));

        if ($term_id < 1) {
            return '';
        }

        $avatar_id = absint(get_term_meta($term_id, '_dls_author_avatar_id', true));
        if ($avatar_id < 1) {
            $avatar_id = absint(get_term_meta($term_id, '_dls_guest_author_avatar_id', true));
        }
        if ($avatar_id < 1) {
            $avatar_id = absint(get_term_meta($term_id, 'avatar', true));
        }

        if ($avatar_id > 0) {
            $avatar_by_id = wp_get_attachment_image_url($avatar_id, [$size, $size]);
            if (!$avatar_by_id) {
                $avatar_by_id = wp_get_attachment_url($avatar_id);
            }

            if (is_string($avatar_by_id) && $avatar_by_id !== '') {
                return $avatar_by_id;
            }
        }

        $custom = esc_url_raw((string) get_term_meta($term_id, '_dls_author_avatar_url', true));
        if ($custom !== '') {
            return $custom;
        }

        return esc_url_raw((string) get_term_meta($term_id, '_dls_guest_author_avatar_url', true));
    }
}

if (!function_exists('dls_native_authors_get_guest_author_avatar_html')) {
    function dls_native_authors_get_guest_author_avatar_html($term_id, $size = 72, $class = 'dls-post-author-card__avatar-img') {
        $term_id = absint($term_id);
        $size = max(24, absint($size));
        $url = dls_native_authors_get_guest_author_avatar_url($term_id, $size);

        if ($url !== '') {
            return '<img class="' . esc_attr($class) . '" src="' . esc_url($url) . '" alt="" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" loading="lazy">';
        }

        $name = dls_native_authors_get_guest_author_name($term_id);
        $letter = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : 'A';

        return '<span class="dls-post-author-card__avatar-fallback" aria-hidden="true">' . esc_html($letter) . '</span>';
    }
}

if (!function_exists('dls_native_authors_get_guest_author_url')) {
    function dls_native_authors_get_guest_author_url($term_id) {
        $term = dls_native_authors_get_guest_author_term($term_id);
        if (!($term instanceof WP_Term)) {
            return '';
        }

        $url = get_term_link($term);

        return is_wp_error($url) ? '' : (string) $url;
    }
}

if (!function_exists('dls_native_authors_get_author_term_id_for_user')) {
    function dls_native_authors_get_author_term_id_for_user($user_id) {
        $user_id = absint($user_id);
        if ($user_id < 1 || !taxonomy_exists('author')) {
            return 0;
        }

        $direct = get_terms([
            'taxonomy'   => 'author',
            'hide_empty' => false,
            'number'     => 1,
            'fields'     => 'ids',
            'meta_query' => [
                [
                    'key'   => 'user_id',
                    'value' => $user_id,
                ],
            ],
        ]);

        if (is_array($direct) && !empty($direct)) {
            return absint(reset($direct));
        }

        $legacy = get_terms([
            'taxonomy'   => 'author',
            'hide_empty' => false,
            'number'     => 1,
            'fields'     => 'ids',
            'meta_query' => [
                [
                    'key'     => 'user_id_' . $user_id,
                    'compare' => 'EXISTS',
                ],
            ],
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

if (!function_exists('dls_native_authors_get_user_author_url')) {
    function dls_native_authors_get_user_author_url($user_id) {
        $user_id = absint($user_id);
        if ($user_id < 1) {
            return '';
        }

        $term_id = dls_native_authors_get_author_term_id_for_user($user_id);
        if ($term_id > 0) {
            $url = get_term_link($term_id, 'author');
            if (!is_wp_error($url) && is_string($url) && $url !== '') {
                return $url;
            }
        }

        return (string) get_author_posts_url($user_id);
    }
}

if (!function_exists('dls_native_authors_normalize_assignments')) {
    function dls_native_authors_normalize_assignments($assignments) {
        $normalized = [];
        $seen = [];

        if (!is_array($assignments)) {
            return [];
        }

        foreach ($assignments as $row) {
            if (!is_array($row)) {
                continue;
            }

            $author_type = strtolower(trim((string) ($row['author_type'] ?? '')));
            $term_id = absint($row['term_id'] ?? ($row['guest_term_id'] ?? 0));
            $user_id = absint($row['user_id'] ?? 0);

            if (($author_type === 'guest' || $term_id > 0) && $term_id > 0) {
                $term = dls_native_authors_get_guest_author_term($term_id);
                $key = 'guest:' . $term_id;

                if (!($term instanceof WP_Term) || !dls_native_authors_is_guest_author_term($term) || isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $normalized[] = [
                    'author_type' => 'guest',
                    'user_id'     => 0,
                    'term_id'     => $term_id,
                    'post_role'   => dls_native_authors_normalize_post_role($row['post_role'] ?? 'author'),
                ];
                continue;
            }

            $key = 'user:' . $user_id;

            if ($user_id < 1 || !get_userdata($user_id) || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = [
                'author_type' => 'user',
                'user_id'     => $user_id,
                'term_id'     => 0,
                'post_role'   => dls_native_authors_normalize_post_role($row['post_role'] ?? 'author'),
            ];
        }

        return $normalized;
    }
}

if (!function_exists('dls_native_authors_get_post_assignments')) {
    function dls_native_authors_get_post_assignments($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return [];
        }

        $stored = get_post_meta($post_id, '_dls_post_author_assignments', true);
        $assignments = dls_native_authors_normalize_assignments($stored);
        $legacy_assignments = dls_native_authors_get_legacy_assignments_for_post($post_id);

        if (!empty($legacy_assignments)) {
            $legacy_guests = [];

            foreach ($legacy_assignments as $legacy_row) {
                if (($legacy_row['author_type'] ?? 'user') === 'guest') {
                    $legacy_guests[] = $legacy_row;
                }
            }

            if (empty($assignments)) {
                $assignments = $legacy_assignments;
            } elseif (!empty($legacy_guests)) {
                $assignments = array_merge($assignments, $legacy_guests);
            }
        }

        if (empty($assignments)) {
            foreach (dls_native_authors_get_stored_ids($post_id) as $user_id) {
                $assignments[] = [
                    'author_type' => 'user',
                    'user_id'     => $user_id,
                    'term_id'     => 0,
                    'post_role'   => 'author',
                ];
            }
        }

        return dls_native_authors_normalize_assignments($assignments);
    }
}

if (!function_exists('dls_native_authors_get_legacy_assignments_for_post')) {
    function dls_native_authors_get_legacy_assignments_for_post($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1 || !taxonomy_exists('author')) {
            return [];
        }

        $terms = wp_get_post_terms($post_id, 'author');
        if (empty($terms) || is_wp_error($terms)) {
            return [];
        }

        $assignments = [];
        $seen = [];

        foreach ($terms as $term) {
            if (!($term instanceof WP_Term)) {
                continue;
            }

            $term_id = (int) $term->term_id;
            if ($term_id < 1) {
                continue;
            }

            $linked_user_id = dls_native_authors_extract_user_id_from_term($term_id);

            if ($linked_user_id > 0) {
                $key = 'user:' . $linked_user_id;
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $assignments[] = [
                    'author_type' => 'user',
                    'user_id'     => $linked_user_id,
                    'term_id'     => 0,
                    'post_role'   => 'author',
                ];
                continue;
            }

            if (!dls_native_authors_is_guest_author_term($term)) {
                continue;
            }

            $key = 'guest:' . $term_id;
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $assignments[] = [
                'author_type' => 'guest',
                'user_id'     => 0,
                'term_id'     => $term_id,
                'post_role'   => 'author',
            ];
        }

        return dls_native_authors_normalize_assignments($assignments);
    }
}

if (!function_exists('dls_native_authors_get_post_people_with_roles')) {
    function dls_native_authors_get_post_people_with_roles($post_id) {
        $items = [];

        foreach (dls_native_authors_get_post_assignments($post_id) as $row) {
            $post_role = dls_native_authors_normalize_post_role($row['post_role'] ?? 'author');
            $author_type = $row['author_type'] ?? 'user';

            if ($author_type === 'guest') {
                $term_id = absint($row['term_id'] ?? 0);
                $term = dls_native_authors_get_guest_author_term($term_id);

                if (!($term instanceof WP_Term) || !dls_native_authors_is_guest_author_term($term)) {
                    continue;
                }

                $items[] = [
                    'author_type' => 'guest',
                    'term_id'     => $term_id,
                    'user_id'     => 0,
                    'term'        => $term,
                    'name'        => dls_native_authors_get_guest_author_name($term_id),
                    'url'         => dls_native_authors_get_guest_author_url($term_id),
                    'post_role'   => $post_role,
                ];
                continue;
            }

            $user_id = absint($row['user_id'] ?? 0);
            $user = get_userdata($user_id);
            if (!($user instanceof WP_User)) {
                continue;
            }

            $items[] = [
                'author_type' => 'user',
                'term_id'     => 0,
                'user_id'     => $user_id,
                'user'        => $user,
                'name'        => (string) $user->display_name,
                'url'         => dls_native_authors_get_user_author_url($user_id),
                'post_role'   => $post_role,
            ];
        }

        return $items;
    }
}

if (!function_exists('dls_native_authors_get_post_users_with_roles')) {
    function dls_native_authors_get_post_users_with_roles($post_id) {
        $items = [];

        foreach (dls_native_authors_get_post_people_with_roles($post_id) as $row) {
            $user = $row['user'] ?? null;
            if (!($user instanceof WP_User)) {
                continue;
            }

            $items[] = [
                'user'      => $user,
                'post_role' => dls_native_authors_normalize_post_role($row['post_role'] ?? 'author'),
            ];
        }

        return $items;
    }
}

if (!function_exists('dls_native_authors_save_assignments_for_post')) {
    function dls_native_authors_save_assignments_for_post($post_id, $selected_ids, $role_map = []) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return;
        }

        $assignments = [];
        $user_ids = [];
        $seen = [];

        foreach ((array) $selected_ids as $item) {
            if (is_array($item)) {
                $raw = $item;
            } else {
                $raw = [
                    'author_type' => 'user',
                    'user_id'     => absint($item),
                ];
            }

            if (!is_array($raw)) {
                continue;
            }

            $map_key = '';
            if (($raw['author_type'] ?? '') === 'guest' || !empty($raw['term_id']) || !empty($raw['guest_term_id'])) {
                $map_key = 'guest:' . absint($raw['term_id'] ?? ($raw['guest_term_id'] ?? 0));
            } else {
                $map_key = 'user:' . absint($raw['user_id'] ?? 0);
            }

            $role = $raw['post_role'] ?? 'author';
            if (is_array($role_map) && isset($role_map[$map_key])) {
                $role = $role_map[$map_key];
            } elseif (is_array($role_map) && isset($role_map[absint($raw['user_id'] ?? 0)])) {
                $role = $role_map[absint($raw['user_id'] ?? 0)];
            }

            $normalized = dls_native_authors_normalize_assignments([array_merge($raw, ['post_role' => $role])]);
            if (empty($normalized)) {
                continue;
            }

            $assignment = reset($normalized);
            $assignment_key = ($assignment['author_type'] === 'guest')
                ? 'guest:' . (int) $assignment['term_id']
                : 'user:' . (int) $assignment['user_id'];

            if (isset($seen[$assignment_key])) {
                continue;
            }

            $seen[$assignment_key] = true;
            $assignments[] = $assignment;

            if ($assignment['author_type'] === 'user' && (int) $assignment['user_id'] > 0) {
                $user_ids[] = (int) $assignment['user_id'];
            }
        }

        if (empty($assignments)) {
            delete_post_meta($post_id, '_dls_post_author_assignments');
        } else {
            update_post_meta($post_id, '_dls_post_author_assignments', $assignments);
        }

        dls_native_authors_store_ids($post_id, dls_native_authors_sanitize_ids($user_ids));

        if (!taxonomy_exists('author')) {
            return;
        }

        $selected_author_term_ids = [];
        foreach ($assignments as $assignment) {
            $author_type = (string) ($assignment['author_type'] ?? 'user');

            if ($author_type === 'guest') {
                $term_id = absint($assignment['term_id'] ?? 0);
                if ($term_id > 0) {
                    $selected_author_term_ids[] = $term_id;
                }
                continue;
            }

            $user_term_id = dls_native_authors_get_author_term_id_for_user(absint($assignment['user_id'] ?? 0));
            if ($user_term_id > 0) {
                $selected_author_term_ids[] = $user_term_id;
            }
        }

        $selected_author_term_ids = array_values(array_unique(array_map('absint', $selected_author_term_ids)));
        $existing_terms = wp_get_post_terms($post_id, 'author');
        $terms_to_keep = [];

        if (is_array($existing_terms) && !is_wp_error($existing_terms)) {
            foreach ($existing_terms as $term) {
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $term_id = (int) $term->term_id;
                if ($term_id < 1) {
                    continue;
                }

                $linked_user_id = dls_native_authors_extract_user_id_from_term($term_id);
                if ($linked_user_id < 1 && !dls_native_authors_is_guest_author_term($term)) {
                    $terms_to_keep[] = $term_id;
                }
            }
        }

        $final_term_ids = array_values(array_unique(array_merge($terms_to_keep, $selected_author_term_ids)));
        wp_set_post_terms($post_id, $final_term_ids, 'author', false);
    }
}

if (!function_exists('dls_native_authors_render_metabox_v2')) {
    function dls_native_authors_render_metabox_v2($post) {
        if (!($post instanceof WP_Post)) {
            return;
        }

        wp_nonce_field('dls_native_authors_save', 'dls_native_authors_nonce');

        $assignments = dls_native_authors_get_post_assignments($post->ID);
        $role_map = [];
        foreach ($assignments as $row) {
            $role_map[(int) $row['user_id']] = dls_native_authors_normalize_post_role($row['post_role']);
        }

        $users = dls_native_authors_get_users();

        echo '<p style="margin:0 0 10px">Select people and choose their role for this text.</p>';
        echo '<div style="max-height:320px; overflow:auto; border:1px solid #dcdcde; padding:8px; background:#fff">';

        foreach ($users as $user) {
            if (!($user instanceof WP_User)) {
                continue;
            }

            $user_id = (int) $user->ID;
            $checked = isset($role_map[$user_id]);
            $role_value = $checked ? $role_map[$user_id] : 'author';
            $wp_role = !empty($user->roles[0]) ? (string) $user->roles[0] : 'user';

            echo '<div style="display:grid; grid-template-columns: 1fr auto; gap:8px; align-items:center; margin:0 0 7px">';
            echo '<label style="display:flex; align-items:center; gap:8px; min-width:0">';
            echo '<input type="checkbox" name="dls_post_authors[]" value="' . esc_attr($user_id) . '"' . ($checked ? ' checked' : '') . '>';
            echo '<span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap">' . esc_html($user->display_name) . '</span>';
            echo '<code style="opacity:.7">' . esc_html($wp_role) . '</code>';
            echo '</label>';

            echo '<select name="dls_post_author_role[' . esc_attr($user_id) . ']" style="min-width:88px">';
            echo '<option value="author"' . selected($role_value, 'author', false) . '>Author</option>';
            echo '<option value="editor"' . selected($role_value, 'editor', false) . '>Editor</option>';
            echo '</select>';
            echo '</div>';
        }

        echo '</div>';
    }
}

add_action('add_meta_boxes', function () {
    // The dedicated admin dropdown plugin owns the post editor UI when loaded.
    if (function_exists('dls_na_ui_render_metabox')) {
        return;
    }

    foreach (dls_native_authors_meta_box_post_types() as $post_type) {
        remove_meta_box('dls-native-authors-box', $post_type, 'side');

        add_meta_box(
            'dls-native-authors-box',
            'DLS Authors',
            'dls_native_authors_render_metabox_v2',
            $post_type,
            'side',
            'high'
        );
    }
}, 100);

add_action('save_post', function ($post_id, $post) {
    // Let the dedicated admin dropdown plugin handle saving when it is active.
    if (function_exists('dls_na_ui_render_metabox')) {
        return;
    }

    if (!($post instanceof WP_Post)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    if (!isset($_POST['dls_native_authors_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dls_native_authors_nonce'])), 'dls_native_authors_save')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $selected = isset($_POST['dls_post_authors']) ? dls_native_authors_sanitize_ids(wp_unslash((array) $_POST['dls_post_authors'])) : [];
    $raw_role_map = isset($_POST['dls_post_author_role']) ? (array) wp_unslash($_POST['dls_post_author_role']) : [];
    $role_map = [];

    foreach ($raw_role_map as $id => $role) {
        $role_map[absint($id)] = dls_native_authors_normalize_post_role($role);
    }

    dls_native_authors_save_assignments_for_post($post_id, $selected, $role_map);
}, 80, 2);

if (!function_exists('dls_native_authors_get_user_short_bio')) {
    function dls_native_authors_get_user_short_bio($user_id) {
        $user_id = absint($user_id);
        if ($user_id < 1) {
            return '';
        }

        $custom = trim((string) get_user_meta($user_id, '_dls_author_short_bio', true));
        if ($custom !== '') {
            return $custom;
        }

        return trim((string) get_the_author_meta('description', $user_id));
    }
}

if (!function_exists('dls_native_authors_get_user_avatar_url')) {
    function dls_native_authors_get_user_avatar_url($user_id, $size = 72) {
        $user_id = absint($user_id);
        $size = max(24, absint($size));

        if ($user_id < 1) {
            return '';
        }

        $custom_id = absint(get_user_meta($user_id, '_dls_author_avatar_id', true));
        if ($custom_id > 0) {
            $custom_by_id = wp_get_attachment_image_url($custom_id, [$size, $size]);
            if (!$custom_by_id) {
                $custom_by_id = wp_get_attachment_url($custom_id);
            }

            if (is_string($custom_by_id) && $custom_by_id !== '') {
                return $custom_by_id;
            }
        }

        $custom = esc_url_raw((string) get_user_meta($user_id, '_dls_author_avatar_url', true));
        if ($custom !== '') {
            return $custom;
        }

        $fallback = get_avatar_url($user_id, ['size' => $size]);

        return is_string($fallback) ? $fallback : '';
    }
}

if (!function_exists('dls_native_authors_get_user_avatar_html')) {
    function dls_native_authors_get_user_avatar_html($user_id, $size = 72, $class = 'dls-post-author-card__avatar-img') {
        $user_id = absint($user_id);
        $size = max(24, absint($size));
        $url = dls_native_authors_get_user_avatar_url($user_id, $size);

        if ($url !== '') {
            return '<img class="' . esc_attr($class) . '" src="' . esc_url($url) . '" alt="" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" loading="lazy">';
        }

        $user = get_userdata($user_id);
        $name = $user instanceof WP_User ? (string) $user->display_name : '';
        $letter = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : 'A';

        return '<span class="dls-post-author-card__avatar-fallback" aria-hidden="true">' . esc_html($letter) . '</span>';
    }
}

if (!function_exists('dls_native_authors_post_role_label')) {
    function dls_native_authors_post_role_label($role) {
        $role = dls_native_authors_normalize_post_role($role);

        if ($role === 'editor') {
            return 'Editor';
        }

        return 'Author';
    }
}

if (!function_exists('dls_native_authors_render_post_authors_block')) {
    function dls_native_authors_render_post_authors_block($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1 || get_post_type($post_id) !== 'post') {
            return '';
        }

        $items = dls_native_authors_get_post_people_with_roles($post_id);
        if (empty($items)) {
            return '';
        }

        $grouped = [
            'author' => [],
            'editor' => [],
        ];

        foreach ($items as $item) {
            $role = dls_native_authors_normalize_post_role($item['post_role'] ?? 'author');
            $grouped[$role][] = $item;
        }

        ob_start();
        ?>
        <section class="dls-post-people" aria-label="Post people">
            <?php if (!empty($grouped['author'])) : ?>
                <div class="dls-post-people__group dls-post-people__group--author">
                    <h2 class="dls-post-people__group-title">Автор</h2>
                    <div class="dls-post-people__list">
                        <?php foreach ($grouped['author'] as $item) : ?>
                            <?php
                            $is_user = ($item['author_type'] ?? 'user') === 'user';
                            $name = trim((string) ($item['name'] ?? ''));
                            if ($name === '') {
                                continue;
                            }
                            $link = trim((string) ($item['url'] ?? ''));
                            $bio = $is_user
                                ? dls_native_authors_get_user_short_bio((int) ($item['user_id'] ?? 0))
                                : dls_native_authors_get_guest_author_bio((int) ($item['term_id'] ?? 0));
                            $avatar_html = $is_user
                                ? dls_native_authors_get_user_avatar_html((int) ($item['user_id'] ?? 0), 84, 'dls-post-person__avatar-img')
                                : dls_native_authors_get_guest_author_avatar_html((int) ($item['term_id'] ?? 0), 84, 'dls-post-person__avatar-img');
                            ?>
                            <article class="dls-post-person">
                                <?php if ($link !== '') : ?>
                                    <a class="dls-post-person__avatar" href="<?php echo esc_url($link); ?>">
                                        <?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </a>
                                    <a class="dls-post-person__name" href="<?php echo esc_url($link); ?>">
                                        <?php echo esc_html($name); ?>
                                    </a>
                                <?php else : ?>
                                    <span class="dls-post-person__avatar">
                                        <?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </span>
                                    <span class="dls-post-person__name">
                                        <?php echo esc_html($name); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($bio !== '') : ?>
                                    <p class="dls-post-person__bio"><?php echo esc_html(wp_trim_words($bio, 32, '…')); ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($grouped['editor'])) : ?>
                <div class="dls-post-people__group dls-post-people__group--editor">
                    <h2 class="dls-post-people__group-title">Редактор</h2>
                    <div class="dls-post-people__list">
                        <?php foreach ($grouped['editor'] as $item) : ?>
                            <?php
                            $is_user = ($item['author_type'] ?? 'user') === 'user';
                            $name = trim((string) ($item['name'] ?? ''));
                            if ($name === '') {
                                continue;
                            }
                            $link = trim((string) ($item['url'] ?? ''));
                            $bio = $is_user
                                ? dls_native_authors_get_user_short_bio((int) ($item['user_id'] ?? 0))
                                : dls_native_authors_get_guest_author_bio((int) ($item['term_id'] ?? 0));
                            $avatar_html = $is_user
                                ? dls_native_authors_get_user_avatar_html((int) ($item['user_id'] ?? 0), 84, 'dls-post-person__avatar-img')
                                : dls_native_authors_get_guest_author_avatar_html((int) ($item['term_id'] ?? 0), 84, 'dls-post-person__avatar-img');
                            ?>
                            <article class="dls-post-person">
                                <?php if ($link !== '') : ?>
                                    <a class="dls-post-person__avatar" href="<?php echo esc_url($link); ?>">
                                        <?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </a>
                                    <a class="dls-post-person__name" href="<?php echo esc_url($link); ?>">
                                        <?php echo esc_html($name); ?>
                                    </a>
                                <?php else : ?>
                                    <span class="dls-post-person__avatar">
                                        <?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </span>
                                    <span class="dls-post-person__name">
                                        <?php echo esc_html($name); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($bio !== '') : ?>
                                    <p class="dls-post-person__bio"><?php echo esc_html(wp_trim_words($bio, 32, '…')); ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
add_action('kadence_single_after_entry_content', function () {
    if (!is_singular('post')) {
        return;
    }

    $post_id = get_the_ID();
    if (!$post_id) {
        return;
    }

    $html = dls_native_authors_render_post_authors_block($post_id);
    if ($html === '') {
        return;
    }

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 25);

add_action('admin_menu', function () {
    add_users_page(
        'DLS Author Profiles',
        'DLS Author Profiles',
        'edit_users',
        'dls-author-profiles',
        function () {
            if (!current_user_can('edit_users')) {
                return;
            }

            $notices = [];

            if (
                isset($_POST['dls_author_profiles_action'])
                && $_POST['dls_author_profiles_action'] === 'save'
                && isset($_POST['dls_author_profiles_nonce'])
                && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dls_author_profiles_nonce'])), 'dls_author_profiles_save')
            ) {
                $rows = isset($_POST['dls_author_profiles']) ? (array) wp_unslash($_POST['dls_author_profiles']) : [];

                foreach ($rows as $user_id_raw => $row) {
                    $user_id = absint($user_id_raw);
                    if ($user_id < 1 || !current_user_can('edit_user', $user_id) || !is_array($row)) {
                        continue;
                    }

                    $display_name = sanitize_text_field((string) ($row['display_name'] ?? ''));
                    $short_bio = sanitize_textarea_field((string) ($row['short_bio'] ?? ''));
                    $avatar_url = esc_url_raw((string) ($row['avatar_url'] ?? ''));
                    $avatar_id = absint($row['avatar_id'] ?? 0);

                    if ($display_name !== '') {
                        wp_update_user([
                            'ID'           => $user_id,
                            'display_name' => $display_name,
                            'nickname'     => $display_name,
                        ]);
                    }

                    if ($short_bio === '') {
                        delete_user_meta($user_id, '_dls_author_short_bio');
                    } else {
                        update_user_meta($user_id, '_dls_author_short_bio', $short_bio);
                    }

                    if ($avatar_id > 0) {
                        update_user_meta($user_id, '_dls_author_avatar_id', $avatar_id);

                        $avatar_by_id = wp_get_attachment_image_url($avatar_id, 'thumbnail');
                        if (!$avatar_by_id) {
                            $avatar_by_id = wp_get_attachment_url($avatar_id);
                        }

                        if (is_string($avatar_by_id) && $avatar_by_id !== '') {
                            $avatar_url = esc_url_raw($avatar_by_id);
                        }
                    } else {
                        delete_user_meta($user_id, '_dls_author_avatar_id');
                    }

                    if ($avatar_url === '') {
                        delete_user_meta($user_id, '_dls_author_avatar_url');
                    } else {
                        update_user_meta($user_id, '_dls_author_avatar_url', $avatar_url);
                    }
                }

                $guest_rows = isset($_POST['dls_guest_author_profiles']) ? (array) wp_unslash($_POST['dls_guest_author_profiles']) : [];

                foreach ($guest_rows as $term_id_raw => $row) {
                    $term_id = absint($term_id_raw);
                    $term = dls_native_authors_get_guest_author_term($term_id);

                    if ($term_id < 1 || !($term instanceof WP_Term) || !dls_native_authors_is_guest_author_term($term) || !is_array($row)) {
                        continue;
                    }

                    $display_name = sanitize_text_field((string) ($row['display_name'] ?? ''));
                    $short_bio = sanitize_textarea_field((string) ($row['short_bio'] ?? ''));
                    $avatar_url = esc_url_raw((string) ($row['avatar_url'] ?? ''));
                    $avatar_id = absint($row['avatar_id'] ?? 0);

                    if ($display_name !== '' && $display_name !== $term->name) {
                        wp_update_term($term_id, 'author', ['name' => $display_name]);
                    }

                    wp_update_term($term_id, 'author', ['description' => $short_bio]);

                    if ($short_bio === '') {
                        delete_term_meta($term_id, '_dls_author_short_bio');
                        delete_term_meta($term_id, '_dls_guest_author_short_bio');
                    } else {
                        update_term_meta($term_id, '_dls_author_short_bio', $short_bio);
                        update_term_meta($term_id, '_dls_guest_author_short_bio', $short_bio);
                    }

                    if ($avatar_id > 0) {
                        update_term_meta($term_id, '_dls_author_avatar_id', $avatar_id);
                        update_term_meta($term_id, '_dls_guest_author_avatar_id', $avatar_id);

                        $avatar_by_id = wp_get_attachment_image_url($avatar_id, 'thumbnail');
                        if (!$avatar_by_id) {
                            $avatar_by_id = wp_get_attachment_url($avatar_id);
                        }

                        if (is_string($avatar_by_id) && $avatar_by_id !== '') {
                            $avatar_url = esc_url_raw($avatar_by_id);
                        }
                    } else {
                        delete_term_meta($term_id, '_dls_author_avatar_id');
                        delete_term_meta($term_id, '_dls_guest_author_avatar_id');
                    }

                    if ($avatar_url === '') {
                        delete_term_meta($term_id, '_dls_author_avatar_url');
                        delete_term_meta($term_id, '_dls_guest_author_avatar_url');
                    } else {
                        update_term_meta($term_id, '_dls_author_avatar_url', $avatar_url);
                        update_term_meta($term_id, '_dls_guest_author_avatar_url', $avatar_url);
                    }
                }

                $notices[] = ['type' => 'success', 'message' => 'Author profiles updated.'];
            }

            if (
                isset($_POST['dls_guest_author_create_action'])
                && $_POST['dls_guest_author_create_action'] === 'create'
                && isset($_POST['dls_guest_author_create_nonce'])
                && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dls_guest_author_create_nonce'])), 'dls_guest_author_create')
            ) {
                $name = sanitize_text_field((string) ($_POST['dls_guest_author_name'] ?? ''));
                $lang = dls_native_authors_normalize_language_code($_POST['dls_guest_author_language'] ?? '');
                $short_bio = sanitize_textarea_field((string) ($_POST['dls_guest_author_short_bio'] ?? ''));
                $avatar_url = esc_url_raw((string) ($_POST['dls_guest_author_avatar_url'] ?? ''));
                $avatar_id = absint($_POST['dls_guest_author_avatar_id'] ?? 0);
                $slug = sanitize_title((string) ($_POST['dls_guest_author_slug'] ?? ''));
                $create_second = !empty($_POST['dls_guest_author_create_second']);
                $second_name = sanitize_text_field((string) ($_POST['dls_guest_author_name_second'] ?? ''));
                $second_lang = dls_native_authors_normalize_language_code($_POST['dls_guest_author_language_second'] ?? '');
                $second_short_bio = sanitize_textarea_field((string) ($_POST['dls_guest_author_short_bio_second'] ?? ''));
                $second_slug = sanitize_title((string) ($_POST['dls_guest_author_slug_second'] ?? ''));

                $entries = [];

                if ($name !== '') {
                    $entries[] = [
                        'name'      => $name,
                        'lang'      => $lang,
                        'short_bio' => $short_bio,
                        'slug'      => $slug,
                    ];
                }

                if ($create_second && $second_name !== '') {
                    $entries[] = [
                        'name'      => $second_name,
                        'lang'      => $second_lang,
                        'short_bio' => $second_short_bio,
                        'slug'      => $second_slug,
                    ];
                }

                if (empty($entries)) {
                    $notices[] = ['type' => 'error', 'message' => 'Guest author name is required.'];
                } elseif (!taxonomy_exists('author')) {
                    $notices[] = ['type' => 'error', 'message' => 'Author taxonomy is not available.'];
                } else {
                    $langs = [];
                    $invalid_language = false;

                    foreach ($entries as $entry) {
                        if (($entry['lang'] ?? '') === '') {
                            $invalid_language = true;
                            break;
                        }

                        if (isset($langs[$entry['lang']])) {
                            $invalid_language = true;
                            break;
                        }

                        $langs[$entry['lang']] = true;
                    }

                    if ($invalid_language) {
                        $notices[] = ['type' => 'error', 'message' => 'Choose different languages for each guest author entry.'];
                    } else {
                        $created_labels = [];

                        foreach ($entries as $entry) {
                            $entry_slug = (string) ($entry['slug'] ?? '');
                            if ($entry_slug === '') {
                                $entry_slug = sanitize_title((string) ($entry['name'] ?? ''));
                            }

                            $entry_lang = (string) ($entry['lang'] ?? '');
                            if ($entry_slug !== '' && $entry_lang !== '' && !preg_match('/(?:^|[-_])' . preg_quote($entry_lang, '/') . '(?:$|[-_0-9])/', $entry_slug)) {
                                $entry_slug .= '-' . $entry_lang;
                            }

                            $create_args = [
                                'description' => (string) ($entry['short_bio'] ?? ''),
                            ];

                            if ($entry_slug !== '') {
                                $create_args['slug'] = $entry_slug;
                            }

                            $created = wp_insert_term((string) ($entry['name'] ?? ''), 'author', $create_args);

                            if (is_wp_error($created)) {
                                $notices[] = ['type' => 'error', 'message' => $created->get_error_message()];
                                continue;
                            }

                            $term_id = absint($created['term_id'] ?? 0);
                            $entry_bio = (string) ($entry['short_bio'] ?? '');

                            update_term_meta($term_id, '_dls_author_language', $entry_lang);
                            update_term_meta($term_id, '_dls_guest_author_language', $entry_lang);

                            if ($entry_bio !== '') {
                                update_term_meta($term_id, '_dls_author_short_bio', $entry_bio);
                                update_term_meta($term_id, '_dls_guest_author_short_bio', $entry_bio);
                            }

                            if ($avatar_id > 0) {
                                update_term_meta($term_id, '_dls_author_avatar_id', $avatar_id);
                                update_term_meta($term_id, '_dls_guest_author_avatar_id', $avatar_id);

                                $avatar_by_id = wp_get_attachment_image_url($avatar_id, 'thumbnail');
                                if (!$avatar_by_id) {
                                    $avatar_by_id = wp_get_attachment_url($avatar_id);
                                }

                                if (is_string($avatar_by_id) && $avatar_by_id !== '') {
                                    $avatar_url = esc_url_raw($avatar_by_id);
                                }
                            }

                            if ($avatar_url !== '') {
                                update_term_meta($term_id, '_dls_author_avatar_url', $avatar_url);
                                update_term_meta($term_id, '_dls_guest_author_avatar_url', $avatar_url);
                            }

                            $created_labels[] = strtoupper($entry_lang) . ': ' . (string) ($entry['name'] ?? '');
                        }

                        if (!empty($created_labels)) {
                            $notices[] = ['type' => 'success', 'message' => 'Guest author created: ' . implode('; ', $created_labels) . '.'];
                        }
                    }
                }
            }

            $users = dls_native_authors_get_users();
            $guest_authors = dls_native_authors_get_guest_author_terms();

            echo '<div class="wrap">';
            echo '<h1>DLS Author Profiles</h1>';
            echo '<p>Edit short bios and profile images used on post contributor cards.</p>';

            foreach ($notices as $notice) {
                $type = ($notice['type'] ?? 'success') === 'error' ? 'notice-error' : 'notice-success';
                echo '<div class="notice ' . esc_attr($type) . '"><p>' . esc_html((string) ($notice['message'] ?? '')) . '</p></div>';
            }

            echo '<h2>New Guest Author</h2>';
            echo '<form method="post" style="max-width:860px; margin:0 0 24px">';
            wp_nonce_field('dls_guest_author_create', 'dls_guest_author_create_nonce');
            echo '<input type="hidden" name="dls_guest_author_create_action" value="create">';
            echo '<table class="form-table" role="presentation"><tbody>';
            echo '<tr><th scope="row"><label for="dls-guest-author-language">Primary Language</label></th><td><select id="dls-guest-author-language" name="dls_guest_author_language"><option value="uk">UK</option><option value="en">EN</option></select></td></tr>';
            echo '<tr><th scope="row"><label for="dls-guest-author-name">Primary Name</label></th><td><input id="dls-guest-author-name" type="text" class="regular-text" name="dls_guest_author_name" value=""></td></tr>';
            echo '<tr><th scope="row"><label for="dls-guest-author-slug">Primary Slug</label></th><td><input id="dls-guest-author-slug" type="text" class="regular-text" name="dls_guest_author_slug" value=""><p class="description">Optional. Language suffix is added automatically when needed.</p></td></tr>';
            echo '<tr><th scope="row"><label for="dls-guest-author-short-bio">Primary Short Bio</label></th><td><textarea id="dls-guest-author-short-bio" class="large-text" style="min-height:90px" name="dls_guest_author_short_bio"></textarea></td></tr>';
            echo '<tr><th scope="row">Second Language</th><td><label><input type="checkbox" name="dls_guest_author_create_second" value="1"> Create second language version at the same time</label></td></tr>';
            echo '<tr><th scope="row"><label for="dls-guest-author-language-second">Second Language</label></th><td><select id="dls-guest-author-language-second" name="dls_guest_author_language_second"><option value="en">EN</option><option value="uk">UK</option></select></td></tr>';
            echo '<tr><th scope="row"><label for="dls-guest-author-name-second">Second Name</label></th><td><input id="dls-guest-author-name-second" type="text" class="regular-text" name="dls_guest_author_name_second" value=""></td></tr>';
            echo '<tr><th scope="row"><label for="dls-guest-author-slug-second">Second Slug</label></th><td><input id="dls-guest-author-slug-second" type="text" class="regular-text" name="dls_guest_author_slug_second" value=""></td></tr>';
            echo '<tr><th scope="row"><label for="dls-guest-author-short-bio-second">Second Short Bio</label></th><td><textarea id="dls-guest-author-short-bio-second" class="large-text" style="min-height:90px" name="dls_guest_author_short_bio_second"></textarea></td></tr>';
            echo '<tr><th scope="row">Profile Image</th><td>';
            echo '<input type="hidden" class="dls-author-avatar-id" name="dls_guest_author_avatar_id" value="">';
            echo '<input type="url" class="dls-author-avatar-url regular-text" style="width:100%; max-width:420px; margin-bottom:6px" placeholder="https://..." name="dls_guest_author_avatar_url" value="">';
            echo '<div style="display:flex; gap:6px"><button type="button" class="button dls-author-avatar-select">Select Image</button><button type="button" class="button dls-author-avatar-remove">Remove</button></div>';
            echo '</td></tr>';
            echo '</tbody></table>';
            echo '<p><button class="button button-primary" type="submit">Create Guest Author</button></p>';
            echo '</form>';

            echo '<form method="post">';
            wp_nonce_field('dls_author_profiles_save', 'dls_author_profiles_nonce');
            echo '<input type="hidden" name="dls_author_profiles_action" value="save">';

            echo '<h2>Users</h2>';
            echo '<table class="widefat striped">';
            echo '<thead><tr><th style="width:80px">Avatar</th><th style="width:220px">Name</th><th style="width:140px">WP Role</th><th>Short Bio</th><th style="width:360px">Custom Image</th></tr></thead>';
            echo '<tbody>';

            foreach ($users as $user) {
                if (!($user instanceof WP_User)) {
                    continue;
                }

                $user_id = (int) $user->ID;
                $wp_role = !empty($user->roles[0]) ? (string) $user->roles[0] : 'user';
                $short_bio = (string) get_user_meta($user_id, '_dls_author_short_bio', true);
                $avatar_url = (string) get_user_meta($user_id, '_dls_author_avatar_url', true);
                $avatar_id = absint(get_user_meta($user_id, '_dls_author_avatar_id', true));

                echo '<tr>';
                echo '<td>' . dls_native_authors_get_user_avatar_html($user_id, 48, 'dls-author-profile-avatar') . '</td>';
                echo '<td><input type="text" style="width:100%" name="dls_author_profiles[' . esc_attr($user_id) . '][display_name]" value="' . esc_attr($user->display_name) . '"></td>';
                echo '<td><code>' . esc_html($wp_role) . '</code></td>';
                echo '<td><textarea style="width:100%; min-height:76px" name="dls_author_profiles[' . esc_attr($user_id) . '][short_bio]">' . esc_textarea($short_bio) . '</textarea></td>';
                echo '<td>';
                echo '<input type="hidden" class="dls-author-avatar-id" name="dls_author_profiles[' . esc_attr($user_id) . '][avatar_id]" value="' . esc_attr((string) $avatar_id) . '">';
                echo '<input type="url" class="dls-author-avatar-url" style="width:100%; margin-bottom:6px" placeholder="https://..." name="dls_author_profiles[' . esc_attr($user_id) . '][avatar_url]" value="' . esc_attr($avatar_url) . '">';
                echo '<div style="display:flex; gap:6px"><button type="button" class="button dls-author-avatar-select">Select Image</button><button type="button" class="button dls-author-avatar-remove">Remove</button></div>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';

            echo '<h2 style="margin-top:28px">Guest Authors</h2>';
            echo '<table class="widefat striped">';
            echo '<thead><tr><th style="width:80px">Avatar</th><th style="width:220px">Name</th><th style="width:140px">Type</th><th>Short Bio</th><th style="width:360px">Custom Image</th></tr></thead>';
            echo '<tbody>';

            if (empty($guest_authors)) {
                echo '<tr><td colspan="5">No guest authors yet.</td></tr>';
            } else {
                foreach ($guest_authors as $term) {
                    if (!($term instanceof WP_Term)) {
                        continue;
                    }

                    $term_id = (int) $term->term_id;
                    $short_bio = dls_native_authors_get_guest_author_bio($term_id);
                    $guest_lang = dls_native_authors_get_guest_author_language($term_id);
                    $avatar_url = (string) get_term_meta($term_id, '_dls_author_avatar_url', true);
                    if ($avatar_url === '') {
                        $avatar_url = (string) get_term_meta($term_id, '_dls_guest_author_avatar_url', true);
                    }
                    $avatar_id = absint(get_term_meta($term_id, '_dls_author_avatar_id', true));
                    if ($avatar_id < 1) {
                        $avatar_id = absint(get_term_meta($term_id, '_dls_guest_author_avatar_id', true));
                    }

                    echo '<tr>';
                    echo '<td>' . dls_native_authors_get_guest_author_avatar_html($term_id, 48, 'dls-author-profile-avatar') . '</td>';
                    echo '<td><input type="text" style="width:100%" name="dls_guest_author_profiles[' . esc_attr($term_id) . '][display_name]" value="' . esc_attr($term->name) . '"></td>';
                    echo '<td><code>guest author' . ($guest_lang !== '' ? ' (' . esc_html(strtoupper($guest_lang)) . ')' : '') . '</code></td>';
                    echo '<td><textarea style="width:100%; min-height:76px" name="dls_guest_author_profiles[' . esc_attr($term_id) . '][short_bio]">' . esc_textarea($short_bio) . '</textarea></td>';
                    echo '<td>';
                    echo '<input type="hidden" class="dls-author-avatar-id" name="dls_guest_author_profiles[' . esc_attr($term_id) . '][avatar_id]" value="' . esc_attr((string) $avatar_id) . '">';
                    echo '<input type="url" class="dls-author-avatar-url" style="width:100%; margin-bottom:6px" placeholder="https://..." name="dls_guest_author_profiles[' . esc_attr($term_id) . '][avatar_url]" value="' . esc_attr($avatar_url) . '">';
                    echo '<div style="display:flex; gap:6px"><button type="button" class="button dls-author-avatar-select">Select Image</button><button type="button" class="button dls-author-avatar-remove">Remove</button></div>';
                    echo '</td>';
                    echo '</tr>';
                }
            }

            echo '</tbody></table>';
            echo '<p><button class="button button-primary" type="submit">Save Profiles</button></p>';
            echo '</form></div>';
        }
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'users_page_dls-author-profiles') {
        return;
    }

    wp_enqueue_media();
});

add_action('admin_footer-users_page_dls-author-profiles', function () {
    ?>
    <script>
    (function () {
        function findRow(el) {
            return el ? el.closest('tr') : null;
        }

        document.addEventListener('click', function (event) {
            var selectBtn = event.target.closest('.dls-author-avatar-select');
            if (selectBtn) {
                event.preventDefault();
                var row = findRow(selectBtn);
                if (!row || typeof wp === 'undefined' || !wp.media) {
                    return;
                }

                var frame = wp.media({
                    title: 'Select Author Image',
                    library: { type: 'image' },
                    button: { text: 'Use image' },
                    multiple: false
                });

                frame.on('select', function () {
                    var selection = frame.state().get('selection').first();
                    if (!selection) {
                        return;
                    }

                    var data = selection.toJSON();
                    var idField = row.querySelector('.dls-author-avatar-id');
                    var urlField = row.querySelector('.dls-author-avatar-url');

                    if (idField) {
                        idField.value = data.id ? String(data.id) : '';
                    }

                    if (urlField) {
                        urlField.value = data.url ? String(data.url) : '';
                    }
                });

                frame.open();
                return;
            }

            var removeBtn = event.target.closest('.dls-author-avatar-remove');
            if (!removeBtn) {
                return;
            }

            event.preventDefault();
            var removeRow = findRow(removeBtn);
            if (!removeRow) {
                return;
            }

            var removeIdField = removeRow.querySelector('.dls-author-avatar-id');
            var removeUrlField = removeRow.querySelector('.dls-author-avatar-url');

            if (removeIdField) {
                removeIdField.value = '';
            }

            if (removeUrlField) {
                removeUrlField.value = '';
            }
        });
    })();
    </script>
    <?php
});


if (!function_exists('dls_native_authors_restore_legacy_avatars')) {
    function dls_native_authors_restore_legacy_avatars($force = false) {
        global $wpdb;

        $migration_key = 'dls_native_authors_avatar_migrated_v1';
        $already = get_option($migration_key, []);

        if (!$force && !empty($already['completed_at'])) {
            return $already;
        }

        $stats = [
            'scanned_terms' => 0,
            'updated_users' => 0,
            'created_users' => 0,
            'completed_at'  => current_time('mysql'),
            'note'          => '',
        ];

        $rows = $wpdb->get_results(
            "
            SELECT tm.term_id, tm.meta_value AS avatar_id
            FROM {$wpdb->termmeta} tm
            INNER JOIN {$wpdb->term_taxonomy} tt
                ON tt.term_id = tm.term_id
            WHERE tt.taxonomy = 'author'
              AND tm.meta_key = 'avatar'
              AND tm.meta_value <> ''
            ",
            ARRAY_A
        );

        if (!is_array($rows) || empty($rows)) {
            $stats['note'] = 'no legacy avatar rows found';
            update_option($migration_key, $stats, false);
            return $stats;
        }

        foreach ($rows as $row) {
            $term_id = absint($row['term_id'] ?? 0);
            $avatar_id = absint($row['avatar_id'] ?? 0);

            if ($term_id < 1 || $avatar_id < 1) {
                continue;
            }

            $stats['scanned_terms']++;

            $user_id = dls_native_authors_extract_user_id_from_legacy_term($term_id);

            if ($user_id < 1) {
                $term_row = dls_native_authors_get_legacy_term_row_by_id($term_id);
                if (is_array($term_row)) {
                    $created = 0;
                    $user_id = dls_native_authors_resolve_legacy_term_user_id($term_row, $created);
                    $stats['created_users'] += absint($created);
                }
            }

            if ($user_id < 1) {
                continue;
            }

            $existing_id = absint(get_user_meta($user_id, '_dls_author_avatar_id', true));
            $existing_url = trim((string) get_user_meta($user_id, '_dls_author_avatar_url', true));

            if ($existing_id > 0 || $existing_url !== '') {
                continue;
            }

            $avatar_url = wp_get_attachment_image_url($avatar_id, 'thumbnail');
            if (!$avatar_url) {
                $avatar_url = wp_get_attachment_url($avatar_id);
            }

            if (!is_string($avatar_url) || $avatar_url === '') {
                continue;
            }

            update_user_meta($user_id, '_dls_author_avatar_id', $avatar_id);
            update_user_meta($user_id, '_dls_author_avatar_url', esc_url_raw($avatar_url));
            $stats['updated_users']++;
        }

        update_option($migration_key, $stats, false);

        return $stats;
    }
}

add_action('init', function () {
    dls_native_authors_restore_legacy_avatars(false);
}, 26);

add_action('admin_init', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!isset($_GET['dls_native_authors_restore_legacy_avatars']) || $_GET['dls_native_authors_restore_legacy_avatars'] !== '1') {
        return;
    }

    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'dls_native_authors_restore_legacy_avatars')) {
        return;
    }

    dls_native_authors_restore_legacy_avatars(true);

    wp_safe_redirect(remove_query_arg(['dls_native_authors_restore_legacy_avatars', '_wpnonce']));
    exit;
});
if (!function_exists('dls_native_authors_role_from_ppma_category')) {
    function dls_native_authors_role_from_ppma_category($category_slug, $category_name = '') {
        $haystack = strtolower(trim((string) $category_slug . ' ' . (string) $category_name));

        if ($haystack === '') {
            return 'author';
        }

        $editor_markers = ['editor', 'edited', 'редактор', 'редакторка', 'редакція', 'editing', 'ред.'];

        foreach ($editor_markers as $marker) {
            if (strpos($haystack, $marker) !== false) {
                return 'editor';
            }
        }

        return 'author';
    }
}

if (!function_exists('dls_native_authors_restore_ppma_roles')) {
    function dls_native_authors_restore_ppma_roles($force = false) {
        global $wpdb;

        $migration_key = 'dls_native_authors_ppma_roles_migrated_v2';
        $already = get_option($migration_key, []);

        if (!$force && !empty($already['completed_at'])) {
            return $already;
        }

        $stats = [
            'scanned_rows'  => 0,
            'updated_posts' => 0,
            'updated_users' => 0,
            'completed_at'  => current_time('mysql'),
            'version'       => 2,
            'note'          => '',
        ];

        $rel_table = $wpdb->prefix . 'ppma_author_relationships';
        $cat_table = $wpdb->prefix . 'ppma_author_categories';

        $rel_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $rel_table));

        if ($rel_exists !== $rel_table) {
            $stats['note'] = 'ppma_author_relationships table not found';
            update_option($migration_key, $stats, false);
            return $stats;
        }

        $categories = [];
        $cat_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $cat_table));

        if ($cat_exists === $cat_table) {
            $rows = $wpdb->get_results("SELECT id, slug, category_name FROM {$cat_table}", ARRAY_A);
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $cat_id = absint($row['id'] ?? 0);
                    if ($cat_id < 1) {
                        continue;
                    }

                    $categories[$cat_id] = [
                        'slug' => (string) ($row['slug'] ?? ''),
                        'name' => (string) ($row['category_name'] ?? ''),
                    ];
                }
            }
        }

        $relations = $wpdb->get_results(
            "
            SELECT post_id, author_term_id, category_id, category_slug
            FROM {$rel_table}
            ORDER BY post_id ASC
            ",
            ARRAY_A
        );

        if (!is_array($relations) || empty($relations)) {
            $stats['note'] = 'no ppma role relationships found';
            update_option($migration_key, $stats, false);
            return $stats;
        }

        $per_post_roles = [];

        foreach ($relations as $row) {
            $stats['scanned_rows']++;

            $post_id = absint($row['post_id'] ?? 0);
            $term_id = absint($row['author_term_id'] ?? 0);

            if ($post_id < 1 || $term_id < 1 || get_post_type($post_id) !== 'post') {
                continue;
            }

            $user_id = dls_native_authors_extract_user_id_from_legacy_term($term_id);

            if ($user_id < 1) {
                $term_row = dls_native_authors_get_legacy_term_row_by_id($term_id);
                if (is_array($term_row)) {
                    $created = 0;
                    $user_id = dls_native_authors_resolve_legacy_term_user_id($term_row, $created);
                    $stats['updated_users'] += absint($created);
                }
            }

            if ($user_id < 1) {
                continue;
            }

            $cat_id = absint($row['category_id'] ?? 0);
            $category_slug = (string) ($row['category_slug'] ?? '');
            $category_name = '';

            if ($cat_id > 0 && isset($categories[$cat_id])) {
                if ($category_slug === '') {
                    $category_slug = (string) $categories[$cat_id]['slug'];
                }

                $category_name = (string) $categories[$cat_id]['name'];
            }

            $role = dls_native_authors_role_from_ppma_category($category_slug, $category_name);

            if (!isset($per_post_roles[$post_id])) {
                $per_post_roles[$post_id] = [];
            }

            if (!isset($per_post_roles[$post_id][$user_id])) {
                $per_post_roles[$post_id][$user_id] = 'author';
            }

            if ($role === 'editor') {
                $per_post_roles[$post_id][$user_id] = 'editor';
            }
        }

        foreach ($per_post_roles as $post_id => $role_map) {
            $existing = dls_native_authors_get_post_assignments($post_id);
            $merged = [];

            foreach ($existing as $row) {
                $uid = absint($row['user_id'] ?? 0);
                if ($uid < 1) {
                    continue;
                }

                $merged[$uid] = dls_native_authors_normalize_post_role($row['post_role'] ?? 'author');
            }

            foreach ($role_map as $uid => $role) {
                $uid = absint($uid);
                if ($uid < 1) {
                    continue;
                }

                if (!isset($merged[$uid]) || $role === 'editor') {
                    $merged[$uid] = dls_native_authors_normalize_post_role($role);
                }
            }

            if (empty($merged)) {
                continue;
            }

            dls_native_authors_save_assignments_for_post($post_id, array_keys($merged), $merged);
            $stats['updated_posts']++;
        }

        update_option($migration_key, $stats, false);

        return $stats;
    }
}

add_action('init', function () {
    dls_native_authors_restore_ppma_roles(false);
}, 27);

add_action('admin_init', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!isset($_GET['dls_native_authors_restore_ppma_roles']) || $_GET['dls_native_authors_restore_ppma_roles'] !== '1') {
        return;
    }

    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'dls_native_authors_restore_ppma_roles')) {
        return;
    }

    dls_native_authors_restore_ppma_roles(true);

    wp_safe_redirect(remove_query_arg(['dls_native_authors_restore_ppma_roles', '_wpnonce']));
    exit;
});

add_action('save_post', function ($post_id, $post) {
    // Duplicate saver kept from earlier migrations; keep it inactive.
    return;

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

    if (!isset($_POST['dls_post_authors']) && !isset($_POST['dls_post_author_role'])) {
        return;
    }

    if (isset($_POST['dls_native_authors_nonce']) && !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dls_native_authors_nonce'])), 'dls_native_authors_save')) {
        return;
    }

    $selected = isset($_POST['dls_post_authors']) ? dls_native_authors_sanitize_ids(wp_unslash((array) $_POST['dls_post_authors'])) : [];
    $raw_role_map = isset($_POST['dls_post_author_role']) ? (array) wp_unslash($_POST['dls_post_author_role']) : [];
    $role_map = [];

    foreach ($raw_role_map as $id => $role) {
        $role_map[absint($id)] = dls_native_authors_normalize_post_role($role);
    }

    dls_native_authors_save_assignments_for_post($post_id, $selected, $role_map);
}, 120, 2);
