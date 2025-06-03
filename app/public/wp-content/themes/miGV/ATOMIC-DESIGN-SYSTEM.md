# miGV Atomic Design System - Self-Sufficient Architecture

## Core Architecture Principle

The miGV theme implements a **self-sufficient atomic design system** where Twig primitives are the single source of truth. Unlike traditional WordPress themes that rely on theme.json, our primitives store actual values and can function independently.

## Key Concepts

### 1. Self-Sufficient Twig Primitives

Each primitive Twig file contains actual values, not CSS variable references:

```twig
{# typography-book.twig - SELF-SUFFICIENT #}
{% set typography_tokens = {
  font_sizes: {
    small: '0.875rem',      {# Actual value, not var(--wp--preset--font-size--small) #}
    medium: '1rem',
    large: '1.25rem',
    'x-large': '1.5rem',
    'xx-large': '2rem',
    huge: '3rem'
  },
  font_weights: {
    regular: '400',
    medium: '500',
    semiBold: '600',
    bold: '700',
    extraBold: '800'
  }
} %}
```

### 2. Data Flow Architecture

```
┌─────────────────────┐
│   Twig Primitives   │ ← Single Source of Truth
│  (Actual Values)    │
└──────────┬──────────┘
           │
           ├─────────────┐
           │             ▼
           │    ┌─────────────────┐
           │    │   Components    │ ← Import primitives
           │    │  (buttons.twig) │
           │    └─────────────────┘
           │
           ▼
    ┌──────────────┐
    │  theme.json  │ ← Synced FROM primitives (optional)
    │ (WordPress)  │
    └──────────────┘
```

### 3. Component Architecture

Components extract styles ONLY from Twig primitives:

```twig
{# button.twig - Uses primitives directly #}
{% include 'primitive-books/typography-book.twig' %}
{% include 'primitive-books/color-book.twig' %}
{% include 'primitive-books/spacing-book.twig' %}

<button 
  class="btn btn-{{ variant }} btn-{{ size }}"
  style="
    {# Typography from primitives #}
    font-family: {{ typography_tokens.font_families.primary }};
    font-size: {{ typography_tokens.font_sizes[size|default('medium')] }};
    font-weight: {{ typography_tokens.font_weights.medium }};
    
    {# Colors from primitives #}
    color: {{ variant == 'primary' ? color_tokens.colors.white : color_tokens.colors.primary }};
    background-color: {{ color_tokens.colors[variant] }};
    
    {# Spacing from primitives #}
    padding: {{ spacing_tokens.sizes[size ~ '-y'] }} {{ spacing_tokens.sizes[size ~ '-x'] }};
    
    {# Other properties #}
    border: none;
    border-radius: {{ border_tokens.radius.md }};
    cursor: pointer;
    transition: all 0.2s ease;
  "
  {% if disabled %}disabled{% endif %}
>
  {{ text }}
</button>
```

## Implementation Status

### Completed
- **Color Primitive Editor** - Visual editor with HSL/CMYK support
- **Typography Primitive Editor** - Complete with all typography controls
- **Design Book Router** - Dynamic routing for design sections

### In Progress (Architecture Update)
- **Self-Sufficient Primitives** - Converting from CSS variables to actual values
- **Component Refactoring** - Updating to use primitive values directly
- **Sync Mechanism** - One-way sync from Twig → theme.json

### Planned
- **Import/Export** - Portable primitive packages
- **Version Control** - Track primitive changes
- **Component Library** - Pre-built self-sufficient components

## File Structure

```
miGV/
├── templates/
│   ├── primitive-books/         # Self-sufficient primitives (source of truth)
│   │   ├── color-book.twig     # Color values: { primary: '#1a73e8' }
│   │   ├── typography-book.twig # Font values: { large: '1.25rem' }
│   │   ├── spacing-book.twig   # Spacing values: { md: '1rem' }
│   │   └── border-book.twig    # Border values: { radius: '0.5rem' }
│   │
│   ├── design-book-editors/    # Visual editors (UI only)
│   │   ├── colors-editor.twig  # Color picker interface
│   │   ├── typography-editor.twig # Typography controls
│   │   ├── spacing-editor.twig # Spacing controls
│   │   ├── layout-editor.twig  # Layout configuration
│   │   ├── components.twig     # Component library
│   │   ├── tokens.twig         # Token browser
│   │   └── documentation.twig  # Design system docs
│   │
│   ├── elements/               # Atomic elements using primitives
│   │   ├── button.twig
│   │   ├── badge.twig
│   │   └── input.twig
│   │
│   └── components/             # Composite components
│       ├── card.twig
│       ├── hero.twig
│       └── navigation.twig
│
├── assets/
│   ├── css/
│   │   ├── design-book.css     # General design book layout
│   │   └── design-book-editors.css # Editor-specific UI styles
│   └── js/
│       ├── design-book.js      # General functionality
│       ├── primitive-colors.js # Color editor functionality
│       └── primitive-typography.js # Typography editor functionality
│
├── inc/
│   ├── design-book-router.php  # Routes and data handling
│   └── primitive-sync.php      # Twig → theme.json sync (NEW)
│
├── theme.json                  # Synced FROM primitives (not source)
└── functions.php              # Core theme functions
```

## Primitive Book Structure

### Color Book Example

```twig
{# primitive-books/color-book.twig #}
{# 
  Self-Sufficient Color Primitive
  Contains actual color values, not CSS variable references
#}

{% set color_tokens = {
  colors: {
    'primary-light': '#4d94ff',
    'primary': '#1a73e8',
    'primary-dark': '#1557b0',
    'secondary-light': '#ffd4a3',
    'secondary': '#ff9800',
    'secondary-dark': '#cc7a00',
    'neutral-light': '#f5f5f5',
    'neutral': '#9e9e9e',
    'neutral-dark': '#616161',
    'white': '#ffffff',
    'black': '#000000'
  },
  gradients: {
    'primary-gradient': 'linear-gradient(135deg, #1a73e8 0%, #1557b0 100%)',
    'warm-gradient': 'linear-gradient(135deg, #ff9800 0%, #ff5722 100%)'
  }
} %}

{# Usage in components #}
{% if color %}
  color: {{ color_tokens.colors[color] ?? color }};
{% endif %}
```

### Typography Book Example

```twig
{# primitive-books/typography-book.twig #}
{# 
  Self-Sufficient Typography Primitive
  Contains actual size/weight values
#}

{% set typography_tokens = {
  font_families: {
    primary: 'Montserrat, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
    secondary: 'Inter, system-ui, sans-serif',
    display: 'Playfair Display, serif',
    mono: 'Monaco, Consolas, "Courier New", monospace'
  },
  font_sizes: {
    xs: '0.75rem',    {# 12px #}
    sm: '0.875rem',   {# 14px #}
    md: '1rem',       {# 16px #}
    lg: '1.125rem',   {# 18px #}
    xl: '1.25rem',    {# 20px #}
    '2xl': '1.5rem',  {# 24px #}
    '3xl': '2rem',    {# 32px #}
    '4xl': '2.5rem',  {# 40px #}
    '5xl': '3rem'     {# 48px #}
  },
  font_weights: {
    light: '300',
    regular: '400',
    medium: '500',
    semibold: '600',
    bold: '700',
    extrabold: '800'
  },
  line_heights: {
    tight: '1.2',
    snug: '1.375',
    normal: '1.5',
    relaxed: '1.75',
    loose: '2'
  },
  letter_spacings: {
    tight: '-0.025em',
    normal: '0',
    wide: '0.025em',
    wider: '0.05em',
    widest: '0.1em'
  }
} %}
```

## Creating Self-Sufficient Components

### 1. Basic Element Example

```twig
{# elements/button.twig #}
{% include 'primitive-books/typography-book.twig' %}
{% include 'primitive-books/color-book.twig' %}
{% include 'primitive-books/spacing-book.twig' %}

{% set size = size|default('md') %}
{% set variant = variant|default('primary') %}

<button 
  class="btn btn-{{ variant }} btn-{{ size }}"
  style="
    {# Typography from primitives #}
    font-family: {{ typography_tokens.font_families.primary }};
    font-size: {{ typography_tokens.font_sizes[size] }};
    font-weight: {{ typography_tokens.font_weights.medium }};
    
    {# Colors from primitives #}
    color: {{ variant == 'primary' ? color_tokens.colors.white : color_tokens.colors.primary }};
    background-color: {{ color_tokens.colors[variant] }};
    
    {# Spacing from primitives #}
    padding: {{ spacing_tokens.sizes[size ~ '-y'] }} {{ spacing_tokens.sizes[size ~ '-x'] }};
    
    {# Other properties #}
    border: none;
    border-radius: {{ border_tokens.radius.md }};
    cursor: pointer;
    transition: all 0.2s ease;
  "
  {% if disabled %}disabled{% endif %}
>
  {{ text }}
</button>
```

### 2. Composite Component Example

```twig
{# components/card.twig #}
{% include 'primitive-books/color-book.twig' %}
{% include 'primitive-books/typography-book.twig' %}
{% include 'primitive-books/spacing-book.twig' %}
{% include 'primitive-books/shadow-book.twig' %}

<article 
  class="card"
  style="
    background: {{ color_tokens.colors['white'] }};
    border-radius: {{ border_tokens.radius.lg }};
    padding: {{ spacing_tokens.sizes.xl }};
    box-shadow: {{ shadow_tokens.shadows.md }};
  "
>
  {% if image %}
    <img src="{{ image }}" alt="{{ image_alt }}" style="
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: {{ border_tokens.radius.md }};
      margin-bottom: {{ spacing_tokens.sizes.lg }};
    ">
  {% endif %}
  
  <h3 style="
    font-size: {{ typography_tokens.font_sizes['2xl'] }};
    font-weight: {{ typography_tokens.font_weights.bold }};
    color: {{ color_tokens.colors['neutral-dark'] }};
    margin-bottom: {{ spacing_tokens.sizes.md }};
  ">
    {{ title }}
  </h3>
  
  <p style="
    font-size: {{ typography_tokens.font_sizes.md }};
    line-height: {{ typography_tokens.line_heights.relaxed }};
    color: {{ color_tokens.colors['neutral'] }};
  ">
    {{ content }}
  </p>
  
  {% if cta %}
    {% include 'elements/button.twig' with {
      text: cta.text,
      variant: cta.variant|default('primary'),
      size: 'md'
    } %}
  {% endif %}
</article>
```

## Primitive Editors

The design book editors (colors-editor.twig, typography-editor.twig, etc.) are UI tools for modifying primitive values. They:

1. **Read from primitive Twig files** (not theme.json)
2. **Update primitive Twig files** when values change
3. **Optionally sync to theme.json** for WordPress compatibility
4. **Use traditional CSS** for their own UI (not part of the design system)

All editor styles are consolidated in `design-book-editors.css` for maintainability.

### Editor Data Flow

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────┐
│ Primitive Editor│ ──> │ Primitive Twig   │ ──> │ theme.json  │
│   (UI Tool)    │     │ (Source of Truth)│     │ (Optional)  │
└─────────────────┘     └──────────────────┘     └─────────────┘
         ▲                        │
         │                        │
         └────────────────────────┘
           Live Preview/Feedback
```

## Syncing Mechanism

### 1. Twig → theme.json Sync (One-way)

```php
// inc/primitive-sync.php
class PrimitiveSync {
    /**
     * Sync primitive values to theme.json
     */
    public function sync_to_theme_json($primitive_type, $values) {
        $theme_json = $this->get_theme_json();
        
        switch ($primitive_type) {
            case 'colors':
                $this->sync_colors($theme_json, $values);
                break;
            case 'typography':
                $this->sync_typography($theme_json, $values);
                break;
        }
        
        $this->save_theme_json($theme_json);
    }
    
    /**
     * Read values from Twig primitive files
     */
    public function read_primitive_values($primitive_type) {
        $file = get_template_directory() . "/templates/primitive-books/{$primitive_type}-book.twig";
        // Parse Twig file to extract token values
        // Return as associative array
    }
}
```

### 2. AJAX Handler for Editor Updates

```javascript
// Update primitive and sync
function updatePrimitive(type, token, value) {
    $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'update_primitive',
            type: type,
            token: token,
            value: value,
            nonce: primitiveNonce
        },
        success: function(response) {
            // Update live preview
            updatePreview(type, token, value);
            
            // Optionally sync to theme.json
            if (syncEnabled) {
                syncToThemeJson(type, token, value);
            }
        }
    });
}
```

## Benefits of Self-Sufficient Architecture

1. **True Portability**: Primitives work without WordPress/theme.json
2. **Version Control**: Track actual values in Git, not just references
3. **Framework Agnostic**: Can port to React, Vue, or vanilla HTML
4. **Single Source of Truth**: No confusion about where values come from
5. **Offline Development**: Work without WordPress running
6. **Component Libraries**: Share primitives across projects
7. **Testing**: Easier to test with actual values

## Migration Guide

### Converting Existing Primitives

1. **Identify CSS Variable References**
   ```twig
   {# OLD - Dependent on theme.json #}
   font-size: var(--wp--preset--font-size--large);
   ```

2. **Replace with Actual Values**
   ```twig
   {# NEW - Self-sufficient #}
   font-size: {{ typography_tokens.font_sizes.large }}; {# 1.25rem #}
   ```

3. **Update Component Imports**
   ```twig
   {# Include all needed primitives at the top #}
   {% include 'primitive-books/typography-book.twig' %}
   {% include 'primitive-books/color-book.twig' %}
   ```

4. **Test Independence**
   - Render component without WordPress
   - Verify all styles are applied
   - Check responsive behavior

## Best Practices

1. **Primitive Organization**
   - One concern per primitive (colors, typography, spacing)
   - Use descriptive token names
   - Document token purposes
   - Include usage examples

2. **Component Development**
   - Always include required primitives
   - Use token fallbacks: `{{ token.value|default('1rem') }}`
   - Keep styling inline or in style blocks
   - Avoid external CSS dependencies

3. **Editor Development**
   - Editors are tools, not part of the design system
   - Can use traditional CSS/JS approaches
   - Focus on user experience
   - Provide visual feedback

4. **Sync Strategy**
   - Primary direction: Twig → theme.json
   - Make theme.json sync optional
   - Validate before syncing
   - Keep sync logs

## Troubleshooting

### Styles Not Applying
- Verify primitive is included
- Check token path correctness
- Ensure no typos in token names
- Test token exists in primitive

### Sync Issues
- Check file permissions
- Verify AJAX handlers
- Review browser console
- Test with simple values first

### Performance
- Cache parsed primitives
- Minimize includes in loops
- Use Twig's `include` with `only`
- Consider build process for production

## Future Enhancements

1. **Primitive Parser**: Automated extraction of values from Twig
2. **Visual Token Browser**: See all tokens in one place
3. **Token Relationships**: Define derived values (e.g., `primary-dark` from `primary`)
4. **Build Tools**: Compile primitives for production
5. **Token API**: REST endpoints for external tools
6. **AI Integration**: Generate color schemes and type scales

## Conclusion

The self-sufficient atomic design system represents a paradigm shift in how we think about design systems. By making Twig primitives the source of truth with actual values, we create a truly portable, maintainable, and scalable system that transcends platform limitations.
