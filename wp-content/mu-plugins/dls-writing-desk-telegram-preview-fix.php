<?php
/**
 * Plugin Name: DLS Writing Desk Telegram Preview Fix
 * Description: Keeps only one Telegram destination as preview so other channels show as post channels.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dls_wd_tg_preview_fix_pick_index')) {
    function dls_wd_tg_preview_fix_pick_index($rows) {
        $fallback = null;
        $preferred = null;

        foreach ((array) $rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $platform = sanitize_key((string) ($row['platform'] ?? ''));
            if ($platform !== 'telegram' || empty($row['preview'])) {
                continue;
            }

            if ($fallback === null) {
                $fallback = $index;
            }

            $name = function_exists('mb_strtolower')
                ? mb_strtolower((string) ($row['name'] ?? ''), 'UTF-8')
                : strtolower((string) ($row['name'] ?? ''));
            $destination = function_exists('mb_strtolower')
                ? mb_strtolower((string) ($row['destination'] ?? ''), 'UTF-8')
                : strtolower((string) ($row['destination'] ?? ''));
            $needle_text = $name . ' ' . $destination;

            foreach (['preview', 'test', 'тест', 'прев'] as $needle) {
                if (strpos($needle_text, $needle) !== false) {
                    $preferred = $index;
                    break 2;
                }
            }
        }

        return $preferred !== null ? $preferred : $fallback;
    }
}

if (!function_exists('dls_wd_tg_preview_fix_normalize')) {
    function dls_wd_tg_preview_fix_normalize() {
        $rows = get_option('dls_writing_desk_destinations', []);
        if (!is_array($rows) || empty($rows)) {
            return;
        }

        $preview_index = dls_wd_tg_preview_fix_pick_index($rows);
        if ($preview_index === null) {
            return;
        }

        $changed = false;
        foreach ($rows as $index => $row) {
            if (!is_array($row) || sanitize_key((string) ($row['platform'] ?? '')) !== 'telegram') {
                continue;
            }

            if ((string) $index === (string) $preview_index) {
                if (empty($rows[$index]['preview'])) {
                    $rows[$index]['preview'] = 1;
                    $changed = true;
                }
                continue;
            }

            if (!empty($rows[$index]['preview'])) {
                unset($rows[$index]['preview']);
                $changed = true;
            }
        }

        if ($changed) {
            update_option('dls_writing_desk_destinations', $rows, false);
        }
    }
}
add_action('admin_init', 'dls_wd_tg_preview_fix_normalize', 4);
