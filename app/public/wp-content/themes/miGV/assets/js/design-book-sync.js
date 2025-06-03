/**
 * Design Book Sync - Bidirectional sync between Twig primitives and theme.json
 */
(function($) {
    'use strict';

    const DesignBookSync = {
        init: function() {
            if (!miDesignBookSync.canEdit) {
                return;
            }

            this.bindEvents();
            this.initializeEditableTokens();
        },

        bindEvents: function() {
            // Color picker events
            $(document).on('change', '.token-color-input', this.handleColorChange.bind(this));
            
            // Typography size events
            $(document).on('change', '.token-typography-input', this.handleTypographyChange.bind(this));
            
            // Spacing events
            $(document).on('change', '.token-spacing-input', this.handleSpacingChange.bind(this));
            
            // Custom property events
            $(document).on('change', '.token-custom-input', this.handleCustomChange.bind(this));
            
            // Load tokens button
            $(document).on('click', '.load-theme-tokens', this.loadThemeTokens.bind(this));
        },

        initializeEditableTokens: function() {
            // Add edit controls to primitive book pages
            if ($('.primitive-book').length) {
                this.addEditControls();
            }
        },

        addEditControls: function() {
            // Add edit controls to color swatches
            $('.color-swatch').each(function() {
                const $swatch = $(this);
                const colorVar = $swatch.data('color-var');
                const colorSlug = $swatch.data('color-slug');
                
                if (colorVar && colorSlug) {
                    const $editControl = $('<div class="token-edit-control">')
                        .append(
                            $('<input>')
                                .attr('type', 'color')
                                .addClass('token-color-input')
                                .data('token-type', 'color')
                                .data('token-path', colorSlug)
                                .val($swatch.css('background-color'))
                        );
                    
                    $swatch.append($editControl);
                }
            });

            // Add edit controls to typography samples
            $('.typography-sample').each(function() {
                const $sample = $(this);
                const sizeVar = $sample.data('size-var');
                const sizeSlug = $sample.data('size-slug');
                
                if (sizeVar && sizeSlug) {
                    const currentSize = $sample.css('font-size');
                    const $editControl = $('<div class="token-edit-control">')
                        .append(
                            $('<input>')
                                .attr('type', 'text')
                                .addClass('token-typography-input')
                                .data('token-type', 'typography')
                                .data('token-path', sizeSlug)
                                .val(currentSize)
                                .attr('placeholder', 'e.g., 1.5rem')
                        );
                    
                    $sample.append($editControl);
                }
            });

            // Add edit controls to spacing samples
            $('.spacing-sample').each(function() {
                const $sample = $(this);
                const spacingVar = $sample.data('spacing-var');
                const spacingSlug = $sample.data('spacing-slug');
                
                if (spacingVar && spacingSlug) {
                    const $editControl = $('<div class="token-edit-control">')
                        .append(
                            $('<input>')
                                .attr('type', 'text')
                                .addClass('token-spacing-input')
                                .data('token-type', 'spacing')
                                .data('token-path', spacingSlug)
                                .attr('placeholder', 'e.g., 2rem')
                        );
                    
                    $sample.append($editControl);
                }
            });
        },

        handleColorChange: function(e) {
            const $input = $(e.target);
            const tokenType = $input.data('token-type');
            const tokenPath = $input.data('token-path');
            const tokenValue = $input.val();

            this.syncToThemeJson(tokenType, tokenPath, tokenValue);
        },

        handleTypographyChange: function(e) {
            const $input = $(e.target);
            const tokenType = $input.data('token-type');
            const tokenPath = $input.data('token-path');
            const tokenValue = $input.val();

            this.syncToThemeJson(tokenType, tokenPath, tokenValue);
        },

        handleSpacingChange: function(e) {
            const $input = $(e.target);
            const tokenType = $input.data('token-type');
            const tokenPath = $input.data('token-path');
            const tokenValue = $input.val();

            this.syncToThemeJson(tokenType, tokenPath, tokenValue);
        },

        handleCustomChange: function(e) {
            const $input = $(e.target);
            const tokenType = 'custom';
            const tokenPath = $input.data('token-path');
            const tokenValue = $input.val();

            this.syncToThemeJson(tokenType, tokenPath, tokenValue);
        },

        syncToThemeJson: function(tokenType, tokenPath, tokenValue) {
            const $status = $('<div class="sync-status">Syncing...</div>');
            $('body').append($status);

            $.ajax({
                url: miDesignBookSync.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mi_sync_primitive_to_theme_json',
                    nonce: miDesignBookSync.nonce,
                    token_type: tokenType,
                    token_path: tokenPath,
                    token_value: tokenValue
                },
                success: function(response) {
                    if (response.success) {
                        $status.text('Synced!').addClass('success');
                        
                        // Update the CSS variable immediately
                        const varName = DesignBookSync.getVariableName(tokenType, tokenPath);
                        if (varName) {
                            document.documentElement.style.setProperty(varName, tokenValue);
                        }
                        
                        setTimeout(() => $status.remove(), 2000);
                    } else {
                        $status.text('Error: ' + response.data).addClass('error');
                        setTimeout(() => $status.remove(), 3000);
                    }
                },
                error: function() {
                    $status.text('Network error').addClass('error');
                    setTimeout(() => $status.remove(), 3000);
                }
            });
        },

        getVariableName: function(tokenType, tokenPath) {
            switch (tokenType) {
                case 'color':
                    return '--wp--preset--color--' + tokenPath;
                case 'typography':
                    return '--wp--preset--font-size--' + tokenPath;
                case 'spacing':
                    return '--wp--preset--spacing--' + tokenPath;
                case 'custom':
                    return '--wp--custom--' + tokenPath.replace(/\./g, '--');
                default:
                    return null;
            }
        },

        loadThemeTokens: function() {
            $.ajax({
                url: miDesignBookSync.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mi_get_theme_json_tokens',
                    nonce: miDesignBookSync.nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Theme tokens:', response.data);
                        DesignBookSync.displayTokens(response.data);
                    }
                }
            });
        },

        displayTokens: function(tokens) {
            // This can be expanded to show tokens in a UI panel
            console.log('Colors:', tokens.colors);
            console.log('Typography:', tokens.typography);
            console.log('Spacing:', tokens.spacing);
            console.log('Custom:', tokens.custom);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        DesignBookSync.init();
    });

})(jQuery);

// Add some basic styles for the edit controls
const style = document.createElement('style');
style.textContent = `
    .token-edit-control {
        position: absolute;
        top: 5px;
        right: 5px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .color-swatch:hover .token-edit-control,
    .typography-sample:hover .token-edit-control,
    .spacing-sample:hover .token-edit-control {
        opacity: 1;
    }
    
    .token-edit-control input {
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .token-color-input {
        width: 40px;
        height: 40px;
        padding: 0;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        cursor: pointer;
    }
    
    .sync-status {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #333;
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        z-index: 9999;
    }
    
    .sync-status.success {
        background: #4caf50;
    }
    
    .sync-status.error {
        background: #f44336;
    }
`;
document.head.appendChild(style);
