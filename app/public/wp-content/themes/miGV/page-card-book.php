<?php
/**
 * Template Name: Card Book
 * Description: Visual card component editor for the atomic design system
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Enqueue design book specific assets
wp_enqueue_style('card-book', get_template_directory_uri() . '/assets/css/design-book.css', [], '1.0.0');
wp_enqueue_script('card-book', get_template_directory_uri() . '/assets/js/card-book.js', ['jquery'], '1.0.0', true);

// Localize script for AJAX and component data
wp_localize_script('card-book', 'cardBook', [
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('card_book_nonce'),
    'themeUrl' => get_template_directory_uri(),
    'componentsUrl' => get_template_directory_uri() . '/templates/',
    'uploadUrl' => admin_url('async-upload.php')
]);

// Get Timber context
$context = Timber::context();
$context['post'] = Timber::get_post();

// Get available properties for dynamic data
$properties = Timber::get_posts([
    'post_type' => 'property',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_status' => 'publish'
]);
$context['properties'] = $properties;

// Get available businesses for dynamic data
$businesses = Timber::get_posts([
    'post_type' => 'business',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_status' => 'publish'
]);
$context['businesses'] = $businesses;

// Component types and their properties
$context['component_types'] = [
    'property' => [
        'name' => 'Property Card',
        'icon' => 'home',
        'fields' => ['price', 'bedrooms', 'bathrooms', 'sqft', 'address']
    ],
    'business' => [
        'name' => 'Business Card',
        'icon' => 'store',
        'fields' => ['category', 'rating', 'hours', 'phone', 'website']
    ],
    'default' => [
        'name' => 'Default Card',
        'icon' => 'card',
        'fields' => []
    ]
];

// Card component options
$context['card_options'] = [
    'variants' => [
        'default' => 'Default',
        'horizontal' => 'Horizontal',
        'compact' => 'Compact',
        'featured' => 'Featured'
    ],
    'text_fields' => [
        'pretitle' => ['label' => 'Pre-title', 'type' => 'text', 'placeholder' => 'e.g., NEW LISTING'],
        'title' => ['label' => 'Title', 'type' => 'text', 'placeholder' => 'Card Title'],
        'subtitle' => ['label' => 'Subtitle', 'type' => 'text', 'placeholder' => 'Card Subtitle'],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'placeholder' => 'Card description...']
    ],
    'button_variants' => [
        'primary' => 'Primary',
        'primary-light' => 'Primary Light',
        'primary-dark' => 'Primary Dark',
        'secondary' => 'Secondary',
        'secondary-light' => 'Secondary Light',
        'secondary-dark' => 'Secondary Dark',
        'neutral' => 'Neutral',
        'neutral-light' => 'Neutral Light',
        'neutral-dark' => 'Neutral Dark',
        'base' => 'Base',
        'base-light' => 'Base Light',
        'base-dark' => 'Base Dark'
    ],
    'button_sizes' => [
        'sm' => 'Small',
        'md' => 'Medium',
        'lg' => 'Large'
    ],
    'badge_variants' => [
        'primary' => 'Primary',
        'primary-light' => 'Primary Light',
        'primary-dark' => 'Primary Dark',
        'secondary' => 'Secondary',
        'secondary-light' => 'Secondary Light',
        'secondary-dark' => 'Secondary Dark',
        'neutral' => 'Neutral',
        'neutral-light' => 'Neutral Light',
        'neutral-dark' => 'Neutral Dark',
        'base' => 'Base',
        'base-light' => 'Base Light',
        'base-dark' => 'Base Dark'
    ],
    'corner_styles' => [
        'sharp' => 'Sharp (0px)',
        'rounded' => 'Rounded (8px)',
        'extra-rounded' => 'Extra Rounded (16px)',
        'pill' => 'Pill (24px)'
    ]
];

// Sample images for testing
$context['sample_images'] = [
    get_template_directory_uri() . '/miDocs/SITE DATA/Images/Properties-Featured/villa-feature-1R.png',
    get_template_directory_uri() . '/miDocs/SITE DATA/Images/Properties-Featured/villa-featured-2.png',
    get_template_directory_uri() . '/miDocs/SITE DATA/Images/Properties-Featured/villa-featured-3.png',
    get_template_directory_uri() . '/miDocs/SITE DATA/Images/Properties-Featured/villa-featured-4.png',
    get_template_directory_uri() . '/miDocs/SITE DATA/Images/Businesses-Featured/SurfShop.png',
    get_template_directory_uri() . '/miDocs/SITE DATA/Images/Businesses-Featured/icecream.png'
];

// Render the template
Timber::render('card-book/index.twig', $context);

get_footer();