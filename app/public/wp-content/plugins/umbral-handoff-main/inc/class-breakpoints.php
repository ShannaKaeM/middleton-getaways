<?php
/**
 * Breakpoints Management API
 * Handles responsive breakpoint settings and utilities
 */

class UmbralEditor_Breakpoints {
    
    /**
     * Option name for storing breakpoints
     */
    const OPTION_NAME = 'umbral_breakpoints';
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Default breakpoints
     */
    private $default_breakpoints = [
        'um_xs' => [
            'label' => 'Extra Small',
            'min_width' => 0,
            'max_width' => 575,
            'icon' => '📱',
            'description' => 'Mobile phones (portrait)'
        ],
        'um_sm' => [
            'label' => 'Small',
            'min_width' => 576,
            'max_width' => 767,
            'icon' => '📱',
            'description' => 'Mobile phones (landscape)'
        ],
        'um_md' => [
            'label' => 'Medium',
            'min_width' => 768,
            'max_width' => 991,
            'icon' => '📋',
            'description' => 'Tablets'
        ],
        'um_lg' => [
            'label' => 'Large',
            'min_width' => 992,
            'max_width' => 1199,
            'icon' => '💻',
            'description' => 'Desktops'
        ],
        'um_xl' => [
            'label' => 'Extra Large',
            'min_width' => 1200,
            'max_width' => 1399,
            'icon' => '🖥️',
            'description' => 'Large desktops'
        ],
        'um_2xl' => [
            'label' => '2X Large',
            'min_width' => 1400,
            'max_width' => null, // No max width for largest breakpoint
            'icon' => '🖥️',
            'description' => 'Extra large desktops'
        ]
    ];
    
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
        add_action('init', [$this, 'init']);
    }
    
    /**
     * Initialize
     */
    public function init() {
        // Ensure default breakpoints are set
        if (!get_option(self::OPTION_NAME)) {
            $this->resetToDefaults();
        }
    }
    
    /**
     * Get all breakpoints
     */
    public function getBreakpoints() {
        $breakpoints = get_option(self::OPTION_NAME, $this->default_breakpoints);
        
        // Ensure breakpoints are sorted by min_width
        uasort($breakpoints, function($a, $b) {
            return $a['min_width'] <=> $b['min_width'];
        });
        
        return $breakpoints;
    }
    
    /**
     * Get a specific breakpoint
     */
    public function getBreakpoint($key) {
        $breakpoints = $this->getBreakpoints();
        return isset($breakpoints[$key]) ? $breakpoints[$key] : null;
    }
    
    /**
     * Update breakpoints
     */
    public function updateBreakpoints($breakpoints) {
        // Validate breakpoints
        $validated = $this->validateBreakpoints($breakpoints);
        
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        return update_option(self::OPTION_NAME, $validated);
    }
    
    /**
     * Update a single breakpoint
     */
    public function updateBreakpoint($key, $breakpoint_data) {
        $breakpoints = $this->getBreakpoints();
        
        // Validate single breakpoint
        $validated = $this->validateBreakpoint($breakpoint_data);
        
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        $breakpoints[$key] = $validated;
        
        return $this->updateBreakpoints($breakpoints);
    }
    
    /**
     * Delete a breakpoint
     */
    public function deleteBreakpoint($key) {
        $breakpoints = $this->getBreakpoints();
        
        if (!isset($breakpoints[$key])) {
            return new WP_Error('breakpoint_not_found', 'Breakpoint not found');
        }
        
        unset($breakpoints[$key]);
        
        return $this->updateBreakpoints($breakpoints);
    }
    
    /**
     * Add a new breakpoint
     */
    public function addBreakpoint($key, $breakpoint_data) {
        $breakpoints = $this->getBreakpoints();
        
        if (isset($breakpoints[$key])) {
            return new WP_Error('breakpoint_exists', 'Breakpoint already exists');
        }
        
        // Validate breakpoint
        $validated = $this->validateBreakpoint($breakpoint_data);
        
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        $breakpoints[$key] = $validated;
        
        return $this->updateBreakpoints($breakpoints);
    }
    
    /**
     * Reset breakpoints to defaults
     */
    public function resetToDefaults() {
        return update_option(self::OPTION_NAME, $this->default_breakpoints);
    }
    
    /**
     * Validate breakpoints array
     */
    private function validateBreakpoints($breakpoints) {
        if (!is_array($breakpoints)) {
            return new WP_Error('invalid_breakpoints', 'Breakpoints must be an array');
        }
        
        $validated = [];
        
        foreach ($breakpoints as $key => $breakpoint) {
            $validated_breakpoint = $this->validateBreakpoint($breakpoint);
            
            if (is_wp_error($validated_breakpoint)) {
                return $validated_breakpoint;
            }
            
            $validated[$key] = $validated_breakpoint;
        }
        
        return $validated;
    }
    
    /**
     * Validate a single breakpoint
     */
    private function validateBreakpoint($breakpoint) {
        $defaults = [
            'label' => '',
            'min_width' => 0,
            'max_width' => null,
            'icon' => '📱',
            'description' => ''
        ];
        
        $breakpoint = wp_parse_args($breakpoint, $defaults);
        
        // Validate required fields
        if (empty($breakpoint['label'])) {
            return new WP_Error('missing_label', 'Breakpoint label is required');
        }
        
        // Validate min_width
        if (!is_numeric($breakpoint['min_width']) || $breakpoint['min_width'] < 0) {
            return new WP_Error('invalid_min_width', 'Min width must be a positive number');
        }
        
        // Validate max_width
        if ($breakpoint['max_width'] !== null) {
            if (!is_numeric($breakpoint['max_width']) || $breakpoint['max_width'] <= $breakpoint['min_width']) {
                return new WP_Error('invalid_max_width', 'Max width must be greater than min width');
            }
        }
        
        // Sanitize data
        $breakpoint['label'] = sanitize_text_field($breakpoint['label']);
        $breakpoint['min_width'] = (int) $breakpoint['min_width'];
        $breakpoint['max_width'] = $breakpoint['max_width'] !== null ? (int) $breakpoint['max_width'] : null;
        $breakpoint['icon'] = sanitize_text_field($breakpoint['icon']);
        $breakpoint['description'] = sanitize_text_field($breakpoint['description']);
        
        return $breakpoint;
    }
    
    /**
     * Get CSS media query for a breakpoint
     */
    public function getMediaQuery($key) {
        $breakpoint = $this->getBreakpoint($key);
        
        if (!$breakpoint) {
            return null;
        }
        
        $query_parts = [];
        
        if ($breakpoint['min_width'] > 0) {
            $query_parts[] = "(min-width: {$breakpoint['min_width']}px)";
        }
        
        if ($breakpoint['max_width'] !== null) {
            $query_parts[] = "(max-width: {$breakpoint['max_width']}px)";
        }
        
        return !empty($query_parts) ? '@media ' . implode(' and ', $query_parts) : null;
    }
    
    /**
     * Get all media queries
     */
    public function getAllMediaQueries() {
        $breakpoints = $this->getBreakpoints();
        $queries = [];
        
        foreach ($breakpoints as $key => $breakpoint) {
            $query = $this->getMediaQuery($key);
            if ($query) {
                $queries[$key] = $query;
            }
        }
        
        return $queries;
    }
    
    /**
     * Get breakpoints for JavaScript/REST API
     */
    public function getBreakpointsForAPI() {
        $breakpoints = $this->getBreakpoints();
        $api_data = [];
        
        foreach ($breakpoints as $key => $breakpoint) {
            $api_data[$key] = [
                'key' => $key,
                'label' => $breakpoint['label'],
                'min_width' => $breakpoint['min_width'],
                'max_width' => $breakpoint['max_width'],
                'icon' => $breakpoint['icon'],
                'description' => $breakpoint['description'],
                'media_query' => $this->getMediaQuery($key)
            ];
        }
        
        return $api_data;
    }
    
    /**
     * Get default breakpoints
     */
    public function getDefaults() {
        return $this->default_breakpoints;
    }
}

// Global helper functions
function umbral_get_breakpoints() {
    return UmbralEditor_Breakpoints::getInstance()->getBreakpoints();
}

function umbral_get_breakpoint($key) {
    return UmbralEditor_Breakpoints::getInstance()->getBreakpoint($key);
}

function umbral_get_media_query($key) {
    return UmbralEditor_Breakpoints::getInstance()->getMediaQuery($key);
}

function umbral_get_breakpoints_for_api() {
    return UmbralEditor_Breakpoints::getInstance()->getBreakpointsForAPI();
}