# Middleton Getaways Design Book System - V2

This document outlines the updated architecture and conventions for the Middleton Getaways WordPress theme's design system, focusing on a modular, atomic design approach. It details the structure and interaction of Primitive Globals, Components, and the Editor System.

## 1. Primitive Globals

Primitive Globals represent the foundational design tokens of the system. They are the single source of truth for design properties and are defined in JSON files.

### 1.1. Structure and Location

- **JSON Files:** Stored in `app/public/wp-content/themes/miGV/primitives/`
  - Examples: `colors.json`, `typography.json`, `spacing.json`, `borders.json`, `shadows.json`
- **Primitive Books (Twig):** Stored in `app/public/wp-content/themes/miGV/templates/primitive-books/`
  - These are Twig templates that consume the JSON primitives and output CSS properties (e.g., `color-book.twig`, `typography-book.twig`). They act as an abstraction layer for applying primitive values.

### 1.2. Usage

- Primitives are loaded into Twig templates using a `load_primitive()` function (e.g., `{% set colors = load_primitive('colors') %}`).
- Primitive books are included in components to apply styles (e.g., `{% include 'primitive-books/color-book.twig' with { color: 'primary-default' } %}`).

## 2. Components

Components are reusable UI elements built from Primitive Globals and other components. They are organized hierarchically by size and complexity.

### 2.1. Naming and Folder Conventions

All non-primitive building blocks are referred to as "components." They are categorized by size:

- **Small Components (sm-components / Atoms/Elements):** Basic UI elements like text, buttons, icons.
  - **JSON Definitions:** `app/public/wp-content/themes/miGV/components/sm-components/`
    - Example: `text.json` (defines predefined text styles like `pretitle`, `title`, `body`)
  - **Twig Templates:** `app/public/wp-content/themes/miGV/templates/components/sm-components/`
    - Example: `text-component.twig` (renders text based on `text.json` styles)

- **Medium Components (md-components / Molecules/Groups):** Combinations of small components, e.g., cards, input fields with labels.
  - **JSON Definitions:** `app/public/wp-content/themes/miGV/components/md-components/`
  - **Twig Templates:** `app/public/wp-content/themes/miGV/templates/components/md-components/`

- **Large Components (lg-components / Organisms/Sections):** Complex sections of a page, e.g., headers, footers, hero sections.
  - **JSON Definitions:** `app/public/wp-content/themes/miGV/components/lg-components/`
  - **Twig Templates:** `app/public/wp-content/themes/miGV/templates/components/lg-components/`

### 2.2. Component Interaction

- Components consume JSON definitions (e.g., `text.json`) using a `load_component()` function (e.g., `{% set text_styles = load_component('sm-components/text') %}`).
- Components apply styles by including Primitive Books.
- Larger components can include smaller components.

## 3. Editor System

The Editor System provides the user interface for composing and editing Primitive Globals and Components. It is distinct from the design system itself but aims for consistent styling.

### 3.1. Naming and Folder Conventions

- **Editor Twig Templates:** Stored in `app/public/wp-content/themes/miGV/templates/design-book-editors/`
  - Organized by component size: `sm-components/`, `md-components/`, `lg-components/`
  - Example: `text-component-editor.twig` (editor UI for the text component)

- **Editor JavaScript:** Stored in `app/public/wp-content/themes/miGV/assets/js/`
  - Organized by component size: `sm-components/`, `md-components/`, `lg-components/`
  - Example: `text-component-editor.js` (handles dynamic behavior of the text component editor)

- **Editor CSS (SCSS):** Stored in `app/public/wp-content/themes/miGV/assets/css/`
  - Example: `_text-editor.scss` (styling for the text component editor UI)

### 3.2. Functionality

- Editors load relevant JSON data (primitives or component definitions) via AJAX.
- They provide UI controls (dropdowns, text areas) for users to select and preview styles.
- Preview functionality relies on dynamically applying CSS properties, as Twig includes are server-side.
- JavaScript handles dynamic updates and interactions.
- WordPress AJAX handlers and nonce verification ensure secure communication.

## 4. WordPress Integration

- **Page Templates:** Custom WordPress page templates (e.g., `page-text-editor.php`) are used to render the editor Twig templates.
- **`functions.php`:** Used to enqueue editor-specific JavaScript and CSS files, and to register AJAX handlers.

## 5. Key Design Principles

- **Single Source of Truth:** Primitives are the ultimate source of truth for design tokens.
- **Modularity and Reusability:** Components are built to be self-contained and reusable.
- **Separation of Concerns:** Editor UI is distinct from the design system components it manipulates.
- **Scalability:** The hierarchical folder structure supports easy expansion with new primitives and components.

This updated structure provides a clear roadmap for developing and maintaining the Middleton Getaways design system.
