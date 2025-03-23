/**
 * Admin JavaScript for Life in the UK Test plugin
 */
jQuery(document).ready(function($) {
    // Toggle question type options
    $('#question_type').on('change', function() {
        var questionType = $(this).val();
        if (questionType === 'true_false') {
            $('#multiple_choice_options').hide();
            $('#true_false_options').show();
            
            // Update correct answer options
            $('#correct_answer').html(
                '<option value="a">True</option>' +
                '<option value="b">False</option>'
            );
        } else {
            $('#true_false_options').hide();
            $('#multiple_choice_options').show();
            
            // Restore correct answer options
            $('#correct_answer').html(
                '<option value="a">Option A</option>' +
                '<option value="b">Option B</option>' +
                '<option value="c">Option C</option>' +
                '<option value="d">Option D</option>'
            );
        }
    });
    
    // Question Bank Modal
    $('#liuk-add-question').on('click', function() {
        $('#liuk-modal-title').text('Add New Question');
        $('#liuk-question-form')[0].reset();
        $('#question_id').val(0);
        $('#question_type').trigger('change');
        $('#liuk-question-modal').show();
    });
    
    $('.liuk-edit-question').on('click', function() {
        var questionId = $(this).data('id');
        $('#liuk-modal-title').text('Edit Question');
        $('#question_id').val(questionId);
        
        // Load question data via AJAX
        $.ajax({
            url: liuk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'liuk_get_question',
                nonce: liuk_ajax.nonce,
                question_id: questionId
            },
            success: function(response) {
                if (response.success) {
                    var question = response.data;
                    $('#question_text').val(question.question_text);
                    $('#question_type').val(question.question_type);
                    $('#option_a').val(question.option_a);
                    $('#option_b').val(question.option_b);
                    $('#option_c').val(question.option_c);
                    $('#option_d').val(question.option_d);
                    $('#correct_answer').val(question.correct_answer);
                    $('#category').val(question.category);
                    $('#difficulty').val(question.difficulty);
                    $('#feedback').val(question.feedback);
                    $('#question_type').trigger('change');
                    $('#liuk-question-modal').show();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while loading the question.');
            }
        });
    });
    
    $('.liuk-delete-question').on('click', function() {
        var questionId = $(this).data('id');
        $('#liuk-confirm-delete').data('id', questionId);
        $('#liuk-delete-modal').show();
    });
    
    $('#liuk-confirm-delete').on('click', function() {
        var questionId = $(this).data('id');
        
        $.ajax({
            url: liuk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'liuk_delete_question',
                nonce: liuk_ajax.nonce,
                question_id: questionId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while deleting the question.');
            }
        });
    });
    
    $('#liuk-question-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: liuk_ajax.ajax_url,
            type: 'POST',
            data: $(this).serialize() + '&action=liuk_save_question',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while saving the question.');
            }
        });
    });
    
    // Test Modal
    $('#liuk-create-test').on('click', function() {
        $('#liuk-test-form')[0].reset();
        $('#liuk-test-modal').show();
    });
    
    $('#liuk-test-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: liuk_ajax.ajax_url,
            type: 'POST',
            data: $(this).serialize() + '&action=liuk_create_test',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while creating the test.');
            }
        });
    });
    
    // Close modals
    $('.liuk-close, .liuk-cancel').on('click', function() {
        $('.liuk-modal').hide();
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('liuk-modal')) {
            $('.liuk-modal').hide();
        }
    });
});