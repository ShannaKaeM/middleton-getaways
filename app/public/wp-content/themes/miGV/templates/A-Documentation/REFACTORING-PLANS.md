# Refactoring Plans and Technical Debt

This document tracks ongoing refactoring efforts, planned improvements, and technical debt for the miGV theme's atomic design system and design book editor. It serves as a working document for development planning and should be regularly updated as work progresses.

## Current Status Overview

### ✅ Completed Refactoring
- JSON-based primitive architecture implementation
- Typography primitive converted to JSON
- Colors primitive converted to JSON
- Custom Timber function `load_primitive()` for JSON loading
- AJAX handlers for saving editor changes to JSON
- Separation of design system and editor documentation

### 🚧 In Progress
- Converting remaining primitives to JSON format (spacing, borders, shadows)
- Implementing bidirectional sync between JSON and theme.json
- Typography editor refinements

### 📋 Planned Refactoring

## Immediate Priority Tasks

### 1. Complete JSON Primitive Migration
**Status:** In Progress  
**Priority:** High  
**Estimated Effort:** 2-3 days

- [ ] Convert spacing primitive to JSON format
- [ ] Convert borders primitive to JSON format  
- [ ] Convert shadows primitive to JSON format
- [ ] Update all primitive book templates to use `load_primitive()`
- [ ] Remove hardcoded values from primitive books

### 2. Typography Editor Enhancements
**Status:** Planning  
**Priority:** High  
**Estimated Effort:** 1-2 days

- [ ] Add font family management (add/remove/reorder)
- [ ] Implement font pairing suggestions
- [ ] Add Google Fonts integration
- [ ] Create font size scale calculator
- [ ] Add line height and letter spacing calculators
- [ ] Implement responsive typography preview

### 3. Theme.json Bidirectional Sync
**Status:** Planning  
**Priority:** High  
**Estimated Effort:** 3-4 days

- [ ] Create sync mechanism from JSON to theme.json
- [ ] Implement selective sync (choose which tokens to sync)
- [ ] Add conflict resolution UI
- [ ] Create backup system before sync
- [ ] Add sync status indicators in editors
- [ ] Document sync architecture

## Medium Priority Tasks

### 4. Performance Optimization
**Status:** Not Started  
**Priority:** Medium  
**Estimated Effort:** 2-3 days

- [ ] Implement JSON caching mechanism
- [ ] Add lazy loading for primitive data
- [ ] Optimize AJAX calls (batch updates)
- [ ] Add debouncing to editor inputs
- [ ] Implement partial JSON updates
- [ ] Add performance monitoring

### 5. Editor UI/UX Improvements
**Status:** Planning  
**Priority:** Medium  
**Estimated Effort:** 3-4 days

- [ ] Add undo/redo functionality
- [ ] Implement keyboard shortcuts
- [ ] Add search/filter for tokens
- [ ] Create token grouping/categorization
- [ ] Add bulk edit capabilities
- [ ] Implement drag-and-drop reordering

### 6. Validation and Error Handling
**Status:** Not Started  
**Priority:** Medium  
**Estimated Effort:** 2 days

- [ ] Add JSON schema validation
- [ ] Implement value validation (colors, units, etc.)
- [ ] Add error recovery mechanisms
- [ ] Create validation UI feedback
- [ ] Add import validation
- [ ] Implement safe fallbacks

## Long-term Goals

### 7. Design System Features
**Status:** Planning  
**Priority:** Low  
**Estimated Effort:** 1-2 weeks

- [ ] Token relationship system (derived values)
- [ ] Design token aliases
- [ ] Conditional tokens (dark mode, breakpoints)
- [ ] Token documentation inline
- [ ] Visual token dependency graph
- [ ] Component token usage analyzer

### 8. Plugin Architecture
**Status:** Research  
**Priority:** Low  
**Estimated Effort:** 2-3 weeks

- [ ] Extract editor system to standalone plugin
- [ ] Create plugin API for extensions
- [ ] Add multi-theme support
- [ ] Implement role-based permissions
- [ ] Create plugin settings page
- [ ] Add automatic updates system

### 9. Developer Experience
**Status:** Planning  
**Priority:** Low  
**Estimated Effort:** 1 week

- [ ] Create CLI tools for primitive management
- [ ] Add VSCode extension for token autocomplete
- [ ] Implement design token linting
- [ ] Create migration tools
- [ ] Add scaffolding commands
- [ ] Build documentation generator

## Technical Debt

### High Priority Debt
1. **Twig Variable Scope Issues**
   - Some components still use hardcoded values
   - Need to audit all components for primitive usage
   - Migration path: Component by component conversion

2. **CSS Organization**
   - `style.css` contains duplicate token definitions
   - Multiple CSS files with overlapping concerns
   - Migration path: Gradual consolidation and removal

3. **Router Architecture**
   - Design book router could be more modular
   - Asset loading could be optimized
   - Migration path: Refactor into class-based system

### Medium Priority Debt
1. **Error Handling**
   - Limited error feedback in editors
   - No graceful degradation
   - Migration path: Add try-catch blocks and user feedback

2. **Testing Coverage**
   - No automated tests for JSON operations
   - No visual regression tests
   - Migration path: Add PHPUnit and Jest tests

3. **Documentation Gaps**
   - Missing inline code documentation
   - No API documentation
   - Migration path: Add PHPDoc and JSDoc comments

## Migration Strategies

### From CSS Variables to Actual Values
For any remaining components using CSS variables:
1. Identify components using `var(--wp--preset--)` 
2. Map CSS variables to JSON primitive paths
3. Update component to load primitive
4. Replace CSS variable with primitive value
5. Test component in isolation
6. Remove CSS variable definition

### From Hardcoded to JSON-based
For any remaining hardcoded values:
1. Extract values to appropriate JSON file
2. Update component to use `load_primitive()`
3. Test with different values
4. Document token usage

## Success Metrics

- [ ] All primitives stored in JSON format
- [ ] Zero hardcoded design values in components
- [ ] All editors using JSON as source of truth
- [ ] Bidirectional sync fully functional
- [ ] Performance metrics within acceptable range
- [ ] Documentation complete and up-to-date

## Notes and Observations

### Lessons Learned
- JSON approach solves Twig variable scope issues effectively
- Separation of editor UI from design system tokens is crucial
- Security must be considered at every AJAX endpoint
- Performance optimization needed for large token sets

### Architectural Decisions
- JSON files as single source of truth
- Editors as separate tools, not part of design system
- Progressive enhancement approach for features
- Maintain WordPress compatibility while being framework-agnostic

---

*Last Updated: June 3, 2025*  
*Next Review: June 10, 2025*
