<?php
/**
 * Import questions template
 *
 * @link       https://secondmedia.co.uk
 * @since      1.0.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Process import if form is submitted
$import_status = '';
$import_message = '';

if (isset($_POST['liuk_import_submit']) && isset($_FILES['liuk_import_file'])) {
    // Verify nonce
    if (!isset($_POST['liuk_import_nonce']) || !wp_verify_nonce($_POST['liuk_import_nonce'], 'liuk_import_questions')) {
        $import_status = 'error';
        $import_message = 'Security verification failed. Please try again.';
    } else {
        // Get the file
        $file = $_FILES['liuk_import_file'];
        
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $import_status = 'error';
            $import_message = 'File upload error: ' . $file['error'];
        } else {
            // Get file extension
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Check file type
            if ($file_ext !== 'csv' && $file_ext !== 'xlsx' && $file_ext !== 'xls') {
                $import_status = 'error';
                $import_message = 'Invalid file type. Please upload a CSV or Excel file.';
            } else {
                // Process the file
                $import_result = process_import_file($file);
                $import_status = $import_result['status'];
                $import_message = $import_result['message'];
            }
        }
    }
}

/**
 * Process the imported file
 */
function process_import_file($file) {
    global $wpdb;
    $table_questions = $wpdb->prefix . 'liuk_questions';
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $data = array();
    
    // Parse CSV file
    if ($file_ext === 'csv') {
        $handle = fopen($file['tmp_name'], 'r');
        
        // Get headers
        $headers = fgetcsv($handle);
        
        // Make headers lowercase for case-insensitive matching
        $headers = array_map('strtolower', $headers);
        
        // Check for required columns
        $required_columns = array('question_text', 'option_a', 'option_b', 'correct_answer', 'category');
        $missing_columns = array();
        
        foreach ($required_columns as $required) {
            if (!in_array(strtolower($required), $headers)) {
                $missing_columns[] = $required;
            }
        }
        
        if (!empty($missing_columns)) {
            return array(
                'status' => 'error',
                'message' => 'Missing required columns: ' . implode(', ', $missing_columns)
            );
        }
        
        // Map column indexes
        $column_map = array();
        foreach ($headers as $index => $header) {
            $column_map[strtolower($header)] = $index;
        }
        
        // Process rows
        while (($row = fgetcsv($handle)) !== false) {
            $question = array(
                'question_text' => $row[$column_map['question_text']],
                'option_a' => $row[$column_map['option_a']],
                'option_b' => $row[$column_map['option_b']],
                'option_c' => isset($column_map['option_c']) ? $row[$column_map['option_c']] : '',
                'option_d' => isset($column_map['option_d']) ? $row[$column_map['option_d']] : '',
                'correct_answer' => strtolower($row[$column_map['correct_answer']]),
                'category' => $row[$column_map['category']],
                'difficulty' => isset($column_map['difficulty']) ? $row[$column_map['difficulty']] : 'medium',
                'question_type' => isset($column_map['question_type']) ? $row[$column_map['question_type']] : 'multiple_choice'
            );
            
            // Determine question type if not explicitly provided
            if (!isset($column_map['question_type'])) {
                // If option_c and option_d are empty and options a/b are True/False, mark as true_false
                if (
                    empty($question['option_c']) && 
                    empty($question['option_d']) && 
                    (strtolower($question['option_a']) === 'true' || strtolower($question['option_a']) === 'true') && 
                    (strtolower($question['option_b']) === 'false' || strtolower($question['option_b']) === 'false')
                ) {
                    $question['question_type'] = 'true_false';
                    $question['option_a'] = 'True';
                    $question['option_b'] = 'False';
                }
            }
            
            $data[] = $question;
        }
        
        fclose($handle);
    } 
    // Parse Excel file
    else if ($file_ext === 'xlsx' || $file_ext === 'xls') {
        // Require PHPExcel
        require_once(LIUK_TEST_PLUGIN_DIR . 'includes/PHPExcel/PHPExcel.php');
        
        $file_type = PHPExcel_IOFactory::identify($file['tmp_name']);
        $reader = PHPExcel_IOFactory::createReader($file_type);
        $excel = $reader->load($file['tmp_name']);
        
        $sheet = $excel->getActiveSheet();
        $highest_row = $sheet->getHighestRow();
        $highest_column = $sheet->getHighestColumn();
        
        // Get headers
        $headers = array();
        $column_index = 0;
        for ($col = 'A'; $col <= $highest_column; $col++) {
            $headers[$column_index] = strtolower($sheet->getCell($col . '1')->getValue());
            $column_index++;
        }
        
        // Check for required columns
        $required_columns = array('question_text', 'option_a', 'option_b', 'correct_answer', 'category');
        $missing_columns = array();
        
        foreach ($required_columns as $required) {
            if (!in_array(strtolower($required), $headers)) {
                $missing_columns[] = $required;
            }
        }
        
        if (!empty($missing_columns)) {
            return array(
                'status' => 'error',
                'message' => 'Missing required columns: ' . implode(', ', $missing_columns)
            );
        }
        
        // Map column indexes
        $column_map = array();
        foreach ($headers as $index => $header) {
            $column_map[strtolower($header)] = $index;
        }
        
        // Process rows
        for ($row = 2; $row <= $highest_row; $row++) {
            $row_data = array();
            for ($col = 0; $col < count($headers); $col++) {
                $column_letter = PHPExcel_Cell::stringFromColumnIndex($col);
                $row_data[$col] = $sheet->getCell($column_letter . $row)->getValue();
            }
            
            $question = array(
                'question_text' => $row_data[$column_map['question_text']],
                'option_a' => $row_data[$column_map['option_a']],
                'option_b' => $row_data[$column_map['option_b']],
                'option_c' => isset($column_map['option_c']) ? $row_data[$column_map['option_c']] : '',
                'option_d' => isset($column_map['option_d']) ? $row_data[$column_map['option_d']] : '',
                'correct_answer' => strtolower($row_data[$column_map['correct_answer']]),
                'category' => $row_data[$column_map['category']],
                'difficulty' => isset($column_map['difficulty']) ? $row_data[$column_map['difficulty']] : 'medium',
                'question_type' => isset($column_map['question_type']) ? $row_data[$column_map['question_type']] : 'multiple_choice'
            );
            
            // Determine question type if not explicitly provided
            if (!isset($column_map['question_type'])) {
                // If option_c and option_d are empty and options a/b are True/False, mark as true_false
                if (
                    empty($question['option_c']) && 
                    empty($question['option_d']) && 
                    (strtolower($question['option_a']) === 'true' || strtolower($question['option_a']) === 'true') && 
                    (strtolower($question['option_b']) === 'false' || strtolower($question['option_b']) === 'false')
                ) {
                    $question['question_type'] = 'true_false';
                    $question['option_a'] = 'True';
                    $question['option_b'] = 'False';
                }
            }
            
            // Skip empty rows
            if (!empty($question['question_text'])) {
                $data[] = $question;
            }
        }
    }
    
    // Insert data into database
    $questions_added = 0;
    $questions_skipped = 0;
    
    foreach ($data as $question) {
        // Validate question
        if (empty($question['question_text']) || empty($question['option_a']) || empty($question['option_b']) || empty($question['correct_answer']) || empty($question['category'])) {
            $questions_skipped++;
            continue;
        }
        
        // Make sure correct_answer is a, b, c, or d
        $question['correct_answer'] = strtolower($question['correct_answer']);
        if (!in_array($question['correct_answer'], array('a', 'b', 'c', 'd'))) {
            $questions_skipped++;
            continue;
        }
        
        // Make sure difficulty is valid
        if (!in_array($question['difficulty'], array('easy', 'medium', 'hard'))) {
            $question['difficulty'] = 'medium';
        }
        
        // Make sure question_type is valid
        if (!in_array($question['question_type'], array('multiple_choice', 'true_false'))) {
            $question['question_type'] = 'multiple_choice';
        }
        
        // Insert question
        $wpdb->insert(
            $table_questions,
            array(
                'question_text' => $question['question_text'],
                'question_type' => $question['question_type'],
                'option_a' => $question['option_a'],
                'option_b' => $question['option_b'],
                'option_c' => $question['option_c'],
                'option_d' => $question['option_d'],
                'correct_answer' => $question['correct_answer'],
                'category' => $question['category'],
                'difficulty' => $question['difficulty']
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($wpdb->insert_id) {
            $questions_added++;
        } else {
            $questions_skipped++;
        }
    }
    
    if ($questions_added > 0) {
        return array(
            'status' => 'success',
            'message' => "Import completed. Added $questions_added questions. Skipped $questions_skipped questions."
        );
    } else {
        return array(
            'status' => 'error',
            'message' => "No questions were imported. Please check your file format and try again."
        );
    }
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (!empty($import_status)): ?>
        <div class="notice notice-<?php echo $import_status === 'success' ? 'success' : 'error'; ?> is-dismissible">
            <p><?php echo esc_html($import_message); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="liuk-admin-section">
        <h2>Import Questions</h2>
        <p>Use this form to import questions from a CSV or Excel file.</p>
        
        <form method="post" enctype="multipart/form-data">
            <div class="liuk-form-group">
                <label for="liuk_import_file">Select File:</label>
                <input type="file" name="liuk_import_file" id="liuk_import_file" accept=".csv, .xlsx, .xls" required>
                <p class="description">Upload a CSV or Excel file containing your questions.</p>
            </div>
            
            <?php wp_nonce_field('liuk_import_questions', 'liuk_import_nonce'); ?>
            
            <div class="liuk-form-actions">
                <button type="submit" name="liuk_import_submit" class="button button-primary">Import Questions</button>
            </div>
        </form>
    </div>
    
    <div class="liuk-admin-section">
        <h2>File Format Instructions</h2>
        
        <p>Your CSV or Excel file should have the following columns:</p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Column Name</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>question_text</strong></td>
                    <td>Yes</td>
                    <td>The text of the question</td>
                </tr>
                <tr>
                    <td><strong>option_a</strong></td>
                    <td>Yes</td>
                    <td>First answer option (for true/false questions, this should be "True")</td>
                </tr>
                <tr>
                    <td><strong>option_b</strong></td>
                    <td>Yes</td>
                    <td>Second answer option (for true/false questions, this should be "False")</td>
                </tr>
                <tr>
                    <td><strong>option_c</strong></td>
                    <td>No</td>
                    <td>Third answer option (leave empty for true/false questions)</td>
                </tr>
                <tr>
                    <td><strong>option_d</strong></td>
                    <td>No</td>
                    <td>Fourth answer option (leave empty for true/false questions)</td>
                </tr>
                <tr>
                    <td><strong>correct_answer</strong></td>
                    <td>Yes</td>
                    <td>The correct answer option (a, b, c, or d)</td>
                </tr>
                <tr>
                    <td><strong>category</strong></td>
                    <td>Yes</td>
                    <td>The category of the question</td>
                </tr>
                <tr>
                    <td><strong>difficulty</strong></td>
                    <td>No</td>
                    <td>The difficulty level of the question (easy, medium, or hard). Defaults to medium.</td>
                </tr>
                <tr>
                    <td><strong>question_type</strong></td>
                    <td>No</td>
                    <td>The type of question (multiple_choice or true_false). Defaults to multiple_choice.</td>
                </tr>
            </tbody>
        </table>
        
        <h3>Example CSV Format:</h3>
        <pre>
question_text,option_a,option_b,option_c,option_d,correct_answer,category,difficulty,question_type
"What is the capital of the UK?","London","Manchester","Birmingham","Edinburgh","a","Geography","easy","multiple_choice"
"The UK has four nations: England, Scotland, Wales and Northern Ireland.","True","False","","","a","Geography","easy","true_false"
        </pre>
        
        <p><strong>Note:</strong> The system will attempt to automatically detect true/false questions if:</p>
        <ul>
            <li>option_c and option_d are empty</li>
            <li>option_a is "True" and option_b is "False"</li>
        </ul>
    </div>
</div>