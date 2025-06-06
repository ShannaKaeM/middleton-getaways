<?php 

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register Timber template paths for Umbral Editor components
 */
add_filter('timber/locations', function ($paths) {
    error_log('Umbral Editor: Registering Timber paths');
    
    // Register components namespace with priority hierarchy
    $child_components_path = get_stylesheet_directory() . '/umbral/editor/components';
    $parent_components_path = get_template_directory() . '/umbral/editor/components';
    $uploads_components_path = wp_upload_dir()['basedir'] . '/umbral/editor/components';
    
    if (is_child_theme() && is_dir($child_components_path)) {
        // Child theme takes priority
        $paths['components'] = [$child_components_path];
        error_log('Umbral Editor: Registered components path from child theme: ' . $child_components_path);
    } elseif (is_dir($parent_components_path)) {
        // Parent theme fallback
        $paths['components'] = [$parent_components_path];
        error_log('Umbral Editor: Registered components path from parent theme: ' . $parent_components_path);
    } elseif (is_dir($uploads_components_path)) {
        // Uploads directory fallback
        $paths['components'] = [$uploads_components_path];
        error_log('Umbral Editor: Registered components path from uploads: ' . $uploads_components_path);
    }

    // Register primitives namespace with priority hierarchy
    $child_primitives_path = get_stylesheet_directory() . '/umbral/editor/primitives';
    $parent_primitives_path = get_template_directory() . '/umbral/editor/primitives';
    $uploads_primitives_path = wp_upload_dir()['basedir'] . '/umbral/editor/primitives';
    
    if (is_child_theme() && is_dir($child_primitives_path)) {
        // Child theme takes priority
        $paths['primitives'] = [$child_primitives_path];
    } elseif (is_dir($parent_primitives_path)) {
        // Parent theme fallback
        $paths['primitives'] = [$parent_primitives_path];
    } elseif (is_dir($uploads_primitives_path)) {
        // Uploads directory fallback
        $paths['primitives'] = [$uploads_primitives_path];
    }
    
    return $paths;
});