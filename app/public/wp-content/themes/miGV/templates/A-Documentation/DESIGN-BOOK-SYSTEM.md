# Design Book System

This document outlines the complete architecture of the Design Book System, which implements a self-sufficient atomic design system where primitives are the single source of truth. The system is divided into two distinct parts: Primitives/Globals and Composable Components.

**Note**: For comprehensive documentation, please refer to:
- [Design Book Overview](/miDocs/DesignBook/DESIGN-BOOK-OVERVIEW.md)
- [Technical Documentation](/miDocs/SYSTEMS/DESIGN-BOOK-TECHNICAL.md)
- [Quick Reference](/miDocs/DesignBook/QUICK-REFERENCE.md)

---

## Part 1: Primitives/Globals - The Foundation

### Philosophy

The Primitive Design Library defines the core, global design tokens that establish a consistent and maintainable visual language across the entire application or website. These primitives are the elemental building blocks of the design.

- **Single Source of Truth:** The primitive JSON files (e.g., `colors.json`, `typography.json`, `spacing.json`) located in the `/primitives/` directory are the **absolute single source of truth** for all design tokens.
- **Consistency:** By centralizing these tokens, we ensure design consistency across all components and UI elements.
- **Maintainability:** Changes to the design language (e.g., updating a primary color) are made in one place, and those changes propagate throughout the system.

### JSON-Based Primitive Architecture

The design system uses JSON files as the single source of truth for all primitive values. This approach provides:

1. **Clean Data/Presentation Separation**: JSON files store pure data, Twig templates handle rendering
2. **Easy Programmatic Updates**: AJAX saves write directly to JSON files
3. **Version Control Friendly**: Clear diffs show exactly what changed
4. **Language Agnostic**: Can be consumed by PHP, JavaScript, or any other tool
5. **Direct Bidirectional Sync**: Enables seamless sync with theme.json
6. **Dynamic Data Handling**: Supports runtime modifications and live preview

### Primitive Structure

**Location:** Primitive JSON files are stored in the `/primitives/` directory.

```
/primitives/
├── colors.json        # Color tokens
├── typography.json    # Typography tokens
├── spacing.json       # Spacing tokens
├── borders.json       # Border tokens 
└── shadows.json       # Shadow tokens 
```

**Format Examples:**

```json
// primitives/colors.json
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

```json
// primitives/spacing.json
{
  "scale": {
    "xs": "0.25rem",
    "sm": "0.5rem",
    "md": "0.75rem",
    "lg": "1rem"
  },
  "padding": {
    "section-sm": "2rem",
    "section-md": "4rem",
    "section-lg": "6rem"
  }
}
```

### Accessing Primitives

#### CSS Custom Properties (Primary Method)

- **Generation:** The `migv_generate_primitive_css_variables()` function reads all primitive JSON files and generates CSS Custom Properties.
- **Availability:** These CSS variables are made available globally via `wp_head` action.
- **Usage:** Components and theme styles should use these CSS variables for styling:

```css
.button {
  background-color: var(--colors-primary-default);
  font-size: var(--typography-font-sizes-medium);
  padding: var(--spacing-padding-md);
}
```

#### Timber/Twig Function

A custom Timber function `load_primitive()` loads JSON data:

```twig
{# Load typography tokens from JSON #}
{% set typography_tokens = load_primitive('typography') %}

{# Use the tokens #}
<p style="font-size: {{ typography_tokens.font_sizes.large }};">
```

### Management UI (Design Book Editor)

- A dedicated UI built with Twig templates allows for viewing and modification of primitive JSON files
- This UI directly reads from and saves to the primitive JSON files, ensuring they remain the single source of truth
- Editor UI styles are completely separate from design system tokens
- The design system can function independently without the editors

### theme.json Integration (Optional)

- While primitive JSON files are the source of truth, primitives *can* be synced to WordPress theme.json
- **Purpose:** Makes design tokens available to the WordPress Block Editor (Gutenberg)
- **Responsibility:** Integration is handled by the theme via sync handlers
- **Important:** theme.json should ONLY contain values synced from primitive JSON files

---

## Part 2: Composable Components - Building with Primitives

### Component Architecture

Components are self-contained units that consume primitives to create reusable UI elements. Each component:
- Loads required primitives at the top
- Uses primitive values exclusively for styling
- Maintains its own JSON configuration file (planned)
- Can be composed with other components

### File Structure

```
/templates/
├── primitive-books/      # Twig templates that consume JSON
│   ├── color-book.twig   # Renders color styles
│   ├── typography-book.twig
│   ├── spacing-book.twig
│   └── ...
└── components/          # Components that use primitive books
    ├── button.twig      # Button component
    ├── card.twig        # Card component
    └── ...
```

### Primitive Book Templates

Primitive books are Twig templates that load JSON data and apply styles based on parameters:

```twig
{# spacing-book.twig #}
{# Load spacing tokens from JSON #}
{% set spacing_tokens = load_primitive('spacing') %}

{# Apply spacing styles based on parameters #}
{% if padding %}
  {% set padding_value = spacing_tokens.padding[padding] ?? spacing_tokens.scale[padding] ?? padding %}
  padding: {{ padding_value }};
{% endif %}

{% if margin %}
  {% set margin_value = spacing_tokens.margin[margin] ?? spacing_tokens.scale[margin] ?? margin %}
  margin: {{ margin_value }};
{% endif %}
```

### Component Implementation

Components use primitive books to apply consistent styling:

```twig
{# button.twig #}
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
">
  {{ text }}
</button>
```

### Component JSON Configuration (Planned)

Each component will have its own JSON configuration file:

```json
// components/button.json
{
  "variants": {
    "primary": {
      "background": "primary",
      "color": "extreme-light",
      "hover": {
        "background": "primary-dark"
      }
    },
    "secondary": {
      "background": "secondary",
      "color": "extreme-light"
    }
  },
  "sizes": {
    "small": {
      "font_size": "small",
      "padding_x": "md",
      "padding_y": "sm"
    },
    "medium": {
      "font_size": "medium",
      "padding_x": "lg",
      "padding_y": "md"
    }
  }
}
```

### Best Practices for Components

1. **Primitive Usage**
   - Always use primitive values, never hardcoded styles
   - Load primitives at component top
   - Use token fallbacks: `{{ token.value ?? 'default' }}`

2. **Component Development**
   - Keep components focused on single responsibility
   - Make components composable
   - Document component parameters
   - Test with different primitive values

3. **Performance**
   - Cache loaded primitives in variables
   - Use Twig's `{% cache %}` for complex components
   - Minimize primitive lookups in loops

## Implementation Status

### Completed Primitives
- ✅ **Colors** - Complete with editor
- ✅ **Typography** - Complete with editor
- ✅ **Spacing** - Complete with editor

### In Progress
- 🚧 **Borders** - JSON structure defined
- 🚧 **Shadows** - JSON structure defined
- 🚧 **Component JSON** - Architecture planned

### Planned
- 📋 **Import/Export** - Primitive packages
- 📋 **Version Control** - Change history
- 📋 **Validation** - JSON schema validation
- 📋 **Component Library** - Full component system

## Security Considerations

1. **File Path Validation**: JSON files only loaded from `/primitives/` directory
2. **JSON Structure**: Validate structure before use in components
3. **AJAX Security**: All saves require nonce verification and capability checks
4. **Build Process**: Consider sanitizing JSON during build for production

## Architecture Principles

1. **Self-Sufficiency**: Primitives contain actual values, not references
2. **Single Source of Truth**: JSON files are the canonical source
3. **Separation of Concerns**: Data (JSON) separate from presentation (Twig)
4. **Tool Independence**: System works without editors
5. **Progressive Enhancement**: Start with primitives, add components

## Conclusion

The Design Book System provides a robust, maintainable approach to design systems by:
- Establishing primitives as the single source of truth
- Separating data from presentation
- Enabling programmatic updates and visual editing
- Supporting component composition
- Maintaining flexibility for future enhancements

By adhering to these principles, the system ensures consistency, maintainability, and scalability while providing an excellent developer experience.