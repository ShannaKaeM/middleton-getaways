/**
 * Layout Primitive Editor JavaScript
 * Handles interactive editing, live preview, and data management for layout design tokens
 */

jQuery(document).ready(function($) {
    'use strict';

    // Layout data structure
    let layoutData = {
        containers: {
            xs: '480px',
            sm: '640px',
            md: '768px',
            lg: '1024px',
            xl: '1280px',
            '2xl': '1536px',
            full: '100%'
        },
        breakpoints: {
            mobile: '480px',
            tablet: '768px',
            desktop: '1024px',
            wide: '1280px',
            ultrawide: '1536px'
        },
        grid: {
            'columns-2': 'repeat(2, minmax(0, 1fr))',
            'columns-3': 'repeat(3, minmax(0, 1fr))',
            'columns-4': 'repeat(4, minmax(0, 1fr))',
            'columns-6': 'repeat(6, minmax(0, 1fr))',
            'columns-12': 'repeat(12, minmax(0, 1fr))',
            'auto-fit': 'repeat(auto-fit, minmax(250px, 1fr))',
            'auto-fill': 'repeat(auto-fill, minmax(200px, 1fr))'
        },
        flexbox: {
            center: 'flex items-center justify-center',
            between: 'flex items-center justify-between',
            around: 'flex items-center justify-around',
            start: 'flex items-start justify-start',
            end: 'flex items-end justify-end',
            column: 'flex flex-col',
            row: 'flex flex-row',
            wrap: 'flex flex-wrap'
        },
        aspectRatios: {
            square: '1 / 1',
            video: '16 / 9',
            golden: '1.618 / 1',
            photo: '4 / 3',
            portrait: '3 / 4',
            wide: '21 / 9',
            'ultra-wide': '32 / 9'
        },
        zIndex: {
            behind: '-1',
            default: '0',
            dropdown: '10',
            sticky: '20',
            fixed: '30',
            modal: '40',
            popover: '50',
            tooltip: '60',
            toast: '70'
        }
    };

    // Initialize the editor
    function initLayoutEditor() {
        loadLayoutData();
        bindEvents();
        updateLivePreview();
    }

    // Load layout data from server
    function loadLayoutData() {
        $.ajax({
            url: primitiveLayout.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mi_get_layout_primitive',
                nonce: primitiveLayout.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    layoutData = response.data;
                    updateInputFields();
                    updateLivePreview();
                }
            },
            error: function() {
                showStatus('Error loading layout data', 'error');
            }
        });
    }

    // Update input fields with current data
    function updateInputFields() {
        $('.layout-input').each(function() {
            const $input = $(this);
            const category = $input.data('category');
            const key = $input.data('key');
            
            if (layoutData[category] && layoutData[category][key]) {
                $input.val(layoutData[category][key]);
                $input.siblings('.copy-btn').data('value', layoutData[category][key]);
            }
        });
    }

    // Update live preview
    function updateLivePreview() {
        // Update container previews
        $('.container-preview').each(function() {
            const $container = $(this);
            const tokenItem = $container.closest('.token-item');
            const key = tokenItem.data('key');
            
            if (layoutData.containers[key]) {
                $container.css('max-width', layoutData.containers[key]);
            }
        });

        // Update grid previews
        $('.grid-preview').each(function() {
            const $grid = $(this);
            const tokenItem = $grid.closest('.token-item');
            const key = tokenItem.data('key');
            
            if (layoutData.grid[key]) {
                $grid.css('grid-template-columns', layoutData.grid[key]);
            }
        });

        // Update aspect ratio previews
        $('.aspect-preview').each(function() {
            const $aspect = $(this);
            const tokenItem = $aspect.closest('.token-item');
            const key = tokenItem.data('key');
            
            if (layoutData.aspectRatios[key]) {
                $aspect.css('aspect-ratio', layoutData.aspectRatios[key]);
            }
        });

        // Update z-index previews
        $('.z-layer').each(function() {
            const $layer = $(this);
            const tokenItem = $layer.closest('.token-item');
            const key = tokenItem.data('key');
            
            if (layoutData.zIndex[key]) {
                $layer.css('z-index', layoutData.zIndex[key]);
            }
        });

        // Update main demo
        updateMainDemo();
    }

    // Update main demo section
    function updateMainDemo() {
        const $demoContainer = $('.demo-container');
        const $demoGrid = $('.demo-grid');
        
        if (layoutData.containers.lg) {
            $demoContainer.css('max-width', layoutData.containers.lg);
        }
        
        if (layoutData.grid['columns-3']) {
            $demoGrid.css('grid-template-columns', layoutData.grid['columns-3']);
        }
        
        $('.demo-item').each(function() {
            if (layoutData.aspectRatios.video) {
                $(this).css('aspect-ratio', layoutData.aspectRatios.video);
            }
        });
    }

    // Bind event handlers
    function bindEvents() {
        // Input change handlers
        $('.layout-input').on('input', handleInputChange);
        
        // Button handlers
        $('#save-layout').on('click', saveLayoutData);
        $('#sync-layout').on('click', syncToTheme);
        $('#reset-layout').on('click', resetLayoutData);
        $('#export-layout').on('click', exportLayoutData);
        
        // Copy button handlers
        $('.copy-btn').on('click', handleCopyClick);
    }

    // Handle input changes
    function handleInputChange() {
        const $input = $(this);
        const category = $input.data('category');
        const key = $input.data('key');
        const value = $input.val();
        
        // Update data structure
        if (layoutData[category]) {
            layoutData[category][key] = value;
        }
        
        // Update copy button
        $input.siblings('.copy-btn').data('value', value);
        
        // Update live preview
        updateLivePreview();
        
        // Mark as modified
        markAsModified();
    }

    // Save layout data
    function saveLayoutData() {
        if (!primitiveLayout.canEdit) {
            showStatus('You do not have permission to edit layout tokens', 'error');
            return;
        }

        $.ajax({
            url: primitiveLayout.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mi_save_layout_primitive',
                nonce: primitiveLayout.nonce,
                layout_data: JSON.stringify(layoutData)
            },
            success: function(response) {
                if (response.success) {
                    showStatus('Layout tokens saved successfully', 'success');
                    clearModified();
                } else {
                    showStatus('Error saving layout tokens: ' + (response.data || 'Unknown error'), 'error');
                }
            },
            error: function() {
                showStatus('Error saving layout tokens', 'error');
            }
        });
    }

    // Sync to theme.json
    function syncToTheme() {
        if (!primitiveLayout.canEdit) {
            showStatus('You do not have permission to sync to theme', 'error');
            return;
        }

        $.ajax({
            url: primitiveLayout.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mi_sync_layout_to_theme',
                nonce: primitiveLayout.nonce,
                layout_data: JSON.stringify(layoutData)
            },
            success: function(response) {
                if (response.success) {
                    showStatus('Layout tokens synced to theme.json successfully', 'success');
                } else {
                    showStatus('Error syncing to theme: ' + (response.data || 'Unknown error'), 'error');
                }
            },
            error: function() {
                showStatus('Error syncing to theme', 'error');
            }
        });
    }

    // Reset layout data
    function resetLayoutData() {
        if (!confirm('Are you sure you want to reset all layout tokens to defaults? This cannot be undone.')) {
            return;
        }

        $.ajax({
            url: primitiveLayout.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mi_reset_layout_primitive',
                nonce: primitiveLayout.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    layoutData = response.data;
                    updateInputFields();
                    updateLivePreview();
                    showStatus('Layout tokens reset to defaults', 'success');
                    clearModified();
                } else {
                    showStatus('Error resetting layout tokens: ' + (response.data || 'Unknown error'), 'error');
                }
            },
            error: function() {
                showStatus('Error resetting layout tokens', 'error');
            }
        });
    }

    // Export layout data
    function exportLayoutData() {
        const dataStr = JSON.stringify(layoutData, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        const url = URL.createObjectURL(dataBlob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = 'layout-tokens.json';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        showStatus('Layout tokens exported successfully', 'success');
    }

    // Handle copy button clicks
    function handleCopyClick(e) {
        e.preventDefault();
        const value = $(this).data('value');
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(value).then(function() {
                showStatus('Copied to clipboard: ' + value, 'success');
            }).catch(function() {
                fallbackCopy(value);
            });
        } else {
            fallbackCopy(value);
        }
    }

    // Fallback copy method
    function fallbackCopy(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showStatus('Copied to clipboard: ' + text, 'success');
        } catch (err) {
            showStatus('Failed to copy to clipboard', 'error');
        }
        
        document.body.removeChild(textArea);
    }

    // Show status message
    function showStatus(message, type) {
        const $status = $('#layout-status');
        $status.removeClass('success error').addClass(type);
        $status.text(message).show();
        
        setTimeout(function() {
            $status.fadeOut();
        }, 3000);
    }

    // Mark as modified
    function markAsModified() {
        $('body').addClass('layout-modified');
    }

    // Clear modified state
    function clearModified() {
        $('body').removeClass('layout-modified');
    }

    // Warn before leaving if modified
    $(window).on('beforeunload', function() {
        if ($('body').hasClass('layout-modified')) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    // Initialize the editor
    initLayoutEditor();
});
