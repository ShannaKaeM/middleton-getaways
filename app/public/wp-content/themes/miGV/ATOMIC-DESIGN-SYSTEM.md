# miGV Atomic Design System

## Implementation Status

### Completed
- **Color Primitive Editor** (`/design-book/colors/`) - Fully functional with HSL/CMYK sliders, live editing, and theme.json sync
- **Color Cleanup & Enhancement** - Removed duplicate colors, added CMYK support, fixed lightness scaling
- **Design Book Router** - Dynamic routing system for all design book sections
- **CSS Architecture Cleanup** - Separated concerns between design-book.css and primitive-editor.css

### In Progress
- Typography Primitive Editor
- Spacing Primitive Editor
- Element Library
- Component Library

### Planned
- Section Templates
- Advanced Component Builder
- Import/Export Functionality
- Design Token Documentation Generator

## Overview

The miGV theme implements a pure atomic design system where all styling comes from `theme.json` and is consumed through Twig templates. This ensures complete consistency and maintainability.

## Architecture

### 1. Core Principles

- **Single Source of Truth**: All design tokens are defined in `theme.json`
- **No External CSS**: Component styles are defined inline using CSS custom properties from theme.json
- **Bidirectional Sync**: Changes in the design book editor sync back to theme.json
- **Atomic Hierarchy**: Primitives → Elements → Components → Sections

### 2. File Structure

```
miGV/
├── theme.json                    # All design tokens
├── style.css                     # Only WordPress required styles
├── templates/
│   ├── primitives/              # Atomic design tokens editors
│   │   └── colors-editor.twig   # Color primitive editor
│   ├── primitive-books/         # Token display templates
│   ├── element-books/          # Small UI elements
│   ├── component-books/        # Larger components
│   └── section-books/          # Page sections
├── assets/
│   ├── css/
│   │   ├── design-book.css     # General design book layout
│   │   └── primitive-editor.css # Editor-specific UI (sliders, tabs)
│   └── js/
│       ├── design-book.js      # General design book functionality
│       └── primitive-colors.js  # Color editor sync functionality
├── inc/
│   └── design-book-router.php  # Dynamic routing for design book
└── functions.php               # Theme setup and sync handlers
```

### 3. Design Tokens in theme.json

All design tokens are defined in `theme.json`:

```json
{
  "settings": {
    "color": {
      "palette": [
        {
          "slug": "primary",
          "color": "#1a73e8",
          "name": "Primary"
        }
      ]
    },
    "typography": {
      "fontSizes": [
        {
          "slug": "small",
          "size": "0.875rem",
          "name": "Small"
        }
      ]
    },
    "spacing": {
      "spacingSizes": [
        {
          "slug": "20",
          "size": "0.5rem",
          "name": "Small"
        }
      ]
    }
  }
}
```

### 4. Using Tokens in Twig

Primitives reference theme.json tokens directly:

```twig
{# typography-book.twig #}
<div class="typography-sample" 
     style="font-size: var(--wp--preset--font-size--{{ size.slug }});"
     data-size-var="--wp--preset--font-size--{{ size.slug }}"
     data-size-slug="{{ size.slug }}">
    {{ size.name }}
</div>
```

### 5. Bidirectional Sync

The design book editor allows live editing of tokens that sync back to theme.json:

1. **Color Editing**: HSL and CMYK sliders with live preview
2. **Typography Editing**: Adjust font sizes in the typography section
3. **Spacing Editing**: Modify spacing values
4. **Custom Properties**: Edit any custom token

Changes are saved via AJAX to `theme.json` and immediately reflected in the UI.

## Implementation Guide

### Creating Design Page Templates

To create a new primitive editor page (like color-book, typography-book, etc.), follow these steps:

#### 1. Create the Page Template PHP File

Create a file like `page-primitive-{name}.php`:

```php
<?php
/**
 * Template Name: Primitive {Name}
 */

// Get Timber context
$context = Timber::context();

// Load theme.json data
$theme_json_path = get_template_directory() . '/theme.json';
$theme_json = json_decode(file_get_contents($theme_json_path), true);

// Extract specific tokens (example for colors)
$colors = $theme_json['settings']['color']['palette'] ?? [];

// Group data as needed
$context['color_groups'] = [
    'primary' => array_filter($colors, function($color) {
        return strpos($color['slug'], 'primary') !== false;
    }),
    // ... more groups
];

// Render the Twig template
Timber::render('primitives/{name}-editor.twig', $context);
```

#### 2. Create the Twig Template

Create `templates/primitives/{name}-editor.twig`:

```twig
{% extends "base.twig" %}

{% block content %}
<div class="primitive-editor">
    <header class="primitive-header">
        <div class="primitive-header-content">
            <h1 class="primitive-title">{{ title }}</h1>
            <p class="primitive-description">{{ description }}</p>
        </div>
        <div class="primitive-header-actions">
            <button class="btn-icon" id="export-tokens">
                <i class="dashicons dashicons-download"></i>
            </button>
            <button class="btn-icon" id="reset-defaults">
                <i class="dashicons dashicons-image-rotate"></i>
            </button>
        </div>
    </header>
    
    <div class="primitive-content">
        {# Your editor content here #}
    </div>
</div>
{% endblock %}
```

#### 3. Add JavaScript for Interactivity

Create `assets/js/primitive-{name}.js`:

```javascript
jQuery(document).ready(function($) {
    // Initialize your editor
    
    // Save changes via AJAX
    function saveToThemeJson(tokenType, slug, value) {
        $.ajax({
            url: primitiveAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'mi_sync_primitive_to_theme_json',
                nonce: primitiveAjax.nonce,
                token_type: tokenType,
                slug: slug,
                value: value
            },
            success: function(response) {
                if (response.success) {
                    // Update UI
                    showNotification('Saved successfully');
                }
            }
        });
    }
});
```

#### 4. Enqueue Assets

Add to your page template or functions.php:

```php
function enqueue_primitive_assets() {
    if (is_page_template('page-primitive-{name}.php')) {
        wp_enqueue_style(
            'primitive-editor',
            get_template_directory_uri() . '/assets/css/primitive-editor.css',
            [],
            filemtime(get_template_directory() . '/assets/css/primitive-editor.css')
        );
        
        wp_enqueue_script(
            'primitive-{name}',
            get_template_directory_uri() . '/assets/js/primitive-{name}.js',
            ['jquery'],
            filemtime(get_template_directory() . '/assets/js/primitive-{name}.js'),
            true
        );
        
        wp_localize_script('primitive-{name}', 'primitiveAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('primitive_editor_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_primitive_assets');
```

### Using the Design Book Router

The router provides a cleaner way to create design book sections without individual page templates:

#### 1. Router Structure

The router (`inc/design-book-router.php`) handles URLs like `/design-book/{section}/`:

```php
class VillaDesignBookRouter {
    public function __construct() {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_design_book_request']);
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^design-book/?$',
            'index.php?design_book=1',
            'top'
        );
        add_rewrite_rule(
            '^design-book/([^/]+)/?$',
            'index.php?design_book=1&design_section=$matches[1]',
            'top'
        );
    }
}
```

#### 2. Adding New Sections

To add a new section to the router:

1. Add to the `$valid_sections` array in the router
2. Create a data method like `get_{section}_data()`
3. Create the Twig template in `templates/primitives/{section}-editor.twig`
4. Add section-specific asset enqueuing if needed

### AJAX Handler Implementation

All AJAX handlers should follow this pattern in `functions.php`:

```php
// Handler for syncing primitives to theme.json
add_action('wp_ajax_mi_sync_primitive_to_theme_json', 'mi_sync_primitive_to_theme_json');
function mi_sync_primitive_to_theme_json() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'primitive_editor_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_die('Insufficient permissions');
    }
    
    // Get parameters
    $token_type = sanitize_text_field($_POST['token_type']);
    $slug = sanitize_text_field($_POST['slug']);
    $value = sanitize_text_field($_POST['value']);
    
    // Load theme.json
    $theme_json_path = get_template_directory() . '/theme.json';
    $theme_json = json_decode(file_get_contents($theme_json_path), true);
    
    // Update the appropriate section
    switch ($token_type) {
        case 'color':
            foreach ($theme_json['settings']['color']['palette'] as &$color) {
                if ($color['slug'] === $slug) {
                    $color['color'] = $value;
                    break;
                }
            }
            break;
        // Add more token types
    }
    
    // Save theme.json
    file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    // Return success
    wp_send_json_success(['message' => 'Token updated successfully']);
}
```

### Theme.json Token Structure

Understanding the token structure is crucial for implementation:

```json
{
  "version": 2,
  "settings": {
    "color": {
      "palette": [
        {
          "slug": "primary",
          "color": "#1a73e8",
          "name": "Primary"
        }
      ]
    },
    "typography": {
      "fontFamilies": [
        {
          "fontFamily": "Montserrat, sans-serif",
          "slug": "montserrat",
          "name": "Montserrat"
        }
      ],
      "fontSizes": [
        {
          "slug": "small",
          "size": "0.875rem",
          "name": "Small"
        }
      ]
    },
    "spacing": {
      "spacingSizes": [
        {
          "slug": "20",
          "size": "0.5rem",
          "name": "Small"
        }
      ]
    },
    "custom": {
      "layout": {
        "contentWidth": "1200px",
        "wideWidth": "1400px"
      }
    }
  }
}
```

### CSS Variable Mapping

Theme.json tokens map to CSS variables:

- **Colors**: `--wp--preset--color--{slug}`
- **Font Sizes**: `--wp--preset--font-size--{slug}`
- **Font Families**: `--wp--preset--font-family--{slug}`
- **Spacing**: `--wp--preset--spacing--{slug}`
- **Custom**: `--wp--custom--{path}--{to}--{value}`

### Security Considerations

1. **Always verify nonces** in AJAX handlers
2. **Check user capabilities** (`edit_theme_options`)
3. **Sanitize all inputs** before saving
4. **Validate theme.json structure** after modifications
5. **Keep backups** of theme.json before modifications

### Testing Your Implementation

1. **Create a test page** and assign your template
2. **Check browser console** for JavaScript errors
3. **Monitor Network tab** for AJAX requests
4. **Verify theme.json updates** after saving
5. **Test CSS variable generation** in browser DevTools

## Existing Implementations

### Color Primitive Editor

The color editor is the most complete implementation and serves as a reference:

**Files:**
- `page-primitive-colors.php` - Loads colors from theme.json and groups them
- `templates/primitives/colors-editor.twig` - UI with color cards, sliders, tabs
- `assets/js/primitive-colors.js` - Handles HSL/CMYK editing, live preview, sync
- `assets/css/primitive-editor.css` - Styles for cards, sliders, tabs

**Key Features:**
- Grouped color display (primary, secondary, neutral, base, extreme)
- HSL sliders with percentage-based lightness
- CMYK sliders with color-coded backgrounds
- Tab switching between HSL/CMYK modes
- Live hex input and color picker
- Opacity slider
- CSS variable preview with copy button
- AJAX sync to theme.json

### Design Book (Component Editor)

**Files:**
- `page-mi-design-book.php` - Main design book page
- `templates/design-book/*.twig` - Component templates
- `assets/js/design-book.js` - Interactive controls
- `inc/design-book-router.php` - Dynamic routing

**Features:**
- Visual component builder
- Live preview with controls
- Generated Twig code output
- Save/load component configurations
- Export as JSON

### Hero Book

**Files:**
- `page-hero-book.php` - Hero component editor
- `templates/hero-book/index.twig` - Editor interface
- `assets/js/hero-book.js` - Hero-specific controls

## Component Hierarchy Examples

### 1. Primitives (Atomic Level)

Primitives are the smallest units that directly map to theme.json:

```twig
{# Color Primitive #}
<div style="background-color: var(--wp--preset--color--primary);">
    Primary Color
</div>

{# Typography Primitive #}
<p style="font-size: var(--wp--preset--font-size--large);">
    Large Text
</p>

{# Spacing Primitive #}
<div style="padding: var(--wp--preset--spacing--40);">
    Spaced Content
</div>
```

### 2. Elements (Molecules)

Elements combine primitives into small, reusable components:

```twig
{# Button Element #}
<button style="
    background: var(--wp--preset--color--primary);
    color: var(--wp--preset--color--base-lightest);
    padding: var(--wp--preset--spacing--20) var(--wp--preset--spacing--40);
    border-radius: var(--wp--custom--border-radius--md);
    font-size: var(--wp--preset--font-size--medium);
    font-family: var(--wp--preset--font-family--montserrat);
    border: none;
    cursor: pointer;
">
    {{ button_text }}
</button>

{# Badge Element #}
<span style="
    background: var(--wp--preset--color--secondary-light);
    color: var(--wp--preset--color--secondary-dark);
    padding: var(--wp--preset--spacing--10) var(--wp--preset--spacing--20);
    border-radius: var(--wp--custom--border-radius--full);
    font-size: var(--wp--preset--font-size--small);
">
    {{ badge_text }}
</span>
```

### 3. Components (Organisms)

Components combine elements and primitives into larger UI pieces:

```twig
{# Card Component #}
<div class="card" style="
    background: var(--wp--preset--color--base-lightest);
    border-radius: var(--wp--custom--border-radius--lg);
    padding: var(--wp--preset--spacing--60);
    box-shadow: var(--wp--custom--shadow--md);
">
    {# Using primitives #}
    <h3 style="
        font-size: var(--wp--preset--font-size--x-large);
        color: var(--wp--preset--color--primary-dark);
        margin-bottom: var(--wp--preset--spacing--20);
    ">{{ card.title }}</h3>
    
    {# Using elements #}
    {% if card.badge %}
        {% include 'elements/badge.twig' with {badge_text: card.badge} %}
    {% endif %}
    
    <p style="
        color: var(--wp--preset--color--base-dark);
        line-height: 1.6;
        margin-bottom: var(--wp--preset--spacing--40);
    ">{{ card.content }}</p>
    
    {# Using elements #}
    {% include 'elements/button.twig' with {button_text: card.cta_text} %}
</div>
```

### 4. Sections (Templates)

Sections combine components into page-level layouts:

```twig
{# Hero Section #}
<section style="
    background: var(--wp--preset--color--primary);
    padding: var(--wp--preset--spacing--80) 0;
">
    <div style="
        max-width: var(--wp--custom--layout--content-width);
        margin: 0 auto;
        padding: 0 var(--wp--preset--spacing--40);
    ">
        {# Include components #}
        {% include 'components/hero-content.twig' %}
        
        {# Grid of cards #}
        <div style="
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--wp--preset--spacing--60);
            margin-top: var(--wp--preset--spacing--80);
        ">
            {% for card in cards %}
                {% include 'components/card.twig' with {card: card} %}
            {% endfor %}
        </div>
    </div>
</section>
```

## Common Patterns

### 1. Token Usage Pattern

Always use CSS custom properties from theme.json:

```twig
{# ✅ GOOD - Uses tokens #}
<div style="color: var(--wp--preset--color--primary);">

{# ❌ BAD - Hardcoded value #}
<div style="color: #1a73e8;">
```

### 2. Responsive Design Pattern

Use custom properties for breakpoints:

```twig
<style>
    .responsive-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--wp--preset--spacing--40);
    }
    
    @media (max-width: 768px) {
        .responsive-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
```

### 3. Component Composition Pattern

Build complex components from simpler ones:

```twig
{# testimonial-card.twig #}
<div class="testimonial-card">
    {# Reuse avatar element #}
    {% include 'elements/avatar.twig' with {
        image: testimonial.author_image,
        size: 'large'
    } %}
    
    {# Reuse typography primitives #}
    {% include 'primitives/quote.twig' with {
        text: testimonial.quote
    } %}
    
    {# Reuse badge element #}
    {% include 'elements/badge.twig' with {
        badge_text: testimonial.author_role
    } %}
</div>
```

### 4. Data Passing Pattern

Pass data cleanly between templates:

```twig
{# Parent template #}
{% set hero_data = {
    title: 'Welcome',
    subtitle: 'To our site',
    background: 'primary',
    buttons: [
        {text: 'Get Started', url: '/start'},
        {text: 'Learn More', url: '/about'}
    ]
} %}

{% include 'sections/hero.twig' with {hero: hero_data} %}
```

### 5. Conditional Styling Pattern

Use data attributes for conditional styles:

```twig
<div 
    class="component"
    data-variant="{{ variant|default('default') }}"
    style="
        background: var(--wp--preset--color--{{ variant == 'dark' ? 'base-darkest' : 'base-lightest' }});
        color: var(--wp--preset--color--{{ variant == 'dark' ? 'base-lightest' : 'base-darkest' }});
    "
>
    {{ content }}
</div>
```

## Working with Timber/Twig

### Context Setup

Always set up proper context in PHP:

```php
$context = Timber::context();
$context['post'] = Timber::get_post();
$context['theme_options'] = get_theme_mod('theme_options');
$context['design_tokens'] = json_decode(file_get_contents(get_template_directory() . '/theme.json'), true);
```

### Useful Twig Filters

```twig
{# Sanitize output #}
{{ user_input|e }}

{# Default values #}
{{ color|default('primary') }}

{# Array manipulation #}
{% for item in items|slice(0, 3) %}

{# String manipulation #}
{{ title|lower|replace({' ': '-'}) }}
```

### Debugging in Twig

```twig
{# Dump variable contents #}
{{ dump(variable) }}

{# Check if variable exists #}
{% if variable is defined %}

{# Check variable type #}
{% if items is iterable %}
```

## Routing System

### Design Book Router

The theme includes a custom router (`inc/design-book-router.php`) that handles dynamic design book sections:

- **Base URL**: `/design-book/`
- **Sections**: `/design-book/{section}/` (e.g., `/design-book/colors/`)
- **Supported Sections**: colors, typography, spacing, layout, components, tokens, documentation

### Legacy URLs

- `/color-book/` - Redirects to `/design-book/colors/`
- Direct page templates still work for backwards compatibility

## CSS Architecture

### Separation of Concerns

1. **design-book.css** - General layout and navigation
   - Design book wrapper and header styles
   - Navigation menu styling
   - General button and action styles
   - Section layouts (non-specific)

2. **primitive-editor.css** - Editor-specific functionality
   - Color cards and swatches
   - HSL/CMYK sliders and controls
   - Tab interfaces
   - Interactive elements
   - Editor-specific layouts

### Key Features Preserved

- **HSL Sliders**: Percentage-based lightness adjustments
- **CMYK Support**: Full CMYK color space with proper gradients
- **Tab Switching**: Clean tab interface for HSL/CMYK modes
- **Montserrat Font**: Consistent typography throughout
- **Live Preview**: Real-time color updates

## Usage Guide

### Creating New Components

1. **Define tokens in theme.json** if needed
2. **Create Twig template** using only CSS custom properties
3. **No external CSS** - all styles inline or via theme.json

Example component:

```twig
{# card.twig #}
<div class="card" style="
    background: var(--wp--preset--color--base-lightest);
    padding: var(--wp--preset--spacing--40);
    border-radius: var(--wp--custom--border-radius--lg);
    box-shadow: var(--wp--custom--shadow--sm);
">
    <h3 style="
        font-size: var(--wp--preset--font-size--large);
        color: var(--wp--preset--color--primary-dark);
        margin-bottom: var(--wp--preset--spacing--20);
    ">{{ title }}</h3>
    
    <p style="
        color: var(--wp--preset--color--base-dark);
        line-height: 1.6;
    ">{{ content }}</p>
</div>
```

### Design Book Editor

Access the design book at: `/design-book/`

Features:
- **Live Preview**: See changes instantly
- **Token Editor**: Edit any design token
- **Component Builder**: Create and configure components
- **Export/Import**: Share component configurations

### AJAX Endpoints

- `mi_sync_primitive_to_theme_json`: Sync token changes
- `mi_get_theme_json_tokens`: Get all tokens
- `mi_save_card_type`: Save component configurations
- `mi_get_card_types`: Load saved configurations

## Best Practices

1. **Never add CSS files** for components
2. **Always use theme.json tokens** via CSS custom properties
3. **Keep components atomic** - single responsibility
4. **Document token usage** in Twig comments
5. **Test bidirectional sync** after changes

## Migration from Old System

1. **Removed Files**:
   - `blocks.css` - Block styles now in theme.json
   - `header.css` - Component styles in Twig
   - Old `style.css` - Replaced with minimal version

2. **Backup Location**: `style-backup.css` contains old styles for reference

3. **Component Migration**:
   - Extract styles to inline or theme.json
   - Replace class-based styling with token-based
   - Test each component in design book

## Troubleshooting

### Styles Not Applying
- Check theme.json syntax
- Verify CSS custom property names
- Clear WordPress cache
- Ensure rewrite rules are flushed

### Sync Not Working
- Check user permissions (edit_theme_options)
- Verify nonce in AJAX calls
- Check browser console for errors

### Missing Tokens
- Run `mi_get_theme_json_tokens` to see available tokens
- Add missing tokens to theme.json
- Regenerate CSS variables

### Router Not Working
- Flush rewrite rules by visiting Settings > Permalinks
- Check if mod_rewrite is enabled
- Verify .htaccess is writable

## Recent Completions

### ✅ Color Cleanup & Enhancement (June 2025)
**Status**: COMPLETED

**Enhancements Made**:
1. **Fixed Lightness Scale Issue**: Corrected HSL lightness slider to use percentage-based adjustments
2. **Added CMYK Support**: Implemented full CMYK color space with tabbed interface
3. **Updated Typography**: Changed design book UI font to Montserrat for consistency
4. **Color Palette Cleanup**: Removed duplicate colors from theme.json
5. **Router Integration**: Fixed design book router to properly load color editor

**Files Modified**:
- `theme.json` - Cleaned color palette
- `primitive-editor.css` - Added CMYK styles, updated font
- `primitive-colors.js` - Fixed lightness, added CMYK support
- `colors-editor.twig` - Added CMYK sliders and tabs
- `design-book-router.php` - Fixed template path and data structure
- `design-book.css` - Removed duplicate color-specific styles

**Result**: Clean, consistent color system with professional-grade editing capabilities and proper routing.

## Future Enhancements

1. **Visual Token Editor**: Drag-and-drop token management
2. **Component Library**: Pre-built component templates
3. **Version Control**: Track theme.json changes
4. **Export System**: Export components as plugins
5. **AI Integration**: Auto-generate components from descriptions
