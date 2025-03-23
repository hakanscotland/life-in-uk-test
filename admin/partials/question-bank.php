<?php
/**
 * Question bank admin template
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

// Get categories for filter
$categories = $wpdb->get_col("SELECT DISTINCT category FROM $table_questions ORDER BY category");

// Get current category filter
$current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

// Prepare query for filtered questions
$where = '';
$params = array();

if (!empty($current_category)) {
    $where = "WHERE category = %s";
    $params[] = $current_category;
}

// Get questions
$questions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_questions $where ORDER BY id DESC",
        $params
    )
);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="liuk-admin-actions">
        <button id="liuk-add-question" class="button button-primary">Add New Question</button>
        
        <div class="liuk-filter">
            <form method="get">
                <input type="hidden" name="page" value="liuk-questions">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?php echo esc_attr($category); ?>" <?php selected($current_category, $category); ?>>
                            <?php echo esc_html($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button">Filter</button>
            </form>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="40%">Question</th>
                <th width="15%">Category</th>
                <th width="10%">Difficulty</th>
                <th width="10%">Type</th>
                <th width="10%">Correct Answer</th>
                <th width="10%">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $question) : ?>
                <tr>
                    <td><?php echo $question->id; ?></td>
                    <td><?php echo esc_html($question->question_text); ?></td>
                    <td><?php echo esc_html($question->category); ?></td>
                    <td><?php echo esc_html($question->difficulty); ?></td>
                    <td><?php echo $question->question_type === 'true_false' ? 'True/False' : 'Multiple Choice'; ?></td>
                    <td>
                        <?php 
                        $correct_option = 'option_' . strtolower($question->correct_answer);
                        echo esc_html($question->$correct_option); 
                        ?>
                    </td>
                    <td>
                        <button class="button liuk-edit-question" data-id="<?php echo $question->id; ?>">Edit</button>
                        <button class="button liuk-delete-question" data-id="<?php echo $question->id; ?>">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($questions)) : ?>
                <tr>
                    <td colspan="7">No questions found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Question Form Modal -->
    <div id="liuk-question-modal" class="liuk-modal">
        <div class="liuk-modal-content">
            <span class="liuk-close">&times;</span>
            <h2 id="liuk-modal-title">Add New Question</h2>
            
            <form id="liuk-question-form">
                <input type="hidden" id="question_id" name="question_id" value="0">
                
                <div class="liuk-form-group">
                    <label for="question_text">Question:</label>
                    <textarea id="question_text" name="question_text" rows="3" required></textarea>
                </div>
                
                <div class="liuk-form-group">
                    <label for="question_type">Question Type:</label>
                    <select id="question_type" name="question_type" required>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                    </select>
                </div>
                
                <div id="multiple_choice_options">
                    <div class="liuk-form-group">
                        <label for="option_a">Option A:</label>
                        <input type="text" id="option_a" name="option_a" required>
                    </div>
                    
                    <div class="liuk-form-group">
                        <label for="option_b">Option B:</label>
                        <input type="text" id="option_b" name="option_b" required>
                    </div>
                    
                    <div class="liuk-form-group">
                        <label for="option_c">Option C:</label>
                        <input type="text" id="option_c" name="option_c">
                    </div>
                    
                    <div class="liuk-form-group">
                        <label for="option_d">Option D:</label>
                        <input type="text" id="option_d" name="option_d">
                    </div>
                </div>
                
                <div id="true_false_options" style="display:none;">
                    <div class="liuk-form-group">
                        <label for="option_a_tf">Option A:</label>
                        <input type="text" id="option_a_tf" name="option_a_tf" value="True" readonly>
                    </div>
                    
                    <div class="liuk-form-group">
                        <label for="option_b_tf">Option B:</label>
                        <input type="text" id="option_b_tf" name="option_b_tf" value="False" readonly>
                    </div>
                </div>
                
                <div class="liuk-form-group">
                    <label for="correct_answer">Correct Answer:</label>
                    <select id="correct_answer" name="correct_answer" required>
                        <option value="a">Option A</option>
                        <option value="b">Option B</option>
                        <option value="c">Option C</option>
                        <option value="d">Option D</option>
                    </select>
                </div>
                
                <div class="liuk-form-group">
                    <label for="category">Category:</label>
                    <input type="text" id="category" name="category" list="categories" required>
                    <datalist id="categories">
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo esc_attr($category); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                
                <div class="liuk-form-group">
                    <label for="difficulty">Difficulty:</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                
                <div class="liuk-form-actions">
                    <button type="submit" class="button button-primary">Save Question</button>
                    <button type="button" class="button liuk-cancel">Cancel</button>
                </div>
                
                <div class="liuk-form-group">
                    <label for="feedback">Question Feedback:</label>
                    <textarea id="feedback" name="feedback" rows="3"></textarea>
                    <p class="description">Optional feedback or explanation for this question. Will be shown after the user answers.</p>
                </div>
                
                <?php wp_nonce_field('liuk_test_nonce', 'nonce'); ?>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="liuk-delete-modal" class="liuk-modal">
        <div class="liuk-modal-content">
            <span class="liuk-close">&times;</span>
            <h2>Delete Question</h2>
            <p>Are you sure you want to delete this question? This action cannot be undone.</p>
            
            <div class="liuk-form-actions">
                <button id="liuk-confirm-delete" class="button button-primary" data-id="0">Delete</button>
                <button class="button liuk-cancel">Cancel</button>
            </div>
            
            <?php wp_nonce_field('liuk_test_nonce', 'delete_nonce'); ?>
        </div>
    </div>
</div>