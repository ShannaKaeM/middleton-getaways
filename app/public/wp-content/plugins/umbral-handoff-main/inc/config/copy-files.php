<?php 

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Global arrays to store dynamically registered components and categories
global $umbral_dynamic_categories, $umbral_dynamic_components;
$umbral_dynamic_categories = [];
$umbral_dynamic_components = [];

// Dynamic component registration functions
if (!function_exists('umbral_register_component_category')) {
    function umbral_register_component_category($slug, $args) {
        global $umbral_dynamic_categories;
        $umbral_dynamic_categories[$slug] = $args;
        error_log("Umbral Editor: Dynamically registered category: {$slug}");
    }
}

if (!function_exists('umbral_register_component')) {
    function umbral_register_component($category, $slug, $args) {
        global $umbral_dynamic_components;
        if (!isset($umbral_dynamic_components[$category])) {
            $umbral_dynamic_components[$category] = [];
        }
        $umbral_dynamic_components[$category][$slug] = $args;
        error_log("Umbral Editor: Dynamically registered component: {$category}/{$slug}");
    }
}

// Helper functions to get registered data
if (!function_exists('umbral_get_dynamic_categories')) {
    function umbral_get_dynamic_categories() {
        global $umbral_dynamic_categories;
        return $umbral_dynamic_categories ?: [];
    }
}

if (!function_exists('umbral_get_dynamic_components')) {
    function umbral_get_dynamic_components($category = null) {
        global $umbral_dynamic_components;
        if ($category) {
            return $umbral_dynamic_components[$category] ?? [];
        }
        return $umbral_dynamic_components ?: [];
    }
}

/**
 * Umbral Editor - Copy Files Configuration
 * 
 * Check for ./umbral in child theme, then parent theme, then uploads directory 
 * If none are found copy ./inc/[create-files] folders / files to /wp-content/uploads/umbral 
 * If parent or child is found, just make an empty ./umbral directory in uploads
 */
class UmbralEditor_File_Manager {
    
    /**
     * Initialize file management
     */
    public static function init() {
        add_action('init', [__CLASS__, 'ensureDirectoriesExist'], 5);
    }
    
    /**
     * Ensure umbral directories exist according to priority
     */
    public static function ensureDirectoriesExist() {
        $child_umbral = get_stylesheet_directory() . '/umbral';
        $parent_umbral = get_template_directory() . '/umbral';
        $uploads_umbral = wp_upload_dir()['basedir'] . '/umbral';
        
        // Check if any umbral directory exists in themes
        $theme_has_umbral = (is_child_theme() && is_dir($child_umbral)) || is_dir($parent_umbral);
        
        if ($theme_has_umbral) {
            // Theme has umbral directory, just ensure uploads has empty umbral directory
            if (!is_dir($uploads_umbral)) {
                wp_mkdir_p($uploads_umbral);
                error_log('Umbral Editor: Created empty uploads/umbral directory');
            }
        } else {
            // No theme umbral directory found, copy template files to uploads
            self::copyTemplateFiles($uploads_umbral);
        }
        
        // Register dynamic components after ensuring directories exist
        self::registerDynamicComponents();
    }
    
    /**
     * Copy template files from inc/[create-files] to uploads/umbral
     */
    private static function copyTemplateFiles($destination) {
        $source = UMBRAL_EDITOR_DIR . 'inc/[create-files]';
        
        if (!is_dir($source)) {
            error_log('Umbral Editor: Template files directory not found: ' . $source);
            return false;
        }
        
        if (!is_dir($destination)) {
            wp_mkdir_p($destination);
        }
        
        // Copy entire directory structure
        self::copyDirectory($source, $destination);
        
        error_log('Umbral Editor: Copied template files to ' . $destination);
        return true;
    }
    
    /**
     * Recursively copy directory
     */
    private static function copyDirectory($source, $destination) {
        if (!is_dir($source)) {
            return false;
        }
        
        if (!is_dir($destination)) {
            wp_mkdir_p($destination);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $dest_path = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                wp_mkdir_p($dest_path);
            } else {
                copy($item, $dest_path);
            }
        }
        
        return true;
    }
    
    /**
     * Register components dynamically from directory structure
     */
    private static function registerDynamicComponents() {
        // Get the active umbral directory (priority: child > parent > uploads)
        $active_dir = self::getActiveUmbralDirectory();
        
        if (!$active_dir || !is_dir($active_dir . '/editor/components')) {
            return;
        }
        
        $components_dir = $active_dir . '/editor/components';
        self::scanAndRegisterComponents($components_dir);
    }
    
    /**
     * Get the active umbral directory based on priority
     */
    private static function getActiveUmbralDirectory() {
        $child_umbral = get_stylesheet_directory() . '/umbral';
        $parent_umbral = get_template_directory() . '/umbral';
        $uploads_umbral = wp_upload_dir()['basedir'] . '/umbral';
        
        if (is_child_theme() && is_dir($child_umbral . '/editor')) {
            return $child_umbral;
        } elseif (is_dir($parent_umbral . '/editor')) {
            return $parent_umbral;
        } elseif (is_dir($uploads_umbral . '/editor')) {
            return $uploads_umbral;
        }
        
        return null;
    }
    
    /**
     * Scan and register components from directory structure
     */
    private static function scanAndRegisterComponents($components_dir) {
        if (!is_dir($components_dir)) {
            return;
        }
        
        $category_dirs = glob($components_dir . '/*', GLOB_ONLYDIR);
        
        foreach ($category_dirs as $category_dir) {
            $category_slug = strtolower(basename($category_dir));
            
            // Register category
            umbral_register_component_category($category_slug, [
                'label' => ucwords(str_replace(['_', '-'], ' ', $category_slug)),
                'description' => 'Components in the ' . $category_slug . ' category',
                'icon' => '📦'
            ]);
            
            // Scan for components in this category
            $component_dirs = glob($category_dir . '/*', GLOB_ONLYDIR);
            
            foreach ($component_dirs as $component_dir) {
                $component_slug = basename($component_dir);
                $fields_file = $component_dir . '/fields.php';
                
                if (file_exists($fields_file)) {
                    // Include the fields file to register the component
                    include_once $fields_file;
                    error_log("Umbral Editor: Loaded component {$category_slug}/{$component_slug}");
                }
            }
        }
    }
}

// Initialize file manager
UmbralEditor_File_Manager::init();