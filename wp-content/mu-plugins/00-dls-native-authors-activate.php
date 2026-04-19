<?php
/**
 * Plugin Name: DLS Native Authors Activator
 * Description: Enables native author MU plugins unless explicitly disabled in wp-config.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Allow wp-config.php to disable with: define('DLS_NATIVE_AUTHORS_ACTIVE', false);
if (!defined('DLS_NATIVE_AUTHORS_ACTIVE')) {
    define('DLS_NATIVE_AUTHORS_ACTIVE', true);
}

if (!function_exists('dls_native_authors_activate_collect_legacy_post_ids_by_term')) {
    /**
     * Collect post IDs linked to a legacy PublishPress author term.
     *
     * @param int $term_id Legacy author term ID.
     * @return int[]
     */
    function dls_native_authors_activate_collect_legacy_post_ids_by_term($term_id) {
        global $wpdb;

        $term_id = absint($term_id);
        if ($term_id < 1) {
            return [];
        }

        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "
                SELECT DISTINCT p.ID
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->term_relationships} tr
                    ON tr.object_id = p.ID
                INNER JOIN {$wpdb->term_taxonomy} tt
                    ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tt.taxonomy = 'author'
                  AND tt.term_id = %d
                  AND p.post_type = 'post'
                  AND p.post_status <> 'trash'
                ",
                $term_id
            )
        );

        $rel_table = $wpdb->prefix . 'ppma_author_relationships';
        $has_rel_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $rel_table));

        if ($has_rel_table === $rel_table) {
            $ppma_post_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "
                    SELECT DISTINCT post_id
                    FROM {$rel_table}
                    WHERE author_term_id = %d
                    ",
                    $term_id
                )
            );

            if (is_array($ppma_post_ids)) {
                $post_ids = array_merge((array) $post_ids, $ppma_post_ids);
            }
        }

        $post_ids = array_values(array_unique(array_map('absint', (array) $post_ids)));

        return array_values(array_filter($post_ids));
    }
}

if (!function_exists('dls_native_authors_activate_restore_author_by_slug')) {
    /**
     * Restore one native author user by slug and re-attach historical post assignments.
     *
     * @param string $slug Author slug/login.
     * @return array<string,mixed>
     */
    function dls_native_authors_activate_restore_author_by_slug($slug) {
        $raw_slug = trim((string) $slug);
        if ($raw_slug === '') {
            return ['ok' => false, 'reason' => 'empty-slug'];
        }

        $login_candidate = sanitize_user($raw_slug, true);
        if ($login_candidate === '') {
            $login_candidate = 'author_restored';
        }

        $slug_candidates = array_values(array_unique(array_filter([
            $raw_slug,
            str_replace('-', '_', $raw_slug),
            str_replace('_', '-', $raw_slug),
            sanitize_title($raw_slug),
        ])));

        $user = get_user_by('login', $login_candidate);
        if (!($user instanceof WP_User)) {
            foreach ($slug_candidates as $candidate) {
                $candidate = sanitize_title((string) $candidate);
                if ($candidate === '') {
                    continue;
                }

                $by_slug = get_user_by('slug', $candidate);
                if ($by_slug instanceof WP_User) {
                    $user = $by_slug;
                    break;
                }
            }
        }

        $term_row = null;
        if (function_exists('dls_native_authors_get_legacy_term_row_by_slug')) {
            foreach ($slug_candidates as $candidate) {
                $row = dls_native_authors_get_legacy_term_row_by_slug((string) $candidate);
                if (is_array($row) && !empty($row['term_id'])) {
                    $term_row = $row;
                    break;
                }
            }
        }

        $created_users = 0;

        if (!($user instanceof WP_User) && is_array($term_row) && function_exists('dls_native_authors_resolve_legacy_term_user_id')) {
            $user_id = absint(dls_native_authors_resolve_legacy_term_user_id($term_row, $created_users));
            if ($user_id > 0) {
                $user = get_userdata($user_id);
            }
        }

        if (!($user instanceof WP_User)) {
            $display_name = trim(str_replace(['_', '-'], ' ', $raw_slug));
            if ($display_name === '') {
                $display_name = $login_candidate;
            }

            if (username_exists($login_candidate)) {
                $existing = get_user_by('login', $login_candidate);
                if ($existing instanceof WP_User) {
                    $user = $existing;
                }
            } else {
                $inserted = wp_insert_user([
                    'user_login'   => $login_candidate,
                    'user_pass'    => wp_generate_password(32, true, true),
                    'display_name' => $display_name,
                    'nickname'     => $display_name,
                    'role'         => 'author',
                    'user_email'   => $login_candidate . '@deadlawyers.local',
                ]);

                if (!is_wp_error($inserted)) {
                    $created_users++;
                    $user = get_userdata((int) $inserted);
                }
            }
        }

        if (!($user instanceof WP_User)) {
            return ['ok' => false, 'reason' => 'unable-to-create-user'];
        }

        $user_id = (int) $user->ID;
        $updated_posts = 0;
        $scanned_posts = 0;

        if (is_array($term_row) && !empty($term_row['term_id'])) {
            $term_id = absint($term_row['term_id']);
            $post_ids = dls_native_authors_activate_collect_legacy_post_ids_by_term($term_id);

            foreach ($post_ids as $post_id) {
                $post_id = absint($post_id);
                if ($post_id < 1 || get_post_type($post_id) !== 'post') {
                    continue;
                }

                $scanned_posts++;

                if (function_exists('dls_native_authors_get_stored_ids') && function_exists('dls_native_authors_store_ids')) {
                    $existing_ids = dls_native_authors_get_stored_ids($post_id);
                    $merged_ids = array_values(array_unique(array_map('absint', array_merge((array) $existing_ids, [$user_id]))));

                    if ($merged_ids !== $existing_ids) {
                        dls_native_authors_store_ids($post_id, $merged_ids);
                        $updated_posts++;
                    }
                }

                if (function_exists('dls_native_authors_get_post_assignments') && function_exists('dls_native_authors_save_assignments_for_post')) {
                    $assignments = dls_native_authors_get_post_assignments($post_id);
                    $role_map = [];

                    foreach ((array) $assignments as $assignment) {
                        $uid = absint($assignment['user_id'] ?? 0);
                        if ($uid > 0) {
                            $role_map[$uid] = 'author';
                        }
                    }

                    if (!isset($role_map[$user_id])) {
                        $role_map[$user_id] = 'author';
                        dls_native_authors_save_assignments_for_post($post_id, array_keys($role_map), $role_map);
                    }
                }
            }
        }

        return [
            'ok'            => true,
            'user_id'       => $user_id,
            'user_login'    => (string) $user->user_login,
            'created_users' => $created_users,
            'scanned_posts' => $scanned_posts,
            'updated_posts' => $updated_posts,
        ];
    }
}

add_action('muplugins_loaded', function () {
    if (!defined('DLS_NATIVE_AUTHORS_ACTIVE') || DLS_NATIVE_AUTHORS_ACTIVE !== true) {
        return;
    }

    if (
        !function_exists('dls_native_authors_get_legacy_term_row_by_slug')
        || !function_exists('dls_native_authors_resolve_legacy_term_user_id')
        || !function_exists('dls_native_authors_get_stored_ids')
        || !function_exists('dls_native_authors_store_ids')
    ) {
        return;
    }

    $option_key = 'dls_native_authors_restore_carpe_diem_v1';
    $already = get_option($option_key, []);

    if (is_array($already) && !empty($already['ok'])) {
        return;
    }

    $result = dls_native_authors_activate_restore_author_by_slug('carpe_diem');
    update_option($option_key, $result, false);
}, 120);
