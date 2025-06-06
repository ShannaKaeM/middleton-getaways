jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize the colors editor
    initColorsEditor();
    
    // Initialize colors editor
    function initColorsEditor() {
        // Only initialize if we're on the colors editor page
        if (!$('.colors-editor').length) return;
        
        bindEventHandlers();
        updatePreviewColors();
        initializePreviewPanels();
        updateAllColorInputs(); // New function to initialize HSL/CMYK
    }
    
    // Bind all event handlers
    function bindEventHandlers() {
        // Preview type switcher
        $('#color-preview-type').on('change', function() {
            var selectedType = $(this).val();
            $('.preview-panel').hide();
            $('#' + selectedType + '-preview').show();
        });
        
        // Color picker changes
        $(document).on('input', '.color-picker', function() {
            const token = $(this).data('token');
            const value = $(this).val();
            updateColorValue(token, value);
        });
        
        // Hex input changes
        $(document).on('input', '.hex-input', function() {
            const token = $(this).data('token');
            const value = $(this).val();
            
            // Validate hex color
            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                updateColorValue(token, value);
            }
        });

        // HSL input changes
        $(document).on('input', '.hsl-input', function() {
            const token = $(this).data('token');
            const hslString = $(this).val();
            const hexValue = hslToHex(hslString);
            if (hexValue) {
                updateColorValue(token, hexValue);
            }
        });

        // CMYK input changes
        $(document).on('input', '.cmyk-input', function() {
            const token = $(this).data('token');
            const cmykString = $(this).val();
            const hexValue = cmykToHex(cmykString);
            if (hexValue) {
                updateColorValue(token, hexValue);
            }
        });
        
        // Copy buttons
        $(document).on('click', '.copy-btn', function(e) { // Changed to .copy-btn
            e.preventDefault();
            const value = $(this).data('copy');
            copyToClipboard(value);
            showCopyFeedback($(this));
        });
        
        // Action buttons
        $('.btn-save, #save-colors').on('click', function() {
            saveColors();
        });
        
        $('.btn-sync, #sync-colors').on('click', function() {
            syncColors();
        });
        
        $('.btn-reset, #reset-colors').on('click', function() {
            if (confirm('Are you sure you want to reset all colors to defaults?')) {
                resetColors();
            }
        });
        
        $('.btn-export, #export-colors').on('click', function() {
            exportColors();
        });
        
        // Update button selectors to match the new template IDs
    }
    
    // Update color value across all related inputs
    function updateColorValue(token, hexValue) {
        const tokenItem = $(`.token-item[data-token="${token}"]`);
        
        // Update color picker
        tokenItem.find('.color-picker').val(hexValue);
        
        // Update hex input
        tokenItem.find('.hex-input').val(hexValue);

        // Update HSL input
        const hsl = hexToHsl(hexValue);
        tokenItem.find('.hsl-input').val(`${hsl.h}, ${hsl.s}%, ${hsl.l}%`);

        // Update CMYK input
        const cmyk = hexToCmyk(hexValue);
        tokenItem.find('.cmyk-input').val(`${cmyk.c}%, ${cmyk.m}%, ${cmyk.y}%, ${cmyk.k}%`);
        
        // Update preview background
        tokenItem.find('.color-preview').css('background-color', hexValue);
        
        // Update copy button data
        tokenItem.find('.copy-btn[data-copy^="#"]').attr('data-copy', hexValue); // Changed to .copy-btn
        
        // Update CSS variable in document
        document.documentElement.style.setProperty(`--colors-${token}`, hexValue);
        
        // Mark as changed
        markAsChanged();
    }

    // Initialize all color inputs (HSL/CMYK) on load
    function updateAllColorInputs() {
        $('.token-item').each(function() {
            const token = $(this).data('token');
            const hexValue = $(this).find('.hex-input').val();
            updateColorValue(token, hexValue); // This will populate HSL/CMYK
        });
    }
    
    // Update all preview colors on page load
    function updatePreviewColors() {
        $('.token-item').each(function() {
            const token = $(this).data('token');
            const colorValue = $(this).find('.hex-input').val();
            $(this).find('.color-preview').css('background-color', colorValue);
            document.documentElement.style.setProperty(`--colors-${token}`, colorValue);
        });
    }

    function initializePreviewPanels() {
        // Show palette preview by default
        $('#palette-preview').show();
        $('#color-preview-type').val('palette');
    }

    // Helper function to copy text to clipboard
    function copyToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }

    // Show feedback after copying
    function showCopyFeedback(button) {
        const originalText = button.html();
        button.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>');
        setTimeout(() => {
            button.html(originalText);
        }, 1500);
    }

    // Mark editor as changed
    function markAsChanged() {
        // Implement visual feedback for changes, e.g., enable save button
    }

    // AJAX Save Colors
    function saveColors() {
        const colorsData = {};
        $('.token-item').each(function() {
            const token = $(this).data('token');
            colorsData[token] = $(this).find('.hex-input').val();
        });

        $.ajax({
            url: miGV.ajax_url,
            type: 'POST',
            data: {
                action: 'migv_save_primitive_colors',
                nonce: miGV.nonce,
                colors: colorsData
            },
            success: function(response) {
                if (response.success) {
                    alert('Colors saved successfully!');
                } else {
                    alert('Error saving colors: ' + response.data);
                }
            },
            error: function() {
                alert('AJAX error saving colors.');
            }
        });
    }

    // AJAX Sync Colors
    function syncColors() {
        const colorsData = {};
        $('.token-item').each(function() {
            const token = $(this).data('token');
            colorsData[token] = $(this).find('.hex-input').val();
        });

        $.ajax({
            url: miGV.ajax_url,
            type: 'POST',
            data: {
                action: 'migv_sync_primitive_colors',
                nonce: miGV.nonce,
                colors: colorsData
            },
            success: function(response) {
                if (response.success) {
                    alert('Colors synced to theme successfully!');
                } else {
                    alert('Error syncing colors: ' + response.data);
                }
            },
            error: function() {
                alert('AJAX error syncing colors.');
            }
        });
    }

    // AJAX Reset Colors
    function resetColors() {
        $.ajax({
            url: miGV.ajax_url,
            type: 'POST',
            data: {
                action: 'migv_reset_primitive_colors',
                nonce: miGV.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Colors reset to defaults!');
                    location.reload(); // Reload page to reflect changes
                } else {
                    alert('Error resetting colors: ' + response.data);
                }
            },
            error: function() {
                alert('AJAX error resetting colors.');
            }
        });
    }

    // AJAX Export Colors
    function exportColors() {
        $.ajax({
            url: miGV.ajax_url,
            type: 'POST',
            data: {
                action: 'migv_export_primitive_colors',
                nonce: miGV.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Assuming response.data contains the JSON string
                    const blob = new Blob([JSON.stringify(response.data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'colors.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    alert('Colors exported successfully!');
                } else {
                    alert('Error exporting colors: ' + response.data);
                }
            },
            error: function() {
                alert('AJAX error exporting colors.');
            }
        });
    }

    // Color Conversion Functions
    // Hex to HSL
    function hexToHsl(hex) {
        if (!hex || typeof hex !== 'string') return { h: 0, s: 0, l: 0 };
        let r = 0, g = 0, b = 0;
        // Handle 3-digit hex
        if (hex.length === 4) {
            r = parseInt(hex[1] + hex[1], 16);
            g = parseInt(hex[2] + hex[2], 16);
            b = parseInt(hex[3] + hex[3], 16);
        } else if (hex.length === 7) {
            r = parseInt(hex.substring(1, 3), 16);
            g = parseInt(hex.substring(3, 5), 16);
            b = parseInt(hex.substring(5, 7), 16);
        } else {
            return { h: 0, s: 0, l: 0 }; // Invalid hex
        }

        r /= 255;
        g /= 255;
        b /= 255;

        let max = Math.max(r, g, b), min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0; // achromatic
        } else {
            let d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }

        return {
            h: Math.round(h * 360),
            s: Math.round(s * 100),
            l: Math.round(l * 100)
        };
    }

    // HSL to Hex
    function hslToHex(hslString) {
        const match = hslString.match(/(\d+),\s*(\d+)%,\s*(\d+)%/);
        if (!match) return null;
        let h = parseInt(match[1]);
        let s = parseInt(match[2]) / 100;
        let l = parseInt(match[3]) / 100;

        let c = (1 - Math.abs(2 * l - 1)) * s,
            x = c * (1 - Math.abs((h / 60) % 2 - 1)),
            m = l - c / 2,
            r = 0, g = 0, b = 0;

        if (0 <= h && h < 60) {
            r = c; g = x; b = 0;
        } else if (60 <= h && h < 120) {
            r = x; g = c; b = 0;
        } else if (120 <= h && h < 180) {
            r = 0; g = c; b = x;
        } else if (180 <= h && h < 240) {
            r = 0; g = x; b = c;
        } else if (240 <= h && h < 300) {
            r = x; g = 0; b = c;
        } else if (300 <= h && h < 360) {
            r = c; g = 0; b = x;
        }
        r = Math.round((r + m) * 255).toString(16);
        g = Math.round((g + m) * 255).toString(16);
        b = Math.round((b + m) * 255).toString(16);

        if (r.length === 1) r = '0' + r;
        if (g.length === 1) g = '0' + g;
        if (b.length === 1) b = '0' + b;

        return '#' + r + g + b;
    }

    // Hex to CMYK
    function hexToCmyk(hex) {
        if (!hex || typeof hex !== 'string') return { c: 0, m: 0, y: 0, k: 0 };
        let r = 0, g = 0, b = 0;
        // Handle 3-digit hex
        if (hex.length === 4) {
            r = parseInt(hex[1] + hex[1], 16);
            g = parseInt(hex[2] + hex[2], 16);
            b = parseInt(hex[3] + hex[3], 16);
        } else if (hex.length === 7) {
            r = parseInt(hex.substring(1, 3), 16);
            g = parseInt(hex.substring(3, 5), 16);
            b = parseInt(hex.substring(5, 7), 16);
        } else {
            return { c: 0, m: 0, y: 0, k: 0 }; // Invalid hex
        }

        r /= 255;
        g /= 255;
        b /= 255;

        let k = 1 - Math.max(r, g, b);
        let c = (1 - r - k) / (1 - k);
        let m = (1 - g - k) / (1 - k);
        let y = (1 - b - k) / (1 - k);

        // Handle black (k=1) case to avoid NaN
        if (k === 1) {
            c = 0;
            m = 0;
            y = 0;
        }

        return {
            c: Math.round(c * 100),
            m: Math.round(m * 100),
            y: Math.round(y * 100),
            k: Math.round(k * 100)
        };
    }

    // CMYK to Hex
    function cmykToHex(cmykString) {
        const match = cmykString.match(/(\d+)%,\s*(\d+)%,\s*(\d+)%,\s*(\d+)%/);
        if (!match) return null;
        let c = parseInt(match[1]) / 100;
        let m = parseInt(match[2]) / 100;
        let y = parseInt(match[3]) / 100;
        let k = parseInt(match[4]) / 100;

        let r = 255 * (1 - c) * (1 - k);
        let g = 255 * (1 - m) * (1 - k);
        let b = 255 * (1 - y) * (1 - k);

        r = Math.round(r).toString(16);
        g = Math.round(g).toString(16);
        b = Math.round(b).toString(16);

        if (r.length === 1) r = '0' + r;
        if (g.length === 1) g = '0' + g;
        if (b.length === 1) b = '0' + b;

        return '#' + r + g + b;
    }

}); // End jQuery(document).ready
