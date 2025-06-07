# TW4 Integration Guide for WordPress & GenerateBlocks

## Overview

This guide explains how Tailwind CSS v4 (TW4) CSS variables integrate with WordPress theme.json and GenerateBlocks to create a unified design system.

## Key Concepts

### 1. TW4 Variable System
With Windpress installed, all TW4 utilities are exposed as CSS variables:
- Colors: `--color-primary`, `--color-secondary`, etc.
- Spacing: `--spacing-4`, `--spacing-8`, etc.
- Typography: `--text-base`, `--text-xl`, etc.
- Shadows: `--shadow-md`, `--shadow-lg`, etc.
- Border Radius: `--radius-md`, `--radius-lg`, etc.

### 2. Integration Strategy
- **No custom prefixes needed** - Use TW4 variables directly
- **Single source of truth** - TW4 config drives everything
- **Automatic updates** - Change TW4 config, everything updates

## Implementation

### WordPress theme.json Integration
```json
{
  "settings": {
    "color": {
      "palette": [
        {
          "name": "Primary",
          "slug": "primary",
          "color": "var(--color-primary)"
        }
      ]
    },
    "spacing": {
      "spacingSizes": [
        {
          "name": "Medium",
          "slug": "md",
          "size": "var(--spacing-4)"
        }
      ]
    }
  }
}
```

### GenerateBlocks Integration
```css
/* GB Container Variables */
--gb-container-padding: var(--spacing-6);
--gb-container-margin: var(--spacing-0);

/* GB Button Variables */
--gb-button-padding-x: var(--spacing-6);
--gb-button-padding-y: var(--spacing-3);
```

## Usage Examples

### In Block Editor
```html
<!-- Uses TW4 primary color -->
<div class="has-primary-background-color">
  Content
</div>
```

### In GenerateBlocks
Add these classes in GB's "Additional CSS Classes":
- `bg-primary` - Primary background
- `p-6` - Padding using TW4 spacing
- `shadow-lg` - Large shadow
- `rounded-lg` - Large border radius

### Custom HTML Blocks
```html
<div class="card bg-white p-6 shadow-md rounded-lg">
  <h3 class="text-xl font-bold text-primary">Title</h3>
  <p class="text-gray-600">Content</p>
</div>
```

## Benefits

1. **No Duplication** - Define once in TW4, use everywhere
2. **Consistency** - All components use same design tokens
3. **Maintainability** - Update TW4 config, everything updates
4. **Performance** - CSS variables are lightweight
5. **Future-proof** - As TW4 evolves, system stays current

## File Structure

```
tw4-design-system/
├── gutenstyles-bridge.css    # Maps TW4 to WP/GB
├── theme-tw4.json            # WordPress theme.json
├── atomic-tw4-system.css     # Atomic design components
└── tw4-components.css        # Component variants
```

## Best Practices

1. **Use TW4 variables directly** - Don't create duplicates
2. **Leverage utility classes** - Use in GB Additional CSS
3. **Keep it simple** - Let TW4 handle the complexity
4. **Document patterns** - Save as reusable blocks

## Troubleshooting

### Variables not working?
1. Ensure Windpress is installed and active
2. Check that CSS files are enqueued properly
3. Verify variable names match TW4 syntax

### Styles not applying?
1. Check CSS specificity
2. Ensure proper file load order
3. Clear browser and WordPress cache
