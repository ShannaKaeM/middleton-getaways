# Windpress Complete Reference
*Comprehensive guide to the Windpress WordPress plugin for TW4 integration*

> 📚 **Related Documentation:** See [TW4-COMPLETE-UPDATED-REFERENCE.md](./TW4-COMPLETE-UPDATED-REFERENCE.md) for complete Tailwind CSS v4 documentation and [INTEGRATION-GUIDE.md](./INTEGRATION-GUIDE.md) for practical implementation.

## 🚀 What is Windpress?

Windpress is the **only** WordPress plugin that provides seamless integration of both Tailwind CSS v3 and v4 without requiring a build process. It compiles Tailwind CSS directly in the browser, making it perfect for WordPress environments including shared hosting.

### Key Benefits
- ✅ **Zero Build Process** - No Node.js, npm, or build tools required
- ✅ **Browser Compilation** - CSS generated in real-time in the browser
- ✅ **TW4 Ready** - Full support for Tailwind CSS v4 features
- ✅ **Universal Compatibility** - Works with any theme, page builder, or plugin
- ✅ **Shared Hosting Friendly** - No server-side requirements
- ✅ **CSS Variables Exposed** - All TW4 variables available for use

---

## 🎯 Core Features

### 1. Dual Version Support
- **Tailwind CSS v3.x** - Traditional JavaScript configuration
- **Tailwind CSS v4.x** - Modern CSS-first configuration with @theme, @utility, @custom-variant

### 2. Page Builder Integrations
**Free Version:**
- Gutenberg (Block Editor)
- GeneratePress
- Kadence WP
- Timber
- Custom Themes/Plugins

**Pro Version:**
- Elementor
- Bricks
- Oxygen
- Breakdance
- Builderius
- LiveCanvas
- Blockstudio
- And more...

### 3. Developer Features
- **Autocompletion** - Intelligent class name suggestions
- **Variable Picker** - Visual selection of theme variables
- **CSS Preview** - Hover to see generated CSS
- **Real-time Compilation** - Instant visual feedback
- **Simple File System** - Easy file management in WordPress admin

---

## ⚙️ Configuration

### Switching Between TW3 and TW4

**Step 1:** Navigate to WindPress admin screen
- WordPress Admin → WindPress

**Step 2:** Go to Settings
- Settings → General

**Step 3:** Select Tailwind CSS Version
- Choose between v3.x or v4.x

**Step 4:** Update main.css file
- Switch to main.css editor tab
- Update configuration based on chosen version

### TW4 Configuration (main.css)
```css
@import "tailwindcss";

@theme {
  /* Colors */
  --color-primary: #869648;
  --color-primary-light: #a0b05e;
  --color-primary-dark: #5a6b2e;
  --color-secondary: #d4a574;
  --color-neutral: #b5b09f;
  
  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;
  --spacing-2xl: 2.5rem;
  
  /* Typography */
  --font-display: "Playfair Display", serif;
  --font-body: "Inter", sans-serif;
  
  /* Custom breakpoints */
  --breakpoint-tablet: 768px;
  --breakpoint-desktop: 1024px;
  --breakpoint-wide: 1400px;
}

/* Custom utilities */
@utility mi-container {
  width: 100%;
  max-width: var(--breakpoint-wide);
  margin-left: auto;
  margin-right: auto;
  padding-left: var(--spacing-md);
  padding-right: var(--spacing-md);
}

@utility mi-card {
  display: flex;
  flex-direction: column;
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: all 0.2s ease;
}

/* Component variants */
@utility card-style-1 {
  background: white;
  border: 1px solid var(--color-gray-200);
  box-shadow: var(--shadow-sm);
}

@utility card-style-2 {
  background: var(--color-primary);
  color: white;
  box-shadow: var(--shadow-lg);
}

/* Custom variants */
@custom-variant card-elevated (&[data-elevated="true"]);
@custom-variant theme-dark (&[data-theme="dark"]);
```

### TW3 Configuration (tailwind.config.js)
```javascript
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          light: '#a0b05e',
          DEFAULT: '#869648',
          dark: '#5a6b2e',
        },
        secondary: '#d4a574',
      },
      spacing: {
        'xs': '0.25rem',
        'sm': '0.5rem',
        'md': '1rem',
        'lg': '1.5rem',
        'xl': '2rem',
        '2xl': '2.5rem',
      },
      fontFamily: {
        'display': ['Playfair Display', 'serif'],
        'body': ['Inter', 'sans-serif'],
      }
    }
  },
  plugins: []
}
```

---

## 🎨 CSS Variables in TW4

### Automatic Variable Exposure
Windpress automatically exposes all Tailwind utilities as CSS variables when using TW4:

```css
/* All these are automatically available */
var(--color-primary)
var(--color-primary-light)
var(--color-primary-dark)
var(--spacing-xs)
var(--spacing-sm)
var(--font-size-xl)
var(--shadow-lg)
var(--radius-md)
```

### Using Variables in WordPress
```php
// In theme.json
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
    }
  }
}
```

```css
/* In custom CSS */
.my-component {
  background-color: var(--color-primary);
  padding: var(--spacing-lg);
  border-radius: var(--radius-md);
}
```

---

## 🔧 Page Builder Integration

### Gutenberg Integration
Windpress adds a "Plain Classes" input field to every Gutenberg block:

```html
<!-- Add classes directly in the block settings -->
<div class="mi-card card-style-1 p-6 shadow-lg">
  Block content here
</div>
```

**Features:**
- Real-time preview
- Class autocompletion
- CSS hover preview
- Variable picker for TW4

### GenerateBlocks Integration
Add Tailwind classes in the "Additional CSS Classes" field:

```html
<!-- Container Block -->
<div class="mi-section section-hero bg-gradient-to-r from-primary to-primary-dark">
  <!-- Inner Container -->
  <div class="mi-container py-20">
    <!-- Content -->
    <div class="mi-card card-style-1 p-8 max-w-2xl mx-auto">
      <h1 class="text-4xl font-bold mb-6">Hero Title</h1>
      <p class="text-lg mb-8">Hero description</p>
      <button class="mi-btn btn-primary btn-lg">Get Started</button>
    </div>
  </div>
</div>
```

### Bricks Integration (Pro)
```html
<!-- In Bricks element settings -->
<div class="mi-card card-style-2 p-6 card-elevated:shadow-2xl" data-elevated="true">
  Content
</div>
```

**Pro Features:**
- Advanced autocompletion
- Variable picker panel
- CSS preview on hover
- HTML to native element conversion

---

## 🎪 Advanced Patterns

### Component Variant System
```css
/* Base component */
@utility mi-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

/* Size variants */
@utility btn-sm {
  padding: var(--spacing-2) var(--spacing-3);
  font-size: var(--font-size-sm);
}

@utility btn-md {
  padding: var(--spacing-3) var(--spacing-4);
  font-size: var(--font-size-base);
}

@utility btn-lg {
  padding: var(--spacing-4) var(--spacing-6);
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

/* Custom variants */
@custom-variant btn-loading (&[data-loading="true"]);
@custom-variant btn-disabled (&:disabled);

/* Variant-specific styles */
.mi-btn.btn-loading\:opacity-50 {
  opacity: 0.5;
  cursor: not-allowed;
}
```

### Usage Examples
```html
<!-- Basic button -->
<button class="mi-btn btn-primary btn-md">Click Me</button>

<!-- Button with state -->
<button class="mi-btn btn-secondary btn-lg btn-loading:opacity-50" data-loading="true">
  Loading...
</button>

<!-- Button with utility overrides -->
<button class="mi-btn btn-primary btn-sm shadow-xl transform hover:scale-105">
  Enhanced Button
</button>
```

### Theme Switching
```css
@theme {
  --color-bg-light: white;
  --color-bg-dark: #1a1a1a;
  --color-text-light: #333;
  --color-text-dark: #fff;
}

@custom-variant theme-light (&[data-theme="light"]);
@custom-variant theme-dark (&[data-theme="dark"]);

@utility adaptive-bg {
  background-color: var(--color-bg-light);
  color: var(--color-text-light);
}

.adaptive-bg.theme-dark\:bg-dark {
  background-color: var(--color-bg-dark);
  color: var(--color-text-dark);
}
```

```html
<div class="adaptive-bg theme-dark:bg-dark p-8" data-theme="dark">
  This adapts to theme changes
</div>
```

---

## 🔗 WordPress Integration Strategies

### Strategy 1: Pure Windpress (Recommended)
Use Windpress as the single source of truth for all Tailwind CSS:

```css
/* main.css in Windpress */
@import "tailwindcss";

@theme {
  --color-primary: #869648;
  --spacing-md: 1rem;
}

@utility mi-card {
  background: white;
  border-radius: var(--radius-lg);
  padding: var(--spacing-md);
}
```

**Benefits:**
- Single configuration location
- All TW4 features available
- CSS variables automatically exposed
- No build process needed

### Strategy 2: Windpress + theme.json Bridge
Combine Windpress with WordPress theme.json:

```json
{
  "version": 3,
  "settings": {
    "color": {
      "palette": [
        {
          "name": "Primary",
          "slug": "primary",
          "color": "var(--color-primary)"
        }
      ]
    }
  }
}
```

```css
/* Bridge CSS */
:root {
  --wp--preset--color--primary: var(--color-primary);
  --wp--preset--spacing--md: var(--spacing-md);
}
```

### Strategy 3: Windpress + Theme Variable Override
Override existing theme variables with Windpress variables:

```css
/* In Windpress main.css */
@theme {
  --color-primary: #869648;
}

/* Override theme variables */
:root {
  --theme-primary-color: var(--color-primary);
  --blocksy-primary-color: var(--color-primary);
  --generatepress-primary: var(--color-primary);
}
```

---

## 🚀 Performance & Optimization

### Caching System
Windpress generates optimized CSS files that are cached for performance:

- **Development Mode**: Real-time compilation for instant feedback
- **Production Mode**: Cached CSS files for optimal performance
- **Purging**: Automatic removal of unused CSS classes

### Best Practices
1. **Use semantic naming** in @theme definitions
2. **Group related utilities** with @utility directive
3. **Leverage custom variants** for state management
4. **Minimize inline styles** - prefer utility classes
5. **Use CSS variables** for dynamic theming

---

## 🛠️ Troubleshooting

### Common Issues

**Classes not working:**
- Check if Windpress is scanning the correct files
- Verify class names in the generated CSS
- Ensure proper syntax in main.css

**Variables not available:**
- Confirm TW4 is selected in settings
- Check @theme definitions in main.css
- Verify CSS variable syntax: `var(--variable-name)`

**Page builder integration issues:**
- Ensure correct input field usage
- Check for conflicting CSS
- Verify plugin compatibility

### Debug Mode
Enable debug mode in Windpress settings to:
- See compilation errors
- View generated CSS
- Monitor file scanning
- Check variable definitions

---

## 📚 Resources

### Official Links
- [Windpress Website](https://wind.press/)
- [Documentation](https://wind.press/docs)
- [WordPress Plugin](https://wordpress.org/plugins/windpress/)
- [Support](https://wind.press/go/ticket)

### Community
- [Discord](https://wind.press/go/discord)
- [Facebook Group](https://wind.press/go/facebook)
- [GitHub](https://wind.press/go/github)

### Pricing
- **Free**: All features, limited integrations, unlimited sites
- **Pro Single**: $49/year, all integrations, 1 site
- **Pro Unlimited**: $99/year, all integrations, unlimited sites
- **Pro Lifetime**: $199, all integrations, unlimited sites, lifetime updates

---

## 🎯 Next Steps

1. **Install Windpress** from WordPress.org
2. **Choose TW4** in settings for modern features
3. **Configure main.css** with your design tokens
4. **Test with page builders** using Additional CSS Classes
5. **Build component system** using @utility and @custom-variant
6. **Integrate with theme.json** if needed
7. **Optimize for production** with caching enabled

---

*This reference covers everything needed to use Windpress effectively with Tailwind CSS v4 in WordPress. Combined with the TW4 Complete Reference, you have a comprehensive guide to modern CSS-first design systems in WordPress.*
