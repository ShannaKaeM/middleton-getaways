# TW4 + Windpress Integration Guide
*Generic implementation guide for WordPress projects*

> 📚 **Complete Reference:** See [TW4-COMPLETE-UPDATED-REFERENCE.md](./TW4-COMPLETE-UPDATED-REFERENCE.md) for comprehensive TW4 documentation and [WINDPRESS-COMPLETE-REFERENCE.md](./WINDPRESS-COMPLETE-REFERENCE.md) for complete Windpress documentation.

## 🎯 Integration Overview

This guide shows how to implement Tailwind CSS v4 with Windpress for **any WordPress project**, creating a maintainable design system that works seamlessly with WordPress and page builders.

### Architecture Decisions
- **Windpress** as the primary TW4 integration
- **CSS Variables** as single source of truth
- **Component-based** design system
- **WordPress theme.json** integration for editor consistency

---

## 📋 Implementation Checklist

### Phase 1: Windpress Setup
- [ ] Install Windpress plugin
- [ ] Switch to Tailwind CSS v4
- [ ] Configure main.css with brand tokens
- [ ] Test basic utility classes

### Phase 2: Design System
- [ ] Define brand color palette
- [ ] Set up typography scale
- [ ] Create spacing system
- [ ] Build component utilities

### Phase 3: WordPress Integration
- [ ] Update theme.json configuration
- [ ] Create CSS bridge file
- [ ] Test with Gutenberg blocks
- [ ] Integrate with page builder

### Phase 4: Component Library
- [ ] Build card components
- [ ] Create button variants
- [ ] Design section layouts
- [ ] Implement responsive patterns

---

## 🎨 Brand Configuration

### Windpress main.css Setup
```css
@import "tailwindcss";

@theme {
  /* === BRAND VARIABLES === */
  
  /* Primary Brand Colors */
  --color-primary: #333;
  --color-primary-50: #f7f7f7;
  --color-primary-100: #e5e5e5;
  --color-primary-200: #d3d3d3;
  --color-primary-300: #c2c2c2;
  --color-primary-400: #b1b1b1;
  --color-primary-500: #333;
  --color-primary-600: #444;
  --color-primary-700: #555;
  --color-primary-800: #666;
  --color-primary-900: #777;
  
  /* Secondary Colors */
  --color-secondary: #666;
  --color-secondary-50: #f2f2f2;
  --color-secondary-100: #e2e2e2;
  --color-secondary-200: #d6d6d6;
  --color-secondary-300: #cacaca;
  --color-secondary-400: #b3b3b3;
  --color-secondary-500: #666;
  --color-secondary-600: #777;
  --color-secondary-700: #888;
  --color-secondary-800: #999;
  --color-secondary-900: #aaa;
  
  /* Neutral Colors */
  --color-neutral: #999;
  --color-neutral-50: #f9f9f9;
  --color-neutral-100: #f5f5f5;
  --color-neutral-200: #f0f0f0;
  --color-neutral-300: #e5e5e5;
  --color-neutral-400: #d3d3d3;
  --color-neutral-500: #999;
  --color-neutral-600: #b3b3b3;
  --color-neutral-700: #a6a6a6;
  --color-neutral-800: #959595;
  --color-neutral-900: #868686;
  
  /* Semantic Colors */
  --color-success: #2ecc71;
  --color-warning: #f1c40f;
  --color-error: #e74c3c;
  --color-info: #3498db;
  
  /* Typography */
  --font-display: "Playfair Display", serif;
  --font-body: "Inter", sans-serif;
  --font-mono: "JetBrains Mono", monospace;
  
  /* Font Sizes */
  --font-size-xs: 0.75rem;
  --font-size-sm: 0.875rem;
  --font-size-base: 1rem;
  --font-size-lg: 1.125rem;
  --font-size-xl: 1.25rem;
  --font-size-2xl: 1.5rem;
  --font-size-3xl: 1.875rem;
  --font-size-4xl: 2.25rem;
  --font-size-5xl: 3rem;
  --font-size-6xl: 3.75rem;
  
  /* Spacing Scale */
  --spacing-px: 1px;
  --spacing-0: 0;
  --spacing-1: 0.25rem;
  --spacing-2: 0.5rem;
  --spacing-3: 0.75rem;
  --spacing-4: 1rem;
  --spacing-5: 1.25rem;
  --spacing-6: 1.5rem;
  --spacing-8: 2rem;
  --spacing-10: 2.5rem;
  --spacing-12: 3rem;
  --spacing-16: 4rem;
  --spacing-20: 5rem;
  --spacing-24: 6rem;
  --spacing-32: 8rem;
  
  /* Border Radius */
  --radius-none: 0;
  --radius-sm: 0.125rem;
  --radius-md: 0.375rem;
  --radius-lg: 0.5rem;
  --radius-xl: 0.75rem;
  --radius-2xl: 1rem;
  --radius-full: 9999px;
  
  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  
  /* Breakpoints */
  --breakpoint-sm: 640px;
  --breakpoint-md: 768px;
  --breakpoint-lg: 1024px;
  --breakpoint-xl: 1280px;
  --breakpoint-2xl: 1536px;
}

/* === COMPONENT UTILITIES === */

/* Container System */
@utility mi-container {
  width: 100%;
  max-width: var(--breakpoint-xl);
  margin-left: auto;
  margin-right: auto;
  padding-left: var(--spacing-4);
  padding-right: var(--spacing-4);
}

@utility mi-container-wide {
  width: 100%;
  max-width: var(--breakpoint-2xl);
  margin-left: auto;
  margin-right: auto;
  padding-left: var(--spacing-6);
  padding-right: var(--spacing-6);
}

@utility mi-container-narrow {
  width: 100%;
  max-width: var(--breakpoint-lg);
  margin-left: auto;
  margin-right: auto;
  padding-left: var(--spacing-4);
  padding-right: var(--spacing-4);
}

/* Section Utilities */
@utility mi-section {
  padding-top: var(--spacing-16);
  padding-bottom: var(--spacing-16);
}

@utility mi-section-sm {
  padding-top: var(--spacing-12);
  padding-bottom: var(--spacing-12);
}

@utility mi-section-lg {
  padding-top: var(--spacing-24);
  padding-bottom: var(--spacing-24);
}

/* Card Components */
@utility mi-card {
  display: flex;
  flex-direction: column;
  background-color: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
  transition: all 0.3s ease;
}

@utility mi-card-flat {
  background-color: white;
  border: 1px solid var(--color-neutral-200);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

@utility mi-card-elevated {
  background-color: white;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-xl);
  overflow: hidden;
}

/* Button Components */
@utility mi-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
  border-radius: var(--radius-md);
}

/* Button Sizes */
@utility btn-sm {
  padding: var(--spacing-2) var(--spacing-4);
  font-size: var(--font-size-sm);
}

@utility btn-md {
  padding: var(--spacing-3) var(--spacing-6);
  font-size: var(--font-size-base);
}

@utility btn-lg {
  padding: var(--spacing-4) var(--spacing-8);
  font-size: var(--font-size-lg);
}

/* Button Styles */
@utility btn-primary {
  background-color: var(--color-primary-500);
  color: white;
}

@utility btn-secondary {
  background-color: var(--color-secondary-500);
  color: white;
}

@utility btn-outline {
  background-color: transparent;
  color: var(--color-primary-500);
  border: 2px solid var(--color-primary-500);
}

@utility btn-ghost {
  background-color: transparent;
  color: var(--color-primary-500);
}

/* === CUSTOM VARIANTS === */

/* Hover states */
@custom-variant btn-hover (&:hover);

/* Focus states */
@custom-variant btn-focus (&:focus);

/* Loading states */
@custom-variant btn-loading (&[data-loading="true"]);

/* Card states */
@custom-variant card-hover (&:hover);

/* Theme variants */
@custom-variant theme-light (&[data-theme="light"]);
@custom-variant theme-dark (&[data-theme="dark"]);

/* === HOVER EFFECTS === */

/* Button hover effects */
.mi-btn.btn-primary.btn-hover\:bg-primary-600:hover {
  background-color: var(--color-primary-600);
  transform: translateY(-1px);
}

.mi-btn.btn-secondary.btn-hover\:bg-secondary-600:hover {
  background-color: var(--color-secondary-600);
  transform: translateY(-1px);
}

.mi-btn.btn-outline.btn-hover\:bg-primary-50:hover {
  background-color: var(--color-primary-50);
}

/* Card hover effects */
.mi-card.card-hover\:shadow-xl:hover {
  box-shadow: var(--shadow-xl);
  transform: translateY(-2px);
}

/* Loading states */
.mi-btn.btn-loading\:opacity-50[data-loading="true"] {
  opacity: 0.5;
  cursor: not-allowed;
}
```

---

## 🔗 WordPress theme.json Integration

### Updated theme.json
```json
{
  "version": 3,
  "settings": {
    "color": {
      "palette": [
        {
          "name": "Primary",
          "slug": "primary",
          "color": "var(--color-primary-500)"
        },
        {
          "name": "Secondary",
          "slug": "secondary",
          "color": "var(--color-secondary-500)"
        },
        {
          "name": "Neutral",
          "slug": "neutral",
          "color": "var(--color-neutral-500)"
        },
        {
          "name": "White",
          "slug": "white",
          "color": "#ffffff"
        },
        {
          "name": "Black",
          "slug": "black",
          "color": "#000000"
        }
      ]
    },
    "typography": {
      "fontFamilies": [
        {
          "name": "Display",
          "slug": "display",
          "fontFamily": "var(--font-display)"
        },
        {
          "name": "Body",
          "slug": "body",
          "fontFamily": "var(--font-body)"
        }
      ],
      "fontSizes": [
        {
          "name": "Small",
          "slug": "small",
          "size": "var(--font-size-sm)"
        },
        {
          "name": "Medium",
          "slug": "medium",
          "size": "var(--font-size-base)"
        },
        {
          "name": "Large",
          "slug": "large",
          "size": "var(--font-size-lg)"
        },
        {
          "name": "Extra Large",
          "slug": "x-large",
          "size": "var(--font-size-2xl)"
        }
      ]
    },
    "spacing": {
      "spacingSizes": [
        {
          "name": "Small",
          "slug": "small",
          "size": "var(--spacing-4)"
        },
        {
          "name": "Medium",
          "slug": "medium",
          "size": "var(--spacing-8)"
        },
        {
          "name": "Large",
          "slug": "large",
          "size": "var(--spacing-16)"
        }
      ]
    }
  },
  "styles": {
    "color": {
      "background": "var(--color-neutral-50)",
      "text": "var(--color-neutral-900)"
    },
    "typography": {
      "fontFamily": "var(--font-body)",
      "fontSize": "var(--font-size-base)"
    }
  }
}
```

### CSS Bridge File
Create `/tw4-design-system/gutenstyles-bridge.css`:

```css
/* WordPress Editor Styles Bridge */
/* Maps TW4 variables to WordPress preset variables */

:root {
  /* Color presets */
  --wp--preset--color--primary: var(--color-primary-500);
  --wp--preset--color--secondary: var(--color-secondary-500);
  --wp--preset--color--neutral: var(--color-neutral-500);
  --wp--preset--color--white: #ffffff;
  --wp--preset--color--black: #000000;
  
  /* Font family presets */
  --wp--preset--font-family--display: var(--font-display);
  --wp--preset--font-family--body: var(--font-body);
  
  /* Font size presets */
  --wp--preset--font-size--small: var(--font-size-sm);
  --wp--preset--font-size--medium: var(--font-size-base);
  --wp--preset--font-size--large: var(--font-size-lg);
  --wp--preset--font-size--x-large: var(--font-size-2xl);
  
  /* Spacing presets */
  --wp--preset--spacing--small: var(--spacing-4);
  --wp--preset--spacing--medium: var(--spacing-8);
  --wp--preset--spacing--large: var(--spacing-16);
}

/* Block-specific styles using TW4 variables */
.wp-block-heading {
  font-family: var(--font-display);
}

.wp-block-paragraph {
  font-family: var(--font-body);
}

.wp-block-button__link {
  border-radius: var(--radius-md);
  padding: var(--spacing-3) var(--spacing-6);
  font-weight: 600;
  transition: all 0.2s ease;
}

.wp-block-group {
  border-radius: var(--radius-lg);
}
```

---

## 🧩 GenerateBlocks Integration

### Container Block Examples
```html
<!-- Hero Section -->
<div class="mi-section-lg bg-gradient-to-r from-primary-500 to-primary-600">
  <div class="mi-container">
    <div class="text-center text-white">
      <h1 class="text-5xl font-display font-bold mb-6">
        Hero Section
      </h1>
      <p class="text-xl mb-8 max-w-2xl mx-auto">
        This is a hero section example
      </p>
      <a href="#" class="mi-btn btn-primary btn-lg btn-hover:bg-primary-600">
        Call to Action
      </a>
    </div>
  </div>
</div>

<!-- Property Cards Section -->
<div class="mi-section bg-neutral-50">
  <div class="mi-container">
    <h2 class="text-3xl font-display font-bold text-center mb-12 text-neutral-700">
      Property Cards
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <!-- Property Card 1 -->
      <div class="mi-card card-hover:shadow-xl">
        <img src="property1.jpg" alt="Property" class="w-full h-48 object-cover">
        <div class="p-6">
          <h3 class="text-xl font-display font-bold mb-4 text-neutral-700">
            Property 1
          </h3>
          <p class="mb-6">
            This is a property card example
          </p>
          <div class="flex justify-between items-center">
            <span class="text-2xl font-bold text-secondary-600">$299/night</span>
            <a href="#" class="mi-btn btn-primary btn-sm">View Details</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Call to Action Section -->
<div class="mi-section bg-secondary-500">
  <div class="mi-container-narrow text-center">
    <h2 class="text-3xl font-display font-bold mb-6 text-white">
      Call to Action
    </h2>
    <p class="text-xl mb-8 text-secondary-100">
      This is a call to action section example
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="#" class="mi-btn btn-primary btn-lg btn-hover:bg-primary-600">
        Call to Action
      </a>
      <a href="#" class="mi-btn btn-outline btn-lg btn-hover:bg-secondary-50 border-white text-white">
        Secondary Action
      </a>
    </div>
  </div>
</div>
```

### Button Block Examples
```html
<!-- Primary CTA Button -->
<a href="#call-to-action" class="mi-btn btn-primary btn-lg btn-hover:bg-primary-600 shadow-lg">
  Call to Action
</a>

<!-- Secondary Button -->
<a href="#secondary-action" class="mi-btn btn-secondary btn-md btn-hover:bg-secondary-600">
  Secondary Action
</a>

<!-- Outline Button -->
<a href="#outline-action" class="mi-btn btn-outline btn-md btn-hover:bg-primary-50">
  Outline Action
</a>

<!-- Ghost Button -->
<a href="#ghost-action" class="mi-btn btn-ghost btn-sm">
  Ghost Action
</a>
```

### Heading Block Examples
```html
<!-- Display Heading -->
<h1 class="text-5xl font-display font-bold text-neutral-700 mb-6">
  Display Heading
</h1>

<!-- Section Heading -->
<h2 class="text-3xl font-display font-semibold text-neutral-600 mb-8">
  Section Heading
</h2>

<!-- Card Heading -->
<h3 class="text-xl font-display font-medium text-neutral-700 mb-4">
  Card Heading
</h3>
```

---

## 🎯 Component Usage Patterns

### Card Variations
```html
<!-- Basic Card -->
<div class="mi-card p-6">
  <h3 class="text-lg font-semibold mb-2">Basic Card</h3>
  <p>This is a basic card example</p>
</div>

<!-- Elevated Card with Hover -->
<div class="mi-card-elevated p-8 card-hover:shadow-xl">
  <h3 class="text-xl font-display font-bold mb-4">Elevated Card</h3>
  <p class="mb-6">This is an elevated card example with hover effect</p>
  <a href="#" class="mi-btn btn-primary btn-sm">Action</a>
</div>

<!-- Flat Card -->
<div class="mi-card-flat p-6">
  <h3 class="text-lg font-medium mb-2">Flat Card</h3>
  <p>This is a flat card example</p>
</div>
```

### Responsive Patterns
```html
<!-- Responsive Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
  <!-- Cards here -->
</div>

<!-- Responsive Container -->
<div class="mi-container lg:mi-container-wide">
  <!-- Content adapts container width -->
</div>

<!-- Responsive Section Spacing -->
<div class="mi-section-sm lg:mi-section lg:mi-section-lg">
  <!-- Spacing increases with screen size -->
</div>
```

---

## 🔧 Development Workflow

### 1. Design Token Updates
When updating brand colors or spacing:

1. Modify `@theme` section in Windpress main.css
2. Save and test in Windpress admin
3. Verify CSS variables are available
4. Update theme.json if needed

### 2. Component Development
When creating new components:

1. Define base utility with `@utility`
2. Create variants with additional utilities
3. Add custom variants with `@custom-variant`
4. Test with GenerateBlocks
5. Document usage patterns

### 3. Testing Checklist
- [ ] Classes work in Gutenberg editor
- [ ] Variables available in browser dev tools
- [ ] GenerateBlocks integration working
- [ ] Responsive behavior correct
- [ ] Hover states functioning
- [ ] Performance impact minimal

---

## 📱 Responsive Design Strategy

### Mobile-First Approach
```html
<!-- Base: Mobile styles -->
<div class="mi-card p-4 text-sm">
  <!-- Small screen: compact layout -->
</div>

<!-- Tablet: Enhanced spacing -->
<div class="mi-card p-4 md:p-6 text-sm md:text-base">
  <!-- Medium screen: more breathing room -->
</div>

<!-- Desktop: Full layout -->
<div class="mi-card p-4 md:p-6 lg:p-8 text-sm md:text-base lg:text-lg">
  <!-- Large screen: maximum comfort -->
</div>
```

### Breakpoint Usage
- `sm:` (640px+) - Small tablets, large phones
- `md:` (768px+) - Tablets
- `lg:` (1024px+) - Small desktops
- `xl:` (1280px+) - Large desktops
- `2xl:` (1536px+) - Extra large screens

---

## 🚀 Performance Optimization

### CSS Purging
Windpress automatically removes unused CSS classes in production mode.

### Variable Optimization
Use CSS variables for dynamic values:
```css
/* Good: Uses variables for consistency */
.dynamic-component {
  background: var(--color-primary-500);
  padding: var(--spacing-6);
}

/* Avoid: Hardcoded values */
.static-component {
  background: #333;
  padding: 1.5rem;
}
```

### Loading Strategy
1. Critical CSS inlined
2. Non-critical CSS loaded asynchronously
3. Windpress handles optimization automatically

---

## 🎯 Next Steps

1. **Install and configure Windpress** with the provided main.css
2. **Update theme.json** with the new configuration
3. **Create the CSS bridge file** for editor consistency
4. **Test with GenerateBlocks** using the example patterns
5. **Build your first component** following the utility patterns
6. **Document your custom components** for team consistency

---

*This integration guide provides everything needed to implement a modern, maintainable design system using TW4 and Windpress in any WordPress project.*
