/**
 * Upload Limits JavaScript
 * 
 * Handles the refresh PHP settings and test upload functionality
 */
jQuery(document).ready(function($) {
    
    // Function to update header stats
    function updateHeaderStats(settings) {
        // Find the stats in the header
        $('.wpl-code-snippets-header__stats-item').each(function() {
            var $item = $(this);
            var $label = $item.find('.wpl-code-snippets-header__stats-item-label');
            var $count = $item.find('.wpl-code-snippets-header__stats-item-count');
            
            // Update based on label
            if ($label.text().indexOf('Upload Limit') !== -1) {
                $count.text(settings.upload_max_filesize);
            } else if ($label.text().indexOf('Memory Limit') !== -1) {
                $count.text(settings.memory_limit);
            }
        });
    }
    
    // Function to refresh PHP settings
    function refreshPhpSettings(showLoading) {
        var $button = $('#refresh-php-settings');
        var originalText = $button.html();
        
        // Show loading state if requested
        if (showLoading) {
            $button.html('<span class="dashicons dashicons-update" style="margin-top: 3px; animation: rotation 2s infinite linear;"></span> ' + 
                        Lukic_upload_vars.refreshing);
            $button.prop('disabled', true);
        }
        
        // Send AJAX request
        return $.ajax({
            url: Lukic_upload_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'Lukic_refresh_php_settings',
                nonce: Lukic_upload_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update settings values
                    $('#current-upload-max-filesize').text(response.data.upload_max_filesize);
                    $('#current-post-max-size').text(response.data.post_max_size);
                    $('#current-max-execution-time').text(response.data.max_execution_time + ' seconds');
                    $('#current-memory-limit').text(response.data.memory_limit);
                    $('#last-updated-time').text(response.data.timestamp);
                    
                    // Update header stats
                    updateHeaderStats(response.data);
                    
                    // Flash the table to indicate it was updated
                    $('#current-php-settings-table').css('background-color', '#f7fcfe');
                    setTimeout(function() {
                        $('#current-php-settings-table').css('background-color', '');
                    }, 1000);
                }
            },
            error: function() {
                if (showLoading) {
                    alert('Error refreshing PHP settings.');
                }
            },
            complete: function() {
                // Restore button state if we were showing loading
                if (showLoading) {
                    $button.html(originalText);
                    $button.prop('disabled', false);
                }
            }
        });
    }
    
    // Refresh PHP settings button click
    $('#refresh-php-settings').on('click', function() {
        refreshPhpSettings(true);
    });
    
    // Test upload functionality
    $('#test-upload-button').on('click', function() {
        var $button = $(this);
        var $fileInput = $('#test-upload-file');
        var $results = $('#upload-test-results');
        var $message = $('.upload-test-message');
        
        // Check if file is selected
        if ($fileInput[0].files.length === 0) {
            alert('Please select a file to test upload.');
            return;
        }
        
        // Get file details before upload attempt
        var file = $fileInput[0].files[0];
        var fileName = file.name;
        var fileSize = formatFileSize(file.size);
        
        // Create form data
        var formData = new FormData();
        formData.append('action', 'Lukic_test_upload');
        formData.append('nonce', Lukic_upload_vars.nonce);
        formData.append('test_file', file);
        
        // Show loading state
        $button.prop('disabled', true);
        $button.text('Uploading...');
        
        // Show results container
        $results.show();
        
        // Set file details regardless of upload result
        $('.test-file-name').text(fileName);
        $('.test-file-size').text(fileSize);
        
        // Send AJAX request
        $.ajax({
            url: Lukic_upload_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {                
                if (response.success) {
                    // Success - file was uploaded (under the limit)
                    $message.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    $('.test-status').html('<span style="color: green;">✓ Success</span>');
                } else {
                    // Error from server (could be size limit exceeded)
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    $('.test-status').html('<span style="color: red;">✗ Failed</span>');
                }
            },
            error: function(xhr) {
                // This is likely a file too large error or server timeout
                var errorMessage = 'The file exceeds the maximum upload size limit. This confirms your upload limit is working correctly.';
                $message.html('<div class="notice notice-warning"><p>' + errorMessage + '</p></div>');
                $('.test-status').html('<span style="color: orange;">✓ Limit Working</span>');
            },
            complete: function() {
                // Restore button state
                $button.prop('disabled', false);
                $button.text('Test Upload');
            }
        });
    });
    
    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Handle form submission with AJAX
    $('#upload-limits-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var formData = $form.serialize();
        var $saveButton = $('#save-settings-button');
        var $savingIndicator = $('#settings-saving-indicator');
        var $savedIndicator = $('#settings-saved-indicator');
        
        // Show saving indicator
        $saveButton.prop('disabled', true);
        $savingIndicator.show();
        $savedIndicator.hide();
        
        // Submit the form via AJAX
        $.ajax({
            url: 'options.php',
            type: 'POST',
            data: formData,
            success: function() {
                // Update the PHP settings
                refreshPhpSettings(false).done(function() {
                    // Show saved indicator
                    $savingIndicator.hide();
                    $savedIndicator.show();
                    
                    // Hide saved indicator after 3 seconds
                    setTimeout(function() {
                        $savedIndicator.fadeOut();
                    }, 3000);
                    
                    // Update URL without reloading page (to maintain state)
                    if (history.pushState) {
                        var newUrl = window.location.href.split('?')[0] + '?page=Lukic-upload-limits&settings-updated=true';
                        window.history.pushState({path: newUrl}, '', newUrl);
                    }
                });
            },
            error: function() {
                alert('Error saving settings. Please try again.');
                $savingIndicator.hide();
            },
            complete: function() {
                // Re-enable save button
                $saveButton.prop('disabled', false);
            }
        });
    });
    
    // Auto-update settings after page load with settings-updated parameter
    if (window.location.search.indexOf('settings-updated=true') > -1) {
        setTimeout(function() {
            refreshPhpSettings(false);
        }, 500);
    }
    
    // Add rotation animation
    $('<style>@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }</style>').appendTo('head');
});
