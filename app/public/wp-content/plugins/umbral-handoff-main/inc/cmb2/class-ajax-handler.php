<?php
/**
 * AJAX Handler for Components Field operations
 */

class UmbralEditor_Ajax_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_umbral_get_component_config', [$this, 'getComponentConfig']);
        add_action('wp_ajax_umbral_save_component_order', [$this, 'saveComponentOrder']);
        add_action('wp_ajax_umbral_duplicate_component', [$this, 'duplicateComponent']);
        add_action('wp_ajax_umbral_validate_component_data', [$this, 'validateComponentData']);
    }
    
    /**
     * Get component configuration for the React UI
     */
    public function getComponentConfig() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'umbral_components_field')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $category = sanitize_text_field($_POST['category'] ?? '');
        $component = sanitize_text_field($_POST['component'] ?? '');
        
        if (empty($category) || empty($component)) {
            wp_send_json_error('Missing category or component');
        }
        
        $registry = UmbralEditor_Component_Registry::getInstance();
        $component_config = $registry->getComponent($category, $component);
        
        if (!$component_config) {
            wp_send_json_error('Component not found');
        }
        
        // Process fields for React UI
        $processed_fields = $this->processFieldsForReact($component_config['fields']);
        
        wp_send_json_success([
            'component' => $component_config,
            'fields' => $processed_fields,
            'default_values' => $this->getDefaultValues($component_config['fields'])
        ]);
    }
    
    /**
     * Save component order (for drag and drop)
     */
    public function saveComponentOrder() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'umbral_components_field')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $field_id = sanitize_text_field($_POST['field_id'] ?? '');
        $component_order = json_decode(stripslashes($_POST['component_order'] ?? ''), true);
        
        if (!$post_id || !$field_id || !is_array($component_order)) {
            wp_send_json_error('Invalid data');
        }
        
        // Get existing components (decode from JSON string)
        $existing_components_raw = get_post_meta($post_id, $field_id, true);
        if (is_string($existing_components_raw)) {
            $existing_components = json_decode($existing_components_raw, true);
        } else {
            $existing_components = $existing_components_raw;
        }
        
        if (!is_array($existing_components)) {
            wp_send_json_error('No existing components found');
        }
        
        // Reorder components based on new order
        $reordered_components = [];
        foreach ($component_order as $component_id) {
            foreach ($existing_components as $component) {
                if ($component['id'] === $component_id) {
                    $reordered_components[] = $component;
                    break;
                }
            }
        }
        
        // Update post meta (save as JSON string)
        update_post_meta($post_id, $field_id, json_encode($reordered_components));
        
        wp_send_json_success('Order updated successfully');
    }
    
    /**
     * Duplicate a component
     */
    public function duplicateComponent() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'umbral_components_field')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $component_data = json_decode(stripslashes($_POST['component_data'] ?? ''), true);
        
        if (!$component_data) {
            wp_send_json_error('Invalid component data');
        }
        
        // Create duplicate with new ID
        $duplicate = $component_data;
        $duplicate['id'] = uniqid('comp_');
        
        // Validate the duplicate
        $registry = UmbralEditor_Component_Registry::getInstance();
        if (!$registry->validateComponent($duplicate)) {
            wp_send_json_error('Invalid component for duplication');
        }
        
        wp_send_json_success([
            'component' => $duplicate,
            'message' => 'Component duplicated successfully'
        ]);
    }
    
    /**
     * Validate component data
     */
    public function validateComponentData() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'umbral_components_field')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $component_data = json_decode(stripslashes($_POST['component_data'] ?? ''), true);
        
        if (!$component_data) {
            wp_send_json_error('Invalid component data');
        }
        
        $registry = UmbralEditor_Component_Registry::getInstance();
        $is_valid = $registry->validateComponent($component_data);
        
        if ($is_valid) {
            // Get component definition for field validation
            $component_def = $registry->getComponent($component_data['category'], $component_data['component']);
            $errors = $this->validateFieldValues($component_data['fields'] ?? [], $component_def['fields'] ?? []);
            
            if (empty($errors)) {
                wp_send_json_success('Component data is valid');
            } else {
                wp_send_json_error([
                    'message' => 'Field validation errors',
                    'errors' => $errors
                ]);
            }
        } else {
            wp_send_json_error('Component structure is invalid');
        }
    }
    
    /**
     * Process field definitions for React UI
     */
    private function processFieldsForReact($fields) {
        $processed = [];
        
        foreach ($fields as $field_key => $field_def) {
            $processed_field = $field_def;
            
            // Add React-specific properties
            $processed_field['key'] = $field_key;
            $processed_field['id'] = $field_key;
            
            // Process options for select fields
            if (isset($field_def['options']) && is_array($field_def['options'])) {
                $processed_field['options'] = array_map(function($value, $key) {
                    return ['value' => $key, 'label' => $value];
                }, $field_def['options'], array_keys($field_def['options']));
            }
            
            // Handle group fields (repeatable)
            if ($field_def['type'] === 'group' && isset($field_def['fields'])) {
                $processed_field['fields'] = $this->processFieldsForReact($field_def['fields']);
            }
            
            $processed[$field_key] = $processed_field;
        }
        
        return $processed;
    }
    
    /**
     * Get default values for fields
     */
    private function getDefaultValues($fields) {
        $defaults = [];
        
        foreach ($fields as $field_key => $field_def) {
            if (isset($field_def['default'])) {
                $defaults[$field_key] = $field_def['default'];
            } else {
                // Set sensible defaults based on field type
                switch ($field_def['type']) {
                    case 'text':
                    case 'text_small':
                    case 'text_url':
                    case 'email':
                    case 'textarea':
                    case 'wysiwyg':
                    case 'oembed':
                        $defaults[$field_key] = '';
                        break;
                        
                    case 'checkbox':
                        $defaults[$field_key] = false;
                        break;
                        
                    case 'file':
                        $defaults[$field_key] = null;
                        break;
                        
                    case 'group':
                        $defaults[$field_key] = [];
                        break;
                        
                    case 'select':
                    case 'radio':
                        // Use first option as default
                        if (isset($field_def['options']) && is_array($field_def['options'])) {
                            $defaults[$field_key] = array_keys($field_def['options'])[0] ?? '';
                        } else {
                            $defaults[$field_key] = '';
                        }
                        break;
                        
                    default:
                        $defaults[$field_key] = '';
                }
            }
        }
        
        return $defaults;
    }
    
    /**
     * Validate field values against field definitions
     */
    private function validateFieldValues($field_values, $field_definitions) {
        $errors = [];
        
        foreach ($field_definitions as $field_key => $field_def) {
            $value = $field_values[$field_key] ?? null;
            
            // Check required fields
            if (!empty($field_def['required']) && empty($value)) {
                $errors[$field_key] = sprintf(
                    __('Field "%s" is required', 'umbral-editor'),
                    $field_def['label'] ?? $field_key
                );
                continue;
            }
            
            // Skip validation if field is empty and not required
            if (empty($value)) {
                continue;
            }
            
            // Type-specific validation
            switch ($field_def['type']) {
                case 'email':
                    if (!is_email($value)) {
                        $errors[$field_key] = __('Invalid email address', 'umbral-editor');
                    }
                    break;
                    
                case 'text_url':
                case 'oembed':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[$field_key] = __('Invalid URL', 'umbral-editor');
                    }
                    break;
                    
                case 'file':
                    if (!is_numeric($value) || !get_attached_file($value)) {
                        $errors[$field_key] = __('Invalid file attachment', 'umbral-editor');
                    }
                    break;
                    
                case 'select':
                case 'radio':
                    if (isset($field_def['options']) && !array_key_exists($value, $field_def['options'])) {
                        $errors[$field_key] = __('Invalid option selected', 'umbral-editor');
                    }
                    break;
            }
        }
        
        return $errors;
    }
}

// Initialize the AJAX handler
new UmbralEditor_Ajax_Handler();