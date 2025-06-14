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
    // Enqueue Google Fonts
    wp_enqueue_style('migv-google-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap', array(), null);
    
    // Enqueue main stylesheet (cleaned to only have WordPress required styles)
    wp_enqueue_style('migv-style', get_stylesheet_uri(), array('migv-google-fonts'), '1.0.0');
    
    // Enqueue design system styles
    wp_enqueue_style('migv-design-book', get_template_directory_uri() . '/assets/css/design-book.css', array('migv-style'), '1.0.0');
    
    // Note: blocks.css removed - all block styles should come from theme.json
    // wp_enqueue_style('migv-blocks', get_template_directory_uri() . '/assets/css/blocks.css', array('migv-design-book'), '1.0.0');
    
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
 * Outputs the dynamically generated primitive CSS variables into the site's <head>.
 * This makes the CSS custom properties available globally for theme components
 * and Design Book Editor previews.
 */
function migv_output_primitive_css_variables_in_head() {
    // Ensure our core function is available
    if ( ! function_exists( 'migv_generate_primitive_css_variables' ) ) {
        // This might happen if design-system-core.php wasn't included correctly.
        // Optionally log an error here.
        // error_log('Error: migv_generate_primitive_css_variables function not found.');
        return;
    }

    $generated_css = migv_generate_primitive_css_variables();
    if ( ! empty( $generated_css ) ) {
        echo "<style id='migv-primitive-variables'>\n" . $generated_css . "</style>\n";
    }
}
add_action( 'wp_head', 'migv_output_primitive_css_variables_in_head' );

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
    
    // Add function to get theme.json data directly in Twig templates
    $twig->addFunction(new Twig\TwigFunction('get_theme_json', function($path = null) {
        $theme_json = wp_get_global_settings();
        
        if ($path === null) {
            return $theme_json;
        }
        
        // Allow dot notation to access nested values
        $keys = explode('.', $path);
        $value = $theme_json;
        
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }
        
        return $value;
    }));
    
    // Add function to get theme mods
    $twig->addFunction(new Twig\TwigFunction('get_theme_mod', 'get_theme_mod'));
    
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
 * Add custom Timber functions
 */
add_filter('timber/twig', function($twig) {
    // Add function to load primitive JSON files
    $twig->addFunction(new \Twig\TwigFunction('load_primitive', function($name) {
        $json_path = get_template_directory() . "/primitives/{$name}.json";
        
        if (!file_exists($json_path)) {
            return null;
        }
        
        $json_content = file_get_contents($json_path);
        return json_decode($json_content, true);
    }));
    
    return $twig;
});

/**
 * Include required files
 */
require get_template_directory() . '/inc/template-functions.php';
// Include Design System Core for CSS variable generation
require_once get_template_directory() . '/inc/design-system-core.php';

require get_template_directory() . '/inc/customizer.php';
// Design book router removed - functionality handled elsewhere

/**
 * AJAX handler for saving color palette
 */
add_action('wp_ajax_save_color_palette', 'migv_save_color_palette');
function migv_save_color_palette() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'color_book_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_die('You do not have permission to edit theme options');
    }
    
    // Get the colors from POST
    $new_colors = isset($_POST['colors']) ? $_POST['colors'] : array();
    
    if (empty($new_colors)) {
        wp_send_json_error('No colors provided');
    }
    
    // Read current theme.json
    $theme_json_path = get_template_directory() . '/theme.json';
    $theme_json = json_decode(file_get_contents($theme_json_path), true);
    
    if (!$theme_json) {
        wp_send_json_error('Could not read theme.json');
    }
    
    // Update color palette
    if (isset($theme_json['settings']['color']['palette'])) {
        foreach ($theme_json['settings']['color']['palette'] as &$color) {
            if (isset($new_colors[$color['slug']])) {
                $color['color'] = sanitize_hex_color($new_colors[$color['slug']]);
            }
        }
    }
    
    // Write back to theme.json
    $json_content = json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    if (file_put_contents($theme_json_path, $json_content)) {
        wp_send_json_success('Colors saved successfully');
    } else {
        wp_send_json_error('Could not write to theme.json');
    }
}

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

/**
 * AJAX handler for syncing primitive values back to theme.json
 */
function mi_sync_primitive_to_theme_json() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $token_type = sanitize_text_field($_POST['token_type']); // e.g., 'color', 'typography', 'spacing'
    $token_path = sanitize_text_field($_POST['token_path']); // e.g., 'primary', 'heading-1'
    $token_value = sanitize_text_field($_POST['token_value']); // The new value
    
    // Get the theme.json file path
    $theme_json_path = get_template_directory() . '/theme.json';
    
    if (!file_exists($theme_json_path)) {
        wp_send_json_error('theme.json not found');
    }
    
    // Read and parse theme.json
    $theme_json_content = file_get_contents($theme_json_path);
    $theme_json = json_decode($theme_json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Invalid theme.json format');
    }
    
    // Update the appropriate token based on type
    $updated = false;
    
    switch ($token_type) {
        case 'color':
            // Update color palette
            if (isset($theme_json['settings']['color']['palette'])) {
                foreach ($theme_json['settings']['color']['palette'] as &$color) {
                    if ($color['slug'] === $token_path) {
                        $color['color'] = $token_value;
                        $updated = true;
                        break;
                    }
                }
            }
            break;
            
        case 'typography':
            // Update font sizes
            if (isset($theme_json['settings']['typography']['fontSizes'])) {
                foreach ($theme_json['settings']['typography']['fontSizes'] as &$size) {
                    if ($size['slug'] === $token_path) {
                        $size['size'] = $token_value;
                        $updated = true;
                        break;
                    }
                }
            }
            break;
            
        case 'spacing':
            // Update spacing scale
            if (isset($theme_json['settings']['spacing']['spacingSizes'])) {
                foreach ($theme_json['settings']['spacing']['spacingSizes'] as &$spacing) {
                    if ($spacing['slug'] === $token_path) {
                        $spacing['size'] = $token_value;
                        $updated = true;
                        break;
                    }
                }
            }
            break;
            
        case 'custom':
            // Update custom properties
            if (!isset($theme_json['custom'])) {
                $theme_json['custom'] = [];
            }
            
            // Parse the path (e.g., 'layout.contentSize' -> ['layout', 'contentSize'])
            $path_parts = explode('.', $token_path);
            $current = &$theme_json['custom'];
            
            for ($i = 0; $i < count($path_parts) - 1; $i++) {
                if (!isset($current[$path_parts[$i]])) {
                    $current[$path_parts[$i]] = [];
                }
                $current = &$current[$path_parts[$i]];
            }
            
            $current[$path_parts[count($path_parts) - 1]] = $token_value;
            $updated = true;
            break;
    }
    
    if ($updated) {
        // Write the updated theme.json back to file
        $json_output = json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (file_put_contents($theme_json_path, $json_output) !== false) {
            // Clear any caches
            wp_cache_flush();
            
            // Regenerate CSS variables
            do_action('wp_theme_json_data_theme', $theme_json);
            
            wp_send_json_success([
                'message' => 'Token updated successfully',
                'token_type' => $token_type,
                'token_path' => $token_path,
                'token_value' => $token_value
            ]);
        } else {
            wp_send_json_error('Could not write to theme.json');
        }
    } else {
        wp_send_json_error('Token not found');
    }
}
add_action('wp_ajax_mi_sync_primitive_to_theme_json', 'mi_sync_primitive_to_theme_json');

/**
 * AJAX handler to get all theme.json tokens for the design book
 */
function mi_get_theme_json_tokens() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $theme_json = wp_get_global_settings();
    $tokens = [];
    
    // Extract color tokens
    if (isset($theme_json['color']['palette'])) {
        foreach ($theme_json['color']['palette'] as $color) {
            $tokens['colors'][] = [
                'slug' => $color['slug'],
                'name' => $color['name'],
                'value' => $color['color'],
                'variable' => '--wp--preset--color--' . $color['slug']
            ];
        }
    }
    
    // Extract typography tokens
    if (isset($theme_json['typography']['fontSizes'])) {
        foreach ($theme_json['typography']['fontSizes'] as $size) {
            $tokens['typography'][] = [
                'slug' => $size['slug'],
                'name' => $size['name'] ?? ucfirst(str_replace('-', ' ', $size['slug'])),
                'value' => $size['size'],
                'variable' => '--wp--preset--font-size--' . $size['slug']
            ];
        }
    }
    
    // Extract spacing tokens
    if (isset($theme_json['spacing']['spacingSizes'])) {
        foreach ($theme_json['spacing']['spacingSizes'] as $spacing) {
            $tokens['spacing'][] = [
                'slug' => $spacing['slug'],
                'name' => $spacing['name'] ?? 'Size ' . $spacing['slug'],
                'value' => $spacing['size'],
                'variable' => '--wp--preset--spacing--' . $spacing['slug']
            ];
        }
    }
    
    // Extract custom tokens
    if (isset($theme_json['custom'])) {
        $tokens['custom'] = $theme_json['custom'];
    }
    
    wp_send_json_success($tokens);
}
add_action('wp_ajax_mi_get_theme_json_tokens', 'mi_get_theme_json_tokens');

/**
 * Add theme.json sync capabilities to the design book
 */
function mi_enqueue_design_book_sync_scripts() {
    if (is_page_template('page-card-book.php')) {
        wp_enqueue_script(
            'mi-design-book-sync',
            get_template_directory_uri() . '/assets/js/design-book-sync.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('mi-design-book-sync', 'miDesignBookSync', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mi_design_book_nonce'),
            'canEdit' => current_user_can('edit_theme_options')
        ));
    }
}
add_action('wp_enqueue_scripts', 'mi_enqueue_design_book_sync_scripts');

/**
 * AJAX handler to get post data for card book
 */
function card_get_post_data() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'card_book_nonce')) {
        wp_die('Security check failed');
    }
    
    $post_id = intval($_POST['post_id']);
    $post = get_post($post_id);
    
    if (!$post) {
        wp_send_json_error('Post not found');
    }
    
    // Get post data
    $data = array(
        'title' => $post->post_title,
        'excerpt' => $post->post_excerpt ?: wp_trim_words($post->post_content, 20),
        'featured_image' => get_the_post_thumbnail_url($post_id, 'large'),
        'meta' => get_post_meta($post_id)
    );
    
    wp_send_json_success($data);
}
add_action('wp_ajax_card_get_post_data', 'card_get_post_data');
add_action('wp_ajax_nopriv_card_get_post_data', 'card_get_post_data');

/**
 * AJAX handler to save card type
 */
function card_save_card_type() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'card_book_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $type_name = sanitize_text_field($_POST['type_name']);
    $display_name = sanitize_text_field($_POST['display_name']);
    $description = sanitize_text_field($_POST['description']);
    $configuration = json_decode(stripslashes($_POST['configuration']), true);
    
    // Save to options
    $card_types = get_option('card_book_types', array());
    $card_types[$type_name] = array(
        'name' => $display_name,
        'description' => $description,
        'configuration' => $configuration
    );
    
    update_option('card_book_types', $card_types);
    
    wp_send_json_success('Card type saved');
}
add_action('wp_ajax_card_save_card_type', 'card_save_card_type');

/**
 * AJAX handler to get card types
 */
function card_get_card_types() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'card_book_nonce')) {
        wp_die('Security check failed');
    }
    
    $custom_types = get_option('card_book_types', array());
    
    $data = array(
        'built_in' => array(
            'property' => array('name' => 'Property Card', 'description' => 'Real estate property listing'),
            'business' => array('name' => 'Business Card', 'description' => 'Business listing'),
            'testimonial' => array('name' => 'Testimonial Card', 'description' => 'Customer testimonial'),
            'team' => array('name' => 'Team Member Card', 'description' => 'Team member profile'),
            'blog' => array('name' => 'Blog Post Card', 'description' => 'Blog post preview')
        ),
        'custom' => $custom_types
    );
    
    wp_send_json_success($data);
}
add_action('wp_ajax_card_get_card_types', 'card_get_card_types');
add_action('wp_ajax_nopriv_card_get_card_types', 'card_get_card_types');

/**
 * Typography Primitive AJAX Handlers
 */

// Update typography primitive
function update_typography_primitive() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get the posted data
    $type = sanitize_text_field($_POST['type']);
    $slug = sanitize_text_field($_POST['slug']);
    $value = sanitize_text_field($_POST['value']);
    
    // Load existing typography data
    $json_path = get_template_directory() . '/primitives/typography.json';
    $typography_data = json_decode(file_get_contents($json_path), true);
    
    // Update the specific value
    if (isset($typography_data[$type][$slug])) {
        $typography_data[$type][$slug] = $value;
        
        // Save back to JSON file
        $json_content = json_encode($typography_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($json_path, $json_content);
        
        wp_send_json_success(array(
            'message' => 'Typography updated successfully',
            'type' => $type,
            'slug' => $slug,
            'value' => $value
        ));
    } else {
        wp_send_json_error('Invalid typography token');
    }
}
add_action('wp_ajax_update_typography_primitive', 'update_typography_primitive');

// Reset typography primitive
function reset_typography_primitive() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Reset logic would go here
    wp_send_json_success(array(
        'message' => 'Typography reset to defaults'
    ));
}
add_action('wp_ajax_reset_typography_primitive', 'reset_typography_primitive');

// Save all typography to JSON primitive
function save_typography_primitive() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get the posted typography data
    $typography_json = stripslashes($_POST['typography']);
    $typography_data = json_decode($typography_json, true);
    
    if (!$typography_data) {
        wp_send_json_error('Invalid typography data');
        return;
    }
    
    // Get the JSON file path
    $json_path = get_template_directory() . '/primitives/typography.json';
    
    // Save to JSON file
    $json_content = json_encode($typography_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $result = file_put_contents($json_path, $json_content);
    
    if ($result !== false) {
        wp_send_json_success(array(
            'message' => 'Typography saved successfully'
        ));
    } else {
        wp_send_json_error('Failed to save typography data');
    }
}
add_action('wp_ajax_save_typography_primitive', 'save_typography_primitive');

// Sync typography to theme.json
function sync_typography_to_theme_json() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get the posted typography data
    $typography_json = stripslashes($_POST['typography']);
    $typography_data = json_decode($typography_json, true);
    
    if (!$typography_data) {
        wp_send_json_error('Invalid typography data');
        return;
    }
    
    // Get theme.json path
    $theme_json_path = get_template_directory() . '/theme.json';
    
    // Read existing theme.json
    if (file_exists($theme_json_path)) {
        $theme_json_content = file_get_contents($theme_json_path);
        $theme_json = json_decode($theme_json_content, true);
    } else {
        $theme_json = array();
    }
    
    // Ensure custom section exists
    if (!isset($theme_json['custom'])) {
        $theme_json['custom'] = array();
    }
    if (!isset($theme_json['custom']['typography'])) {
        $theme_json['custom']['typography'] = array();
    }
    if (!isset($theme_json['custom']['typography']['baseStyles'])) {
        $theme_json['custom']['typography']['baseStyles'] = array();
    }
    
    // Ensure settings section exists for font sizes and families
    if (!isset($theme_json['settings'])) {
        $theme_json['settings'] = array();
    }
    if (!isset($theme_json['settings']['typography'])) {
        $theme_json['settings']['typography'] = array();
    }
    
    // Convert typography data to theme.json format
    // Font sizes
    if (isset($typography_data['font_sizes'])) {
        $font_sizes = array();
        foreach ($typography_data['font_sizes'] as $slug => $size) {
            $font_sizes[] = array(
                'slug' => $slug,
                'size' => $size,
                'name' => ucwords(str_replace('-', ' ', $slug))
            );
        }
        $theme_json['settings']['typography']['fontSizes'] = $font_sizes;
    }
    
    // Font families
    if (isset($typography_data['font_families'])) {
        $font_families = array();
        foreach ($typography_data['font_families'] as $slug => $family) {
            $font_families[] = array(
                'slug' => $slug,
                'fontFamily' => $family,
                'name' => ucwords(str_replace('-', ' ', $slug))
            );
        }
        $theme_json['settings']['typography']['fontFamilies'] = $font_families;
    }
    
    // Add custom typography properties
    // Font weights
    if (isset($typography_data['font_weights'])) {
        $theme_json['custom']['typography']['baseStyles']['fontWeights'] = $typography_data['font_weights'];
    }
    
    // Line heights
    if (isset($typography_data['line_heights'])) {
        $theme_json['custom']['typography']['baseStyles']['lineHeights'] = $typography_data['line_heights'];
    }
    
    // Letter spacing
    if (isset($typography_data['letter_spacings'])) {
        $theme_json['custom']['typography']['baseStyles']['letterSpacing'] = $typography_data['letter_spacings'];
    }
    
    // Text transforms
    if (isset($typography_data['text_transforms'])) {
        $theme_json['custom']['typography']['baseStyles']['textTransforms'] = $typography_data['text_transforms'];
    }
    
    // Save updated theme.json
    $json_content = json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $result = file_put_contents($theme_json_path, $json_content);
    
    if ($result !== false) {
        wp_send_json_success(array(
            'message' => 'Typography synced to theme.json successfully'
        ));
    } else {
        wp_send_json_error('Failed to update theme.json');
    }
}
add_action('wp_ajax_sync_typography_to_theme_json', 'sync_typography_to_theme_json');

// === SPACING PRIMITIVE AJAX HANDLERS (Updated for categorized structure) ===

// Save spacing primitive
function save_spacing_primitive() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get and validate data
    $spacing_data = isset($_POST['spacing_data']) ? json_decode(stripslashes($_POST['spacing_data']), true) : null;
    
    if (!$spacing_data) {
        wp_send_json_error('Invalid spacing data');
        return;
    }
    
    // Validate expected structure
    $expected_categories = ['scale', 'padding', 'margin', 'gap', 'layout'];
    foreach ($expected_categories as $category) {
        if (!isset($spacing_data[$category]) || !is_array($spacing_data[$category])) {
            $spacing_data[$category] = [];
        }
    }
    
    // Save to JSON file
    $json_path = get_template_directory() . '/primitives/spacing.json';
    
    // Create backup before saving
    if (file_exists($json_path)) {
        $backup_path = get_template_directory() . '/primitives/spacing.backup.json';
        copy($json_path, $backup_path);
    }
    
    $result = file_put_contents($json_path, json_encode($spacing_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Trigger action for any additional processing
        do_action('mi_spacing_primitive_saved', $spacing_data);
        
        wp_send_json_success('Spacing primitive saved successfully');
    } else {
        wp_send_json_error('Failed to save spacing primitive');
    }
}
add_action('wp_ajax_save_spacing_primitive', 'save_spacing_primitive');

// Sync spacing to theme.json
function sync_spacing_to_theme_json() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get spacing data
    $spacing_data = isset($_POST['spacing_data']) ? json_decode(stripslashes($_POST['spacing_data']), true) : null;
    
    if (!$spacing_data) {
        wp_send_json_error('Invalid spacing data');
        return;
    }
    
    // Load existing theme.json
    $theme_json_path = get_template_directory() . '/theme.json';
    $theme_json = json_decode(file_get_contents($theme_json_path), true);
    
    if (!$theme_json) {
        wp_send_json_error('Failed to load theme.json');
        return;
    }
    
    // Update spacing settings
    if (!isset($theme_json['settings']['spacing'])) {
        $theme_json['settings']['spacing'] = [];
    }
    
    // Convert scale values to spacingSizes
    $theme_json['settings']['spacing']['spacingSizes'] = [];
    if (isset($spacing_data['scale'])) {
        foreach ($spacing_data['scale'] as $slug => $size) {
            $theme_json['settings']['spacing']['spacingSizes'][] = [
                'slug' => $slug,
                'size' => $size,
                'name' => strtoupper(str_replace('-', ' ', $slug))
            ];
        }
    }
    
    // Ensure spacing features are enabled
    $theme_json['settings']['spacing']['blockGap'] = true;
    $theme_json['settings']['spacing']['margin'] = true;
    $theme_json['settings']['spacing']['padding'] = true;
    $theme_json['settings']['spacing']['units'] = ['px', 'em', 'rem', '%', 'vh', 'vw'];
    
    // Custom spacing values (for documentation/reference)
    if (!isset($theme_json['settings']['custom'])) {
        $theme_json['settings']['custom'] = [];
    }
    
    $theme_json['settings']['custom']['spacing'] = [
        'padding' => $spacing_data['padding'] ?? [],
        'margin' => $spacing_data['margin'] ?? [],
        'gap' => $spacing_data['gap'] ?? [],
        'layout' => $spacing_data['layout'] ?? []
    ];
    
    // Create backup before saving
    $backup_path = get_template_directory() . '/theme.json.backup';
    copy($theme_json_path, $backup_path);
    
    // Save updated theme.json
    $result = file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Clear any caches
        do_action('mi_theme_json_updated', 'spacing');
        
        wp_send_json_success('Spacing synced to theme.json');
    } else {
        wp_send_json_error('Failed to sync to theme.json');
    }
}
add_action('wp_ajax_sync_spacing_to_theme_json', 'sync_spacing_to_theme_json');

// === LAYOUT PRIMITIVE AJAX HANDLERS ===

/**
 * AJAX handler for getting layout primitive data
 */
function mi_get_layout_primitive() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $layout_file = get_template_directory() . '/primitives/layout.json';
    
    if (file_exists($layout_file)) {
        $layout_data = json_decode(file_get_contents($layout_file), true);
        wp_send_json_success($layout_data);
    } else {
        wp_send_json_error('Layout primitive file not found');
    }
}
add_action('wp_ajax_mi_get_layout_primitive', 'mi_get_layout_primitive');

/**
 * AJAX handler for saving layout primitive data
 */
function mi_save_layout_primitive() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    if (!isset($_POST['layout_data'])) {
        wp_send_json_error('No layout data provided');
        return;
    }

    $layout_data = json_decode(stripslashes($_POST['layout_data']), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Invalid JSON data');
        return;
    }

    $layout_file = get_template_directory() . '/primitives/layout.json';
    $result = file_put_contents($layout_file, json_encode($layout_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Clear any caches
        do_action('mi_primitive_updated', 'layout');
        
        wp_send_json_success('Layout primitive saved successfully');
    } else {
        wp_send_json_error('Failed to save layout primitive');
    }
}
add_action('wp_ajax_mi_save_layout_primitive', 'mi_save_layout_primitive');

/**
 * AJAX handler for syncing layout primitive to theme.json
 */
function mi_sync_layout_to_theme() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    if (!isset($_POST['layout_data'])) {
        wp_send_json_error('No layout data provided');
        return;
    }

    $layout_data = json_decode(stripslashes($_POST['layout_data']), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Invalid JSON data');
        return;
    }

    // Get current theme.json
    $theme_json_path = get_template_directory() . '/theme.json';
    $theme_json = json_decode(file_get_contents($theme_json_path), true);
    
    if (!$theme_json) {
        wp_send_json_error('Could not read theme.json');
        return;
    }

    // Initialize settings.layout if it doesn't exist
    if (!isset($theme_json['settings'])) {
        $theme_json['settings'] = array();
    }
    if (!isset($theme_json['settings']['layout'])) {
        $theme_json['settings']['layout'] = array();
    }

    // Map layout primitive data to theme.json structure
    if (isset($layout_data['containers'])) {
        $theme_json['settings']['layout']['contentSize'] = $layout_data['containers']['lg'] ?? '1024px';
        $theme_json['settings']['layout']['wideSize'] = $layout_data['containers']['xl'] ?? '1280px';
    }

    // Add custom layout tokens to custom section
    if (!isset($theme_json['settings']['custom'])) {
        $theme_json['settings']['custom'] = array();
    }
    $theme_json['settings']['custom']['layout'] = $layout_data;

    // Save theme.json
    $result = file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Clear any caches
        do_action('mi_theme_json_updated', 'layout');
        
        wp_send_json_success('Layout synced to theme.json');
    } else {
        wp_send_json_error('Failed to sync to theme.json');
    }
}
add_action('wp_ajax_mi_sync_layout_to_theme', 'mi_sync_layout_to_theme');

/**
 * AJAX handler for resetting layout primitive to defaults
 */
function mi_reset_layout_primitive() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Default layout data
    $default_layout = array(
        'containers' => array(
            'xs' => '480px',
            'sm' => '640px',
            'md' => '768px',
            'lg' => '1024px',
            'xl' => '1280px',
            '2xl' => '1536px',
            'full' => '100%'
        ),
        'breakpoints' => array(
            'mobile' => '480px',
            'tablet' => '768px',
            'desktop' => '1024px',
            'wide' => '1280px',
            'ultrawide' => '1536px'
        ),
        'grid' => array(
            'columns-2' => 'repeat(2, minmax(0, 1fr))',
            'columns-3' => 'repeat(3, minmax(0, 1fr))',
            'columns-4' => 'repeat(4, minmax(0, 1fr))',
            'columns-6' => 'repeat(6, minmax(0, 1fr))',
            'columns-12' => 'repeat(12, minmax(0, 1fr))',
            'auto-fit' => 'repeat(auto-fit, minmax(250px, 1fr))',
            'auto-fill' => 'repeat(auto-fill, minmax(200px, 1fr))'
        ),
        'flexbox' => array(
            'center' => 'flex items-center justify-center',
            'between' => 'flex items-center justify-between',
            'around' => 'flex items-center justify-around',
            'start' => 'flex items-start justify-start',
            'end' => 'flex items-end justify-end',
            'column' => 'flex flex-col',
            'row' => 'flex flex-row',
            'wrap' => 'flex flex-wrap'
        ),
        'aspectRatios' => array(
            'square' => '1 / 1',
            'video' => '16 / 9',
            'golden' => '1.618 / 1',
            'photo' => '4 / 3',
            'portrait' => '3 / 4',
            'wide' => '21 / 9',
            'ultra-wide' => '32 / 9'
        ),
        'zIndex' => array(
            'behind' => '-1',
            'default' => '0',
            'dropdown' => '10',
            'sticky' => '20',
            'fixed' => '30',
            'modal' => '40',
            'popover' => '50',
            'tooltip' => '60',
            'toast' => '70'
        )
    );

    $layout_file = get_template_directory() . '/primitives/layout.json';
    $result = file_put_contents($layout_file, json_encode($default_layout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Clear any caches
        do_action('mi_primitive_updated', 'layout');
        
        wp_send_json_success($default_layout);
    } else {
        wp_send_json_error('Failed to reset layout primitive');
    }
}
add_action('wp_ajax_mi_reset_layout_primitive', 'mi_reset_layout_primitive');

// === BORDERS PRIMITIVE AJAX HANDLERS ===

// Save borders primitive
function save_borders_primitive() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get and validate data
    $borders_data = isset($_POST['borders_data']) ? json_decode(stripslashes($_POST['borders_data']), true) : null;
    
    if (!$borders_data) {
        wp_send_json_error('Invalid borders data');
        return;
    }
    
    // Validate expected structure
    $expected_categories = ['widths', 'styles', 'radii'];
    foreach ($expected_categories as $category) {
        if (!isset($borders_data[$category]) || !is_array($borders_data[$category])) {
            $borders_data[$category] = [];
        }
    }
    
    // Save to JSON file
    $json_path = get_template_directory() . '/primitives/borders.json';
    
    // Create backup before saving
    if (file_exists($json_path)) {
        $backup_path = get_template_directory() . '/primitives/borders.backup.json';
        copy($json_path, $backup_path);
    }
    
    $result = file_put_contents($json_path, json_encode($borders_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Trigger action for any additional processing
        do_action('mi_borders_primitive_saved', $borders_data);
        
        wp_send_json_success('Borders primitive saved successfully');
    } else {
        wp_send_json_error('Failed to save borders primitive');
    }
}
add_action('wp_ajax_save_borders_primitive', 'save_borders_primitive');

// Sync borders to theme.json
function sync_borders_to_theme_json() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get borders data
    $borders_data = isset($_POST['borders_data']) ? json_decode(stripslashes($_POST['borders_data']), true) : null;
    
    if (!$borders_data) {
        wp_send_json_error('Invalid borders data');
        return;
    }
    
    // Load existing theme.json
    $theme_json_path = get_template_directory() . '/theme.json';
    $theme_json = json_decode(file_get_contents($theme_json_path), true);
    
    if (!$theme_json) {
        wp_send_json_error('Failed to load theme.json');
        return;
    }
    
    // Ensure settings structure exists
    if (!isset($theme_json['settings'])) {
        $theme_json['settings'] = [];
    }
    if (!isset($theme_json['settings']['custom'])) {
        $theme_json['settings']['custom'] = [];
    }
    
    // Add borders to custom settings
    $theme_json['settings']['custom']['borders'] = [
        'widths' => $borders_data['widths'] ?? [],
        'styles' => $borders_data['styles'] ?? [],
        'radii' => $borders_data['radii'] ?? []
    ];
    
    // Enable border features
    if (!isset($theme_json['settings']['border'])) {
        $theme_json['settings']['border'] = [];
    }
    $theme_json['settings']['border']['color'] = true;
    $theme_json['settings']['border']['radius'] = true;
    $theme_json['settings']['border']['style'] = true;
    $theme_json['settings']['border']['width'] = true;
    
    // Create backup before saving
    $backup_path = get_template_directory() . '/theme.json.backup';
    copy($theme_json_path, $backup_path);
    
    // Save updated theme.json
    $result = file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Clear any caches
        do_action('mi_theme_json_updated', 'borders');
        
        wp_send_json_success('Borders synced to theme.json');
    } else {
        wp_send_json_error('Failed to sync to theme.json');
    }
}
add_action('wp_ajax_sync_borders_to_theme_json', 'sync_borders_to_theme_json');

// === SHADOWS PRIMITIVE AJAX HANDLERS ===

// Save shadows primitive
function save_shadows_primitive() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get and validate data
    $shadows_data = isset($_POST['shadows_data']) ? json_decode(stripslashes($_POST['shadows_data']), true) : null;
    
    if (!$shadows_data) {
        wp_send_json_error('Invalid shadows data');
        return;
    }
    
    // Validate expected structure
    $expected_categories = ['scale', 'elevation', 'inset', 'colored', 'special'];
    foreach ($expected_categories as $category) {
        if (!isset($shadows_data[$category]) || !is_array($shadows_data[$category])) {
            $shadows_data[$category] = [];
        }
    }
    
    // Save to JSON file
    $json_path = get_template_directory() . '/primitives/shadows.json';
    
    // Create backup before saving
    if (file_exists($json_path)) {
        $backup_path = get_template_directory() . '/primitives/shadows.backup.json';
        copy($json_path, $backup_path);
    }
    
    $result = file_put_contents($json_path, json_encode($shadows_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Trigger action for any additional processing
        do_action('mi_shadows_primitive_saved', $shadows_data);
        
        wp_send_json_success('Shadows primitive saved successfully');
    } else {
        wp_send_json_error('Failed to save shadows primitive');
    }
}
add_action('wp_ajax_save_shadows_primitive', 'save_shadows_primitive');

// Sync shadows to theme.json
function sync_shadows_to_theme_json() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get shadows data
    $shadows_data = isset($_POST['shadows_data']) ? json_decode(stripslashes($_POST['shadows_data']), true) : null;
    
    if (!$shadows_data) {
        wp_send_json_error('Invalid shadows data');
        return;
    }
    
    // Load existing theme.json
    $theme_json_path = get_template_directory() . '/theme.json';
    $theme_json = json_decode(file_get_contents($theme_json_path), true);
    
    if (!$theme_json) {
        wp_send_json_error('Failed to load theme.json');
        return;
    }
    
    // Ensure settings structure exists
    if (!isset($theme_json['settings'])) {
        $theme_json['settings'] = [];
    }
    if (!isset($theme_json['settings']['custom'])) {
        $theme_json['settings']['custom'] = [];
    }
    
    // Add shadows to custom settings
    $theme_json['settings']['custom']['shadows'] = [
        'scale' => $shadows_data['scale'] ?? [],
        'elevation' => $shadows_data['elevation'] ?? [],
        'inset' => $shadows_data['inset'] ?? [],
        'colored' => $shadows_data['colored'] ?? [],
        'special' => $shadows_data['special'] ?? []
    ];
    
    // Enable shadow features
    if (!isset($theme_json['settings']['shadow'])) {
        $theme_json['settings']['shadow'] = [];
    }
    
    // Convert scale shadows to WordPress shadow presets
    $theme_json['settings']['shadow']['presets'] = [];
    if (isset($shadows_data['scale'])) {
        foreach ($shadows_data['scale'] as $slug => $value) {
            $theme_json['settings']['shadow']['presets'][] = [
                'slug' => $slug,
                'shadow' => $value,
                'name' => strtoupper(str_replace('-', ' ', $slug))
            ];
        }
    }
    
    // Create backup before saving
    $backup_path = get_template_directory() . '/theme.json.backup';
    copy($theme_json_path, $backup_path);
    
    // Save updated theme.json
    $result = file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Clear any caches
        do_action('mi_theme_json_updated', 'shadows');
        
        wp_send_json_success('Shadows synced to theme.json');
    } else {
        wp_send_json_error('Failed to sync to theme.json');
    }
}
add_action('wp_ajax_sync_shadows_to_theme_json', 'sync_shadows_to_theme_json');

// Save animations primitive
function save_animations_primitive() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get and validate data
    $animations_data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : null;
    
    if (!$animations_data) {
        wp_send_json_error('Invalid animations data');
        return;
    }
    
    // Validate expected structure
    $expected_categories = ['durations', 'easings', 'delays', 'transitions'];
    foreach ($expected_categories as $category) {
        if (!isset($animations_data[$category]) || !is_array($animations_data[$category])) {
            $animations_data[$category] = [];
        }
    }
    
    // Save to JSON file
    $json_path = get_template_directory() . '/primitives/animations.json';
    
    // Create backup before saving
    if (file_exists($json_path)) {
        $backup_path = get_template_directory() . '/primitives/animations.backup.json';
        copy($json_path, $backup_path);
    }
    
    $result = file_put_contents($json_path, json_encode($animations_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Trigger action for any additional processing
        do_action('mi_animations_primitive_saved', $animations_data);
        
        wp_send_json_success('Animations primitive saved successfully');
    } else {
        wp_send_json_error('Failed to save animations primitive');
    }
}
add_action('wp_ajax_save_animations_primitive', 'save_animations_primitive');

// Sync animations to theme.json
function sync_animations_to_theme_json() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Load animations data from JSON file
    $animations_json_path = get_template_directory() . '/primitives/animations.json';
    if (!file_exists($animations_json_path)) {
        wp_send_json_error('Animations primitive file not found');
        return;
    }
    
    $animations_data = json_decode(file_get_contents($animations_json_path), true);
    if (!$animations_data) {
        wp_send_json_error('Invalid animations data');
        return;
    }
    
    // Load existing theme.json
    $theme_json_path = get_template_directory() . '/theme.json';
    $theme_json = json_decode(file_get_contents($theme_json_path), true);
    
    if (!$theme_json) {
        wp_send_json_error('Failed to load theme.json');
        return;
    }
    
    // Ensure settings structure exists
    if (!isset($theme_json['settings'])) {
        $theme_json['settings'] = [];
    }
    if (!isset($theme_json['settings']['custom'])) {
        $theme_json['settings']['custom'] = [];
    }
    
    // Add animations to custom settings
    $theme_json['settings']['custom']['animations'] = [
        'durations' => $animations_data['durations'] ?? [],
        'easings' => $animations_data['easings'] ?? [],
        'delays' => $animations_data['delays'] ?? [],
        'transitions' => $animations_data['transitions'] ?? []
    ];
    
    // Create backup before saving
    $backup_path = get_template_directory() . '/theme.json.backup';
    copy($theme_json_path, $backup_path);
    
    // Save updated theme.json
    $result = file_put_contents($theme_json_path, json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    if ($result !== false) {
        // Clear any caches
        do_action('mi_theme_json_updated', 'animations');
        
        wp_send_json_success('Animations synced to theme.json');
    } else {
        wp_send_json_error('Failed to sync to theme.json');
    }
}
add_action('wp_ajax_sync_animations_to_theme_json', 'sync_animations_to_theme_json');

/**
 * AJAX handler for saving colors primitive
 */
function migv_save_colors_primitive() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check user capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get the colors data
    $colors_data = isset($_POST['colors']) ? $_POST['colors'] : array();
    
    if (empty($colors_data)) {
        wp_send_json_error('No colors data provided');
        return;
    }
    
    // Sanitize the colors data
    $sanitized_colors = array();
    foreach ($colors_data as $slug => $value) {
        $sanitized_slug = sanitize_key($slug);
        $sanitized_value = sanitize_hex_color($value);
        
        if ($sanitized_slug && $sanitized_value) {
            $sanitized_colors[$sanitized_slug] = $sanitized_value;
        }
    }
    
    if (empty($sanitized_colors)) {
        wp_send_json_error('No valid colors provided');
        return;
    }
    
    // Define file paths
    $colors_file = get_template_directory() . '/primitives/colors.json';
    $backup_file = get_template_directory() . '/primitives/colors.backup.json';
    
    // Create backup of existing file
    if (file_exists($colors_file)) {
        copy($colors_file, $backup_file);
    }
    
    // Write the new colors file (flat structure)
    $json_content = json_encode($sanitized_colors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    if (file_put_contents($colors_file, $json_content) === false) {
        wp_send_json_error('Failed to save colors file');
        return;
    }
    
    wp_send_json_success(array(
        'message' => 'Colors saved successfully',
        'colors' => $sanitized_colors
    ));
}
add_action('wp_ajax_save_colors_primitive', 'migv_save_colors_primitive');

/**
 * AJAX handler for syncing colors primitive to theme.json
 */
function migv_sync_colors_primitive() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mi_design_book_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Check user capabilities
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Load colors from colors.json
    $colors_file = get_template_directory() . '/primitives/colors.json';
    
    if (!file_exists($colors_file)) {
        wp_send_json_error('Colors file not found');
        return;
    }
    
    $colors_data = json_decode(file_get_contents($colors_file), true);
    
    if (!$colors_data) {
        wp_send_json_error('Invalid colors data');
        return;
    }
    
    // Load theme.json
    $theme_json_file = get_template_directory() . '/theme.json';
    
    if (!file_exists($theme_json_file)) {
        wp_send_json_error('theme.json not found');
        return;
    }
    
    $theme_json = json_decode(file_get_contents($theme_json_file), true);
    
    if (!$theme_json) {
        wp_send_json_error('Invalid theme.json');
        return;
    }
    
    // Create backup
    $backup_file = get_template_directory() . '/theme.backup.json';
    copy($theme_json_file, $backup_file);
    
    // Update color palette in theme.json (flat structure)
    $color_palette = array();
    foreach ($colors_data as $slug => $value) {
        $color_palette[] = array(
            'slug' => $slug,
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'color' => $value
        );
    }
    
    // Ensure the structure exists
    if (!isset($theme_json['settings'])) {
        $theme_json['settings'] = array();
    }
    if (!isset($theme_json['settings']['color'])) {
        $theme_json['settings']['color'] = array();
    }
    
    // Update the palette
    $theme_json['settings']['color']['palette'] = $color_palette;
    
    // Save theme.json
    $json_content = json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    if (file_put_contents($theme_json_file, $json_content) === false) {
        wp_send_json_error('Failed to update theme.json');
        return;
    }
    
    wp_send_json_success(array(
        'message' => 'Colors synced to theme.json successfully',
        'colors_count' => count($color_palette)
    ));
}
add_action('wp_ajax_sync_colors_primitive', 'migv_sync_colors_primitive');

/**
 * Register main Design Book menu
 */
function register_design_book_menu() {
    add_menu_page(
        'Design Book',
        'Design Book',
        'edit_theme_options',
        'design-book',
        'render_design_book_main_page',
        'dashicons-art',
        30
    );
}
add_action('admin_menu', 'register_design_book_menu');

/**
 * Render main design book page
 */
function render_design_book_main_page() {
    echo '<div class="wrap">';
    echo '<h1>Design Book</h1>';
    echo '<p>Welcome to the Design Book. Use the submenu to access different primitive editors.</p>';
    echo '<ul>';
    echo '<li><a href="' . admin_url('admin.php?page=spacing-primitive') . '">Spacing Editor</a></li>';
    // Add links to other editors as they are created
    echo '</ul>';
    echo '</div>';
}

/**
 * Page registration for Spacing Editor
 */
function register_spacing_editor_page() {
    add_submenu_page(
        'design-book', // Parent menu slug
        'Spacing Primitive',
        'Spacing',
        'edit_theme_options',
        'spacing-primitive',
        'render_spacing_editor_page'
    );
}
add_action('admin_menu', 'register_spacing_editor_page');

/**
 * Render the spacing editor page
 */
function render_spacing_editor_page() {
    // Enqueue editor assets
    wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.0');
    wp_enqueue_script('primitive-spacing', get_template_directory_uri() . '/assets/js/primitive-spacing.js', array('jquery'), '1.0.0', true);
    
    // Localize script
    wp_localize_script('primitive-spacing', 'primitiveSpacing', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mi_design_book_nonce'),
        'primitiveType' => 'spacing'
    ));
    
    // Prepare context for Timber
    $context = Timber::context();
    $context['page_title'] = 'Spacing Primitive Editor';
    
    // Render the template
    Timber::render('design-book-editors/spacing-editor.twig', $context);
}

/**
 * Page registration for Layout Editor
 */
function register_layout_editor_page() {
    add_submenu_page(
        'design-book', // Parent menu slug
        'Layout Primitive',
        'Layout',
        'edit_theme_options',
        'layout-primitive',
        'render_layout_editor_page'
    );
}
add_action('admin_menu', 'register_layout_editor_page');

/**
 * Render the layout editor page
 */
function render_layout_editor_page() {
    // Enqueue editor assets
    wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.0');
    wp_enqueue_script('primitive-layout', get_template_directory_uri() . '/assets/js/primitive-layout.js', array('jquery'), '1.0.0', true);
    
    // Localize script
    wp_localize_script('primitive-layout', 'primitiveLayout', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mi_design_book_nonce'),
        'primitiveType' => 'layout',
        'canEdit' => current_user_can('edit_theme_options')
    ));
    
    // Prepare context for Timber
    $context = Timber::context();
    $context['page_title'] = 'Layout Primitive Editor';
    
    // Render the template
    Timber::render('design-book-editors/layout-editor.twig', $context);
}

/**
 * Enqueue design book editor styles for color book page
 */
function migv_enqueue_color_book_styles() {
    if (is_page('color-book') || is_page_template('page-color-book.php')) {
        wp_enqueue_style('design-book-editors', get_template_directory_uri() . '/assets/css/design-book-editors.css', array(), '1.0.1');
        wp_enqueue_script('primitive-colors', get_template_directory_uri() . '/assets/js/primitive-colors.js', array('jquery'), '1.0.1', true);
        
        // Localize script for AJAX
        wp_localize_script('primitive-colors', 'miGV', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('migv_ajax_nonce'),
            'theme_uri' => get_template_directory_uri()
        ));
    }
}
add_action('wp_enqueue_scripts', 'migv_enqueue_color_book_styles');

/**
 * Enqueue scripts for the text editor component.
 */

