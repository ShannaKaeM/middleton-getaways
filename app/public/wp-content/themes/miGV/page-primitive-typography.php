<?php
/**
 * Template Name: Primitive - Typography
 * Description: Visual editor for typography design tokens
 */

// Enqueue necessary scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('primitive-editor', get_template_directory_uri() . '/assets/css/primitive-editor.css', array(), '1.0.0');
    wp_enqueue_script('primitive-typography', get_template_directory_uri() . '/assets/js/primitive-typography.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('primitive-typography', 'primitiveTypography', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mi_design_book_nonce'),
        'canEdit' => current_user_can('edit_theme_options')
    ));
});

// Get theme.json typography data
$theme_json = wp_get_global_settings();
$font_sizes = $theme_json['typography']['fontSizes'] ?? [];
$font_families = $theme_json['typography']['fontFamilies'] ?? [];

// Set up Timber context
$context = Timber::context();
$context['font_sizes'] = $font_sizes;
$context['font_families'] = $font_families;
$context['can_edit'] = current_user_can('edit_theme_options');

// Render the template
Timber::render('primitives/typography-editor.twig', $context);
