# TW4 Design System Guide

## Quick Start

### What This Is
A modern component system using Tailwind CSS v4's new features (@utility, @custom-variant) for WordPress and GenerateBlocks.

### Core Files
```
tw4-components.css        # Component variants (cards, buttons, sections)
gutenstyles-bridge.css    # Maps TW4 → WordPress/GB
theme-tw4.json           # WordPress theme.json using TW4 variables
```

## How to Use

### 1. Basic Usage
```html
<!-- Component + Variant + Overrides -->
<div class="mi-card card-style-1 p-8 shadow-xl" data-card-style="1">
  <h3>Card Title</h3>
  <p>Content here</p>
</div>
```

### 2. In GenerateBlocks
Add in "Additional CSS Classes" field:
- `mi-card card-style-1` (component + variant)
- `p-6 shadow-lg rounded-lg` (utility overrides)

### 3. Available Components

**Cards**
- `card-style-1` - Clean minimal
- `card-style-2` - Branded bold  
- `card-style-3` - Gradient modern

**Buttons**
- `btn-primary`, `btn-secondary`, `btn-outline`, `btn-ghost`
- Sizes: `size-sm`, `size-md`, `size-lg`, `size-xl`

**Sections**
- `section-hero`, `section-feature`, `section-cta`

## Technical Details

### TW4 Variables Available
All TW4 utilities are CSS variables via Windpress:
- Colors: `var(--color-primary)`
- Spacing: `var(--spacing-6)`
- Typography: `var(--text-xl)`
- Shadows: `var(--shadow-lg)`
- Radius: `var(--radius-md)`

### How Variants Work
```css
/* Define variant with @utility */
@utility card-style-1 {
  background: var(--color-white);
  padding: var(--spacing-6);
  border-radius: var(--radius-lg);
}

/* Enable data attribute with @custom-variant */
@custom-variant card-style-1 (&[data-card-style="1"]);
```

### WordPress Integration
The `gutenstyles-bridge.css` maps TW4 to WordPress:
```css
--wp--preset--color--primary: var(--color-primary);
--wp--preset--spacing--md: var(--spacing-4);
```

## Examples

### Hero Section Pattern
```html
<section class="mi-section section-hero" data-section="hero">
  <div class="mi-container">
    <h1 class="text-5xl font-bold mb-6">Welcome</h1>
    <a href="#" class="mi-btn btn-primary size-lg">Get Started</a>
  </div>
</section>
```

### Responsive Card
```html
<!-- Different styles at different breakpoints -->
<div class="mi-card card-style-1 md:card-style-2 lg:card-style-3">
  <!-- Mobile: style 1, Tablet: style 2, Desktop: style 3 -->
</div>
```

## Setup in WordPress

1. **Enqueue CSS files** in functions.php:
```php
wp_enqueue_style('tw4-components', 
  get_stylesheet_directory_uri() . '/tw4-design-system/tw4-components.css');
wp_enqueue_style('tw4-bridge', 
  get_stylesheet_directory_uri() . '/tw4-design-system/gutenstyles-bridge.css');
```

2. **Use in block patterns** or GenerateBlocks
3. **Override inline** with TW4 utilities as needed

## Key Benefits

✅ **Clean variants** - `data-card-style="1"` instead of multiple classes  
✅ **Inline overrides** - Add utilities to tweak per instance  
✅ **Single source** - TW4 variables power everything  
✅ **WordPress native** - Works with theme.json and block editor  

## Links
- [TW4 Theme Docs](https://tailwindcss.com/docs/theme)
- [Functions & Directives](https://tailwindcss.com/docs/functions-and-directives)
- View examples: `tw4-component-examples.html`
