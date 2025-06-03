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
