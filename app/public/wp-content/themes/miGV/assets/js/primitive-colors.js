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

    // CMYK conversion utilities
    function hexToCMYK(hex) {
        hex = hex.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16) / 255;
        const g = parseInt(hex.substr(2, 2), 16) / 255;
        const b = parseInt(hex.substr(4, 2), 16) / 255;
        
        const k = 1 - Math.max(r, g, b);
        const c = k === 1 ? 0 : (1 - r - k) / (1 - k);
        const m = k === 1 ? 0 : (1 - g - k) / (1 - k);
        const y = k === 1 ? 0 : (1 - b - k) / (1 - k);
        
        return {
            c: Math.round(c * 100),
            m: Math.round(m * 100),
            y: Math.round(y * 100),
            k: Math.round(k * 100)
        };
    }

    function cmykToHex(c, m, y, k) {
        c = c / 100;
        m = m / 100;
        y = y / 100;
        k = k / 100;
        
        const r = 255 * (1 - c) * (1 - k);
        const g = 255 * (1 - m) * (1 - k);
        const b = 255 * (1 - y) * (1 - k);
        
        const toHex = x => {
            const hex = Math.round(x).toString(16);
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

    // Tab switching for HSL/CMYK
    $('.tab-btn').on('click', function() {
        const $panel = $(this).closest('.adjustment-panel');
        const tab = $(this).data('tab');
        
        // Update tab buttons
        $panel.find('.tab-btn').removeClass('active');
        $(this).addClass('active');
        
        // Update tab content
        $panel.find('.tab-content').removeClass('active');
        $panel.find(`.tab-content[data-tab="${tab}"]`).addClass('active');
    });
    
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
        
        // Update hex input
        $card.find('.color-hex-input').val(color);
        
        // Reset adjustment sliders
        $card.find('.hue-slider, .saturation-slider, .lightness-slider').val(0);
        $card.find('.cyan-slider, .magenta-slider, .yellow-slider, .black-slider').val(0);
        
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
            showSaveIndicator('error', 'Invalid hex color format');
            return;
        }
        
        // Update preview
        $card.find('.color-preview').css('background-color', color);
        
        // Update color picker
        $card.find('.color-picker-input').val(color);
        
        // Reset adjustment sliders
        $card.find('.hue-slider, .saturation-slider, .lightness-slider').val(0);
        $card.find('.cyan-slider, .magenta-slider, .yellow-slider, .black-slider').val(0);
        
        // Sync to theme.json
        syncColor(slug, color);
    });
    
    // Opacity slider
    $('.color-opacity-slider').on('input', function() {
        const opacity = $(this).val();
        const $card = $(this).closest('.color-card');
        const $preview = $card.find('.color-preview');
        
        // Update slider value display
        $(this).siblings('.slider-value').text(opacity + '%');
        
        // Apply opacity to preview
        $preview.css('opacity', opacity / 100);
    });
    
    // HSL adjustment sliders - FIXED lightness issue
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
        // Fixed: Use percentage-based lightness adjustment instead of absolute
        let l = Math.max(0, Math.min(100, hsl.l * (1 + lightAdjust / 100)));
        
        const newColor = hslToHex(h, s, l);
        
        // Update preview only (don't save until slider is released)
        $card.find('.color-preview').css('background-color', newColor);
    });

    // CMYK adjustment sliders
    $('.cyan-slider, .magenta-slider, .yellow-slider, .black-slider').on('input', function() {
        const $card = $(this).closest('.color-card');
        const baseColor = $card.find('.color-hex-input').val();
        const cmyk = hexToCMYK(baseColor);
        
        // Apply adjustments
        const cyanAdjust = parseInt($card.find('.cyan-slider').val());
        const magentaAdjust = parseInt($card.find('.magenta-slider').val());
        const yellowAdjust = parseInt($card.find('.yellow-slider').val());
        const blackAdjust = parseInt($card.find('.black-slider').val());
        
        let c = Math.max(0, Math.min(100, cmyk.c + cyanAdjust));
        let m = Math.max(0, Math.min(100, cmyk.m + magentaAdjust));
        let y = Math.max(0, Math.min(100, cmyk.y + yellowAdjust));
        let k = Math.max(0, Math.min(100, cmyk.k + blackAdjust));
        
        const newColor = cmykToHex(c, m, y, k);
        
        // Update preview only (don't save until slider is released)
        $card.find('.color-preview').css('background-color', newColor);
    });
    
    // Save on HSL slider release
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
        let l = Math.max(0, Math.min(100, hsl.l * (1 + lightAdjust / 100)));
        
        const newColor = hslToHex(h, s, l);
        
        // Update inputs
        $card.find('.color-hex-input').val(newColor);
        $card.find('.color-picker-input').val(newColor);
        
        // Sync to theme.json
        syncColor(slug, newColor);
    });

    // Save on CMYK slider release
    $('.cyan-slider, .magenta-slider, .yellow-slider, .black-slider').on('change', function() {
        const $card = $(this).closest('.color-card');
        const slug = $card.find('.color-hex-input').data('slug');
        const baseColor = $card.find('.color-hex-input').val();
        const cmyk = hexToCMYK(baseColor);
        
        // Apply adjustments
        const cyanAdjust = parseInt($card.find('.cyan-slider').val());
        const magentaAdjust = parseInt($card.find('.magenta-slider').val());
        const yellowAdjust = parseInt($card.find('.yellow-slider').val());
        const blackAdjust = parseInt($card.find('.black-slider').val());
        
        let c = Math.max(0, Math.min(100, cmyk.c + cyanAdjust));
        let m = Math.max(0, Math.min(100, cmyk.m + magentaAdjust));
        let y = Math.max(0, Math.min(100, cmyk.y + yellowAdjust));
        let k = Math.max(0, Math.min(100, cmyk.k + blackAdjust));
        
        const newColor = cmykToHex(c, m, y, k);
        
        // Update inputs
        $card.find('.color-hex-input').val(newColor);
        $card.find('.color-picker-input').val(newColor);
        
        // Sync to theme.json
        syncColor(slug, newColor);
    });
    
    // CSS variable preview button
    $('.css-variable-preview').on('click', function() {
        const variable = $(this).data('variable');
        const $card = $(this).closest('.color-card');
        const currentColor = $card.find('.color-hex-input').val();
        
        // Create a preview modal or tooltip showing how the variable would look
        const $preview = $(`
            <div class="css-preview-modal">
                <div class="css-preview-content">
                    <h4>CSS Variable Preview</h4>
                    <div class="css-example">
                        <code>color: ${variable};</code>
                        <div class="color-sample" style="background-color: ${currentColor}"></div>
                    </div>
                    <div class="css-example">
                        <code>background: ${variable};</code>
                        <div class="color-sample" style="background-color: ${currentColor}; color: white; padding: 8px;">Sample Text</div>
                    </div>
                    <button class="close-preview">Close</button>
                </div>
            </div>
        `);
        
        $('body').append($preview);
        
        // Close preview
        $preview.on('click', '.close-preview, .css-preview-modal', function(e) {
            if (e.target === this) {
                $preview.remove();
            }
        });
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
    
    // CSS variable preview
    $('.color-card').on('input', '.color-hex-input', function() {
        const $card = $(this).closest('.color-card');
        const slug = $card.data('slug');
        const color = $(this).val();
        
        // Update CSS variable preview
        $card.find('.css-variable-preview').text(`--wp--preset--color--${slug}: ${color};`);
    });
});
