<?php
/**
 * Component Compiler Utilities
 * Handles compilation of component styles and scripts with Timber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Global arrays to store compiled styles and scripts
global $umbral_component_styles, $umbral_component_scripts;
$umbral_component_styles = [];
$umbral_component_scripts = [];

// Compilation now happens inline during component render

// Hook to output styles in wp_head
add_action('wp_head', function() {
    global $umbral_component_styles;
    error_log("wp_head hook fired - styles available: " . json_encode(array_keys($umbral_component_styles ?: [])));
    
    if (empty($umbral_component_styles)) {
        error_log("No component styles to output");
        return;
    }
    
    foreach ($umbral_component_styles as $component_id => $css) {
        if (!empty($css)) {
            error_log("Outputting styles for component: {$component_id} (length: " . strlen($css) . ")");
            echo '<style id="' . esc_attr($component_id) . '-styles">' . "\n";
            echo $css;
            echo '</style>' . "\n";
        } else {
            error_log("Empty CSS for component: {$component_id}");
        }
    }
});

// Hook to output scripts in wp_footer
add_action('wp_footer', function() {
    global $umbral_component_scripts;
    foreach ($umbral_component_scripts as $component_id => $js) {
        if (!empty($js)) {
            echo '<script id="' . esc_attr($component_id) . '-js">' . "\n";
            echo $js;
            echo '</script>' . "\n";
        }
    }
});

/**
 * Compile component styles and scripts and add them inline to head/footer
 * 
 * @param string $component_path Full path to component directory
 * @param array $context Component context data for Timber compilation
 * @return void
 */
function compileComponent($component_path, $context) {
    // Generate component ID from path
    $component_name = basename($component_path);
    $category_name = basename(dirname($component_path));
    $component_id = $category_name . '-' . $component_name;
    
    error_log("=== COMPILE COMPONENT CALLED ===");
    error_log("Component path: {$component_path}");
    error_log("Component ID: {$component_id}");
    
    // Get breakpoints for responsive CSS
    $breakpoints_manager = UmbralEditor_Breakpoints::getInstance();
    $all_breakpoints = $breakpoints_manager->getBreakpoints();
    
    // Compile styles and output inline
    $compiled_css = compileComponentStyles($component_path, $context, $all_breakpoints, $breakpoints_manager);
    if ($compiled_css) {
        echo '<style id="' . esc_attr($component_id) . '-styles">' . "\n";
        echo $compiled_css;
        echo '</style>' . "\n";
        error_log("SUCCESS: Output inline styles for {$component_id} (length: " . strlen($compiled_css) . ")");
    } else {
        error_log("FAILURE: No compiled CSS returned for {$component_id}");
    }
    
    // Compile scripts and output inline
    $compiled_js = compileComponentScripts($component_path, $context);
    if ($compiled_js) {
        echo '<script id="' . esc_attr($component_id) . '-js">' . "\n";
        echo $compiled_js;
        echo '</script>' . "\n";
        error_log("SUCCESS: Output inline scripts for {$component_id} (length: " . strlen($compiled_js) . ")");
    } else {
        error_log("FAILURE: No compiled JS returned for {$component_id}");
    }
}

/**
 * Compile all component styles with breakpoint wrapping
 * 
 * @param string $component_path Full path to component directory
 * @param array $context Component context data
 * @param array $breakpoints Available breakpoints
 * @param UmbralEditor_Breakpoints $breakpoints_manager Breakpoints manager instance
 * @return string Compiled CSS string
 */
function compileComponentStyles($component_path, $context, $breakpoints, $breakpoints_manager) {
    $compiled_css = '';
    $styles_dir = $component_path . '/styles';
    
    // Check if styles directory exists
    if (!is_dir($styles_dir)) {
        error_log("Styles directory not found: {$styles_dir}");
        return '';
    }
    
    // Get breakpoint file mapping
    $breakpoint_file_mapping = [];
    foreach ($breakpoints as $breakpoint_key => $breakpoint_data) {
        $file_suffix = strtoupper(str_replace('um_', '', $breakpoint_key));
        $breakpoint_file_mapping[$breakpoint_key] = $file_suffix . '.css';
    }
    
    try {
        // Always include LG styles as base default (no media query)
        $lg_file = $styles_dir . '/LG.css';
        if (file_exists($lg_file)) {
            $lg_css = file_get_contents($lg_file);
            $compiled_css .= "/* Base default styles (LG breakpoint) */\n";
            $compiled_css .= Timber::compile_string($lg_css, $context) . "\n\n";
            error_log("Compiled LG.css from: {$lg_file}");
        }
        
        // Process all other breakpoints
        foreach ($breakpoints as $breakpoint_key => $breakpoint_data) {
            // Skip LG since it's already included
            if ($breakpoint_key === 'um_lg') {
                continue;
            }
            
            $css_filename = $breakpoint_file_mapping[$breakpoint_key] ?? null;
            if (!$css_filename) {
                continue;
            }
            
            $css_file = $styles_dir . '/' . $css_filename;
            if (!file_exists($css_file)) {
                continue;
            }
            
            $css_content = file_get_contents($css_file);
            $compiled_content = Timber::compile_string($css_content, $context);
            
            // Get media query and wrap CSS
            $media_query = $breakpoints_manager->getMediaQuery($breakpoint_key);
            
            if ($media_query) {
                $compiled_css .= "/* {$breakpoint_data['label']} breakpoint */\n";
                $compiled_css .= "{$media_query} {\n";
                $compiled_css .= $compiled_content;
                $compiled_css .= "\n}\n\n";
            } else {
                $compiled_css .= "/* {$breakpoint_data['label']} breakpoint (no media query) */\n";
                $compiled_css .= $compiled_content . "\n\n";
            }
            
            error_log("Compiled {$css_filename} from: {$css_file}");
        }
        
    } catch (Exception $e) {
        error_log("Error compiling styles from {$component_path}: " . $e->getMessage());
    }
    
    return $compiled_css;
}

/**
 * Legacy function - kept for backward compatibility
 * @deprecated Use compile_and_enqueue_component_styles instead
 */
function compile_component_styles($category_name, $component_name, $context, $breakpoints, $breakpoints_manager) {
    $compiled_css = '';
    
    // Get breakpoint file mapping dynamically from breakpoints API
    $breakpoint_file_mapping = [];
    foreach ($breakpoints as $breakpoint_key => $breakpoint_data) {
        // Convert breakpoint key to CSS filename (e.g., 'um_xs' => 'XS.css')
        $file_suffix = strtoupper(str_replace('um_', '', $breakpoint_key));
        $breakpoint_file_mapping[$breakpoint_key] = $file_suffix . '.css';
    }
    
    try {
        // First, always include LG styles as the base default (no media query)
        $lg_css = get_component_style_file($category_name, $component_name, 'LG.css');
        if ($lg_css) {
            $compiled_css .= "/* Base default styles (LG breakpoint) */\n";
            $compiled_css .= Timber::compile_string($lg_css, $context) . "\n\n";
        }
        
        // Process all other breakpoints using the actual breakpoints API
        foreach ($breakpoints as $breakpoint_key => $breakpoint_data) {
            // Skip LG since it's already included as base default
            if ($breakpoint_key === 'um_lg' || !isset($breakpoint_file_mapping[$breakpoint_key])) {
                continue;
            }
            
            $css_file = $breakpoint_file_mapping[$breakpoint_key];
            $css_content = get_component_style_file($category_name, $component_name, $css_file);
            
            if (!$css_content) {
                continue;
            }
            
            // Compile CSS content with Timber context
            $compiled_content = Timber::compile_string($css_content, $context);
            
            // Get media query from breakpoints manager API
            $media_query = $breakpoints_manager->getMediaQuery($breakpoint_key);
            
            if ($media_query) {
                // Wrap CSS in media query from API
                $compiled_css .= "/* {$breakpoint_data['label']} breakpoint */\n";
                $compiled_css .= "$media_query {\n";
                $compiled_css .= $compiled_content;
                $compiled_css .= "\n}\n\n";
            } else {
                // If no media query returned, include as base styles (shouldn't happen with LG approach)
                $compiled_css .= "/* {$breakpoint_data['label']} breakpoint (no media query) */\n";
                $compiled_css .= $compiled_content . "\n\n";
            }
        }
        
    } catch (Exception $e) {
        error_log("Umbral Editor: Error compiling styles for {$category_name}/{$component_name}: " . $e->getMessage());
        $compiled_css = "/* Error compiling styles */";
    }
    
    return $compiled_css;
}

/**
 * Compile all component scripts
 * 
 * @param string $component_path Full path to component directory
 * @param array $context Component context data
 * @return string Compiled JavaScript string
 */
function compileComponentScripts($component_path, $context) {
    $compiled_js = '';
    $scripts_dir = $component_path . '/scripts';
    
    // Check if scripts directory exists
    if (!is_dir($scripts_dir)) {
        error_log("Scripts directory not found: {$scripts_dir}");
        return '';
    }
    
    try {
        // Look for example.js file
        $js_file = $scripts_dir . '/example.js';
        if (file_exists($js_file)) {
            $js_content = file_get_contents($js_file);
            $compiled_js = Timber::compile_string($js_content, $context);
            error_log("Compiled example.js from: {$js_file}");
        } else {
            error_log("No example.js found in: {$scripts_dir}");
        }
        
    } catch (Exception $e) {
        error_log("Error compiling scripts from {$component_path}: " . $e->getMessage());
    }
    
    return $compiled_js;
}

/**
 * Legacy function - kept for backward compatibility
 * @deprecated Use compile_and_enqueue_component_scripts instead
 */
function compile_component_scripts($category_name, $component_name, $context) {
    $compiled_js = '';
    
    try {
        $js_content = get_component_script_file($category_name, $component_name, 'example.js');
        
        if ($js_content) {
            $compiled_js = Timber::compile_string($js_content, $context);
        }
        
    } catch (Exception $e) {
        error_log("Umbral Editor: Error compiling scripts for {$category_name}/{$component_name}: " . $e->getMessage());
        $compiled_js = "/* Error compiling scripts */";
    }
    
    return $compiled_js;
}

/**
 * Get component style file content
 * Uses the same priority system as component registration
 * 
 * @param string $category_name Component category
 * @param string $component_name Component name  
 * @param string $filename CSS filename
 * @return string|false File content or false if not found
 */
function get_component_style_file($category_name, $component_name, $filename) {
    // Use the same priority system as the file manager
    $possible_paths = [
        // Child theme (highest priority)
        get_stylesheet_directory() . "/umbral/editor/components/{$category_name}/{$component_name}/styles/{$filename}",
        // Parent theme
        get_template_directory() . "/umbral/editor/components/{$category_name}/{$component_name}/styles/{$filename}",
        // Uploads directory
        wp_upload_dir()['basedir'] . "/umbral/editor/components/{$category_name}/{$component_name}/styles/{$filename}",
        // Plugin directory (fallback)
        plugin_dir_path(__FILE__) . "[create-files]/editor/components/{$category_name}/{$component_name}/styles/{$filename}"
    ];
    
    // Always log compilation attempts
    error_log("=== UMBRAL COMPILER DEBUG ===");
    error_log("Looking for style {$filename} for {$category_name}/{$component_name}");
    error_log("Child theme dir: " . get_stylesheet_directory());
    error_log("Template dir: " . get_template_directory());
    
    foreach ($possible_paths as $i => $path) {
        $exists = file_exists($path);
        error_log("Path " . ($i + 1) . ": {$path} " . ($exists ? '✓ FOUND' : '✗ not found'));
        if ($exists) {
            error_log("SUCCESS: Using style file from: {$path}");
            $content = file_get_contents($path);
            error_log("File content length: " . strlen($content) . " bytes");
            return $content;
        }
    }
    
    error_log("FAILURE: Style file {$filename} not found for {$category_name}/{$component_name}");
    return false;
}

/**
 * Get component script file content
 * Uses the same priority system as component registration
 * 
 * @param string $category_name Component category
 * @param string $component_name Component name
 * @param string $filename JS filename  
 * @return string|false File content or false if not found
 */
function get_component_script_file($category_name, $component_name, $filename) {
    // Use the same priority system as the file manager
    $possible_paths = [
        // Child theme (highest priority)
        get_stylesheet_directory() . "/umbral/editor/components/{$category_name}/{$component_name}/scripts/{$filename}",
        // Parent theme
        get_template_directory() . "/umbral/editor/components/{$category_name}/{$component_name}/scripts/{$filename}",
        // Uploads directory
        wp_upload_dir()['basedir'] . "/umbral/editor/components/{$category_name}/{$component_name}/scripts/{$filename}",
        // Plugin directory (fallback)
        plugin_dir_path(__FILE__) . "[create-files]/editor/components/{$category_name}/{$component_name}/scripts/{$filename}"
    ];
    
    error_log("Umbral Editor: Looking for script {$filename} for {$category_name}/{$component_name}:");
    foreach ($possible_paths as $path) {
        error_log("  - {$path} " . (file_exists($path) ? '✓ FOUND' : '✗ not found'));
        if (file_exists($path)) {
            error_log("Umbral Editor: Using script file from: {$path}");
            return file_get_contents($path);
        }
    }
    
    error_log("Umbral Editor: Script file {$filename} not found for {$category_name}/{$component_name}");
    return false;
}

/**
 * Get component style file content from specific component directory
 * 
 * @param string $component_dir Full path to component directory
 * @param string $filename CSS filename
 * @return string|false File content or false if not found
 */
function get_component_style_file_from_path($component_dir, $filename) {
    $style_file = $component_dir . '/styles/' . $filename;
    
    error_log("Looking for style file: {$style_file}");
    
    if (file_exists($style_file)) {
        error_log("SUCCESS: Found style file: {$style_file}");
        return file_get_contents($style_file);
    }
    
    error_log("FAILURE: Style file not found: {$style_file}");
    return false;
}

/**
 * Get component script file content from specific component directory
 * 
 * @param string $component_dir Full path to component directory
 * @param string $filename JS filename
 * @return string|false File content or false if not found
 */
function get_component_script_file_from_path($component_dir, $filename) {
    $script_file = $component_dir . '/scripts/' . $filename;
    
    error_log("Looking for script file: {$script_file}");
    
    if (file_exists($script_file)) {
        error_log("SUCCESS: Found script file: {$script_file}");
        return file_get_contents($script_file);
    }
    
    error_log("FAILURE: Script file not found: {$script_file}");
    return false;
}