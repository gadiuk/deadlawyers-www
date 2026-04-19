<?php
/**
 * Plugin Name: DLS Native Authors Activator
 * Description: Enables native author MU plugins unless explicitly disabled in wp-config.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Allow wp-config.php to disable with: define('DLS_NATIVE_AUTHORS_ACTIVE', false);
if (!defined('DLS_NATIVE_AUTHORS_ACTIVE')) {
    define('DLS_NATIVE_AUTHORS_ACTIVE', true);
}
