# Design Book System - Technical Documentation

## Overview

The Design Book System is a JSON-based design token management system that implements atomic design principles for the Middleton Getaways WordPress theme. This document provides technical implementation details for developers.

## System Architecture

### Data Flow

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│ Primitive JSON  │────▶│ Primitive Books  │────▶│   Components    │
│     Files       │     │  (Twig Templates)│     │ (Twig/HTML/CSS) │
└─────────────────┘     └──────────────────┘     └─────────────────┘
         │                        │                        │
         │                        │                        │
         ▼                        ▼                        ▼
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│ Design Book     │     │  CSS Variables   │     │   Rendered UI   │
│    Editors      │     │   (wp_head)      │     │                 │
└─────────────────┘     └──────────────────┘     └─────────────────┘
```

### Core Components

#### 1. Primitive JSON Files
- **Location**: `/wp-content/themes/miGV/primitives/`
- **Format**: Standard JSON with nested object structures
- **Purpose**: Single source of truth for all design tokens
- **Access**: Read/write via PHP, read-only via Twig

#### 2. Primitive Books (Twig Templates)
- **Location**: `/wp-content/themes/miGV/templates/primitive-books/`
- **Purpose**: Convert primitive values to CSS properties
- **Usage**: Included in component templates with parameters
- **Features**: Token lookup with fallback values

#### 3. Design Book Editors
- **Location**: `/wp-content/themes/miGV/templates/design-book-editors/`
- **Purpose**: Visual interface for editing primitives
- **Technology**: Twig templates with jQuery/AJAX
- **Security**: Nonce verification and capability checks

#### 4. JavaScript Handlers
- **Location**: `/wp-content/themes/miGV/assets/js/primitive-*.js`
- **Purpose**: Handle editor interactions and AJAX calls
- **Features**: Live preview, validation, save/sync operations

#### 5. PHP Infrastructure
- **Core Functions**: `/wp-content/themes/miGV/inc/design-system-core.php`
- **AJAX Handlers**: Defined in `functions.php`
- **Page Templates**: `/wp-content/themes/miGV/page-primitive-*.php`

## Implementation Details

### Loading Primitives in Twig

The `load_primitive()` function is registered as a Twig function:

```php
// In functions.php
add_filter('timber/twig', function($twig) {
    $twig->addFunction(new \Twig\TwigFunction('load_primitive', function($name) {
        $json_path = get_template_directory() . "/primitives/{$name}.json";
        
        if (!file_exists($json_path)) {
            return null;
        }
        
        $json_content = file_get_contents($json_path);
        return json_decode($json_content, true);
    }));
    
    return $twig;
});
```

### CSS Variable Generation

The `migv_generate_primitive_css_variables()` function converts JSON to CSS:

```php
function migv_generate_primitive_css_variables() {
    $primitive_files = [
        'colors'     => get_template_directory() . '/primitives/colors.json',
        'typography' => get_template_directory() . '/primitives/typography.json',
        'spacing'    => get_template_directory() . '/primitives/spacing.json',
        'borders'    => get_template_directory() . '/primitives/borders.json',
    ];

    $css_variables = [];

    foreach ($primitive_files as $prefix => $file_path) {
        if (!file_exists($file_path)) {
            continue;
        }

        $data = json_decode(file_get_contents($file_path), true);
        
        // Recursively generate CSS variables
        $generate_vars_recursive = function($array, $parent_key = '') use (&$generate_vars_recursive, &$css_variables, $prefix) {
            foreach ($array as $key => $value) {
                $css_var_name = $parent_key ? "--{$prefix}-{$parent_key}-{$key}" : "--{$prefix}-{$key}";
                $css_var_name = str_replace('_', '-', $css_var_name);

                if (is_array($value)) {
                    $generate_vars_recursive($value, $parent_key ? "{$parent_key}-{$key}" : $key);
                } else {
                    $css_variables[] = esc_attr($css_var_name) . ': ' . esc_attr($value) . ';';
                }
            }
        };

        $generate_vars_recursive($data);
    }

    return ":root {\n    " . implode("\n    ", $css_variables) . "\n}\n";
}
```

### AJAX Handler Pattern

Standard pattern for primitive AJAX handlers:

```php
function save_{primitive}_primitive() {
    // 1. Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // 2. Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // 3. Validate data
    $data = json_decode(stripslashes($_POST['{primitive}_data']), true);
    if (!$data) {
        wp_send_json_error('Invalid data');
        return;
    }
    
    // 4. Save to JSON
    $json_path = get_template_directory() . '/primitives/{primitive}.json';
    $result = file_put_contents($json_path, json_encode($data, JSON_PRETTY_PRINT));
    
    // 5. Return response
    if ($result !== false) {
        wp_send_json_success('Saved successfully');
    } else {
        wp_send_json_error('Failed to save');
    }
}
add_action('wp_ajax_save_{primitive}_primitive', 'save_{primitive}_primitive');
```

### Primitive Book Pattern

Standard template structure for primitive books:

```twig
{#
  {Primitive} Primitive Book
  
  Usage:
  {% include 'primitive-books/{primitive}-book.twig' with {
    parameter: 'value'
  } %}
#}

{# Load primitive tokens from JSON #}
{% set tokens = load_primitive('{primitive}') %}

{# Apply styles based on parameters #}
{% if parameter %}
  {% set value = tokens.category[parameter] ?? parameter %}
  css-property: {{ value }};
{% endif %}
```

## Security Considerations

### File Access
- JSON files are only writable via authenticated AJAX calls
- File paths are validated to prevent directory traversal
- Backup files are created before modifications

### User Permissions
- Only users with `edit_theme_options` capability can save
- Nonce verification on all AJAX requests
- Sanitization of all input data

### Data Validation
- JSON structure validation before saving
- Type checking for expected data formats
- Fallback values for missing tokens

## Performance Optimization

### Caching Strategy
1. **Server-side**: Primitive values cached in PHP variables
2. **Client-side**: CSS variables cached by browser
3. **Twig caching**: Complex components use Twig cache

### Loading Optimization
- Primitives loaded once per request
- CSS variables generated on `wp_head` action
- Editor assets only loaded on editor pages

### Best Practices
1. Minimize primitive file reads
2. Use CSS variables over inline styles
3. Cache primitive lookups in loops
4. Lazy load editor functionality

## API Reference

### PHP Functions

#### `load_primitive($name)`
Loads a primitive JSON file and returns its contents as an array.

**Parameters:**
- `$name` (string): Name of the primitive file (without .json)

**Returns:**
- Array of primitive values or null if file not found

#### `migv_generate_primitive_css_variables()`
Generates CSS custom properties from all primitive files.

**Returns:**
- String containing CSS variables in :root block

### JavaScript Functions

#### `collectAllData()`
Collects all token values from editor inputs.

**Returns:**
- Object containing all primitive data

#### `saveToJSON()`
Saves primitive data to JSON file via AJAX.

#### `syncToThemeJSON()`
Syncs primitive data to WordPress theme.json.

#### `updateLivePreview()`
Updates the live preview based on current selections.

### Twig Functions

#### `load_primitive(name)`
Loads primitive data from JSON file.

**Parameters:**
- `name`: Primitive file name

**Returns:**
- Associative array of primitive values

### AJAX Actions

#### `save_{primitive}_primitive`
Saves primitive data to JSON file.

**POST Parameters:**
- `nonce`: Security nonce
- `{primitive}_data`: JSON string of primitive data

#### `sync_{primitive}_to_theme_json`
Syncs primitive data to theme.json.

**POST Parameters:**
- `nonce`: Security nonce
- `{primitive}_data`: JSON string of primitive data

## Extending the System

### Adding New Primitives

1. **Create JSON structure**:
```json
{
  "category": {
    "token": "value"
  }
}
```

2. **Create primitive book**:
```twig
{% set tokens = load_primitive('new-primitive') %}
{% if parameter %}
  property: {{ tokens.category[parameter] ?? parameter }};
{% endif %}
```

3. **Create editor template** with form inputs and preview

4. **Add JavaScript handler** following the established pattern

5. **Register AJAX handlers** in functions.php

6. **Update CSS generation** in design-system-core.php

### Creating Custom Token Types

1. Define JSON structure
2. Implement value transformation logic
3. Create specialized preview components
4. Add validation rules
5. Document usage patterns

## Integration Points

### WordPress Integration
- **theme.json**: Optional sync for Gutenberg compatibility
- **Customizer**: Can read primitive values
- **Block Editor**: CSS variables available in editor

### External Tool Integration
- **Build Process**: Can read/transform JSON files
- **CI/CD**: Validate JSON structure in pipeline
- **Design Tools**: Export/import capabilities (planned)

## Troubleshooting Guide

### Debug Mode
Enable debug logging in WordPress:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| "Invalid nonce" | Session expired | Refresh page and retry |
| "Insufficient permissions" | User lacks capability | Check user role |
| "Invalid JSON" | Syntax error in data | Validate JSON structure |
| "File not found" | Missing primitive file | Check file exists |
| "Failed to save" | Write permissions | Check directory permissions |

### Performance Profiling

Use these hooks to measure performance:
```php
add_action('mi_before_primitive_load', 'start_timer');
add_action('mi_after_primitive_load', 'end_timer');
```

## Migration Guide

### From Hardcoded Values
1. Identify all hardcoded design values
2. Create appropriate primitive tokens
3. Replace hardcoded values with primitive references
4. Test thoroughly

### From CSS Variables
1. Map existing variables to primitive structure
2. Update variable names to match convention
3. Replace var() references
4. Update documentation

## Version History

### v1.0.0 (Current)
- Initial implementation
- Colors, Typography, Spacing, Borders primitives
- Visual editors for all primitives
- CSS variable generation
- theme.json sync capability

### Planned Features
- Shadow primitives
- Animation tokens
- Validation system
- Import/Export
- Version control integration
- Multi-theme support

## Resources

### Internal Documentation
- [Design Book Overview](../DesignBook/DESIGN-BOOK-OVERVIEW.md)
- [Theme Structure](./VILLA-FILE-STRUCTURE-OVERVIEW.md)
- [Development Roadmap](../DesignBook/Roadmap)

### External Resources
- [Atomic Design Principles](https://atomicdesign.bradfrost.com/)
- [Design Tokens W3C](https://www.w3.org/community/design-tokens/)
- [WordPress Theme JSON](https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-json/)

## Support

For questions or issues:
1. Check troubleshooting guide
2. Review error logs
3. Consult team documentation
4. Contact development team

---

*Last Updated: [Current Date]*
*Version: 1.0.0*
