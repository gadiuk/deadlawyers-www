<?php
/**
 * PublishPress Authors Pro plugin bootstrap file.
 *
 * @link        https://publishpress.com/multiple-authors/
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 *
 * @publishpress-authors-pro
 * Plugin Name: PublishPress Authors Pro
 * Plugin URI:  https://publishpress.com/
 * Version: 4.13.0
 * Text Domain: publishpress-authors-pro
 * Domain Path: /languages
 * Requires at least: 5.5
 * Requires PHP: 7.2.5
 * Description: PublishPress Authors allows you to add multiple authors and guest authors to WordPress posts.
 * Author:      PublishPress
 * Author URI:  https://publishpress.com
 *
 * Based on Co-Authors Plus
 *  - Author: Mohammad Jangda, Daniel Bachhuber, Automattic
 *  - Copyright: 2008-2015 Shared and distributed between  Mohammad Jangda, Daniel Bachhuber, Weston Ruter
 */

global $wp_version;

$min_php_version = '7.2.5';
$min_wp_version  = '5.5';

// If the PHP or WP version is not compatible, terminate the plugin execution.
$invalid_php_version = version_compare(phpversion(), $min_php_version, '<');
$invalid_wp_version = version_compare($wp_version, $min_wp_version, '<');

if ($invalid_php_version || $invalid_wp_version) {
    return;
}

// Halt if there is a free version of the plugin already active, to avoid compatibility issue with older versions.
if (
    is_file(WP_PLUGIN_DIR . '/publishpress-authors/publishpress-authors.php')
    && is_readable(WP_PLUGIN_DIR . '/publishpress-authors/publishpress-authors.php')
) {
    if (! function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if (is_plugin_active('publishpress-authors/publishpress-authors.php')) {
        add_action('admin_notices', function () {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php
                    echo sprintf(
                        esc_html__(
                            'Please deactivate %1$s when %2$s is activated.',
                            'publishpress-authors'
                        ),
                        'PublishPress Authors',
                        'PublishPress Authors Pro'
                    ); ?></p>
            </div>
            <?php
        });

        return;
    }
}

if (! defined('PP_AUTHORS_PRO_LOADED') ) {

    if (! defined('PP_AUTHORS_PRO_LIB_VENDOR_PATH')) {
        define('PP_AUTHORS_PRO_LIB_VENDOR_PATH', __DIR__ . '/lib/vendor');
    }

    $instanceProtectionIncPath = PP_AUTHORS_PRO_LIB_VENDOR_PATH . '/publishpress/instance-protection/include.php';
    if (is_file($instanceProtectionIncPath) && is_readable($instanceProtectionIncPath)) {
        require_once $instanceProtectionIncPath;
    }

    if (class_exists('PublishPressInstanceProtection\\Config')) {
        $pluginCheckerConfig = new PublishPressInstanceProtection\Config();
        $pluginCheckerConfig->pluginSlug = 'publishpress-authors-pro';
        $pluginCheckerConfig->pluginName = 'PublishPress Authors Pro';
        $pluginCheckerConfig->isProPlugin = true;
        $pluginCheckerConfig->freePluginName = 'PublishPress Authors';

        $pluginChecker = new PublishPressInstanceProtection\InstanceChecker($pluginCheckerConfig);
    }

    $autoloadFilePath = PP_AUTHORS_PRO_LIB_VENDOR_PATH . '/autoload.php';
    if (! class_exists('ComposerAutoloaderInitPPAuthorsPro')
        && is_file($autoloadFilePath)
        && is_readable($autoloadFilePath)
    ) {
        require_once $autoloadFilePath;
    }

    define('PUBLISHPRESS_AUTHORS_SKIP_VERSION_NOTICES', true);

    // Initialize the free plugin.
    require_once PP_AUTHORS_PRO_LIB_VENDOR_PATH . '/publishpress/publishpress-authors/publishpress-authors.php';

    add_action('plugins_loaded', function() {
        require_once __DIR__ . '/includes.php';

        $plugin = new \PPAuthorsPro\Plugin();
        $plugin->init();

        do_action('plublishpress_authors_pro_loaded');
    }, -9);
}
