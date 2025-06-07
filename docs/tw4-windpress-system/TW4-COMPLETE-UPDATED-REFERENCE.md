# Tailwind CSS v4 Complete Updated Reference
*Based on comprehensive review of official TW4 documentation*

## 🚀 Key Differences: TW3 vs TW4

### Configuration Approach
**TW3:** JavaScript-based configuration (`tailwind.config.js`)
**TW4:** CSS-first configuration using `@theme`, `@utility`, `@custom-variant`

### Component Patterns
**TW3:** `@layer components` with `@apply`
**TW4:** `@utility` directive for everything (no separate component layer)

---

## 📋 Complete TW4 Directives Reference

### 1. `@import` - Import CSS Files
```css
@import "tailwindcss";
@import "./custom-styles.css";
```

### 2. `@theme` - Define Design Tokens
```css
@theme {
  /* Colors */
  --color-primary: #869648;
  --color-avocado-100: oklch(0.99 0 0);
  --color-avocado-200: oklch(0.98 0.04 113.22);
  
  /* Typography */
  --font-display: "Satoshi", "sans-serif";
  --font-body: "Inter", sans-serif;
  
  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  
  /* Breakpoints */
  --breakpoint-3xl: 120rem;
  
  /* Custom properties */
  --ease-fluid: cubic-bezier(0.3, 0, 0, 1);
  --ease-snappy: cubic-bezier(0.2, 0, 0, 1);
}
```

### 3. `@source` - Explicit Source File Registration
```css
@source "../node_modules/@my-company/ui-lib";
@source "./custom-components/**/*.php";
```

### 4. `@utility` - Custom Utility Classes
```css
/* Simple utility */
@utility content-auto {
  content-visibility: auto;
}

/* Functional utility with theme values */
@utility tab-* {
  tab-size: --value(--tab-size-*);
}

/* Component-style utility */
@utility mi-card {
  display: flex;
  flex-direction: column;
  background-color: white;
  border-radius: var(--radius-lg);
  padding: var(--spacing-6);
  box-shadow: var(--shadow-md);
}
```

### 5. `@variant` - Apply Variants to CSS
```css
.my-element {
  background: white;
  @variant dark {
    background: black;
  }
  @variant hover {
    transform: scale(1.05);
  }
}
```

### 6. `@custom-variant` - Define Custom Variants
```css
/* Data attribute variants */
@custom-variant theme-midnight (&:where([data-theme="midnight"] *));
@custom-variant card-elevated (&[data-elevated="true"]);

/* Complex selectors */
@custom-variant group-focus (&:where(.group:focus *));
@custom-variant peer-checked (&:where(.peer:checked ~ *));

/* Usage */
.theme-midnight\:bg-black {
  background-color: black;
}
```

### 7. `@apply` - Inline Utilities (Legacy Support)
```css
.select2-dropdown {
  @apply rounded-b-lg shadow-md;
}
```

### 8. `@reference` - Reference External Stylesheets
```css
@reference "./base-styles.css";
```

---

## 🎨 Advanced Color System

### Color Definition Patterns
```css
@theme {
  /* Modern color spaces */
  --color-primary: oklch(0.84 0.18 117.33);
  --color-secondary: hsl(210 40% 50%);
  --color-accent: #3b82f6;
  
  /* Color scales */
  --color-brand-50: #f0f9ff;
  --color-brand-100: #e0f2fe;
  --color-brand-500: #0ea5e9;
  --color-brand-900: #0c4a6e;
}
```

### Using Colors in CSS
```css
/* Direct variable reference */
.my-component {
  background-color: var(--color-primary);
  color: var(--color-white);
}

/* With opacity */
.overlay {
  background-color: rgb(from var(--color-primary) r g b / 0.5);
}
```

---

## 🔄 State Management & Variants

### Built-in Pseudo-class Variants
```html
<!-- Hover, focus, active -->
<button class="bg-blue-500 hover:bg-blue-600 focus:ring-2 active:scale-95">
  Button
</button>

<!-- Form states -->
<input class="border-gray-300 focus:border-blue-500 disabled:bg-gray-100 required:border-red-500">

<!-- Child selectors -->
<div class="first:mt-0 last:mb-0 odd:bg-gray-50 even:bg-white">
  Content
</div>
```

### Parent/Sibling State Variants
```html
<!-- Group hover -->
<div class="group">
  <img class="group-hover:scale-110" src="...">
  <h3 class="group-hover:text-blue-600">Title</h3>
</div>

<!-- Peer states -->
<input class="peer" type="checkbox">
<label class="peer-checked:text-blue-600">Label</label>
```

### Arbitrary Variants
```html
<!-- Custom selectors -->
<li class="[&.is-dragging]:cursor-grabbing">Item</li>

<!-- Media queries -->
<div class="[@media(hover:hover)]:hover:bg-gray-100">Hover-capable devices</div>

<!-- Supports queries -->
<div class="[@supports(display:grid)]:grid">Grid if supported</div>

<!-- Complex selectors (use _ for spaces) -->
<div class="[&_p]:mt-4">All p elements inside</div>
```

---

## 📱 Responsive Design

### Standard Breakpoints
```css
/* Default breakpoints */
sm: 640px   /* Small tablets, large phones */
md: 768px   /* Tablets */
lg: 1024px  /* Small desktops */
xl: 1280px  /* Large desktops */
2xl: 1536px /* Extra large screens */
```

### Custom Breakpoints
```css
@theme {
  --breakpoint-xs: 475px;
  --breakpoint-3xl: 1920px;
  --breakpoint-4xl: 2560px;
}
```

### Container Queries (New!)
```html
<!-- Basic container queries -->
<div class="@container">
  <div class="flex flex-col @md:flex-row">
    Content adapts to container size
  </div>
</div>

<!-- Named containers -->
<div class="@container/main">
  <div class="@sm/main:text-lg">
    Targets specific container
  </div>
</div>

<!-- Container query ranges -->
<div class="@container">
  <div class="@sm:@max-md:hidden">
    Only visible in specific container size range
  </div>
</div>

<!-- Arbitrary container sizes -->
<div class="@container">
  <div class="@min-[475px]:block">
    Custom container breakpoint
  </div>
</div>

<!-- Container query units -->
<div class="@container">
  <div class="w-[50cqw] h-[25cqh]">
    50% container width, 25% container height
  </div>
</div>
```

---

## 🌙 Dark Mode

### Automatic Dark Mode (Default)
```html
<div class="bg-white dark:bg-gray-800 text-black dark:text-white">
  Responds to system preference
</div>
```

### Manual Dark Mode Toggle
```css
@import "tailwindcss";

/* Override dark variant for manual control */
@custom-variant dark (&:where(.dark, .dark *));
```

```html
<html class="dark">
  <div class="bg-white dark:bg-black">
    Controlled by .dark class
  </div>
</html>
```

```javascript
// Toggle dark mode
function toggleDarkMode() {
  document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', 
    document.documentElement.classList.contains('dark') ? 'dark' : 'light'
  );
}
```

---

## 🎯 Class Detection & Optimization

### How Classes Are Detected
- Tailwind scans all files as **plain text**
- Looks for tokens that match class name patterns
- Generates CSS for valid utility classes only

### Dynamic Class Names (❌ Don't Do This)
```html
<!-- BAD: Dynamic construction -->
<div class="text-{{ error ? 'red' : 'green' }}-600"></div>
<button class="bg-{{ color }}-500">Button</button>
```

### Static Class Names (✅ Do This)
```html
<!-- GOOD: Complete class names -->
<div class="{{ error ? 'text-red-600' : 'text-green-600' }}"></div>
```

```javascript
// GOOD: Map props to static classes
function Button({ color, children }) {
  const colorVariants = {
    blue: "bg-blue-600 hover:bg-blue-500 text-white",
    red: "bg-red-500 hover:bg-red-400 text-white",
    yellow: "bg-yellow-300 hover:bg-yellow-400 text-black",
  };
  return <button className={`${colorVariants[color]} px-4 py-2 rounded`}>
    {children}
  </button>;
}
```

### File Scanning Rules
**Scanned:**
- All project files
- Template files
- JavaScript/PHP/etc.

**Ignored:**
- `.gitignore` files
- Binary files (images, videos)
- CSS files
- Package manager lock files

### Safelisting Classes
```css
/* Force include specific classes */
@source "path/to/dynamic-content";

/* Or use comments to safelist */
/* 
  Safelist: 
  bg-red-500 bg-green-500 bg-blue-500
  text-red-600 text-green-600 text-blue-600
*/
```

---

## 🧩 Component Patterns in TW4

### Base Component Pattern
```css
@utility mi-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
  border-radius: var(--radius-md);
}

/* Size variants */
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

/* Style variants */
@utility btn-primary {
  background-color: var(--color-primary);
  color: white;
}

@utility btn-secondary {
  background-color: transparent;
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
}

/* State variants */
@custom-variant btn-loading (&[data-loading="true"]);
@custom-variant btn-disabled (&:disabled);
```

### Usage Examples
```html
<!-- Basic button -->
<button class="mi-btn btn-primary btn-md">
  Click Me
</button>

<!-- Button with state -->
<button class="mi-btn btn-secondary btn-lg" data-loading="true">
  Loading...
</button>

<!-- Button with utility overrides -->
<button class="mi-btn btn-primary btn-sm shadow-xl transform hover:scale-105">
  Enhanced Button
</button>
```

### Card Component System
```css
@utility mi-card {
  display: flex;
  flex-direction: column;
  background-color: white;
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: all 0.3s ease;
}

@utility card-flat {
  border: 1px solid var(--color-gray-200);
  box-shadow: none;
}

@utility card-elevated {
  box-shadow: var(--shadow-xl);
}

@utility card-interactive {
  cursor: pointer;
}

@custom-variant card-hover (&:hover);
```

```html
<div class="mi-card card-elevated card-interactive card-hover:shadow-2xl p-6">
  <h3 class="text-lg font-semibold mb-2">Card Title</h3>
  <p class="text-gray-600">Card content goes here</p>
</div>
```

---

## 🎪 Advanced Patterns

### CSS Variable Composition
```css
/* Tailwind uses CSS variables for composable effects */
.blur-sm {
  --tw-blur: blur(var(--blur-sm));
  filter: var(--tw-blur,) var(--tw-brightness,) var(--tw-grayscale,);
}

.grayscale {
  --tw-grayscale: grayscale(100%);
  filter: var(--tw-blur,) var(--tw-brightness,) var(--tw-grayscale,);
}
```

```html
<!-- Multiple filters compose automatically -->
<div class="blur-sm grayscale brightness-75">
  Multiple effects combined
</div>
```

### Theme Switching System
```css
@theme {
  --color-bg-light: white;
  --color-bg-dark: #1a1a1a;
  --color-text-light: #333;
  --color-text-dark: #fff;
}

@custom-variant theme-light (&[data-theme="light"]);
@custom-variant theme-dark (&[data-theme="dark"]);

@utility adaptive-surface {
  background-color: var(--color-bg-light);
  color: var(--color-text-light);
}

/* Theme-specific overrides */
.adaptive-surface.theme-dark\:bg-dark {
  background-color: var(--color-bg-dark);
  color: var(--color-text-dark);
}
```

### Component Variant Architecture
```css
/* Base component */
@utility mi-section {
  padding-top: var(--spacing-16);
  padding-bottom: var(--spacing-16);
}

/* Size variants */
@utility section-sm { padding-top: var(--spacing-8); padding-bottom: var(--spacing-8); }
@utility section-lg { padding-top: var(--spacing-24); padding-bottom: var(--spacing-24); }
@utility section-xl { padding-top: var(--spacing-32); padding-bottom: var(--spacing-32); }

/* Style variants */
@utility section-hero { 
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: white;
}

@utility section-feature {
  background-color: var(--color-gray-50);
}

/* Layout variants */
@utility section-centered {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}
```

---

## 🔧 Functions Reference

### `--alpha()` Function
```css
@utility bg-primary-50 {
  background-color: --alpha(--color-primary, 0.5);
}
```

### `--spacing()` Function
```css
@utility p-custom {
  padding: --spacing(--spacing-md);
}
```

### `--value()` Function
```css
@utility tab-* {
  tab-size: --value(--tab-size-*);
}
```

---

## 📚 Migration from TW3 to TW4

### Configuration Migration
```javascript
// TW3 (tailwind.config.js)
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: '#869648',
      },
      spacing: {
        '18': '4.5rem',
      }
    }
  }
}
```

```css
/* TW4 (main.css) */
@import "tailwindcss";

@theme {
  --color-primary: #869648;
  --spacing-18: 4.5rem;
}
```

### Component Migration
```css
/* TW3 */
@layer components {
  .btn-primary {
    @apply bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600;
  }
}
```

```css
/* TW4 */
@utility btn-primary {
  background-color: var(--color-blue-500);
  color: white;
  padding: var(--spacing-4) var(--spacing-4);
  border-radius: var(--radius-md);
}

/* Hover state handled with variants */
.btn-primary:hover {
  background-color: var(--color-blue-600);
}
```

---

## 🚀 Best Practices

### 1. Design Token Organization
```css
@theme {
  /* Group related tokens */
  
  /* Brand Colors */
  --color-primary: #869648;
  --color-secondary: #d4a574;
  
  /* Semantic Colors */
  --color-success: #10b981;
  --color-error: #ef4444;
  
  /* Typography Scale */
  --font-size-xs: 0.75rem;
  --font-size-sm: 0.875rem;
  --font-size-base: 1rem;
  
  /* Spacing Scale */
  --spacing-1: 0.25rem;
  --spacing-2: 0.5rem;
  --spacing-3: 0.75rem;
}
```

### 2. Component Naming Convention
```css
/* Use consistent prefixes */
@utility mi-card { /* mi = middleton */ }
@utility mi-btn { }
@utility mi-section { }

/* Variant naming */
@utility card-elevated { }
@utility btn-primary { }
@utility section-hero { }
```

### 3. Utility vs Component Decision
**Use `@utility` for:**
- Reusable component patterns
- Multi-property combinations
- Design system components

**Use regular utilities for:**
- Single property changes
- Layout adjustments
- Quick overrides

### 4. Performance Optimization
- Keep class names complete and static
- Use CSS variables for dynamic values
- Leverage automatic purging
- Minimize arbitrary values

---

## 🔗 Official Resources

- [Tailwind CSS v4 Documentation](https://tailwindcss.com/docs)
- [Theme Variables](https://tailwindcss.com/docs/theme)
- [Adding Custom Styles](https://tailwindcss.com/docs/adding-custom-styles)
- [Functions and Directives](https://tailwindcss.com/docs/functions-and-directives)
- [Hover, Focus, and Other States](https://tailwindcss.com/docs/hover-focus-and-other-states)
- [Responsive Design](https://tailwindcss.com/docs/responsive-design)
- [Dark Mode](https://tailwindcss.com/docs/dark-mode)
- [Colors](https://tailwindcss.com/docs/colors)
- [Detecting Classes](https://tailwindcss.com/docs/detecting-classes-in-source-files)

---

*This comprehensive reference covers all major TW4 features based on thorough review of official documentation. Use this as your complete guide to modern CSS-first design systems with Tailwind CSS v4.*
