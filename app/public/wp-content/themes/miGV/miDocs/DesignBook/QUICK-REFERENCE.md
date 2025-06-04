# Design Book System - Quick Reference

## What is the Design Book System?

A comprehensive design token management system that provides both programmatic and visual interfaces for managing design values in the Middleton Getaways theme.

## Quick Start

### For Developers

#### Using Design Tokens in Templates

```twig
{# Method 1: Include primitive books #}
<div style="
  {% include 'primitive-books/color-book.twig' with {
    background: 'primary',
    color: 'extreme-light'
  } %}
  {% include 'primitive-books/spacing-book.twig' with {
    padding: 'lg',
    margin_y: 'md'
  } %}
">
  Content
</div>

{# Method 2: Load primitives directly #}
{% set colors = load_primitive('colors') %}
<p style="color: {{ colors.primary.default }};">Text</p>

{# Method 3: Use CSS variables #}
<style>
.my-class {
  color: var(--colors-primary-default);
  padding: var(--spacing-scale-lg);
}
</style>
```

### For Designers/Content Editors

#### Accessing Visual Editors

1. **Colors Editor**: `/primitive-colors`
   - Edit color palettes
   - Live preview changes
   - Copy color values

2. **Typography Editor**: `/primitive-typography`
   - Manage font families, sizes, weights
   - Preview text styles
   - Set line heights and letter spacing

3. **Spacing Editor**: `/primitive-spacing`
   - Define spacing scale
   - Set padding/margin values
   - Configure layout spacing

4. **Borders Editor**: `/primitive-borders`
   - Set border widths
   - Define border styles
   - Configure border radii

## Available Primitives

### Colors
- `primary` (light, default, dark)
- `secondary` (light, default, dark)
- `neutral` (light, default, dark)
- `base` (lightest, light, default, dark, darkest)
- `extreme` (light, dark)

### Typography
- Font families: `heading`, `body`, `ui`
- Font sizes: `xs`, `sm`, `base`, `lg`, `xl`, `2xl`, `3xl`
- Font weights: `normal`, `medium`, `semiBold`, `bold`
- Line heights: `tight`, `base`, `relaxed`

### Spacing
- Scale: `xs`, `sm`, `md`, `lg`, `xl`, `2xl`, `3xl`
- Padding: `section-sm`, `section-md`, `section-lg`
- Margin: `element-sm`, `element-md`, `element-lg`
- Gap: `grid-sm`, `grid-md`, `grid-lg`

### Borders
- Widths: `none`, `thin`, `medium`, `thick`, `heavy`
- Styles: `none`, `solid`, `dashed`, `dotted`, `double`
- Radii: `none`, `xs`, `sm`, `md`, `lg`, `xl`, `2xl`, `full`

## Common Patterns

### Card Component
```twig
<article class="card" style="
  {% include 'primitive-books/color-book.twig' with {
    background: 'base-lightest',
    color: 'base-darkest'
  } %}
  {% include 'primitive-books/spacing-book.twig' with {
    padding: 'lg'
  } %}
  {% include 'primitive-books/border-book.twig' with {
    border_width: 'thin',
    border_style: 'solid',
    border_color: 'neutral-light',
    border_radius: 'md'
  } %}
">
  {{ content }}
</article>
```

### Button Component
```twig
<button style="
  {% include 'primitive-books/color-book.twig' with {
    background: variant == 'primary' ? 'primary' : 'secondary',
    color: 'extreme-light'
  } %}
  {% include 'primitive-books/typography-book.twig' with {
    font_size: size == 'large' ? 'lg' : 'base',
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
  {{ label }}
</button>
```

## File Locations

```
/wp-content/themes/miGV/
├── primitives/              # JSON token files
│   ├── colors.json
│   ├── typography.json
│   ├── spacing.json
│   └── borders.json
│
├── templates/
│   ├── primitive-books/     # Token application templates
│   └── design-book-editors/ # Visual editor UIs
│
└── assets/js/              # Editor functionality
    ├── primitive-colors.js
    ├── primitive-typography.js
    ├── primitive-spacing.js
    └── primitive-borders.js
```

## Tips & Best Practices

### Do's ✅
- Use semantic token names
- Test changes in preview before saving
- Keep token count manageable
- Document special use cases
- Coordinate with team on changes

### Don'ts ❌
- Don't hardcode design values
- Don't edit JSON files directly (use editors)
- Don't create duplicate tokens
- Don't skip the preview step
- Don't make breaking changes without communication

## Troubleshooting

**Q: My changes aren't showing up**
- Clear browser cache
- Check if changes were saved
- Verify correct token name usage

**Q: Editor won't save**
- Check user permissions
- Look for console errors
- Verify JSON syntax

**Q: CSS variables not working**
- Ensure proper syntax: `var(--prefix-token-name)`
- Check if primitive is loaded
- Verify token exists

## Need Help?

1. Check [full documentation](./DESIGN-BOOK-OVERVIEW.md)
2. Review [technical guide](../SYSTEMS/DESIGN-BOOK-TECHNICAL.md)
3. Ask the development team

---

*Quick Reference v1.0 - Design Book System*
