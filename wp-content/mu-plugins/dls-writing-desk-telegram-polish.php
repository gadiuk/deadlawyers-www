<?php
/**
 * Plugin Name: DLS Writing Desk Telegram Polish
 * Description: Adds Telegram formatting helpers, destination cleanup, center-editor behavior, and per-channel footers.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_wd_tp_normalize_telegram_username')) {
    function dls_wd_tp_normalize_telegram_username($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (function_exists('dls_writing_desk_normalize_telegram_username')) {
            return dls_writing_desk_normalize_telegram_username($value);
        }

        $value = preg_replace('/^https?:\/\/(?:www\.)?t\.me\//i', '', $value);
        $value = preg_replace('/^@+/', '', (string) $value);
        $value = preg_replace('/[^A-Za-z0-9_]/', '', (string) $value);

        return $value === '' ? '' : '@' . $value;
    }
}

if (!function_exists('dls_wd_tp_ensure_author_telegram_map')) {
    function dls_wd_tp_ensure_author_telegram_map() {
        foreach (['admin' => '@gadiuk', 'gadiuk' => '@gadiuk'] as $login => $username) {
            $user = get_user_by('login', (string) $login);
            $username = dls_wd_tp_normalize_telegram_username($username);
            if (!($user instanceof WP_User) || $username === '') {
                continue;
            }

            foreach (['_dls_author_telegram', '_dls_author_telegram_username', '_dls_telegram_username', 'telegram_username', 'telegram'] as $meta_key) {
                if (trim((string) get_user_meta($user->ID, $meta_key, true)) === '') {
                    update_user_meta($user->ID, $meta_key, $username);
                }
            }
        }
    }
}
add_action('admin_init', 'dls_wd_tp_ensure_author_telegram_map');

if (!function_exists('dls_wd_tp_post_kicker')) {
    function dls_wd_tp_post_kicker($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        if (function_exists('dls_writing_desk_get_post_kicker')) {
            return trim((string) dls_writing_desk_get_post_kicker($post_id));
        }

        foreach (['_dls_writing_desk_kicker', '_dls_post_kicker', 'kicker'] as $meta_key) {
            $value = trim((string) get_post_meta($post_id, $meta_key, true));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('dls_wd_tp_default_telegram_text')) {
    function dls_wd_tp_default_telegram_text($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        $parts = [];
        $title = trim((string) get_the_title($post_id));
        $kicker = dls_wd_tp_post_kicker($post_id);

        if ($title !== '') {
            $headline = $kicker !== '' ? '[' . $kicker . '] -- ' . $title : $title;
            $parts[] = '<b>' . esc_html($headline) . '</b>';
        }

        $lead = trim((string) get_post_field('post_excerpt', $post_id));
        if ($lead !== '') {
            $parts[] = esc_html($lead);
        }

        $url = get_permalink($post_id);
        if (is_string($url) && $url !== '') {
            $parts[] = esc_url($url);
        }

        return implode("\n\n", array_filter($parts));
    }
}

if (!function_exists('dls_wd_tp_old_default_telegram_text')) {
    function dls_wd_tp_old_default_telegram_text($post_id) {
        if (function_exists('dls_writing_desk_default_telegram_base_text')) {
            return trim((string) dls_writing_desk_default_telegram_base_text($post_id));
        }

        return '';
    }
}

if (!function_exists('dls_wd_tp_is_default_like')) {
    function dls_wd_tp_is_default_like($post_id, $text) {
        $text = trim((string) $text);
        if ($text === '') {
            return true;
        }

        $old = dls_wd_tp_old_default_telegram_text($post_id);
        $new = trim(dls_wd_tp_default_telegram_text($post_id));
        $plain_text = trim(wp_strip_all_tags($text));
        $plain_new = trim(wp_strip_all_tags($new));

        return ($old !== '' && $text === $old)
            || ($new !== '' && $text === $new)
            || ($plain_new !== '' && $plain_text === $plain_new);
    }
}

if (!function_exists('dls_wd_tp_refresh_default_telegram_text')) {
    function dls_wd_tp_refresh_default_telegram_text($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return;
        }

        $default = dls_wd_tp_default_telegram_text($post_id);
        if ($default === '') {
            return;
        }

        $stored = trim((string) get_post_meta($post_id, '_dls_writing_desk_telegram_default_text', true));
        if (dls_wd_tp_is_default_like($post_id, $stored)) {
            update_post_meta($post_id, '_dls_writing_desk_telegram_default_text', $default);
        }
    }
}

if (!function_exists('dls_wd_tp_admin_init')) {
    function dls_wd_tp_admin_init() {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if ($page === 'dls-writing-desk-telegram') {
            dls_wd_tp_refresh_default_telegram_text(absint($_GET['desk_post'] ?? 0));
        }
    }
}
add_action('admin_init', 'dls_wd_tp_admin_init', 50);

if (!function_exists('dls_wd_tp_footer_meta_key')) {
    function dls_wd_tp_footer_meta_key() {
        return '_dls_wd_tg_channel_footers';
    }
}

if (!function_exists('dls_wd_tp_sanitize_footer_rows')) {
    function dls_wd_tp_sanitize_footer_rows($rows) {
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

            $items[] = ['label' => $label, 'url' => $url];
        }

        return $items;
    }
}

if (!function_exists('dls_wd_tp_footer_settings')) {
    function dls_wd_tp_footer_settings($post_id) {
        $stored = get_post_meta(absint($post_id), dls_wd_tp_footer_meta_key(), true);
        return is_array($stored) ? $stored : [];
    }
}

if (!function_exists('dls_wd_tp_save_telegram_payload')) {
    function dls_wd_tp_save_telegram_payload() {
        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? 0);
        if ($post_id < 1 || !current_user_can('edit_post', $post_id)) {
            return;
        }

        $submitted = isset($_POST['dls_writing_desk_telegram_default_text'])
            ? trim((string) wp_unslash($_POST['dls_writing_desk_telegram_default_text']))
            : '';

        if (dls_wd_tp_is_default_like($post_id, $submitted)) {
            $_POST['dls_writing_desk_telegram_default_text'] = dls_wd_tp_default_telegram_text($post_id);
        }

        if (!empty($_POST['dls_writing_desk_social']) && is_array($_POST['dls_writing_desk_social'])) {
            foreach ($_POST['dls_writing_desk_social'] as $key => $row) {
                if (is_array($row)) {
                    $_POST['dls_writing_desk_social'][$key]['description'] = '';
                }
            }
        }

        $payload = isset($_POST['dls_wd_tg_footers']) ? (array) wp_unslash($_POST['dls_wd_tg_footers']) : [];
        $clean = [];
        foreach ($payload as $key => $row) {
            $key = sanitize_key((string) $key);
            if ($key === '' || !is_array($row)) {
                continue;
            }

            $custom = !empty($row['custom']) ? 1 : 0;
            $rows = dls_wd_tp_sanitize_footer_rows($row['rows'] ?? []);
            if (!$custom && empty($rows)) {
                continue;
            }

            $clean[$key] = ['custom' => $custom, 'rows' => $rows];
        }

        if (empty($clean)) {
            delete_post_meta($post_id, dls_wd_tp_footer_meta_key());
        } else {
            update_post_meta($post_id, dls_wd_tp_footer_meta_key(), $clean);
        }
    }
}
add_action('admin_post_dls_writing_desk_telegram_save', 'dls_wd_tp_save_telegram_payload', 0);

if (!function_exists('dls_wd_tp_enforce_single_preview_destination')) {
    function dls_wd_tp_enforce_single_preview_destination() {
        if (empty($_POST['dls_writing_desk_destinations']) || !is_array($_POST['dls_writing_desk_destinations'])) {
            return;
        }

        $rows = (array) wp_unslash($_POST['dls_writing_desk_destinations']);
        $preview_index = null;
        foreach ($rows as $index => $row) {
            if (!is_array($row) || sanitize_key((string) ($row['platform'] ?? '')) !== 'telegram' || empty($row['preview'])) {
                continue;
            }

            $name = function_exists('mb_strtolower') ? mb_strtolower((string) ($row['name'] ?? ''), 'UTF-8') : strtolower((string) ($row['name'] ?? ''));
            $destination = function_exists('mb_strtolower') ? mb_strtolower((string) ($row['destination'] ?? ''), 'UTF-8') : strtolower((string) ($row['destination'] ?? ''));
            $haystack = $name . ' ' . $destination;
            if ($preview_index === null || strpos($haystack, 'preview') !== false || strpos($haystack, 'test') !== false || strpos($haystack, 'тест') !== false || strpos($haystack, 'прев') !== false) {
                $preview_index = $index;
            }
        }

        foreach ($_POST['dls_writing_desk_destinations'] as $index => $row) {
            if (!is_array($row) || sanitize_key((string) ($row['platform'] ?? '')) !== 'telegram') {
                continue;
            }

            if ((string) $index === (string) $preview_index) {
                $_POST['dls_writing_desk_destinations'][$index]['preview'] = '1';
            } else {
                unset($_POST['dls_writing_desk_destinations'][$index]['preview']);
            }
        }
    }
}
add_action('admin_post_dls_writing_desk_destinations_save', 'dls_wd_tp_enforce_single_preview_destination', 1);

if (!function_exists('dls_wd_tp_set_scheduled_post')) {
    function dls_wd_tp_set_scheduled_post($post_id) {
        $GLOBALS['dls_wd_tp_current_post_id'] = absint($post_id);
    }
}
add_action('dls_writing_desk_telegram_scheduled_send', 'dls_wd_tp_set_scheduled_post', 1, 1);

if (!function_exists('dls_wd_tp_destination_key_from_chat')) {
    function dls_wd_tp_destination_key_from_chat($chat_id) {
        $chat_id = trim((string) $chat_id);
        if ($chat_id === '' || !function_exists('dls_writing_desk_get_social_destinations')) {
            return '';
        }

        foreach ((array) dls_writing_desk_get_social_destinations() as $destination) {
            if (($destination['platform'] ?? '') === 'telegram' && trim((string) ($destination['destination'] ?? '')) === $chat_id) {
                return sanitize_key((string) ($destination['key'] ?? ''));
            }
        }

        return '';
    }
}

if (!function_exists('dls_wd_tp_footer_text')) {
    function dls_wd_tp_footer_text($rows) {
        $parts = [];
        foreach ((array) $rows as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            if ($label === '') {
                continue;
            }

            $parts[] = $url !== '' ? '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>' : esc_html($label);
        }

        return implode(' | ', $parts);
    }
}

if (!function_exists('dls_wd_tp_remove_shared_footer')) {
    function dls_wd_tp_remove_shared_footer($text) {
        $text = (string) $text;
        if (!function_exists('dls_writing_desk_telegram_footer_text')) {
            return $text;
        }

        $footer = trim((string) dls_writing_desk_telegram_footer_text());
        if ($footer === '') {
            return $text;
        }

        foreach ([$footer, html_entity_decode($footer, ENT_QUOTES | ENT_HTML5, 'UTF-8')] as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && substr(trim($text), -strlen($candidate)) === $candidate) {
                return rtrim(substr(trim($text), 0, -strlen($candidate)));
            }
        }

        return $text;
    }
}

if (!function_exists('dls_wd_tp_allowed_telegram_html')) {
    function dls_wd_tp_allowed_telegram_html() {
        return [
            'a' => ['href' => true],
            'b' => [],
            'strong' => [],
            'i' => [],
            'em' => [],
            'u' => [],
            'ins' => [],
            's' => [],
            'strike' => [],
            'del' => [],
            'code' => [],
            'pre' => ['language' => true],
            'blockquote' => ['expandable' => true],
            'tg-spoiler' => [],
        ];
    }
}

if (!function_exists('dls_wd_tp_filter_telegram_request')) {
    function dls_wd_tp_filter_telegram_request($args, $url) {
        if (strpos((string) $url, 'https://api.telegram.org/bot') !== 0 || strpos((string) $url, '/sendMessage') === false) {
            return $args;
        }

        if (empty($args['body']) || !is_array($args['body'])) {
            return $args;
        }

        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? ($GLOBALS['dls_wd_tp_current_post_id'] ?? ($GLOBALS['dls_wd_tg_cf_current_post_id'] ?? 0)));
        if ($post_id > 0 && function_exists('dls_writing_desk_telegram_text')) {
            $default_text = function_exists('dls_writing_desk_get_telegram_default_text') ? dls_writing_desk_get_telegram_default_text($post_id) : dls_wd_tp_default_telegram_text($post_id);
            $args['body']['text'] = dls_writing_desk_telegram_text($post_id, [
                'description' => '',
                'default_text' => $default_text,
            ]);
        }

        $destination_key = dls_wd_tp_destination_key_from_chat($args['body']['chat_id'] ?? '');
        if ($post_id > 0 && $destination_key !== '') {
            $footer_settings = dls_wd_tp_footer_settings($post_id);
            if (!empty($footer_settings[$destination_key]['custom'])) {
                $text = dls_wd_tp_remove_shared_footer((string) ($args['body']['text'] ?? ''));
                $footer = dls_wd_tp_footer_text($footer_settings[$destination_key]['rows'] ?? []);
                $args['body']['text'] = $footer !== '' ? trim($text . "\n\n" . $footer) : trim($text);
            }
        }

        if (!empty($args['body']['text'])) {
            $text = html_entity_decode((string) $args['body']['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $args['body']['text'] = wp_kses($text, dls_wd_tp_allowed_telegram_html());
        }
        $args['body']['parse_mode'] = 'HTML';

        return $args;
    }
}
add_filter('http_request_args', 'dls_wd_tp_filter_telegram_request', 100, 2);

if (!function_exists('dls_wd_tp_admin_assets')) {
    function dls_wd_tp_admin_assets() {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if (!in_array($page, ['dls-writing-desk', 'dls-writing-desk-telegram', 'dls-writing-desk-destinations'], true)) {
            return;
        }

        $post_id = absint($_GET['desk_post'] ?? 0);
        wp_enqueue_script('jquery');
        wp_register_style('dls-writing-desk-telegram-polish', false, [], '1.1.0');
        wp_enqueue_style('dls-writing-desk-telegram-polish');
        wp_add_inline_style('dls-writing-desk-telegram-polish', dls_wd_tp_admin_css());
        wp_add_inline_script('jquery', 'window.dlsWdTgChannelFooters = ' . wp_json_encode(dls_wd_tp_footer_settings($post_id)) . ';', 'before');
        wp_add_inline_script('jquery', dls_wd_tp_admin_js());
    }
}
add_action('admin_enqueue_scripts', 'dls_wd_tp_admin_assets', 90);

if (!function_exists('dls_wd_tp_admin_css')) {
    function dls_wd_tp_admin_css() {
        return <<<'CSS'
#dls-writing-desk-title,
input[name="dls_writing_desk_title"],
.dls-writing-desk__title,
.dls-writing-desk__headline {
  color: #000 !important;
}
.dls-tg-tools {
  display: flex !important;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0 0 8px;
}
.dls-tg-tools button,
.dls-social-remove-row,
.dls-tg-channel-footer button {
  border: 1px solid #d7cdc1;
  background: #fffaf3;
  border-radius: 999px;
  color: #231b16;
  cursor: pointer;
  font-size: 12px;
  font-weight: 700;
  line-height: 1;
  padding: 7px 9px;
}
.dls-tg-tools button:hover,
.dls-tg-tools button:focus,
.dls-social-remove-row:hover,
.dls-social-remove-row:focus,
.dls-tg-channel-footer button:hover,
.dls-tg-channel-footer button:focus {
  background: #f3e1c6;
  border-color: #be8d4a;
}
.dls-social-remove-row,
.dls-tg-channel-footer__remove {
  background: #fff !important;
  border-color: #d9b8a7 !important;
  color: #9a2a16 !important;
  margin-left: 8px;
}
tr.dls-social-row-removed,
.dls-writing-desk__destination-row.dls-social-row-removed {
  display: none !important;
}
.dls-writing-desk__social-card textarea[name^="dls_writing_desk_social["][name$="[description]"] {
  display: none !important;
}
.dls-writing-desk__social-card .dls-tg-tools {
  display: none !important;
}
.dls-tg-center-note {
  background: #fff7e8;
  border: 1px solid #ead8b8;
  border-radius: 12px;
  color: #765d45;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.35;
  margin: 8px 0 12px;
  padding: 9px 11px;
}
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
  letter-spacing: .08em;
  text-transform: uppercase;
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
.dls-tg-channel-footer__note,
.dls-tg-channel-summary {
  color: #806b58;
  font-size: 12px;
  line-height: 1.35;
}
.dls-tg-channel-summary {
  border: 1px solid #e3dbd2;
  border-radius: 16px;
  background: #fffaf3;
  margin: 0 0 16px;
  padding: 14px;
}
.dls-tg-channel-summary strong {
  color: #211814;
  display: block;
  margin-bottom: 8px;
}
.dls-tg-channel-summary ul {
  margin: 0;
  padding-left: 18px;
}
CSS;
    }
}

if (!function_exists('dls_wd_tp_admin_js')) {
    function dls_wd_tp_admin_js() {
        return <<<'JS'
(function () {
  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (char) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char];
    });
  }

  function wrapSelection(textarea, before, after, placeholder) {
    if (!textarea) return;
    var start = textarea.selectionStart || 0;
    var end = textarea.selectionEnd || 0;
    var value = textarea.value || '';
    var selected = value.slice(start, end) || placeholder || '';
    var insert = before + selected + after;
    textarea.value = value.slice(0, start) + insert + value.slice(end);
    textarea.focus();
    textarea.selectionStart = start + before.length;
    textarea.selectionEnd = start + before.length + selected.length;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function addTelegramToolbar(textarea) {
    if (!textarea || textarea.dataset.dlsTgToolbar === '1') return;
    textarea.dataset.dlsTgToolbar = '1';

    var previous = textarea.previousElementSibling;
    if (previous && previous.classList && previous.classList.contains('dls-tg-tools')) return;

    var toolbar = document.createElement('div');
    toolbar.className = 'dls-tg-tools';
    var tools = [
      ['Bold', '<b>', '</b>', 'bold text'],
      ['Italic', '<i>', '</i>', 'italic text'],
      ['Underline', '<u>', '</u>', 'underlined text'],
      ['Strike', '<s>', '</s>', 'struck text'],
      ['Spoiler', '<tg-spoiler>', '</tg-spoiler>', 'hidden text'],
      ['Code', '<code>', '</code>', 'code'],
      ['Pre', '<pre>', '</pre>', 'code block'],
      ['Quote', '<blockquote>', '</blockquote>', 'quote'],
      ['Firm quote', '<blockquote><b>Позиція фірми:</b>\n', '</blockquote>', 'quote'],
      ['Expandable quote', '<blockquote expandable>', '</blockquote>', 'long quote'],
      ['Link', '', '', 'link text']
    ];

    tools.forEach(function (tool) {
      var button = document.createElement('button');
      button.type = 'button';
      button.textContent = tool[0];
      button.addEventListener('click', function () {
        if (tool[0] === 'Link') {
          var url = window.prompt('URL', 'https://');
          if (!url) return;
          wrapSelection(textarea, '<a href="' + url.replace(/"/g, '&quot;') + '">', '</a>', tool[3]);
          return;
        }
        wrapSelection(textarea, tool[1], tool[2], tool[3]);
      });
      toolbar.appendChild(button);
    });

    textarea.parentNode.insertBefore(toolbar, textarea);
  }

  function setupTelegramTextareas() {
    var center = document.querySelector('textarea[name="dls_writing_desk_telegram_default_text"]');
    addTelegramToolbar(center);

    document.querySelectorAll('.dls-writing-desk__social-card textarea[name^="dls_writing_desk_social["][name$="[description]"]').forEach(function (textarea) {
      textarea.value = '';
      var previous = textarea.previousElementSibling;
      if (previous && previous.classList && previous.classList.contains('dls-tg-tools')) previous.remove();
      if (!textarea.parentNode.querySelector('.dls-tg-center-note')) {
        var note = document.createElement('div');
        note.className = 'dls-tg-center-note';
        note.textContent = 'Post text is edited in the center panel. This channel keeps only delivery controls.';
        textarea.parentNode.insertBefore(note, textarea);
      }
    });
  }

  function setupSinglePreviewCheckbox() {
    document.querySelectorAll('input[name^="dls_writing_desk_destinations["][name$="[preview]"]').forEach(function (checkbox) {
      checkbox.addEventListener('change', function () {
        if (!checkbox.checked) return;
        document.querySelectorAll('input[name^="dls_writing_desk_destinations["][name$="[preview]"]').forEach(function (other) {
          if (other !== checkbox) other.checked = false;
        });
      });
    });
  }

  function destinationRows() {
    return Array.prototype.slice.call(document.querySelectorAll('[name^="dls_writing_desk_destinations["]')).map(function (input) {
      return input.closest('tr') || input.closest('.dls-writing-desk__destination-row') || input.parentElement;
    }).filter(function (row, index, rows) {
      return row && rows.indexOf(row) === index;
    });
  }

  function setupDestinationRemoveButtons() {
    destinationRows().forEach(function (row) {
      if (row.querySelector('.dls-social-remove-row')) return;
      var fields = row.querySelectorAll('input[name^="dls_writing_desk_destinations["], select[name^="dls_writing_desk_destinations["]');
      if (!fields.length) return;
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'dls-social-remove-row';
      button.textContent = 'Remove';
      button.addEventListener('click', function () {
        if (!window.confirm('Remove this social destination? Press Save Destinations after this.')) return;
        fields.forEach(function (field) {
          if (field.type === 'checkbox' || field.type === 'radio') field.checked = false;
          else if (field.tagName === 'SELECT') field.selectedIndex = 0;
          else field.value = '';
          field.dispatchEvent(new Event('change', { bubbles: true }));
        });
        row.classList.add('dls-social-row-removed');
      });
      fields[fields.length - 1].insertAdjacentElement('afterend', button);
    });
  }

  function channelKey(card) {
    var input = card.querySelector('input[name^="dls_writing_desk_social["][name$="[enabled]"]');
    var match = input && input.name ? input.name.match(/^dls_writing_desk_social\[([^\]]+)\]/) : null;
    return match ? match[1] : '';
  }

  function footerRowHtml(key, index, row) {
    row = row || {};
    return '<div class="dls-tg-channel-footer__row">' +
      '<input type="text" name="dls_wd_tg_footers[' + key + '][rows][' + index + '][label]" placeholder="Label" value="' + escapeHtml(row.label) + '">' +
      '<input type="url" name="dls_wd_tg_footers[' + key + '][rows][' + index + '][url]" placeholder="https://..." value="' + escapeHtml(row.url) + '">' +
      '<button type="button" class="dls-tg-channel-footer__remove">Remove</button>' +
      '</div>';
  }

  function setupChannelFooter(card) {
    if (card.querySelector('.dls-tg-channel-footer')) return;
    var key = channelKey(card);
    if (!key) return;

    var stored = (window.dlsWdTgChannelFooters || {})[key] || {};
    var rows = Array.isArray(stored.rows) ? stored.rows : [];
    var html = '<div class="dls-tg-channel-footer" data-footer-key="' + key + '">' +
      '<div class="dls-tg-channel-footer__head"><strong>Footer</strong>' +
      '<label class="dls-tg-channel-footer__toggle"><input type="checkbox" name="dls_wd_tg_footers[' + key + '][custom]" value="1"' + (stored.custom ? ' checked' : '') + '> Custom</label></div>' +
      '<div class="dls-tg-channel-footer__rows"></div>' +
      '<button type="button" class="dls-tg-channel-footer__add">Add footer link</button>' +
      '<p class="dls-tg-channel-footer__note">Custom off: shared footer. Custom on with no rows: no footer.</p>' +
      '</div>';
    card.insertAdjacentHTML('beforeend', html);

    var footer = card.querySelector('.dls-tg-channel-footer[data-footer-key="' + key + '"]');
    var rowsWrap = footer.querySelector('.dls-tg-channel-footer__rows');
    rows.forEach(function (row, index) {
      rowsWrap.insertAdjacentHTML('beforeend', footerRowHtml(key, index, row));
    });

    footer.querySelector('.dls-tg-channel-footer__add').addEventListener('click', function () {
      var index = rowsWrap.querySelectorAll('.dls-tg-channel-footer__row').length;
      rowsWrap.insertAdjacentHTML('beforeend', footerRowHtml(key, index, {}));
      footer.querySelector('input[name="dls_wd_tg_footers[' + key + '][custom]"]').checked = true;
    });

    footer.addEventListener('click', function (event) {
      if (!event.target.classList.contains('dls-tg-channel-footer__remove')) return;
      event.target.closest('.dls-tg-channel-footer__row').remove();
      footer.querySelector('input[name="dls_wd_tg_footers[' + key + '][custom]"]').checked = true;
    });
  }

  function setupChannelSummary(cards) {
    if (!cards.length || document.querySelector('.dls-tg-channel-summary')) return;
    var channels = cards.map(function (card) {
      var label = card.querySelector('.dls-writing-desk__label span');
      var enabled = card.querySelector('input[name^="dls_writing_desk_social["][name$="[enabled]"]');
      var name = label ? label.textContent.trim() : '';
      if (!name || !enabled) return '';
      return '<li>' + escapeHtml(name) + (enabled.checked ? ' — selected' : '') + '</li>';
    }).filter(Boolean);
    if (!channels.length) return;

    var summary = document.createElement('div');
    summary.className = 'dls-tg-channel-summary';
    summary.innerHTML = '<strong>Connected Telegram channels</strong><ul>' + channels.join('') + '</ul>';
    cards[0].parentNode.insertBefore(summary, cards[0]);
  }

  function init() {
    setupTelegramTextareas();
    setupSinglePreviewCheckbox();
    setupDestinationRemoveButtons();
    var cards = Array.prototype.slice.call(document.querySelectorAll('.dls-writing-desk__social-card'));
    cards.forEach(setupChannelFooter);
    setupChannelSummary(cards);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
JS;
    }
}
