<?php
/**
 * Middleton Getaways Blocksy Child Theme
 * 
 * @package MiddletonGetawaysBlocksy
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue parent theme styles
 */
function middleton_getaways_enqueue_styles() {
    wp_enqueue_style('blocksy-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('middleton-getaways-style', get_stylesheet_directory_uri() . '/style.css', array('blocksy-parent-style'));
}
add_action('wp_enqueue_scripts', 'middleton_getaways_enqueue_styles');

/* ==========================================================================
   Custom Functions - Add your customizations below
   ========================================================================== */

/**
 * Enqueue Color Book assets for color book page template
 */
function middleton_getaways_enqueue_color_book_assets() {
    // Only load on color book page template
    if (is_page_template('page-color-book.php')) {
        wp_enqueue_style(
            'mi-color-book-style', 
            get_stylesheet_directory_uri() . '/color-book.css', 
            array('middleton-getaways-style'), 
            '1.0.0'
        );
        
        wp_enqueue_script(
            'mi-color-book-script', 
            get_stylesheet_directory_uri() . '/color-book.js', 
            array(), 
            '1.0.0', 
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'middleton_getaways_enqueue_color_book_assets');
