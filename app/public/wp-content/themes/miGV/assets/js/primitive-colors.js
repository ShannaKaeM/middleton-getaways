jQuery(document).ready(function($) {
    // Color manipulation utilities
    function hexToHSL(hex) {
        // Remove # if present
        hex = hex.replace('#', '');
        
        // Convert to RGB
        const r = parseInt(hex.substr(0, 2), 16) / 255;
        const g = parseInt(hex.substr(2, 2), 16) / 255;
        const b = parseInt(hex.substr(4, 2), 16) / 255;
        
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;
        
        if (max === min) {
            h = s = 0; // achromatic
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            
            switch (max) {
                case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                case g: h = ((b - r) / d + 2) / 6; break;
                case b: h = ((r - g) / d + 4) / 6; break;
            }
        }
        
        return {
            h: Math.round(h * 360),
            s: Math.round(s * 100),
            l: Math.round(l * 100)
        };
    }
    
    function hslToHex(h, s, l) {
        h = h / 360;
        s = s / 100;
        l = l / 100;
        
        let r, g, b;
        
        if (s === 0) {
            r = g = b = l; // achromatic
        } else {
            const hue2rgb = (p, q, t) => {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1/6) return p + (q - p) * 6 * t;
                if (t < 1/2) return q;
                if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                return p;
            };
            
            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;
            
            r = hue2rgb(p, q, h + 1/3);
            g = hue2rgb(p, q, h);
            b = hue2rgb(p, q, h - 1/3);
        }
        
        const toHex = x => {
            const hex = Math.round(x * 255).toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        };
        
        return '#' + toHex(r) + toHex(g) + toHex(b);
    }
    
    // Save indicator
    function showSaveIndicator(status, message) {
        const $indicator = $('#save-indicator');
        const $status = $indicator.find('.save-status');
        
        $indicator.removeClass('success error saving').addClass(status + ' show');
        $status.text(message);
        
        setTimeout(() => {
            $indicator.removeClass('show');
        }, 3000);
    }
    
    // Sync color to theme.json
    function syncColor(slug, color) {
        if (!primitiveColors.canEdit) {
            showSaveIndicator('error', 'You do not have permission to edit colors');
            return;
        }
        
        showSaveIndicator('saving', 'Saving color...');
        
        $.ajax({
            url: primitiveColors.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mi_sync_primitive_to_theme_json',
                nonce: primitiveColors.nonce,
                type: 'color',
                slug: slug,
                value: color
            },
            success: function(response) {
                if (response.success) {
                    showSaveIndicator('success', 'Color saved successfully');
                    // Update CSS variable
                    document.documentElement.style.setProperty('--wp--preset--color--' + slug, color);
                } else {
                    showSaveIndicator('error', response.data || 'Failed to save color');
                }
            },
            error: function() {
                showSaveIndicator('error', 'Network error occurred');
            }
        });
    }
    
    // Color picker trigger
    $('.color-picker-trigger').on('click', function(e) {
        e.preventDefault();
        const $card = $(this).closest('.color-card');
        $card.find('.color-picker-input').click();
    });
    
    // Color picker change
    $('.color-picker-input').on('change', function() {
        const color = $(this).val();
        const slug = $(this).data('slug');
        const $card = $(this).closest('.color-card');
        
        // Update preview
        $card.find('.color-preview').css('background-color', color);
        $card.find('.color-hex-input').val(color);
        
        // Reset adjustment sliders
        $card.find('.hue-slider, .saturation-slider, .lightness-slider').val(0);
        
        // Sync to theme.json
        syncColor(slug, color);
    });
    
    // Hex input change
    $('.color-hex-input').on('change', function() {
        const color = $(this).val();
        const slug = $(this).data('slug');
        const $card = $(this).closest('.color-card');
        
        // Validate hex
        if (!/^#[0-9A-F]{6}$/i.test(color)) {
            $(this).val($card.find('.color-picker-input').val());
            return;
        }
        
        // Update preview and picker
        $card.find('.color-preview').css('background-color', color);
        $card.find('.color-picker-input').val(color);
        
        // Reset adjustment sliders
        $card.find('.hue-slider, .saturation-slider, .lightness-slider').val(0);
        
        // Sync to theme.json
        syncColor(slug, color);
    });
    
    // Opacity slider
    $('.color-opacity-slider').on('input', function() {
        const opacity = $(this).val();
        const $card = $(this).closest('.color-card');
        const $preview = $card.find('.color-preview');
        const baseColor = $card.find('.color-hex-input').val();
        
        // Update slider value display
        $(this).siblings('.slider-value').text(opacity + '%');
        
        // Apply opacity to preview
        $preview.css('opacity', opacity / 100);
    });
    
    // Fine-tune toggle
    $('.adjustment-toggle').on('click', function() {
        $(this).siblings('.adjustment-panel').toggleClass('active');
    });
    
    // HSL adjustment sliders
    $('.hue-slider, .saturation-slider, .lightness-slider').on('input', function() {
        const $card = $(this).closest('.color-card');
        const baseColor = $card.find('.color-hex-input').val();
        const hsl = hexToHSL(baseColor);
        
        // Apply adjustments
        const hueAdjust = parseInt($card.find('.hue-slider').val());
        const satAdjust = parseInt($card.find('.saturation-slider').val());
        const lightAdjust = parseInt($card.find('.lightness-slider').val());
        
        let h = (hsl.h + hueAdjust + 360) % 360;
        let s = Math.max(0, Math.min(100, hsl.s + satAdjust));
        let l = Math.max(0, Math.min(100, hsl.l + lightAdjust));
        
        const newColor = hslToHex(h, s, l);
        
        // Update preview only (don't save until slider is released)
        $card.find('.color-preview').css('background-color', newColor);
    });
    
    // Save on slider release
    $('.hue-slider, .saturation-slider, .lightness-slider').on('change', function() {
        const $card = $(this).closest('.color-card');
        const slug = $card.find('.color-hex-input').data('slug');
        const baseColor = $card.find('.color-hex-input').val();
        const hsl = hexToHSL(baseColor);
        
        // Apply adjustments
        const hueAdjust = parseInt($card.find('.hue-slider').val());
        const satAdjust = parseInt($card.find('.saturation-slider').val());
        const lightAdjust = parseInt($card.find('.lightness-slider').val());
        
        let h = (hsl.h + hueAdjust + 360) % 360;
        let s = Math.max(0, Math.min(100, hsl.s + satAdjust));
        let l = Math.max(0, Math.min(100, hsl.l + lightAdjust));
        
        const newColor = hslToHex(h, s, l);
        
        // Update inputs
        $card.find('.color-hex-input').val(newColor);
        $card.find('.color-picker-input').val(newColor);
        
        // Sync to theme.json
        syncColor(slug, newColor);
    });
    
    // Copy to clipboard
    $('.copy-btn').on('click', function() {
        const text = $(this).data('copy');
        
        // Create temporary input
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Visual feedback
        const originalHtml = $(this).html();
        $(this).html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>');
        
        setTimeout(() => {
            $(this).html(originalHtml);
        }, 1000);
    });
    
    // Export colors
    $('#export-colors').on('click', function() {
        const colors = {};
        
        $('.color-card').each(function() {
            const slug = $(this).data('slug');
            const color = $(this).find('.color-hex-input').val();
            colors[slug] = color;
        });
        
        // Create download
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(colors, null, 2));
        const downloadAnchor = document.createElement('a');
        downloadAnchor.setAttribute("href", dataStr);
        downloadAnchor.setAttribute("download", "theme-colors.json");
        document.body.appendChild(downloadAnchor);
        downloadAnchor.click();
        downloadAnchor.remove();
        
        showSaveIndicator('success', 'Colors exported successfully');
    });
    
    // Reset colors (would need default values stored)
    $('#reset-colors').on('click', function() {
        if (confirm('Are you sure you want to reset all colors to their default values?')) {
            // This would need to be implemented with default values
            showSaveIndicator('error', 'Reset functionality not yet implemented');
        }
    });
});
