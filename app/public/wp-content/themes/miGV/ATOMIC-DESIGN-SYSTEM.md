# miGV Atomic Design System

## Implementation Status

### Completed
- **Color Primitive Editor** (`/color-book/`) - Fully functional with all color groups (primary, secondary, neutral, base, extreme, other) displayed in proper grid layouts with live editing capabilities

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
│   ├── primitive-books/         # Atomic design tokens
│   ├── element-books/          # Small UI elements
│   ├── component-books/        # Larger components
│   └── section-books/          # Page sections
├── assets/
│   ├── css/
│   │   └── design-book.css     # Editor UI only
│   └── js/
│       └── design-book-sync.js # Sync functionality
└── functions.php               # Sync handlers

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

1. **Color Editing**: Click on any color swatch to change it
2. **Typography Editing**: Adjust font sizes in the typography section
3. **Spacing Editing**: Modify spacing values
4. **Custom Properties**: Edit any custom token

Changes are saved via AJAX to `theme.json` and immediately reflected in the UI.

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

### Sync Not Working
- Check user permissions (edit_theme_options)
- Verify nonce in AJAX calls
- Check browser console for errors

### Missing Tokens
- Run `mi_get_theme_json_tokens` to see available tokens
- Add missing tokens to theme.json
- Regenerate CSS variables

## Future Enhancements

1. **Visual Token Editor**: Drag-and-drop token management
2. **Component Library**: Pre-built component templates
3. **Version Control**: Track theme.json changes
4. **Export System**: Export components as plugins
5. **AI Integration**: Auto-generate components from descriptions
