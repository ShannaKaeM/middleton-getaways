<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Component compiler utilities are loaded globally in the main plugin file

/**
 * Hero-1 Component Renderer
 * 
 * @param array $context Timber context passed from block
 * @param array $component_data Component field data
 * @param array $breakpoints Available breakpoints for responsive styles
 * 
 * @return string Rendered component HTML
 */

// Get component directory for file paths
$component_dir = dirname(__FILE__);
$component_name = basename($component_dir);
$category_name = basename(dirname($component_dir));

// Prepare component context
$component_context = [
    'title' => $component_data['title'] ?? 'Welcome to Our Amazing Site',
    'subtitle' => $component_data['subtitle'] ?? 'Discover the possibilities and transform your experience with our innovative solutions.',
    'background_image' => $component_data['background_image'] ?? null,
    'button_text' => $component_data['button_text'] ?? 'Get Started',
    'button_url' => $component_data['button_url'] ?? '#',
    'text_color' => $component_data['text_color'] ?? 'white',
    'overlay_opacity' => $component_data['overlay_opacity'] ?? '0.5',
    'height' => $component_data['height'] ?? 'large',
    'component_id' => $category_name . '-' . $component_name
];

// Compile and auto-enqueue component assets (styles and scripts)
compileComponent($component_dir, $component_context);

// Merge with main context
$merged_context = array_merge($context, $component_context);

// Render component using Timber
echo Timber::compile('@components/Heroes/hero-1/view.twig', $merged_context);