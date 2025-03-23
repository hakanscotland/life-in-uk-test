<?php
/**
 * Plugin Name: Life in the UK Test
 * Plugin URI: https://secondmedia.co.uk
 * Description: Comprehensive solution for Life in the UK Test preparation
 * Version: 1.0.1
 * Author: Hakan Dag
 * Author URI: https://secondmedia.co.uk
 * License: GPL-2.0+
 * Text Domain: liuk-test
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LIUK_TEST_VERSION', '1.0.1');
define('LIUK_TEST_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LIUK_TEST_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once LIUK_TEST_PLUGIN_DIR . 'includes/class-liuk-test-activator.php';
require_once LIUK_TEST_PLUGIN_DIR . 'includes/class-liuk-test-deactivator.php';
require_once LIUK_TEST_PLUGIN_DIR . 'includes/class-liuk-test.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('LIUK_Test_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('LIUK_Test_Deactivator', 'deactivate'));

// Initialize the plugin
function run_liuk_test() {
    $plugin = new LIUK_Test();
    $plugin->run();
}
run_liuk_test();