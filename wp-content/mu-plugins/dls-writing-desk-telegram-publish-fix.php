<?php
/**
 * Plugin Name: DLS Writing Desk Telegram Publish Fix
 * Description: Disables Telegram link previews and updates already-sent Telegram messages instead of duplicating media.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_wd_tpf_is_telegram_api')) {
    function dls_wd_tpf_is_telegram_api($url, $method = '') {
        $url = (string) $url;
        if (strpos($url, 'https://api.telegram.org/bot') !== 0) {
            return false;
        }

        return $method === '' || strpos($url, '/' . $method) !== false;
    }
}

if (!function_exists('dls_wd_tpf_current_post_id')) {
    function dls_wd_tpf_current_post_id() {
        return absint($_POST['dls_writing_desk_post_id'] ?? ($GLOBALS['dls_wd_tp_current_post_id'] ?? 0));
    }
}

if (!function_exists('dls_wd_tpf_destination_key_from_chat')) {
    function dls_wd_tpf_destination_key_from_chat($chat_id) {
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

if (!function_exists('dls_wd_tpf_log_meta')) {
    function dls_wd_tpf_log_meta($post_id) {
        foreach (['_dls_writing_desk_telegram_log', 'dls_writing_desk_telegram_log', '_dls_wd_telegram_log'] as $meta_key) {
            $value = get_post_meta(absint($post_id), $meta_key, true);
            if (is_array($value)) {
                return [$meta_key, $value];
            }
        }

        return ['', []];
    }
}

if (!function_exists('dls_wd_tpf_existing_message_id')) {
    function dls_wd_tpf_existing_message_id($post_id, $destination_key) {
        $destination_key = sanitize_key((string) $destination_key);
        if (absint($post_id) < 1 || $destination_key === '') {
            return 0;
        }

        [, $log] = dls_wd_tpf_log_meta($post_id);
        if (empty($log[$destination_key]) || !is_array($log[$destination_key])) {
            return 0;
        }

        return absint($log[$destination_key]['message_id'] ?? 0);
    }
}

if (!function_exists('dls_wd_tpf_fake_response')) {
    function dls_wd_tpf_fake_response($result) {
        return [
            'headers' => [],
            'body' => wp_json_encode(['ok' => true, 'result' => $result]),
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies' => [],
            'filename' => null,
        ];
    }
}

if (!function_exists('dls_wd_tpf_allowed_html')) {
    function dls_wd_tpf_allowed_html() {
        if (function_exists('dls_wd_tp_allowed_telegram_html')) {
            return dls_wd_tp_allowed_telegram_html();
        }

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

if (!function_exists('dls_wd_tpf_normalize_firm_quote')) {
    function dls_wd_tpf_normalize_firm_quote($text) {
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (!preg_match('/^\s*Firm Quote\s*\R\s*/iu', $text)) {
            return $text;
        }

        $text = preg_replace('/^\s*Firm Quote\s*\R\s*/iu', '', $text, 1);
        $text = preg_replace('/^\s*Позиція фірми:\s*/u', '<b>Позиція фірми:</b>' . "\n", $text, 1);

        if (preg_match('/\R\s*\Rhttps?:\/\//iu', $text, $match, PREG_OFFSET_CAPTURE)) {
            $offset = (int) $match[0][1];
            return '<blockquote>' . rtrim(substr($text, 0, $offset)) . '</blockquote>' . substr($text, $offset);
        }

        return '<blockquote>' . trim($text) . '</blockquote>';
    }
}

if (!function_exists('dls_wd_tpf_apply_channel_footer')) {
    function dls_wd_tpf_apply_channel_footer($post_id, $destination_key, $text) {
        if ($post_id < 1 || $destination_key === '' || !function_exists('dls_wd_tp_footer_settings') || !function_exists('dls_wd_tp_footer_text')) {
            return $text;
        }

        $footer_settings = dls_wd_tp_footer_settings($post_id);
        if (empty($footer_settings[$destination_key]['custom'])) {
            return $text;
        }

        if (function_exists('dls_wd_tp_remove_shared_footer')) {
            $text = dls_wd_tp_remove_shared_footer($text);
        }

        $footer = dls_wd_tp_footer_text($footer_settings[$destination_key]['rows'] ?? []);
        return $footer !== '' ? trim($text . "\n\n" . $footer) : trim($text);
    }
}

if (!function_exists('dls_wd_tpf_prepare_body')) {
    function dls_wd_tpf_prepare_body($body, $post_id, $destination_key) {
        if (!is_array($body)) {
            return $body;
        }

        if ($post_id > 0 && function_exists('dls_writing_desk_telegram_text')) {
            $default_text = function_exists('dls_writing_desk_get_telegram_default_text')
                ? dls_writing_desk_get_telegram_default_text($post_id)
                : (string) ($body['text'] ?? '');
            $body['text'] = dls_writing_desk_telegram_text($post_id, [
                'description' => '',
                'default_text' => $default_text,
            ]);
        }

        $body['disable_web_page_preview'] = 'true';
        $body['link_preview_options'] = wp_json_encode(['is_disabled' => true]);
        $body['parse_mode'] = 'HTML';

        if (!empty($body['text'])) {
            $text = dls_wd_tpf_normalize_firm_quote((string) $body['text']);
            $text = dls_wd_tpf_apply_channel_footer($post_id, $destination_key, $text);
            $body['text'] = wp_kses($text, dls_wd_tpf_allowed_html());
        }

        return $body;
    }
}

if (!function_exists('dls_wd_tpf_prepare_send_message')) {
    function dls_wd_tpf_prepare_send_message($args, $url) {
        if (!dls_wd_tpf_is_telegram_api($url, 'sendMessage') || empty($args['body']) || !is_array($args['body'])) {
            return $args;
        }

        $post_id = dls_wd_tpf_current_post_id();
        $destination_key = dls_wd_tpf_destination_key_from_chat($args['body']['chat_id'] ?? '');
        $args['body'] = dls_wd_tpf_prepare_body($args['body'], $post_id, $destination_key);

        return $args;
    }
}
add_filter('http_request_args', 'dls_wd_tpf_prepare_send_message', 120, 2);

if (!function_exists('dls_wd_tpf_edit_existing_message')) {
    function dls_wd_tpf_edit_existing_message($preempt, $args, $url) {
        if (!dls_wd_tpf_is_telegram_api($url, 'sendMessage') || empty($args['body']) || !is_array($args['body'])) {
            return $preempt;
        }

        $post_id = dls_wd_tpf_current_post_id();
        $chat_id = trim((string) ($args['body']['chat_id'] ?? ''));
        $destination_key = dls_wd_tpf_destination_key_from_chat($chat_id);
        $message_id = dls_wd_tpf_existing_message_id($post_id, $destination_key);
        if ($post_id < 1 || $chat_id === '' || $destination_key === '' || $message_id < 1) {
            return $preempt;
        }

        $body = dls_wd_tpf_prepare_body($args['body'], $post_id, $destination_key);
        $body['message_id'] = $message_id;
        unset($body['disable_notification']);

        $edit_args = $args;
        $edit_args['body'] = $body;
        $edit_url = str_replace('/sendMessage', '/editMessageText', (string) $url);

        remove_filter('pre_http_request', 'dls_wd_tpf_edit_existing_message', 10);
        $response = wp_remote_post($edit_url, $edit_args);
        add_filter('pre_http_request', 'dls_wd_tpf_edit_existing_message', 10, 3);

        if (is_wp_error($response)) {
            return $preempt;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($decoded) || empty($decoded['ok'])) {
            return $preempt;
        }

        $GLOBALS['dls_wd_tpf_skip_photo_for_chat'][$chat_id] = true;
        return $response;
    }
}
add_filter('pre_http_request', 'dls_wd_tpf_edit_existing_message', 10, 3);

if (!function_exists('dls_wd_tpf_skip_photo_after_edit')) {
    function dls_wd_tpf_skip_photo_after_edit($preempt, $args, $url) {
        if (!dls_wd_tpf_is_telegram_api($url, 'sendPhoto') || empty($args['body']) || !is_array($args['body'])) {
            return $preempt;
        }

        $chat_id = trim((string) ($args['body']['chat_id'] ?? ''));
        if ($chat_id === '' || empty($GLOBALS['dls_wd_tpf_skip_photo_for_chat'][$chat_id])) {
            return $preempt;
        }

        unset($GLOBALS['dls_wd_tpf_skip_photo_for_chat'][$chat_id]);
        return dls_wd_tpf_fake_response(['message_id' => 0]);
    }
}
add_filter('pre_http_request', 'dls_wd_tpf_skip_photo_after_edit', 11, 3);
