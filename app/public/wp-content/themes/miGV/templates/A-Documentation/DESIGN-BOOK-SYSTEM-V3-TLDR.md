# Design Book System V3 - Developer Guide

## Core Architecture

```
Design Tokens (JSON) → Pre-Configured Components → Data Layer → Rendered Output
                          ↑ AI operates here with constraints
```

## System Philosophy

**Key Insight**: By pre-configuring components with specific token options, we create guardrails that ensure AI (and developers) use the design system correctly. This eliminates randomness, maintains consistency, and makes global updates trivial.

## 1. Design Tokens

### Structure
- **Location**: `/tokens/*.json` 
- **Purpose**: Single source of truth - the ONLY place design values live
- **Files**: `colors.json`, `typography.json`, `spacing.json`, `borders.json`, `shadows.json`

## 2. Pre-Configured Components (The Secret Sauce)

### Component Configuration Pattern

Instead of allowing arbitrary values, components are pre-configured with specific token options:

```json
// components/sm-components/button.json
{
  "allowed_options": {
    "variant": ["primary", "secondary", "outline", "ghost"],
    "size": ["sm", "md", "lg"],
    "color": ["primary", "secondary", "neutral", "danger"],
    "spacing": ["compact", "normal", "relaxed"],
    "radius": ["none", "sm", "md", "lg", "full"]
  },
  "defaults": {
    "variant": "primary",
    "size": "md",
    "spacing": "normal",
    "radius": "md"
  },
  "token_mappings": {
    "primary": {
      "background": "colors.primary.default",
      "hover": "colors.primary.dark",
      "text": "colors.extreme-light"
    },
    "secondary": {
      "background": "colors.secondary.default",
      "hover": "colors.secondary.dark", 
      "text": "colors.extreme-light"
    }
  }
}
```

### Why This Matters

1. **AI Constraint**: AI can only choose from pre-defined options
2. **No Magic Values**: No hardcoded colors, sizes, or spacing
3. **Global Updates**: Change token, update everywhere
4. **Type Safety**: Limited options = fewer bugs
5. **Documentation**: Config IS the documentation

### CMB2 Integration with Constraints

```php
function register_button_field($cmb, $field_id) {
    // Load component config to get allowed options
    $button_config = json_decode(
        file_get_contents(get_template_directory() . '/components/sm-components/button.json'), 
        true
    );
    
    $cmb->add_field([
        'id'      => $field_id . '_variant',
        'type'    => 'select',
        'options' => array_combine(
            $button_config['allowed_options']['variant'],
            array_map('ucfirst', $button_config['allowed_options']['variant'])
        ),
        'default' => $button_config['defaults']['variant'],
        'desc'    => 'AI: Use only these pre-configured variants'
    ]);
    
    $cmb->add_field([
        'id'      => $field_id . '_size',
        'type'    => 'select', 
        'options' => array_combine(
            $button_config['allowed_options']['size'],
            ['Small', 'Medium', 'Large']
        ),
        'default' => $button_config['defaults']['size']
    ]);
}
```

## 3. Component Implementation

### Token-Constrained Template

```twig
{# components/sm-components/button.twig #}
{% set config = load_component_config('button') %}
{% set variant = data.variant|default(config.defaults.variant) %}
{% set size = data.size|default(config.defaults.size) %}

{# Validate against allowed options #}
{% if variant not in config.allowed_options.variant %}
    {% set variant = config.defaults.variant %}
{% endif %}

{# Apply pre-configured token mappings #}
{% set styles = config.token_mappings[variant] %}

<button class="btn btn--{{ variant }} btn--{{ size }}"
        style="
            background-color: var(--{{ styles.background }});
            color: var(--{{ styles.text }});
        "
        data-hover-bg="var(--{{ styles.hover }})">
    {{ data.text }}
</button>
```

## 4. AI Integration Benefits

### Structured Component Generation

When AI generates components, it works within constraints:

```markdown
AI Prompt: "Create a hero section with a call-to-action button"

AI sees button.json config and knows:
- Can only use: primary, secondary, outline, ghost
- Can only use sizes: sm, md, lg
- Cannot invent new variants
- Must reference existing tokens
```

### Example AI-Safe Component

```json
// components/lg-components/hero.json
{
  "allowed_options": {
    "layout": ["centered", "left-aligned", "split"],
    "background": ["none", "gradient", "image", "pattern"],
    "height": ["auto", "medium", "tall", "full"],
    "overlay": ["none", "light", "dark"],
    "spacing": ["compact", "normal", "spacious"]
  },
  "sub_components": {
    "heading": {
      "size": ["display-sm", "display-md", "display-lg"],
      "color": ["primary", "secondary", "neutral-dark", "extreme-light"]
    },
    "button": {
      "component": "sm-components/button",
      "allowed_variants": ["primary", "secondary"]
    }
  }
}
```

## 5. Practical Examples

### Pre-Configured Card Component

```json
// components/md-components/card.json
{
  "allowed_options": {
    "style": ["default", "bordered", "elevated", "flat"],
    "padding": ["none", "sm", "md", "lg"],
    "image_position": ["top", "left", "right", "background"]
  },
  "style_mappings": {
    "default": {
      "shadow": "shadows.elevation-1",
      "border": "none",
      "background": "colors.base-white"
    },
    "bordered": {
      "shadow": "none",
      "border": "borders.width-thin colors.neutral-light",
      "background": "colors.base-white"
    },
    "elevated": {
      "shadow": "shadows.elevation-3",
      "border": "none",
      "background": "colors.base-white"
    }
  }
}
```

### Usage in Templates

```twig
{# AI or developer can only use pre-defined options #}
{% include 'components/md-components/card.twig' with {
    style: 'elevated',      {# ✓ Valid option #}
    padding: 'md',          {# ✓ Valid option #}
    image_position: 'top'   {# ✓ Valid option #}
    {# style: 'fancy'       ✗ Would fallback to default #}
} %}
```

## 6. System Benefits

### For AI
- **Clear Boundaries**: Can't generate invalid styles
- **Predictable Output**: Same input = same output
- **Easy Learning**: Finite set of options to understand

### For Maintenance
- **Single Update Point**: Change token, updates everywhere
- **No Drift**: Components can't slowly diverge from design system
- **Easy Auditing**: Can query which components use which tokens

### For Developers
- **No Decisions**: Options are pre-made
- **Consistency**: Can't accidentally break design system
- **Documentation**: Config files are self-documenting

## 7. Implementation Strategy

### Step 1: Define Token Structure
```json
// tokens/colors.json
{
  "primary": { "light": "#...", "default": "#...", "dark": "#..." },
  "secondary": { "light": "#...", "default": "#...", "dark": "#..." }
}
```

### Step 2: Create Component Config
```json
// components/[size]/[name].json
{
  "allowed_options": { /* finite choices */ },
  "defaults": { /* sensible defaults */ },
  "token_mappings": { /* option → token paths */ }
}
```

### Step 3: Build Constrained Template
```twig
{# Load config, validate options, apply tokens #}
```

### Step 4: Generate CMB2 Fields from Config
```php
// Dynamically create fields based on allowed_options
```

## Quick Reference

### Component Config Structure
```json
{
  "allowed_options": {},  // What can be selected
  "defaults": {},         // Fallback values
  "token_mappings": {},   // How options map to tokens
  "sub_components": {}    // Nested component rules
}
```

### AI Prompt Template
```
Using the component config at /components/[type]/[name].json:
1. Only use options from 'allowed_options'
2. Reference tokens via 'token_mappings'
3. Include all required sub_components
4. Follow the existing pattern
```

That's it. Pre-configured components = constrained choices = consistent system.
