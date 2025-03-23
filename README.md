# Life in the UK Test WordPress Plugin

A comprehensive WordPress plugin for creating and managing Life in the UK test preparation materials.

## Description

The Life in the UK Test plugin provides a complete solution for websites offering preparation materials for the official Life in the UK citizenship test. This plugin allows administrators to create question banks, generate mock tests, and track user progress.

The British citizenship test is an examination used to determine applicants' knowledge of life in the United Kingdom. It consists of 24 multiple-choice questions to be answered in 45 minutes, with a passing score of 75% (18 correct answers).

## Features

### For Administrators:
- Create and manage a comprehensive question bank
- Support for multiple-choice and true/false questions
- Categorize questions by topic and difficulty
- Generate random mock tests or create specific test sets
- Monitor user progress and test results
- Track most commonly missed questions

### For Users:
- Take timed mock tests simulating real exam conditions
- Review test results and performance metrics
- Access personalized practice tests based on previously incorrect answers
- Track progress through a detailed history dashboard
- Compare performance with other users via the leaderboard

## Installation

1. Upload the `life-in-uk-test` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the 'UK Test' menu in your admin dashboard to set up questions and tests

## Usage

### Setting Up the Question Bank
1. Navigate to **UK Test > Question Bank** in your WordPress admin
2. Click **Add New Question**
3. Select the question type (Multiple Choice or True/False)
4. Enter the question text, answer options, and select the correct answer
5. Assign a category and difficulty level
6. Save the question

### Creating Mock Tests
1. Navigate to **UK Test > Mock Tests**
2. Click **Create New Test**
3. Enter a name for the test
4. Optionally select specific categories to include
5. The system will automatically select 24 random questions for the test

### Displaying Tests on Your Site
Use these shortcodes to display tests and progress tracking:

- `[liuk_test]` - Display a random mock test
- `[liuk_test test_id="1"]` - Display a specific mock test
- `[liuk_test wrong_questions="1"]` - Display a test based on user's wrong questions
- `[liuk_my_progress]` - Display user's test history and progress
- `[liuk_leaderboard]` - Display the leaderboard

### Monitoring User Progress
1. Navigate to **UK Test > User Progress**
2. View detailed statistics for all users who have taken tests
3. Track pass rates, average scores, and user activity
4. Analyze most commonly missed questions

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher


To make the Excel file import functionality work, you'll need to install the PHPExcel library. Here are the steps:

1. Download PHPExcel from https://github.com/PHPOffice/PHPExcel/archive/refs/tags/1.8.2.zip

2. Create a folder named "PHPExcel" in your plugin's "includes" directory:
   ```
   life-in-uk-test/includes/PHPExcel/
   ```

3. Extract the downloaded zip file and copy the PHPExcel library files into this folder.

4. The structure should look like:
   ```
   life-in-uk-test/includes/PHPExcel/PHPExcel.php
   life-in-uk-test/includes/PHPExcel/PHPExcel/...
   ```

Note: If you prefer to use a different PHP Excel library (such as PhpSpreadsheet), you'll need to modify the Excel parsing code in the import-questions.php file accordingly.

## Frequently Asked Questions

### How many questions can I add to the question bank?
There is no limit to the number of questions you can add.

### Can users retake tests?
Yes, users can take any test multiple times to improve their scores.

### Does the plugin track which questions users answer incorrectly?
Yes, the plugin tracks incorrect answers and can generate targeted practice tests from these questions.

### Can I customize the passing score?
The passing score is set to 75% (18 out of 24 questions) to match the official test requirements.

## License
This plugin is licensed under the GPL v2 or later.

## Credits
Developed by [Hakan Dag](https://secondmedia.co.uk)