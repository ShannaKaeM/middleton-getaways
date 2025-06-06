jQuery(document).ready(function($) {
    'use strict';

    // Check if the text component editor exists on the page
    if (!$('.text-editor-component').length) {
        return;
    }

    let textComponentData = {};
    let typographyPrimitives = {}; // Still needed to populate individual typography dropdowns
    let colorPrimitives = {}; // Still needed to populate individual color dropdowns

    // Function to load all necessary JSON data
    function loadAllData() {
        // Load text component data
        $.getJSON(miGV.theme_uri + '/components/sm-components/text.json', function(data) {
            textComponentData = data;
            populateTextStylesDropdown(data);
            updatePreview(); // Initial preview update after loading data
        }).fail(function() {
            console.error('Error loading text.json');
        });

        // Load typography primitives (for individual selection if needed)
        $.getJSON(miGV.theme_uri + '/primitives/typography.json', function(data) {
            typographyPrimitives = data;
            populatePrimitiveDropdowns('typography', typographyPrimitives);
        }).fail(function() {
            console.error('Error loading typography.json');
        });

        // Load color primitives (for individual selection if needed)
        $.getJSON(miGV.theme_uri + '/primitives/colors.json', function(data) {
            colorPrimitives = data;
            populatePrimitiveDropdowns('colors', colorPrimitives);
        }).fail(function() {
            console.error('Error loading colors.json');
        });
    }

    // Function to populate the main text style dropdown
    function populateTextStylesDropdown(data) {
        const $select = $('#text-style-select'); // Assuming a new dropdown for text styles
        $select.empty();
        $select.append($('<option>', { value: '', text: '-- Select Text Style --' }));
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                $select.append($('<option>', { value: key, text: key.replace(/-/g, ' ').replace(/_/g, ' ').trim() }));
            }
        }
    }

    // Function to populate individual primitive dropdowns (e.g., font family, color)
    function populatePrimitiveDropdowns(primitiveType, data) {
        $(`.primitive-select[data-primitive-type="${primitiveType}"]`).each(function() {
            const primitiveKey = $(this).data('primitive-key');
            const $select = $(this);
            $select.empty(); // Clear existing options

            // Add a default empty option
            $select.append($('<option>', {
                value: '',
                text: `-- Select ${primitiveKey.replace(/_/g, ' ')} --`
            }));

            // Recursively add options for nested objects
            function addOptions(obj, prefix = '') {
                for (const key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        if (typeof obj[key] === 'object' && obj[key] !== null) {
                            addOptions(obj[key], prefix + key + '-');
                        } else {
                            $select.append($('<option>', {
                                value: prefix + key,
                                text: (prefix + key).replace(/_/g, ' ').replace(/-/g, ' ').trim()
                            }));
                        }
                    }
                }
            }

            // Special handling for colors to flatten them for selection
            if (primitiveType === 'colors') {
                for (const group in data) {
                    if (data.hasOwnProperty(group) && typeof data[group] === 'object') {
                        for (const shade in data[group]) {
                            if (data[group].hasOwnProperty(shade)) {
                                const value = group + '-' + shade;
                                $select.append($('<option>', {
                                    value: value,
                                    text: value.replace(/_/g, ' ').replace(/-/g, ' ').trim()
                                }));
                            }
                        }
                    }
                }
            } else {
                addOptions(data[primitiveKey] || data, '');
            }
        });
    }

    // Event listener for dropdown changes (both text style and individual primitives)
    $(document).on('change', '#text-style-select, .primitive-select', function() {
        updatePreview();
    });

    // Event listener for text content changes
    $(document).on('input', '.text-content-input', function() {
        updatePreview();
    });

    // Function to update the preview
    function updatePreview() {
        const $preview = $('.text-preview');
        const $textContentInput = $('.text-content-input');
        const $previewText = $preview.find('.preview-text');
        const selectedTextStyleKey = $('#text-style-select').val();

        // Update preview text content
        $previewText.text($textContentInput.val() || 'This is a preview of your text.');

        let currentStyles = {};

        // If a text style is selected, use its defined primitives
        if (selectedTextStyleKey && textComponentData[selectedTextStyleKey]) {
            currentStyles = textComponentData[selectedTextStyleKey];
        } else {
            // Otherwise, use individual primitive selections
            $('.primitive-select[data-primitive-type="typography"]').each(function() {
                const key = $(this).data('primitive-key');
                const value = $(this).val();
                if (value) {
                    currentStyles[key] = value;
                }
            });
            $('.primitive-select[data-primitive-type="colors"]').each(function() {
                const key = $(this).data('primitive-key');
                const value = $(this).val();
                if (value) {
                    currentStyles[key] = value;
                }
            });
        }

        // Construct inline style string for preview
        let inlineStyle = '';

        // Apply typography styles
        const typographyMap = {
            'font_family': 'font-family',
            'font_size': 'font-size',
            'font_weight': 'font-weight',
            'line_height': 'line-height',
            'letter_spacing': 'letter-spacing',
            'text_transform': 'text-transform'
        };

        for (const key in typographyMap) {
            if (currentStyles.hasOwnProperty(key) && typographyPrimitives[key]) {
                const primitiveValue = getPrimitiveValue('typography', typographyPrimitives[key], currentStyles[key]);
                if (primitiveValue) {
                    inlineStyle += `${typographyMap[key]}: ${primitiveValue}; `;
                }
            }
        }

        // Apply color styles
        const colorMap = {
            'color': 'color',
            'background_color': 'background-color'
        };

        for (const key in colorMap) {
            if (currentStyles.hasOwnProperty(key) && colorPrimitives) {
                const primitiveValue = getPrimitiveValue('colors', colorPrimitives, currentStyles[key]);
                if (primitiveValue) {
                    inlineStyle += `${colorMap[key]}: ${primitiveValue}; `;
                }
            }
        }

        $preview.attr('style', inlineStyle.trim());
    }

    // Helper to get the actual primitive value from the loaded JSON data
    function getPrimitiveValue(primitiveType, data, keyPath) {
        // Handle nested keys like 'font_sizes-large' or 'primary-default'
        const parts = keyPath.split('-');
        let value = data;
        for (let i = 0; i < parts.length; i++) {
            if (value && typeof value === 'object' && value.hasOwnProperty(parts[i])) {
                value = value[parts[i]];
            } else {
                return null; // Key not found
            }
        }
        return value;
    }

    // Initial load of all data
    loadAllData();
});
