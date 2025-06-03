<?php
/**
 * Template Name: Primitive - Spacing
 * Description: Visual editor for spacing design tokens
 */

// Enqueue necessary scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.0');
    wp_enqueue_script('primitive-spacing', get_template_directory_uri() . '/assets/js/primitive-spacing.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('primitive-spacing', 'primitiveSpacing', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mi_design_book_nonce'),
        'canEdit' => current_user_can('edit_theme_options')
    ));
});

// Get theme.json spacing data
$theme_json = wp_get_global_settings();
$spacing_sizes = $theme_json['spacing']['spacingSizes'] ?? [];

// Sort spacing sizes by numeric value
usort($spacing_sizes, function($a, $b) {
    $a_val = floatval(preg_replace('/[^0-9.]/', '', $a['size']));
    $b_val = floatval(preg_replace('/[^0-9.]/', '', $b['size']));
    return $a_val <=> $b_val;
});

// Set up Timber context
$context = Timber::context();
$context['spacing_sizes'] = $spacing_sizes;
$context['can_edit'] = current_user_can('edit_theme_options');

// Render the template
Timber::render('primitives/spacing-editor.twig', $context);
