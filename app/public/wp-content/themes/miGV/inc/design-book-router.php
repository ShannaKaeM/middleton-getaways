<?php
/**
 * Villa Design Book Router
 * Handles dynamic routing for design book pages
 */

if (!defined('ABSPATH')) {
    exit;
}

class VillaDesignBookRouter {
    
    private $sections = [
        'typography' => [
            'name' => 'Typography',
            'description' => 'Manage fonts, sizes, and text styles',
            'icon' => 'text',
            'template' => 'design-book-editors/typography-editor.twig',
            'capabilities' => ['edit_theme_options']
        ],
        'colors' => [
            'name' => 'Colors',
            'description' => 'Manage color palette and schemes',
            'icon' => 'palette',
            'template' => 'design-book-editors/colors-editor.twig',
            'capabilities' => ['edit_theme_options']
        ],
        'layout' => [
            'name' => 'Layout',
            'description' => 'Spacing, grids, and layout tokens',
            'icon' => 'grid',
            'template' => 'design-book-editors/layout-editor.twig',
            'capabilities' => ['edit_theme_options']
        ],
        'components' => [
            'name' => 'Components',
            'description' => 'UI components and patterns',
            'icon' => 'components',
            'template' => 'design-book-editors/components.twig',
            'capabilities' => ['edit_theme_options']
        ],
        'text-component' => [
            'name' => 'Text Component',
            'description' => 'Manage text styles and components',
            'icon' => 'text',
            'template' => 'design-book-editors/sm-components/text-component-editor.twig',
            'capabilities' => ['edit_theme_options']
        ],
        'tokens' => [
            'name' => 'Design Tokens',
            'description' => 'CSS custom properties and variables',
            'icon' => 'code',
            'template' => 'design-book-editors/tokens.twig',
            'capabilities' => ['edit_theme_options']
        ],
        'documentation' => [
            'name' => 'Documentation',
            'description' => 'Usage guidelines and examples',
            'icon' => 'book',
            'template' => 'design-book-editors/documentation.twig',
            'capabilities' => ['read']
        ]
    ];

    public function __construct() {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_design_book_routes']);
        add_action('wp_loaded', [$this, 'flush_rewrite_rules_maybe']);
    }

    /**
     * Add rewrite rules for design book sections
     */
    public function add_rewrite_rules() {
        // Main design book page
        add_rewrite_rule(
            '^design-book/?$',
            'index.php?pagename=design-book',
            'top'
        );

        // Design book sections
        foreach ($this->sections as $slug => $section) {
            add_rewrite_rule(
                '^design-book/' . $slug . '/?$',
                'index.php?pagename=design-book&design_book_section=' . $slug,
                'top'
            );
        }
    }

    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'design_book_section';
        return $vars;
    }

    /**
     * Handle design book routing
     */
    public function handle_design_book_routes() {
        $section = get_query_var('design_book_section');
        
        if (!$section || !is_page('design-book')) {
            return;
        }

        // Check if section exists
        if (!isset($this->sections[$section])) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            return;
        }

        // Check user capabilities
        $section_config = $this->sections[$section];
        if (!empty($section_config['capabilities'])) {
            $has_permission = false;
            foreach ($section_config['capabilities'] as $capability) {
                if (current_user_can($capability)) {
                    $has_permission = true;
                    break;
                }
            }
            
            if (!$has_permission) {
                wp_redirect(wp_login_url(get_permalink()));
                exit;
            }
        }

        // Set up context and render
        $this->render_design_book_section($section);
    }

    /**
     * Render design book section
     */
    private function render_design_book_section($section) {
        // Enqueue design book assets
        wp_enqueue_style('villa-design-book', get_template_directory_uri() . '/assets/css/design-book.css', [], '1.0.0');
        wp_enqueue_script('villa-design-book', get_template_directory_uri() . '/assets/js/design-book.js', ['jquery'], '1.0.0', true);

        // Enqueue section-specific assets
        if ($section === 'colors') {
            wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.1');
            wp_enqueue_script('primitive-colors', get_template_directory_uri() . '/assets/js/primitive-colors.js', array('jquery'), '1.0.1', true);
            
            // Localize script for primitive colors
            wp_localize_script('primitive-colors', 'primitiveColors', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('migv_nonce'),
                'themeUrl' => get_template_directory_uri()
            ));
        } elseif ($section === 'typography') {
            wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.1');
            wp_enqueue_script('primitive-typography', get_template_directory_uri() . '/assets/js/primitive-typography.js', array('jquery'), '1.0.1', true);
            
            // Localize script for primitive typography
            wp_localize_script('primitive-typography', 'primitiveTypography', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('migv_nonce'),
                'themeUrl' => get_template_directory_uri()
            ));
        }

        // Localize script for AJAX
        wp_localize_script('villa-design-book', 'villaDesignBook', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('migv_nonce'),
            'themeUrl' => get_template_directory_uri(),
            'currentSection' => $section
        ]);

        // Get Timber context
        $context = Timber::context();
        $context['post'] = new Timber\Post();

        // Add design book context
        $context['design_book'] = [
            'current_section' => $section,
            'section_config' => $this->sections[$section],
            'navigation' => $this->get_navigation($section),
            'breadcrumbs' => $this->get_breadcrumbs($section)
        ];

        // Add section-specific data
        $context = $this->add_section_data($context, $section);

        // Conditionally enqueue scripts based on section
        if ($section === 'text-component') {
            wp_enqueue_script('migv-text-component-editor-scripts', get_template_directory_uri() . '/assets/js/sm-components/text-component-editor.js', array('jquery'), null, true);
            // Localize script for AJAX if needed
            wp_localize_script('migv-text-component-editor-scripts', 'miGV', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('migv_design_book_nonce'),
                'theme_uri' => get_template_directory_uri()
            ));
        }

        // Get header
        get_header();

        // Render template
        $template = $this->sections[$section]['template'];
        Timber::render($template, $context);
        
        // Get footer
        get_footer();
        exit;
    }

    /**
     * Get navigation with active states
     */
    private function get_navigation($current_section) {
        $navigation = [];
        
        foreach ($this->sections as $slug => $section) {
            $navigation[] = [
                'name' => $section['name'],
                'slug' => $slug,
                'url' => home_url('/design-book/' . $slug . '/'),
                'icon' => $section['icon'],
                'description' => $section['description'],
                'active' => ($slug === $current_section)
            ];
        }
        
        return $navigation;
    }

    /**
     * Get breadcrumbs
     */
    private function get_breadcrumbs($section) {
        return [
            ['name' => 'Dashboard', 'url' => home_url('/dashboard/')],
            ['name' => 'Design Book', 'url' => home_url('/design-book/')],
            ['name' => $this->sections[$section]['name'], 'url' => '']
        ];
    }

    /**
     * Add section-specific data to context
     */
    private function add_section_data($context, $section) {
        switch ($section) {
            case 'typography':
                $context = array_merge($context, $this->get_typography_data());
                break;
            case 'colors':
                $context = array_merge($context, $this->get_colors_editor_data());
                break;
            case 'layout':
                $context['layout'] = $this->get_layout_data();
                break;
            case 'components':
                $context['components'] = $this->get_components_data();
                break;
            case 'tokens':
                $context['tokens'] = $this->get_tokens_data();
                break;
        }
        
        return $context;
    }

    /**
     * Get typography data from theme.json
     */
    private function get_typography_data() {
        $theme_json = wp_get_global_settings();
        
        // Get custom typography data
        $custom_typography = $theme_json['custom']['typography']['baseStyles'] ?? [];
        $font_weights = $custom_typography['fontWeights'] ?? [];
        $line_heights = $custom_typography['lineHeights'] ?? [];
        $letter_spacings = $custom_typography['letterSpacing'] ?? [];
        
        // Convert font weights to array format if needed
        if (!empty($font_weights) && !isset($font_weights[0])) {
            $weights_array = [];
            foreach ($font_weights as $slug => $value) {
                $weights_array[] = [
                    'slug' => $slug,
                    'value' => $value
                ];
            }
            $font_weights = $weights_array;
        }
        
        // Convert line heights to array format if needed
        if (!empty($line_heights) && !isset($line_heights[0])) {
            $heights_array = [];
            foreach ($line_heights as $slug => $value) {
                $heights_array[] = [
                    'slug' => $slug,
                    'value' => $value
                ];
            }
            $line_heights = $heights_array;
        }
        
        // Convert letter spacing to array format if needed
        if (!empty($letter_spacings) && !isset($letter_spacings[0])) {
            $spacings_array = [];
            foreach ($letter_spacings as $slug => $value) {
                $spacings_array[] = [
                    'slug' => $slug,
                    'value' => $value
                ];
            }
            $letter_spacings = $spacings_array;
        }
        
        return [
            'font_families' => $theme_json['typography']['fontFamilies'] ?? [],
            'font_sizes' => $theme_json['typography']['fontSizes'] ?? [],
            'font_weights' => $font_weights,
            'line_heights' => $line_heights,
            'letter_spacings' => $letter_spacings,
            'current_scale' => get_theme_mod('villa_typography_scale', 1.25),
            'base_font_size' => get_theme_mod('villa_base_font_size', 16)
        ];
    }

    /**
     * Get colors data for the editor with proper grouping
     */
    private function get_colors_editor_data() {
        // Get colors from theme.json
        $theme_json_path = get_template_directory() . '/theme.json';
        $theme_json = [];
        
        if (file_exists($theme_json_path)) {
            $theme_json = json_decode(file_get_contents($theme_json_path), true);
        }
        
        $colors = $theme_json['settings']['color']['palette'] ?? [];
        
        // If no colors found, return empty structure
        if (empty($colors)) {
            return ['color_groups' => []];
        }
        
        // Create a lookup array for easy access
        $color_lookup = [];
        foreach ($colors as $color) {
            if (isset($color['slug'])) {
                $color_lookup[$color['slug']] = $color;
            }
        }
        
        // Define the color groups structure
        $color_groups = [
            'primary' => [
                'title' => 'Primary Colors',
                'colors' => []
            ],
            'secondary' => [
                'title' => 'Secondary Colors',
                'colors' => []
            ],
            'neutral' => [
                'title' => 'Neutral Colors',
                'colors' => []
            ],
            'base' => [
                'title' => 'Base Colors',
                'colors' => []
            ],
            'extreme' => [
                'title' => 'Extreme Colors',
                'colors' => []
            ],
            'other' => [
                'title' => 'Other Colors',
                'colors' => []
            ]
        ];
        
        // Populate the groups with colors from theme.json
        // Primary group
        if (isset($color_lookup['primary-light'])) $color_groups['primary']['colors'][] = $color_lookup['primary-light'];
        if (isset($color_lookup['primary'])) $color_groups['primary']['colors'][] = $color_lookup['primary'];
        if (isset($color_lookup['primary-dark'])) $color_groups['primary']['colors'][] = $color_lookup['primary-dark'];
        
        // Secondary group
        if (isset($color_lookup['secondary-light'])) $color_groups['secondary']['colors'][] = $color_lookup['secondary-light'];
        if (isset($color_lookup['secondary'])) $color_groups['secondary']['colors'][] = $color_lookup['secondary'];
        if (isset($color_lookup['secondary-dark'])) $color_groups['secondary']['colors'][] = $color_lookup['secondary-dark'];
        
        // Neutral group
        if (isset($color_lookup['neutral-light'])) $color_groups['neutral']['colors'][] = $color_lookup['neutral-light'];
        if (isset($color_lookup['neutral'])) $color_groups['neutral']['colors'][] = $color_lookup['neutral'];
        if (isset($color_lookup['neutral-dark'])) $color_groups['neutral']['colors'][] = $color_lookup['neutral-dark'];
        
        // Base group
        if (isset($color_lookup['base-lightest'])) $color_groups['base']['colors'][] = $color_lookup['base-lightest'];
        if (isset($color_lookup['base-light'])) $color_groups['base']['colors'][] = $color_lookup['base-light'];
        if (isset($color_lookup['base'])) $color_groups['base']['colors'][] = $color_lookup['base'];
        if (isset($color_lookup['base-dark'])) $color_groups['base']['colors'][] = $color_lookup['base-dark'];
        if (isset($color_lookup['base-darkest'])) $color_groups['base']['colors'][] = $color_lookup['base-darkest'];
        
        // Extreme group
        if (isset($color_lookup['extreme-light'])) $color_groups['extreme']['colors'][] = $color_lookup['extreme-light'];
        if (isset($color_lookup['extreme-dark'])) $color_groups['extreme']['colors'][] = $color_lookup['extreme-dark'];
        
        // Other colors (any remaining colors not in the above groups)
        $assigned_slugs = [];
        foreach ($color_groups as $group) {
            foreach ($group['colors'] as $color) {
                $assigned_slugs[] = $color['slug'];
            }
        }
        
        foreach ($colors as $color) {
            if (!in_array($color['slug'], $assigned_slugs)) {
                $color_groups['other']['colors'][] = $color;
            }
        }
        
        // Remove empty groups
        $color_groups = array_filter($color_groups, function($group) {
            return !empty($group['colors']);
        });
        
        return [
            'color_groups' => $color_groups,
            'debug_colors' => $colors,
            'debug_color_count' => count($colors)
        ];
    }

    /**
     * Get layout data
     */
    private function get_layout_data() {
        return [
            'spacing_scale' => get_theme_mod('villa_spacing_scale', []),
            'breakpoints' => get_theme_mod('villa_breakpoints', []),
            'container_widths' => get_theme_mod('villa_container_widths', [])
        ];
    }

    /**
     * Get components data
     */
    private function get_components_data() {
        return [
            'button_styles' => get_theme_mod('villa_button_styles', []),
            'form_styles' => get_theme_mod('villa_form_styles', []),
            'card_styles' => get_theme_mod('villa_card_styles', [])
        ];
    }

    /**
     * Get design tokens
     */
    private function get_tokens_data() {
        $theme_json_path = get_template_directory() . '/theme.json';
        $theme_json = file_exists($theme_json_path) ? json_decode(file_get_contents($theme_json_path), true) : [];
        
        return [
            'theme_json' => $theme_json,
            'css_variables' => $this->generate_css_variables($theme_json)
        ];
    }

    /**
     * Generate CSS variables from theme.json
     */
    private function generate_css_variables($theme_json) {
        $variables = [];
        
        // Colors
        if (isset($theme_json['settings']['color']['palette'])) {
            foreach ($theme_json['settings']['color']['palette'] as $color) {
                $variables['colors'][] = [
                    'name' => '--wp--preset--color--' . $color['slug'],
                    'value' => $color['color'],
                    'description' => $color['name']
                ];
            }
        }
        
        // Font sizes
        if (isset($theme_json['settings']['typography']['fontSizes'])) {
            foreach ($theme_json['settings']['typography']['fontSizes'] as $size) {
                $variables['typography'][] = [
                    'name' => '--wp--preset--font-size--' . $size['slug'],
                    'value' => $size['size'],
                    'description' => ucfirst(str_replace('-', ' ', $size['slug']))
                ];
            }
        }
        
        return $variables;
    }

    /**
     * Flush rewrite rules if needed
     */
    public function flush_rewrite_rules_maybe() {
        if (get_option('villa_design_book_rewrite_rules_flushed') !== '2') {
            flush_rewrite_rules();
            update_option('villa_design_book_rewrite_rules_flushed', '2');
        }
    }

    /**
     * Get available sections
     */
    public function get_sections() {
        return $this->sections;
    }
}

// Initialize the router
new VillaDesignBookRouter();
