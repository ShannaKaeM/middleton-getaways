# Primitive Design Library

This document outlines the architecture and philosophy behind the Primitive Design Library, which forms the foundational layer of the design system.

## 1. Philosophy

The Primitive Design Library defines the core, global design tokens that establish a consistent and maintainable visual language across the entire application or website. These primitives are the elemental building blocks of the design.

- **Single Source of Truth:** The primitive JSON files (e.g., `colors.json`, `typography.json`, `spacing.json`) located in the `/primitives/` directory are the **absolute single source of truth** for all design tokens.
- **Consistency:** By centralizing these tokens, we ensure design consistency across all components and UI elements.
- **Maintainability:** Changes to the design language (e.g., updating a primary color) are made in one place, and those changes propagate throughout the system.

## 2. Structure

- **Location:** Primitive JSON files are stored in the `[theme_or_library_path]/primitives/` directory.
- **Format:** Each file (e.g., `colors.json`) contains a structured JSON object representing a category of design tokens. For example:
  ```json
  // primitives/colors.json (example)
  {
    "primary": "#3b82f6",
    "secondary": "#10b981",
    // ... more colors
  }
  ```
  ```json
  // primitives/typography.json (example)
  {
    "font_sizes": {
      "small": "0.875em",
      "medium": "1em"
    },
    // ... more typography tokens
  }
  ```

## 3. Accessing Primitives

Primitives are made available to the system (themes, components, editor UIs) primarily through CSS Custom Properties and optionally via a PHP API.

### 3.1. CSS Custom Properties (Primary Method)

- **Generation:** A core function within the Design Library (e.g., `DesignBookCore::generateCssVariables()`) reads all primitive JSON files and generates a corresponding set of CSS Custom Properties.
- **Availability:** These CSS variables are made available globally (e.g., within a `:root {}` block in a core CSS file like `design-book-editors.css` or a dedicated `_primitives.css`).
- **Usage:** Components and theme styles should use these CSS variables for styling. Example:
  ```css
  .button {
    background-color: var(--color-primary);
    font-size: var(--font-size-medium);
    padding: var(--spacing-sm);
  }
  ```

### 3.2. PHP API (Optional - For Themes/Plugins)

- The Design Library can provide a PHP API for themes or other plugins to programmatically access the raw primitive data.
- **Example:** `DesignBookCore::getTokens('colors')` could return an array of all color tokens.
- **Use Cases:** Populating select dropdowns in block editor controls, generating dynamic style variations, or for other programmatic needs by the integrating theme.

## 4. Management UI (Design Book Editor)

- A dedicated UI (the "Design Book Editor" pages, built with Twig templates) allows for the viewing and modification of these global primitive JSON files.
- This UI directly reads from and saves to the primitive JSON files, ensuring they remain the single source of truth.

## 5. `theme.json` Integration (Optional)

- While the primitive JSON files are the ultimate source of truth, the defined primitives *can* be synced or mapped to a WordPress theme's `theme.json` file.
- **Purpose:** This makes the design tokens available to the WordPress Block Editor (Gutenberg), allowing built-in blocks and Global Styles to utilize the defined color palettes, font sizes, etc.
- **Responsibility:** The integration with `theme.json` (e.g., via runtime filters like `theme_json_data_theme` or a build process) is typically the responsibility of the **integrating theme**, not the core Primitive Design Library itself. The library may provide helper functions to facilitate this.

## 6. Next Phase: Component Library Integration

(This section will be expanded or moved to a separate `COMPONENT-DESIGN-LIBRARY.md` document.)

- How components (e.g., Twig components in a block builder) consume global primitives.
- Strategies for component-specific settings while adhering to the global design language.
- UI for component-specific settings (typically handled by the theme/block builder).
