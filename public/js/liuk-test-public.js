/**
 * Public JavaScript for Life in the UK Test plugin
 */
jQuery(document).ready(function($) {
    // Initialize variables
    var currentQuestion = 1;
    var questionCount = $('#liuk-test-form').data('question-count');
    var timer = null;
    var timeLeft = 45 * 60; // 45 minutes in seconds
    var questionStatus = {}; // Track question status: correct, incorrect, or review
    var showInstantFeedback = false; // Set to false by default for review/check functionality
    
    // Initialize test
    initializeTest();
    
    /**
     * Initialize the test
     */
    function initializeTest() {
        // Show the first question
        $('.liuk-question').hide();
        $('#question-1').show();
        
        // Start timer
        startTimer();
        
        // Update navigation
        updateNavigation();
        
        // Button event handlers
        $('#liuk-prev-btn').on('click', prevQuestion);
        $('#liuk-next-btn').on('click', nextQuestion);
        $('#liuk-submit-btn').on('click', submitTest);
        
        // Use event delegation for Review and Check buttons
        $(document).on('click', '.liuk-review-btn', function() {
            console.log('Review button clicked');
            reviewQuestion(currentQuestion);
        });

        $(document).on('click', '.liuk-check-btn', function() {
            console.log('Check button clicked');
            checkQuestion(currentQuestion);
        });
        
        // Question number clicks
        $('.liuk-question-number').on('click', function() {
            var questionNum = $(this).data('question');
            goToQuestion(questionNum);
        });
        
        // Make the whole option div clickable
        $('.liuk-option').on('click', function() {
            var $radio = $(this).find('input[type="radio"]');
            $radio.prop('checked', true);
        });
        
        // Check if instant feedback is enabled via data attribute
        var $testForm = $('#liuk-test-form');
        if ($testForm.data('instant-feedback') !== undefined) {
            showInstantFeedback = $testForm.data('instant-feedback') === '1';
        }
    }
    
    /**
     * Update navigation UI
     */
    function updateNavigation() {
        // Update current question indicator
        $('.liuk-question-number').removeClass('active');
        $('.liuk-question-number[data-question="' + currentQuestion + '"]').addClass('active');
        
        // Update button states
        $('#liuk-prev-btn').prop('disabled', currentQuestion === 1);
        $('#liuk-next-btn').prop('disabled', currentQuestion === questionCount);
        
        // Update progress bar
        var progress = (currentQuestion / questionCount) * 100;
        $('.liuk-progress-fill').css('width', progress + '%');
    }
    
    /**
     * Go to a specific question
     */
    function goToQuestion(questionNum) {
        if (questionNum >= 1 && questionNum <= questionCount) {
            $('.liuk-question').hide();
            currentQuestion = parseInt(questionNum);
            $('#question-' + currentQuestion).show();
            updateNavigation();
        }
    }
    
    /**
     * Go to the next question
     */
    function nextQuestion() {
        if (currentQuestion < questionCount) {
            $('.liuk-question').hide();
            currentQuestion++;
            $('#question-' + currentQuestion).show();
            updateNavigation();
        }
    }
    
    /**
     * Go to the previous question
     */
    function prevQuestion() {
        if (currentQuestion > 1) {
            $('.liuk-question').hide();
            currentQuestion--;
            $('#question-' + currentQuestion).show();
            updateNavigation();
        }
    }
    
    /**
     * Review the current question
     */
    function reviewQuestion(questionNum) {
        console.log('Reviewing question', questionNum);
        var $question = $('#question-' + questionNum);
        var questionId = $('input[name="question_id_' + questionNum + '"]').val();
        var $selectedOption = $question.find('input[type="radio"]:checked');
        
        // Check if an option is selected
        if ($selectedOption.length === 0) {
            alert('Please select an answer before reviewing.');
            return;
        }
        
        var userAnswer = $selectedOption.val();
        var correctAnswer = $('input[name="correct_answer_' + questionId + '"]').val();
        var isCorrect = userAnswer === correctAnswer;
        
        // Update question status
        questionStatus[questionNum] = isCorrect ? 'correct' : 'incorrect';
        
        // Update question number styling
        $('.liuk-question-number[data-question="' + questionNum + '"]').removeClass('correct incorrect review');
        $('.liuk-question-number[data-question="' + questionNum + '"]').addClass(questionStatus[questionNum]);
        
        // Mark as reviewed
        $('.liuk-question-number[data-question="' + questionNum + '"]').addClass('reviewed');
        
        // Show basic feedback without explanation
        var $feedback = $('#liuk-feedback-' + questionNum);
        $feedback.removeClass('correct incorrect');
        
        if (isCorrect) {
            $feedback.addClass('correct');
            $feedback.find('.liuk-feedback-heading').text('Correct!');
        } else {
            $feedback.addClass('incorrect');
            $feedback.find('.liuk-feedback-heading').text('Incorrect');
        }
        
        // Don't show the text feedback or highlight correct answer in review mode
        $feedback.find('.liuk-feedback-text').hide();
        
        $feedback.slideDown();
        
        // Reset option styling
        $question.find('.liuk-option').removeClass('correct incorrect');
        
        // Style the selected option
        $selectedOption.closest('.liuk-option').addClass(isCorrect ? 'correct' : 'incorrect');
    }
    
    /**
     * Check the current question
     */
    function checkQuestion(questionNum) {
        console.log('Checking question', questionNum);
        var $question = $('#question-' + questionNum);
        var questionId = $('input[name="question_id_' + questionNum + '"]').val();
        var $selectedOption = $question.find('input[type="radio"]:checked');
        
        // Check if an option is selected
        if ($selectedOption.length === 0) {
            alert('Please select an answer before checking.');
            return;
        }
        
        var userAnswer = $selectedOption.val();
        var correctAnswer = $('input[name="correct_answer_' + questionId + '"]').val();
        var isCorrect = userAnswer === correctAnswer;
        var feedback = $('input[name="feedback_' + questionId + '"]').val();
        
        // Update question status
        questionStatus[questionNum] = isCorrect ? 'correct' : 'incorrect';
        
        // Update question number styling
        $('.liuk-question-number[data-question="' + questionNum + '"]').removeClass('correct incorrect review');
        $('.liuk-question-number[data-question="' + questionNum + '"]').addClass(questionStatus[questionNum]);
        
        // Mark as checked
        $('.liuk-question-number[data-question="' + questionNum + '"]').addClass('checked');
        
        // Show detailed feedback with explanation
        var $feedback = $('#liuk-feedback-' + questionNum);
        $feedback.removeClass('correct incorrect');
        
        if (isCorrect) {
            $feedback.addClass('correct');
            $feedback.find('.liuk-feedback-heading').text('Correct!');
        } else {
            $feedback.addClass('incorrect');
            $feedback.find('.liuk-feedback-heading').text('Incorrect');
        }
        
        // Show the text feedback
        $feedback.find('.liuk-feedback-text').show();
        
        $feedback.slideDown();
        
        // Reset option styling
        $question.find('.liuk-option').removeClass('correct incorrect');
        
        // Style the selected option and correct option
        $selectedOption.closest('.liuk-option').addClass(isCorrect ? 'correct' : 'incorrect');
        
        // If incorrect, highlight the correct answer
        if (!isCorrect) {
            $question.find('input[value="' + correctAnswer + '"]').closest('.liuk-option').addClass('correct');
        }
    }
    
    /**
     * Start the test timer
     */
    function startTimer() {
        updateTimerDisplay();
        
        timer = setInterval(function() {
            timeLeft--;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert('Time is up! Your test will be submitted now.');
                submitTest();
            } else {
                updateTimerDisplay();
            }
        }, 1000);
    }
    
    /**
     * Update the timer display
     */
    function updateTimerDisplay() {
        var minutes = Math.floor(timeLeft / 60);
        var seconds = timeLeft % 60;
        
        $('#liuk-timer-display').text(
            minutes.toString().padStart(2, '0') + ':' + 
            seconds.toString().padStart(2, '0')
        );
    }
    
    /**
     * Submit the test
     */
    function submitTest() {
        // Check if all questions are answered
        var unansweredCount = 0;
        
        for (var i = 1; i <= questionCount; i++) {
            var questionId = $('input[name="question_id_' + i + '"]').val();
            if (!$('input[name="question_' + questionId + '"]:checked').length) {
                unansweredCount++;
            }
        }
        
        if (unansweredCount > 0) {
            if (!confirm('You have ' + unansweredCount + ' unanswered questions. Are you sure you want to submit the test?')) {
                return;
            }
        }
        
        // Stop the timer
        clearInterval(timer);
        
        // Calculate time taken
        var timeTaken = 45 * 60 - timeLeft;
        
        // Get form data and add the completion time
        var formData = $('#liuk-test-form').serialize() + '&completion_time=' + timeTaken;
        
        // Submit via AJAX
        $.ajax({
            url: liuk_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=liuk_submit_test',
            beforeSend: function() {
                $('#liuk-submit-btn').prop('disabled', true).text('Submitting...');
            },
            success: function(response) {
                if (response.success) {
                    displayResults(response.data);
                } else {
                    alert('Error: ' + response.data);
                    $('#liuk-submit-btn').prop('disabled', false).text('Submit Test');
                }
            },
            error: function() {
                alert('An error occurred while submitting the test. Please try again.');
                $('#liuk-submit-btn').prop('disabled', false).text('Submit Test');
            }
        });
    }
    
    /**
     * Display the test results
     */
    function displayResults(results) {
        var resultsHTML = '<div class="liuk-results">';
        
        if (results.passed) {
            resultsHTML += '<div class="liuk-results-icon passed">✓</div>';
            resultsHTML += '<h2>Congratulations! You Passed</h2>';
        } else {
            resultsHTML += '<div class="liuk-results-icon failed">✗</div>';
            resultsHTML += '<h2>Test Failed</h2>';
        }
        
        resultsHTML += '<div class="liuk-score">' + results.score + ' out of ' + results.max_score + ' (' + results.percentage + '%)</div>';
        
        if (results.passed) {
            resultsHTML += '<div class="liuk-result-message">Well done! You have passed the mock Life in the UK Test.</div>';
        } else {
            resultsHTML += '<div class="liuk-result-message">You need at least ' + results.pass_threshold + ' correct answers (' + 
                           '75%) to pass. Keep practicing!</div>';
        }
        
        resultsHTML += '<div class="liuk-result-details">';
        resultsHTML += '<p><strong>Time taken:</strong> ' + results.completion_time + '</p>';
        
        if (results.wrong_questions.length > 0) {
            resultsHTML += '<p><strong>Number of incorrect answers:</strong> ' + results.wrong_questions.length + '</p>';
        }
        
        resultsHTML += '</div>';
        
        // Add question summary if enabled
        if (results.show_answers && results.question_details.length > 0) {
            resultsHTML += '<div class="liuk-question-summary">';
            resultsHTML += '<h3>Question Summary</h3>';
            
            // Add tabs for correct and incorrect questions
            resultsHTML += '<div class="liuk-tabs">';
            resultsHTML += '<button class="liuk-tab active" data-tab="all">All Questions</button>';
            resultsHTML += '<button class="liuk-tab" data-tab="correct">Correct (' + (results.score) + ')</button>';
            resultsHTML += '<button class="liuk-tab" data-tab="incorrect">Incorrect (' + (results.max_score - results.score) + ')</button>';
            resultsHTML += '</div>';
            
            resultsHTML += '<div class="liuk-tab-content">';
            
            // Sort questions by number
            results.question_details.sort(function(a, b) {
                return a.id - b.id;
            });
            
            // Add each question with result
            for (var i = 0; i < results.question_details.length; i++) {
                var question = results.question_details[i];
                var questionClass = question.is_correct ? 'liuk-correct' : 'liuk-incorrect';
                
                resultsHTML += '<div class="liuk-summary-question ' + questionClass + '" data-status="' + 
                              (question.is_correct ? 'correct' : 'incorrect') + '">';
                
                resultsHTML += '<div class="liuk-summary-header">';
                resultsHTML += '<h4>Question ' + (i + 1) + ' ';
                
                if (question.is_correct) {
                    resultsHTML += '<span class="liuk-correct-label">✓ Correct</span>';
                } else {
                    resultsHTML += '<span class="liuk-incorrect-label">✗ Incorrect</span>';
                }
                
                resultsHTML += '</h4>';
                resultsHTML += '</div>';
                
                resultsHTML += '<div class="liuk-summary-body">';
                resultsHTML += '<p>' + question.text + '</p>';
                
                var userAnswerLetter = question.user_answer.toUpperCase();
                var correctAnswerLetter = question.correct_answer.toUpperCase();
                
                // Show the options
                var options = ['A', 'B', 'C', 'D'];
                resultsHTML += '<div class="liuk-summary-options">';
                
                for (var j = 0; j < options.length; j++) {
                    var optionKey = options[j].toLowerCase();
                    if (question.options[optionKey]) {
                        var optionClass = '';
                        
                        if (optionKey === question.user_answer) {
                            optionClass = question.is_correct ? 'liuk-option-correct' : 'liuk-option-wrong';
                        } else if (optionKey === question.correct_answer && results.show_correct_answers) {
                            optionClass = 'liuk-option-correct';
                        }
                        
                        resultsHTML += '<div class="liuk-summary-option ' + optionClass + '">';
                        resultsHTML += '<span class="liuk-option-letter">' + options[j] + '</span>';
                        resultsHTML += '<span class="liuk-option-text">' + question.options[optionKey] + '</span>';
                        resultsHTML += '</div>';
                    }
                }
                
                resultsHTML += '</div>'; // End options
                
                if (!question.is_correct && results.show_correct_answers) {
                    resultsHTML += '<div class="liuk-correct-answer">';
                    resultsHTML += '<p>Correct answer: <strong>' + correctAnswerLetter + ') ' + 
                                  question.options[question.correct_answer] + '</strong></p>';
                    resultsHTML += '</div>';
                }
                
                resultsHTML += '</div>'; // End summary body
                resultsHTML += '</div>'; // End summary question
            }
            
            resultsHTML += '</div>'; // End tab content
            resultsHTML += '</div>'; // End question summary
        }
        
        resultsHTML += '<div class="liuk-result-actions">';
        resultsHTML += '<a href="' + window.location.href + '" class="liuk-button">Take Another Test</a>';
        resultsHTML += '<a href="#" id="liuk-practice-wrong" class="liuk-button liuk-button-alt">Practice Wrong Questions</a>';
        resultsHTML += '</div>';
        
        resultsHTML += '</div>';
        
        $('.liuk-test-container').html(resultsHTML);
        
        // Set up practice wrong questions button
        $('#liuk-practice-wrong').on('click', function(e) {
            e.preventDefault();
            
            $(this).text('Loading...').prop('disabled', true);
            
            $.ajax({
                url: liuk_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'liuk_get_wrong_questions_test',
                    nonce: liuk_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.url;
                    } else {
                        alert('Error: ' + response.data);
                        $('#liuk-practice-wrong').text('Practice Wrong Questions').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $('#liuk-practice-wrong').text('Practice Wrong Questions').prop('disabled', false);
                }
            });
        });
        
        // Set up tab functionality
        $('.liuk-tab').on('click', function() {
            $('.liuk-tab').removeClass('active');
            $(this).addClass('active');
            
            var tabType = $(this).data('tab');
            
            if (tabType === 'all') {
                $('.liuk-summary-question').show();
            } else if (tabType === 'correct') {
                $('.liuk-summary-question').hide();
                $('.liuk-summary-question[data-status="correct"]').show();
            } else if (tabType === 'incorrect') {
                $('.liuk-summary-question').hide();
                $('.liuk-summary-question[data-status="incorrect"]').show();
            }
        });
    }
    
    // Polyfill for String.padStart
    if (!String.prototype.padStart) {
        String.prototype.padStart = function padStart(targetLength, padString) {
            targetLength = targetLength >> 0;
            padString = String(padString || ' ');
            if (this.length > targetLength) {
                return String(this);
            } else {
                targetLength = targetLength - this.length;
                if (targetLength > padString.length) {
                    padString += padString.repeat(targetLength / padString.length);
                }
                return padString.slice(0, targetLength) + String(this);
            }
        };
    }
});