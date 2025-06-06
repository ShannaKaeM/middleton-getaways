<?php
/**
 * Simple CMB2 test - add directly to functions.php if needed
 */

// Ensure CMB2 is loaded
if (file_exists(__DIR__ . '/vendor/cmb2/cmb2/init.php')) {
    require_once __DIR__ . '/vendor/cmb2/cmb2/init.php';
}

// Simple CMB2 metabox using admin settings
add_action('cmb2_admin_init', function() {
    // Get enabled post types from admin settings
    if (class_exists('UmbralEditor_Admin')) {
        $enabled_post_types = UmbralEditor_Admin::getEnabledPostTypes();
        
        // If no post types are enabled, don't register the test metabox
        if (empty($enabled_post_types)) {
            error_log('Simple Test: No post types enabled, skipping test metabox registration');
            return;
        }
        
        error_log('Simple Test: Adding test metabox for post types: ' . implode(', ', $enabled_post_types));
        
        $metabox = new_cmb2_box([
            'id' => 'test_components_metabox',
            'title' => 'Test Components Field',
            'object_types' => $enabled_post_types,
            'context' => 'normal',
            'priority' => 'high',
        ]);

        // Test our custom components field
        $metabox->add_field([
            'name' => 'Page Components',
            'id' => 'components',
            'type' => 'components_field',
            'desc' => 'Testing our custom components field type',
            'categories' => ['hero', 'testimonials', 'content']
        ]);
    } else {
        error_log('Simple Test: UmbralEditor_Admin class not found, using fallback to page only');
        
        // Fallback to page only if admin class not available
        $metabox = new_cmb2_box([
            'id' => 'test_components_metabox',
            'title' => 'Test Components Field',
            'object_types' => ['page'],
            'context' => 'normal',
            'priority' => 'high',
        ]);

        $metabox->add_field([
            'name' => 'Page Components',
            'id' => 'components',
            'type' => 'components_field',
            'desc' => 'Testing our custom components field type',
            'categories' => ['hero', 'testimonials', 'content']
        ]);
    }
});

// Also add a custom field type test
add_action('cmb2_render_test_field', function($field, $value, $object_id, $object_type, $field_type_object) {
    echo '<div style="padding: 20px; border: 2px solid #0073aa; background: #f0f8ff;">';
    echo '<h3>🎯 Custom Field Type Working!</h3>';
    echo '<p>This proves custom CMB2 field types can be rendered.</p>';
    echo '<input type="text" value="' . esc_attr($value) . '" name="' . esc_attr($field_type_object->_name()) . '">';
    echo '</div>';
}, 10, 5);