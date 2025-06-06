<?php
/**
 * Debug test - Simple CMB2 metabox to verify CMB2 is working
 */

// Hook into CMB2 init
add_action('cmb2_admin_init', function() {
    error_log('Debug: CMB2 admin init hook fired');
    
    if (!class_exists('CMB2')) {
        error_log('Debug: CMB2 class not available');
        return;
    }
    
    error_log('Debug: CMB2 class is available, creating test metabox');
    
    $metabox = new_cmb2_box([
        'id' => 'debug_test_metabox',
        'title' => 'DEBUG: CMB2 Test Metabox',
        'object_types' => ['page'],
        'context' => 'normal',
        'priority' => 'high',
    ]);
    
    $metabox->add_field([
        'name' => 'Debug Test Field',
        'id' => 'debug_test_field',
        'type' => 'text',
        'desc' => 'If you see this, CMB2 is working!'
    ]);
    
    error_log('Debug: Test metabox created successfully');
});

// Also test if CMB2 is loaded at all
add_action('init', function() {
    error_log('Debug: WordPress init hook - CMB2 available: ' . (class_exists('CMB2') ? 'YES' : 'NO'));
});

add_action('admin_init', function() {
    error_log('Debug: WordPress admin_init hook - CMB2 available: ' . (class_exists('CMB2') ? 'YES' : 'NO'));
});