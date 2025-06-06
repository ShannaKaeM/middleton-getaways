<?php
/**
 * Main CMB2 Components Field Implementation
 */

class UmbralEditor_Components_Field {
    
    /**
     * Field type name
     */
    const FIELD_TYPE = 'components_field';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register the field type hooks
        add_action('cmb2_render_' . self::FIELD_TYPE, [$this, 'render'], 10, 5);
        
        // Add field type to CMB2's allowed types
        add_filter('cmb2_valid_field_types', [$this, 'addFieldType']);
    }
    
    /**
     * Add our field type to CMB2's valid field types
     */
    public function addFieldType($field_types) {
        $field_types[] = self::FIELD_TYPE;
        return $field_types;
    }
    
    
    /**
     * Render the components field - React handles everything via REST API
     */
    public function render($field, $escaped_value, $object_id, $object_type, $field_type_object) {
        $field_id = $field->args('id');
        $field_name = $field_type_object->_name();
        
        ?>
        <!-- Hidden input for form backup -->
        <input 
            type="hidden" 
            name="<?php echo esc_attr($field_name); ?>" 
            id="<?php echo esc_attr($field_id); ?>" 
            value=""
        />
        
        <!-- React component - fetches everything via REST API -->
        <umbral-components-field 
            field-id="<?php echo esc_attr($field_id); ?>"
            field-name="<?php echo esc_attr($field_name); ?>"
            post-id="<?php echo esc_attr($object_id); ?>"
            rest-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
        ></umbral-components-field>
        
        <?php
        // Add description if provided
        if ($field->args('desc')) {
            echo '<p class="cmb2-metabox-description">' . $field->args('desc') . '</p>';
        }
    }
    
    
    
}