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

// Get custom typography data from theme.json
$custom_typography = $theme_json['custom']['typography']['baseStyles'] ?? [];
$font_weights = $custom_typography['fontWeights'] ?? [];
$line_heights = $custom_typography['lineHeights'] ?? [];
$letter_spacings = $custom_typography['letterSpacing'] ?? [];

// Convert font weights to array format if needed
if (!empty($font_weights) && !isset($font_weights[0])) {
    $weights_array = [];
    foreach ($font_weights as $slug => $value) {
        $weights_array[] = [
            'slug' => $slug,
            'value' => $value
        ];
    }
    $font_weights = $weights_array;
}

// Convert line heights to array format if needed
if (!empty($line_heights) && !isset($line_heights[0])) {
    $heights_array = [];
    foreach ($line_heights as $slug => $value) {
        $heights_array[] = [
            'slug' => $slug,
            'value' => $value
        ];
    }
    $line_heights = $heights_array;
}

// Convert letter spacing to array format if needed
if (!empty($letter_spacings) && !isset($letter_spacings[0])) {
    $spacings_array = [];
    foreach ($letter_spacings as $slug => $value) {
        $spacings_array[] = [
            'slug' => $slug,
            'value' => $value
        ];
    }
    $letter_spacings = $spacings_array;
}

// Set up Timber context
$context = Timber::context();
$context['font_sizes'] = $font_sizes;
$context['font_families'] = $font_families;
$context['font_weights'] = $font_weights;
$context['line_heights'] = $line_heights;
$context['letter_spacings'] = $letter_spacings;
$context['can_edit'] = current_user_can('edit_theme_options');

// Render the template
Timber::render('primitives/typography-editor.twig', $context);
