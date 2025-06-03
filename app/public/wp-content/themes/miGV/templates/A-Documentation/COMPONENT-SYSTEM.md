# miGV Atomic Design System - Self-Sufficient Architecture

## Core Architecture Principle

The miGV theme implements a **self-sufficient atomic design system** where primitives are the single source of truth. The system uses a JSON-based architecture that provides data/presentation separation and programmatic updates.

## JSON-Based Primitive Architecture (Current Implementation)

### Overview

The design system uses JSON files as the single source of truth for all primitive values. This approach provides:

1. **Clean Data/Presentation Separation**: JSON files store pure data, Twig templates handle rendering
2. **Easy Programmatic Updates**: AJAX saves write directly to JSON files
3. **Version Control Friendly**: Clear diffs show exactly what changed
4. **Language Agnostic**: Can be consumed by PHP, JavaScript, or any other tool
5. **Direct Bidirectional Sync**: Enables seamless sync with theme.json
6. **Dynamic Data Handling**: Supports runtime modifications and live preview

### File Structure

```
/primitives/                    # JSON primitive data files
├── colors.json                # Color tokens
├── typography.json            # Typography tokens
├── spacing.json              # Spacing tokens (to be created)
├── borders.json              # Border tokens (to be created)
└── shadows.json              # Shadow tokens (to be created)

/templates/primitive-books/    # Twig templates that consume JSON
├── color-book.twig           # Renders color styles
├── typography-book.twig      # Renders typography styles
└── ...                       # Other primitive renderers

/templates/components/         # Components that use primitive books
├── button.twig               # Button component
├── ...                       # Other components
```

### Implementation Details

#### 1. Loading Primitives in Twig

A custom Timber function `load_primitive()` loads JSON data:

```php
// functions.php
add_filter('timber/twig', function($twig) {
    $twig->addFunction(new \Twig\TwigFunction('load_primitive', function($name) {
        $json_path = get_template_directory() . "/primitives/{$name}.json";
        
        if (!file_exists($json_path)) {
            return null;
        }
        
        $json_content = file_get_contents($json_path);
        return json_decode($json_content, true);
    }));
    
    return $twig;
});
```

Usage in Twig:
```twig
{# Load typography tokens from JSON #}
{% set typography_tokens = load_primitive('typography') %}

{# Use the tokens #}
<p style="font-size: {{ typography_tokens.font_sizes.large }};">
```

#### 2. JSON Primitive Structure

Example `typography.json`:
```json
{
  "font_families": {
    "montserrat": "Montserrat, -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif",
    "inter": "Inter, system-ui, sans-serif",
    "playfair-display": "Playfair Display, serif",
    "roboto": "Roboto, sans-serif"
  },
  "font_sizes": {
    "small": "0.8125rem",
    "medium": "1rem",
    "large": "1.25rem",
    "x-large": "1.5rem",
    "xx-large": "2rem",
    "huge": "6.25rem"
  },
  "font_weights": {
    "regular": "400",
    "medium": "500",
    "semiBold": "600",
    "bold": "700",
    "extraBold": "800"
  },
  "line_heights": {
    "tight": "1.1",
    "normal": "1.2",
    "relaxed": "1.4",
    "loose": "1.6"
  },
  "letter_spacings": {
    "tight": "-0.025em",
    "normal": "0",
    "wide": "0.025em",
    "wider": "0.05em",
    "widest": "0.1em"
  }
}
```

#### 3. Primitive Book Templates

Primitive books load JSON data and apply styles based on parameters:

```twig
{# typography-book.twig #}
{# Load typography tokens from JSON #}
{% set typography_tokens = load_primitive('typography') %}

{# Apply typography styles based on parameters #}
{% if font_family %}
  font-family: {{ typography_tokens.font_families[font_family] ?? font_family }};
{% endif %}

{% if font_size %}
  font-size: {{ typography_tokens.font_sizes[font_size] ?? font_size }};
{% endif %}

{% if font_weight %}
  font-weight: {{ typography_tokens.font_weights[font_weight] ?? font_weight }};
{% endif %}

{% if line_height %}
  line-height: {{ typography_tokens.line_heights[line_height] ?? line_height }};
{% endif %}

{% if letter_spacing %}
  letter-spacing: {{ typography_tokens.letter_spacings[letter_spacing] ?? letter_spacing }};
{% endif %}
```

## Implementation Status

### Completed
- **JSON Primitive Architecture** - Core JSON loading system via Timber
- **Typography JSON Primitive** - Complete typography tokens in JSON
- **Colors JSON Primitive** - Complete color tokens in JSON
- **Primitive Loader Function** - `load_primitive()` Timber function

### In Progress
- **Spacing JSON Primitive** - Converting to JSON format
- **Borders JSON Primitive** - Converting to JSON format
- **Shadows JSON Primitive** - Converting to JSON format
- **Theme.json Sync** - Bidirectional sync mechanism

### Planned
- **Import/Export** - JSON primitive packages
- **Version Control** - Track primitive changes with history
- **Validation** - JSON schema validation
- **Caching** - Performance optimization for JSON loading

## Design System Tools

The design system is supported by visual editing tools that allow for real-time token management. These tools are documented separately in [DESIGN-BOOK-EDITOR-SYSTEM.md](./DESIGN-BOOK-EDITOR-SYSTEM.md).

Key points about the relationship:
- Editors are **tools** for managing primitives, not part of the design system itself
- They load from and save to the same JSON primitives used by components
- Editor UI styles are completely separate from design system tokens
- The design system can function independently without the editors

## Security Considerations

1. **File Path Validation**: JSON files only loaded from `/primitives/` directory
2. **JSON Structure**: Validate structure before use in components
3. **Build Process**: Consider sanitizing JSON during build for production

## Best Practices

1. **JSON Organization**
   - One concern per JSON file (colors, typography, spacing)
   - Use descriptive token names
   - Keep structure flat when possible
   - Include comments via separate documentation

2. **Component Development**
   - Load primitives at component top
   - Use token fallbacks: `{{ token.value ?? 'default' }}`
   - Keep styling inline or in style blocks
   - Cache loaded primitives in variables

3. **Performance**
   - Cache JSON reads when possible
   - Minimize JSON file sizes
   - Consider build process for production
   - Use Twig's `{% cache %}` for complex components

## Troubleshooting

### JSON Not Loading
- Verify file exists in `/primitives/` directory
- Check file permissions (readable by web server)
- Validate JSON syntax
- Check PHP error logs

### Styles Not Applying
- Confirm primitive is loaded
- Check token path correctness
- Verify fallback values work
- Test in isolation

## Future Enhancements

1. **JSON Schema Validation**: Ensure JSON structure consistency
2. **Visual Token Browser**: See all tokens across all primitives
3. **Token Relationships**: Define derived values (e.g., color variations)
4. **Build Tools Integration**: Compile JSON to CSS/SCSS
5. **Token API**: REST endpoints for external tools
6. **Version History**: Track changes with rollback capability
7. **Multi-theme Support**: Share primitives across themes

## Conclusion

The JSON-based primitive architecture represents the evolution of our self-sufficient design system. By separating data (JSON) from presentation (Twig), we've created a more maintainable, scalable, and tool-friendly system that maintains the benefits of self-sufficiency while adding programmatic flexibility.
