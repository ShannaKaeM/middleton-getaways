# Design Book System Overview

## Table of Contents
1. [Introduction](#introduction)
2. [Core Concepts](#core-concepts)
3. [System Architecture](#system-architecture)
4. [Primitives](#primitives)
5. [Design Book Editor](#design-book-editor)
6. [Implementation Guide](#implementation-guide)
7. [Best Practices](#best-practices)

## Introduction

The Design Book System is a comprehensive design token management system for the Middleton Getaways theme (miGV). It implements an atomic design approach where primitives serve as the single source of truth for all design tokens.

### Key Benefits
- **Consistency**: Centralized design tokens ensure visual consistency across the entire site
- **Maintainability**: Update design values in one place, changes propagate everywhere
- **Flexibility**: JSON-based system allows for easy updates without touching code
- **Visual Editing**: Intuitive UI for non-developers to manage design tokens
- **Version Control**: Track changes to design tokens through Git

## Core Concepts

### Design Tokens
Design tokens are the smallest pieces of a design system - colors, typography, spacing, etc. They are the atomic elements that combine to create all UI components.

### Primitives as Single Source of Truth
All design values are stored in JSON files within the `/primitives/` directory. These files are:
- The authoritative source for all design tokens
- Version controlled
- Programmatically accessible
- Visually editable through the Design Book Editor

### Self-Sufficient System
The design system can function independently without the visual editors. The primitives and primitive books provide all necessary functionality.

## System Architecture

```
/wp-content/themes/miGV/
├── primitives/                    # JSON design token files
│   ├── colors.json               # Color tokens
│   ├── typography.json           # Typography tokens
│   ├── spacing.json              # Spacing tokens
│   ├── borders.json              # Border tokens
│   └── shadows.json              # Shadow tokens (planned)
│
├── templates/
│   ├── primitive-books/          # Twig templates that consume primitives
│   │   ├── color-book.twig       # Color styling template
│   │   ├── typography-book.twig  # Typography styling template
│   │   ├── spacing-book.twig     # Spacing styling template
│   │   └── border-book.twig      # Border styling template
│   │
│   └── design-book-editors/      # Visual editor interfaces
│       ├── colors-editor.twig    # Color editor UI
│       ├── typography-editor.twig # Typography editor UI
│       ├── spacing-editor.twig   # Spacing editor UI
│       └── borders-editor.twig   # Borders editor UI
│
├── assets/
│   ├── css/
│   │   └── design-book-editors.css # Editor UI styles
│   └── js/
│       ├── primitive-colors.js    # Color editor functionality
│       ├── primitive-typography.js # Typography editor functionality
│       ├── primitive-spacing.js   # Spacing editor functionality
│       └── primitive-borders.js   # Borders editor functionality
│
├── inc/
│   └── design-system-core.php    # Core functions for CSS variable generation
│
└── page-primitive-*.php          # WordPress page templates for editors
```

## Primitives

### Color Primitive (`colors.json`)
```json
{
  "primary": {
    "light": "#d6dcd6",
    "default": "#5a7b7c",
    "dark": "#3a5a59"
  },
  "secondary": {
    "light": "#c38484",
    "default": "#975d55",
    "dark": "#853d2d"
  }
}
```

### Typography Primitive (`typography.json`)
```json
{
  "font_families": {
    "heading": "Playfair Display, serif",
    "body": "Inter, sans-serif"
  },
  "font_sizes": {
    "xs": "0.75rem",
    "sm": "0.875rem",
    "base": "1rem",
    "lg": "1.125rem"
  }
}
```

### Spacing Primitive (`spacing.json`)
```json
{
  "scale": {
    "xs": "0.25rem",
    "sm": "0.5rem",
    "md": "0.75rem",
    "lg": "1rem"
  },
  "padding": {
    "section-sm": "2rem",
    "section-md": "4rem"
  }
}
```

### Borders Primitive (`borders.json`)
```json
{
  "widths": {
    "thin": "1px",
    "medium": "2px",
    "thick": "3px"
  },
  "styles": {
    "solid": "solid",
    "dashed": "dashed"
  },
  "radii": {
    "sm": "0.25rem",
    "md": "0.5rem",
    "lg": "0.75rem"
  }
}
```

## Design Book Editor

The Design Book Editor provides a visual interface for managing design tokens.

### Features
- **Live Preview**: See changes in real-time
- **Save to JSON**: Updates primitive JSON files directly
- **Sync to theme.json**: Optional WordPress integration
- **Reset to Defaults**: Restore original values
- **Copy Values**: Quick access to token values
- **Responsive Design**: Works on all devices

### Accessing Editors

1. **Via WordPress Pages**:
   - Create pages with slugs: `primitive-colors`, `primitive-typography`, `primitive-spacing`, `primitive-borders`
   - Assign corresponding page templates

2. **Direct Access**:
   - Colors: `/primitive-colors`
   - Typography: `/primitive-typography`
   - Spacing: `/primitive-spacing`
   - Borders: `/primitive-borders`

### Editor Capabilities
- Only users with `edit_theme_options` capability can save changes
- All users can view and copy values
- Changes are tracked through Git version control

## Implementation Guide

### Using Primitives in Components

#### 1. Include Primitive Books in Twig Templates

```twig
{# Button Component #}
<button style="
  {% include 'primitive-books/color-book.twig' with {
    background: 'primary',
    color: 'extreme-light'
  } %}
  {% include 'primitive-books/typography-book.twig' with {
    font_size: 'medium',
    font_weight: 'semiBold'
  } %}
  {% include 'primitive-books/spacing-book.twig' with {
    padding_x: 'lg',
    padding_y: 'md'
  } %}
  {% include 'primitive-books/border-book.twig' with {
    border_radius: 'md'
  } %}
">
  {{ button_text }}
</button>
```

#### 2. Load Primitives Directly in Twig

```twig
{# Load spacing tokens #}
{% set spacing_tokens = load_primitive('spacing') %}

{# Use token values #}
<div style="padding: {{ spacing_tokens.padding['section-lg'] }};">
  Content
</div>
```

#### 3. Use CSS Variables

All primitives are automatically converted to CSS variables:

```css
.my-component {
  background-color: var(--colors-primary-default);
  font-size: var(--typography-font-sizes-base);
  padding: var(--spacing-scale-lg);
  border-radius: var(--borders-radii-md);
}
```

### Creating New Primitives

1. **Create JSON file** in `/primitives/` directory
2. **Create primitive book** template in `/templates/primitive-books/`
3. **Create editor template** in `/templates/design-book-editors/`
4. **Create JavaScript file** for editor functionality
5. **Create PHP page template** for WordPress integration
6. **Add AJAX handlers** in `functions.php`
7. **Update design-system-core.php** to include new primitive

## Best Practices

### For Developers

1. **Always use primitives** - Never hardcode design values
2. **Load primitives efficiently** - Cache loaded values in variables
3. **Use semantic naming** - Token names should describe purpose, not appearance
4. **Document changes** - Include comments when updating primitive structures
5. **Test across contexts** - Ensure primitives work in all usage scenarios

### For Designers

1. **Maintain consistency** - Use existing tokens before creating new ones
2. **Think systematically** - Consider how changes affect the entire system
3. **Document intentions** - Add comments to explain design decisions
4. **Review before saving** - Check live preview to ensure desired results
5. **Coordinate updates** - Communicate changes to the development team

### Version Control

1. **Commit primitive changes separately** - Makes tracking easier
2. **Use descriptive commit messages** - "Update primary color palette"
3. **Review diffs carefully** - JSON formatting can make diffs large
4. **Consider branching** - For major design system updates
5. **Keep backups** - System auto-creates `.backup.json` files

### Performance Optimization

1. **Minimize primitive lookups** in loops
2. **Use Twig caching** for complex components
3. **Lazy load editors** - Only load JS/CSS when needed
4. **Optimize JSON structure** - Keep nesting reasonable
5. **Monitor CSS variable count** - Too many can impact performance

## Troubleshooting

### Common Issues

1. **Changes not appearing**:
   - Clear WordPress cache
   - Check browser cache
   - Verify JSON syntax

2. **Editor not saving**:
   - Check user permissions
   - Verify nonce
   - Check browser console for errors

3. **CSS variables not working**:
   - Ensure `design-system-core.php` is included
   - Check `wp_head` action is firing
   - Verify primitive file paths

4. **JSON syntax errors**:
   - Use JSON validator
   - Check for trailing commas
   - Ensure proper escaping

## Future Enhancements

### Planned Features
- Shadow primitives
- Animation primitives
- Import/Export functionality
- Visual diff tool
- Primitive validation
- Component library integration
- Dark mode support
- A11y color contrast checker

### Potential Improvements
- GraphQL API for primitives
- Real-time collaboration
- Design token documentation
- Figma plugin integration
- Primitive inheritance system
- Multi-theme support

## Conclusion

The Design Book System provides a robust, scalable approach to design token management. By maintaining primitives as the single source of truth and providing both programmatic and visual interfaces, it bridges the gap between designers and developers while ensuring consistency and maintainability across the entire project.
