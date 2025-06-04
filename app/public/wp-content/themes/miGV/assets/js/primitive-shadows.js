/**
 * Shadows Primitive Editor JavaScript
 * Following the established pattern from primitive-borders.js
 */

jQuery(document).ready(function($) {
    let hasChanges = false;
    let originalData = {};

    // Initialize
    function init() {
        loadOriginalData();
        bindEvents();
        initializePreviewControls();
    }

    // Track original data for reset functionality
    function loadOriginalData() {
        $('.token-input').each(function() {
            const $input = $(this);
            const category = $input.data('category');
            const slug = $input.data('slug');
            
            if (!originalData[category]) originalData[category] = {};
            originalData[category][slug] = $input.val();
        });
    }

    // Bind all events
    function bindEvents() {
        // Token input changes
        $('.token-input').on('input change', handleTokenChange);
        
        // Save button
        $('.btn-save').on('click', saveToJSON);
        
        // Sync button
        $('.btn-sync').on('click', syncToThemeJSON);
        
        // Reset button
        $('.btn-reset').on('click', resetToDefaults);
        
        // Copy buttons
        $('.copy-button').on('click', copyToClipboard);
        
        // Live preview controls
        $('.preview-shadow-type, .preview-shadow-size, .preview-elevation').on('change', updateLivePreview);
        
        // Warn before leaving with unsaved changes
        $(window).on('beforeunload', function() {
            if (hasChanges) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    }

    // Handle token changes
    function handleTokenChange() {
        hasChanges = true;
        $('.btn-save').addClass('has-changes');
        
        const $input = $(this);
        const newValue = $input.val();
        
        // Update relevant preview elements
        updateTokenPreview($input, newValue);
    }

    // Update preview for a specific token
    function updateTokenPreview($input, value) {
        const $tokenItem = $input.closest('.token-item');
        const $preview = $tokenItem.find('.shadow-preview-box');
        
        if (value.toLowerCase() === 'none') {
            $preview.css('box-shadow', 'none');
        } else {
            $preview.css('box-shadow', value);
        }
    }

    // Initialize live preview controls
    function initializePreviewControls() {
        updateLivePreview();
    }

    // Update live preview
    function updateLivePreview() {
        const shadowType = $('.preview-shadow-type').val();
        const shadowSize = $('.preview-shadow-size').val();
        const elevation = $('.preview-elevation').val();
        
        const $preview = $('#shadow-preview-element');
        
        // Reset shadows
        $preview.css('box-shadow', 'none');
        
        // Apply selected shadow based on type
        let shadowValue = '';
        
        if (shadowType === 'scale') {
            shadowValue = $(`.token-input[data-category="scale"][data-slug="${shadowSize}"]`).val();
        } else if (shadowType === 'elevation') {
            shadowValue = $(`.token-input[data-category="elevation"][data-slug="${elevation}"]`).val();
        } else if (shadowType === 'inset') {
            shadowValue = $(`.token-input[data-category="inset"][data-slug="${shadowSize}"]`).val();
        } else if (shadowType === 'colored') {
            shadowValue = $(`.token-input[data-category="colored"][data-slug="primary"]`).val();
        }
        
        if (shadowValue && shadowValue !== 'none') {
            $preview.css('box-shadow', shadowValue);
        }
    }

    // Collect all data from inputs
    function collectAllData() {
        const data = {};
        
        $('.token-input').each(function() {
            const $input = $(this);
            const category = $input.data('category');
            const slug = $input.data('slug');
            const value = $input.val();
            
            if (!data[category]) data[category] = {};
            data[category][slug] = value;
        });
        
        return data;
    }

    // Save to JSON primitive
    function saveToJSON() {
        const data = collectAllData();
        
        $.ajax({
            url: primitiveShadows.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_shadows_primitive',
                nonce: primitiveShadows.nonce,
                shadows_data: JSON.stringify(data)
            },
            beforeSend: function() {
                $('.btn-save').text('Saving...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    hasChanges = false;
                    $('.btn-save').removeClass('has-changes');
                    showNotification('Shadows saved successfully!', 'success');
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Failed to save shadows data', 'error');
            },
            complete: function() {
                $('.btn-save').text('Save to JSON').prop('disabled', false);
            }
        });
    }

    // Sync to theme.json
    function syncToThemeJSON() {
        const data = collectAllData();
        
        $.ajax({
            url: primitiveShadows.ajaxUrl,
            type: 'POST',
            data: {
                action: 'sync_shadows_to_theme_json',
                nonce: primitiveShadows.nonce,
                shadows_data: JSON.stringify(data)
            },
            beforeSend: function() {
                $('.btn-sync').text('Syncing...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Synced to theme.json successfully!', 'success');
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Failed to sync to theme.json', 'error');
            },
            complete: function() {
                $('.btn-sync').text('Sync to theme.json').prop('disabled', false);
            }
        });
    }

    // Reset to defaults
    function resetToDefaults() {
        if (!confirm('Are you sure you want to reset all shadow values to their defaults? This cannot be undone.')) {
            return;
        }
        
        // Reset inputs to original values
        $('.token-input').each(function() {
            const $input = $(this);
            const category = $input.data('category');
            const slug = $input.data('slug');
            const originalValue = originalData[category][slug];
            
            $input.val(originalValue);
            updateTokenPreview($input, originalValue);
        });
        
        hasChanges = false;
        $('.btn-save').removeClass('has-changes');
        showNotification('Reset to default values', 'info');
        
        // Update live preview
        updateLivePreview();
    }

    // Copy to clipboard
    function copyToClipboard(e) {
        const value = $(this).data('value');
        const $button = $(this);
        
        navigator.clipboard.writeText(value).then(function() {
            const originalText = $button.text();
            $button.text('Copied!');
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        }).catch(function() {
            // Fallback for older browsers
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(value).select();
            document.execCommand('copy');
            $temp.remove();
            
            const originalText = $button.text();
            $button.text('Copied!');
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        });
    }

    // Show notification
    function showNotification(message, type = 'success') {
        const $notification = $('<div class="editor-notification"></div>')
            .addClass('notification-' + type)
            .text(message);
        
        $('body').append($notification);
        
        setTimeout(function() {
            $notification.addClass('show');
        }, 10);
        
        setTimeout(function() {
            $notification.removeClass('show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    // Initialize the editor
    init();
});