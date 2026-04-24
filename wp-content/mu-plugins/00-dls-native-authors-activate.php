<?php
/**
 * Plugin Name: DLS Native Authors Activator
 * Description: Keeps the native author MU plugins disabled by default for production recovery.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Keep the custom native-author stack off so PublishPress Authors
// remains the only live author system.
if (!defined('DLS_NATIVE_AUTHORS_ACTIVE')) {
    define('DLS_NATIVE_AUTHORS_ACTIVE', false);
}
