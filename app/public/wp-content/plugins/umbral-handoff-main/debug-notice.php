<?php
/**
 * Debug admin notice to help troubleshoot
 */

add_action('admin_notices', function() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'page') {
        $cmb2_available = class_exists('CMB2') ? 'YES' : 'NO';
        $components_class = class_exists('UmbralEditor_Components_Field') ? 'YES' : 'NO';
        $page_metabox_class = class_exists('UmbralEditor_Page_Metabox') ? 'YES' : 'NO';
        
        // Check if our hooks are registered
        $render_hook = has_action('cmb2_render_components_field') ? 'YES' : 'NO';
        $sanitize_hook = has_filter('cmb2_sanitize_components_field') ? 'YES' : 'NO';
        
        // Check if script files exist
        $main_script = file_exists(plugin_dir_path(__FILE__) . '../dist/js/umbral-editor.js') ? 'YES' : 'NO';
        $components_script = file_exists(plugin_dir_path(__FILE__) . '../dist/js/umbral-components-field.js') ? 'YES' : 'NO';
        
        echo '<div class="notice notice-info">';
        echo '<h3>🔧 Umbral Editor Debug Info</h3>';
        echo '<p><strong>CMB2 Available:</strong> ' . $cmb2_available . '</p>';
        echo '<p><strong>Components Field Class:</strong> ' . $components_class . '</p>';
        echo '<p><strong>Page Metabox Class:</strong> ' . $page_metabox_class . '</p>';
        echo '<p><strong>Render Hook Registered:</strong> ' . $render_hook . '</p>';
        echo '<p><strong>Sanitize Hook Registered:</strong> ' . $sanitize_hook . '</p>';
        echo '<p><strong>Main Script Built:</strong> ' . $main_script . '</p>';
        echo '<p><strong>Components Script Built:</strong> ' . $components_script . '</p>';
        echo '<p><strong>Current Screen:</strong> ' . $screen->id . '</p>';
        echo '<p><em>Check your WordPress debug.log and browser console for more info.</em></p>';
        echo '</div>';
    }
});