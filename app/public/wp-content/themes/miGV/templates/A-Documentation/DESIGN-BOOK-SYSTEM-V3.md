# Middleton Getaways Design Book System - V3

This document outlines the complete architecture for the Middleton Getaways WordPress theme's design system, including the data layer for dynamic and static content. It builds upon V2 by adding comprehensive data handling strategies while maintaining the atomic design approach.

## Table of Contents

1. [Core Architecture](#1-core-architecture)
2. [Primitive Globals](#2-primitive-globals)
3. [Components](#3-components)
4. [Data Layer](#4-data-layer)
5. [Content Strategies](#5-content-strategies)
6. [Implementation Examples](#6-implementation-examples)
7. [Best Practices](#7-best-practices)
8. [Architecture Principles](#8-architecture-principles)

## 1. Core Architecture

The Design Book System is built on three foundational pillars:

1. **Primitive Globals** - Design tokens (colors, typography, spacing, etc.)
2. **Components** - Reusable UI elements built from primitives
3. **Data Layer** - Content management through CMB2, Timber/Twig, and JSON

### System Flow

```
Primitives (JSON) → Components (Twig) → Data (CMB2/JSON) → Rendered Output
```

## 2. Primitive Globals

### 2.1. Structure and Location

- **JSON Files:** `app/public/wp-content/themes/miGV/primitives/`
  - `colors.json`, `typography.json`, `spacing.json`, `borders.json`, `shadows.json`
- **Primitive Books:** `app/public/wp-content/themes/miGV/templates/primitive-books/`
  - Twig templates that consume JSON primitives and output CSS properties

### 2.2. Usage

```twig
{# Load primitives #}
{% set colors = load_primitive('colors') %}
{% set typography = load_primitive('typography') %}

{# Apply via primitive books #}
{% include 'primitive-books/color-book.twig' with { 
  background: 'primary', 
  color: 'extreme-light' 
} %}
```

## 3. Components

### 3.1. Component Hierarchy

Components are organized by size and complexity:

- **Small Components (sm-components):** Atoms/Elements
  - Buttons, icons, text elements, inputs
- **Medium Components (md-components):** Molecules/Groups
  - Cards, form groups, media objects
- **Large Components (lg-components):** Organisms/Sections
  - Heroes, headers, footers, content sections

### 3.2. Folder Structure

```
components/
├── sm-components/
│   ├── button.json         # Component configuration
│   └── text.json
├── md-components/
│   ├── card.json
│   └── form-group.json
└── lg-components/
    ├── hero.json
    └── content-section.json

templates/components/
├── sm-components/
│   ├── button.twig         # Component template
│   └── text.twig
├── md-components/
│   └── card.twig
└── lg-components/
    └── hero.twig
```

## 4. Data Layer

The data layer provides flexible content management through multiple strategies:

### 4.1. Dynamic Data (CMB2 + Timber/Twig)

#### CMB2 Field Registration

```php
// functions.php or component registration file
function register_hero_fields() {
    $cmb = new_cmb2_box([
        'id'            => 'hero_section',
        'title'         => 'Hero Section',
        'object_types'  => ['page'],
        'context'       => 'normal',
        'priority'      => 'high',
    ]);

    $cmb->add_field([
        'name'    => 'Hero Title',
        'id'      => 'hero_title',
        'type'    => 'text',
        'default' => 'Welcome to Our Site',
    ]);

    $cmb->add_field([
        'name'    => 'Hero Image',
        'id'      => 'hero_image',
        'type'    => 'file',
        'options' => ['url' => false],
        'query_args' => [
            'type' => 'image',
        ],
    ]);

    $cmb->add_field([
        'name'    => 'Button Text',
        'id'      => 'hero_button_text',
        'type'    => 'text',
        'default' => 'Learn More',
    ]);

    $cmb->add_field([
        'name'    => 'Button Style',
        'id'      => 'hero_button_style',
        'type'    => 'select',
        'options' => [
            'primary'   => 'Primary',
            'secondary' => 'Secondary',
            'outline'   => 'Outline',
        ],
        'default' => 'primary',
    ]);
}
add_action('cmb2_admin_init', 'register_hero_fields');
```

#### Timber Context Setup

```php
// page.php or template file
$context = Timber::context();
$post = new Timber\Post();

// Add CMB2 data to context
$context['hero'] = [
    'title' => get_post_meta($post->ID, 'hero_title', true),
    'image' => get_post_meta($post->ID, 'hero_image_id', true),
    'button' => [
        'text' => get_post_meta($post->ID, 'hero_button_text', true),
        'style' => get_post_meta($post->ID, 'hero_button_style', true),
        'url' => get_post_meta($post->ID, 'hero_button_url', true),
    ]
];

// Alternative: Auto-load all CMB2 fields
$context['fields'] = get_post_meta($post->ID);

Timber::render('page.twig', $context);
```

### 4.2. Static Data (JSON)

#### Content JSON Structure

```json
// data/hero-content.json
{
  "default": {
    "title": "Welcome to Middleton Getaways",
    "subtitle": "Your Perfect Vacation Destination",
    "image": "/assets/images/hero-default.jpg",
    "button": {
      "text": "Explore Rentals",
      "url": "/rentals",
      "style": "primary"
    }
  },
  "seasonal": {
    "summer": {
      "title": "Summer Escapes Await",
      "subtitle": "Book Your Beach Getaway",
      "image": "/assets/images/hero-summer.jpg"
    },
    "winter": {
      "title": "Cozy Winter Retreats",
      "subtitle": "Mountain Cabins Available",
      "image": "/assets/images/hero-winter.jpg"
    }
  }
}
```

#### Loading Static Content

```php
// Custom function to load content JSON
function load_content_json($filename) {
    $file_path = get_template_directory() . '/data/' . $filename . '.json';
    if (file_exists($file_path)) {
        $json_content = file_get_contents($file_path);
        return json_decode($json_content, true);
    }
    return null;
}

// In Timber context
$context['hero_content'] = load_content_json('hero-content');
```

### 4.3. Hybrid Approach (CMB2 + JSON Fallbacks)

```php
// Combine dynamic and static data with fallbacks
function get_hero_data($post_id) {
    // Load static defaults
    $static_data = load_content_json('hero-content')['default'] ?? [];
    
    // Get CMB2 overrides
    $dynamic_data = [
        'title' => get_post_meta($post_id, 'hero_title', true),
        'subtitle' => get_post_meta($post_id, 'hero_subtitle', true),
        'image' => get_post_meta($post_id, 'hero_image_id', true),
    ];
    
    // Merge with dynamic data taking precedence
    return array_merge($static_data, array_filter($dynamic_data));
}
```

## 5. Content Strategies

### 5.1. Page-Specific Content (CMB2)

Best for content that changes per page:

```php
// Register repeatable field group for features
$group_field_id = $cmb->add_field([
    'id'          => 'features_group',
    'type'        => 'group',
    'repeatable'  => true,
    'options'     => [
        'group_title'   => 'Feature {#}',
        'add_button'    => 'Add Feature',
        'remove_button' => 'Remove Feature',
        'sortable'      => true,
    ],
]);

$cmb->add_group_field($group_field_id, [
    'name' => 'Feature Title',
    'id'   => 'title',
    'type' => 'text',
]);

$cmb->add_group_field($group_field_id, [
    'name' => 'Feature Icon',
    'id'   => 'icon',
    'type' => 'select',
    'options' => [
        'wifi' => 'WiFi',
        'parking' => 'Parking',
        'pool' => 'Pool',
        'gym' => 'Gym',
    ],
]);
```

### 5.2. Global Content (JSON + Options)

Best for site-wide content:

```json
// data/global-content.json
{
  "company": {
    "name": "Middleton Getaways",
    "tagline": "Your Home Away From Home",
    "phone": "555-0123",
    "email": "info@middletongetaways.com"
  },
  "social": {
    "facebook": "https://facebook.com/middletongetaways",
    "instagram": "https://instagram.com/middletongetaways",
    "twitter": "https://twitter.com/middletongetaways"
  }
}
```

### 5.3. Component-Specific Content

```json
// components/lg-components/testimonials.json
{
  "config": {
    "display_count": 3,
    "rotation_speed": 5000,
    "show_navigation": true
  },
  "content": {
    "title": "What Our Guests Say",
    "subtitle": "Real experiences from real travelers"
  },
  "items": [
    {
      "author": "Jane Doe",
      "location": "New York, NY",
      "rating": 5,
      "text": "Amazing stay! The property was exactly as described.",
      "date": "2024-01-15"
    }
  ]
}
```

## 6. Implementation Examples

### 6.1. Hero Component with Dynamic Data

```twig
{# templates/components/lg-components/hero.twig #}
{% set hero_data = hero|default(hero_content.default) %}

<section class="hero" style="
  {% include 'primitive-books/spacing-book.twig' with {
    padding_y: 'section-lg'
  } %}
">
  {% if hero_data.image %}
    <div class="hero__background">
      <img src="{{ Image(hero_data.image).src }}" alt="{{ hero_data.title }}">
    </div>
  {% endif %}
  
  <div class="hero__content">
    <h1 style="
      {% include 'primitive-books/typography-book.twig' with {
        font_size: 'xx-large',
        font_weight: 'bold'
      } %}
    ">{{ hero_data.title }}</h1>
    
    {% if hero_data.subtitle %}
      <p style="
        {% include 'primitive-books/typography-book.twig' with {
          font_size: 'large'
        } %}
      ">{{ hero_data.subtitle }}</p>
    {% endif %}
    
    {% if hero_data.button %}
      {% include 'components/sm-components/button.twig' with {
        text: hero_data.button.text,
        url: hero_data.button.url,
        style: hero_data.button.style
      } %}
    {% endif %}
  </div>
</section>
```

### 6.2. Card Component with Mixed Data

```twig
{# templates/components/md-components/card.twig #}
{# Can accept data from CMB2, JSON, or direct parameters #}

{% set card_styles = load_component('md-components/card') %}
{% set card_data = data|default({}) %}

<article class="card" style="
  {% include 'primitive-books/border-book.twig' with {
    radius: card_styles.border_radius|default('md'),
    width: 'thin',
    color: 'neutral-light'
  } %}
  {% include 'primitive-books/shadow-book.twig' with {
    elevation: card_styles.shadow|default('2')
  } %}
">
  {% if card_data.image or image %}
    <div class="card__image">
      <img src="{{ Image(card_data.image|default(image)).src }}" 
           alt="{{ card_data.title|default(title) }}">
    </div>
  {% endif %}
  
  <div class="card__content" style="
    {% include 'primitive-books/spacing-book.twig' with {
      padding: card_styles.content_padding|default('lg')
    } %}
  ">
    <h3>{{ card_data.title|default(title) }}</h3>
    <p>{{ card_data.description|default(description) }}</p>
    
    {% if card_data.link or link %}
      {% include 'components/sm-components/button.twig' with {
        text: card_data.link_text|default(link_text)|default('Read More'),
        url: card_data.link|default(link),
        style: 'outline'
      } %}
    {% endif %}
  </div>
</article>
```

### 6.3. Dynamic Component Loader

```php
// Helper function to load component with data
function render_component($component_path, $data = [], $context = []) {
    $timber_context = array_merge(Timber::context(), $context, ['data' => $data]);
    
    // Load component configuration if exists
    $config_path = get_template_directory() . '/components/' . $component_path . '.json';
    if (file_exists($config_path)) {
        $timber_context['config'] = json_decode(file_get_contents($config_path), true);
    }
    
    // Load component-specific content if exists
    $content_path = get_template_directory() . '/data/' . $component_path . '-content.json';
    if (file_exists($content_path)) {
        $timber_context['content'] = json_decode(file_get_contents($content_path), true);
    }
    
    return Timber::compile('components/' . $component_path . '.twig', $timber_context);
}

// Usage in template
echo render_component('lg-components/hero', [
    'title' => get_field('custom_hero_title') ?: 'Default Title',
    'image' => get_field('custom_hero_image')
]);
```

## 7. Best Practices

### 7.1. Data Organization

1. **CMB2 Fields**
   - Use for page-specific, user-editable content
   - Group related fields logically
   - Provide sensible defaults
   - Use appropriate field types (file for images, select for predefined options)

2. **JSON Files**
   - Use for static, rarely-changing content
   - Organize by component or content type
   - Include versioning in filename if needed
   - Validate JSON structure before deployment

3. **Hybrid Approach**
   - CMB2 for overrides, JSON for defaults
   - Clear precedence rules (dynamic > static)
   - Document which approach is used where

### 7.2. Component Design

1. **Data Flexibility**
   - Accept data from multiple sources
   - Use sensible parameter defaults
   - Don't hardcode content in templates

2. **Separation of Concerns**
   - Components handle presentation
   - Data layer handles content
   - Primitives handle styling

3. **Reusability**
   - Design components to work with various data shapes
   - Use consistent parameter naming
   - Document expected data structure

### 7.3. Performance Considerations

1. **Caching**
   - Cache JSON file reads
   - Use WordPress transients for processed data
   - Implement Timber cache for complex components

2. **Lazy Loading**
   - Load JSON files only when needed
   - Use WordPress lazy loading for images
   - Defer non-critical component rendering

## 8. Architecture Principles

1. **Single Source of Truth**
   - Primitives: JSON files in `/primitives/`
   - Dynamic content: CMB2 fields in database
   - Static content: JSON files in `/data/`

2. **Progressive Enhancement**
   - Start with primitives
   - Build components
   - Add data layer
   - Enhance with interactivity

3. **Flexibility**
   - Components work with multiple data sources
   - Fallback chains ensure content availability
   - System functions without any single part

4. **Maintainability**
   - Clear file organization
   - Consistent naming conventions
   - Comprehensive documentation
   - Version control friendly

5. **Scalability**
   - Modular architecture supports growth
   - Performance optimizations built-in
   - Easy to add new components or data sources

## Conclusion

The Design Book System V3 provides a complete solution for building maintainable, scalable WordPress themes by:

- **Establishing clear separation** between design tokens, components, and content
- **Providing flexible data management** through CMB2, JSON, and hybrid approaches
- **Enabling both dynamic and static content** strategies
- **Supporting progressive enhancement** and graceful degradation
- **Maintaining consistency** through centralized primitives

This architecture ensures that designers can work with visual tools, developers can build efficiently, and content editors can manage content easily, all while maintaining a cohesive system.
