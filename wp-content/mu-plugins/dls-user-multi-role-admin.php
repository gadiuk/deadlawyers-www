<?php
/**
 * Plugin Name: DLS User Multi Role Admin
 * Description: Adds a safe admin UI for assigning additional roles to users.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_user_multi_role_get_roles')) {
    function dls_user_multi_role_get_roles() {
        if (!function_exists('get_editable_roles')) {
            return [];
        }

        $roles = get_editable_roles();

        return is_array($roles) ? $roles : [];
    }
}

if (!function_exists('dls_user_multi_role_render_field')) {
    function dls_user_multi_role_render_field($user) {
        if (!($user instanceof WP_User)) {
            return;
        }

        if (
            !current_user_can('manage_options')
            && !current_user_can('edit_users')
            && !current_user_can('promote_users')
        ) {
            return;
        }

        $roles = dls_user_multi_role_get_roles();
        if (empty($roles)) {
            return;
        }

        $current_roles = array_map('strval', (array) $user->roles);
        ?>
        <h2>Additional Roles</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Extra roles</th>
                <td>
                    <?php wp_nonce_field('dls_user_multi_role_save', 'dls_user_multi_role_nonce'); ?>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Additional Roles</span></legend>
                        <?php foreach ($roles as $role_key => $role_data) : ?>
                            <?php
                            $role_key = sanitize_key((string) $role_key);
                            $role_name = '';
                            if (is_array($role_data) && isset($role_data['name'])) {
                                $role_name = (string) $role_data['name'];
                            }
                            if ($role_key === '' || $role_name === '') {
                                continue;
                            }
                            ?>
                            <label style="display:block; margin:0 0 6px;">
                                <input
                                    type="checkbox"
                                    name="dls_additional_roles[]"
                                    value="<?php echo esc_attr($role_key); ?>"
                                    <?php checked(in_array($role_key, $current_roles, true)); ?>
                                >
                                <span><?php echo esc_html($role_name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                    <p class="description">The main WordPress role still works as usual. These checkboxes let you keep more than one role on the same user.</p>
                </td>
            </tr>
        </table>
        <?php
    }
}

add_action('show_user_profile', 'dls_user_multi_role_render_field');
add_action('edit_user_profile', 'dls_user_multi_role_render_field');

if (!function_exists('dls_user_multi_role_can_manage')) {
    function dls_user_multi_role_can_manage() {
        return current_user_can('manage_options')
            || current_user_can('edit_users')
            || current_user_can('promote_users');
    }
}

if (!function_exists('dls_user_multi_role_apply_roles')) {
    function dls_user_multi_role_apply_roles($user, $roles_to_apply) {
        if (!($user instanceof WP_User) || empty($roles_to_apply) || !is_array($roles_to_apply)) {
            return;
        }

        foreach ((array) $user->roles as $old_role) {
            $user->remove_role((string) $old_role);
        }

        $primary_role = array_shift($roles_to_apply);
        $user->add_role($primary_role);

        foreach ($roles_to_apply as $extra_role) {
            $user->add_role($extra_role);
        }
    }
}

if (!function_exists('dls_user_multi_role_admin_menu')) {
    function dls_user_multi_role_admin_menu() {
        if (!dls_user_multi_role_can_manage()) {
            return;
        }

        add_menu_page(
            'Role Manager',
            'Role Manager',
            'manage_options',
            'dls-additional-roles',
            'dls_user_multi_role_render_admin_page',
            'dashicons-admin-users',
            58
        );

        add_users_page(
            'Additional Roles',
            'Additional Roles',
            'edit_users',
            'dls-additional-roles',
            'dls_user_multi_role_render_admin_page'
        );
    }
}

add_action('admin_menu', 'dls_user_multi_role_admin_menu');

if (!function_exists('dls_user_multi_role_render_admin_page')) {
    function dls_user_multi_role_render_admin_page() {
        if (!dls_user_multi_role_can_manage()) {
            return;
        }

        $editable_roles = dls_user_multi_role_get_roles();
        $selected_user_id = isset($_GET['user_id']) ? absint($_GET['user_id']) : 0;
        $selected_user = $selected_user_id > 0 ? get_userdata($selected_user_id) : null;
        $users = get_users([
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'number'  => 5000,
        ]);
        ?>
        <div class="wrap">
            <h1>Additional Roles</h1>
            <form method="get" action="">
                <input type="hidden" name="page" value="dls-additional-roles">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="dls-role-user-id">User</label></th>
                        <td>
                            <select id="dls-role-user-id" name="user_id" style="min-width:320px;">
                                <option value="">Select user</option>
                                <?php foreach ((array) $users as $user) : ?>
                                    <?php if (!($user instanceof WP_User)) { continue; } ?>
                                    <option value="<?php echo esc_attr((string) $user->ID); ?>" <?php selected($selected_user_id, (int) $user->ID); ?>>
                                        <?php echo esc_html((string) $user->display_name . ' (' . (string) $user->user_login . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="button button-secondary">Open</button>
                        </td>
                    </tr>
                </table>
            </form>

            <?php if ($selected_user instanceof WP_User) : ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('dls_user_multi_role_save', 'dls_user_multi_role_nonce'); ?>
                    <input type="hidden" name="action" value="dls_user_multi_role_admin_save">
                    <input type="hidden" name="user_id" value="<?php echo esc_attr((string) $selected_user->ID); ?>">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">Main role</th>
                            <td><strong><?php echo esc_html(implode(', ', array_map('strval', (array) $selected_user->roles))); ?></strong></td>
                        </tr>
                        <tr>
                            <th scope="row">Extra roles</th>
                            <td>
                                <?php foreach ($editable_roles as $role_key => $role_data) : ?>
                                    <?php
                                    $role_key = sanitize_key((string) $role_key);
                                    $role_name = is_array($role_data) && isset($role_data['name']) ? (string) $role_data['name'] : '';
                                    if ($role_key === '' || $role_name === '') {
                                        continue;
                                    }
                                    ?>
                                    <label style="display:block; margin:0 0 6px;">
                                        <input
                                            type="checkbox"
                                            name="dls_additional_roles[]"
                                            value="<?php echo esc_attr($role_key); ?>"
                                            <?php checked(in_array($role_key, (array) $selected_user->roles, true)); ?>
                                        >
                                        <span><?php echo esc_html($role_name); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Save Roles'); ?>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('dls_user_multi_role_save')) {
    function dls_user_multi_role_save($user_id) {
        $user_id = absint($user_id);
        if ($user_id < 1) {
            return;
        }

        if (
            !current_user_can('edit_user', $user_id)
            && !current_user_can('promote_user', $user_id)
            && !current_user_can('manage_options')
        ) {
            return;
        }

        $nonce = isset($_POST['dls_user_multi_role_nonce']) ? sanitize_text_field(wp_unslash($_POST['dls_user_multi_role_nonce'])) : '';
        if ($nonce === '' || !wp_verify_nonce($nonce, 'dls_user_multi_role_save')) {
            return;
        }

        $editable_roles = dls_user_multi_role_get_roles();
        if (empty($editable_roles)) {
            return;
        }

        $posted_primary_role = isset($_POST['role']) ? sanitize_key(wp_unslash($_POST['role'])) : '';
        $posted_roles = isset($_POST['dls_additional_roles']) ? (array) wp_unslash($_POST['dls_additional_roles']) : [];
        $posted_roles = array_values(array_unique(array_filter(array_map('sanitize_key', $posted_roles))));

        $roles_to_apply = [];

        if ($posted_primary_role !== '' && isset($editable_roles[$posted_primary_role])) {
            $roles_to_apply[] = $posted_primary_role;
        }

        foreach ($posted_roles as $role_key) {
            if (!isset($editable_roles[$role_key]) || in_array($role_key, $roles_to_apply, true)) {
                continue;
            }

            $roles_to_apply[] = $role_key;
        }

        if (empty($roles_to_apply)) {
            return;
        }

        $user = get_userdata($user_id);
        if (!($user instanceof WP_User)) {
            return;
        }

        dls_user_multi_role_apply_roles($user, $roles_to_apply);
    }
}

add_action('personal_options_update', 'dls_user_multi_role_save');
add_action('edit_user_profile_update', 'dls_user_multi_role_save');

if (!function_exists('dls_user_multi_role_admin_save')) {
    function dls_user_multi_role_admin_save() {
        if (!dls_user_multi_role_can_manage()) {
            wp_die('You do not have permission to edit roles.');
        }

        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        dls_user_multi_role_save($user_id);

        $redirect_url = add_query_arg([
            'page'    => 'dls-additional-roles',
            'user_id' => $user_id,
        ], admin_url('users.php'));

        wp_safe_redirect($redirect_url);
        exit;
    }
}

add_action('admin_post_dls_user_multi_role_admin_save', 'dls_user_multi_role_admin_save');
