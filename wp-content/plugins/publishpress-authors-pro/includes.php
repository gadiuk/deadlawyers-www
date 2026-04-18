<?php
/**
 * File responsible for defining basic general constants used by the plugin.
 *
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

if (! defined('ABSPATH')) {
    die('No direct script access allowed.');
}

if (! defined('PP_AUTHORS_PRO_LOADED')) {
    require_once __DIR__ . '/defines.php';
    require_once __DIR__ . '/deprecated.php';


    if (file_exists(PP_AUTHORS_PRO_LIB_VENDOR_PATH . '/cmb2/cmb2/init.php')) {
        require_once PP_AUTHORS_PRO_LIB_VENDOR_PATH . '/cmb2/cmb2/init.php';
    }
}
