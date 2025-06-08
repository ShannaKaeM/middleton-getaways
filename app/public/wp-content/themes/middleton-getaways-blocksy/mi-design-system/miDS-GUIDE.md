# Middleton Getaways Design System Guide
*Complete implementation guide for the MI Design System*

## 📚 Documentation References

### Generic Documentation (Reusable)
> **Location:** [docs/tw4-windpress-system/](../../../../../docs/tw4-windpress-system/)
> - [TW4-COMPLETE-REFERENCE.md](../../../../../docs/tw4-windpress-system/TW4-COMPLETE-REFERENCE.md) - Complete TW4 reference
> - [WINDPRESS-COMPLETE-REFERENCE.md](../../../../../docs/tw4-windpress-system/WINDPRESS-COMPLETE-REFERENCE.md) - Complete Windpress reference

### Project Files (This Directory)
> **Location:** `mi-design-system/`
> - [miDS-GUIDE.md](./miDS-GUIDE.md) - **This guide** (complete implementation)
> - [WINDPRESS-MOC.md](./WINDPRESS-MOC.md) - Main Windpress CSS to copy
> - [theme.json](../theme.json) - WordPress theme configuration
> - [html-snippets.html](./html-snippets.html) - Ready-to-use component markup

---

## 🚀 Initial Setup & Testing

### Step 1: Verify Windpress is Working

1. **Copy minimal CSS to Windpress:**
   ```css
   /* From WINDPRESS-MINIMAL.css */
   @layer theme, base, components, utilities;
   
   @import "tailwindcss/theme.css" layer(theme) theme(static);
   @import "tailwindcss/utilities.css" layer(utilities);
   
   @theme {
     --color-primary-600: #6b7a37;
     --spacing-md: 1rem;
   }
   
   @utility card {
     background-color: white;
     border: 1px solid #e5e7eb;
     border-radius: 0.5rem;
     padding: var(--spacing-md);
   }
   ```

2. **Test with this HTML:**
   ```html
   <div class="p-4">
     <div class="card">
       <h3 class="text-lg font-semibold">Test Card</h3>
       <p class="text-gray-600">If you see padding, border, and gray text, Windpress is working!</p>
     </div>
   </div>
   ```

3. **Check that:**
   - ✅ Tailwind utilities work (padding, text colors)
   - ✅ Custom utilities work (card styling)
   - ✅ CSS variables are available

### Step 2: Load Full Design System

Once verified, copy the full CSS from `WINDPRESS-MOC.md` into Windpress.

---

## 🎨 Middleton Getaways Design System

### Semantic Color System
```css
/* === SEMANTIC COLOR TOKENS === */

/* Primary Colors */
--color-primary-50 through --color-primary-900

/* Secondary Colors */
--color-secondary-50 through --color-secondary-900

/* Base/Neutral Colors */
--color-base-50 through --color-base-900

/* Semantic Mappings */
--color-surface: #ffffff;
--color-canvas: #faf9f7;
--color-border: #e5e7eb;
--color-text: var(--color-base-800);
--color-text-muted: var(--color-base-600);
```

### Brand Typography
- **Display Font:** "Playfair Display" - Elegant serif for headings
- **Body Font:** "Inter" - Clean sans-serif for readability

### Brand Voice
- **Luxury** - High-end vacation rentals
- **Natural** - Connection to nature and landscapes  
- **Welcoming** - Warm hospitality experience

---

## 🧪 Simple Test Block

### Basic Card Component Test

**HTML for GenerateBlocks Custom HTML Block:**
```html
<div class="card">
  <div class="card-content">
    <span class="text-primary-600 text-sm uppercase tracking-wider">Featured</span>
    <h3 class="card-title">Mountain Retreat</h3>
    <p class="card-description">Experience luxury in the mountains with breathtaking views.</p>
    <div class="card-footer">
      <button class="btn btn-primary">Book Now</button>
      <button class="btn btn-secondary">Learn More</button>
    </div>
  </div>
</div>
```

**What to Check:**
- Card has white background with border and shadow
- Primary color text appears green (#6b7a37)
- Buttons have proper styling and hover effects
- Spacing and typography follow the design system

### Container Query Test

**Responsive Card that Adapts to Container:**
```html
<div class="@container/card">
  <div class="card @sm/card:card-md @lg/card:card-lg">
    <div class="card-content">
      <h3 class="card-title @sm/card:text-xl @lg/card:text-2xl">
        Adaptive Card
      </h3>
      <p class="card-description">
        This card grows based on its container size, not viewport.
      </p>
    </div>
  </div>
</div>
```

---

## 🏗️ Component System

### Available Components

#### Cards

**Design Decision: Semantic Tokens + Size Variants**

We use semantic color tokens throughout:
- `text-primary-600` instead of `text-sage-600`
- `bg-secondary-500` instead of `bg-sand-500`
- `border-base-300` instead of `border-stone-300`

**Size Variants:**
```html
<!-- Small cards - sidebars, tight spaces -->
<div class="card card-sm">
  <div class="card-content">
    <h3 class="card-title">Cozy Cabin</h3>
    <p class="card-description">Perfect for couples...</p>
  </div>
</div>

<!-- Medium cards - standard content -->
<div class="card card-md aspect-video">
  <div class="card-header">
    <img src="mountain-view.jpg" alt="Mountain View" class="w-full h-full object-cover" />
  </div>
  <div class="card-content">
    <h3 class="card-title">Mountain Retreat</h3>
    <p class="card-description">Discover tranquility in our luxury retreat...</p>
  </div>
  <div class="card-footer">
    <button class="btn btn-primary">Book Now</button>
    <button class="btn btn-secondary">Learn More</button>
  </div>
</div>

<!-- Large cards - featured content -->
<div class="card card-lg">
  <div class="card-content">
    <span class="card-pretitle">Premium Experience</span>
    <h3 class="card-title">Luxury Suite</h3>
    <p class="card-description">Indulge in our most exclusive accommodations...</p>
  </div>
</div>

<!-- Extra large cards - hero sections -->
<div class="card card-xl aspect-video">
  <div class="card-header">
    <img src="hero-landscape.jpg" alt="Hero" class="w-full h-full object-cover" />
  </div>
  <div class="card-content">
    <h1 class="card-title">Welcome to Paradise</h1>
    <p class="card-description">Experience the ultimate mountain getaway...</p>
  </div>
</div>
```

**Responsive Sizing:**
```html
<!-- Cards that grow with viewport -->
<div class="card card-sm md:card-md lg:card-lg">
  <div class="card-content">
    <h3 class="card-title">Responsive Card</h3>
    <p class="card-description">Adapts to screen size...</p>
  </div>
</div>
```

**Container Queries for Fine-Tuning:**
```html
<!-- When you need container-specific adjustments -->
<div class="card card-md @container/card">
  <div class="card-content">
    <h3 class="card-title @lg/card:text-2xl">Smart Scaling Title</h3>
    <p class="card-description">Adjusts based on card container size...</p>
  </div>
  <div class="card-footer @md/card:flex-row">
    <button class="btn btn-primary">Action</button>
  </div>
</div>
```

**When to Use Each Approach:**
- **Size Variants:** 90% of use cases, predictable viewport-based scaling
- **Container Queries:** Complex layouts (grids, sidebars), reusable components
- **Aspect Ratios:** When you need specific proportional control

**Available Card Elements:**
- `card-header` - Images, icons, or color backgrounds
- `card-content` - Main content area
- `card-title` - Primary heading
- `card-description` - Body text
- `card-pretitle` - Small label above title
- `card-footer` - Actions, buttons, metadata

#### Buttons

**Design Decision: Size Variants + Style Variants**

Buttons follow the same philosophy as cards - simple size variants with clear style options.

**Size Variants:**
```html
<!-- Small buttons - compact spaces, secondary actions -->
<button class="btn btn-primary btn-sm">Small Action</button>

<!-- Medium buttons - standard size, most common -->
<button class="btn btn-primary btn-md">Standard Action</button>

<!-- Large buttons - prominent CTAs, hero sections -->
<button class="btn btn-primary btn-lg">Primary CTA</button>

<!-- Extra large buttons - hero sections, major actions -->
<button class="btn btn-primary btn-xl">Hero Action</button>
```

**Style Variants:**
```html
<!-- Primary - main actions, CTAs -->
<button class="btn btn-primary btn-md">Book Now</button>

<!-- Secondary - alternative actions -->
<button class="btn btn-secondary btn-md">Learn More</button>

<!-- Outline - subtle actions -->
<button class="btn btn-outline btn-md">Contact Us</button>

<!-- Ghost - minimal actions, links -->
<button class="btn btn-ghost btn-sm">View Gallery</button>
```

**Responsive Sizing:**
```html
<!-- Buttons that grow with viewport -->
<button class="btn btn-primary btn-sm md:btn-md lg:btn-lg">
  Responsive Button
</button>

<!-- Different styles at different sizes -->
<button class="btn btn-ghost btn-sm md:btn-secondary md:btn-md lg:btn-primary lg:btn-lg">
  Progressive Enhancement
</button>
```

**In Card Footers:**
```html
<div class="card-footer">
  <button class="btn btn-primary btn-sm @sm/card:btn-md">
    Primary Action
  </button>
  <button class="btn btn-secondary btn-sm @sm/card:btn-md">
    Secondary Action
  </button>
</div>
```

**Available Button Combinations:**
- **Sizes:** `btn-sm`, `btn-md`, `btn-lg`, `btn-xl`
- **Styles:** `btn-primary`, `btn-secondary`, `btn-outline`, `btn-ghost`
- **Base:** Always include `btn` class first

#### Sections
```html
<!-- Hero Section -->
<div class="mi-section-lg bg-gradient-to-r from-primary-500 to-primary-600">
  <div class="mi-container">
    <div class="text-center text-white">
      <h1 class="text-5xl font-display font-bold mb-6">
        Luxury Vacation Rentals
      </h1>
      <p class="text-xl mb-8 max-w-2xl mx-auto">
        Discover exceptional properties in the most beautiful destinations
      </p>
      <a href="#" class="mi-btn btn-secondary btn-lg btn-hover:bg-secondary-600">
        Explore Properties
      </a>
    </div>
  </div>
</div>

<!-- Feature Section -->
<div class="mi-section bg-base-50">
  <div class="mi-container">
    <h2 class="text-3xl font-display font-bold text-center mb-12 text-primary-700">
      Featured Properties
    </h2>
    <!-- Content here -->
  </div>
</div>

<!-- Call to Action Section -->
<div class="mi-section bg-secondary-500">
  <div class="mi-container-narrow text-center">
    <h2 class="text-3xl font-display font-bold mb-6 text-white">
      Ready to Book Your Perfect Getaway?
    </h2>
    <p class="text-xl mb-8 text-secondary-100">
      Browse our collection of hand-picked vacation rentals
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="#" class="mi-btn btn-primary btn-lg btn-hover:bg-primary-700">
        Browse Properties
      </a>
      <a href="#" class="mi-btn btn-outline btn-lg btn-hover:bg-secondary-50 border-white text-white">
        Contact Us
      </a>
    </div>
  </div>
</div>
```

---

## 📋 GenerateBlocks Integration

### Block Settings with Semantic Tokens

#### Container Block Classes
```
Primary Hero: mi-section-lg bg-gradient-to-r from-primary-500 to-primary-600
Feature Section: mi-section bg-base-50
CTA Section: mi-section bg-secondary-500
Property Grid: mi-container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8
```

#### Button Block Classes
```
Primary CTA: mi-btn btn-primary btn-lg hover:bg-primary-700
Secondary: mi-btn btn-secondary btn-md hover:bg-secondary-600
Outline: mi-btn btn-outline btn-md hover:bg-primary-50
Ghost: mi-btn btn-ghost btn-sm
```

#### Heading Block Classes
```
Page Title: text-5xl font-bold text-primary-700
Section Title: text-3xl font-semibold text-primary-600
Card Title: text-xl font-medium text-base-800
Subtitle: text-lg text-base-600
```

### Usage in GenerateBlocks
1. **Container Block:** Add classes in "Additional CSS Classes" field
2. **Button Block:** Use button classes for consistent styling
3. **Heading Block:** Apply typography classes for brand consistency
4. **Custom HTML:** Use full component markup for complex elements

---

## 🎯 Design Guidelines

### Color Usage
- **Primary (Green #6b7a37)** - Main brand color, CTAs, headings
- **Secondary (Warm accent)** - Secondary actions, warm accents
- **Base (Neutrals)** - Backgrounds, body text, borders

### Typography Hierarchy
- **H1:** 5xl, Bold, Primary-700
- **H2:** 3xl, Semibold, Primary-600  
- **H3:** xl, Medium, Base-800
- **Body:** base, Regular, Base-600

### Component Patterns
- **Cards:** Always use `mi-card` with hover effects for interactive elements
- **Buttons:** Primary green for main actions, secondary warm for supporting actions
- **Sections:** Consistent padding with `mi-section` variants
- **Containers:** Use `mi-container` for content width constraints

---

## 🚀 Implementation Checklist

### Phase 1: Initial Setup ✅
- [x] Test Windpress with minimal CSS
- [x] Verify Tailwind utilities work
- [x] Confirm custom utilities work
- [ ] Copy full WINDPRESS-MOC.md to Windpress
- [ ] Update theme.json with semantic tokens

### Phase 2: Components
- [ ] Test simple card component
- [ ] Add container query support
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
