jQuery(document).ready(function($) {
    'use strict';

    // Initialize typography controls
    function initTypographyControls() {
        // Font size controls
        $('.font-size-input').on('input change', function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            const unit = $(`.font-size-unit[data-slug="${slug}"]`).val() || 'rem';
            updateFontSize(slug, value + unit);
        });

        // Font size unit changes
        $('.font-size-unit').on('change', function() {
            const slug = $(this).data('slug');
            const value = $(`.font-size-input[data-slug="${slug}"]`).val();
            const unit = $(this).val();
            updateFontSize(slug, value + unit);
        });

        // Preview scale sliders
        $('.preview-scale').on('input', function() {
            const slug = $(this).data('slug');
            const scale = $(this).val();
            const $slider = $(this);
            
            // Update slider value display
            $slider.siblings('.slider-value').text(scale + '%');
            
            // Update preview scale
            const $preview = $(`.preview-${slug}`);
            $preview.css('transform', `scale(${scale / 100})`);
            $preview.css('transform-origin', 'top left');
        });

        // Font family controls (textarea)
        $('.font-stack-input').on('input change', function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            updateFontFamily(slug, value);
            markAsChanged();
        });

        // Font weight controls
        $('.weight-value').on('input change', function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            updateFontWeight(slug, value);
        });

        // Line height controls
        $('.line-height-value').on('input change', function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            updateLineHeight(slug, value);
        });

        // Letter spacing controls
        $('.letter-spacing-value').on('input change', function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            updateLetterSpacing(slug, value.includes('em') ? value : value + 'em');
        });

        // Text transform controls
        $('.text-transform-value').on('change', function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            updateTextTransform(slug, value);
        });

        // Save to JSON button
        $('#save-to-json').on('click', function() {
            saveAllToJSON();
        });

        // Sync to theme.json button
        $('#sync-to-theme-json').on('click', function() {
            syncToThemeJSON();
        });

        // Reset button
        $('#reset-typography').on('click', function() {
            if (confirm('Reset all typography values to defaults?')) {
                resetTypography();
            }
        });

        // Export button
        $('#export-typography').on('click', function() {
            exportTypography();
        });

        // Copy buttons
        $('.copy-btn').on('click', function() {
            const value = $(this).data('copy');
            copyToClipboard(value);
        });
    }

    // Update font size
    function updateFontSize(slug, value) {
        // Update preview
        $(`.preview-${slug} p`).css('font-size', value);
        
        // Update value display
        $(`.font-size-value[data-slug="${slug}"]`).text(value);
        
        // Mark as changed
        markAsChanged();
    }

    // Update font family
    function updateFontFamily(slug, value) {
        // Update preview
        $(`.preview-family-${slug} p`).css('font-family', value);
        
        // Mark as changed
        markAsChanged();
    }

    // Update font weight
    function updateFontWeight(slug, value) {
        // Update preview
        $(`.preview-weight-${slug} p`).css('font-weight', value);
        
        // Update value display
        $(`.font-weight-value[data-slug="${slug}"]`).text(value);
        
        // Mark as changed
        markAsChanged();
    }

    // Update line height
    function updateLineHeight(slug, value) {
        // Update preview
        $(`.preview-line-height-${slug} p`).css('line-height', value);
        
        // Update value display
        $(`.line-height-value[data-slug="${slug}"]`).text(value);
        
        // Mark as changed
        markAsChanged();
    }

    // Update letter spacing
    function updateLetterSpacing(slug, value) {
        // Update preview
        $(`.preview-letter-spacing-${slug} p`).css('letter-spacing', value);
        
        // Update value display
        $(`.letter-spacing-value[data-slug="${slug}"]`).text(value);
        
        // Mark as changed
        markAsChanged();
    }

    // Update text transform
    function updateTextTransform(slug, value) {
        // Update preview
        $(`.preview-text-transform-${slug} p`).css('text-transform', value);
        
        // Update value display
        $(`.text-transform-value[data-slug="${slug}"]`).text(value);
        
        // Mark as changed
        markAsChanged();
    }

    // Mark as changed (show unsaved indicator)
    let hasChanges = false;
    function markAsChanged() {
        hasChanges = true;
        $('#save-to-json').addClass('has-changes');
    }

    // Save all changes to JSON
    function saveAllToJSON() {
        if (!primitiveTypography.canEdit) {
            showSaveIndicator('No edit permissions', 'error');
            return;
        }

        showSaveIndicator('Saving to JSON...');

        const typography = collectAllValues();

        $.ajax({
            url: primitiveTypography.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_typography_primitive',
                typography_data: JSON.stringify(typography),
                nonce: primitiveTypography.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSaveIndicator('Saved to JSON!', 'success');
                    hasChanges = false;
                    $('#save-to-json').removeClass('has-changes');
                } else {
                    showSaveIndicator('Error saving', 'error');
                    console.error('Save error:', response.data);
                }
            },
            error: function() {
                showSaveIndicator('Error saving', 'error');
            }
        });
    }

    // Sync to theme.json
    function syncToThemeJSON() {
        if (!primitiveTypography.canEdit) {
            showSaveIndicator('No edit permissions', 'error');
            return;
        }

        showSaveIndicator('Syncing to theme.json...');

        const typography = collectAllValues();

        $.ajax({
            url: primitiveTypography.ajaxUrl,
            type: 'POST',
            data: {
                action: 'sync_typography_to_theme_json',
                typography_data: JSON.stringify(typography),
                nonce: primitiveTypography.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSaveIndicator('Synced to theme.json!', 'success');
                } else {
                    showSaveIndicator('Error syncing', 'error');
                    console.error('Sync error:', response.data);
                }
            },
            error: function() {
                showSaveIndicator('Error syncing', 'error');
            }
        });
    }

    // Collect all current values
    function collectAllValues() {
        const typography = {
            font_sizes: {},
            font_families: {},
            font_weights: {},
            line_heights: {},
            letter_spacings: {},
            text_transforms: {}
        };

        // Collect font sizes
        $('.font-size-input').each(function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            const unit = $(`.font-size-unit[data-slug="${slug}"]`).val() || 'rem';
            typography.font_sizes[slug] = value + unit;
        });

        // Collect font families from textarea
        $('.font-stack-input').each(function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            typography.font_families[slug] = value;
        });

        // Collect font weights
        $('.weight-value').each(function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            typography.font_weights[slug] = value;
        });

        // Collect line heights
        $('.line-height-value').each(function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            typography.line_heights[slug] = value;
        });

        // Collect letter spacings
        $('.letter-spacing-value').each(function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            // Check if the value already has 'em' unit
            typography.letter_spacings[slug] = value.includes('em') ? value : value + 'em';
        });

        // Collect text transforms
        $('.text-transform-value').each(function() {
            const slug = $(this).data('slug');
            const value = $(this).val();
            typography.text_transforms[slug] = value;
        });

        return typography;
    }

    // Save to primitive
    function saveToPrimitive(type, slug, value) {
        if (!primitiveTypography.canEdit) {
            console.log('No edit permissions');
            return;
        }

        // Show saving indicator
        showSaveIndicator('Saving...');

        $.ajax({
            url: primitiveTypography.ajaxUrl,
            type: 'POST',
            data: {
                action: 'update_typography_primitive',
                type: type,
                slug: slug,
                value: value,
                nonce: primitiveTypography.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSaveIndicator('Saved!', 'success');
                } else {
                    showSaveIndicator('Error saving', 'error');
                    console.error('Save error:', response.data);
                }
            },
            error: function() {
                showSaveIndicator('Error saving', 'error');
            }
        });
    }

    // Reset typography
    function resetTypography() {
        showSaveIndicator('Resetting...');

        $.ajax({
            url: primitiveTypography.ajaxUrl,
            type: 'POST',
            data: {
                action: 'reset_typography_primitive',
                nonce: primitiveTypography.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSaveIndicator('Reset complete!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showSaveIndicator('Error resetting', 'error');
                }
            },
            error: function() {
                showSaveIndicator('Error resetting', 'error');
            }
        });
    }

    // Export typography
    function exportTypography() {
        const typography = collectAllValues();

        // Download as JSON
        const dataStr = JSON.stringify(typography, null, 2);
        const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
        const exportFileDefaultName = 'typography-primitives.json';

        const linkElement = document.createElement('a');
        linkElement.setAttribute('href', dataUri);
        linkElement.setAttribute('download', exportFileDefaultName);
        linkElement.click();
    }

    // Copy to clipboard
    function copyToClipboard(text) {
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        
        showSaveIndicator('Copied!', 'success');
    }

    // Show save indicator
    function showSaveIndicator(message, type = 'info') {
        const $indicator = $('#save-indicator');
        const $status = $indicator.find('.save-status');
        
        $status.text(message);
        $indicator.removeClass('success error info').addClass(type).addClass('show');
        
        if (type !== 'info') {
            setTimeout(function() {
                $indicator.removeClass('show');
            }, 2000);
        }
    }

    // Warn before leaving with unsaved changes
    $(window).on('beforeunload', function() {
        if (hasChanges) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    // Initialize
    initTypographyControls();
});
