<?php
/**
 * Preview Template for Umbral Editor
 * Used when ?umbral=preview is present
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get query parameters for template control
$show_header = !isset($_GET['header']) || $_GET['header'] !== 'false';
$show_footer = !isset($_GET['footer']) || $_GET['footer'] !== 'false';

// Get source_id parameter to determine which page's components to load
$source_id = isset($_GET['source_id']) ? intval($_GET['source_id']) : 0;
$mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'single';
$post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
$core_page = isset($_GET['core_page']) ? sanitize_text_field($_GET['core_page']) : '';

// Debug logging
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Umbral Preview: source_id=' . $source_id . ', mode=' . $mode . ', post_type=' . $post_type . ', core_page=' . $core_page);
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
    
    <style>
        html {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        /* Preview mode specific styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        #wpadminbar {
            display: none !important;
        }
        
        /* Ensure responsive behavior in iframe */
        html {
            overflow-x: hidden;
        }
    </style>
</head>

<body <?php body_class('umbral-preview-mode'); ?>>

<?php if ($show_header): ?>
    <?php get_header(); ?>
<?php endif; ?>

<main id="main" class="site-main umbral-preview-main">
    <?php
    /**
     * Hook: um_above_content
     * 
     * @hooked - content before main components
     */
    do_action('um_above_content');
    ?>
    
    <?php
    /**
     * Hook: um_the_content
     * 
     * @hooked - page content, components, etc.
     */
    $content = get_post($source_id);

    echo do_blocks($content->post_content);
    ?>
    
    <?php
    /**
     * Hook: um_below_content
     * 
     * @hooked - content after main components
     */
    do_action('um_below_content');
    ?>
</main>

<?php if ($show_footer): ?>
    <?php get_footer(); ?>
<?php endif; ?>

<?php wp_footer(); ?>

<?php

?>

<script>
// Disable any scrolling animations or fancy effects in preview
document.addEventListener('DOMContentLoaded', function() {
    // Remove any scroll event listeners that might cause issues
    window.onscroll = null;
    
    // Disable smooth scrolling
    document.documentElement.style.scrollBehavior = 'auto';
    
    // Notify parent frame that preview is loaded (for iframe communication)
    if (window.parent && window.parent !== window) {
        window.parent.postMessage({
            type: 'umbral_preview_loaded',
            url: window.location.href
        }, '*');
    }
});
</script>

</body>
</html>