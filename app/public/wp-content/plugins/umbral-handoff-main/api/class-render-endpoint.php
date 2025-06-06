<?php
/**
 * Render Components Endpoint for Block Editor Preview
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class UmbralEditor_Render_Endpoint {
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        error_log('Umbral Editor: Registering render-components route');
        
        $route_registered = register_rest_route('umbral-editor/v1', '/render-components/(?P<post_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'render_components'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'post_id' => [
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ]
            ]
        ]);
        
        error_log('Umbral Editor: Route registration result: ' . ($route_registered ? 'success' : 'failed'));
        
        // Also register a simple test route
        register_rest_route('umbral-editor/v1', '/render-test', [
            'methods' => 'GET',
            'callback' => [$this, 'test_endpoint'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * Test endpoint to verify route registration
     */
    public function test_endpoint($request) {
        return [
            'success' => true,
            'message' => 'Render endpoint is working!'
        ];
    }
    
    /**
     * Check permissions for the endpoint
     */
    public function check_permissions($request) {
        return current_user_can('edit_posts');
    }
    
    /**
     * Render components for block editor preview
     */
    public function render_components($request) {
        $post_id = $request->get_param('post_id');
        
        if (!$post_id) {
            return new WP_Error('missing_post_id', 'Post ID is required', ['status' => 400]);
        }
        
        try {
            // Get components data from the post meta
            $components_raw = get_post_meta($post_id, 'components', true);
            
            // Decode JSON string to array if needed
            if (is_string($components_raw)) {
                $components = json_decode($components_raw, true);
            } else {
                $components = $components_raw;
            }
            
            // Ensure components is an array
            if (!is_array($components)) {
                $components = [];
            }
            
            if (empty($components)) {
                return [
                    'success' => true,
                    'data' => [
                        'html' => '<div style="border: 2px dashed #ccc; padding: 2rem; text-align: center; border-radius: 8px; background: #f9f9f9;"><h3 style="margin: 0 0 1rem 0; color: #666;">Umbral Components Block</h3><p style="margin: 0; color: #888;">Add components using the Umbral Editor CMB2 field</p></div>',
                        'components_count' => 0
                    ]
                ];
            }
            
            // Prepare Timber context
            $context = Timber::context();
            $context['post'] = Timber::get_post($post_id);
            $context['block_id'] = 'umbral-components-preview-' . uniqid();
            
            // Get breakpoints for responsive styles
            $breakpoints_manager = UmbralEditor_Breakpoints::getInstance();
            $breakpoints = $breakpoints_manager->getBreakpoints();
            $context['breakpoints'] = $breakpoints;
            
            $output = '';
            
            foreach ($components as $component_data) {
                $rendered = $this->render_component($component_data, $context, $breakpoints);
                $output .= $rendered;
            }
            
            // Wrap in a container for block editor styling
            $wrapped_output = '<div class="umbral-components-block-preview" style="background: #fff;">' . $output . '</div>';
            
            return [
                'success' => true,
                'data' => [
                    'html' => $wrapped_output,
                    'components_count' => count($components)
                ]
            ];
            
        } catch (Exception $e) {
            return new WP_Error('render_error', 'Failed to render components: ' . $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Render individual component (copied from block index.php)
     */
    private function render_component($component_data, $context, $breakpoints) {
        $active_dir = $this->get_active_directory();
        if (!$active_dir) {
            return '<p>No Umbral directory found</p>';
        }
        
        $category = $component_data['category'] ?? '';
        $component = $component_data['component'] ?? '';
        $component_fields = $component_data['fields'] ?? [];
        
        if (!$category || !$component) {
            return '<p>Invalid component data - Category: ' . esc_html($category) . ', Component: ' . esc_html($component) . '</p>';
        }
        
        $render_file = $active_dir . "/editor/components/{$category}/{$component}/render.php";
        
        if (!file_exists($render_file)) {
            return "<p>Component render file not found: {$category}/{$component} at {$render_file}</p>";
        }
        
        // Make variables available to the render file
        $component_data = $component_fields; // Pass the fields data as component_data
        
        // Include and execute the render file
        ob_start();
        include $render_file;
        $rendered = ob_get_clean();
        
        if (empty($rendered)) {
            return "<p>Component rendered but returned empty content: {$category}/{$component}</p>";
        }
        
        return $rendered;
    }
    
    /**
     * Get the active umbral directory based on priority (copied from block index.php)
     */
    private function get_active_directory() {
        $child_umbral = get_stylesheet_directory() . '/umbral';
        $parent_umbral = get_template_directory() . '/umbral';
        $uploads_umbral = wp_upload_dir()['basedir'] . '/umbral';
        
        if (is_child_theme() && is_dir($child_umbral . '/editor')) {
            return $child_umbral;
        } elseif (is_dir($parent_umbral . '/editor')) {
            return $parent_umbral;
        } elseif (is_dir($uploads_umbral . '/editor')) {
            return $uploads_umbral;
        }
        
        return null;
    }
}