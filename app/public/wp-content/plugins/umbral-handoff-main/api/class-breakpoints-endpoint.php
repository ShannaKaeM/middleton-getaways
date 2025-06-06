<?php
/**
 * REST API endpoint for breakpoints management
 */

class UmbralEditor_Breakpoints_Endpoint {
    
    /**
     * Namespace for the REST API
     */
    const NAMESPACE = 'umbral-editor/v1';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Constructor kept for compatibility, but registration now handled by register() method
    }
    
    /**
     * Register the endpoint (called by API class)
     */
    public function register() {
        $this->register_routes();
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Get all breakpoints
        register_rest_route(self::NAMESPACE, '/breakpoints', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_breakpoints'],
            'permission_callback' => '__return_true',
        ]);
        
        // Update all breakpoints
        register_rest_route(self::NAMESPACE, '/breakpoints', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_breakpoints'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'breakpoints' => [
                    'required' => true,
                    'type' => 'object',
                    'description' => 'Breakpoints configuration object'
                ]
            ]
        ]);
        
        // Get single breakpoint
        register_rest_route(self::NAMESPACE, '/breakpoints/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_breakpoint'],
            'permission_callback' => '__return_true',
            'args' => [
                'key' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Breakpoint key'
                ]
            ]
        ]);
        
        // Update single breakpoint
        register_rest_route(self::NAMESPACE, '/breakpoints/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_breakpoint'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'key' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Breakpoint key'
                ],
                'label' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Breakpoint label'
                ],
                'min_width' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'Minimum width in pixels'
                ],
                'max_width' => [
                    'type' => ['integer', 'null'],
                    'description' => 'Maximum width in pixels'
                ],
                'icon' => [
                    'type' => 'string',
                    'description' => 'Breakpoint icon'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Breakpoint description'
                ]
            ]
        ]);
        
        // Add new breakpoint
        register_rest_route(self::NAMESPACE, '/breakpoints', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_breakpoint'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'key' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Breakpoint key'
                ],
                'label' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Breakpoint label'
                ],
                'min_width' => [
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'Minimum width in pixels'
                ],
                'max_width' => [
                    'type' => ['integer', 'null'],
                    'description' => 'Maximum width in pixels'
                ],
                'icon' => [
                    'type' => 'string',
                    'description' => 'Breakpoint icon'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Breakpoint description'
                ]
            ]
        ]);
        
        // Delete breakpoint
        register_rest_route(self::NAMESPACE, '/breakpoints/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [$this, 'delete_breakpoint'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'key' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Breakpoint key'
                ]
            ]
        ]);
        
        // Reset to defaults
        register_rest_route(self::NAMESPACE, '/breakpoints/reset', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'reset_breakpoints'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }
    
    /**
     * Get all breakpoints
     */
    public function get_breakpoints($request) {
        $breakpoints_api = UmbralEditor_Breakpoints::getInstance();
        $breakpoints = $breakpoints_api->getBreakpointsForAPI();
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $breakpoints
        ], 200);
    }
    
    /**
     * Update all breakpoints
     */
    public function update_breakpoints($request) {
        $breakpoints_api = UmbralEditor_Breakpoints::getInstance();
        $breakpoints = $request->get_param('breakpoints');
        
        $result = $breakpoints_api->updateBreakpoints($breakpoints);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Breakpoints updated successfully',
            'data' => $breakpoints_api->getBreakpointsForAPI()
        ], 200);
    }
    
    /**
     * Get single breakpoint
     */
    public function get_breakpoint($request) {
        $breakpoints_api = UmbralEditor_Breakpoints::getInstance();
        $key = $request->get_param('key');
        
        $breakpoint = $breakpoints_api->getBreakpoint($key);
        
        if (!$breakpoint) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Breakpoint not found'
            ], 404);
        }
        
        $breakpoint['key'] = $key;
        $breakpoint['media_query'] = $breakpoints_api->getMediaQuery($key);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $breakpoint
        ], 200);
    }
    
    /**
     * Update single breakpoint
     */
    public function update_breakpoint($request) {
        $breakpoints_api = UmbralEditor_Breakpoints::getInstance();
        $key = $request->get_param('key');
        
        $breakpoint_data = [
            'label' => $request->get_param('label'),
            'min_width' => $request->get_param('min_width'),
            'max_width' => $request->get_param('max_width'),
            'icon' => $request->get_param('icon'),
            'description' => $request->get_param('description')
        ];
        
        $result = $breakpoints_api->updateBreakpoint($key, $breakpoint_data);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Breakpoint updated successfully',
            'data' => $breakpoints_api->getBreakpoint($key)
        ], 200);
    }
    
    /**
     * Create new breakpoint
     */
    public function create_breakpoint($request) {
        $breakpoints_api = UmbralEditor_Breakpoints::getInstance();
        $key = $request->get_param('key');
        
        $breakpoint_data = [
            'label' => $request->get_param('label'),
            'min_width' => $request->get_param('min_width'),
            'max_width' => $request->get_param('max_width'),
            'icon' => $request->get_param('icon'),
            'description' => $request->get_param('description')
        ];
        
        $result = $breakpoints_api->addBreakpoint($key, $breakpoint_data);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Breakpoint created successfully',
            'data' => $breakpoints_api->getBreakpoint($key)
        ], 201);
    }
    
    /**
     * Delete breakpoint
     */
    public function delete_breakpoint($request) {
        $breakpoints_api = UmbralEditor_Breakpoints::getInstance();
        $key = $request->get_param('key');
        
        $result = $breakpoints_api->deleteBreakpoint($key);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Breakpoint deleted successfully'
        ], 200);
    }
    
    /**
     * Reset breakpoints to defaults
     */
    public function reset_breakpoints($request) {
        $breakpoints_api = UmbralEditor_Breakpoints::getInstance();
        
        $result = $breakpoints_api->resetToDefaults();
        
        if (!$result) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Failed to reset breakpoints'
            ], 500);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Breakpoints reset to defaults',
            'data' => $breakpoints_api->getBreakpointsForAPI()
        ], 200);
    }
    
    /**
     * Check permissions for breakpoints management
     */
    public function check_permissions($request) {
        // Check if user can manage options (administrators)
        return current_user_can('manage_options');
    }
}