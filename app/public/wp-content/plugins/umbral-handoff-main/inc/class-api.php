<?php
/**
 * REST API functionality for Umbral Editor
 */

class UmbralEditor_API {
    
    private $user_endpoint;
    private $site_endpoint;
    private $breakpoints_endpoint;
    private $render_endpoint;
    
    /**
     * Initialize API functionality
     */
    public function init() {
        add_action('rest_api_init', [$this, 'initRestApi']);
        $this->loadEndpoints();
    }
    
    /**
     * Load API endpoint classes
     */
    private function loadEndpoints() {
        require_once UMBRAL_EDITOR_DIR . 'api/class-user-endpoint.php';
        require_once UMBRAL_EDITOR_DIR . 'api/class-site-endpoint.php';
        require_once UMBRAL_EDITOR_DIR . 'api/class-breakpoints-endpoint.php';
        require_once UMBRAL_EDITOR_DIR . 'api/class-render-endpoint.php';
        
        $this->user_endpoint = new UmbralEditor_User_Endpoint();
        $this->site_endpoint = new UmbralEditor_Site_Endpoint();
        $this->breakpoints_endpoint = new UmbralEditor_Breakpoints_Endpoint();
        $this->render_endpoint = new UmbralEditor_Render_Endpoint();
    }
    
    /**
     * Initialize REST API endpoints
     */
    public function initRestApi() {
        // Register our endpoints
        $this->user_endpoint->register();
        $this->site_endpoint->register();
        $this->breakpoints_endpoint->register();
        $this->render_endpoint->register_routes();
        
        // Register a simple status endpoint
        register_rest_route('umbral-editor/v1', '/status', [
            'methods' => 'GET',
            'callback' => [$this, 'getStatus'],
            'permission_callback' => '__return_true'
        ]);
        
        // Register Components Field endpoints
        register_rest_route('umbral-editor/v1', '/components/available', [
            'methods' => 'GET',
            'callback' => [$this, 'getAvailableComponents'],
            'permission_callback' => [$this, 'checkEditPermission']
        ]);
        
        register_rest_route('umbral-editor/v1', '/components/validate', [
            'methods' => 'POST',
            'callback' => [$this, 'validateComponent'],
            'permission_callback' => [$this, 'checkEditPermission']
        ]);
        
        // File upload endpoint
        register_rest_route('umbral-editor/v1', '/upload', [
            'methods' => 'POST',
            'callback' => [$this, 'uploadFile'],
            'permission_callback' => [$this, 'checkEditPermission']
        ]);
        
        // Components Field data endpoints
        register_rest_route('umbral-editor/v1', '/components-field/(?P<post_id>\d+)/(?P<field_id>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getComponentsFieldData'],
            'permission_callback' => [$this, 'checkEditPermission'],
            'args' => [
                'post_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'field_id' => [
                    'validate_callback' => function($param) {
                        return is_string($param);
                    }
                ]
            ]
        ]);
        
        register_rest_route('umbral-editor/v1', '/components-field/(?P<post_id>\d+)/(?P<field_id>[a-zA-Z0-9_-]+)', [
            'methods' => 'POST',
            'callback' => [$this, 'saveComponentsFieldData'],
            'permission_callback' => [$this, 'checkEditPermission'],
            'args' => [
                'post_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'field_id' => [
                    'validate_callback' => function($param) {
                        return is_string($param);
                    }
                ]
            ]
        ]);
    }
    
    /**
     * API status endpoint (no auth required)
     */
    public function getStatus() {
        return rest_ensure_response([
            'success' => true,
            'message' => 'Umbral Editor API is working!',
            'version' => UMBRAL_EDITOR_VERSION,
            'endpoints' => [
                'user' => rest_url('umbral-editor/v1/user'),
                'site' => rest_url('umbral-editor/v1/site'),
                'status' => rest_url('umbral-editor/v1/status'),
                'upload' => rest_url('umbral-editor/v1/upload'),
                'components_available' => rest_url('umbral-editor/v1/components/available'),
                'components_validate' => rest_url('umbral-editor/v1/components/validate'),
                'breakpoints' => rest_url('umbral-editor/v1/breakpoints'),
                'render_components' => rest_url('umbral-editor/v1/render-components')
            ]
        ]);
    }
    
    /**
     * Get available components for the command palette
     */
    public function getAvailableComponents($request) {
        if (!class_exists('UmbralEditor_Component_Registry')) {
            return new WP_Error('registry_not_found', 'Component registry not loaded', ['status' => 500]);
        }
        
        $registry = UmbralEditor_Component_Registry::getInstance();
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'categories' => $registry->getCategories(),
                'components' => $registry->getComponentsForCommandPalette()
            ]
        ]);
    }
    
    /**
     * Validate component data
     */
    public function validateComponent($request) {
        $component_data = $request->get_json_params();
        
        if (!$component_data) {
            return new WP_Error('invalid_data', 'No component data provided', ['status' => 400]);
        }
        
        if (!class_exists('UmbralEditor_Component_Registry')) {
            return new WP_Error('registry_not_found', 'Component registry not loaded', ['status' => 500]);
        }
        
        $registry = UmbralEditor_Component_Registry::getInstance();
        $is_valid = $registry->validateComponent($component_data);
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'valid' => $is_valid,
                'component_data' => $component_data
            ]
        ]);
    }
    
    /**
     * Get Components Field data via REST API
     */
    public function getComponentsFieldData($request) {
        $post_id = intval($request['post_id']);
        $field_id = sanitize_text_field($request['field_id']);
        
        error_log("Umbral Editor REST: Getting field data for post {$post_id}, field {$field_id}");
        
        // Check if post exists and user can edit it
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('post_not_found', 'Post not found', ['status' => 404]);
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('insufficient_permissions', 'Cannot edit this post', ['status' => 403]);
        }
        
        // Get the field data
        $field_data = get_post_meta($post_id, $field_id, true);
        if (!$field_data) {
            $field_data = [];
        }
        
        // Ensure it's an array
        if (is_string($field_data)) {
            $field_data = json_decode($field_data, true) ?: [];
        }
        
        error_log("Umbral Editor REST: Retrieved data: " . print_r($field_data, true));
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'post_id' => $post_id,
                'field_id' => $field_id,
                'components' => $field_data
            ]
        ]);
    }
    
    /**
     * Save Components Field data via REST API
     */
    public function saveComponentsFieldData($request) {
        $post_id = intval($request['post_id']);
        $field_id = sanitize_text_field($request['field_id']);
        $components_data = $request->get_json_params();
        
        error_log("Umbral Editor REST: Saving field data for post {$post_id}, field {$field_id}");
        error_log("Umbral Editor REST: Data to save: " . print_r($components_data, true));
        
        // Check if post exists and user can edit it
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('post_not_found', 'Post not found', ['status' => 404]);
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('insufficient_permissions', 'Cannot edit this post', ['status' => 403]);
        }
        
        // Validate components data
        if (!isset($components_data['components']) || !is_array($components_data['components'])) {
            return new WP_Error('invalid_data', 'Components data must be an array', ['status' => 400]);
        }
        
        // Sanitize the components data
        $sanitized_components = [];
        if (class_exists('UmbralEditor_Component_Registry')) {
            $registry = UmbralEditor_Component_Registry::getInstance();
            
            foreach ($components_data['components'] as $component) {
                if ($registry->validateComponent($component)) {
                    $sanitized_components[] = $this->sanitizeComponentData($component, $registry);
                }
            }
        } else {
            // Basic sanitization if registry not available
            $sanitized_components = array_map([$this, 'basicSanitizeComponent'], $components_data['components']);
        }
        
        // Save to post meta (serialize array as JSON string)
        $result = update_post_meta($post_id, $field_id, json_encode($sanitized_components));
        
        error_log("Umbral Editor REST: Save result: " . ($result ? 'success' : 'failed'));
        error_log("Umbral Editor REST: Saved data: " . print_r($sanitized_components, true));
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'post_id' => $post_id,
                'field_id' => $field_id,
                'components' => $sanitized_components,
                'saved' => $result !== false
            ]
        ]);
    }
    
    /**
     * Sanitize component data using registry
     */
    private function sanitizeComponentData($component, $registry) {
        $sanitized = [
            'id' => sanitize_text_field($component['id'] ?? uniqid('comp_')),
            'category' => sanitize_text_field($component['category']),
            'component' => sanitize_text_field($component['component']),
            'fields' => []
        ];
        
        // Get component definition for field type validation
        $component_def = $registry->getComponent($component['category'], $component['component']);
        if ($component_def && isset($component['fields']) && is_array($component['fields'])) {
            foreach ($component['fields'] as $field_key => $field_value) {
                if (isset($component_def['fields'][$field_key])) {
                    $field_type = $component_def['fields'][$field_key]['type'];
                    $sanitized['fields'][$field_key] = $this->sanitizeFieldValue($field_value, $field_type);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Basic component sanitization (fallback)
     */
    private function basicSanitizeComponent($component) {
        return [
            'id' => sanitize_text_field($component['id'] ?? uniqid('comp_')),
            'category' => sanitize_text_field($component['category'] ?? ''),
            'component' => sanitize_text_field($component['component'] ?? ''),
            'fields' => is_array($component['fields'] ?? []) ? 
                array_map('sanitize_text_field', $component['fields']) : []
        ];
    }
    
    /**
     * Sanitize individual field values (reuse from Components Field class)
     */
    private function sanitizeFieldValue($value, $type) {
        switch ($type) {
            case 'text':
            case 'text_small':
                return sanitize_text_field($value);
            case 'text_url':
                return esc_url_raw($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'wysiwyg':
                return wp_kses_post($value);
            case 'email':
                return sanitize_email($value);
            case 'select':
            case 'radio':
                return sanitize_text_field($value);
            case 'checkbox':
                return (bool) $value;
            case 'file':
                // File fields store attachment ID as integer
                return $value ? absint($value) : null;
            case 'oembed':
                return esc_url_raw($value);
            case 'group':
                if (is_array($value)) {
                    return array_map(function($item) {
                        return is_array($item) ? array_map('sanitize_text_field', $item) : sanitize_text_field($item);
                    }, $value);
                }
                return [];
            default:
                return sanitize_text_field($value);
        }
    }
    
    /**
     * Handle file upload via REST API
     */
    public function uploadFile($request) {
        // Check if files were uploaded
        $files = $request->get_file_params();
        if (empty($files) || !isset($files['file'])) {
            return new WP_Error('no_file', 'No file was uploaded', ['status' => 400]);
        }
        
        $file = $files['file'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'File upload failed', ['status' => 400]);
        }
        
        // WordPress file upload handling
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        // Handle the upload
        $upload_overrides = [
            'test_form' => false // We're not using a standard form
        ];
        
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // File uploaded successfully, now create attachment
            $attachment = [
                'post_mime_type' => $movefile['type'],
                'post_title' => sanitize_file_name(pathinfo($movefile['file'], PATHINFO_FILENAME)),
                'post_content' => '',
                'post_status' => 'inherit'
            ];
            
            // Insert the attachment
            $attachment_id = wp_insert_attachment($attachment, $movefile['file']);
            
            if (!is_wp_error($attachment_id)) {
                // Generate metadata
                if (!function_exists('wp_generate_attachment_metadata')) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                }
                
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $movefile['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
                
                // Get full attachment data
                $attachment_info = wp_get_attachment_metadata($attachment_id);
                $attachment_url = wp_get_attachment_url($attachment_id);
                
                return rest_ensure_response([
                    'success' => true,
                    'data' => [
                        'id' => $attachment_id,
                        'url' => $attachment_url,
                        'filename' => basename($movefile['file']),
                        'filesize' => size_format(filesize($movefile['file'])),
                        'type' => $movefile['type'],
                        'metadata' => $attachment_info
                    ]
                ]);
            } else {
                // Clean up uploaded file if attachment creation failed
                unlink($movefile['file']);
                return new WP_Error('attachment_error', 'Failed to create attachment', ['status' => 500]);
            }
        } else {
            return new WP_Error('upload_failed', $movefile['error'] ?? 'Upload failed', ['status' => 500]);
        }
    }
    
    /**
     * Permission check for editing content
     */
    public function checkEditPermission() {
        return current_user_can('upload_files') && current_user_can('edit_posts');
    }
}