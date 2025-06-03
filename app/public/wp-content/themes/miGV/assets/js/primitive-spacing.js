/**
 * Spacing Primitive Editor JavaScript
 * Following the established pattern from primitive-typography.js
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
        $('.preview-type, .preview-value').on('change', updateLivePreview);
        
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
        const category = $input.data('category');
        const slug = $input.data('slug');
        
        // Update relevant preview elements
        updateTokenPreview($input, newValue);
    }

    // Update preview for a specific token
    function updateTokenPreview($input, value) {
        const $tokenItem = $input.closest('.token-item');
        const category = $input.data('category');
        
        switch(category) {
            case 'scale':
                $tokenItem.find('.spacing-box').css({
                    'width': value,
                    'height': value
                });
                $tokenItem.find('.spacing-ruler').css('width', value);
                break;
                
            case 'padding':
                $tokenItem.find('.padding-container').css('padding', value);
                break;
                
            case 'margin':
                $tokenItem.find('.margin-element').css('margin', value);
                break;
                
            case 'gap':
                $tokenItem.find('.gap-container').css('gap', value);
                break;
                
            case 'layout':
                if ($input.data('slug').includes('width')) {
                    $tokenItem.find('.width-preview').css('width', value);
                } else {
                    $tokenItem.find('.layout-spacing-preview').css('padding', value);
                }
                break;
        }
    }

    // Initialize live preview controls
    function initializePreviewControls() {
        updateLivePreview();
    }

    // Update live preview
    function updateLivePreview() {
        const type = $('.preview-type').val();
        const value = $('.preview-value').val();
        const $preview = $('#spacing-preview');
        
        // Reset all spacing
        $preview.css({
            'padding': '',
            'margin': '',
            'gap': ''
        });
        
        // Apply selected spacing
        const cssValue = `var(--spacing-${type}-${value}, var(--spacing-scale-${value}))`;
        
        switch(type) {
            case 'padding':
                $preview.css('padding', cssValue);
                break;
            case 'margin':
                $preview.css('margin', cssValue);
                break;
            case 'gap':
                $preview.css({
                    'display': 'flex',
                    'gap': cssValue
                });
                $preview.html(`
                    <div class="preview-content">Item 1</div>
                    <div class="preview-content">Item 2</div>
                    <div class="preview-content">Item 3</div>
                `);
                break;
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
            url: primitiveSpacing.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_spacing_primitive',
                nonce: primitiveSpacing.nonce,
                spacing_data: JSON.stringify(data)
            },
            beforeSend: function() {
                $('.btn-save').text('Saving...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    hasChanges = false;
                    $('.btn-save').removeClass('has-changes');
                    showNotification('Spacing saved successfully!', 'success');
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Failed to save spacing data', 'error');
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
            url: primitiveSpacing.ajaxUrl,
            type: 'POST',
            data: {
                action: 'sync_spacing_to_theme_json',
                nonce: primitiveSpacing.nonce,
                spacing_data: JSON.stringify(data)
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
        if (!confirm('Are you sure you want to reset all spacing values to their defaults? This cannot be undone.')) {
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