<?php
/**
 * Dynamic Component Registry
 * Replacement for static registry that uses dynamic registration
 */

class UmbralEditor_Component_Registry {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
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
        // Private constructor for singleton
    }
    
    /**
     * Get all registered categories
     */
    public function getCategories() {
        return umbral_get_dynamic_categories();
    }
    
    /**
     * Get all registered components
     */
    public function getComponents() {
        return umbral_get_dynamic_components();
    }
    
    /**
     * Get components formatted for command palette
     */
    public function getComponentsForCommandPalette() {
        $components = umbral_get_dynamic_components();
        $formatted_components = [];
        
        foreach ($components as $category_slug => $category_components) {
            foreach ($category_components as $component_slug => $component_data) {
                $formatted_components[] = [
                    'id' => $component_slug,
                    'component' => $component_slug, // React expects 'component' field
                    'category' => $category_slug,
                    'type' => 'component', // React expects 'type' field for handleAddComponent
                    'label' => $component_data['label'] ?? ucwords(str_replace(['-', '_'], ' ', $component_slug)),
                    'title' => $component_data['title'] ?? $component_data['label'] ?? ucwords(str_replace(['-', '_'], ' ', $component_slug)),
                    'description' => $component_data['description'] ?? '',
                    'icon' => $component_data['icon'] ?? '🧩',
                    'fields' => $component_data['fields'] ?? []
                ];
            }
        }
        
        return $formatted_components;
    }
    
    /**
     * Validate component data
     */
    public function validateComponent($component_data) {
        // Check if component has required fields
        if (!isset($component_data['component']) || !isset($component_data['category'])) {
            return false;
        }
        
        $components = umbral_get_dynamic_components();
        
        // Check if component exists in the specified category
        $category = $component_data['category'];
        $component = $component_data['component'];
        
        return isset($components[$category][$component]);
    }
    
    /**
     * Get a specific component
     */
    public function getComponent($category, $component_id = null) {
        $components = umbral_get_dynamic_components();
        
        // If two parameters provided, look in specific category
        if ($component_id !== null) {
            return $components[$category][$component_id] ?? null;
        }
        
        // If one parameter provided, search all categories (backward compatibility)
        foreach ($components as $category_components) {
            if (isset($category_components[$category])) {
                return $category_components[$category];
            }
        }
        
        return null;
    }
    
    /**
     * Check if a component exists
     */
    public function hasComponent($component_id) {
        return $this->getComponent($component_id) !== null;
    }
    
    /**
     * Get components in a specific category
     */
    public function getComponentsByCategory($category_slug) {
        $components = umbral_get_dynamic_components();
        return $components[$category_slug] ?? [];
    }
}