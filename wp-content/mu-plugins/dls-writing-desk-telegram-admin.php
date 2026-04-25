<?php
/**
 * Plugin Name: DLS Writing Desk Telegram Admin
 * Description: Makes Writing Desk destinations Telegram-only and adds channel access controls.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_wd_tga_access_option')) {
    function dls_wd_tga_access_option() {
        return 'dls_wd_telegram_channel_access';
    }
}

if (!function_exists('dls_wd_tga_roles')) {
    function dls_wd_tga_roles() {
        return [
            'editor' => 'Editors',
            'author' => 'Authors',
            'contributor' => 'Contributors',
        ];
    }
}

if (!function_exists('dls_wd_tga_current_role')) {
    function dls_wd_tga_current_role() {
        if (current_user_can('manage_options')) {
            return 'administrator';
        }

        $user = wp_get_current_user();
        $roles = is_array($user->roles ?? null) ? array_map('sanitize_key', $user->roles) : [];
        foreach (['editor', 'author', 'contributor'] as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        return current_user_can('edit_others_posts') ? 'editor' : 'author';
    }
}

if (!function_exists('dls_wd_tga_destinations')) {
    function dls_wd_tga_destinations() {
        if (function_exists('dls_writing_desk_get_social_destinations')) {
            return array_values(array_filter((array) dls_writing_desk_get_social_destinations(), static function ($destination) {
                return ($destination['platform'] ?? '') === 'telegram';
            }));
        }

        $stored = get_option('dls_writing_desk_destinations', []);
        if (!is_array($stored)) {
            return [];
        }

        return array_values(array_filter($stored, static function ($destination) {
            return is_array($destination) && sanitize_key((string) ($destination['platform'] ?? '')) === 'telegram';
        }));
    }
}

if (!function_exists('dls_wd_tga_access_settings')) {
    function dls_wd_tga_access_settings() {
        $stored = get_option(dls_wd_tga_access_option(), []);
        return is_array($stored) ? $stored : [];
    }
}

if (!function_exists('dls_wd_tga_user_can_channel')) {
    function dls_wd_tga_user_can_channel($channel_key) {
        $channel_key = sanitize_key((string) $channel_key);
        if ($channel_key === '') {
            return false;
        }

        if (current_user_can('manage_options')) {
            return true;
        }

        $settings = dls_wd_tga_access_settings();
        if (empty($settings[$channel_key]) || !is_array($settings[$channel_key])) {
            return true;
        }

        $role = dls_wd_tga_current_role();
        return !empty($settings[$channel_key][$role]);
    }
}

if (!function_exists('dls_wd_tga_clean_destinations')) {
    function dls_wd_tga_clean_destinations() {
        $stored = get_option('dls_writing_desk_destinations', []);
        if (!is_array($stored)) {
            return;
        }

        $clean = [];
        $changed = false;
        foreach ($stored as $row) {
            if (!is_array($row)) {
                $changed = true;
                continue;
            }

            if (sanitize_key((string) ($row['platform'] ?? '')) !== 'telegram') {
                $changed = true;
                continue;
            }

            $row['platform'] = 'telegram';
            $clean[] = $row;
        }

        if ($changed) {
            update_option('dls_writing_desk_destinations', $clean, false);
        }
    }
}

if (!function_exists('dls_wd_tga_admin_init')) {
    function dls_wd_tga_admin_init() {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if (in_array($page, ['dls-writing-desk-destinations', 'dls-writing-desk-telegram', 'dls-writing-desk-access'], true)) {
            dls_wd_tga_clean_destinations();
        }
    }
}
add_action('admin_init', 'dls_wd_tga_admin_init', 3);

if (!function_exists('dls_wd_tga_filter_destination_save')) {
    function dls_wd_tga_filter_destination_save() {
        if (empty($_POST['dls_writing_desk_destinations']) || !is_array($_POST['dls_writing_desk_destinations'])) {
            return;
        }

        foreach ($_POST['dls_writing_desk_destinations'] as $index => $row) {
            if (!is_array($row)) {
                unset($_POST['dls_writing_desk_destinations'][$index]);
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $destination = trim((string) ($row['destination'] ?? ''));
            $token = trim((string) ($row['token'] ?? ''));

            if ($name === '' && $destination === '' && $token === '') {
                unset($_POST['dls_writing_desk_destinations'][$index]);
                continue;
            }

            $_POST['dls_writing_desk_destinations'][$index]['platform'] = 'telegram';
        }
    }
}
add_action('admin_post_dls_writing_desk_destinations_save', 'dls_wd_tga_filter_destination_save', 0);

if (!function_exists('dls_wd_tga_save_access')) {
    function dls_wd_tga_save_access() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $payload = isset($_POST['dls_wd_telegram_channel_access']) ? (array) wp_unslash($_POST['dls_wd_telegram_channel_access']) : [];
        $roles = array_keys(dls_wd_tga_roles());
        $clean = [];

        foreach (dls_wd_tga_destinations() as $destination) {
            $key = sanitize_key((string) ($destination['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            foreach ($roles as $role) {
                $clean[$key][$role] = !empty($payload[$key][$role]) ? 1 : 0;
            }
        }

        update_option(dls_wd_tga_access_option(), $clean, false);
    }
}
add_action('admin_post_dls_writing_desk_access_save', 'dls_wd_tga_save_access', 0);

if (!function_exists('dls_wd_tga_filter_telegram_save')) {
    function dls_wd_tga_filter_telegram_save() {
        if (empty($_POST['dls_writing_desk_social']) || !is_array($_POST['dls_writing_desk_social'])) {
            return;
        }

        foreach ($_POST['dls_writing_desk_social'] as $key => $row) {
            $clean_key = sanitize_key((string) $key);
            if (!dls_wd_tga_user_can_channel($clean_key) && is_array($row)) {
                $_POST['dls_writing_desk_social'][$key]['enabled'] = '';
            }
        }
    }
}
add_action('admin_post_dls_writing_desk_telegram_save', 'dls_wd_tga_filter_telegram_save', 0);

if (!function_exists('dls_wd_tga_rename_menu')) {
    function dls_wd_tga_rename_menu() {
        global $submenu;

        if (!is_array($submenu)) {
            return;
        }

        foreach ($submenu as $parent => $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $index => $item) {
                if (($item[2] ?? '') === 'dls-writing-desk-destinations') {
                    $submenu[$parent][$index][0] = 'Telegram';
                }
            }
        }
    }
}
add_action('admin_menu', 'dls_wd_tga_rename_menu', 999);

if (!function_exists('dls_wd_tga_admin_assets')) {
    function dls_wd_tga_admin_assets() {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if (!in_array($page, ['dls-writing-desk-destinations', 'dls-writing-desk-telegram', 'dls-writing-desk-access'], true)) {
            return;
        }

        $destinations = dls_wd_tga_destinations();
        $access = dls_wd_tga_access_settings();
        $allowed = [];
        foreach ($destinations as $destination) {
            $key = sanitize_key((string) ($destination['key'] ?? ''));
            if ($key !== '') {
                $allowed[$key] = dls_wd_tga_user_can_channel($key) ? 1 : 0;
            }
        }

        wp_enqueue_script('jquery');
        wp_register_style('dls-wd-telegram-admin', false, [], '1.0.1');
        wp_enqueue_style('dls-wd-telegram-admin');
        wp_add_inline_style('dls-wd-telegram-admin', dls_wd_tga_css());
        wp_add_inline_script('jquery', 'window.dlsWdTelegramAdmin = ' . wp_json_encode([
            'page' => $page,
            'roles' => dls_wd_tga_roles(),
            'destinations' => $destinations,
            'access' => $access,
            'allowed' => $allowed,
        ]) . ';', 'before');
        wp_add_inline_script('jquery', dls_wd_tga_js());
    }
}
add_action('admin_enqueue_scripts', 'dls_wd_tga_admin_assets', 95);

if (!function_exists('dls_wd_tga_css')) {
    function dls_wd_tga_css() {
        return <<<'CSS'
.dls-wd-hidden-social-platform,
.dls-wd-disallowed-channel,
.dls-wd-telegram-destinations-table th:first-child,
.dls-wd-telegram-destinations-table td:first-child {
  display: none !important;
}
.dls-wd-telegram-destinations-table th,
.dls-wd-telegram-destinations-table td {
  vertical-align: middle;
}
.dls-wd-telegram-destinations-table td:nth-child(5),
.dls-wd-telegram-destinations-table td:nth-child(6) {
  white-space: nowrap;
}
.dls-wd-telegram-access {
  background: #fffaf3;
  border: 1px solid #e5d9ca;
  border-radius: 18px;
  margin: 24px 0;
  padding: 18px;
}
.dls-wd-telegram-access h2 {
  margin: 0 0 8px;
}
.dls-wd-telegram-access p {
  color: #806b58;
  margin: 0 0 14px;
}
.dls-wd-telegram-access table {
  border-collapse: collapse;
  width: 100%;
}
.dls-wd-telegram-access th,
.dls-wd-telegram-access td {
  border-top: 1px solid #eadfce;
  padding: 10px;
  text-align: left;
}
.dls-wd-telegram-access label {
  font-weight: 700;
}
.dls-wd-telegram-only-note {
  background: #fff7e8;
  border: 1px solid #ead8b8;
  border-radius: 12px;
  color: #765d45;
  font-size: 13px;
  font-weight: 700;
  margin: 10px 0 16px;
  padding: 10px 12px;
}
CSS;
    }
}

if (!function_exists('dls_wd_tga_js')) {
    function dls_wd_tga_js() {
        return <<<'JS'
(function () {
  var data = window.dlsWdTelegramAdmin || {};
  var destinationObserver = null;
  var forceQueued = false;

  function replaceText(value) {
    return String(value || '')
      .replace(/Social Destinations/g, 'Telegram')
      .replace(/Connect pages and channels/g, 'Telegram channels')
      .replace(/Add each Facebook page, LinkedIn page, or Telegram channel here\. These connections feed the Writing Desk\./g, 'Add Telegram channels here. These connections feed the Writing Desk.')
      .replace(/Facebook page, LinkedIn page, or Telegram channel/g, 'Telegram channel')
      .replace(/Facebook and LinkedIn destinations are hidden and will not be saved\./g, 'Only Telegram channels are available here.')
      .replace(/Page \/ Channel/g, 'Telegram channel')
      .replace(/Access Token \/ Bot Token/g, 'Bot token')
      .replace(/Add Destination/g, 'Add Telegram Channel')
      .replace(/Save Destinations/g, 'Save Telegram')
      .replace(/Destinations saved\./g, 'Telegram channels saved.');
  }

  function textReplace(root) {
    if (!root) return;
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
    var node;
    while ((node = walker.nextNode())) {
      var next = replaceText(node.nodeValue);
      if (next !== node.nodeValue) node.nodeValue = next;
    }
    document.title = replaceText(document.title);
  }

  function findDestinationsForm() {
    var actionForm = document.querySelector('form[action*="dls_writing_desk_destinations_save"]');
    if (actionForm) return actionForm;
    var platform = document.querySelector('select[name^="dls_writing_desk_destinations["][name$="[platform]"]');
    return platform ? platform.closest('form') : null;
  }

  function markDestinationsTable(form) {
    if (!form) return;
    var platform = form.querySelector('select[name^="dls_writing_desk_destinations["][name$="[platform]"]');
    var table = platform ? platform.closest('table') : form.querySelector('table');
    if (!table) return;

    table.classList.add('dls-wd-telegram-destinations-table');
    var headings = table.querySelectorAll('thead th, tr:first-child th');
    var labels = ['', 'Name', 'Telegram channel', 'Bot token', 'Active', 'Preview'];
    headings.forEach(function (heading, index) {
      if (labels[index]) heading.textContent = labels[index];
    });
  }

  function forceTelegramDestinations() {
    var form = findDestinationsForm();
    if (!form || data.page !== 'dls-writing-desk-destinations') return;

    markDestinationsTable(form);
    textReplace(form);

    form.querySelectorAll('select[name^="dls_writing_desk_destinations["][name$="[platform]"]').forEach(function (select) {
      select.value = 'telegram';
      Array.prototype.slice.call(select.options).forEach(function (option) {
        if (option.value !== 'telegram') option.remove();
      });
      var cell = select.closest('td') || select.parentElement;
      if (cell) cell.classList.add('dls-wd-hidden-social-platform');
    });

    form.querySelectorAll('input[name^="dls_writing_desk_destinations["][name$="[platform]"]').forEach(function (input) {
      input.value = 'telegram';
    });

    form.querySelectorAll('input[name^="dls_writing_desk_destinations["][name$="[name]"]').forEach(function (input) {
      input.placeholder = 'Telegram channel name';
    });

    form.querySelectorAll('input[name^="dls_writing_desk_destinations["][name$="[destination]"]').forEach(function (input) {
      input.placeholder = '@channel or channel id';
    });

    form.querySelectorAll('input[name^="dls_writing_desk_destinations["][name$="[token]"]').forEach(function (input) {
      input.placeholder = 'Telegram bot token';
    });

    if (!document.querySelector('.dls-wd-telegram-only-note')) {
      var note = document.createElement('div');
      note.className = 'dls-wd-telegram-only-note';
      note.textContent = 'This page is Telegram-only. Add Telegram channels and bot tokens here.';
      form.insertBefore(note, form.firstChild);
    }
  }

  function queueForceTelegramDestinations() {
    if (forceQueued) return;
    forceQueued = true;
    window.setTimeout(function () {
      forceQueued = false;
      forceTelegramDestinations();
    }, 0);
  }

  function watchDestinationRows() {
    if (data.page !== 'dls-writing-desk-destinations' || destinationObserver) return;
    var form = findDestinationsForm();
    if (!form) return;

    destinationObserver = new MutationObserver(queueForceTelegramDestinations);
    destinationObserver.observe(form, { childList: true, subtree: true });

    form.addEventListener('click', function (event) {
      var button = event.target.closest('button, a, input[type="button"], input[type="submit"]');
      if (!button) return;
      if (/add/i.test(button.textContent || button.value || '')) {
        window.setTimeout(forceTelegramDestinations, 20);
      }
    });
  }

  function addAccessSection() {
    if (data.page !== 'dls-writing-desk-access' || document.querySelector('.dls-wd-telegram-access')) return;
    var destinations = Array.isArray(data.destinations) ? data.destinations : [];
    var roles = data.roles || {};
    var access = data.access || {};
    var form = document.querySelector('form[action*="dls_writing_desk_access_save"], form');
    if (!form) return;

    var html = '<section class="dls-wd-telegram-access"><h2>Telegram channel access</h2>' +
      '<p>Administrators always have access. Use this for editors, authors and contributors.</p>';

    if (!destinations.length) {
      html += '<p>No Telegram channels yet. Add them on the Telegram page.</p></section>';
    } else {
      html += '<table><thead><tr><th>Channel</th>';
      Object.keys(roles).forEach(function (role) { html += '<th>' + roles[role] + '</th>'; });
      html += '</tr></thead><tbody>';
      destinations.forEach(function (destination) {
        var key = destination.key || '';
        var channelAccess = access[key] || {};
        html += '<tr><td><strong>' + escapeHtml(destination.name || key) + '</strong><br><small>' + escapeHtml(destination.destination || '') + '</small></td>';
        Object.keys(roles).forEach(function (role) {
          var checked = channelAccess.hasOwnProperty(role) ? !!channelAccess[role] : true;
          html += '<td><label><input type="checkbox" name="dls_wd_telegram_channel_access[' + key + '][' + role + ']" value="1"' + (checked ? ' checked' : '') + '> Allow</label></td>';
        });
        html += '</tr>';
      });
      html += '</tbody></table></section>';
    }

    var submit = form.querySelector('button[type="submit"], input[type="submit"]');
    if (submit && submit.parentNode) submit.parentNode.insertAdjacentHTML('beforebegin', html);
    else form.insertAdjacentHTML('beforeend', html);
  }

  function hideDisallowedChannels() {
    if (data.page !== 'dls-writing-desk-telegram') return;
    var allowed = data.allowed || {};
    document.querySelectorAll('.dls-writing-desk__social-card').forEach(function (card) {
      var input = card.querySelector('input[name^="dls_writing_desk_social["][name$="[enabled]"]');
      var match = input && input.name ? input.name.match(/^dls_writing_desk_social\[([^\]]+)\]/) : null;
      var key = match ? match[1] : '';
      if (key && allowed.hasOwnProperty(key) && !allowed[key]) {
        card.classList.add('dls-wd-disallowed-channel');
        input.checked = false;
      }
    });
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (char) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char];
    });
  }

  function init() {
    textReplace(document.body);
    forceTelegramDestinations();
    watchDestinationRows();
    addAccessSection();
    hideDisallowedChannels();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
JS;
    }
}
