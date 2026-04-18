<?php
/*
 * Plugin Name: TotalPoll – Lite
 * Plugin URI: https://totalsuite.net/products/totalpoll/
 * Description: TotalPoll is a responsive and customizable poll plugin that will help you create voting contest, competition, image poll, simple poll.
 * Version: 4.12.0
 * Author: TotalSuite
 * Author URI: https://totalsuite.net/
 * Text Domain: totalpoll
 * Domain Path: languages
 * Requires at least: 4.8
 * Requires PHP: 5.6
 * Tested up to: 6.8.2
 */



// Root plugin file name
define( 'TOTALPOLL_ROOT', __FILE__ );

// TotalPoll environment
$env = require dirname( __FILE__ ) . '/env.php';

// Include plugin setup
require_once dirname( __FILE__ ) . '/setup.php';

// Setup
$plugin = new TotalPollSetup( $env );

// Oh yeah, we're up and running!
