<?php
/**
 * Middleton Getaways - Blocksy Child Theme Functions
 * 
 * @package MiddletonGetaways
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MI Brand Color System - Automatic Blocksy Sync
 * This syncs your brand colors to Blocksy's customizer automatically
 */

// Define MI brand colors (single source of truth)
function mi_get_brand_colors() {
    return array(
        'mi_primary'        => array('color' => '#5a7b7c', 'name' => 'MI Primary'),
        'mi_primary_light'  => array('color' => '#d6dcd6', 'name' => 'MI Primary Light'),
        'mi_primary_dark'   => array('color' => '#3a5a59', 'name' => 'MI Primary Dark'),
        'mi_secondary'      => array('color' => '#975d55', 'name' => 'MI Secondary'),
        'mi_secondary_light'=> array('color' => '#c38484', 'name' => 'MI Secondary Light'),
        'mi_secondary_dark' => array('color' => '#853d2d', 'name' => 'MI Secondary Dark'),
        'mi_neutral'        => array('color' => '#b5b09f', 'name' => 'MI Neutral'),
        'mi_neutral_light'  => array('color' => '#d1cfc2', 'name' => 'MI Neutral Light'),
        'mi_neutral_dark'   => array('color' => '#8f8c7f', 'name' => 'MI Neutral Dark'),
        'mi_base_lightest'  => array('color' => '#f7f6f3', 'name' => 'MI Base Lightest'),
    );
}

// Sync MI colors to Blocksy customizer automatically
function mi_sync_colors_to_blocksy() {
    $mi_colors = mi_get_brand_colors();
    $color_index = 1;
    
    foreach ($mi_colors as $key => $color_data) {
        if ($color_index <= 10) { // Blocksy supports 10 custom colors
            // Set the color value
            set_theme_mod("palette_color_{$color_index}", $color_data['color']);
            // Set the color name (if Blocksy supports it)
            set_theme_mod("palette_color_{$color_index}_name", $color_data['name']);
            $color_index++;
        }
    }
}

// Hook to sync colors when theme is activated or customizer is saved
add_action('after_setup_theme', 'mi_sync_colors_to_blocksy');
add_action('customize_save_after', 'mi_sync_colors_to_blocksy');

// Add CSS variables for the colors
function mi_add_color_css_variables() {
    $mi_colors = mi_get_brand_colors();
    
    echo '<style id="mi-color-variables">';
    echo ':root {';
    
    // Add MI brand variables
    foreach ($mi_colors as $key => $color_data) {
        $css_var = str_replace('_', '-', $key);
        echo "--{$css_var}: {$color_data['color']};";
    }
    
    // Map to Blocksy system
    $color_index = 1;
    foreach ($mi_colors as $key => $color_data) {
        if ($color_index <= 10) {
            $css_var = str_replace('_', '-', $key);
            echo "--theme-palette-color-{$color_index}: var(--{$css_var});";
            $color_index++;
        }
    }
    
    echo '}';
    echo '</style>';
}
add_action('wp_head', 'mi_add_color_css_variables');
add_action('admin_head', 'mi_add_color_css_variables');

/**
 * Theme setup
 */
function mg_blocksy_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('title-tag');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Add support for wide and full width blocks
    add_theme_support('align-wide');
    
    // Add support for editor color palette
    add_theme_support('editor-color-palette', array(
        array(
            'name'  => __('Primary Green', 'middleton-getaways-blocksy'),
            'slug'  => 'primary',
            'color' => '#2c5530',
        ),
        array(
            'name'  => __('Secondary Green', 'middleton-getaways-blocksy'),
            'slug'  => 'secondary',
            'color' => '#8fbc8f',
        ),
        array(
            'name'  => __('Accent Gold', 'middleton-getaways-blocksy'),
            'slug'  => 'accent',
            'color' => '#d4a574',
        ),
        array(
            'name'  => __('Dark Gray', 'middleton-getaways-blocksy'),
            'slug'  => 'neutral-dark',
            'color' => '#2d3748',
        ),
        array(
            'name'  => __('Light Gray', 'middleton-getaways-blocksy'),
            'slug'  => 'neutral-light',
            'color' => '#f7fafc',
        ),
    ));
}
add_action('after_setup_theme', 'mg_blocksy_setup');

/**
 * Enqueue styles and scripts
 */
function mg_blocksy_enqueue_assets() {
    // Enqueue parent theme styles
    wp_enqueue_style(
        'blocksy-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue child theme styles
    wp_enqueue_style(
        'mg-blocksy-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('blocksy-parent-style'),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue custom JavaScript
    wp_enqueue_script(
        'mg-blocksy-scripts',
        get_stylesheet_directory_uri() . '/assets/js/custom.js',
        array('jquery'),
        wp_get_theme()->get('Version'),
        true
    );
    
    // Localize script for AJAX
    wp_localize_script('mg-blocksy-scripts', 'mgBlocksy', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mg_blocksy_nonce'),
        'themeUrl' => get_stylesheet_directory_uri()
    ));
}
add_action('wp_enqueue_scripts', 'mg_blocksy_enqueue_assets');

/**
 * Add Google Fonts
 */
function mg_blocksy_google_fonts() {
    wp_enqueue_style(
        'mg-google-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap',
        array(),
        null
    );
}
add_action('wp_enqueue_scripts', 'mg_blocksy_google_fonts');

/**
 * Register widget areas
 */
function mg_blocksy_widgets_init() {
    register_sidebar(array(
        'name'          => __('Property Sidebar', 'middleton-getaways-blocksy'),
        'id'            => 'property-sidebar',
        'description'   => __('Sidebar for property pages', 'middleton-getaways-blocksy'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer Column 1', 'middleton-getaways-blocksy'),
        'id'            => 'footer-1',
        'description'   => __('First footer column', 'middleton-getaways-blocksy'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer Column 2', 'middleton-getaways-blocksy'),
        'id'            => 'footer-2',
        'description'   => __('Second footer column', 'middleton-getaways-blocksy'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer Column 3', 'middleton-getaways-blocksy'),
        'id'            => 'footer-3',
        'description'   => __('Third footer column', 'middleton-getaways-blocksy'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'mg_blocksy_widgets_init');

/**
 * Custom post types
 */
function mg_blocksy_register_post_types() {
    // Properties post type
    register_post_type('property', array(
        'labels' => array(
            'name' => __('Properties', 'middleton-getaways-blocksy'),
            'singular_name' => __('Property', 'middleton-getaways-blocksy'),
            'add_new' => __('Add New Property', 'middleton-getaways-blocksy'),
            'add_new_item' => __('Add New Property', 'middleton-getaways-blocksy'),
            'edit_item' => __('Edit Property', 'middleton-getaways-blocksy'),
            'new_item' => __('New Property', 'middleton-getaways-blocksy'),
            'view_item' => __('View Property', 'middleton-getaways-blocksy'),
            'search_items' => __('Search Properties', 'middleton-getaways-blocksy'),
            'not_found' => __('No properties found', 'middleton-getaways-blocksy'),
            'not_found_in_trash' => __('No properties found in trash', 'middleton-getaways-blocksy'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'properties'),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'menu_icon' => 'dashicons-admin-home',
        'show_in_rest' => true,
    ));
    
    // Testimonials post type
    register_post_type('testimonial', array(
        'labels' => array(
            'name' => __('Testimonials', 'middleton-getaways-blocksy'),
            'singular_name' => __('Testimonial', 'middleton-getaways-blocksy'),
            'add_new' => __('Add New Testimonial', 'middleton-getaways-blocksy'),
            'add_new_item' => __('Add New Testimonial', 'middleton-getaways-blocksy'),
            'edit_item' => __('Edit Testimonial', 'middleton-getaways-blocksy'),
            'new_item' => __('New Testimonial', 'middleton-getaways-blocksy'),
            'view_item' => __('View Testimonial', 'middleton-getaways-blocksy'),
            'search_items' => __('Search Testimonials', 'middleton-getaways-blocksy'),
            'not_found' => __('No testimonials found', 'middleton-getaways-blocksy'),
            'not_found_in_trash' => __('No testimonials found in trash', 'middleton-getaways-blocksy'),
        ),
        'public' => true,
        'has_archive' => false,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-format-quote',
        'show_in_rest' => true,
    ));
}
add_action('init', 'mg_blocksy_register_post_types');

/**
 * Register taxonomies
 */
function mg_blocksy_register_taxonomies() {
    // Property categories
    register_taxonomy('property_category', 'property', array(
        'labels' => array(
            'name' => __('Property Categories', 'middleton-getaways-blocksy'),
            'singular_name' => __('Property Category', 'middleton-getaways-blocksy'),
            'search_items' => __('Search Categories', 'middleton-getaways-blocksy'),
            'all_items' => __('All Categories', 'middleton-getaways-blocksy'),
            'edit_item' => __('Edit Category', 'middleton-getaways-blocksy'),
            'update_item' => __('Update Category', 'middleton-getaways-blocksy'),
            'add_new_item' => __('Add New Category', 'middleton-getaways-blocksy'),
            'new_item_name' => __('New Category Name', 'middleton-getaways-blocksy'),
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array('slug' => 'property-category'),
        'show_in_rest' => true,
    ));
    
    // Property locations
    register_taxonomy('property_location', 'property', array(
        'labels' => array(
            'name' => __('Property Locations', 'middleton-getaways-blocksy'),
            'singular_name' => __('Property Location', 'middleton-getaways-blocksy'),
            'search_items' => __('Search Locations', 'middleton-getaways-blocksy'),
            'all_items' => __('All Locations', 'middleton-getaways-blocksy'),
            'edit_item' => __('Edit Location', 'middleton-getaways-blocksy'),
            'update_item' => __('Update Location', 'middleton-getaways-blocksy'),
            'add_new_item' => __('Add New Location', 'middleton-getaways-blocksy'),
            'new_item_name' => __('New Location Name', 'middleton-getaways-blocksy'),
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array('slug' => 'location'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'mg_blocksy_register_taxonomies');

/**
 * Add custom image sizes
 */
function mg_blocksy_image_sizes() {
    add_image_size('property-card', 400, 300, true);
    add_image_size('property-hero', 1200, 600, true);
    add_image_size('testimonial-avatar', 80, 80, true);
}
add_action('after_setup_theme', 'mg_blocksy_image_sizes');

/**
 * Customize excerpt length
 */
function mg_blocksy_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'mg_blocksy_excerpt_length');

/**
 * Customize excerpt more text
 */
function mg_blocksy_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'mg_blocksy_excerpt_more');

/**
 * Add custom body classes
 */
function mg_blocksy_body_classes($classes) {
    if (is_singular('property')) {
        $classes[] = 'single-property';
    }
    
    if (is_post_type_archive('property')) {
        $classes[] = 'property-archive';
    }
    
    return $classes;
}
add_filter('body_class', 'mg_blocksy_body_classes');

/**
 * Enqueue admin styles
 */
function mg_blocksy_admin_styles() {
    wp_enqueue_style(
        'mg-blocksy-admin',
        get_stylesheet_directory_uri() . '/assets/css/admin.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('admin_enqueue_scripts', 'mg_blocksy_admin_styles');

/**
 * Add theme customizer options
 */
function mg_blocksy_customize_register($wp_customize) {
    // Add section for theme options
    $wp_customize->add_section('mg_blocksy_options', array(
        'title' => __('Middleton Getaways Options', 'middleton-getaways-blocksy'),
        'priority' => 30,
    ));
    
    // Contact information
    $wp_customize->add_setting('mg_contact_phone', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('mg_contact_phone', array(
        'label' => __('Contact Phone', 'middleton-getaways-blocksy'),
        'section' => 'mg_blocksy_options',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('mg_contact_email', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_email',
    ));
    
    $wp_customize->add_control('mg_contact_email', array(
        'label' => __('Contact Email', 'middleton-getaways-blocksy'),
        'section' => 'mg_blocksy_options',
        'type' => 'email',
    ));
    
    // Social media links
    $wp_customize->add_setting('mg_facebook_url', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    $wp_customize->add_control('mg_facebook_url', array(
        'label' => __('Facebook URL', 'middleton-getaways-blocksy'),
        'section' => 'mg_blocksy_options',
        'type' => 'url',
    ));
    
    $wp_customize->add_setting('mg_instagram_url', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    $wp_customize->add_control('mg_instagram_url', array(
        'label' => __('Instagram URL', 'middleton-getaways-blocksy'),
        'section' => 'mg_blocksy_options',
        'type' => 'url',
    ));
}
add_action('customize_register', 'mg_blocksy_customize_register');

/**
 * Helper function to get theme option
 */
function mg_get_theme_option($option_name, $default = '') {
    return get_theme_mod($option_name, $default);
}

/**
 * Add shortcode for property grid
 */
function mg_property_grid_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6,
        'category' => '',
        'location' => '',
    ), $atts);
    
    $args = array(
        'post_type' => 'property',
        'posts_per_page' => intval($atts['limit']),
        'post_status' => 'publish',
    );
    
    if (!empty($atts['category'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'property_category',
            'field' => 'slug',
            'terms' => $atts['category'],
        );
    }
    
    if (!empty($atts['location'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'property_location',
            'field' => 'slug',
            'terms' => $atts['location'],
        );
    }
    
    $properties = new WP_Query($args);
    
    if (!$properties->have_posts()) {
        return '<p>' . __('No properties found.', 'middleton-getaways-blocksy') . '</p>';
    }
    
    ob_start();
    ?>
    <div class="property-grid">
        <?php while ($properties->have_posts()) : $properties->the_post(); ?>
            <div class="property-card">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="property-image">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('property-card'); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="property-content">
                    <h3 class="property-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    <div class="property-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                    <a href="<?php the_permalink(); ?>" class="property-link">
                        <?php _e('View Details', 'middleton-getaways-blocksy'); ?>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('property_grid', 'mg_property_grid_shortcode');
