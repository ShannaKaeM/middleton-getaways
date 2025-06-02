# Villa Portal Backend Refactor Plan

## Overview
This plan outlines the comprehensive refactoring of the Villa Portal backend to implement a fully composable component system using atomic design principles with Twig templates.

## Current State Analysis
- **Backend**: WordPress mu-plugins providing custom post types and functionality
- **Frontend**: PHP-generated HTML with CSS styling
- **Templates**: Basic Twig templates with primitives
- **Design System**: DesignBook with visual editing capabilities

## Target Architecture
- **Atomic Design**: Primitives → Elements → Components → Sections → Pages
- **Component-Driven**: All UI built from composable Twig components
- **Data-Agnostic**: Components accept props, not tied to specific post types
- **Theme Integration**: All styles flow from theme.json tokens

## Phase 1: Foundation (Week 1)
### 1.1 Complete Element Books
- [x] Update existing primitives (typography, color, spacing, layout, border, shadow, animation)
- [ ] Create badge-book.twig for status badges, tags, counts
- [ ] Create avatar-book.twig for user/group avatars
- [ ] Create icon-book.twig for icon integration
- [ ] Create form-element-book.twig for inputs, selects, textareas

### 1.2 Core Components
- [ ] Create card-book.twig with variants:
  - Default card
  - Image card
  - Horizontal card
  - Compact card
- [ ] Create button-group-book.twig for action areas
- [ ] Create meta-info-book.twig for metadata displays

## Phase 2: Dashboard Components (Week 2)
### 2.1 Layout Components
- [ ] Create grid-book.twig for responsive grids
- [ ] Create dashboard-layout-book.twig for sidebar/content layout
- [ ] Create section-book.twig for dashboard sections
- [ ] Create tab-navigation-book.twig for tab interfaces

### 2.2 Specialized Cards
- [ ] Create property-card-book.twig using card-book base
- [ ] Create project-card-book.twig with status/priority
- [ ] Create group-card-book.twig with member counts
- [ ] Create announcement-card-book.twig with read/pinned states
- [ ] Create ticket-card-book.twig with priority indicators

### 2.3 Dashboard Widgets
- [ ] Create stat-card-book.twig for metrics
- [ ] Create alert-book.twig for messages
- [ ] Create empty-state-book.twig for no-data states
- [ ] Create loading-book.twig for loading states

## Phase 3: Backend Integration (Week 3)
### 3.1 Refactor PHP Output
- [ ] Update villa-frontend-dashboard.php to use Twig components
- [ ] Create Twig context builders for each post type
- [ ] Implement component prop mapping from WordPress data
- [ ] Add caching layer for compiled templates

### 3.2 Dynamic Features
- [ ] Implement AJAX loading with Twig partial rendering
- [ ] Create live preview system for DesignBook
- [ ] Add component variation switcher
- [ ] Implement responsive preview modes

### 3.3 Data Abstraction
- [ ] Create data transformer classes for each post type
- [ ] Implement consistent prop interfaces
- [ ] Add data validation layer
- [ ] Create mock data generators for testing

## Phase 4: DesignBook Integration (Week 4)
### 4.1 Component Builders
- [ ] Create visual builders for each component book
- [ ] Implement prop controls (dropdowns, toggles, inputs)
- [ ] Add live preview with prop changes
- [ ] Create component documentation system

### 4.2 Design Token Management
- [ ] Enhance theme.json editor interface
- [ ] Create visual token picker for components
- [ ] Implement token inheritance system
- [ ] Add custom property generation

### 4.3 Export/Import
- [ ] Create component export system
- [ ] Implement design system versioning
- [ ] Add rollback capabilities
- [ ] Create shareable component libraries

## Phase 5: Performance & Polish (Week 5)
### 5.1 Optimization
- [ ] Implement Twig template caching
- [ ] Add lazy loading for images
- [ ] Optimize CSS delivery (critical CSS)
- [ ] Implement component code splitting

### 5.2 Accessibility
- [ ] Add ARIA labels to all components
- [ ] Implement keyboard navigation
- [ ] Add screen reader support
- [ ] Create accessibility testing suite

### 5.3 Documentation
- [ ] Create component usage guide
- [ ] Document prop interfaces
- [ ] Add code examples for each component
- [ ] Create migration guide from old system

## Implementation Strategy

### Component Creation Pattern
```twig
{# component-books/example-book.twig #}
{% set default_props = {
    variant: 'default',
    size: 'medium',
    // ... other defaults
} %}
{% set props = default_props|merge(props ?? {}) %}

<div class="component-{{ props.variant }}">
    {% include 'primitive-books/...' with { ... } %}
    {% include 'element-books/...' with { ... } %}
    {{ props.content }}
</div>
```

### PHP Integration Pattern
```php
// Render component with data
Timber::render('component-books/card-book.twig', [
    'props' => [
        'variant' => 'property',
        'data' => $property_data,
        'actions' => ['view', 'edit']
    ]
]);
```

### Progressive Migration
1. Start with new features using components
2. Gradually replace existing templates
3. Maintain backward compatibility
4. Deprecate old code gradually

## Success Metrics
- All dashboard UI using atomic components
- 50% reduction in CSS complexity
- Consistent design token usage
- Full DesignBook coverage
- Zero regression in functionality

## Risk Mitigation
- Create comprehensive test suite
- Implement feature flags for gradual rollout
- Maintain fallback rendering system
- Document all breaking changes
- Regular stakeholder demos

## Timeline Summary
- **Week 1**: Foundation elements and core components
- **Week 2**: Dashboard-specific components
- **Week 3**: Backend integration and data layer
- **Week 4**: DesignBook enhancements
- **Week 5**: Performance optimization and documentation

## Next Steps
1. Review and approve plan
2. Set up development environment
3. Create component examples
4. Begin Phase 1 implementation
5. Weekly progress reviews