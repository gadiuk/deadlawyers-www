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

if (!function_exists('dls_native_authors_restore_carpe_diem_once')) {
    /**
     * Restore the legacy "carpe_diem" author account and attach it back to legacy-authored posts.
     * Runs once after deploy and stores completion stats in an option.
     *
     * @return void
     */
    function dls_native_authors_restore_carpe_diem_once() {
        if (!defined('DLS_NATIVE_AUTHORS_ACTIVE') || DLS_NATIVE_AUTHORS_ACTIVE !== true) {
            return;
        }

        $option_key = 'dls_native_authors_restore_carpe_diem_v1';
        $state = get_option($option_key, []);

        if (is_array($state) && !empty($state['completed_at'])) {
            return;
        }

        $stats = [
            'completed_at'  => current_time('mysql'),
            'user_id'       => 0,
            'term_id'       => 0,
            'updated_posts' => 0,
            'created_user'  => false,
            'note'          => '',
        ];

        $slugs = ['carpe_diem', 'carpe-diem'];
        $user = get_user_by('login', 'carpe_diem');

        if (!($user instanceof WP_User)) {
            foreach ($slugs as $slug) {
                $candidate = get_user_by('slug', $slug);
                if ($candidate instanceof WP_User) {
                    $user = $candidate;
                    break;
                }
            }
        }

        $term_row = null;
        if (function_exists('dls_native_authors_get_legacy_term_row_by_slug')) {
            foreach ($slugs as $slug) {
                $candidate = dls_native_authors_get_legacy_term_row_by_slug($slug);
                if (is_array($candidate)) {
                    $term_row = $candidate;
                    break;
                }
            }
        }

        if (!($user instanceof WP_User) && is_array($term_row) && function_exists('dls_native_authors_resolve_legacy_term_user_id')) {
            $created = 0;
            $resolved_user_id = absint(dls_native_authors_resolve_legacy_term_user_id($term_row, $created));
            if ($resolved_user_id > 0) {
                $resolved_user = get_userdata($resolved_user_id);
                if ($resolved_user instanceof WP_User) {
                    $user = $resolved_user;
                    $stats['created_user'] = $created > 0;
                }
            }
        }

        if (!($user instanceof WP_User)) {
            $email = 'carpe_diem@deadlawyers.local';
            $existing_by_email = get_user_by('email', $email);

            if ($existing_by_email instanceof WP_User) {
                $user = $existing_by_email;
            } else {
                $new_user_id = wp_insert_user([
                    'user_login'   => 'carpe_diem',
                    'user_pass'    => wp_generate_password(24, true, true),
                    'display_name' => 'carpe_diem',
                    'nickname'     => 'carpe_diem',
                    'role'         => 'author',
                    'user_email'   => $email,
                    'user_nicename'=> 'carpe_diem',
                ]);

                if (!is_wp_error($new_user_id)) {
                    $new_user = get_userdata(absint($new_user_id));
                    if ($new_user instanceof WP_User) {
                        $user = $new_user;
                        $stats['created_user'] = true;
                    }
                }
            }
        }

        if (!($user instanceof WP_User)) {
            $stats['note'] = 'Unable to resolve or create carpe_diem user.';
            update_option($option_key, $stats, false);
            return;
        }

        $stats['user_id'] = (int) $user->ID;

        $term_id = 0;
        if (is_array($term_row)) {
            $term_id = absint($term_row['term_id'] ?? 0);
        }

        if ($term_id < 1 && taxonomy_exists('author')) {
            foreach ($slugs as $slug) {
                $term = get_term_by('slug', $slug, 'author');
                if ($term instanceof WP_Term) {
                    $term_id = (int) $term->term_id;
                    break;
                }
            }
        }

        if ($term_id > 0) {
            update_term_meta($term_id, 'user_id', (int) $user->ID);
            $stats['term_id'] = $term_id;
        }

        $post_ids = [];
        if ($term_id > 0) {
            global $wpdb;
            $post_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "
                    SELECT DISTINCT tr.object_id
                    FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt
                        ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    WHERE tt.taxonomy = %s
                      AND tt.term_id = %d
                    ",
                    'author',
                    $term_id
                )
            );
        }

        if (!is_array($post_ids)) {
            $post_ids = [];
        }

        $post_ids = array_values(array_filter(array_map('absint', $post_ids)));

        foreach ($post_ids as $post_id) {
            if ($post_id < 1) {
                continue;
            }

            $updated = false;

            if (
                function_exists('dls_native_authors_get_post_assignments')
                && function_exists('dls_native_authors_save_assignments_for_post')
            ) {
                $existing = (array) dls_native_authors_get_post_assignments($post_id);
                $selected = [];
                $role_map = [];

                foreach ($existing as $row) {
                    $existing_user_id = absint($row['user_id'] ?? 0);
                    if ($existing_user_id < 1) {
                        continue;
                    }

                    $selected[] = $existing_user_id;
                    $role_map[$existing_user_id] = (($row['post_role'] ?? 'author') === 'editor') ? 'editor' : 'author';
                }

                if (!in_array((int) $user->ID, $selected, true)) {
                    $selected[] = (int) $user->ID;
                    $role_map[(int) $user->ID] = 'author';
                    $updated = true;
                }

                dls_native_authors_save_assignments_for_post($post_id, $selected, $role_map);
            } else {
                $stored = get_post_meta($post_id, '_dls_post_authors', true);
                $ids = is_array($stored) ? $stored : [];
                $ids = array_values(array_filter(array_map('absint', $ids)));

                if (!in_array((int) $user->ID, $ids, true)) {
                    $ids[] = (int) $user->ID;
                    update_post_meta($post_id, '_dls_post_authors', $ids);
                    add_post_meta($post_id, '_dls_post_author', (int) $user->ID, false);
                    $updated = true;
                }
            }

            if ($updated) {
                $stats['updated_posts']++;
            }
        }

        update_option($option_key, $stats, false);
    }
}
add_action('init', 'dls_native_authors_restore_carpe_diem_once', 30);
