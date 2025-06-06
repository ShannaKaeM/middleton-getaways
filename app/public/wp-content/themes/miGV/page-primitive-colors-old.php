<?php
/**
 * Template Name: Primitive - Colors
 * Description: Visual editor for color design tokens
 */

// Enqueue necessary scripts and styles directly
wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.1');
wp_enqueue_script('primitive-colors', get_template_directory_uri() . '/assets/js/primitive-colors.js', array('jquery'), '1.0.1', true);

// Localize script for AJAX
wp_localize_script('primitive-colors', 'primitiveColors', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('mi_design_book_nonce'),
    'canEdit' => current_user_can('edit_theme_options')
));

// Get header
get_header();

// Set up Timber context
$context = Timber::context();
$context['can_edit'] = current_user_can('edit_theme_options');

// Render the template
Timber::render('design-book-editors/colors-editor.twig', $context);

// Get footer
get_footer();
