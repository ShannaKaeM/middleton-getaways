/**
 * Mi Design Book - Interactive Card Component Editor
 */
(function($) {
    'use strict';

    // Component state
    let cardState = {
        type: 'default',
        variant: 'default',
        pretitle: '',
        title: '',
        subtitle: '',
        description: '',
        image: '',
        badges: [],
        actions: [],
        meta: [],
        cornerStyle: 'rounded',
        showShadow: true,
        showHover: true,
        // Type-specific fields
        property: {
            price: '',
            bedrooms: '',
            bathrooms: '',
            sqft: '',
            address: ''
        },
        business: {
            category: '',
            rating: '',
            hours: '',
            phone: '',
            website: ''
        }
    };

    // Field mappings for dynamic data
    const fieldMappings = {
        property: {
            title: 'Post Title',
            description: 'Post Excerpt',
            image: 'Featured Image',
            'property_price': 'Price Badge/Meta',
            'property_bedrooms': 'Bedrooms Meta',
            'property_bathrooms': 'Bathrooms Meta',
            'property_sqft': 'Square Feet Meta',
            'property_status': 'Status Badge',
            'property_type': 'Type Badge',
            'property_address': 'Address Meta',
            'property_city': 'City Meta'
        },
        business: {
            title: 'Post Title',
            description: 'Post Excerpt',
            image: 'Featured Image',
            'business_type': 'Type Badge',
            'business_phone': 'Phone Meta',
            'business_hours': 'Hours Meta',
            'business_website': 'Website Button'
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        initializeEventHandlers();
        loadCardTypes();
        updatePreview();
        updateCode();
    });

    // Initialize all event handlers
    function initializeEventHandlers() {
        // Data source toggle
        $('input[name="data-source"]').on('change', function() {
            const isDynamic = $(this).val() === 'dynamic';
            $('#dynamic-data-selector').toggle(isDynamic);
            
            if (!isDynamic) {
                // Clear dynamic data when switching back to static
                $('#dynamic-post-selector').val('');
                $('.form-control').prop('readonly', false);
            }
        });

        // Dynamic post selector
        $('#dynamic-post-selector').on('change', function() {
            const postId = $(this).val();
            const postType = $(this).find(':selected').data('type');
            
            if (postId) {
                fetchPostData(postId, postType);
            } else {
                // Clear fields if no post selected
                $('.form-control').prop('readonly', false);
                $('#field-mappings').text('Select a post to see field mappings');
            }
        });
        // Card type change
        $('#card-type').on('change', function() {
            cardState.type = $(this).val();
            updateTypeSpecificFields();
            updatePreview();
            updateCode();
        });

        // Card variant change
        $('#card-variant').on('change', function() {
            cardState.variant = $(this).val();
            updatePreview();
            updateCode();
        });

        // Text field changes
        $('#card-pretitle, #card-title, #card-subtitle, #card-description').on('input', function() {
            const field = $(this).attr('id').replace('card-', '');
            cardState[field] = $(this).val();
            updatePreview();
            updateCode();
        });

        // Style options
        $('#corner-style').on('change', function() {
            cardState.cornerStyle = $(this).val();
            updatePreview();
            updateCode();
        });

        $('#show-shadow').on('change', function() {
            cardState.showShadow = $(this).is(':checked');
            updatePreview();
            updateCode();
        });

        $('#show-hover').on('change', function() {
            cardState.showHover = $(this).is(':checked');
            updatePreview();
            updateCode();
        });

        // Badge management
        $('.add-badge').on('click', function() {
            const text = $('.badge-text').val();
            const variant = $('.badge-variant').val();
            
            if (text) {
                cardState.badges.push({ content: text, variant: variant });
                $('.badge-text').val('');
                updateBadgesList();
                updatePreview();
                updateCode();
            }
        });

        // Action/Button management
        $('.add-action').on('click', function() {
            const label = $('.action-label').val();
            const variant = $('.action-variant').val();
            const size = $('.action-size').val();
            
            if (label) {
                cardState.actions.push({ 
                    label: label, 
                    variant: variant,
                    size: size,
                    href: '#' 
                });
                $('.action-label').val('');
                updateActionsList();
                updatePreview();
                updateCode();
            }
        });

        // Image handling
        $('#upload-image').on('click', function() {
            // In a real implementation, this would open WordPress media library
            // For now, we'll use a prompt
            const imageUrl = prompt('Enter image URL:');
            if (imageUrl) {
                cardState.image = imageUrl;
                updateImagePreview();
                updatePreview();
                updateCode();
            }
        });

        $('#use-sample').on('click', function() {
            $('#sample-images-modal').show();
        });

        // Sample image selection
        $('.sample-image-item').on('click', function() {
            cardState.image = $(this).data('url');
            updateImagePreview();
            updatePreview();
            updateCode();
            $('#sample-images-modal').hide();
        });

        $('#close-modal').on('click', function() {
            $('#sample-images-modal').hide();
        });

        // Preview controls
        $('#refresh-preview').on('click', function() {
            updatePreview();
        });

        $('#preview-background').on('change', function() {
            const bg = $(this).val();
            $('#preview-container')
                .removeClass('bg-gray bg-dark bg-pattern')
                .addClass(bg !== 'white' ? 'bg-' + bg : '');
        });

        // Export functionality
        $('#export-code').on('click', function() {
            const code = generateTwigCode();
            downloadCode(code);
        });

        $('#copy-code').on('click', function() {
            const code = $('#generated-code').text();
            copyToClipboard(code);
        });

        // Type-specific field handlers
        $('#property-price, #property-bedrooms, #property-bathrooms, #property-sqft').on('input', function() {
            const field = $(this).attr('id').replace('property-', '');
            cardState.property[field] = $(this).val();
            updateMetaFromTypeFields();
            updatePreview();
            updateCode();
        });

        $('#business-category, #business-rating, #business-hours').on('input', function() {
            const field = $(this).attr('id').replace('business-', '');
            cardState.business[field] = $(this).val();
            updateMetaFromTypeFields();
            updatePreview();
            updateCode();
        });
    }

    // Update type-specific fields visibility
    function updateTypeSpecificFields() {
        const type = cardState.type;
        
        if (type === 'property' || type === 'business') {
            $('#type-specific-fields').show();
            $('.property-fields').toggle(type === 'property');
            $('.business-fields').toggle(type === 'business');
        } else {
            $('#type-specific-fields').hide();
        }
        
        updateMetaFromTypeFields();
    }

    // Update meta data based on type-specific fields
    function updateMetaFromTypeFields() {
        cardState.meta = [];
        
        if (cardState.type === 'property') {
            if (cardState.property.bedrooms) {
                cardState.meta.push({
                    icon: 'bed',
                    value: cardState.property.bedrooms + ' Beds'
                });
            }
            if (cardState.property.bathrooms) {
                cardState.meta.push({
                    icon: 'bath',
                    value: cardState.property.bathrooms + ' Baths'
                });
            }
            if (cardState.property.sqft) {
                cardState.meta.push({
                    icon: 'home',
                    value: cardState.property.sqft + ' sqft'
                });
            }
        } else if (cardState.type === 'business') {
            if (cardState.business.category) {
                cardState.meta.push({
                    icon: 'tag',
                    value: cardState.business.category
                });
            }
            if (cardState.business.rating) {
                cardState.meta.push({
                    icon: 'star',
                    value: cardState.business.rating + ' ★'
                });
            }
            if (cardState.business.hours) {
                cardState.meta.push({
                    icon: 'clock',
                    value: cardState.business.hours
                });
            }
        }
    }

    // Update badges list display
    function updateBadgesList() {
        const $list = $('#badges-list');
        $list.empty();
        
        cardState.badges.forEach((badge, index) => {
            const $badge = $(`
                <div class="badge-item">
                    <span>${badge.content} (${badge.variant})</span>
                    <span class="remove" data-index="${index}">×</span>
                </div>
            `);
            $list.append($badge);
        });
        
        // Add remove handlers
        $list.find('.remove').on('click', function() {
            const index = $(this).data('index');
            cardState.badges.splice(index, 1);
            updateBadgesList();
            updatePreview();
            updateCode();
        });
    }

    // Update actions list display
    function updateActionsList() {
        const $list = $('#actions-list');
        $list.empty();
        
        cardState.actions.forEach((action, index) => {
            const $action = $(`
                <div class="action-item">
                    <span>${action.label} (${action.variant})</span>
                    <span class="remove" data-index="${index}">×</span>
                </div>
            `);
            $list.append($action);
        });
        
        // Add remove handlers
        $list.find('.remove').on('click', function() {
            const index = $(this).data('index');
            cardState.actions.splice(index, 1);
            updateActionsList();
            updatePreview();
            updateCode();
        });
    }

    // Fetch post data via AJAX
    function fetchPostData(postId, postType) {
        $.ajax({
            url: miDesignBook.ajaxurl,
            type: 'POST',
            data: {
                action: 'mi_get_post_data',
                post_id: postId,
                nonce: miDesignBook.nonce
            },
            beforeSend: function() {
                $('#dynamic-post-selector').prop('disabled', true);
                $('#field-mappings').html('<em>Loading...</em>');
            },
            success: function(response) {
                if (response.success) {
                    populateFieldsFromPost(response.data, postType);
                    showFieldMappings(postType);
                } else {
                    alert('Error loading post data');
                }
            },
            error: function() {
                alert('Error connecting to server');
            },
            complete: function() {
                $('#dynamic-post-selector').prop('disabled', false);
            }
        });
    }

    // Populate fields from post data
    function populateFieldsFromPost(data, postType) {
        // Basic fields
        $('#card-title').val(data.title).prop('readonly', true);
        $('#card-description').val(data.description).prop('readonly', true);
        
        // Set image
        if (data.image) {
            cardState.image = data.image;
            updateImagePreview();
        }
        
        // Clear existing badges and meta
        cardState.badges = [];
        cardState.meta = [];
        
        // Handle property-specific fields
        if (postType === 'property' && data.fields) {
            // Set card type
            $('#card-type').val('property');
            cardState.type = 'property';
            
            // Price
            if (data.fields.price) {
                $('#card-pretitle').val('$' + parseInt(data.fields.price).toLocaleString()).prop('readonly', true);
                cardState.pretitle = '$' + parseInt(data.fields.price).toLocaleString();
            }
            
            // Status badge
            if (data.fields.status) {
                const statusMap = {
                    'available': 'primary',
                    'sold': 'secondary-dark',
                    'pending': 'neutral',
                    'rented': 'secondary',
                    'off_market': 'base'
                };
                cardState.badges.push({
                    content: data.fields.status.charAt(0).toUpperCase() + data.fields.status.slice(1).replace('_', ' '),
                    variant: statusMap[data.fields.status] || 'neutral'
                });
            }
            
            // Type badge
            if (data.fields.type) {
                cardState.badges.push({
                    content: data.fields.type.charAt(0).toUpperCase() + data.fields.type.slice(1),
                    variant: 'primary-light'
                });
            }
            
            // Property meta
            if (data.fields.bedrooms) {
                cardState.meta.push({ icon: 'bed', value: data.fields.bedrooms + ' Beds' });
            }
            if (data.fields.bathrooms) {
                cardState.meta.push({ icon: 'bath', value: data.fields.bathrooms + ' Baths' });
            }
            if (data.fields.sqft) {
                cardState.meta.push({ icon: 'home', value: parseInt(data.fields.sqft).toLocaleString() + ' sqft' });
            }
            
            // Location
            if (data.fields.address && data.fields.city) {
                $('#card-subtitle').val(data.fields.address + ', ' + data.fields.city).prop('readonly', true);
                cardState.subtitle = data.fields.address + ', ' + data.fields.city;
            }
            
            // Update property-specific form fields
            $('#property-price').val(data.fields.price).prop('readonly', true);
            $('#property-bedrooms').val(data.fields.bedrooms).prop('readonly', true);
            $('#property-bathrooms').val(data.fields.bathrooms).prop('readonly', true);
            $('#property-sqft').val(data.fields.sqft).prop('readonly', true);
            
        } else if (postType === 'business' && data.fields) {
            // Set card type
            $('#card-type').val('business');
            cardState.type = 'business';
            
            // Business type badge
            if (data.fields.type) {
                cardState.badges.push({
                    content: data.fields.type.charAt(0).toUpperCase() + data.fields.type.slice(1),
                    variant: 'primary'
                });
            }
            
            // Business meta
            if (data.fields.phone) {
                cardState.meta.push({ icon: 'phone', value: data.fields.phone });
            }
            if (data.fields.hours) {
                cardState.meta.push({ icon: 'clock', value: data.fields.hours.split('\n')[0] }); // First line of hours
            }
            
            // Address
            if (data.fields.address) {
                $('#card-subtitle').val(data.fields.address).prop('readonly', true);
                cardState.subtitle = data.fields.address;
            }
            
            // Update business-specific form fields
            $('#business-category').val(data.fields.type).prop('readonly', true);
            $('#business-hours').val(data.fields.hours).prop('readonly', true);
        }
        
        // Add View Details button
        cardState.actions = [{
            label: 'View Details',
            variant: 'primary',
            size: 'md',
            href: data.link || '#'
        }];
        
        // Update displays
        updateBadgesList();
        updateActionsList();
        updateTypeSpecificFields();
        updatePreview();
        updateCode();
    }

    // Show field mappings
    function showFieldMappings(postType) {
        const mappings = fieldMappings[postType] || {};
        let html = '<div style="font-size: 0.8rem; line-height: 1.4;">';
        
        for (const [field, mapping] of Object.entries(mappings)) {
            html += `<strong>${field}:</strong> → ${mapping}<br/>`;
        }
        
        html += '</div>';
        $('#field-mappings').html(html);
    }

    // Update image preview
    function updateImagePreview() {
        const $preview = $('#image-preview');
        if (cardState.image) {
            $preview.html(`<img src="${cardState.image}" alt="Card image" />`);
        } else {
            $preview.html('<span class="placeholder">No image selected</span>');
        }
    }

    // Update card preview
    function updatePreview() {
        const $preview = $('#card-preview');
        
        // Build preview HTML (simplified version of actual card component)
        let previewHTML = `
            <div class="villa-card villa-card--${cardState.variant} ${cardState.cornerStyle} ${cardState.showShadow ? 'has-shadow' : ''} ${cardState.showHover ? 'has-hover' : ''}">
        `;
        
        // Image
        if (cardState.image) {
            previewHTML += `
                <div class="villa-card__image">
                    <img src="${cardState.image}" alt="${cardState.title || 'Card image'}" />
                </div>
            `;
        }
        
        // Content
        previewHTML += '<div class="villa-card__content">';
        
        // Badges
        if (cardState.badges.length > 0) {
            previewHTML += '<div class="villa-card__badges">';
            cardState.badges.forEach(badge => {
                previewHTML += `<span class="villa-badge villa-badge--${badge.variant}">${badge.content}</span>`;
            });
            previewHTML += '</div>';
        }
        
        // Text content
        if (cardState.pretitle) {
            previewHTML += `<div class="villa-card__pretitle">${cardState.pretitle}</div>`;
        }
        if (cardState.title) {
            previewHTML += `<h3 class="villa-card__title">${cardState.title}</h3>`;
        }
        if (cardState.subtitle) {
            previewHTML += `<div class="villa-card__subtitle">${cardState.subtitle}</div>`;
        }
        if (cardState.description) {
            previewHTML += `<p class="villa-card__description">${cardState.description}</p>`;
        }
        
        // Meta info
        if (cardState.meta.length > 0) {
            previewHTML += '<div class="villa-card__meta">';
            cardState.meta.forEach(meta => {
                previewHTML += `<span class="villa-meta-item"><i class="icon-${meta.icon}"></i> ${meta.value}</span>`;
            });
            previewHTML += '</div>';
        }
        
        // Actions
        if (cardState.actions.length > 0) {
            previewHTML += '<div class="villa-card__actions">';
            cardState.actions.forEach(action => {
                previewHTML += `<a href="${action.href}" class="villa-btn villa-btn--${action.variant} villa-btn--${action.size}">${action.label}</a>`;
            });
            previewHTML += '</div>';
        }
        
        previewHTML += '</div></div>';
        
        $preview.html(previewHTML);
    }

    // Update generated code
    function updateCode() {
        const code = generateTwigCode();
        $('#generated-code').text(code);
    }

    // Generate Twig code
    function generateTwigCode() {
        let code = "{% include 'component-books/card-book.twig' with {\n";
        
        // Add non-empty properties
        if (cardState.variant !== 'default') {
            code += `    variant: '${cardState.variant}',\n`;
        }
        if (cardState.pretitle) {
            code += `    pretitle: '${escapeString(cardState.pretitle)}',\n`;
        }
        if (cardState.title) {
            code += `    title: '${escapeString(cardState.title)}',\n`;
        }
        if (cardState.subtitle) {
            code += `    subtitle: '${escapeString(cardState.subtitle)}',\n`;
        }
        if (cardState.description) {
            code += `    description: '${escapeString(cardState.description)}',\n`;
        }
        if (cardState.image) {
            code += `    image: '${cardState.image}',\n`;
        }
        
        // Badges
        if (cardState.badges.length > 0) {
            code += '    badges: [\n';
            cardState.badges.forEach((badge, index) => {
                code += `        {content: '${escapeString(badge.content)}', variant: '${badge.variant}'}`;
                code += index < cardState.badges.length - 1 ? ',\n' : '\n';
            });
            code += '    ],\n';
        }
        
        // Meta
        if (cardState.meta.length > 0) {
            code += '    meta: [\n';
            cardState.meta.forEach((meta, index) => {
                code += `        {icon: '${meta.icon}', value: '${escapeString(meta.value)}'}`;
                code += index < cardState.meta.length - 1 ? ',\n' : '\n';
            });
            code += '    ],\n';
        }
        
        // Actions
        if (cardState.actions.length > 0) {
            code += '    actions: [\n';
            cardState.actions.forEach((action, index) => {
                code += `        {label: '${escapeString(action.label)}', variant: '${action.variant}', size: '${action.size}', href: '${action.href}'}`;
                code += index < cardState.actions.length - 1 ? ',\n' : '\n';
            });
            code += '    ],\n';
        }
        
        // Style options
        if (cardState.cornerStyle !== 'rounded') {
            code += `    corner_style: '${cardState.cornerStyle}',\n`;
        }
        if (!cardState.showShadow) {
            code += '    show_shadow: false,\n';
        }
        if (!cardState.showHover) {
            code += '    show_hover: false,\n';
        }
        
        // Remove trailing comma and newline
        code = code.replace(/,\n$/, '\n');
        
        code += '} %}';
        
        return code;
    }

    // Escape string for Twig
    function escapeString(str) {
        return str.replace(/'/g, "\\'");
    }

    // Copy to clipboard
    function copyToClipboard(text) {
        const temp = $('<textarea>');
        $('body').append(temp);
        temp.val(text).select();
        document.execCommand('copy');
        temp.remove();
        
        // Show feedback
        const $btn = $('#copy-code');
        const originalText = $btn.text();
        $btn.text('Copied!');
        setTimeout(() => {
            $btn.text(originalText);
        }, 2000);
    }

    // Download code as file
    function downloadCode(code) {
        const blob = new Blob([code], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'card-component.twig';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    // Toggle custom type input
    $('#toggle-custom-type').on('click', function() {
        const isCustom = $('#custom-type-name').is(':visible');
        $('#custom-type-name').toggle(!isCustom);
        $('#card-type').toggle(isCustom);
        $(this).text(isCustom ? 'Custom' : 'Cancel');
    });

    // Save as type
    $('#save-as-type').on('click', function() {
        $('#save-type-form').slideToggle();
        // Pre-fill with current type if custom
        if ($('#custom-type-name').is(':visible')) {
            $('#save-type-name').val($('#custom-type-name').val());
        }
    });

    $('#cancel-save-type').on('click', function() {
        $('#save-type-form').slideUp();
    });

    $('#confirm-save-type').on('click', function() {
        const typeName = $('#save-type-name').val().trim();
        const description = $('#save-type-description').val().trim();
        
        if (!typeName) {
            alert('Please enter a type name');
            return;
        }
        
        // Prepare configuration
        const configuration = {
            variant: cardState.variant,
            badges: cardState.badges,
            meta: cardState.meta,
            actions: cardState.actions,
            cornerStyle: cardState.cornerStyle,
            showShadow: cardState.showShadow,
            showHover: cardState.showHover,
            // Store field mappings if in dynamic mode
            fieldMappings: $('input[name="data-source"]:checked').val() === 'dynamic' ? {
                postType: $('#dynamic-post-selector').find(':selected').data('type'),
                pretitle: cardState.pretitle,
                subtitle: cardState.subtitle
            } : null
        };
        
        // Save via AJAX
        $.ajax({
            url: miDesignBook.ajaxurl,
            type: 'POST',
            data: {
                action: 'mi_save_card_type',
                nonce: miDesignBook.nonce,
                type_name: typeName.toLowerCase().replace(/\s+/g, '-'),
                display_name: typeName,
                description: description,
                configuration: JSON.stringify(configuration)
            },
            success: function(response) {
                if (response.success) {
                    alert('Card type saved successfully!');
                    $('#save-type-form').slideUp();
                    $('#save-type-name').val('');
                    $('#save-type-description').val('');
                    loadCardTypes(); // Reload the types dropdown
                } else {
                    alert('Error saving card type: ' + response.data);
                }
            },
            error: function() {
                alert('Error saving card type');
            }
        });
    });

    // Load saved type
    $('#saved-types').on('change', function() {
        const selectedType = $(this).val();
        if (selectedType) {
            loadSavedType(selectedType);
        }
    });

    // Load card types from database
    function loadCardTypes() {
        $.ajax({
            url: miDesignBook.ajaxurl,
            type: 'POST',
            data: {
                action: 'mi_get_card_types',
                nonce: miDesignBook.nonce
            },
            success: function(response) {
                if (response.success) {
                    const customGroup = $('#custom-types-group');
                    customGroup.empty();
                    
                    // Add custom types to dropdown
                    for (const [key, type] of Object.entries(response.data.custom)) {
                        customGroup.append(`
                            <option value="${key}">${type.name} - ${type.description}</option>
                        `);
                    }
                    
                    // Store types for later use
                    window.savedCardTypes = {
                        ...response.data.built_in,
                        ...response.data.custom
                    };
                }
            }
        });
    }

    // Load a saved type configuration
    function loadSavedType(typeName) {
        const typeConfig = window.savedCardTypes[typeName];
        if (!typeConfig) {
            alert('Type not found');
            return;
        }
        
        // Apply configuration
        const config = typeConfig.configuration;
        
        // Set variant
        if (config.variant) {
            $('#card-variant').val(config.variant);
            cardState.variant = config.variant;
        }
        
        // Set badges
        if (config.badges) {
            cardState.badges = config.badges;
            updateBadgesList();
        }
        
        // Set meta
        if (config.meta) {
            cardState.meta = config.meta;
        }
        
        // Set actions
        if (config.actions) {
            cardState.actions = config.actions;
            updateActionsList();
        }
        
        // Set style options
        if (config.cornerStyle) {
            $('#corner-style').val(config.cornerStyle);
            cardState.cornerStyle = config.cornerStyle;
        }
        
        $('#show-shadow').prop('checked', config.showShadow !== false);
        $('#show-hover').prop('checked', config.showHover !== false);
        cardState.showShadow = config.showShadow !== false;
        cardState.showHover = config.showHover !== false;
        
        // Update preview
        updatePreview();
        updateCode();
        
        // Show success message
        const $select = $('#saved-types');
        const originalText = $select.find('option:selected').text();
        $select.find('option:selected').text(originalText + ' ✓');
        setTimeout(() => {
            $select.find('option:selected').text(originalText);
        }, 2000);
    }

})(jQuery);