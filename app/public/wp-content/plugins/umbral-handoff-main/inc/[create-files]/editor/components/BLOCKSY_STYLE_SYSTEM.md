# Blocksy Style System for Umbral Components

This document outlines how to use the Blocksy-inspired design system when creating components for the Umbral Editor. The system provides a comprehensive set of CSS variables, utility classes, and component patterns that ensure consistency and maintainability.

## Overview

The Blocksy style system is built around:
- **CSS Variables**: Comprehensive design tokens for colors, typography, spacing, and more
- **Utility Classes**: Pre-built classes for common styling needs
- **Component Patterns**: Reusable patterns for cards, buttons, grids, etc.
- **Responsive Design**: Mobile-first approach with consistent breakpoints
- **Scoped Styling**: Component-specific CSS variables that extend the base system

## Reference Files

- **[Component Creation Guide](./COMPONENT_CREATION_GUIDE.md)** - Complete guide for creating components
- **[Example Blocksy CSS](./example_blocksy.css)** - Full implementation of the Blocksy design system

## Design Tokens (CSS Variables)

### Colors
```css
/* Text Colors */
--blocksy-color-text: #2c3e50;
--blocksy-color-text-light: #5a6c7d;
--blocksy-color-text-lighter: #8a9ba8;

/* Background Colors */
--blocksy-color-background: #ffffff;
--blocksy-color-background-alt: #f8f9fa;
--blocksy-color-background-secondary: #e9ecef;

/* Brand Colors */
--blocksy-color-primary: #3498db;
--blocksy-color-primary-hover: #2980b9;
--blocksy-color-success: #27ae60;
--blocksy-color-warning: #f39c12;
--blocksy-color-error: #e74c3c;
```

### Typography
```css
/* Font Families */
--blocksy-font-primary: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
--blocksy-font-secondary: Georgia, 'Times New Roman', serif;
--blocksy-font-mono: 'SF Mono', Monaco, 'Cascadia Code', monospace;

/* Font Sizes */
--blocksy-text-xs: 0.75rem;    /* 12px */
--blocksy-text-sm: 0.875rem;   /* 14px */
--blocksy-text-base: 1rem;     /* 16px */
--blocksy-text-lg: 1.125rem;   /* 18px */
--blocksy-text-xl: 1.25rem;    /* 20px */
--blocksy-text-2xl: 1.5rem;    /* 24px */
--blocksy-text-3xl: 1.875rem;  /* 30px */
```

### Spacing
```css
--blocksy-space-xs: 0.25rem;   /* 4px */
--blocksy-space-sm: 0.5rem;    /* 8px */
--blocksy-space-md: 1rem;      /* 16px */
--blocksy-space-lg: 1.5rem;    /* 24px */
--blocksy-space-xl: 2rem;      /* 32px */
--blocksy-space-2xl: 3rem;     /* 48px */
--blocksy-space-3xl: 4rem;     /* 64px */
```

### Border Radius & Shadows
```css
/* Border Radius */
--blocksy-radius-sm: 0.125rem;  /* 2px */
--blocksy-radius: 0.25rem;      /* 4px */
--blocksy-radius-lg: 0.5rem;    /* 8px */
--blocksy-radius-xl: 0.75rem;   /* 12px */

/* Shadows */
--blocksy-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--blocksy-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
--blocksy-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
```

## Component Styling Architecture

### 1. Component CSS Variables and Base Stylesheet

**LG.css as Base Stylesheet**: The `LG.css` file serves as the base stylesheet for each component. This is where you should:

1. **Define component-scoped CSS variables** that extend the Blocksy system
2. **Add custom styles or variables** that don't exist in the [example_blocksy.css](./example_blocksy.css)
3. **Scope all variables to the component** to prevent conflicts with other components

**LG.css Structure:**
```css
/* Component-scoped variables extending Blocksy */
#{{ component_id }} {
    /* Always scope variables to the component ID */
    --component-bg: var(--blocksy-color-background);
    --component-text: var(--blocksy-color-text);
    --component-accent: var(--blocksy-color-primary);
    --component-spacing: var(--blocksy-space-lg);
    --component-radius: var(--blocksy-radius-lg);
    
    /* Custom variables not in Blocksy system */
    --component-custom-gradient: linear-gradient(135deg, var(--blocksy-color-primary), var(--blocksy-color-success));
    --component-hover-scale: 1.02;
}

/* Desktop-specific component styles */
.my-component .element {
    background: var(--component-bg);
    color: var(--component-text);
    padding: var(--component-spacing);
    border-radius: var(--component-radius);
}
```

**Best Practices for LG.css:**
- ✅ **Prefer existing Blocksy variables** from [example_blocksy.css](./example_blocksy.css)
- ✅ **Scope all custom variables** to `#{{ component_id }}`
- ✅ **Use semantic variable names** that describe purpose, not appearance
- ✅ **Extend Blocksy variables** rather than creating completely new values
- ❌ **Don't create variables** that already exist in the Blocksy system

### 2. Responsive CSS Structure

Each component uses separate CSS files for different breakpoints:

```
styles/
├── XS.css     # Mobile (< 576px)
├── SM.css     # Small tablets (≥ 576px)
├── MD.css     # Tablets (≥ 768px)
├── LG.css     # Desktop Base (≥ 992px) - Contains component CSS variables
├── XL.css     # Large desktop (≥ 1200px)
└── 2XL.css    # Extra large (≥ 1400px)
```

**Important**: The `LG.css` file serves as the **base stylesheet** for each component. This is where you should define component-scoped CSS variables and any custom styles that don't exist in the [example_blocksy.css](./example_blocksy.css). Always prefer using existing Blocksy variables and utilities before creating custom ones.

### 3. Breakpoint-Specific Styling

**XS.css** (Mobile-first):
```css
/* Mobile styles using Blocksy variables */
.feature-grid .grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--blocksy-space-md);
    padding: var(--blocksy-space-md);
}

.feature-grid .card {
    padding: var(--blocksy-space-lg);
    border-radius: var(--blocksy-radius-lg);
    background: var(--blocksy-color-background);
    box-shadow: var(--blocksy-shadow);
}
```

**LG.css** (Desktop Base Stylesheet):
```css
/* Component-scoped variables (define in LG.css) */
#{{ component_id }} {
    --grid-columns: 3;
    --grid-gap: var(--blocksy-space-xl);
    --card-hover-lift: -4px;
    --card-transition: var(--blocksy-transition);
}

/* Desktop styles using component variables */
.feature-grid .grid {
    grid-template-columns: repeat(var(--grid-columns), 1fr);
    gap: var(--grid-gap);
    padding: var(--blocksy-space-xl);
}

.feature-grid .card:hover {
    transform: translateY(var(--card-hover-lift));
    box-shadow: var(--blocksy-shadow-lg);
    transition: var(--card-transition);
}
```

## Utility Classes

Use Blocksy utility classes for common styling needs:

```html
<!-- Typography -->
<h2 class="blocksy-text-2xl blocksy-font-primary">Title</h2>
<p class="blocksy-text-base blocksy-text-secondary">Description</p>

<!-- Spacing -->
<div class="blocksy-p-lg blocksy-m-md">Content</div>

<!-- Layout -->
<div class="blocksy-grid blocksy-grid-cols-3">Grid items</div>

<!-- Styling -->
<div class="blocksy-card blocksy-shadow-md blocksy-rounded-lg">Card</div>
```

## Component Patterns

### Card Pattern
```css
.my-component .card {
    /* Use Blocksy card pattern */
    background: var(--blocksy-color-background);
    border: 1px solid var(--blocksy-color-background-secondary);
    border-radius: var(--blocksy-radius-lg);
    box-shadow: var(--blocksy-shadow);
    padding: var(--blocksy-space-lg);
    transition: var(--blocksy-transition);
}

.my-component .card:hover {
    box-shadow: var(--blocksy-shadow-md);
    transform: translateY(-2px);
}
```

### Button Pattern
```css
.my-component .button {
    display: inline-flex;
    align-items: center;
    padding: var(--blocksy-space-sm) var(--blocksy-space-md);
    background: var(--blocksy-color-primary);
    color: white;
    border-radius: var(--blocksy-radius);
    font-family: var(--blocksy-font-primary);
    font-size: var(--blocksy-text-sm);
    font-weight: 500;
    transition: var(--blocksy-transition);
}

.my-component .button:hover {
    background: var(--blocksy-color-primary-hover);
    transform: translateY(-1px);
    box-shadow: var(--blocksy-shadow-md);
}
```

### Grid Pattern
```css
.my-component .grid {
    display: grid;
    gap: var(--blocksy-space-lg);
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}
```

## Best Practices

### 1. Always Use Blocksy Variables
```css
/* ✅ Good - Using Blocksy variables */
.component {
    color: var(--blocksy-color-text);
    padding: var(--blocksy-space-lg);
    border-radius: var(--blocksy-radius-lg);
}

/* ❌ Bad - Hard-coded values */
.component {
    color: #2c3e50;
    padding: 24px;
    border-radius: 8px;
}
```

### 2. Scope Variables to Components (Always in LG.css)
```css
/* ✅ Good - Component-scoped variables in LG.css */
#{{ component_id }} {
    --card-bg: var(--blocksy-color-background);
    --card-hover-bg: var(--blocksy-color-background-alt);
    --card-border: var(--blocksy-color-background-secondary);
    
    /* Custom variables for component-specific needs */
    --card-special-accent: color-mix(in srgb, var(--blocksy-color-primary) 20%, transparent);
}

#{{ component_id }} .card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
}

#{{ component_id }} .card:hover {
    background: var(--card-hover-bg);
    box-shadow: inset 0 0 0 2px var(--card-special-accent);
}
```

### 3. Mobile-First Responsive Design
```css
/* XS.css - Mobile first */
.component {
    padding: var(--blocksy-space-md);
    font-size: var(--blocksy-text-sm);
}

/* LG.css - Desktop enhancement */
.component {
    padding: var(--blocksy-space-xl);
    font-size: var(--blocksy-text-lg);
}
```

### 4. Consistent Hover States
```css
.component .interactive-element {
    transition: var(--blocksy-transition);
}

.component .interactive-element:hover {
    transform: translateY(-2px);
    box-shadow: var(--blocksy-shadow-md);
}
```

### 5. Dark Mode Support
The Blocksy system automatically handles dark mode through CSS custom properties:

```css
/* Variables automatically switch in dark mode */
.component {
    background: var(--blocksy-color-background); /* Auto-switches */
    color: var(--blocksy-color-text); /* Auto-switches */
}
```

## Implementation Example

Here's a complete example of a component using the Blocksy system:

**view.twig**:
```html
{# Styles and scripts are auto-enqueued by compileComponent() #}
<section id="{{ component_id }}" class="blocksy-container">
    <div class="blocksy-grid">
        {% for feature in features %}
        <div class="feature-card blocksy-card">
            <div class="feature-icon blocksy-text-3xl">{{ feature.icon }}</div>
            <h3 class="feature-title blocksy-text-xl">{{ feature.title }}</h3>
            <p class="feature-description blocksy-text-base">{{ feature.description }}</p>
        </div>
        {% endfor %}
    </div>
</section>
```

**styles/XS.css**:
```css
/* Mobile styles */
.feature-card {
    background: var(--feature-card-bg);
    border: 1px solid var(--feature-card-border);
    padding: var(--blocksy-space-lg);
    text-align: center;
}

.feature-icon {
    color: var(--feature-icon-color);
    margin-bottom: var(--blocksy-space-md);
}

.feature-title {
    color: var(--feature-title-color);
    margin-bottom: var(--blocksy-space-sm);
}

.feature-description {
    color: var(--feature-text-color);
}
```

**styles/LG.css** (Base Stylesheet):
```css
/* Component-scoped variables */
#{{ component_id }} {
    --feature-card-bg: var(--blocksy-color-background);
    --feature-card-border: var(--blocksy-color-background-secondary);
    --feature-icon-color: var(--blocksy-color-primary);
    --feature-title-color: var(--blocksy-color-text);
    --feature-text-color: var(--blocksy-color-text-light);
    
    /* Custom variables for enhanced effects */
    --feature-hover-lift: -4px;
    --feature-hover-shadow: var(--blocksy-shadow-lg);
}

/* Desktop styles using component variables */
.feature-card {
    background: var(--feature-card-bg);
    border: 1px solid var(--feature-card-border);
}

.feature-card:hover {
    transform: translateY(var(--feature-hover-lift));
    box-shadow: var(--feature-hover-shadow);
}

.feature-icon {
    color: var(--feature-icon-color);
}

.feature-title {
    color: var(--feature-title-color);
}

.feature-description {
    color: var(--feature-text-color);
}
```

This approach ensures your components are consistent, maintainable, and seamlessly integrate with the Blocksy design system while maintaining the flexibility to customize as needed.

## Related Documentation

- **[Component Creation Guide](./COMPONENT_CREATION_GUIDE.md)** - Complete guide for creating components
- **[Example Blocksy CSS](./example_blocksy.css)** - Full Blocksy design system implementation