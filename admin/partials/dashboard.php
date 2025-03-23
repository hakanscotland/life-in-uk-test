<?php
/**
 * Admin dashboard template
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_questions = $wpdb->prefix . 'liuk_questions';
$table_tests = $wpdb->prefix . 'liuk_tests';
$table_user_tests = $wpdb->prefix . 'liuk_user_tests';

$question_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_questions");
$test_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_tests");
$user_test_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_user_tests");
$user_count = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_user_tests");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="liuk-admin-dashboard">
        <div class="liuk-admin-card">
            <h2><?php echo $question_count; ?></h2>
            <p>Questions in Database</p>
        </div>
        <div class="liuk-admin-card">
            <h2><?php echo $test_count; ?></h2>
            <p>Mock Tests Created</p>
        </div>
        <div class="liuk-admin-card">
            <h2><?php echo $user_test_count; ?></h2>
            <p>Tests Taken</p>
        </div>
        <div class="liuk-admin-card">
            <h2><?php echo $user_count; ?></h2>
            <p>Active Users</p>
        </div>
    </div>
    
    <div class="liuk-admin-section">
        <h2>Recent Activity</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Score</th>
                    <th>Result</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent_tests = $wpdb->get_results(
                    "SELECT u.user_id, u.score, u.max_score, u.created_at
                     FROM $table_user_tests u
                     ORDER BY u.created_at DESC
                     LIMIT 10"
                );
                
                foreach ($recent_tests as $test) {
                    $user_data = get_userdata($test->user_id);
                    $display_name = $user_data ? $user_data->display_name : 'Unknown User';
                    $score_percentage = round(($test->score / $test->max_score) * 100, 1);
                    $passed = $score_percentage >= 75;
                    ?>
                    <tr>
                        <td><?php echo esc_html($display_name); ?></td>
                        <td><?php echo $test->score . '/' . $test->max_score . ' (' . $score_percentage . '%)'; ?></td>
                        <td class="liuk-result <?php echo $passed ? 'passed' : 'failed'; ?>">
                            <?php echo $passed ? 'PASSED' : 'FAILED'; ?>
                        </td>
                        <td><?php echo date('M j, Y, g:i a', strtotime($test->created_at)); ?></td>
                    </tr>
                    <?php
                }
                
                if (empty($recent_tests)) {
                    echo '<tr><td colspan="4">No tests taken yet.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <div class="liuk-admin-section">
        <h2>Plugin Usage Instructions</h2>
        
        <h3>Getting Started</h3>
        <ol>
            <li>Add questions to the question bank (multiple choice or true/false)</li>
            <li>Create mock tests from your questions</li>
            <li>Place shortcodes on your pages to display tests to users</li>
            <li>Monitor user progress and performance</li>
        </ol>
        
        <h3>Shortcodes</h3>
        <p>Use these shortcodes to display the Life in the UK Test on your site:</p>
        <ul>
            <li><code>[liuk_test]</code> - Display a random mock test</li>
            <li><code>[liuk_test test_id="1"]</code> - Display a specific mock test</li>
            <li><code>[liuk_test wrong_questions="1"]</code> - Display a test based on user's wrong questions</li>
            <li><code>[liuk_my_progress]</code> - Display user's test history and progress</li>
            <li><code>[liuk_leaderboard]</code> - Display the leaderboard</li>
        </ul>
        
        <h3>Question Types</h3>
        <p>The plugin supports two question types:</p>
        <ul>
            <li><strong>Multiple Choice</strong> - Traditional questions with 2-4 possible answers</li>
            <li><strong>True/False</strong> - Questions with only True or False as possible answers</li>
        </ul>
        
        <h3>Features</h3>
        <ul>
            <li>Track user progress and wrong questions</li>
            <li>Generate targeted practice tests from users' wrong answers</li>
            <li>Display leaderboard to encourage competition</li>
            <li>Time-limited tests (45 minutes) to simulate real exam conditions</li>
            <li>Randomized question order and option order</li>
        </ul>
    </div>
</div>