# Middleton Getaways Design System Guide
*Complete implementation guide for the MI Design System*

## 📚 Documentation References

### Generic Documentation (Reusable)
> **Location:** [docs/tw4-windpress-system/](../../../../../docs/tw4-windpress-system/)
> - [TW4-COMPLETE-UPDATED-REFERENCE.md](../../../../../docs/tw4-windpress-system/TW4-COMPLETE-UPDATED-REFERENCE.md) - Complete TW4 reference
> - [WINDPRESS-COMPLETE-REFERENCE.md](../../../../../docs/tw4-windpress-system/WINDPRESS-COMPLETE-REFERENCE.md) - Complete Windpress reference  
> - [INTEGRATION-GUIDE.md](../../../../../docs/tw4-windpress-system/INTEGRATION-GUIDE.md) - Generic implementation guide

### Project Files (This Directory)
> **Location:** `mi-design-system/`
> - [mi-ds-guide.md](./mi-ds-guide.md) - **This guide** (complete implementation)
> - [mi-ds-theme.json-example](./mi-ds-theme.json-example) - WordPress theme.json template
> - [mi-ds-windpress-main-example.css](./mi-ds-windpress-main-example.css) - Windpress CSS template

---

## 🎨 Middleton Getaways Brand System

### Brand Colors
```css
/* === MIDDLETON GETAWAYS BRAND === */

/* Primary Brand Colors - Sage Green */
--color-sage: #869648;
--color-sage-50: #f6f7f2;
--color-sage-100: #eaeddc;
--color-sage-200: #d5dbb9;
--color-sage-300: #bac491;
--color-sage-400: #a0b05e;
--color-sage-500: #869648;
--color-sage-600: #6b7a37;
--color-sage-700: #525d2c;
--color-sage-800: #434a25;
--color-sage-900: #3a4022;

/* Secondary Colors - Sand */
--color-sand: #d4a574;
--color-sand-50: #faf8f5;
--color-sand-100: #f3ede3;
--color-sand-200: #e8d7c1;
--color-sand-300: #dbc19b;
--color-sand-400: #d4a574;
--color-sand-500: #c8944f;
--color-sand-600: #b17f3a;
--color-sand-700: #936531;
--color-sand-800: #78522c;
--color-sand-900: #634427;

/* Neutral Colors - Stone */
--color-stone: #b5b09f;
--color-stone-50: #f8f7f6;
--color-stone-100: #efede9;
--color-stone-200: #ddd9d1;
--color-stone-300: #c7c1b5;
--color-stone-400: #b5b09f;
--color-stone-500: #a39d8a;
--color-stone-600: #8e8775;
--color-stone-700: #756f5f;
--color-stone-800: #615c4f;
--color-stone-900: #514d43;

/* Semantic Colors */
--color-success: #10b981;
--color-warning: #f59e0b;
--color-error: #ef4444;
--color-info: #3b82f6;
```

### Brand Typography
- **Display Font:** "Playfair Display" - Elegant serif for headings
- **Body Font:** "Inter" - Clean sans-serif for readability

### Brand Voice
- **Luxury** - High-end vacation rentals
- **Natural** - Connection to nature and landscapes  
- **Welcoming** - Warm hospitality experience

---

## 🚀 Quick Start

### What This Is
A modern component system using Tailwind CSS v4's new features (@utility, @custom-variant) for WordPress and GenerateBlocks.

### Core Implementation Files
```
Real Files (You Create):
├── theme.json                    # WordPress theme configuration
└── [Windpress CSS]              # Main CSS in Windpress plugin

Template Files (This Directory):
├── mi-ds-theme.json-example      # Template for theme.json
└── mi-ds-windpress-main-example.css  # Template for Windpress CSS
```

### Implementation Steps
1. **Copy** `mi-ds-theme.json-example` → `theme.json` in your theme root
2. **Copy** `mi-ds-windpress-main-example.css` content into Windpress plugin
3. **Customize** colors and components as needed
4. **Test** with GenerateBlocks and Gutenberg

---

## 🏗️ Component System

### Available Components

#### Cards
```html
<!-- Basic Card -->
<div class="mi-card p-6">
  <h3 class="text-lg font-semibold mb-2">Basic Card</h3>
  <p>Simple card with default styling</p>
</div>

<!-- Elevated Card with Hover -->
<div class="mi-card card-hover:shadow-xl p-6">
  <h3 class="text-xl font-display font-bold mb-4">Premium Card</h3>
  <p class="mb-6">Enhanced card with elevation and hover effects</p>
  <a href="#" class="mi-btn btn-primary btn-sm">Action</a>
</div>

<!-- Flat Card -->
<div class="mi-card-flat p-6">
  <h3 class="text-lg font-medium mb-2">Flat Card</h3>
  <p>Minimal card with border styling</p>
</div>
```

#### Buttons
```html
<!-- Primary CTA Button -->
<a href="#book-now" class="mi-btn btn-primary btn-lg btn-hover:bg-sage-600 shadow-lg">
  Book Your Stay
</a>

<!-- Secondary Button -->
<a href="#learn-more" class="mi-btn btn-secondary btn-md btn-hover:bg-sand-600">
  Learn More
</a>

<!-- Outline Button -->
<a href="#contact" class="mi-btn btn-outline btn-md btn-hover:bg-sage-50">
  Get in Touch
</a>

<!-- Ghost Button -->
<a href="#gallery" class="mi-btn btn-ghost btn-sm">
  View Gallery
</a>
```

#### Sections
```html
<!-- Hero Section -->
<div class="mi-section-lg bg-gradient-to-r from-sage-500 to-sage-600">
  <div class="mi-container">
    <div class="text-center text-white">
      <h1 class="text-5xl font-display font-bold mb-6">
        Luxury Vacation Rentals
      </h1>
      <p class="text-xl mb-8 max-w-2xl mx-auto">
        Discover exceptional properties in the most beautiful destinations
      </p>
      <a href="#" class="mi-btn btn-secondary btn-lg btn-hover:bg-sand-600">
        Explore Properties
      </a>
    </div>
  </div>
</div>

<!-- Feature Section -->
<div class="mi-section bg-stone-50">
  <div class="mi-container">
    <h2 class="text-3xl font-display font-bold text-center mb-12 text-sage-700">
      Featured Properties
    </h2>
    <!-- Content here -->
  </div>
</div>

<!-- Call to Action Section -->
<div class="mi-section bg-sand-500">
  <div class="mi-container-narrow text-center">
    <h2 class="text-3xl font-display font-bold mb-6 text-white">
      Ready to Book Your Perfect Getaway?
    </h2>
    <p class="text-xl mb-8 text-sand-100">
      Browse our collection of hand-picked vacation rentals
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="#" class="mi-btn btn-primary btn-lg btn-hover:bg-sage-600">
        Browse Properties
      </a>
      <a href="#" class="mi-btn btn-outline btn-lg btn-hover:bg-sand-50 border-white text-white">
        Contact Us
      </a>
    </div>
  </div>
</div>
```

---

## 📋 GenerateBlocks Integration

### Block Settings for Middleton Getaways

#### Container Block Classes
```
Primary Hero: mi-section-lg bg-gradient-to-r from-sage-500 to-sage-600
Feature Section: mi-section bg-stone-50
CTA Section: mi-section bg-sand-500
Property Grid: mi-container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8
```

#### Button Block Classes
```
Primary CTA: mi-btn btn-primary btn-lg btn-hover:bg-sage-600
Secondary: mi-btn btn-secondary btn-md btn-hover:bg-sand-600
Outline: mi-btn btn-outline btn-md btn-hover:bg-sage-50
Ghost: mi-btn btn-ghost btn-sm
```

#### Heading Block Classes
```
Page Title: text-5xl font-display font-bold text-sage-700
Section Title: text-3xl font-display font-semibold text-sage-600
Card Title: text-xl font-display font-medium text-sage-700
Subtitle: text-lg text-stone-600
```

### Usage in GenerateBlocks
1. **Container Block:** Add classes in "Additional CSS Classes" field
2. **Button Block:** Use button classes for consistent styling
3. **Heading Block:** Apply typography classes for brand consistency
4. **Custom HTML:** Use full component markup for complex elements

---

## 🎯 Brand Guidelines

### Color Usage
- **Sage Green (#869648)** - Primary brand color, CTAs, headings
- **Sand (#d4a574)** - Secondary actions, warm accents
- **Stone (#b5b09f)** - Neutral backgrounds, subtle text

### Typography Hierarchy
- **H1:** 5xl, Playfair Display, Bold, Sage-700
- **H2:** 3xl, Playfair Display, Semibold, Sage-600  
- **H3:** xl, Playfair Display, Medium, Sage-700
- **Body:** base, Inter, Regular, Stone-600

### Component Patterns
- **Cards:** Always use `mi-card` with hover effects for interactive elements
- **Buttons:** Primary sage for main actions, secondary sand for supporting actions
- **Sections:** Consistent padding with `mi-section` variants
- **Containers:** Use `mi-container` for content width constraints

---

## 🚀 Implementation Checklist

### Phase 1: Setup
- [ ] Copy `mi-ds-theme.json-example` to `theme.json`
- [ ] Copy CSS from `mi-ds-windpress-main-example.css` to Windpress plugin
- [ ] Install and configure Windpress plugin
- [ ] Test basic utility classes

### Phase 2: Components
- [ ] Test card components in GenerateBlocks
- [ ] Configure button styles and hover effects
- [ ] Set up section layouts and spacing
- [ ] Verify responsive behavior

### Phase 3: WordPress Integration
- [ ] Test theme.json color palette in Gutenberg
- [ ] Configure GenerateBlocks with MI classes
- [ ] Test editor styling consistency
- [ ] Optimize for performance

### Phase 4: Content Implementation
- [ ] Apply design system to homepage
- [ ] Style property listing pages
- [ ] Implement booking flow styling
- [ ] Final testing and optimization

---

## 🔧 Customization

### Adding New Colors
1. Add CSS variables to the `@theme` section in Windpress
2. Update `theme.json` color palette
3. Create utility classes if needed
4. Test in both editor and frontend

### Creating New Components
1. Use `@utility` directive for component base styles
2. Add variants with `@custom-variant` if needed
3. Document usage in this guide
4. Test with GenerateBlocks integration

### Responsive Adjustments
1. Use TW4 container queries for component-specific breakpoints
2. Test on mobile, tablet, and desktop
3. Adjust spacing and typography as needed
4. Verify GenerateBlocks responsive behavior

---

*This guide provides everything needed to implement and maintain the Middleton Getaways design system using TW4 and Windpress.*
