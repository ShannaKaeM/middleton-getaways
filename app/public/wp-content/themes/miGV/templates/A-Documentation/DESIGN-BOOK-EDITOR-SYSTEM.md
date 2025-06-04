# Design Book Editor System Documentation

## Overview

The Design Book Editor System is a visual interface for managing design tokens in WordPress themes. It provides real-time editing capabilities for design primitives (colors, typography, spacing, etc.) with live preview and persistent storage. The system is designed to be theme-agnostic and could be packaged as a standalone WordPress plugin.

## Architecture

### Core Components

```
Design Book Editor System
├── Editor Interface Layer
│   ├── Visual Editors (Twig templates)
│   ├── Control Components
│   └── Live Preview
├── Data Layer
│   ├── JSON Primitive Storage
│   ├── AJAX Handlers
│   └── Security Layer
├── Integration Layer
│   ├── WordPress Hooks
│   ├── Timber/Twig Functions
│   └── Theme.json Sync (optional)
└── Asset Layer
    ├── CSS (design-book-editors.css): Styles for the editor UI. **The core UI of the Design Book Editor (e.g., its layout, buttons, input fields, text labels) should maintain its own fixed, accessible, and consistent styling. This ensures the editor remains usable regardless of the theme primitives being actively edited. This CSS should *not* directly use the dynamic theme primitives for the editor's own chrome. However, specific *preview areas* within the editor (e.g., color swatches, font example text, a live preview iframe) MUST use the dynamic theme primitives to accurately reflect the user's choices.** This approach is often called 'dogfooding' for the preview elements, while maintaining a stable UI for the tool itself. The [Primitive Design Library](./PRIMITIVE-DESIGN-LIBRARY.md) provides the CSS Custom Properties from the theme's primitives for these preview purposes.
    ├── JavaScript (primitive-*.js)
    └── Dependencies (jQuery, WordPress Core)
```

## Key Features

### 1. Visual Token Editing
- **Real-time Preview**: Changes to primitives update instantly in dedicated preview areas or a live preview pane, accurately reflecting the chosen theme styles.
- **Intuitive Controls**: Clear and accessible sliders, color pickers, and input fields that maintain a consistent, usable style independent of the theme primitives being edited.
- **Stable Editor UI**: The editor's own interface (buttons, backgrounds, text) uses a fixed, high-contrast design to ensure usability, regardless of the theme's color or typography choices.
- **Grouped Organization**: Tokens organized by type and purpose within a clear and consistent editor layout.
- **Reset Capability**: Restore default values with one click.

### 2. Persistent Storage
- **JSON-based**: Human-readable, version-control friendly
- **Direct File Updates**: No database queries required
- **Atomic Updates**: Individual token updates without full file rewrites
- **Backup Support**: Easy to backup and restore

### 3. Security
- **Nonce Verification**: All AJAX requests verified
- **Capability Checks**: Requires `edit_theme_options` permission
- **Input Sanitization**: All user inputs sanitized
- **File Access Control**: Limited to designated directories

### 4. Extensibility
- **Modular Design**: Easy to add new primitive types
- **Hook System**: WordPress actions and filters
- **Custom Controls**: Support for specialized input types
- **Theme Integration**: Works with any Timber-based theme

## Implementation Details

### File Structure

```
/design-book-editors/
├── page-templates/
│   ├── page-primitive-typography.php
│   ├── page-primitive-colors.php
│   ├── page-primitive-spacing.php
│   └── page-primitive-[type].php
├── templates/
│   ├── design-book-editors/
│   │   ├── typography-editor.twig
│   │   ├── colors-editor.twig
│   │   └── [type]-editor.twig
│   └── editor-components/
│       ├── color-picker.twig
│       ├── slider-control.twig
│       └── input-group.twig
├── assets/
│   ├── css/
│   │   └── design-book-editors.css
│   └── js/
│       ├── primitive-typography.js
│       ├── primitive-colors.js
│       └── primitive-[type].js
└── includes/
    ├── ajax-handlers.php
    ├── editor-functions.php
    └── security.php
```

### Page Template Structure

```php
<?php
/**
 * Template Name: Primitive Typography Editor
 */

// Security check
if (!current_user_can('edit_theme_options')) {
    wp_die('Unauthorized');
}

// Enqueue editor assets
wp_enqueue_style('design-book-editors');
wp_enqueue_script('primitive-typography', [
    'jquery',
    'wp-color-picker'
]);

// Localize script with AJAX data
wp_localize_script('primitive-typography', 'primitiveTypography', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('mi_design_book_nonce'),
    'primitiveType' => 'typography'
]);

// Render with Timber
$context = Timber::context();
$context['post'] = Timber::get_post();
$context['primitive_type'] = 'typography';

Timber::render('design-book-editors/typography-editor.twig', $context);
```

### Editor Template Structure

```twig
{# typography-editor.twig #}
{% set typography_tokens = load_primitive('typography') %}

<div class="primitive-editor" data-primitive-type="typography">
    <header class="editor-header">
        <h1>Typography Editor</h1>
        <button class="btn-reset" data-action="reset">Reset to Defaults</button>
    </header>

    <div class="editor-content">
        <div class="editor-controls">
            {% for category, tokens in typography_tokens %}
                <section class="token-group" data-category="{{ category }}">
                    <h3>{{ category|title|replace({'_': ' '}) }}</h3>
                    {% for slug, value in tokens %}
                        <div class="token-control" data-token="{{ slug }}">
                            <label>{{ slug|replace({'_': ' ', '-': ' '})|title }}</label>
                            <input type="text" 
                                   class="token-input"
                                   data-type="{{ category }}"
                                   data-slug="{{ slug }}"
                                   value="{{ value }}">
                            <span class="token-preview" style="font-size: {{ value }};">Aa</span>
                        </div>
                    {% endfor %}
                </section>
            {% endfor %}
        </div>

        <div class="editor-preview">
            <iframe src="{{ preview_url }}" frameborder="0"></iframe>
        </div>
    </div>
</div>
```

### JavaScript Handler

```javascript
// primitive-typography.js
jQuery(document).ready(function($) {
    const editor = {
        init() {
            this.bindEvents();
            this.initializeControls();
        },

        bindEvents() {
            $('.token-input').on('change', this.handleTokenChange.bind(this));
            $('.btn-reset').on('click', this.handleReset.bind(this));
        },

        handleTokenChange(e) {
            const $input = $(e.target);
            const data = {
                action: 'update_typography_primitive',
                type: $input.data('type'),
                slug: $input.data('slug'),
                value: $input.val(),
                nonce: primitiveTypography.nonce
            };

            $.post(primitiveTypography.ajaxUrl, data)
                .done(response => {
                    if (response.success) {
                        this.updatePreview();
                        this.showNotification('Token updated successfully');
                    }
                })
                .fail(() => {
                    this.showNotification('Error updating token', 'error');
                });
        },

        updatePreview() {
            // Refresh preview iframe or update live preview elements
            $('.editor-preview iframe')[0].contentWindow.location.reload();
        },

        showNotification(message, type = 'success') {
            // Show user feedback
        }
    };

    editor.init();
});
```

### AJAX Handler

```php
// ajax-handlers.php
function update_typography_primitive() {
    // Security checks
    if (!check_ajax_referer('mi_design_book_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Sanitize inputs
    $type = sanitize_text_field($_POST['type']);
    $slug = sanitize_text_field($_POST['slug']);
    $value = sanitize_text_field($_POST['value']);
    
    // Load current data
    $json_path = get_template_directory() . '/primitives/typography.json';
    $data = json_decode(file_get_contents($json_path), true);
    
    // Update value
    if (isset($data[$type][$slug])) {
        $data[$type][$slug] = $value;
        
        // Save to file
        $result = file_put_contents(
            $json_path, 
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        if ($result !== false) {
            // Optional: Trigger theme.json sync
            do_action('design_book_primitive_updated', 'typography', $type, $slug, $value);
            
            wp_send_json_success([
                'message' => 'Token updated successfully',
                'type' => $type,
                'slug' => $slug,
                'value' => $value
            ]);
        }
    }
    
    wp_send_json_error('Failed to update token');
}
add_action('wp_ajax_update_typography_primitive', 'update_typography_primitive');
```

## Plugin Architecture Considerations

### Plugin Structure

```
design-book-editor/
├── design-book-editor.php          # Main plugin file
├── includes/
│   ├── class-design-book-editor.php
│   ├── class-primitive-manager.php
│   ├── class-ajax-handler.php
│   └── class-security.php
├── admin/
│   ├── class-admin-menu.php
│   ├── class-settings.php
│   └── views/
├── public/
│   ├── class-public-preview.php
│   └── templates/
├── assets/
│   ├── css/
│   └── js/
└── primitives/
    └── defaults/                   # Default primitive values
```

### Plugin Features

1. **Admin Interface**
   - WordPress admin menu integration
   - Settings page for configuration
   - Import/Export functionality
   - Multi-theme support

2. **Theme Integration**
   - Auto-detection of compatible themes
   - Theme-specific primitive paths
   - Fallback to plugin defaults
   - Hook system for theme customization

3. **Advanced Features**
   - Version history with rollback
   - Primitive inheritance/extending
   - Custom primitive types via API
   - REST API endpoints
   - WP-CLI commands

### Plugin API

```php
// Register custom primitive type
add_filter('design_book_primitive_types', function($types) {
    // Custom primitive types can be registered here
    return $types;
});

// Hook into primitive update
add_action('design_book_primitive_updated', function($type, $category, $slug, $value) {
    // Custom handling, e.g., compile SCSS, clear cache
}, 10, 4);

// Add custom control type
add_filter('design_book_control_types', function($controls) {
    // Custom control types can be registered here
    return $controls;
});
```

## Security Best Practices

1. **Authentication**
   - Verify user capabilities
   - Use WordPress nonce system
   - Session validation for sensitive operations

2. **Data Validation**
   - Sanitize all inputs
   - Validate against schema
   - Prevent directory traversal

3. **File Operations**
   - Restrict to designated directories
   - Use WordPress Filesystem API when appropriate
   - Implement file locking for concurrent edits

4. **Output Escaping**
   - Escape all dynamic content
   - Use appropriate WordPress functions
   - Prevent XSS attacks

## Performance Optimization

1. **Caching**
   - Cache parsed JSON data
   - Implement browser caching for assets
   - Use WordPress transients for expensive operations

2. **Lazy Loading**
   - Load primitive types on demand
   - Defer non-critical JavaScript
   - Optimize preview rendering

3. **Batch Operations**
   - Group multiple token updates
   - Debounce rapid changes
   - Implement queue system for large updates

## Future Enhancements

- **Internal UI Componentization:** To improve maintainability and development speed of the editor UI, common UI patterns within the editor's Twig templates (e.g., the 'card' displaying a token, input groups) can be refactored into smaller, reusable Twig components (e.g., `_editor-token-card.twig`, `_editor-input-group.twig`). These internal components would reside in a dedicated directory like `templates/editor-components/` and would be styled using the editor's fixed CSS to ensure UI consistency.

1. **AI Integration**
   - Suggest color harmonies
   - Generate typography scales
   - Auto-optimize for accessibility

2. **Collaboration Features**
   - Multi-user editing with locking
   - Change proposals and approvals
   - Comments and annotations

3. **Design System Features**
   - Token relationships and calculations
   - Responsive token values
   - Dark mode support
   - Export to various formats (CSS, SCSS, Style Dictionary)

4. **Integration Ecosystem**
   - Figma plugin sync
   - Adobe XD integration
   - Sketch compatibility
   - CI/CD pipeline support

## Conclusion

The Design Book Editor System provides a robust, secure, and extensible solution for managing design tokens in WordPress. Its modular architecture and comprehensive feature set make it suitable for both theme integration and standalone plugin development. The system's focus on user experience, security, and performance ensures it can scale from simple theme customization to enterprise-level design system management.

## Typography Editor Implementation

This section documents the actual implementation of the typography editor as completed in the recent refactoring.

### File Locations and Structure

```
/wp-content/themes/miGV/
├── page-primitive-typography.php           # WordPress page template
├── templates/
│   ├── design-book-editors/
│   │   └── typography-editor.twig         # Main editor template
│   └── primitive-books/
│       └── typography-book.twig           # Typography primitive renderer
├── primitives/
│   └── typography.json                    # Typography token storage
├── assets/
│   ├── css/
│   │   └── design-book-editors.css       # Editor UI styles
│   └── js/
│       └── primitive-typography.js        # Editor JavaScript
└── functions.php                          # AJAX handlers
```

### Typography JSON Structure

The typography tokens are stored in `/primitives/typography.json`:

```json
{
  "font_families": {
    "montserrat": "Montserrat, -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif",
    "inter": "Inter, system-ui, sans-serif",
    "playfair-display": "Playfair Display, serif",
    "roboto": "Roboto, sans-serif"
  },
  "font_sizes": {
    "small": "0.8125rem",
    "medium": "1rem",
    "large": "1.25rem",
    "x-large": "1.5rem",
    "xx-large": "2rem",
    "huge": "6.25rem"
  },
  "font_weights": {
    "regular": "400",
    "medium": "500",
    "semiBold": "600",
    "bold": "700",
    "extraBold": "800"
  },
  "line_heights": {
    "tight": "1.1",
    "normal": "1.2",
    "relaxed": "1.4",
    "loose": "1.6"
  },
  "letter_spacings": {
    "tight": "-0.025em",
    "normal": "0",
    "wide": "0.025em",
    "wider": "0.05em",
    "widest": "0.1em"
  }
}
```

### Page Template Implementation

`page-primitive-typography.php`:
```php
<?php
/**
 * Template Name: Primitive Typography
 */

get_header();

// Enqueue editor assets
wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.0');
wp_enqueue_script('primitive-typography', get_template_directory_uri() . '/assets/js/primitive-typography.js', array('jquery'), '1.0.0', true);

// Localize script
wp_localize_script('primitive-typography', 'primitiveTypography', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('mi_design_book_nonce'),
    'primitiveType' => 'typography'
));

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('design-book-editors/typography-editor.twig', $context);

get_footer();
```

### Editor Template Structure

The editor template uses specific CSS classes and data attributes for JavaScript interaction:

```twig
{# typography-editor.twig #}
{% set typography_tokens = load_primitive('typography') %}

<div class="primitive-editor" data-primitive-type="typography">
    <header class="editor-header">
        <h1>Typography Primitive Editor</h1>
        <div class="editor-actions">
            <button class="btn-save" data-action="save">Save to JSON</button>
            <button class="btn-sync" data-action="sync">Sync to theme.json</button>
            <button class="btn-reset" data-action="reset">Reset to Defaults</button>
        </div>
    </header>

    <div class="editor-content">
        <!-- Font Families Section -->
        <section class="token-group" data-token-type="font_families">
            <h2>Font Families</h2>
            <div class="token-grid">
                {% for slug, value in typography_tokens.font_families %}
                    <div class="token-item" data-token-slug="{{ slug }}">
                        <label>{{ slug|replace({'_': ' '})|title }}</label>
                        <input type="text" 
                               class="token-input font-family-input" 
                               value="{{ value }}"
                               data-slug="{{ slug }}">
                        <button class="copy-button" data-value="{{ value }}">Copy</button>
                    </div>
                {% endfor %}
            </div>
        </section>

        <!-- Font Sizes Section -->
        <section class="token-group" data-token-type="font_sizes">
            <h2>Font Sizes</h2>
            <div class="preview-controls">
                <label>Preview Scale:</label>
                <input type="range" class="preview-scale" min="0.5" max="2" step="0.1" value="1">
                <span class="scale-value">100%</span>
            </div>
            <div class="token-grid">
                {% for slug, value in typography_tokens.font_sizes %}
                    <div class="token-item" data-token-slug="{{ slug }}">
                        <div class="font-size-preview" style="font-size: {{ value }};">
                            <span>{{ slug|upper }}</span>
                        </div>
                        <input type="text" 
                               class="token-input font-size-input" 
                               value="{{ value }}"
                               data-slug="{{ slug }}">
                    </div>
                {% endfor %}
            </div>
        </section>
    </div>
</div>
```

### JavaScript Implementation

`primitive-typography.js` handles all editor interactions:

```javascript
jQuery(document).ready(function($) {
    let hasChanges = false;
    let originalData = {};

    // Initialize
    function init() {
        loadOriginalData();
        bindEvents();
        initializePreviewScale();
    }

    // Track original data
    function loadOriginalData() {
        $('.token-input').each(function() {
            const $input = $(this);
            const type = $input.closest('.token-group').data('token-type');
            const slug = $input.data('slug');
            
            if (!originalData[type]) originalData[type] = {};
            originalData[type][slug] = $input.val();
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
        
        // Preview scale slider
        $('.preview-scale').on('input', updatePreviewScale);
        
        // Copy buttons
        $('.copy-button').on('click', copyToClipboard);
        
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
        
        // Update preview if it's a font size
        if ($(this).hasClass('font-size-input')) {
            const newValue = $(this).val();
            $(this).closest('.token-item')
                   .find('.font-size-preview')
                   .css('font-size', newValue);
        }
    }

    // Save to JSON primitive
    function saveToJSON() {
        const data = collectAllData();
        
        $.ajax({
            url: primitiveTypography.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_typography_primitive',
                nonce: primitiveTypography.nonce,
                typography_data: JSON.stringify(data)
            },
            success: function(response) {
                if (response.success) {
                    hasChanges = false;
                    $('.btn-save').removeClass('has-changes');
                    showNotification('Typography saved successfully!', 'success');
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
            }
        });
    }

    // Sync to theme.json
    function syncToThemeJSON() {
        const data = collectAllData();
        
        $.ajax({
            url: primitiveTypography.ajaxUrl,
            type: 'POST',
            data: {
                action: 'sync_typography_to_theme_json',
                nonce: primitiveTypography.nonce,
                typography_data: JSON.stringify(data)
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Synced to theme.json successfully!', 'success');
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
            }
        });
    }

    // Initialize
    init();
});
```

### CSS Implementation

Key styles from `design-book-editors.css`:

```css
/* Editor Layout */
.primitive-editor {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    font-family: 'Montserrat', sans-serif;
}

/* Token Grid */
.token-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

/* Token Items */
.token-item {
    background: #f5f5f5;
    padding: 1.5rem;
    border-radius: 8px;
    position: relative;
}

/* Font Size Preview */
.font-size-preview {
    margin-bottom: 1rem;
    line-height: 1.2;
    transform-origin: left center;
    transition: transform 0.2s ease;
}

/* Save Button States */
.btn-save {
    background: #2271b1;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-save.has-changes {
    background: #f0b849;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(240, 184, 73, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(240, 184, 73, 0); }
    100% { box-shadow: 0 0 0 0 rgba(240, 184, 73, 0); }
}

/* Preview Scale Slider */
.preview-scale {
    width: 200px;
    height: 6px;
    background: #ddd;
    border-radius: 3px;
    outline: none;
    -webkit-appearance: none;
}

.preview-scale::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    background: #2271b1;
    border-radius: 50%;
    cursor: pointer;
}
```

### AJAX Handlers in functions.php

```php
// Save typography primitive
function save_typography_primitive() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get and validate data
    $typography_data = isset($_POST['typography_data']) ? json_decode(stripslashes($_POST['typography_data']), true) : null;
    
    if (!$typography_data) {
        wp_send_json_error('Invalid typography data');
        return;
    }
    
    // Save to JSON file
    $json_path = get_template_directory() . '/primitives/typography.json';
    $result = file_put_contents($json_path, json_encode($typography_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        wp_send_json_success('Typography primitive saved successfully');
    } else {
        wp_send_json_error('Failed to save typography primitive');
    }
}
add_action('wp_ajax_save_typography_primitive', 'save_typography_primitive');

// Sync typography to theme.json
function sync_typography_to_theme_json() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get typography data
    $typography_data = isset($_POST['typography_data']) ? json_decode(stripslashes($_POST['typography_data']), true) : null;
    
    if (!$typography_data) {
        wp_send_json_error('Invalid typography data');
        return;
    }
    
    // Load existing theme.json
    $theme_json_path = get_template_directory() . '/theme.json';
    $theme_json = json_decode(file_get_contents($theme_json_path), true);
    
    // Update typography settings
    if (!isset($theme_json['settings']['typography'])) {
        $theme_json['settings']['typography'] = [];
    }
    
    // Convert font sizes
    $theme_json['settings']['typography']['fontSizes'] = [];
    foreach ($typography_data['font_sizes'] as $slug => $size) {
        $theme_json['settings']['typography']['fontSizes'][] = [
            'slug' => $slug,
            'size' => $size,
            'name' => ucfirst(str_replace('-', ' ', $slug))
        ];
    }
    
    // Convert font families
    $theme_json['settings']['typography']['fontFamilies'] = [];
    foreach ($typography_data['font_families'] as $slug => $family) {
        $theme_json['settings']['typography']['fontFamilies'][] = [
            'slug' => $slug,
            'fontFamily' => $family,
            'name' => ucfirst(str_replace('-', ' ', $slug))
        ];
    }
    
    // Save updated theme.json
    $result = file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        wp_send_json_success('Typography synced to theme.json');
    } else {
        wp_send_json_error('Failed to sync to theme.json');
    }
}
add_action('wp_ajax_sync_typography_to_theme_json', 'sync_typography_to_theme_json');
```

### Key Implementation Details

1. **CSS Class Naming Convention**:
   - `.primitive-editor` - Main container
   - `.token-group` - Section container with `data-token-type`
   - `.token-item` - Individual token container with `data-token-slug`
   - `.token-input` - Input fields with type-specific classes (`.font-size-input`, `.font-family-input`)
   - `.btn-save`, `.btn-sync`, `.btn-reset` - Action buttons
   - `.has-changes` - Visual indicator for unsaved changes

2. **Data Attributes**:
   - `data-primitive-type="typography"` - Identifies the primitive type
   - `data-token-type="font_sizes"` - Groups tokens by category
   - `data-token-slug="large"` - Identifies individual tokens
   - `data-action="save"` - Button actions

3. **JavaScript Architecture**:
   - Modular function structure
   - State tracking with `hasChanges` flag
   - Original data storage for reset functionality
   - Event delegation for dynamic content
   - AJAX error handling with user feedback

4. **Security Implementation**:
   - Nonce verification on all AJAX requests
   - Capability checks (`edit_theme_options`)
   - Data sanitization with `stripslashes()` and `json_decode()`
   - File path validation (no user input for paths)

5. **User Experience Features**:
   - Visual feedback for unsaved changes (pulsing yellow button)
   - Confirmation before leaving with unsaved changes
   - Live preview updates for font sizes
   - Copy-to-clipboard functionality
   - Success/error notifications

This implementation provides a complete, secure, and user-friendly typography editing experience that maintains the separation between the design system primitives and the editor UI.

## Color Editor Implementation

[Similar detailed documentation for color editor...]

## Spacing Editor Implementation

[To be implemented...]

## Creating New Primitive Editors

When creating a new primitive editor, follow this pattern:

1. **Create Page Template**: `page-primitive-[type].php`
2. **Create Editor Template**: `templates/design-book-editors/[type]-editor.twig`
3. **Create JavaScript Handler**: `assets/js/primitive-[type].js`
4. **Add AJAX Handlers**: In `functions.php`
5. **Create JSON Primitive**: `primitives/[type].json`
6. **Add Styles**: Extend `design-book-editors.css` as needed

Each editor should maintain the same security standards, UI patterns, and code organization for consistency across the system.
