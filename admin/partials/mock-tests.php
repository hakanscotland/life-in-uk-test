<?php
/**
 * Mock tests admin template
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_tests = $wpdb->prefix . 'liuk_tests';
$table_questions = $wpdb->prefix . 'liuk_questions';

// Get categories for filter
$categories = $wpdb->get_col("SELECT DISTINCT category FROM $table_questions ORDER BY category");

// Get tests
$tests = $wpdb->get_results("SELECT * FROM $table_tests ORDER BY id DESC");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="liuk-admin-actions">
        <button id="liuk-create-test" class="button button-primary">Create New Test</button>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="30%">Test Name</th>
                <th width="20%">Questions</th>
                <th width="20%">Created</th>
                <th width="25%">Shortcode</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tests as $test) : ?>
                <tr>
                    <td><?php echo $test->id; ?></td>
                    <td><?php echo esc_html($test->test_name); ?></td>
                    <td>
                        <?php
                        $question_ids = json_decode($test->questions);
                        echo count($question_ids) . ' questions';
                        ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($test->created_at)); ?></td>
                    <td><code>[liuk_test test_id="<?php echo $test->id; ?>"]</code></td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($tests)) : ?>
                <tr>
                    <td colspan="5">No tests created yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Create Test Modal -->
    <div id="liuk-test-modal" class="liuk-modal">
        <div class="liuk-modal-content">
            <span class="liuk-close">&times;</span>
            <h2>Create New Test</h2>
            
            <form id="liuk-test-form">
                <div class="liuk-form-group">
                    <label for="test_name">Test Name:</label>
                    <input type="text" id="test_name" name="test_name" required>
                </div>
                
                <div class="liuk-form-group">
                    <label>Categories (optional):</label>
                    <div class="liuk-categories-list">
                        <?php foreach ($categories as $category) : ?>
                            <label>
                                <input type="checkbox" name="categories[]" value="<?php echo esc_attr($category); ?>">
                                <?php echo esc_html($category); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="description">If no categories are selected, questions will be chosen randomly from all categories.</p>
                </div>
                
                <div class="liuk-form-actions">
                    <button type="submit" class="button button-primary">Create Test</button>
                    <button type="button" class="button liuk-cancel">Cancel</button>
                </div>
                
                <?php wp_nonce_field('liuk_test_nonce', 'nonce'); ?>
            </form>
        </div>
    </div>
    
    <div class="liuk-admin-section">
        <h2>Shortcodes</h2>
        <p>Use these shortcodes to display tests on your site:</p>
        <ul>
            <li><code>[liuk_test]</code> - Display a random mock test</li>
            <li><code>[liuk_test test_id="X"]</code> - Display a specific mock test (replace X with test ID)</li>
            <li><code>[liuk_test wrong_questions="1"]</code> - Display a test based on user's wrong questions</li>
        </ul>
        
        <h3>Other Available Shortcodes</h3>
        <ul>
            <li><code>[liuk_my_progress]</code> - Display user's test history and progress</li>
            <li><code>[liuk_leaderboard]</code> - Display the leaderboard</li>
        </ul>
    </div>
</div>