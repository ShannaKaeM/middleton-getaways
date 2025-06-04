/**
 * Animations Primitive Editor JavaScript
 * Following the established pattern from primitive-shadows.js
 */

jQuery(document).ready(function($) {
    let hasChanges = false;
    let originalData = {};

    // Initialize
    function init() {
        loadOriginalData();
        bindEvents();
        initializePreviewControls();
        startAnimationDemos();
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
        $('#preview-duration, #preview-easing, #preview-delay').on('change', updateLivePreview);
        $('#trigger-animation').on('click', triggerAnimation);
        
        // Warn before leaving with unsaved changes
        $(window).on('beforeunload', function() {
            if (hasChanges) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    }

    // Initialize preview controls and animation demos
    function initializePreviewControls() {
        updateLivePreview();
    }

    // Start continuous animation demos for each category
    function startAnimationDemos() {
        // Duration demos - pulse animation
        $('.duration-demo').each(function() {
            const $box = $(this);
            const duration = $box.data('duration');
            $box.css({
                'animation': `pulse ${duration} ease-in-out infinite`,
                'animation-duration': duration
            });
        });

        // Easing demos - slide animation
        $('.easing-demo').each(function() {
            const $box = $(this);
            const easing = $box.data('easing');
            $box.css({
                'animation': `slideInOut 2s ${easing} infinite`,
                'animation-timing-function': easing
            });
        });

        // Delay demos - fade animation with delays
        $('.delay-demo').each(function() {
            const $box = $(this);
            const delay = $box.data('delay');
            $box.css({
                'animation': `fadeInOut 1.5s ease-in-out infinite`,
                'animation-delay': delay
            });
        });

        // Transition demos - hover effects
        $('.transition-demo').each(function() {
            const $box = $(this);
            const transition = $box.data('transition');
            $box.css({
                'transition-property': transition,
                'transition-duration': '300ms',
                'transition-timing-function': 'ease-out'
            });

            // Add hover effect
            $box.on('mouseenter', function() {
                $(this).css({
                    'background-color': 'var(--color-primary)',
                    'transform': 'scale(1.05)',
                    'box-shadow': '0 4px 12px rgba(0,0,0,0.15)'
                });
            }).on('mouseleave', function() {
                $(this).css({
                    'background-color': '',
                    'transform': '',
                    'box-shadow': ''
                });
            });
        });
    }

    // Handle token input changes
    function handleTokenChange() {
        const $input = $(this);
        const value = $input.val();
        const category = $input.data('category');
        const slug = $input.data('slug');

        // Update preview
        updateTokenPreview($input, value);
        
        // Update copy button
        $input.siblings('.copy-button').attr('data-value', value);
        
        // Mark as changed
        markAsChanged();
        
        console.log(`Animation token changed: ${category}.${slug} = ${value}`);
    }

    // Update individual token preview
    function updateTokenPreview($input, value) {
        const $preview = $input.closest('.token-item').find('.animation-preview-box');
        const category = $input.data('category');

        switch(category) {
            case 'durations':
                $preview.css('animation-duration', value);
                break;
            case 'easings':
                $preview.css('animation-timing-function', value);
                break;
            case 'delays':
                $preview.css('animation-delay', value);
                break;
            case 'transitions':
                $preview.css('transition-property', value);
                break;
        }
    }

    // Update live preview
    function updateLivePreview() {
        const duration = $('#preview-duration').val();
        const easing = $('#preview-easing').val();
        const delay = $('#preview-delay').val();

        const $preview = $('#animation-preview');
        $preview.css({
            'transition-duration': duration,
            'transition-timing-function': easing,
            'transition-delay': delay,
            'transition-property': 'all'
        });
    }

    // Trigger animation in live preview
    function triggerAnimation() {
        const $preview = $('#animation-preview');
        
        // Reset animation
        $preview.removeClass('animate');
        
        // Force reflow
        $preview[0].offsetHeight;
        
        // Add animation class
        $preview.addClass('animate');
        
        // Remove class after animation
        setTimeout(() => {
            $preview.removeClass('animate');
        }, 2000);
    }

    // Copy to clipboard
    function copyToClipboard(e) {
        e.preventDefault();
        const value = $(this).attr('data-value');
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(value).then(() => {
                showNotification('Copied to clipboard: ' + value, 'success');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = value;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Copied to clipboard: ' + value, 'success');
        }
    }

    // Save to JSON
    function saveToJSON() {
        if (!primitiveAnimations.canEdit) {
            showNotification('You do not have permission to edit animations', 'error');
            return;
        }

        const data = collectFormData();
        
        $.ajax({
            url: primitiveAnimations.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_animations_primitive',
                nonce: primitiveAnimations.nonce,
                data: JSON.stringify(data)
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Animations saved successfully!', 'success');
                    hasChanges = false;
                    updateSaveButton();
                    
                    // Update original data
                    originalData = JSON.parse(JSON.stringify(data));
                } else {
                    showNotification('Error saving animations: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Network error while saving animations', 'error');
            }
        });
    }

    // Sync to theme.json
    function syncToThemeJSON() {
        if (!primitiveAnimations.canEdit) {
            showNotification('You do not have permission to sync animations', 'error');
            return;
        }

        $.ajax({
            url: primitiveAnimations.ajaxUrl,
            type: 'POST',
            data: {
                action: 'sync_animations_to_theme_json',
                nonce: primitiveAnimations.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Animations synced to theme.json successfully!', 'success');
                } else {
                    showNotification('Error syncing animations: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Network error while syncing animations', 'error');
            }
        });
    }

    // Reset to defaults
    function resetToDefaults() {
        if (!confirm('Are you sure you want to reset all animations to their default values? This will lose any unsaved changes.')) {
            return;
        }

        $('.token-input').each(function() {
            const $input = $(this);
            const category = $input.data('category');
            const slug = $input.data('slug');
            const originalValue = originalData[category] && originalData[category][slug];
            
            if (originalValue) {
                $input.val(originalValue);
                updateTokenPreview($input, originalValue);
                $input.siblings('.copy-button').attr('data-value', originalValue);
            }
        });

        hasChanges = false;
        updateSaveButton();
        updateLivePreview();
        showNotification('Reset to default values', 'info');
    }

    // Collect form data
    function collectFormData() {
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

    // Mark as changed
    function markAsChanged() {
        hasChanges = true;
        updateSaveButton();
    }

    // Update save button state
    function updateSaveButton() {
        const $saveBtn = $('.btn-save');
        if (hasChanges) {
            $saveBtn.addClass('has-changes').text('Save Changes');
        } else {
            $saveBtn.removeClass('has-changes').text('Save to JSON');
        }
    }

    // Show notification
    function showNotification(message, type = 'info') {
        const $notification = $('#animation-status');
        $notification
            .removeClass('success error info')
            .addClass(type)
            .text(message)
            .show()
            .addClass('show');

        setTimeout(() => {
            $notification.removeClass('show');
            setTimeout(() => {
                $notification.hide();
            }, 300);
        }, 3000);
    }

    // Initialize when DOM is ready
    init();
});
