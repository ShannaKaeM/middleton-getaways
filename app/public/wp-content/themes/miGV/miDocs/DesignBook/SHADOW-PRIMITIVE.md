# Shadow Primitive Documentation

## Overview

The shadow primitive provides a comprehensive system for managing box-shadow, text-shadow, and elevation effects throughout the Middleton Getaways theme. It follows the established Design Book System pattern with JSON-based tokens and a Twig primitive book for implementation.

## Structure

### Scale System (`scale`)
Progressive shadow sizes from `xs` to `2xl`, providing a consistent scale for general use:
- `none`: No shadow
- `xs`: Subtle shadow for minimal depth
- `sm`: Small shadow for slight elevation
- `md`: Medium shadow for cards and containers
- `lg`: Large shadow for prominent elements
- `xl`: Extra large shadow for modals and overlays
- `2xl`: Maximum shadow for highest elevation

### Elevation System (`elevation`)
Numeric elevation levels (0-5) based on Material Design principles:
- `0`: No elevation (flat)
- `1`: Raised slightly (app bars, buttons)
- `2`: Raised (cards, dropdowns)
- `3`: Elevated (popovers, tooltips)
- `4`: Highly elevated (modals)
- `5`: Maximum elevation (notifications)

### Inset Shadows (`inset`)
For creating pressed or recessed effects:
- `xs` through `xl`: Progressive inset depths
- Used for input fields, pressed buttons, or sunken containers

### Colored Shadows (`colored`)
Brand-colored shadows using theme primary and secondary colors:
- `primary`: Primary color shadow
- `primary-lg`: Larger primary shadow
- `secondary`: Secondary color shadow
- `secondary-lg`: Larger secondary shadow
- `warm`: Warm-toned shadow
- `cool`: Cool-toned shadow

### Special Purpose Shadows (`special`)
Pre-configured shadows for specific UI patterns:
- `focus`: Focus ring for interactive elements
- `focus-danger`: Error/danger focus state
- `card`: Standard card shadow
- `card-hover`: Card hover state
- `button`: Default button shadow
- `button-hover`: Button hover state
- `dropdown`: Dropdown menu shadow
- `modal`: Modal dialog shadow
- `text`: Text shadow for improved readability

## Usage Examples

### Basic Shadow
```twig
<div style="{% include 'primitive-books/shadow-book.twig' with { shadow: 'md' } %}">
  Content with medium shadow
</div>
```

### Multiple Shadows
```twig
<div style="{% include 'primitive-books/shadow-book.twig' with { 
  shadow: 'sm', 
  inset_shadow: 'xs' 
} %}">
  Combined outer and inner shadow
</div>
```

### Interactive States
```twig
<button class="btn" style="
  {% include 'primitive-books/shadow-book.twig' with { button_shadow: 'button' } %}
  transition: box-shadow 0.3s ease;
">
  <style>
    .btn:hover {
      {% include 'primitive-books/shadow-book.twig' with { button_shadow: 'button-hover' } %}
    }
  </style>
  Hover Me
</button>
```

### CSS Variables
All shadow tokens are available as CSS variables:
```css
.element {
  box-shadow: var(--shadows-scale-md);
}

.elevated {
  box-shadow: var(--shadows-elevation-3);
}
```

## Best Practices

1. **Use semantic tokens**: Prefer `card_shadow: 'card'` over generic `shadow: 'md'`
2. **Maintain hierarchy**: Use elevation system for consistent depth perception
3. **Consider performance**: Avoid excessive shadows on frequently animated elements
4. **Accessibility**: Ensure sufficient contrast when using colored shadows
5. **Consistency**: Use the same shadow tokens for similar UI patterns

## Integration Status

- ✅ JSON structure defined (`/primitives/shadows.json`)
- ✅ Primitive book created (`/templates/primitive-books/shadow-book.twig`)
- ⏳ Visual editor planned (Phase 2)
- ⏳ Theme.json sync planned

## Next Steps

1. Update `design-system-core.php` to include shadows in CSS variable generation
2. Create visual editor interface (Week 1-2 of Q1 2025)
3. Add interactive preview system
4. Implement theme.json synchronization
5. Create migration guide for existing shadow implementations