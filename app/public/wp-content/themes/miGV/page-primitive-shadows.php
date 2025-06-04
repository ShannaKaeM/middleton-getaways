<?php
/**
 * Template Name: Primitive - Shadows
 * Description: Visual editor for shadow design tokens
 */

// Enqueue necessary scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.0');
    wp_enqueue_script('primitive-shadows', get_template_directory_uri() . '/assets/js/primitive-shadows.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('primitive-shadows', 'primitiveShadows', array(
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

// The shadows editor template will include the primitive book directly
// No need to pass theme.json data since we're using self-sufficient primitives

// Render the shadows editor template
Timber::render('design-book-editors/shadows-editor.twig', $context);

// Get footer
get_footer();