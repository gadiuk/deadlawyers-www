<?php
/**
 * Plugin Name: DLS Native Authors Activator
 * Description: Keeps the native author MU plugins disabled by default for production recovery.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Recovery default: keep the native-author stack off unless explicitly re-enabled in wp-config.php.
if (!defined('DLS_NATIVE_AUTHORS_ACTIVE')) {
    define('DLS_NATIVE_AUTHORS_ACTIVE', false);
}
