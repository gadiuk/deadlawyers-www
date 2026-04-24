<?php
/**
 * Plugin Name: DLS Native Authors Activator
 * Description: Keeps the native author MU plugins disabled by default for production recovery.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Keep the native-author stack on for the normal WordPress editor.
// The Writing Desk is controlled separately.
if (!defined('DLS_NATIVE_AUTHORS_ACTIVE')) {
    define('DLS_NATIVE_AUTHORS_ACTIVE', true);
}
