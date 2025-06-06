# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build Commands

```bash
# Development with hot reload
npm run dev

# Production build (builds all configurations)
npm run build

# Build and watch for changes
npm run build:watch

# Build components field only
npm run build:components

# Build frontend editor only
npm run build:frontend

# Preview built files
npm run preview
```

## Architecture Overview

This is a WordPress plugin that extends CMB2 with a React-based flexible content components field. The architecture uses multiple Vite builds and web components for WordPress integration.

### Core Components
- **`umbral-editor.php`** - Main WordPress plugin file that initializes all modules
- **`inc/cmb2/class-components-field.php`** - CMB2 field type registration and rendering
- **`src/components-field-main.jsx`** - Entry point for the CMB2 components field web component
- **`src/components/ComponentsField.jsx`** - Main React application for the components field
- **`src/UmbralStyles.jsx`** - Complete design system with CSS-in-JS for Shadow DOM isolation

### Multi-Build System
The project uses three separate Vite configurations:

1. **Main Editor** (`vite.config.js`) - Builds `src/main.jsx` → `dist/js/umbral-editor.js`
2. **Components Field** (`vite.components.config.js`) - Builds `src/components-field-main.jsx` → `dist/js/umbral-components-field.js`
3. **Frontend Editor** (`vite.frontend.config.js`) - Builds `src/FrontendEditor.jsx` → `dist/js/umbral-frontend-editor.js`

All builds output IIFE bundles for WordPress compatibility.

### Web Components Integration
React components are converted to web components using `@r2wc/react-to-web-component`:

```php
<umbral-components-field 
    field-id="<?php echo esc_attr($field_id); ?>"
    field-name="<?php echo esc_attr($field_name); ?>"
    post-id="<?php echo esc_attr($object_id); ?>"
    rest-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
></umbral-components-field>
```

Props are automatically converted from kebab-case attributes to camelCase React props.

### WordPress Integration
- **REST API** endpoints at `/wp-json/umbral-editor/v1/` for data persistence
- **CMB2 Field Extension** - Adds `components_field` type to CMB2
- **Shadow DOM** provides complete style isolation from WordPress themes
- **Plugin Module System** - Organized into classes for Admin, Assets, API, Frontend, etc.

### Data Flow
1. PHP renders web component with server data as attributes
2. React components fetch additional data via REST API using nonce authentication
3. Components data is persisted via REST API calls
4. Hidden form inputs maintain compatibility with CMB2 form submission
5. Custom events escape Shadow DOM boundaries for frontend editor integration

### Key Features
- **Component Registry** - Centralized component definitions with categories
- **Drag & Drop** - React DnD for component reordering
- **Command Palette** - Quick component search and insertion
- **Real-time Saving** - Auto-save with unsaved changes detection
- **Breakpoints System** - Responsive design management
- **Frontend Preview** - Live preview with iframe integration

## Tech Stack
- React 18 with hooks and Framer Motion animations
- Radix UI components (Dialog, Tabs, Switch, Dropdown)
- React DnD for drag-and-drop functionality
- WordPress REST API with nonce security
- CMB2 field system integration
- Shadow DOM for style isolation
- Multiple Vite build configurations