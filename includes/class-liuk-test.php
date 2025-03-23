<?php
/**
 * The core plugin class
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.1
 *
 * @package    Life_In_UK_Test
 * @subpackage Life_In_UK_Test/includes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The core plugin class
 */
class LIUK_Test {
    
    /**
     * Define the core functionality of the plugin
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Load the required dependencies for this plugin
     */
    private function load_dependencies() {
        require_once LIUK_TEST_PLUGIN_DIR . 'admin/class-liuk-test-admin.php';
        require_once LIUK_TEST_PLUGIN_DIR . 'public/class-liuk-test-public.php';
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        $admin = new LIUK_Test_Admin();
        
        // Admin assets
        add_action('admin_enqueue_scripts', array($admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($admin, 'enqueue_scripts'));
        
        // Admin menu
        add_action('admin_menu', array($admin, 'add_admin_menu'));
        
        // Admin AJAX handlers
        add_action('wp_ajax_liuk_save_question', array($admin, 'save_question'));
        add_action('wp_ajax_liuk_get_question', array($admin, 'get_question'));
        add_action('wp_ajax_liuk_delete_question', array($admin, 'delete_question'));
        add_action('wp_ajax_liuk_create_test', array($admin, 'create_test'));
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        $public = new LIUK_Test_Public();
        
        // Public assets
        add_action('wp_enqueue_scripts', array($public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($public, 'enqueue_scripts'));
        
        // Shortcodes
        add_shortcode('liuk_test', array($public, 'display_test'));
        add_shortcode('liuk_my_progress', array($public, 'display_user_progress'));
        add_shortcode('liuk_leaderboard', array($public, 'display_leaderboard'));
        
        // Public AJAX handlers
        add_action('wp_ajax_liuk_submit_test', array($public, 'submit_test'));
        add_action('wp_ajax_liuk_get_wrong_questions_test', array($public, 'get_wrong_questions_test'));
    }
    
    /**
     * Run the plugin
     */
    public function run() {
        // Plugin is now running
    }
}