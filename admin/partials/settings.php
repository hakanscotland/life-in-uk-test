<?php
/**
 * Plugin settings template
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Save settings if form is submitted
if (isset($_POST['liuk_save_settings'])) {
    // Verify nonce
    if (!isset($_POST['liuk_settings_nonce']) || !wp_verify_nonce($_POST['liuk_settings_nonce'], 'liuk_save_settings')) {
        wp_die('Security check failed');
    }

    // Process settings
    $test_page_id = isset($_POST['liuk_test_page']) ? intval($_POST['liuk_test_page']) : 0;
    update_option('liuk_test_page', $test_page_id);
    
    $show_answers = isset($_POST['liuk_show_answers']) ? '1' : '0';
    update_option('liuk_show_answers', $show_answers);
    
    $show_correct_answers = isset($_POST['liuk_show_correct_answers']) ? '1' : '0';
    update_option('liuk_show_correct_answers', $show_correct_answers);
    
    // Process the new setting
    $show_instant_feedback = isset($_POST['liuk_instant_feedback']) ? '1' : '0';
    update_option('liuk_instant_feedback', $show_instant_feedback);
    
    $message = 'Settings saved successfully.';
}

// Get current settings
$test_page_id = get_option('liuk_test_page', 0);
$show_answers = get_option('liuk_show_answers', '1');
$show_correct_answers = get_option('liuk_show_correct_answers', '1');
$show_instant_feedback = get_option('liuk_instant_feedback', '1');

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (isset($message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div class="liuk-admin-section">
            <h2>General Settings</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="liuk_test_page">Test Page</label>
                    </th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'liuk_test_page',
                            'id' => 'liuk_test_page',
                            'show_option_none' => '-- Select a page --',
                            'option_none_value' => '0',
                            'selected' => $test_page_id
                        ));
                        ?>
                        <p class="description">Select the page where the test is displayed. This page should contain the [liuk_test] shortcode.</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="liuk-admin-section">
            <h2>Test Results Settings</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Show Question Summary</th>
                    <td>
                        <label for="liuk_show_answers">
                            <input type="checkbox" name="liuk_show_answers" id="liuk_show_answers" value="1" <?php checked($show_answers, '1'); ?>>
                            Show summary of correct and incorrect answers after test
                        </label>
                        <p class="description">When enabled, users will see which questions they answered correctly and incorrectly at the end of the test.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Show Correct Answers</th>
                    <td>
                        <label for="liuk_show_correct_answers">
                            <input type="checkbox" name="liuk_show_correct_answers" id="liuk_show_correct_answers" value="1" <?php checked($show_correct_answers, '1'); ?>>
                            Show correct answers for incorrectly answered questions
                        </label>
                        <p class="description">When enabled, users will see the correct answers for questions they got wrong.</p>
                    </td>
                </tr>
                
                // Add this inside the settings table for Test Results Settings
                <tr>
                    <th scope="row">Instant Feedback</th>
                    <td>
                        <label for="liuk_instant_feedback">
                            <input type="checkbox" name="liuk_instant_feedback" id="liuk_instant_feedback" value="1" <?php checked($show_instant_feedback, '1'); ?>>
                            Show instant feedback after each question is answered
                        </label>
                        <p class="description">When enabled, users will see feedback immediately after answering each question.</p>
                    </td>
                </tr>

            </table>
        </div>
        
        <?php wp_nonce_field('liuk_save_settings', 'liuk_settings_nonce'); ?>
        <p class="submit">
            <button type="submit" name="liuk_save_settings" class="button button-primary">Save Settings</button>
        </p>
    </form>
</div>