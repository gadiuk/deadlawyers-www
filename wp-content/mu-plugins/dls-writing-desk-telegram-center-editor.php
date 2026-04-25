<?php
/**
 * Plugin Name: DLS Writing Desk Telegram Center Editor
 * Description: Uses the center Telegram editor as the only post text editor and hides per-channel text overrides.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_wd_tg_ce_post_kicker')) {
    function dls_wd_tg_ce_post_kicker($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        if (function_exists('dls_writing_desk_get_post_kicker')) {
            return trim((string) dls_writing_desk_get_post_kicker($post_id));
        }

        foreach (['_dls_writing_desk_kicker', '_dls_post_kicker', 'kicker'] as $key) {
            $value = trim((string) get_post_meta($post_id, $key, true));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('dls_wd_tg_ce_default_text')) {
    function dls_wd_tg_ce_default_text($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return '';
        }

        $parts = [];
        $title = trim((string) get_the_title($post_id));
        $kicker = dls_wd_tg_ce_post_kicker($post_id);

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

if (!function_exists('dls_wd_tg_ce_old_default_text')) {
    function dls_wd_tg_ce_old_default_text($post_id) {
        if (function_exists('dls_writing_desk_default_telegram_base_text')) {
            return trim((string) dls_writing_desk_default_telegram_base_text($post_id));
        }

        return '';
    }
}

if (!function_exists('dls_wd_tg_ce_is_old_default')) {
    function dls_wd_tg_ce_is_old_default($post_id, $text) {
        $text = trim((string) $text);
        if ($text === '') {
            return true;
        }

        $old_default = dls_wd_tg_ce_old_default_text($post_id);
        $new_default = trim(dls_wd_tg_ce_default_text($post_id));
        $plain_new_default = trim(wp_strip_all_tags($new_default));
        $plain_text = trim(wp_strip_all_tags($text));

        return ($old_default !== '' && $text === $old_default)
            || ($new_default !== '' && $text === $new_default)
            || ($plain_new_default !== '' && $plain_text === $plain_new_default);
    }
}

if (!function_exists('dls_wd_tg_ce_refresh_default')) {
    function dls_wd_tg_ce_refresh_default($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return;
        }

        $default = dls_wd_tg_ce_default_text($post_id);
        if ($default === '') {
            return;
        }

        $stored = trim((string) get_post_meta($post_id, '_dls_writing_desk_telegram_default_text', true));
        if (dls_wd_tg_ce_is_old_default($post_id, $stored)) {
            update_post_meta($post_id, '_dls_writing_desk_telegram_default_text', $default);
        }
    }
}

if (!function_exists('dls_wd_tg_ce_admin_init')) {
    function dls_wd_tg_ce_admin_init() {
        if (sanitize_key((string) ($_GET['page'] ?? '')) === 'dls-writing-desk-telegram') {
            dls_wd_tg_ce_refresh_default(absint($_GET['desk_post'] ?? 0));
        }
    }
}
add_action('admin_init', 'dls_wd_tg_ce_admin_init', 30);

if (!function_exists('dls_wd_tg_ce_prepare_save')) {
    function dls_wd_tg_ce_prepare_save() {
        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? 0);
        if ($post_id < 1) {
            return;
        }

        $submitted = isset($_POST['dls_writing_desk_telegram_default_text'])
            ? trim((string) wp_unslash($_POST['dls_writing_desk_telegram_default_text']))
            : '';

        if (dls_wd_tg_ce_is_old_default($post_id, $submitted)) {
            $_POST['dls_writing_desk_telegram_default_text'] = dls_wd_tg_ce_default_text($post_id);
        }

        if (!empty($_POST['dls_writing_desk_social']) && is_array($_POST['dls_writing_desk_social'])) {
            foreach ($_POST['dls_writing_desk_social'] as $key => $row) {
                if (is_array($row)) {
                    $_POST['dls_writing_desk_social'][$key]['description'] = '';
                }
            }
        }
    }
}
add_action('admin_post_dls_writing_desk_telegram_save', 'dls_wd_tg_ce_prepare_save', 0);

if (!function_exists('dls_wd_tg_ce_force_center_text')) {
    function dls_wd_tg_ce_force_center_text($args, $url) {
        if (strpos((string) $url, 'https://api.telegram.org/bot') !== 0 || strpos((string) $url, '/sendMessage') === false) {
            return $args;
        }

        if (empty($args['body']) || !is_array($args['body'])) {
            return $args;
        }

        $post_id = absint($_POST['dls_writing_desk_post_id'] ?? ($GLOBALS['dls_wd_tg_cf_current_post_id'] ?? 0));
        if ($post_id < 1 || !function_exists('dls_writing_desk_telegram_text')) {
            return $args;
        }

        $default_text = function_exists('dls_writing_desk_get_telegram_default_text')
            ? dls_writing_desk_get_telegram_default_text($post_id)
            : dls_wd_tg_ce_default_text($post_id);

        $args['body']['text'] = dls_writing_desk_telegram_text($post_id, [
            'description' => '',
            'default_text' => $default_text,
        ]);
        $args['body']['parse_mode'] = 'HTML';

        return $args;
    }
}
add_filter('http_request_args', 'dls_wd_tg_ce_force_center_text', 30, 2);

if (!function_exists('dls_wd_tg_ce_admin_assets')) {
    function dls_wd_tg_ce_admin_assets() {
        if (sanitize_key((string) ($_GET['page'] ?? '')) !== 'dls-writing-desk-telegram') {
            return;
        }

        wp_enqueue_script('jquery');
        wp_register_style('dls-wd-tg-center-editor', false, [], '1.0.0');
        wp_enqueue_style('dls-wd-tg-center-editor');
        wp_add_inline_style('dls-wd-tg-center-editor', dls_wd_tg_ce_css());
        wp_add_inline_script('jquery', dls_wd_tg_ce_js());
    }
}
add_action('admin_enqueue_scripts', 'dls_wd_tg_ce_admin_assets', 60);

if (!function_exists('dls_wd_tg_ce_css')) {
    function dls_wd_tg_ce_css() {
        return <<<'CSS'
.dls-writing-desk__social-card textarea[name^="dls_writing_desk_social["][name$="[description]"],
.dls-writing-desk__social-card textarea[name^="dls_writing_desk_social["][name$="[description]"] + .dls-tg-tools,
.dls-writing-desk__social-card .dls-tg-tools + textarea[name^="dls_writing_desk_social["][name$="[description]"] {
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
.dls-tg-tools {
  display: flex !important;
}
CSS;
    }
}

if (!function_exists('dls_wd_tg_ce_js')) {
    function dls_wd_tg_ce_js() {
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

  function addToolbar(textarea) {
    if (!textarea || textarea.dataset.dlsCenterTools === '1') return;
    textarea.dataset.dlsCenterTools = '1';
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

  function setup() {
    var center = document.querySelector('textarea[name="dls_writing_desk_telegram_default_text"]');
    addToolbar(center);

    document.querySelectorAll('.dls-writing-desk__social-card textarea[name^="dls_writing_desk_social["][name$="[description]"]').forEach(function (textarea) {
      textarea.value = '';
      var tools = textarea.previousElementSibling;
      if (tools && tools.classList && tools.classList.contains('dls-tg-tools')) tools.remove();
      if (!textarea.parentNode.querySelector('.dls-tg-center-note')) {
        var note = document.createElement('div');
        note.className = 'dls-tg-center-note';
        note.textContent = 'Post text is edited in the center panel. This channel keeps only delivery controls.';
        textarea.parentNode.insertBefore(note, textarea);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setup);
  } else {
    setup();
  }
})();
JS;
    }
}
