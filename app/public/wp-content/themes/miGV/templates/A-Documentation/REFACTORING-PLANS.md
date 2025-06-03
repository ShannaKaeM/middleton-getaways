# Refactoring Plans and Technical Debt

**Note for Assistants/Developers:** This document serves as the primary procedural guide for the current refactoring effort related to the theme's design system primitives and their integration. It outlines specific tasks, steps, and checklists. For a deeper understanding of the underlying concepts, architecture, and philosophy, please refer to the complementary documents linked within, particularly:
- `PRIMITIVE-DESIGN-LIBRARY.md` (for understanding the design tokens)
- `DESIGN-BOOK-EDITOR-SYSTEM.md` (for the editor's architecture)
- `COMPONENT-SYSTEM.md` (for how theme components use these primitives)

When undertaking tasks from this plan, use the detailed steps provided herein. If conceptual clarity is needed, consult the linked documents.

This document tracks ongoing refactoring efforts, planned improvements, and technical debt for the miGV theme's atomic design system and design book editor. It serves as a working document for development planning and should be regularly updated as work progresses.

## Guiding Principles & Key Architectural Decisions (Recently Solidified)

- **Primitive JSONs as Single Source of Truth:** `colors.json`, `typography.json`, `spacing.json` (and future primitives like borders, shadows) are the canonical source for all design tokens.
- **CSS Custom Properties as Primary Consumption Method:**
    - A PHP function (`migv_generate_primitive_css_variables()`) dynamically generates CSS custom properties (e.g., `--color-primary`, `--typography-font-sizes-sm`) from the primitive JSONs.
    - These CSS variables are the **primary way theme components (e.g., text-book, card) should receive styling values.**
    - They are also used by the Design Book Editor's *preview areas* to display live changes.
- **Design Book Editor UI Styling:**
    - The editor's main interface (its "work clothes" - buttons, layout, inputs) will have its **own fixed, accessible CSS** to ensure usability, independent of the theme primitives being edited.
    - Only *preview elements* within the editor will use the dynamic theme primitives.
- **`theme.json` as Optional Integration:** Syncing to `theme.json` is a secondary step, primarily for compatibility with the WordPress Block Editor (Gutenberg), and not core to the functioning of the theme's custom components or the Design Book Editor.

## Phase 1: Establish Core Primitive Library & CSS Variable Generation
**Goal:** Create a robust system for managing primitive design tokens in JSON files and making them available as CSS custom properties for consumption by the theme and editor previews.

### ✅ Completed in This Phase (or leading up to it)
- Foundational JSON-based primitive architecture established for `colors.json`, `typography.json`.
- Custom Timber function `load_primitive()` for direct JSON access (still useful for non-CSS data needs, like populating block controls).
- AJAX handlers for saving editor changes to `colors.json` and `typography.json`.
- Key documentation created/updated:
    - `PRIMITIVE-DESIGN-LIBRARY.md` (defines core primitive concepts).
    - `DESIGN-BOOK-EDITOR-SYSTEM.md` (updated to reflect editor styling strategy).
- PHP function `migv_generate_primitive_css_variables()` created in `inc/design-system-core.php` to generate CSS custom properties from all primitive JSONs.

### 🚧 Actively Working On (Current Step - You Are Here)
- **Integrate CSS Variable Generation into Theme:**
    - [ ] Include `inc/design-system-core.php` in `functions.php`.
    - [ ] Hook `migv_generate_primitive_css_variables()` to `wp_head` (conditionally for editor pages, and potentially globally for the theme) to output CSS variables.
    - [ ] Verify Design Book Editor *preview areas* (e.g., typography examples in `typography-editor.twig`) correctly use these generated CSS variables and update live.

### 📋 Next Steps in Phase 1
- **Complete JSON Primitive Migration & Integration:**
    The following steps detail how to integrate remaining and new primitive types (Spacing, Borders, Shadows) into the Design Book Editor system. This process should meticulously follow the pattern established during the successful refactoring of the Typography editor, where JSON primitives are the source of truth, and CSS custom variables are used for live previews. For broader context on the overall system architecture, please refer to:
    - [PRIMITIVE-DESIGN-LIBRARY.md](./PRIMITIVE-DESIGN-LIBRARY.md)
    - [DESIGN-BOOK-EDITOR-SYSTEM.md](./DESIGN-BOOK-EDITOR-SYSTEM.md)
    - [COMPONENT-SYSTEM.md](./COMPONENT-SYSTEM.md)

    If at any point the process for a specific primitive type becomes unclear, refer back to the implementation of the Typography editor (`templates/design-book-editors/typography-editor.twig`, relevant sections in `functions.php` for AJAX and page registration, and `inc/design-system-core.php` for CSS variable generation) as the canonical example.

    #### Pattern for Integrating/Refactoring Primitive Editors (Based on Typography Editor Success)

    For each primitive type (e.g., Spacing, Borders, Shadows), follow these detailed steps to ensure consistency and proper integration with the CSS variable system:

    1.  **JSON Primitive File (`primitives/<primitive_name>.json`):**
        *   [ ] **Create/Verify File:** Ensure the JSON file (e.g., `primitives/spacing.json`) exists. If creating new (like for Borders, Shadows), define its initial structure and values.
            *   *Example Structure (borders.json):* `{"widths": {"thin": "1px"}, "styles": {"solid": "solid"}, "radii": {"sm": "3px"}}`
            *   *Example Structure (shadows.json):* `{"small": "0 1px 2px 0 rgba(0,0,0,0.05)"}`
        *   [ ] **Define Structure:** Ensure a clear, logical structure for the tokens within the JSON.

    2.  **CSS Variable Generation (`inc/design-system-core.php`):**
        *   [ ] **Add to Processor:** Update the `$primitive_types` array in the `migv_generate_primitive_css_variables()` function to include the new primitive type (e.g., add `'borders'`, `'shadows'`). Remember this array is currently: `['colors', 'typography', 'spacing']`.
        *   [ ] **Verify Generation:** Confirm that the function correctly processes the new JSON file and generates the appropriate CSS custom properties (e.g., `--spacing-sm`, `--borders-widths-thin`, `--shadows-small`). Check naming conventions (hyphenated, prefixed like `--<primitive_type>-<group_key>-<token_key>`).

    3.  **Editor Twig Template (`templates/design-book-editors/<primitive_name>-editor.twig`):**
        *   [ ] **Create/Update Template:** Create a new Twig file or update an existing one for the primitive editor. Model it closely on `typography-editor.twig`.
        *   [ ] **Load Primitives:** Use `{% set tokens = load_primitive('<primitive_name>') %}` at the top to load the JSON data.
        *   [ ] **Input Fields:** Create HTML forms with input fields (text, select, color pickers as appropriate) for each token, populating their initial values from `tokens`. Ensure `name` attributes are set correctly for AJAX submission (e.g., `name="data[widths][thin]"`).
        *   [ ] **Preview Elements:**
            *   Design clear preview elements that visually demonstrate each token.
            *   **Crucially, style these preview elements using the generated CSS custom properties.**
                *   *Example (spacing preview):* A div with `style="margin-top: var(--spacing-{{ slug }}); width: 100px; height: 20px; background: grey;"` above another element.
                *   *Example (border preview):* `<div style="width:100px; height:50px; border-width: var(--borders-widths-{{ slug }}); border-style: var(--borders-styles-{{ style_slug }}); border-color: var(--colors-primary);">Preview</div>` (Note: border color might come from `colors.json`).
            *   Ensure the text or content within the preview also helps demonstrate the token.
        *   [ ] **Dynamic Updates:** Verify that after saving changes (which updates the JSON and triggers CSS variable regeneration), the preview elements correctly reflect the new styles via the CSS variables on page reload.

    4.  **AJAX Handler (in `functions.php` or a dedicated includes file like `inc/ajax-handlers.php`):**
        *   [ ] **Create/Verify Handler:** Ensure a WordPress AJAX action handler exists (e.g., `wp_ajax_save_spacing_data`) to receive data from the editor form. Model on `save_typography_data()`.
        *   [ ] **Security:** Implement nonce verification (`check_ajax_referer('save_<primitive_name>_nonce', 'nonce')`) and capability checks (`current_user_can('edit_theme_options')`).
        *   [ ] **Data Processing:** Sanitize and validate incoming data from `$_POST['data']`.
        *   [ ] **JSON Update:** Read the existing JSON file (`get_template_directory() . '/primitives/<primitive_name>.json'`), update it with the new data using `array_replace_recursive` or similar, and save it back using `file_put_contents`. Ensure proper JSON encoding (`JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES`).
        *   [ ] **Response:** Send a JSON success or error response (`wp_send_json_success()` or `wp_send_json_error()`).

    5.  **WordPress Admin Page Registration (e.g., in `inc/admin-pages.php` or `functions.php`):**
        *   [ ] **Register Page:** Ensure the editor page is registered (e.g., using `add_submenu_page` under a main "Design Book" menu). Model on existing editor page registrations.
        *   [ ] **Callback Function:** The callback function for `add_submenu_page` should prepare any necessary context (like nonce for saving) and render the correct `primitive_name-editor.twig` template using Timber (e.g., `Timber::render('design-book-editors/<primitive_name>-editor.twig', $context);`).

    6.  **JavaScript (e.g., `js/design-book-editors/<primitive_name>-editor.js`):**
        *   [ ] **Enqueue Script:** Enqueue a dedicated JavaScript file for the editor page if client-side interactivity (beyond standard form submission) is needed. Ensure it's enqueued only on that specific admin page. Pass nonce to script using `wp_localize_script`.
        *   [ ] **AJAX Submission:** Implement the JavaScript to handle form submission via AJAX (using `fetch` or jQuery `$.ajax`), sending data to the corresponding AJAX handler. Include the nonce.
        *   [ ] **UI Feedback:** Provide user feedback (e.g., "Saving...", "Saved!", "Error").

    7.  **Styling (`css/design-book-editors.css` or specific editor CSS):**
        *   [ ] **Editor UI Styling:** Ensure the editor's own UI elements ("work clothes") are styled consistently with other editors and are accessible. Use existing CSS classes where possible.
        *   [ ] **Preview Styling:** Add any necessary fixed styles for the preview areas themselves (e.g., layout of preview cards, fixed font sizes for labels within previews if not token-driven).

    - [ ] **Spacing:**
        - [ ] **Follow the "Pattern for Integrating/Refactoring Primitive Editors" above.**
        - [ ] Specific considerations for Spacing:
            - [ ] JSON File: `primitives/spacing.json`. Example: `{"xs": "0.25rem", "sm": "0.5rem", "md": "1rem", "lg": "2rem", "xl": "4rem"}`.
            - [ ] CSS Variables: Ensure generation like `--spacing-xs`, `--spacing-md`.
            - [ ] Editor Template: `spacing-editor.twig`. Previews should visually show different spacing values (e.g., using `margin` or `padding` on preview elements, or showing rulers/blocks of different sizes).

    - [ ] **Borders (New Primitive):**
        - [ ] **Follow the "Pattern for Integrating/Refactoring Primitive Editors" above.**
        - [ ] Specific considerations for Borders:
            - [ ] JSON File: Create `primitives/borders.json`.
                *   *Suggested Structure:*
                    ```json
                    {
                      "widths": { "thin": "1px", "medium": "2px", "thick": "3px", "none": "0px" },
                      "styles": { "solid": "solid", "dashed": "dashed", "dotted": "dotted", "none": "none" },
                      "radii": { "none": "0", "xs": "0.125rem", "sm": "0.25rem", "md": "0.5rem", "lg": "1rem", "full": "9999px" }
                    }
                    ```
                *   Note: Border colors should primarily be sourced from `colors.json` using CSS variables (e.g., `border-color: var(--colors-neutral-500);`). The `borders.json` file itself does not need to store color values, but rather the structural aspects of borders.
            - [ ] CSS Variables: Ensure generation like `--borders-widths-thin`, `--borders-styles-solid`, `--borders-radii-sm`.
            - [ ] Editor Template: Create `borders-editor.twig`. Previews should allow combining width, style, and radius on a sample box. The color can be a fixed preview color or selectable from color tokens.

    - [ ] **Shadows (New Primitive):**
        - [ ] **Follow the "Pattern for Integrating/Refactoring Primitive Editors" above.**
        - [ ] Specific considerations for Shadows:
            - [ ] JSON File: Create `primitives/shadows.json`.
                *   *Suggested Structure:*
                    ```json
                    {
                      "xs": "0 1px 2px 0 rgba(0, 0, 0, 0.03)",
                      "sm": "0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.04)",
                      "md": "0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.05)",
                      "lg": "0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04)",
                      "xl": "0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.03)",
                      "inner": "inset 0 2px 4px 0 rgba(0, 0, 0, 0.05)",
                      "none": "none"
                    }
                    ```
            - [ ] CSS Variables: Ensure generation like `--shadows-xs`, `--shadows-md`.
            - [ ] Editor Template: Create `shadows-editor.twig`. Previews should apply `box-shadow: var(--shadows-{{ slug }});` to sample elements to demonstrate the effect.
- **Initial `design-book-editors.css` Cleanup:**
    - [ ] Remove any manually added `:root` CSS variables from `design-book-editors.css` that are now dynamically generated by the PHP function.
    - [ ] Ensure basic functionality of editor UI; full "work clothes" styling can be refined in Phase 3.

## Future Phases Roadmap

### Phase 2: Component Refactoring & Styling
**Goal:** Refactor existing theme components and guide future component development to primarily use the generated CSS Custom Properties for styling, ensuring consistency and maintainability.
- [ ] **Identify Key Components for Refactoring:** (e.g., `text-book`, `card-component`, `button-component`, etc.)
- [ ] **Develop Component Styling Guidelines:** Document best practices for using CSS variables in components.
- [ ] **Refactor Component CSS/Styles:** Systematically update components to use `var(--css-variable-name)` instead of direct `load_primitive()` for styling attributes.
- [ ] **Test Components:** Verify correct rendering and reactivity to primitive changes.
- [ ] **Complete `ATOMIC-DESIGN-SYSTEM.md` Refactoring:** Fully rewrite this document to focus on component building principles using CSS variables and referencing the `PRIMITIVE-DESIGN-LIBRARY.md`.

### Phase 3: Design Book Editor UI/UX Refinement
**Goal:** Enhance the Design Book Editor's own UI for optimal usability, accessibility, and maintainability, implementing the "fixed work clothes" concept.
- [ ] **Static HTML/CSS Mockups:** Create detailed mockups for common editor UI elements (token cards, input groups, section layouts) focusing on clarity, accessibility, and a consistent visual language for the editor itself.
- [ ] **Implement Editor "Work Clothes" CSS:** Apply the refined, fixed styles from mockups to `design-book-editors.css` for the editor's chrome and overall UI.
- [ ] **Internal Editor UI Componentization (Optional/Advanced):** Refactor common UI patterns within the editor's Twig templates into reusable internal Twig components (e.g., `_editor-token-card.twig`) to improve the editor's codebase.

### Phase 4: `theme.json` Synchronization (Optional Integration)
**Goal:** Provide robust, optional synchronization mechanisms between the primitive JSONs and the theme's `theme.json` file for Gutenberg compatibility.
- [ ] Review and refine existing `sync_typography_to_theme_json()` AJAX handler.
- [ ] Develop similar sync mechanisms or a unified handler for colors, spacing, etc.
- [ ] Explore runtime filter approach (e.g., `theme_json_data_theme`) as an alternative or supplement to direct file writing for `theme.json`.
- [ ] Document `theme.json` integration methods and best practices for theme developers.

### Phase 5: Advanced Features & Polish
**Goal:** Add advanced capabilities and further polish the entire system.
- [ ] Import/Export functionality for primitive sets (e.g., sharing token packages).
- [ ] Version control/history tracking for changes to primitive JSON files.
- [ ] JSON schema validation for primitive files to ensure data integrity.
- [ ] Performance optimizations (caching strategies for generated CSS, JSON parsing).
- [ ] Comprehensive end-user and developer documentation review and expansion.
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

### 8. Developer Experience
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
