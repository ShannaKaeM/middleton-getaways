---
description: Review TW4 documentation and implementation guide
---

# TW4 Design System Reference

This workflow provides quick access to the Tailwind CSS v4 design system documentation for the Middleton Getaways project.

## 1. Main Documentation
View the complete guide at:
```
tw4-design-system/TW4-GUIDE.md
```

This single guide contains:
- Quick start instructions
- Component usage examples
- Technical details
- WordPress/GenerateBlocks integration
- Available variants

## 2. File Structure

```
tw4-design-system/
├── INDEX.md                      # Directory listing
├── TW4-GUIDE.md                 # ⭐ MAIN DOCUMENTATION
├── tw4-components.css           # Component variants
├── gutenstyles-bridge.css       # WordPress/GB bridge
├── atomic-tw4-system.css        # Base atomic components
├── theme-tw4.json              # WordPress theme.json
└── Examples/
    ├── tw4-component-examples.html
    ├── hero-pattern-tw4.html
    └── generateblocks-hero-pattern.html
```

## 3. Quick Usage Pattern

```html
<!-- Component + Variant + Overrides -->
<div class="mi-card card-style-1 p-6 shadow-lg" data-card-style="1">
  <h3 class="text-xl font-bold">Title</h3>
  <p>Content</p>
</div>
```

## 4. In GenerateBlocks

Add these in "Additional CSS Classes":
- Component: `mi-card card-style-1`
- Utilities: `p-6 shadow-lg rounded-lg`
- Responsive: `md:p-8 lg:shadow-xl`

## 5. Available Components

- **Cards**: `card-style-1`, `card-style-2`, `card-style-3`
- **Buttons**: `btn-primary`, `btn-secondary`, `btn-outline`, `btn-ghost`
- **Sections**: `section-hero`, `section-feature`, `section-cta`
- **Sizes**: `size-sm`, `size-md`, `size-lg`, `size-xl`

## 6. Key TW4 Features

- **@utility directive**: Define component styles as utilities
- **@custom-variant directive**: Create data-attribute variants
- **CSS variables**: All utilities exposed as `--color-*`, `--spacing-*`, etc.

---

📖 **For complete details, see: `tw4-design-system/TW4-GUIDE.md`**
