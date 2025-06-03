<?php
/**
 * Template Name: Primitive - Colors
 * Description: Visual editor for color design tokens
 */

// Enqueue necessary scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('primitive-editor', get_template_directory_uri() . '/assets/css/primitive-editor.css', array(), '1.0.1');
    wp_enqueue_script('primitive-colors', get_template_directory_uri() . '/assets/js/primitive-colors.js', array('jquery'), '1.0.1', true);
    
    // Localize script for AJAX
    wp_localize_script('primitive-colors', 'primitiveColors', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mi_design_book_nonce'),
        'canEdit' => current_user_can('edit_theme_options')
    ));
});

// Get colors directly from theme.json file
$colors = [];
$theme_json_file = get_template_directory() . '/theme.json';

if (file_exists($theme_json_file)) {
    $theme_json_content = json_decode(file_get_contents($theme_json_file), true);
    if (isset($theme_json_content['settings']['color']['palette'])) {
        $colors = $theme_json_content['settings']['color']['palette'];
    }
}

// If no colors found, show error
if (empty($colors)) {
    wp_die('No colors found in theme.json. Please check your theme.json file structure.');
}

// Create a lookup array for easy access
$color_lookup = [];
foreach ($colors as $color) {
    if (isset($color['slug'])) {
        $color_lookup[$color['slug']] = $color;
    }
}

// Define the color groups structure based on your requirements
$color_groups = [
    'primary' => [
        'title' => 'Primary Colors',
        'colors' => []
    ],
    'secondary' => [
        'title' => 'Secondary Colors',
        'colors' => []
    ],
    'neutral' => [
        'title' => 'Neutral Colors',
        'colors' => []
    ],
    'base' => [
        'title' => 'Base Colors',
        'colors' => []
    ],
    'extreme' => [
        'title' => 'Extreme Colors',
        'colors' => []
    ],
    'other' => [
        'title' => 'Other Colors',
        'colors' => []
    ]
];

// Populate the groups with colors from theme.json
// Primary group
if (isset($color_lookup['primary-light'])) $color_groups['primary']['colors'][] = $color_lookup['primary-light'];
if (isset($color_lookup['primary'])) $color_groups['primary']['colors'][] = $color_lookup['primary'];
if (isset($color_lookup['primary-dark'])) $color_groups['primary']['colors'][] = $color_lookup['primary-dark'];

// Secondary group
if (isset($color_lookup['secondary-light'])) $color_groups['secondary']['colors'][] = $color_lookup['secondary-light'];
if (isset($color_lookup['secondary'])) $color_groups['secondary']['colors'][] = $color_lookup['secondary'];
if (isset($color_lookup['secondary-dark'])) $color_groups['secondary']['colors'][] = $color_lookup['secondary-dark'];

// Neutral group
if (isset($color_lookup['neutral-light'])) $color_groups['neutral']['colors'][] = $color_lookup['neutral-light'];
if (isset($color_lookup['neutral'])) $color_groups['neutral']['colors'][] = $color_lookup['neutral'];
if (isset($color_lookup['neutral-dark'])) $color_groups['neutral']['colors'][] = $color_lookup['neutral-dark'];

// Base group (5 shades)
if (isset($color_lookup['base-lightest'])) $color_groups['base']['colors'][] = $color_lookup['base-lightest'];
if (isset($color_lookup['base-light'])) $color_groups['base']['colors'][] = $color_lookup['base-light'];
if (isset($color_lookup['base'])) $color_groups['base']['colors'][] = $color_lookup['base'];
if (isset($color_lookup['base-dark'])) $color_groups['base']['colors'][] = $color_lookup['base-dark'];
if (isset($color_lookup['base-darkest'])) $color_groups['base']['colors'][] = $color_lookup['base-darkest'];

// Extreme group
if (isset($color_lookup['extreme-light'])) $color_groups['extreme']['colors'][] = $color_lookup['extreme-light'];
if (isset($color_lookup['extreme-dark'])) $color_groups['extreme']['colors'][] = $color_lookup['extreme-dark'];

// Other colors (if any remain after cleanup)
$assigned_slugs = ['primary-light', 'primary', 'primary-dark', 'secondary-light', 'secondary', 'secondary-dark', 
                   'neutral-light', 'neutral', 'neutral-dark', 'base-lightest', 'base-light', 'base', 'base-dark', 
                   'base-darkest', 'extreme-light', 'extreme-dark'];

foreach ($colors as $color) {
    if (isset($color['slug']) && !in_array($color['slug'], $assigned_slugs)) {
        $color_groups['other']['colors'][] = $color;
    }
}

// Remove empty groups
$color_groups = array_filter($color_groups, function($group) {
    return !empty($group['colors']);
});

// Set up Timber context
$context = Timber::context();
$context['color_groups'] = $color_groups;
$context['can_edit'] = current_user_can('edit_theme_options');

// Debug output
$context['debug_colors'] = $colors;
$context['debug_color_count'] = count($colors);

// Render the template
Timber::render('primitives/colors-editor.twig', $context);
