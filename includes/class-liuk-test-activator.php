<?php
/**
 * Class responsible for plugin activation
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
 * Class responsible for plugin activation
 */
class LIUK_Test_Activator {
    
    /**
     * Plugin activation function
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create questions table
        $table_questions = $wpdb->prefix . 'liuk_questions';
        $sql_questions = "CREATE TABLE $table_questions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            question_text text NOT NULL,
            question_type varchar(20) NOT NULL DEFAULT 'multiple_choice',
            option_a text NOT NULL,
            option_b text NOT NULL,
            option_c text,
            option_d text,
            correct_answer char(1) NOT NULL,
            category varchar(100) NOT NULL,
            difficulty varchar(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Alter the questions table to add the feedback column if it doesn't exist
        $check_feedback_column = $wpdb->get_results("SHOW COLUMNS FROM $table_questions LIKE 'feedback'");
        if (empty($check_feedback_column)) {
            $wpdb->query("ALTER TABLE $table_questions ADD COLUMN feedback text");
        }

        // Create mock tests table
        $table_tests = $wpdb->prefix . 'liuk_tests';
        $sql_tests = "CREATE TABLE $table_tests (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            test_name varchar(100) NOT NULL,
            questions text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Create user tests table
        $table_user_tests = $wpdb->prefix . 'liuk_user_tests';
        $sql_user_tests = "CREATE TABLE $table_user_tests (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            test_id mediumint(9) NOT NULL,
            score int(3) NOT NULL,
            max_score int(3) NOT NULL,
            wrong_questions text,
            completion_time int(5) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Create user wrong questions table
        $table_user_wrong = $wpdb->prefix . 'liuk_user_wrong_questions';
        $sql_user_wrong = "CREATE TABLE $table_user_wrong (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            question_id mediumint(9) NOT NULL,
            wrong_count int(3) DEFAULT 1 NOT NULL,
            last_wrong datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_question (user_id, question_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_questions);
        dbDelta($sql_tests);
        dbDelta($sql_user_tests);
        dbDelta($sql_user_wrong);
        
        // Add capabilities for admin
        $admin_role = get_role('administrator');
        $admin_role->add_cap('manage_liuk_test');
        
        // Add plugin options
        add_option('liuk_test_page', 0); // Page ID for tests
    }
}