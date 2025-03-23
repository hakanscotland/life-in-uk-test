<?php
/**
 * User progress admin template
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_user_tests = $wpdb->prefix . 'liuk_user_tests';

// Get user stats
$user_stats = $wpdb->get_results(
    "SELECT user_id, 
     COUNT(*) as tests_taken, 
     SUM(CASE WHEN (score/max_score) >= 0.75 THEN 1 ELSE 0 END) as tests_passed,
     AVG(score/max_score*100) as average_score
     FROM $table_user_tests 
     GROUP BY user_id 
     ORDER BY average_score DESC"
);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="liuk-admin-section">
        <h2>User Statistics</h2>
        <p>This table shows test statistics for all users.</p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Tests Taken</th>
                    <th>Tests Passed</th>
                    <th>Pass Rate</th>
                    <th>Average Score</th>
                    <th>Last Test</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_stats as $user) : 
                    $user_data = get_userdata($user->user_id);
                    $display_name = $user_data ? $user_data->display_name : 'Unknown User';
                    $pass_rate = $user->tests_taken > 0 ? round(($user->tests_passed / $user->tests_taken) * 100, 1) : 0;
                    
                    // Get last test date
                    $last_test = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT created_at FROM $table_user_tests WHERE user_id = %d ORDER BY created_at DESC LIMIT 1",
                            $user->user_id
                        )
                    );
                    ?>
                    <tr>
                        <td><?php echo esc_html($display_name); ?></td>
                        <td><?php echo $user->tests_taken; ?></td>
                        <td><?php echo $user->tests_passed; ?></td>
                        <td><?php echo $pass_rate; ?>%</td>
                        <td><?php echo round($user->average_score, 1); ?>%</td>
                        <td><?php echo date('M j, Y', strtotime($last_test)); ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (empty($user_stats)) : ?>
                    <tr>
                        <td colspan="6">No user activity yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="liuk-admin-section">
        <h2>Most Missed Questions</h2>
        <p>This table shows the questions that users most frequently answer incorrectly.</p>
        
        <?php
        $missed_questions = $wpdb->get_results(
            "SELECT q.id, q.question_text, q.category, COUNT(w.id) as wrong_count
             FROM {$wpdb->prefix}liuk_questions q
             JOIN {$wpdb->prefix}liuk_user_wrong_questions w ON q.id = w.question_id
             GROUP BY q.id
             ORDER BY wrong_count DESC
             LIMIT 10"
        );
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="60%">Question</th>
                    <th width="20%">Category</th>
                    <th width="15%">Wrong Answers</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($missed_questions as $question) : ?>
                    <tr>
                        <td><?php echo $question->id; ?></td>
                        <td><?php echo esc_html($question->question_text); ?></td>
                        <td><?php echo esc_html($question->category); ?></td>
                        <td><?php echo $question->wrong_count; ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (empty($missed_questions)) : ?>
                    <tr>
                        <td colspan="4">No data available yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="liuk-admin-section">
        <h2>Test Duration Statistics</h2>
        <p>This chart shows the average time users take to complete tests.</p>
        
        <?php
        $duration_stats = $wpdb->get_results(
            "SELECT 
                CASE 
                    WHEN completion_time <= 900 THEN 'Under 15 minutes'
                    WHEN completion_time <= 1500 THEN '15-25 minutes'
                    WHEN completion_time <= 2100 THEN '25-35 minutes'
                    ELSE 'Over 35 minutes'
                END as duration_range,
                COUNT(*) as test_count
             FROM {$wpdb->prefix}liuk_user_tests
             GROUP BY duration_range
             ORDER BY MIN(completion_time)"
        );
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Time Range</th>
                    <th>Number of Tests</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_tests = array_sum(array_column($duration_stats, 'test_count'));
                foreach ($duration_stats as $stat) : 
                    $percentage = $total_tests > 0 ? round(($stat->test_count / $total_tests) * 100, 1) : 0;
                ?>
                    <tr>
                        <td><?php echo esc_html($stat->duration_range); ?></td>
                        <td><?php echo $stat->test_count; ?></td>
                        <td><?php echo $percentage; ?>%</td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (empty($duration_stats)) : ?>
                    <tr>
                        <td colspan="3">No data available yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>