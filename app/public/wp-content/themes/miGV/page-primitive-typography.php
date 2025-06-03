<?php
/**
 * Template Name: Primitive - Typography
 * Description: Visual editor for typography design tokens
 */

// Enqueue necessary scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.0');
    wp_enqueue_script('primitive-typography', get_template_directory_uri() . '/assets/js/primitive-typography.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('primitive-typography', 'primitiveTypography', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mi_design_book_nonce'),
        'canEdit' => current_user_can('edit_theme_options')
    ));
});

// Get header
get_header();

// Set up Timber context
$context = Timber::context();
$context['post'] = Timber::get_post();

// The typography editor template will include the primitive book directly
// No need to pass theme.json data since we're using self-sufficient primitives

// Render the typography editor template
Timber::render('design-book-editors/typography-editor.twig', $context);

// Get footer
get_footer();
