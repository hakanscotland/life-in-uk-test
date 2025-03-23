<?php
/**
 * Public-facing functionality of the plugin
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.3
 *
 * @package    Life_In_UK_Test
 * @subpackage Life_In_UK_Test/public
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The public-facing functionality of the plugin
 */
class LIUK_Test_Public {
    
    /**
     * Register the stylesheets for the public-facing side of the site
     */
    public function enqueue_styles() {
        wp_enqueue_style('liuk-test-public', LIUK_TEST_PLUGIN_URL . 'public/css/liuk-test-public.css', array(), LIUK_TEST_VERSION, 'all');
        wp_enqueue_style('liuk-test-responsive', LIUK_TEST_PLUGIN_URL . 'public/css/liuk-test-responsive.css', array('liuk-test-public'), LIUK_TEST_VERSION, 'all');
    }
    /**
     * Register the JavaScript for the public-facing side of the site
     */
    public function enqueue_scripts() {
        wp_enqueue_script('liuk-test-public', LIUK_TEST_PLUGIN_URL . 'public/js/liuk-test-public.js', array('jquery'), LIUK_TEST_VERSION, false);
        
        wp_localize_script('liuk-test-public', 'liuk_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('liuk_test_nonce')
        ));
    }
    
    /**
     * Render the test shortcode
     */
    public function display_test($atts) {
        $atts = shortcode_atts(array(
            'test_id' => 0,
            'wrong_questions' => 0
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<p>Please log in to take the test.</p>';
        }
        
        $test_id = intval($atts['test_id']);
        $wrong_questions = intval($atts['wrong_questions']);
        
        if ($wrong_questions) {
            return $this->display_wrong_questions_test();
        } elseif ($test_id > 0) {
            return $this->display_specific_test($test_id);
        } else {
            return $this->display_random_test();
        }
    }
    
    /**
     * Display a test based on user's wrong questions
     */
    private function display_wrong_questions_test() {
        global $wpdb;
        $user_id = get_current_user_id();
        
        // Get user's wrong questions
        $table_wrong = $wpdb->prefix . 'liuk_user_wrong_questions';
        $wrong_questions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT question_id FROM $table_wrong WHERE user_id = %d ORDER BY wrong_count DESC, last_wrong DESC LIMIT 24",
                $user_id
            ),
            ARRAY_A
        );
        
        if (count($wrong_questions) < 5) {
            return '<p>You need at least 5 wrong questions in your history to create a practice test. Please take some regular tests first.</p>';
        }
        
        $question_ids = array_column($wrong_questions, 'question_id');
        
        // If less than 24 wrong questions, fill with random questions
        if (count($question_ids) < 24) {
            $table_questions = $wpdb->prefix . 'liuk_questions';
            $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));
            $additional_questions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id FROM $table_questions WHERE id NOT IN ($placeholders) ORDER BY RAND() LIMIT %d",
                    array_merge($question_ids, array(24 - count($question_ids)))
                ),
                ARRAY_A
            );
            
            $additional_ids = array_column($additional_questions, 'id');
            $question_ids = array_merge($question_ids, $additional_ids);
        }
        
        // Shuffle the question IDs
        shuffle($question_ids);
        
        // Limit to 24 questions
        $question_ids = array_slice($question_ids, 0, 24);
        
        return $this->render_test($question_ids, 'Wrong Questions Practice Test');
    }
    
    /**
     * Display a specific test by ID
     */
    private function display_specific_test($test_id) {
        global $wpdb;
        $table_tests = $wpdb->prefix . 'liuk_tests';
        
        $test = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_tests WHERE id = %d",
                $test_id
            )
        );
        
        if (!$test) {
            return '<p>Test not found.</p>';
        }
        
        $question_ids = json_decode($test->questions);
        
        return $this->render_test($question_ids, $test->test_name);
    }
    
    /**
     * Display a random test
     */
    private function display_random_test() {
        global $wpdb;
        $table_questions = $wpdb->prefix . 'liuk_questions';
        
        $questions = $wpdb->get_results(
            "SELECT id FROM $table_questions ORDER BY RAND() LIMIT 24",
            ARRAY_A
        );
        
        if (count($questions) < 24) {
            return '<p>Not enough questions available. Please contact the administrator.</p>';
        }
        
        $question_ids = array_column($questions, 'id');
        
        return $this->render_test($question_ids, 'Random Mock Test');
    }
    
    /**
     * Render the test with given question IDs
     */
    /**
 * Render the test with given question IDs
 */
private function render_test($question_ids, $test_name) {
    global $wpdb;
    $table_questions = $wpdb->prefix . 'liuk_questions';
    
    $output = '<div class="liuk-test-container">';
    $output .= '<h2>' . esc_html($test_name) . '</h2>';
    $output .= '<p>You have 45 minutes to answer all 24 questions. You need to score at least 75% (18 correct answers) to pass.</p>';
    
    // Create question navigation
    $output .= '<div class="liuk-question-navigation">';
    $output .= '<div class="liuk-time-limit">Time limit: <span id="liuk-timer-display">45:00</span></div>';
    $output .= '<div class="liuk-progress-bar"><div class="liuk-progress-fill"></div></div>';

    // Question number buttons
    $output .= '<div class="liuk-question-numbers">';
    for ($i = 1; $i <= count($question_ids); $i++) {
        $output .= '<button type="button" class="liuk-question-number" data-question="' . $i . '">' . $i . '</button>';
    }
    $output .= '</div>';

    // Question status legend
    $output .= '<div class="liuk-question-legend">';
    $output .= '<span class="liuk-legend-item liuk-correct">Correct</span>';
    $output .= '<span class="liuk-legend-item liuk-review">Review</span>';
    $output .= '<span class="liuk-legend-item liuk-incorrect">Incorrect</span>';
    $output .= '</div>';
    $output .= '</div>'; // End question navigation
    
    // Add data attribute for instant feedback setting
    $show_instant_feedback = get_option('liuk_instant_feedback', '1');
    $output .= '<form id="liuk-test-form" data-question-count="' . count($question_ids) . '" data-instant-feedback="' . $show_instant_feedback . '">';
    
    $question_number = 1;
    foreach ($question_ids as $question_id) {
        $question = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_questions WHERE id = %d",
                $question_id
            )
        );
        
        if (!$question) {
            continue;
        }
        
        $output .= '<div class="liuk-question" id="question-' . $question_number . '">';
        $output .= '<h3>Question ' . $question_number . '</h3>';
        $output .= '<p class="liuk-question-text">' . esc_html($question->question_text) . '</p>';
        
        // Handle different question types
        if ($question->question_type === 'true_false') {
            $options = array(
                'a' => 'True',
                'b' => 'False'
            );
        } else {
            // Create array of options for multiple choice
            $options = array(
                'a' => $question->option_a,
                'b' => $question->option_b
            );
            
            // Only add options C and D if they are not empty
            if (!empty($question->option_c)) {
                $options['c'] = $question->option_c;
            }
            
            if (!empty($question->option_d)) {
                $options['d'] = $question->option_d;
            }
        }
        
        $keys = array_keys($options);
        shuffle($keys);
        
        $output .= '<div class="liuk-options">';
        foreach ($keys as $key) {
            $output .= '<div class="liuk-option">';
            $output .= '<input type="radio" name="question_' . $question->id . '" id="question_' . $question->id . '_' . $key . '" value="' . $key . '">';
            $output .= '<label for="question_' . $question->id . '_' . $key . '">' . esc_html($options[$key]) . '</label>';
            $output .= '</div>';
        }
        $output .= '</div>';
        
        // Add feedback area after the options
        $output .= '<div class="liuk-feedback-area" id="liuk-feedback-' . $question_number . '">';
        $output .= '<div class="liuk-feedback-heading"></div>';
        $output .= '<div class="liuk-feedback-text">' . (empty($question->feedback) ? '' : esc_html($question->feedback)) . '</div>';
        $output .= '</div>';
        
        $output .= '<input type="hidden" name="question_id_' . $question_number . '" value="' . $question->id . '">';
        $output .= '<input type="hidden" name="correct_answer_' . $question->id . '" value="' . $question->correct_answer . '">';
        $output .= '<input type="hidden" name="feedback_' . $question->id . '" value="' . esc_attr($question->feedback) . '">';
        $output .= '</div>';
        
        $question_number++;
    }
    
    $output .= '<div class="liuk-question-actions">';
    $output .= '<button type="button" class="liuk-prev-btn" id="liuk-prev-btn" disabled>Previous</button>';
    $output .= '<button type="button" class="liuk-review-btn" data-question="' . $question_number . '">Review</button>';
    $output .= '<button type="button" class="liuk-check-btn" data-question="' . $question_number . '">Check</button>';
    $output .= '<button type="button" class="liuk-next-btn" id="liuk-next-btn">Next</button>';
    $output .= '</div>';
        
    $output .= '<div class="liuk-submit">';
    $output .= '<button type="button" id="liuk-submit-btn">Submit Test</button>';
    $output .= '</div>';
    
    $output .= wp_nonce_field('liuk_test_nonce', 'liuk_test_nonce', true, false);
    $output .= '</form>';
    $output .= '</div>';
    
    return $output;
}
    
    /**
     * Display leaderboard
     */
    public function display_leaderboard() {
        global $wpdb;
        $table_user_tests = $wpdb->prefix . 'liuk_user_tests';
        
        // Get top 20 users by average score
        $leaderboard = $wpdb->get_results(
            "SELECT user_id, 
             COUNT(*) as tests_taken, 
             SUM(score) as total_score, 
             SUM(max_score) as total_possible,
             AVG(score/max_score*100) as average_percentage
             FROM $table_user_tests 
             GROUP BY user_id 
             HAVING tests_taken >= 3
             ORDER BY average_percentage DESC 
             LIMIT 20"
        );
        
        $output = '<div class="liuk-leaderboard-container">';
        $output .= '<h2>Leaderboard</h2>';
        
        if (empty($leaderboard)) {
            $output .= '<p>No data available yet.</p>';
        } else {
            $output .= '<table class="liuk-leaderboard">';
            $output .= '<thead><tr><th>Rank</th><th>User</th><th>Tests</th><th>Avg. Score</th><th>Pass Rate</th></tr></thead>';
            $output .= '<tbody>';
            
            $rank = 1;
            foreach ($leaderboard as $user) {
                $user_data = get_userdata($user->user_id);
                $display_name = $user_data ? $user_data->display_name : 'Unknown User';
                
                // Calculate pass rate
                $pass_count = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_user_tests WHERE user_id = %d AND (score/max_score) >= 0.75",
                        $user->user_id
                    )
                );
                $pass_rate = round(($pass_count / $user->tests_taken) * 100, 1);
                
                $output .= '<tr>';
                $output .= '<td>' . $rank . '</td>';
                $output .= '<td>' . esc_html($display_name) . '</td>';
                $output .= '<td>' . $user->tests_taken . '</td>';
                $output .= '<td>' . round($user->average_percentage, 1) . '%</td>';
                $output .= '<td>' . $pass_rate . '%</td>';
                $output .= '</tr>';
                
                $rank++;
            }
            
            $output .= '</tbody>';
            $output .= '</table>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * AJAX handler for submitting tests
     */
    public function submit_test() {
        check_ajax_referer('liuk_test_nonce', 'liuk_test_nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to submit a test.');
            return;
        }
        
        $user_id = get_current_user_id();
        $question_count = isset($_POST['question_count']) ? intval($_POST['question_count']) : 24;
        $completion_time = isset($_POST['completion_time']) ? intval($_POST['completion_time']) : 0;
        
        $score = 0;
        $wrong_questions = array();
        
        // Process answers
        for ($i = 1; $i <= $question_count; $i++) {
            $question_id_key = 'question_id_' . $i;
            if (!isset($_POST[$question_id_key])) {
                continue;
            }
            
            $question_id = intval($_POST[$question_id_key]);
            $answer_key = 'question_' . $question_id;
            $correct_key = 'correct_answer_' . $question_id;
            
            if (!isset($_POST[$answer_key]) || !isset($_POST[$correct_key])) {
                continue;
            }
            
            $user_answer = sanitize_text_field($_POST[$answer_key]);
            $correct_answer = sanitize_text_field($_POST[$correct_key]);
            
            if ($user_answer === $correct_answer) {
                $score++;
            } else {
                $wrong_questions[] = $question_id;
                $this->record_wrong_question($user_id, $question_id);
            }
        }
        
        // Save test result
        global $wpdb;
        $table_user_tests = $wpdb->prefix . 'liuk_user_tests';
        
        $wpdb->insert(
            $table_user_tests,
            array(
                'user_id' => $user_id,
                'test_id' => isset($_POST['test_id']) ? intval($_POST['test_id']) : 0,
                'score' => $score,
                'max_score' => $question_count,
                'wrong_questions' => json_encode($wrong_questions),
                'completion_time' => $completion_time
            ),
            array('%d', '%d', '%d', '%d', '%s', '%d')
        );
        
        $passed = ($score / $question_count) >= 0.75;
        $pass_threshold = ceil($question_count * 0.75);
        
        $result = array(
            'score' => $score,
            'max_score' => $question_count,
            'percentage' => round(($score / $question_count) * 100, 1),
            'passed' => $passed,
            'pass_threshold' => $pass_threshold,
            'completion_time' => $this->format_time($completion_time),
            'wrong_questions' => $wrong_questions
        );
        
        wp_send_json_success($result);
    }
    
    /**
     * Record a wrong question for a user
     */
    private function record_wrong_question($user_id, $question_id) {
        global $wpdb;
        $table_wrong = $wpdb->prefix . 'liuk_user_wrong_questions';
        
        // Check if entry exists
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_wrong WHERE user_id = %d AND question_id = %d",
                $user_id, $question_id
            )
        );
        
        if ($existing) {
            // Update existing record
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table_wrong SET wrong_count = wrong_count + 1, last_wrong = CURRENT_TIMESTAMP WHERE id = %d",
                    $existing
                )
            );
        } else {
            // Insert new record
            $wpdb->insert(
                $table_wrong,
                array(
                    'user_id' => $user_id,
                    'question_id' => $question_id,
                    'wrong_count' => 1
                ),
                array('%d', '%d', '%d')
            );
        }
    }
    
    /**
     * Format time in minutes and seconds
     */
    private function format_time($seconds) {
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
    
    /**
 * AJAX handler for getting wrong questions test URL
 */
public function get_wrong_questions_test() {
    // Don't use 'liuk_test_nonce' here - use just 'nonce' as the parameter name
    check_ajax_referer('liuk_test_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to access this feature.');
        return;
    }
    
    // Get test page URL from options if available
    $test_page_id = get_option('liuk_test_page', 0);
    $test_page_url = $test_page_id > 0 ? get_permalink($test_page_id) : home_url();
    
    // Add wrong_questions parameter
    $wrong_questions_test_url = add_query_arg('wrong_questions', '1', $test_page_url);
    
    // Add debug information
    $debug_info = array(
        'url' => $wrong_questions_test_url,
        'test_page_id' => $test_page_id,
        'test_page_url' => $test_page_url
    );
    
$passed = ($score / $question_count) >= 0.75;
$pass_threshold = ceil($question_count * 0.75);

// Collect detailed question info if showing answers is enabled
$show_answers = get_option('liuk_show_answers', '1');
$show_correct_answers = get_option('liuk_show_correct_answers', '1');
$question_details = array();

if ($show_answers === '1') {
    global $wpdb;
    $table_questions = $wpdb->prefix . 'liuk_questions';
    
    for ($i = 1; $i <= $question_count; $i++) {
        $question_id_key = 'question_id_' . $i;
        if (!isset($_POST[$question_id_key])) {
            continue;
        }
        
        $question_id = intval($_POST[$question_id_key]);
        $answer_key = 'question_' . $question_id;
        $correct_key = 'correct_answer_' . $question_id;
        
        if (!isset($_POST[$correct_key])) {
            continue;
        }
        
        $user_answer = isset($_POST[$answer_key]) ? sanitize_text_field($_POST[$answer_key]) : '';
        $correct_answer = sanitize_text_field($_POST[$correct_key]);
        $is_correct = ($user_answer === $correct_answer);
        
        // Get question text and options
        $question = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT question_text, option_a, option_b, option_c, option_d FROM $table_questions WHERE id = %d",
                $question_id
            )
        );
        
        if ($question) {
            $question_details[] = array(
                'id' => $question_id,
                'text' => $question->question_text,
                'options' => array(
                    'a' => $question->option_a,
                    'b' => $question->option_b,
                    'c' => $question->option_c,
                    'd' => $question->option_d
                ),
                'user_answer' => $user_answer,
                'correct_answer' => $correct_answer,
                'is_correct' => $is_correct
            );
        }
    }
}

$result = array(
    'score' => $score,
    'max_score' => $question_count,
    'percentage' => round(($score / $question_count) * 100, 1),
    'passed' => $passed,
    'pass_threshold' => $pass_threshold,
    'completion_time' => $this->format_time($completion_time),
    'wrong_questions' => $wrong_questions,
    'show_answers' => $show_answers === '1',
    'show_correct_answers' => $show_correct_answers === '1',
    'question_details' => $question_details
);

wp_send_json_success($result);
    }
}