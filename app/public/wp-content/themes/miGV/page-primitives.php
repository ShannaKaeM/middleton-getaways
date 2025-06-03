<?php
/**
 * Template Name: Primitives Dashboard
 * Description: Dashboard for all primitive editors
 */

// Enqueue styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('primitive-editor', get_template_directory_uri() . '/assets/css/primitive-editor.css', array(), '1.0.0');
});

// Define available primitive editors
$primitive_editors = [
    [
        'title' => 'Colors',
        'description' => 'Edit color palettes and create color variations',
        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2v20M2 12h20"/></svg>',
        'url' => home_url('/primitive-colors/'),
        'color' => '#3b82f6'
    ],
    [
        'title' => 'Typography',
        'description' => 'Manage font sizes, families, and typographic scales',
        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>',
        'url' => home_url('/primitive-typography/'),
        'color' => '#8b5cf6'
    ],
    [
        'title' => 'Spacing',
        'description' => 'Define spacing scales and rhythm for consistent layouts',
        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>',
        'url' => home_url('/primitive-spacing/'),
        'color' => '#10b981'
    ]
];

// Set up Timber context
$context = Timber::context();
$context['primitive_editors'] = $primitive_editors;

// Render the template
Timber::render('primitives/dashboard.twig', $context);
