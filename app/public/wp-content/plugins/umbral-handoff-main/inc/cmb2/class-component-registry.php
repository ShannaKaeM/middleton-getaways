<?php
/**
 * Component Registry - Manages registration of components and categories
 */

class UmbralEditor_Component_Registry {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Registered categories
     */
    private $categories = [];
    
    /**
     * Registered components
     */
    private $components = [];
    
    /**
     * Get singleton instance
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
        // Initialize default categories
        $this->initDefaultCategories();
    }
    
    /**
     * Initialize default categories
     */
    private function initDefaultCategories() {
        $this->registerCategory('hero', [
            'label' => __('Hero Sections', 'umbral-editor'),
            'description' => __('Banner and hero components', 'umbral-editor'),
            'icon' => '🎯'
        ]);
        
        $this->registerCategory('testimonials', [
            'label' => __('Testimonials', 'umbral-editor'),
            'description' => __('Customer testimonials and reviews', 'umbral-editor'),
            'icon' => '💬'
        ]);
        
        $this->registerCategory('content', [
            'label' => __('Content Blocks', 'umbral-editor'),
            'description' => __('General content components', 'umbral-editor'),
            'icon' => '📄'
        ]);
    }
    
    /**
     * Register a new category
     */
    public function registerCategory($slug, $args = []) {
        $defaults = [
            'label' => ucfirst($slug),
            'description' => '',
            'icon' => '📦',
            'order' => 10
        ];
        
        $this->categories[$slug] = wp_parse_args($args, $defaults);
        
        return true;
    }
    
    /**
     * Register a new component
     */
    public function registerComponent($category, $slug, $args = []) {
        if (!isset($this->categories[$category])) {
            return new WP_Error('invalid_category', 'Category does not exist');
        }
        
        $defaults = [
            'label' => ucfirst($slug),
            'description' => '',
            'icon' => '🧩',
            'fields' => [],
            'template' => '',
            'order' => 10
        ];
        
        $component = wp_parse_args($args, $defaults);
        $component['category'] = $category;
        
        if (!isset($this->components[$category])) {
            $this->components[$category] = [];
        }
        
        $this->components[$category][$slug] = $component;
        
        return true;
    }
    
    /**
     * Get all categories
     */
    public function getCategories() {
        return $this->categories;
    }
    
    /**
     * Get components for a specific category
     */
    public function getComponents($category = null) {
        if ($category) {
            return isset($this->components[$category]) ? $this->components[$category] : [];
        }
        return $this->components;
    }
    
    /**
     * Get a specific component
     */
    public function getComponent($category, $slug) {
        if (isset($this->components[$category][$slug])) {
            return $this->components[$category][$slug];
        }
        return null;
    }
    
    /**
     * Get components formatted for React command palette
     */
    public function getComponentsForCommandPalette() {
        $commands = [];
        
        foreach ($this->categories as $cat_slug => $category) {
            // Only add components in this category (no category headers)
            $components = $this->getComponents($cat_slug);
            foreach ($components as $comp_slug => $component) {
                $commands[] = [
                    'id' => "{$cat_slug}_{$comp_slug}",
                    'type' => 'component',
                    'title' => $component['label'],
                    'description' => $component['description'],
                    'icon' => $component['icon'],
                    'category' => $cat_slug,
                    'categoryTitle' => $category['label'],
                    'categoryIcon' => $category['icon'],
                    'component' => $comp_slug,
                    'fields' => $component['fields']
                ];
            }
        }
        
        return $commands;
    }
    
    /**
     * Validate component data structure
     */
    public function validateComponent($data) {
        if (!isset($data['category'], $data['component'])) {
            return false;
        }
        
        $component = $this->getComponent($data['category'], $data['component']);
        if (!$component) {
            return false;
        }
        
        // Validate field data against component definition
        if (isset($data['fields']) && is_array($data['fields'])) {
            foreach ($data['fields'] as $field_key => $field_value) {
                if (!isset($component['fields'][$field_key])) {
                    return false;
                }
            }
        }
        
        return true;
    }
}

// Global helper functions
function umbral_register_component_category($slug, $args = []) {
    return UmbralEditor_Component_Registry::getInstance()->registerCategory($slug, $args);
}

function umbral_register_component($category, $slug, $args = []) {
    return UmbralEditor_Component_Registry::getInstance()->registerComponent($category, $slug, $args);
}

function umbral_get_components($category = null) {
    return UmbralEditor_Component_Registry::getInstance()->getComponents($category);
}

function umbral_get_component($category, $slug) {
    return UmbralEditor_Component_Registry::getInstance()->getComponent($category, $slug);
}