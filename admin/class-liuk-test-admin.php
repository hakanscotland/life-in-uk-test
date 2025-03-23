<?php
/**
 * Admin-specific functionality of the plugin
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.2
 *
 * @package    Life_In_UK_Test
 * @subpackage Life_In_UK_Test/admin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The admin-specific functionality of the plugin
 */
class LIUK_Test_Admin {
    
    /**
     * Register the stylesheets for the admin area
     */
    public function enqueue_styles() {
        wp_enqueue_style('liuk-test-admin', LIUK_TEST_PLUGIN_URL . 'admin/css/liuk-test-admin.css', array(), LIUK_TEST_VERSION, 'all');
    }
    
    /**
     * Register the JavaScript for the admin area
     */
    public function enqueue_scripts() {
        wp_enqueue_script('liuk-test-admin', LIUK_TEST_PLUGIN_URL . 'admin/js/liuk-test-admin.js', array('jquery'), LIUK_TEST_VERSION, false);
        
        wp_localize_script('liuk-test-admin', 'liuk_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('liuk_test_nonce')
        ));
    }
    
    /**
     * Add menu items to the admin area
     */
    public function add_admin_menu() {
        add_menu_page(
            'Life in the UK Test',
            'UK Test',
            'manage_liuk_test',
            'liuk-test',
            array($this, 'display_admin_dashboard'),
            'dashicons-welcome-learn-more',
            30
        );
        
        add_submenu_page(
            'liuk-test',
            'Question Bank',
            'Question Bank',
            'manage_liuk_test',
            'liuk-questions',
            array($this, 'display_question_bank')
        );
        
        add_submenu_page(
            'liuk-test',
            'Mock Tests',
            'Mock Tests',
            'manage_liuk_test',
            'liuk-tests',
            array($this, 'display_mock_tests')
        );
        
        add_submenu_page(
            'liuk-test',
            'User Progress',
            'User Progress',
            'manage_liuk_test',
            'liuk-progress',
            array($this, 'display_user_progress')
        );
        
        add_submenu_page(
            'liuk-test',
            'Import Questions',
            'Import Questions',
            'manage_liuk_test',
            'liuk-import',
            array($this, 'display_import_questions')
        );
        
        add_submenu_page(
            'liuk-test',
            'Settings',
            'Settings',
            'manage_liuk_test',
            'liuk-settings',
            array($this, 'display_settings')
        );

    }
    
    /**
     * Display the import questions page
     */
    public function display_import_questions() {
        include LIUK_TEST_PLUGIN_DIR . 'admin/partials/import-questions.php';
    }
    
    /**
     * Display the admin dashboard
     */
    public function display_admin_dashboard() {
        include LIUK_TEST_PLUGIN_DIR . 'admin/partials/dashboard.php';
    }
    
    /**
     * Display the question bank page
     */
    public function display_question_bank() {
        include LIUK_TEST_PLUGIN_DIR . 'admin/partials/question-bank.php';
    }
    
    /**
     * Display the mock tests page
     */
    public function display_mock_tests() {
        include LIUK_TEST_PLUGIN_DIR . 'admin/partials/mock-tests.php';
    }
    
    /**
     * Display the user progress page
     */
    public function display_user_progress() {
        include LIUK_TEST_PLUGIN_DIR . 'admin/partials/user-progress.php';
    }
    
    /**
     * Display the settings page
     */
    public function display_settings() {
        include LIUK_TEST_PLUGIN_DIR . 'admin/partials/settings.php';
    }
        
    /**
     * AJAX handler for saving questions
     */
    public function save_question() {
        check_ajax_referer('liuk_test_nonce', 'nonce');
        
        if (!current_user_can('manage_liuk_test')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $question_text = sanitize_textarea_field($_POST['question_text']);
        $question_type = sanitize_text_field($_POST['question_type']);
        
        // Handle different question types
        if ($question_type === 'true_false') {
            $option_a = 'True';
            $option_b = 'False';
            $option_c = '';
            $option_d = '';
        } else {
            $option_a = sanitize_textarea_field($_POST['option_a']);
            $option_b = sanitize_textarea_field($_POST['option_b']);
            $option_c = isset($_POST['option_c']) ? sanitize_textarea_field($_POST['option_c']) : '';
            $option_d = isset($_POST['option_d']) ? sanitize_textarea_field($_POST['option_d']) : '';
        }
        
        $correct_answer = sanitize_text_field($_POST['correct_answer']);
        $category = sanitize_text_field($_POST['category']);
        $difficulty = sanitize_text_field($_POST['difficulty']);
        $feedback = isset($_POST['feedback']) ? sanitize_textarea_field($_POST['feedback']) : '';
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'liuk_questions';
        
        $data = array(
            'question_text' => $question_text,
            'question_type' => $question_type,
            'option_a' => $option_a,
            'option_b' => $option_b,
            'option_c' => $option_c,
            'option_d' => $option_d,
            'correct_answer' => $correct_answer,
            'category' => $category,
            'difficulty' => $difficulty,
            'feedback' => $feedback
        );
        
        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        
        if ($question_id > 0) {
            // Update existing question
            $wpdb->update($table_name, $data, array('id' => $question_id), $format, array('%d'));
            wp_send_json_success('Question updated successfully');
        } else {
            // Insert new question
            $wpdb->insert($table_name, $data, $format);
            wp_send_json_success('Question added successfully');
        }
    }
    
    /**
     * AJAX handler for getting question data
     */
    public function get_question() {
        check_ajax_referer('liuk_test_nonce', 'nonce');
        
        if (!current_user_can('manage_liuk_test')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        
        if ($question_id <= 0) {
            wp_send_json_error('Invalid question ID');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'liuk_questions';
        
        $question = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $question_id
            ),
            ARRAY_A
        );
        
        if (!$question) {
            wp_send_json_error('Question not found');
            return;
        }
        
        wp_send_json_success($question);
    }
    
    /**
     * AJAX handler for deleting questions
     */
    public function delete_question() {
        check_ajax_referer('liuk_test_nonce', 'nonce');
        
        if (!current_user_can('manage_liuk_test')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        
        if ($question_id <= 0) {
            wp_send_json_error('Invalid question ID');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'liuk_questions';
        
        $wpdb->delete($table_name, array('id' => $question_id), array('%d'));
        wp_send_json_success('Question deleted successfully');
    }
    
    /**
     * AJAX handler for creating tests
     */
    public function create_test() {
        check_ajax_referer('liuk_test_nonce', 'nonce');
        
        if (!current_user_can('manage_liuk_test')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $test_name = sanitize_text_field($_POST['test_name']);
        $categories = isset($_POST['categories']) ? array_map('sanitize_text_field', $_POST['categories']) : array();
        
        global $wpdb;
        $table_questions = $wpdb->prefix . 'liuk_questions';
        $table_tests = $wpdb->prefix . 'liuk_tests';
        
        // Query to get random questions
        $where_clause = '';
        if (!empty($categories)) {
            $categories_placeholders = implode(',', array_fill(0, count($categories), '%s'));
            $where_clause = $wpdb->prepare("WHERE category IN ($categories_placeholders)", $categories);
        }
        
        $questions = $wpdb->get_results(
            "SELECT id FROM $table_questions $where_clause ORDER BY RAND() LIMIT 24",
            ARRAY_A
        );
        
        if (count($questions) < 24) {
            wp_send_json_error('Not enough questions available. Please add more questions first.');
            return;
        }
        
        $question_ids = array_column($questions, 'id');
        
        $wpdb->insert(
            $table_tests,
            array(
                'test_name' => $test_name,
                'questions' => json_encode($question_ids)
            ),
            array('%s', '%s')
        );
        
        wp_send_json_success('Mock test created successfully');
    }
}