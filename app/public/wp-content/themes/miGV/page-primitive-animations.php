<?php
/**
 * Template Name: Primitive - Animations
 * Description: Visual editor for animation design tokens
 */

// Enqueue necessary scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.0');
    wp_enqueue_script('primitive-animations', get_template_directory_uri() . '/assets/js/primitive-animations.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('primitive-animations', 'primitiveAnimations', array(
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

// Render the animations editor template
Timber::render('design-book-editors/animations-editor.twig', $context);

// Get footer
get_footer();
