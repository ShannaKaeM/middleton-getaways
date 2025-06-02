<?php
/**
 * miGV Theme Functions (Updated with Timber & CMB2)
 * 
 * @package miGV
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load Composer autoloader
$autoloader_path = dirname(__FILE__) . '/../../../../../vendor/autoload.php';
if (file_exists($autoloader_path)) {
    require_once $autoloader_path;
} else {
    // Add admin notice if autoloader is missing
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>Composer autoloader not found. Please run "composer install" in the project root.</p></div>';
    });
}

// Initialize Timber
if (class_exists('Timber\Timber')) {
    Timber\Timber::init();
    
    // Set Timber directories
    Timber\Timber::$dirname = array('templates', 'views');
    
    // Add Timber context filters
    add_filter('timber/context', 'migv_add_to_context');
    add_filter('timber/twig', 'migv_add_to_twig');
} else {
    // Add admin notice if Timber is not available
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>Timber not found. Please install Timber via Composer.</p></div>';
    });
}

/**
 * Theme setup
 */
function migv_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    
    // Add theme support for responsive embeds
    add_theme_support('responsive-embeds');
    
    // Add theme support for editor styles
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');
    
    // Add theme support for wide and full alignment
    add_theme_support('align-wide');
    
    // Add theme support for block editor color palette
    add_theme_support('editor-color-palette');
    
    // Add theme support for custom line height
    add_theme_support('custom-line-height');
    
    // Add theme support for custom spacing
    add_theme_support('custom-spacing');
    
    // Add theme support for custom units
    add_theme_support('custom-units');
    
    // Add theme support for theme.json
    add_theme_support('wp-block-styles');
    add_theme_support('appearance-tools');
    
    // Add theme support for post formats
    add_theme_support('post-formats', array(
        'aside',
        'gallery',
        'link',
        'image',
        'quote',
        'status',
        'video',
        'audio',
        'chat',
    ));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'migv'),
        'footer'  => __('Footer Menu', 'migv'),
    ));
    
    // Set content width
    if (!isset($content_width)) {
        $content_width = 1280;
    }
}
add_action('after_setup_theme', 'migv_setup');

/**
 * Enqueue scripts and styles
 */
function migv_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('migv-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Enqueue block styles
    wp_enqueue_style('migv-blocks', get_template_directory_uri() . '/assets/css/blocks.css', array('migv-style'), '1.0.0');
    
    // Enqueue main JavaScript
    wp_enqueue_script('migv-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true);
    
    // Enqueue comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    // Localize script for AJAX
    wp_localize_script('migv-main', 'migv_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('migv_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'migv_scripts');

/**
 * Enqueue admin scripts and styles
 */
function migv_admin_scripts() {
    wp_enqueue_style('migv-admin', get_template_directory_uri() . '/assets/css/admin.css', array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'migv_admin_scripts');

/**
 * Register widget areas
 */
function migv_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'migv'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'migv'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer', 'migv'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here to appear in your footer.', 'migv'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'migv_widgets_init');

/**
 * Add to Timber context
 */
function migv_add_to_context($context) {
    // Add menus to context
    $context['menu'] = Timber\Timber::get_menu('primary');
    $context['footer_menu'] = Timber\Timber::get_menu('footer');
    
    // Add site info
    $context['site'] = new Timber\Site();
    $context['site']->language_attributes = function() {
        return get_language_attributes();
    };
    
    // Add WordPress functions as callable functions
    $context['wp_head'] = function() {
        ob_start();
        wp_head();
        return ob_get_clean();
    };
    
    $context['wp_body_open'] = function() {
        ob_start();
        wp_body_open();
        return ob_get_clean();
    };
    
    $context['wp_footer'] = function() {
        ob_start();
        wp_footer();
        return ob_get_clean();
    };
    
    $context['body_class'] = function() {
        return implode(' ', get_body_class());
    };
    
    // Add translation function
    $context['__'] = function($text, $domain = 'migv') {
        return __($text, $domain);
    };
    
    // Add sidebar
    $context['sidebar'] = Timber\Timber::get_widgets('sidebar-1');
    $context['footer_widgets'] = Timber\Timber::get_widgets('footer-1');
    
    return $context;
}

/**
 * Add to Twig
 */
function migv_add_to_twig($twig) {
    // Add WordPress functions to Twig for base.twig compatibility
    $twig->addFunction(new Twig\TwigFunction('wp_head', 'wp_head'));
    $twig->addFunction(new Twig\TwigFunction('wp_body_open', 'wp_body_open'));
    $twig->addFunction(new Twig\TwigFunction('wp_footer', 'wp_footer'));
    $twig->addFunction(new Twig\TwigFunction('body_class', 'body_class'));
    $twig->addFunction(new Twig\TwigFunction('language_attributes', 'language_attributes'));
    
    return $twig;
}

// Custom post types are now registered in mu-plugins/villa-cpt-registration.php

/**
 * Register custom taxonomies
 */
function migv_register_taxonomies() {
    // Property categories
    register_taxonomy('property_category', 'property', array(
        'labels' => array(
            'name'              => __('Property Categories', 'migv'),
            'singular_name'     => __('Property Category', 'migv'),
            'search_items'      => __('Search Property Categories', 'migv'),
            'all_items'         => __('All Property Categories', 'migv'),
            'edit_item'         => __('Edit Property Category', 'migv'),
            'update_item'       => __('Update Property Category', 'migv'),
            'add_new_item'      => __('Add New Property Category', 'migv'),
            'new_item_name'     => __('New Property Category Name', 'migv'),
            'menu_name'         => __('Categories', 'migv'),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'property-category'),
        'show_in_rest'      => true,
    ));
    
    // Property locations
    register_taxonomy('property_location', 'property', array(
        'labels' => array(
            'name'              => __('Property Locations', 'migv'),
            'singular_name'     => __('Property Location', 'migv'),
            'search_items'      => __('Search Property Locations', 'migv'),
            'all_items'         => __('All Property Locations', 'migv'),
            'edit_item'         => __('Edit Property Location', 'migv'),
            'update_item'       => __('Update Property Location', 'migv'),
            'add_new_item'      => __('Add New Property Location', 'migv'),
            'new_item_name'     => __('New Property Location Name', 'migv'),
            'menu_name'         => __('Locations', 'migv'),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'location'),
        'show_in_rest'      => true,
    ));
}
add_action('init', 'migv_register_taxonomies');

/**
 * Enqueue Customizer scripts
 */
function migv_customize_preview_js() {
    wp_enqueue_script('migv-customizer', get_template_directory_uri() . '/assets/js/customizer.js', array('customize-preview'), '1.0.0', true);
}
add_action('customize_preview_init', 'migv_customize_preview_js');

/**
 * Register custom block category
 */
function migv_register_block_category($categories) {
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'migv-blocks',
                'title' => __('miGV Blocks', 'migv'),
                'icon'  => 'admin-home',
            ),
        )
    );
}
add_filter('block_categories_all', 'migv_register_block_category');

/**
 * Custom page templates for membership pages
 */
function migv_custom_page_templates($template) {
    if (is_page()) {
        global $post;
        $page_slug = $post->post_name;
        
        // Check for custom Twig templates
        $custom_templates = array(
            'login' => 'page-login.twig',
            'register' => 'page-register.twig', 
            'members' => 'page-members.twig',
            'user' => 'page-profile.twig'
        );
        
        if (isset($custom_templates[$page_slug])) {
            $context = Timber::context();
            $context['post'] = new Timber\Post();
            
            Timber::render($custom_templates[$page_slug], $context);
            exit;
        }
    }
    
    return $template;
}
add_filter('template_include', 'migv_custom_page_templates');

/**
 * Include required files
 */
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/customizer.php';
// Design book router removed - functionality handled elsewhere

/**
 * Timber Site class extension
 */
class MiGVSite extends Timber\Site {
    public function __construct() {
        add_action('after_setup_theme', array($this, 'theme_supports'));
        add_filter('timber/context', array($this, 'add_to_context'));
        add_filter('timber/twig', array($this, 'add_to_twig'));
        parent::__construct();
    }

    public function theme_supports() {
        // Additional theme supports can be added here
    }

    public function add_to_context($context) {
        $context['foo'] = 'bar';
        $context['stuff'] = 'I am a value set in your functions.php file';
        $context['notes'] = 'These values are available everytime you call Timber::context();';
        return $context;
    }

    public function add_to_twig($twig) {
        /* this is where you can add your own functions to twig */
        $twig->addExtension(new Twig\Extension\StringLoaderExtension());
        return $twig;
    }
}

new MiGVSite();

// Design book AJAX handlers removed - functionality can be rebuilt as needed

/**
 * AJAX handler for Mi Design Book - fetch post data
 */
function mi_get_post_data_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_die('Security check failed');
    }
    
    $post_id = intval($_POST['post_id']);
    $post = Timber::get_post($post_id);
    
    if (!$post) {
        wp_send_json_error('Post not found');
    }
    
    // Prepare response data based on post type
    $response = [
        'title' => $post->title(),
        'description' => $post->excerpt() ?: wp_trim_words($post->content(), 30),
        'image' => $post->thumbnail() ? $post->thumbnail()->src('large') : '',
        'link' => $post->link(),
        'post_type' => $post->post_type
    ];
    
    // Add custom fields based on post type
    if ($post->post_type === 'property') {
        $response['fields'] = [
            'price' => get_post_meta($post_id, 'property_price', true),
            'bedrooms' => get_post_meta($post_id, 'property_bedrooms', true),
            'bathrooms' => get_post_meta($post_id, 'property_bathrooms', true),
            'sqft' => get_post_meta($post_id, 'property_sqft', true),
            'status' => get_post_meta($post_id, 'property_status', true),
            'type' => get_post_meta($post_id, 'property_type', true),
            'address' => get_post_meta($post_id, 'property_address', true),
            'city' => get_post_meta($post_id, 'property_city', true),
            'state' => get_post_meta($post_id, 'property_state', true),
            'features' => get_post_meta($post_id, 'property_features', true),
            'year_built' => get_post_meta($post_id, 'property_year_built', true)
        ];
    } elseif ($post->post_type === 'business') {
        $response['fields'] = [
            'type' => get_post_meta($post_id, 'business_type', true),
            'phone' => get_post_meta($post_id, 'business_phone', true),
            'email' => get_post_meta($post_id, 'business_email', true),
            'website' => get_post_meta($post_id, 'business_website', true),
            'address' => get_post_meta($post_id, 'business_address', true),
            'hours' => get_post_meta($post_id, 'business_hours', true)
        ];
    }
    
    wp_send_json_success($response);
}
add_action('wp_ajax_mi_get_post_data', 'mi_get_post_data_handler');
add_action('wp_ajax_nopriv_mi_get_post_data', 'mi_get_post_data_handler');

/**
 * AJAX handler for saving card type configurations
 */
function mi_save_card_type_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $type_name = sanitize_key($_POST['type_name']);
    $configuration = json_decode(stripslashes($_POST['configuration']), true);
    
    if (empty($type_name) || empty($configuration)) {
        wp_send_json_error('Invalid data');
    }
    
    // Get existing card types
    $card_types = get_option('mi_card_types', []);
    
    // Add or update the card type
    $card_types[$type_name] = [
        'name' => sanitize_text_field($_POST['display_name']),
        'description' => sanitize_text_field($_POST['description']),
        'configuration' => $configuration,
        'created' => current_time('mysql'),
        'author' => get_current_user_id()
    ];
    
    // Save to database
    update_option('mi_card_types', $card_types);
    
    wp_send_json_success([
        'message' => 'Card type saved successfully',
        'type_name' => $type_name
    ]);
}
add_action('wp_ajax_mi_save_card_type', 'mi_save_card_type_handler');

/**
 * AJAX handler for loading card types
 */
function mi_get_card_types_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_die('Security check failed');
    }
    
    $card_types = get_option('mi_card_types', []);
    
    // Add built-in types
    $built_in_types = [
        'card-property' => [
            'name' => 'Property Card',
            'description' => 'Standard property listing card',
            'configuration' => [
                'variant' => 'default',
                'badges' => [
                    ['field' => 'property_status', 'variant' => 'primary'],
                    ['field' => 'property_type', 'variant' => 'primary-light']
                ],
                'meta' => [
                    ['field' => 'property_bedrooms', 'icon' => 'bed', 'suffix' => ' Beds'],
                    ['field' => 'property_bathrooms', 'icon' => 'bath', 'suffix' => ' Baths'],
                    ['field' => 'property_sqft', 'icon' => 'home', 'suffix' => ' sqft']
                ],
                'pretitle_field' => 'property_price',
                'subtitle_fields' => ['property_address', 'property_city']
            ]
        ],
        'card-business' => [
            'name' => 'Business Card',
            'description' => 'Standard business listing card',
            'configuration' => [
                'variant' => 'default',
                'badges' => [
                    ['field' => 'business_type', 'variant' => 'primary']
                ],
                'meta' => [
                    ['field' => 'business_phone', 'icon' => 'phone'],
                    ['field' => 'business_hours', 'icon' => 'clock']
                ],
                'subtitle_field' => 'business_address'
            ]
        ]
    ];
    
    wp_send_json_success([
        'built_in' => $built_in_types,
        'custom' => $card_types
    ]);
}
add_action('wp_ajax_mi_get_card_types', 'mi_get_card_types_handler');

/**
 * AJAX handler for deleting card types
 */
function mi_delete_card_type_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $type_name = sanitize_key($_POST['type_name']);
    
    // Get existing card types
    $card_types = get_option('mi_card_types', []);
    
    // Remove the card type
    if (isset($card_types[$type_name])) {
        unset($card_types[$type_name]);
        update_option('mi_card_types', $card_types);
        wp_send_json_success('Card type deleted');
    } else {
        wp_send_json_error('Card type not found');
    }
}
add_action('wp_ajax_mi_delete_card_type', 'mi_delete_card_type_handler');
