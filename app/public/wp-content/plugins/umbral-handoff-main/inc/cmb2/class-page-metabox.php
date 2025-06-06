<?php
/**
 * Main Metabox for Pages with Components Field
 */

class UmbralEditor_Page_Metabox {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('cmb2_admin_init', [$this, 'addPageMetabox']);
    }
    
    /**
     * Add page components metabox
     */
    public function addPageMetabox() {
        // Debug: Check if CMB2 is available
        if (!class_exists('CMB2')) {
            error_log('Umbral Editor: CMB2 class not found when trying to add metabox');
            return;
        }
        
        // Check if admin class exists
        if (!class_exists('UmbralEditor_Admin')) {
            error_log('Umbral Editor: UmbralEditor_Admin class not found when trying to get enabled post types');
            return;
        }
        
        // Get enabled post types from admin settings
        $enabled_post_types = UmbralEditor_Admin::getEnabledPostTypes();
        
        // If no post types are enabled, don't register the metabox
        if (empty($enabled_post_types)) {
            error_log('Umbral Editor: No post types enabled, skipping metabox registration');
            return;
        }
        
        error_log('Umbral Editor: Adding metabox for post types: ' . implode(', ', $enabled_post_types));
        $metabox = new_cmb2_box([
            'id' => 'umbral_components_main',
            'title' => __('Page Builder - Umbral Editor', 'umbral-editor'),
            'object_types' => $enabled_post_types,
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => true,
        ]);
        
        // Add the Components Field
        $metabox->add_field([
            'name' => __('Page Components', 'umbral-editor'),
            'desc' => __('Build your page layout with flexible content components. Click "Add Component" to get started, then choose from Hero sections, Testimonials, Content blocks, and more.', 'umbral-editor'),
            'id' => 'page_components',
            'type' => 'components_field',
            'categories' => [], // Allow all categories
        ]);
        
        // Add settings field
        $metabox->add_field([
            'name' => __('Display Mode', 'umbral-editor'),
            'desc' => __('Choose how to display the components on the frontend', 'umbral-editor'),
            'id' => 'components_display_mode',
            'type' => 'select',
            'options' => [
                'replace' => __('Replace page content entirely', 'umbral-editor'),
                'prepend' => __('Show before page content', 'umbral-editor'),
                'append' => __('Show after page content', 'umbral-editor'),
                'manual' => __('Manual (use template function)', 'umbral-editor'),
            ],
            'default' => 'replace'
        ]);
        
        // Add a simple text field for page settings
        $metabox->add_field([
            'name' => __('Custom Page Title', 'umbral-editor'),
            'desc' => __('Optional custom title to override the default page title', 'umbral-editor'),
            'id' => 'custom_page_title',
            'type' => 'text',
        ]);
    }
}

// Initialize page metabox
add_action('cmb2_init', function() {
    new UmbralEditor_Page_Metabox();
}, 25);