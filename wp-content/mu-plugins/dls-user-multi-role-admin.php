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
        if (!($user instanceof WP_User) || !current_user_can('promote_users')) {
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

if (!function_exists('dls_user_multi_role_save')) {
    function dls_user_multi_role_save($user_id) {
        $user_id = absint($user_id);
        if ($user_id < 1) {
            return;
        }

        if (!current_user_can('promote_user', $user_id) || !current_user_can('promote_users')) {
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

add_action('personal_options_update', 'dls_user_multi_role_save');
add_action('edit_user_profile_update', 'dls_user_multi_role_save');
