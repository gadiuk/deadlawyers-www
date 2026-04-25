<?php
/**
 * Plugin Name: DLS Writing Desk Telegram Polish
 * Description: Adds Telegram formatting helpers, preview-channel guardrails, and default message polish for Writing Desk.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_wd_tp_author_telegram_fallbacks')) {
    function dls_wd_tp_author_telegram_fallbacks() {
        return [
            'admin' => '@gadiuk',
            'gadiuk' => '@gadiuk',
        ];
    }
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
        foreach (dls_wd_tp_author_telegram_fallbacks() as $login => $username) {
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
            $parts[] = $kicker !== '' ? '[' . $kicker . '] -- ' . $title : $title;
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

if (!function_exists('dls_wd_tp_existing_default_telegram_text')) {
    function dls_wd_tp_existing_default_telegram_text($post_id) {
        if (function_exists('dls_writing_desk_default_telegram_base_text')) {
            return (string) dls_writing_desk_default_telegram_base_text($post_id);
        }

        return '';
    }
}

if (!function_exists('dls_wd_tp_refresh_default_telegram_text')) {
    function dls_wd_tp_refresh_default_telegram_text($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return;
        }

        $new_default = dls_wd_tp_default_telegram_text($post_id);
        if ($new_default === '') {
            return;
        }

        $stored = trim((string) get_post_meta($post_id, '_dls_writing_desk_telegram_default_text', true));
        $old_default = trim(dls_wd_tp_existing_default_telegram_text($post_id));

        if ($stored === '' || ($old_default !== '' && $stored === $old_default)) {
            update_post_meta($post_id, '_dls_writing_desk_telegram_default_text', $new_default);
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
add_action('admin_init', 'dls_wd_tp_admin_init', 20);

if (!function_exists('dls_wd_tp_prepare_telegram_post_payload')) {
    function dls_wd_tp_prepare_telegram_post_payload() {
        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? 0);
        if ($post_id < 1) {
            return;
        }

        $submitted = isset($_POST['dls_writing_desk_telegram_default_text'])
            ? trim((string) wp_unslash($_POST['dls_writing_desk_telegram_default_text']))
            : '';
        $old_default = trim(dls_wd_tp_existing_default_telegram_text($post_id));

        if ($submitted === '' || ($old_default !== '' && $submitted === $old_default)) {
            $_POST['dls_writing_desk_telegram_default_text'] = dls_wd_tp_default_telegram_text($post_id);
        }
    }
}
add_action('admin_post_dls_writing_desk_telegram_save', 'dls_wd_tp_prepare_telegram_post_payload', 1);

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

            $name = strtolower((string) ($row['name'] ?? ''));
            if ($preview_index === null || strpos($name, 'preview') !== false || strpos($name, 'прев') !== false) {
                $preview_index = $index;
            }
        }

        foreach ($_POST['dls_writing_desk_destinations'] as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $platform = sanitize_key((string) ($row['platform'] ?? ''));
            if ($platform !== 'telegram') {
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

if (!function_exists('dls_wd_tp_tinymce_init')) {
    function dls_wd_tp_tinymce_init($settings, $editor_id) {
        if ((string) $editor_id !== 'dls_writing_desk_content') {
            return $settings;
        }

        $settings['toolbar1'] = 'formatselect,bold,italic,underline,strikethrough,link,unlink,blockquote,bullist,numlist,outdent,indent,alignleft,aligncenter,alignright,undo,redo,removeformat';
        $settings['toolbar2'] = 'pastetext,charmap,hr,forecolor,backcolor,code';
        $settings['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Quote=blockquote;Preformatted=pre';
        $settings['menubar'] = false;

        return $settings;
    }
}
add_filter('tiny_mce_before_init', 'dls_wd_tp_tinymce_init', 20, 2);

if (!function_exists('dls_wd_tp_mce_buttons')) {
    function dls_wd_tp_mce_buttons($buttons, $editor_id = '') {
        if ((string) $editor_id !== 'dls_writing_desk_content') {
            return $buttons;
        }

        foreach (['underline', 'strikethrough', 'unlink', 'outdent', 'indent', 'alignleft', 'aligncenter', 'alignright'] as $button) {
            if (!in_array($button, $buttons, true)) {
                $buttons[] = $button;
            }
        }

        return $buttons;
    }
}
add_filter('mce_buttons', 'dls_wd_tp_mce_buttons', 20, 2);

if (!function_exists('dls_wd_tp_mce_buttons_2')) {
    function dls_wd_tp_mce_buttons_2($buttons, $editor_id = '') {
        if ((string) $editor_id !== 'dls_writing_desk_content') {
            return $buttons;
        }

        return array_values(array_unique(array_merge($buttons, ['pastetext', 'charmap', 'hr', 'forecolor', 'backcolor', 'code'])));
    }
}
add_filter('mce_buttons_2', 'dls_wd_tp_mce_buttons_2', 20, 2);

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

        if (empty($args['body']) || !is_array($args['body']) || empty($args['body']['text'])) {
            return $args;
        }

        $text = html_entity_decode((string) $args['body']['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $args['body']['text'] = wp_kses($text, dls_wd_tp_allowed_telegram_html());
        $args['body']['parse_mode'] = 'HTML';

        return $args;
    }
}
add_filter('http_request_args', 'dls_wd_tp_filter_telegram_request', 20, 2);

if (!function_exists('dls_wd_tp_admin_assets')) {
    function dls_wd_tp_admin_assets($hook) {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        if (!in_array($page, ['dls-writing-desk', 'dls-writing-desk-telegram', 'dls-writing-desk-destinations'], true)) {
            return;
        }

        wp_enqueue_script('jquery');
        wp_register_style('dls-writing-desk-telegram-polish', false, [], '1.0.0');
        wp_enqueue_style('dls-writing-desk-telegram-polish');
        wp_add_inline_style('dls-writing-desk-telegram-polish', dls_wd_tp_admin_css());
        wp_add_inline_script('jquery', dls_wd_tp_admin_js());
    }
}
add_action('admin_enqueue_scripts', 'dls_wd_tp_admin_assets', 30);

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
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0 0 8px;
}
.dls-tg-tools button {
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
.dls-tg-tools button:focus {
  background: #f3e1c6;
  border-color: #be8d4a;
}
.dls-tg-channel-summary {
  border: 1px solid #e3dbd2;
  border-radius: 16px;
  background: #fffaf3;
  margin: 0 0 16px;
  padding: 14px;
}
.dls-tg-channel-summary strong {
  display: block;
  margin-bottom: 8px;
}
.dls-tg-channel-summary ul {
  margin: 0;
  padding-left: 18px;
}
.dls-tg-channel-summary li {
  margin: 4px 0;
}
CSS;
    }
}

if (!function_exists('dls_wd_tp_admin_js')) {
    function dls_wd_tp_admin_js() {
        return <<<'JS'
(function () {
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
    document.querySelectorAll('textarea[name="dls_writing_desk_telegram_default_text"], textarea[name^="dls_writing_desk_social["][name$="[description]"]').forEach(addTelegramToolbar);
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

  function setupChannelSummary() {
    var cards = Array.prototype.slice.call(document.querySelectorAll('.dls-writing-desk__social-card'));
    if (!cards.length || document.querySelector('.dls-tg-channel-summary')) return;

    var channels = cards.map(function (card) {
      var label = card.querySelector('.dls-writing-desk__label span');
      var enabled = card.querySelector('input[name^="dls_writing_desk_social["][name$="[enabled]"]');
      var name = label ? label.textContent.trim() : '';
      if (!name || !enabled) return '';
      return '<li>' + name + (enabled.checked ? ' — selected' : '') + '</li>';
    }).filter(Boolean);

    if (!channels.length) return;

    var summary = document.createElement('div');
    summary.className = 'dls-tg-channel-summary';
    summary.innerHTML = '<strong>Connected Telegram channels</strong><ul>' + channels.join('') + '</ul>';
    cards[0].parentNode.insertBefore(summary, cards[0]);
  }

  document.addEventListener('DOMContentLoaded', function () {
    setupTelegramTextareas();
    setupSinglePreviewCheckbox();
    setupChannelSummary();
  });
})();
JS;
    }
}
