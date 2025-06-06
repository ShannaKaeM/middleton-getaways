<?php
/**
 * Plugin Name: Umbral Editor - CMB2 Components Field
 * Description: A powerful CMB2 field extension for flexible content components with React-based UI and category system
 * Version: 1.0.0
 * Author: Daniel Snell @Umbral.ai
 * License: GPL v2 or later
 * Text Domain: umbral-editor
 * Domain Path: /languages
 *
 * @package UmbralEditor
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('UMBRAL_EDITOR_VERSION', '1.0.0');
define('UMBRAL_EDITOR_FILE', __FILE__);
define('UMBRAL_EDITOR_DIR', plugin_dir_path(__FILE__));
define('UMBRAL_EDITOR_URL', plugin_dir_url(__FILE__));
define('UMBRAL_EDITOR_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class UmbralEditor {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Plugin modules
     */
    private $admin;
    private $assets;
    private $api;
    
    /**
     * Get single instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->loadDependencies();
        $this->init();
    }
    
    /**
     * Load required dependencies
     */
    private function loadDependencies() {
        // Load Composer autoloader
        if (file_exists(UMBRAL_EDITOR_DIR . 'vendor/autoload.php')) {
            require_once UMBRAL_EDITOR_DIR . 'vendor/autoload.php';
        }
        
        // Initialize Timber.
        Timber\Timber::init();

        // Initialize CMB2 if not already loaded
        if (!class_exists('CMB2_Bootstrap_260', false)) {
            if (file_exists(UMBRAL_EDITOR_DIR . 'vendor/cmb2/cmb2/init.php')) {
                require_once UMBRAL_EDITOR_DIR . 'vendor/cmb2/cmb2/init.php';
                error_log('Umbral Editor: Loaded CMB2 from vendor directory');
            } else {
                error_log('Umbral Editor: CMB2 init.php not found at ' . UMBRAL_EDITOR_DIR . 'vendor/cmb2/cmb2/init.php');
            }
        } else {
            error_log('Umbral Editor: CMB2 already loaded');
        }
        
        require_once UMBRAL_EDITOR_DIR . 'inc/class-admin.php';
        require_once UMBRAL_EDITOR_DIR . 'inc/class-assets.php';
        require_once UMBRAL_EDITOR_DIR . 'inc/class-api.php';
        require_once UMBRAL_EDITOR_DIR . 'inc/class-frontend.php';
        require_once UMBRAL_EDITOR_DIR . 'inc/class-notices.php';
        require_once UMBRAL_EDITOR_DIR . 'inc/class-breakpoints.php';
        require_once UMBRAL_EDITOR_DIR . 'inc/class-component-compiler.php';
        require_once UMBRAL_EDITOR_DIR . 'inc/class-dynamic-registry.php';
        
        // Load Timber configuration
        require_once UMBRAL_EDITOR_DIR . 'inc/config/timber-paths.php';
        
        // Load file management and dynamic component registration
        require_once UMBRAL_EDITOR_DIR . 'inc/config/copy-files.php';
        
        // Load CMB2 extension modules
        $this->loadCMB2Extensions();
        
        // Load debug files for testing
        require_once UMBRAL_EDITOR_DIR . 'simple-test.php';
        if (defined('WP_DEBUG') && WP_DEBUG) {
            require_once UMBRAL_EDITOR_DIR . 'debug-metabox.php';
            require_once UMBRAL_EDITOR_DIR . 'debug-notice.php';
        }
    }
    
    /**
     * Initialize the plugin
     */
    private function init() {
        // Initialize modules
        $this->admin = new UmbralEditor_Admin();
        $this->assets = new UmbralEditor_Assets();
        $this->api = new UmbralEditor_API();
        $this->frontend = new UmbralEditor_Frontend();
        $this->notices = new UmbralEditor_Notices();
        $this->breakpoints = UmbralEditor_Breakpoints::getInstance();
        
        // Hook into WordPress
        add_action('init', [$this, 'onInit']);
        add_action('init', [$this, 'registerBlocks']);
        
        // Ensure CMB2 is loaded early
        add_action('plugins_loaded', [$this, 'ensureCMB2'], 5);
        
        // Initialize modules
        $this->admin->init();
        $this->assets->init();
        $this->api->init();
        // Frontend initializes itself in constructor
        
        // Plugin lifecycle hooks
        register_activation_hook(__FILE__, [$this, 'onActivation']);
        register_deactivation_hook(__FILE__, [$this, 'onDeactivation']);
    }
    
    /**
     * Initialize plugin
     */
    public function onInit() {
        // Load text domain
        load_plugin_textdomain('umbral-editor', false, dirname(UMBRAL_EDITOR_BASENAME) . '/languages');
        
        // Register meta fields for REST API access (needed for block editor)
        $this->registerMetaFields();
    }
    
    /**
     * Register meta fields for REST API access
     */
    private function registerMetaFields() {
        register_post_meta('', 'components', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'description' => 'Umbral Editor components data',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
    }
    
    /**
     * Plugin activation
     */
    public function onActivation() {
        // Flush rewrite rules for REST API
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function onDeactivation() {
        // Clean up
        flush_rewrite_rules();
    }
    
    /**
     * Load CMB2 extension modules
     */
    private function loadCMB2Extensions() {
        $cmb2_files = [
            // 'inc/cmb2/class-component-registry.php',
            // 'inc/cmb2/class-component-category.php', 
            'inc/cmb2/class-components-field.php',
            'inc/cmb2/class-field-renderer.php',
            'inc/cmb2/class-ajax-handler.php',
            'inc/cmb2/class-page-metabox.php'
        ];
        
        foreach ($cmb2_files as $file) {
            $file_path = UMBRAL_EDITOR_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
                error_log('Umbral Editor: Loaded CMB2 file: ' . $file);
            } else {
                error_log('Umbral Editor: CMB2 file not found: ' . $file_path);
            }
        }
        
        // Initialize CMB2 components field
        add_action('cmb2_init', [$this, 'initCMB2Components']);
    }
    
    /**
     * Ensure CMB2 is loaded early
     */
    public function ensureCMB2() {
        error_log('Umbral Editor: ensureCMB2 called on plugins_loaded');
        
        // Try to initialize CMB2 if not already done
        if (!class_exists('CMB2_Bootstrap_260', false)) {
            if (file_exists(UMBRAL_EDITOR_DIR . 'vendor/cmb2/cmb2/init.php')) {
                require_once UMBRAL_EDITOR_DIR . 'vendor/cmb2/cmb2/init.php';
                error_log('Umbral Editor: CMB2 loaded via plugins_loaded hook');
            }
        }
        
        // Check if CMB2 is available
        if (class_exists('CMB2')) {
            error_log('Umbral Editor: CMB2 class available after plugins_loaded');
        } else {
            error_log('Umbral Editor: CMB2 class NOT available after plugins_loaded');
        }
    }
    
    /**
     * Initialize CMB2 Components Field
     */
    public function initCMB2Components() {
        error_log('Umbral Editor: initCMB2Components called');
        if (class_exists('UmbralEditor_Components_Field')) {
            new UmbralEditor_Components_Field();
            error_log('Umbral Editor: Components Field initialized');
        } else {
            error_log('Umbral Editor: UmbralEditor_Components_Field class not found');
        }
    }
    
    /**
     * Register blocks
     */
    public function registerBlocks() {
        error_log('Umbral Editor: Attempting to register blocks');
        
        $block_dir = UMBRAL_EDITOR_DIR . 'blocks/components';
        error_log('Umbral Editor: Block directory path: ' . $block_dir);
        
        // Check if directory exists
        if (!is_dir($block_dir)) {
            error_log('Umbral Editor: Block directory does not exist: ' . $block_dir);
            return;
        }
        
        // Check if block.json exists
        $block_json = $block_dir . '/block.json';
        if (!file_exists($block_json)) {
            error_log('Umbral Editor: block.json does not exist: ' . $block_json);
            return;
        }
        
        // Register the block
        $result = register_block_type($block_dir);
        if ($result) {
            error_log('Umbral Editor: Block registered successfully - ' . $result->name);
        } else {
            error_log('Umbral Editor: Block registration failed');
        }
    }
}

// Initialize the plugin
function umbralEditor() {
    return UmbralEditor::getInstance();
}

// Start the plugin
umbralEditor();