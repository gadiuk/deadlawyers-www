<?php
/**
 * Plugin Name: DLS Writing Desk Telegram Channel Footers
 * Description: Adds per-channel Telegram footer controls for Writing Desk broadcasts.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_wd_tg_cf_meta_key')) {
    function dls_wd_tg_cf_meta_key() {
        return '_dls_wd_tg_channel_footers';
    }
}

if (!function_exists('dls_wd_tg_cf_sanitize_rows')) {
    function dls_wd_tg_cf_sanitize_rows($rows) {
        $items = [];

        foreach ((array) $rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = sanitize_text_field((string) ($row['label'] ?? ''));
            $url = esc_url_raw((string) ($row['url'] ?? ''));
            if ($label === '') {
                continue;
            }

            $items[] = [
                'label' => $label,
                'url' => $url,
            ];
        }

        return $items;
    }
}

if (!function_exists('dls_wd_tg_cf_get_settings')) {
    function dls_wd_tg_cf_get_settings($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return [];
        }

        $stored = get_post_meta($post_id, dls_wd_tg_cf_meta_key(), true);
        return is_array($stored) ? $stored : [];
    }
}

if (!function_exists('dls_wd_tg_cf_save_from_post')) {
    function dls_wd_tg_cf_save_from_post() {
        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? 0);
        if ($post_id < 1 || !current_user_can('edit_post', $post_id)) {
            return;
        }

        $payload = isset($_POST['dls_wd_tg_footers']) ? (array) wp_unslash($_POST['dls_wd_tg_footers']) : [];
        $clean = [];

        foreach ($payload as $key => $row) {
            $key = sanitize_key((string) $key);
            if ($key === '' || !is_array($row)) {
                continue;
            }

            $custom = !empty($row['custom']) ? 1 : 0;
            $rows = dls_wd_tg_cf_sanitize_rows($row['rows'] ?? []);

            if (!$custom && empty($rows)) {
                continue;
            }

            $clean[$key] = [
                'custom' => $custom,
                'rows' => $rows,
            ];
        }

        if (empty($clean)) {
            delete_post_meta($post_id, dls_wd_tg_cf_meta_key());
            return;
        }

        update_post_meta($post_id, dls_wd_tg_cf_meta_key(), $clean);
    }
}
add_action('admin_post_dls_writing_desk_telegram_save', 'dls_wd_tg_cf_save_from_post', 1);

if (!function_exists('dls_wd_tg_cf_set_current_post')) {
    function dls_wd_tg_cf_set_current_post($post_id) {
        $GLOBALS['dls_wd_tg_cf_current_post_id'] = absint($post_id);
    }
}
add_action('dls_writing_desk_telegram_scheduled_send', 'dls_wd_tg_cf_set_current_post', 1, 1);

if (!function_exists('dls_wd_tg_cf_destination_key_from_chat')) {
    function dls_wd_tg_cf_destination_key_from_chat($chat_id) {
        $chat_id = trim((string) $chat_id);
        if ($chat_id === '' || !function_exists('dls_writing_desk_get_social_destinations')) {
            return '';
        }

        foreach ((array) dls_writing_desk_get_social_destinations() as $destination) {
            if (($destination['platform'] ?? '') !== 'telegram') {
                continue;
            }

            if (trim((string) ($destination['destination'] ?? '')) === $chat_id) {
                return sanitize_key((string) ($destination['key'] ?? ''));
            }
        }

        return '';
    }
}

if (!function_exists('dls_wd_tg_cf_footer_text')) {
    function dls_wd_tg_cf_footer_text($rows) {
        $parts = [];

        foreach ((array) $rows as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            if ($label === '') {
                continue;
            }

            $parts[] = $url !== ''
                ? '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>'
                : esc_html($label);
        }

        return implode(' | ', $parts);
    }
}

if (!function_exists('dls_wd_tg_cf_remove_shared_footer')) {
    function dls_wd_tg_cf_remove_shared_footer($text) {
        $text = (string) $text;
        if (!function_exists('dls_writing_desk_telegram_footer_text')) {
            return $text;
        }

        $footer = trim((string) dls_writing_desk_telegram_footer_text());
        if ($footer === '') {
            return $text;
        }

        $plain_footer = html_entity_decode($footer, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        foreach ([$footer, $plain_footer] as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && substr(trim($text), -strlen($candidate)) === $candidate) {
                return rtrim(substr(trim($text), 0, -strlen($candidate)));
            }
        }

        return $text;
    }
}

if (!function_exists('dls_wd_tg_cf_filter_request')) {
    function dls_wd_tg_cf_filter_request($args, $url) {
        if (strpos((string) $url, 'https://api.telegram.org/bot') !== 0 || strpos((string) $url, '/sendMessage') === false) {
            return $args;
        }

        if (empty($args['body']) || !is_array($args['body']) || empty($args['body']['text'])) {
            return $args;
        }

        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? ($GLOBALS['dls_wd_tg_cf_current_post_id'] ?? 0));
        if ($post_id < 1) {
            return $args;
        }

        $destination_key = dls_wd_tg_cf_destination_key_from_chat($args['body']['chat_id'] ?? '');
        if ($destination_key === '') {
            return $args;
        }

        $settings = dls_wd_tg_cf_get_settings($post_id);
        if (empty($settings[$destination_key]) || empty($settings[$destination_key]['custom'])) {
            return $args;
        }

        $text = dls_wd_tg_cf_remove_shared_footer((string) $args['body']['text']);
        $footer = dls_wd_tg_cf_footer_text($settings[$destination_key]['rows'] ?? []);

        $args['body']['text'] = $footer !== '' ? trim($text . "\n\n" . $footer) : trim($text);
        $args['body']['parse_mode'] = 'HTML';

        return $args;
    }
}
add_filter('http_request_args', 'dls_wd_tg_cf_filter_request', 40, 2);

if (!function_exists('dls_wd_tg_cf_admin_assets')) {
    function dls_wd_tg_cf_admin_assets() {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if ($page !== 'dls-writing-desk-telegram') {
            return;
        }

        $post_id = absint($_GET['desk_post'] ?? 0);
        $settings = dls_wd_tg_cf_get_settings($post_id);

        wp_enqueue_script('jquery');
        wp_register_style('dls-wd-tg-channel-footers', false, [], '1.0.0');
        wp_enqueue_style('dls-wd-tg-channel-footers');
        wp_add_inline_style('dls-wd-tg-channel-footers', dls_wd_tg_cf_css());
        wp_add_inline_script('jquery', 'window.dlsWdTgChannelFooters = ' . wp_json_encode($settings) . ';', 'before');
        wp_add_inline_script('jquery', dls_wd_tg_cf_js());
    }
}
add_action('admin_enqueue_scripts', 'dls_wd_tg_cf_admin_assets', 35);

if (!function_exists('dls_wd_tg_cf_css')) {
    function dls_wd_tg_cf_css() {
        return <<<'CSS'
.dls-tg-channel-footer {
  border-top: 1px solid #eadfce;
  margin-top: 16px;
  padding-top: 14px;
}
.dls-tg-channel-footer__head {
  align-items: center;
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 10px;
}
.dls-tg-channel-footer__head strong {
  color: #211814;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: .08em;
}
.dls-tg-channel-footer__toggle {
  align-items: center;
  display: inline-flex;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
}
.dls-tg-channel-footer__row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr) auto;
  gap: 8px;
  margin-bottom: 8px;
}
.dls-tg-channel-footer__row input {
  border: 1px solid #d9cdbd;
  border-radius: 10px;
  min-width: 0;
  padding: 8px 10px;
  width: 100%;
}
.dls-tg-channel-footer button {
  border: 1px solid #d7cdc1;
  background: #fffaf3;
  border-radius: 999px;
  color: #231b16;
  cursor: pointer;
  font-size: 12px;
  font-weight: 700;
  line-height: 1;
  padding: 8px 10px;
}
.dls-tg-channel-footer__remove {
  color: #9a2a16 !important;
}
.dls-tg-channel-footer__note {
  color: #806b58;
  font-size: 12px;
  line-height: 1.35;
  margin: 8px 0 0;
}
CSS;
    }
}

if (!function_exists('dls_wd_tg_cf_js')) {
    function dls_wd_tg_cf_js() {
        return <<<'JS'
(function () {
  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (char) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char];
    });
  }

  function channelKey(card) {
    var input = card.querySelector('input[name^="dls_writing_desk_social["][name$="[enabled]"]');
    var match = input && input.name ? input.name.match(/^dls_writing_desk_social\[([^\]]+)\]/) : null;
    return match ? match[1] : '';
  }

  function rowHtml(key, index, row) {
    row = row || {};
    return '<div class="dls-tg-channel-footer__row">' +
      '<input type="text" name="dls_wd_tg_footers[' + key + '][rows][' + index + '][label]" placeholder="Label" value="' + escapeHtml(row.label) + '">' +
      '<input type="url" name="dls_wd_tg_footers[' + key + '][rows][' + index + '][url]" placeholder="https://..." value="' + escapeHtml(row.url) + '">' +
      '<button type="button" class="dls-tg-channel-footer__remove">Remove</button>' +
      '</div>';
  }

  function setupFooter(card) {
    if (card.querySelector('.dls-tg-channel-footer')) return;
    var key = channelKey(card);
    if (!key) return;

    var stored = (window.dlsWdTgChannelFooters || {})[key] || {};
    var custom = stored.custom ? ' checked' : '';
    var rows = Array.isArray(stored.rows) ? stored.rows : [];
    var html = '<div class="dls-tg-channel-footer" data-footer-key="' + key + '">' +
      '<div class="dls-tg-channel-footer__head"><strong>Footer</strong>' +
      '<label class="dls-tg-channel-footer__toggle"><input type="checkbox" name="dls_wd_tg_footers[' + key + '][custom]" value="1"' + custom + '> Custom for this channel</label></div>' +
      '<div class="dls-tg-channel-footer__rows"></div>' +
      '<button type="button" class="dls-tg-channel-footer__add">Add footer link</button>' +
      '<p class="dls-tg-channel-footer__note">If custom is off, this channel uses the shared footer. If custom is on and rows are empty, this channel sends without footer.</p>' +
      '</div>';

    card.insertAdjacentHTML('beforeend', html);
    var footer = card.querySelector('.dls-tg-channel-footer[data-footer-key="' + key + '"]');
    var rowsWrap = footer.querySelector('.dls-tg-channel-footer__rows');

    if (rows.length) {
      rows.forEach(function (row, index) {
        rowsWrap.insertAdjacentHTML('beforeend', rowHtml(key, index, row));
      });
    }

    footer.querySelector('.dls-tg-channel-footer__add').addEventListener('click', function () {
      var index = rowsWrap.querySelectorAll('.dls-tg-channel-footer__row').length;
      rowsWrap.insertAdjacentHTML('beforeend', rowHtml(key, index, {}));
      footer.querySelector('input[name="dls_wd_tg_footers[' + key + '][custom]"]').checked = true;
    });

    footer.addEventListener('click', function (event) {
      if (!event.target.classList.contains('dls-tg-channel-footer__remove')) return;
      event.target.closest('.dls-tg-channel-footer__row').remove();
      footer.querySelector('input[name="dls_wd_tg_footers[' + key + '][custom]"]').checked = true;
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dls-writing-desk__social-card').forEach(setupFooter);
  });
})();
JS;
    }
}
