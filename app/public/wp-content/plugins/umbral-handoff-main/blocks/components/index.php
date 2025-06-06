<?php
/**
 * Render callback for the Umbral Editor Components Block
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the block content.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!function_exists('umbral_get_active_directory')) {
    /**
     * Get the active umbral directory based on priority
     */
    function umbral_get_active_directory() {
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
}

if (!function_exists('umbral_render_component')) {
    /**
     * Render individual component
     */
    function umbral_render_component($component_data, $context, $breakpoints) {
        $active_dir = umbral_get_active_directory();
        if (!$active_dir) {
            return '<p>No Umbral directory found</p>';
        }
        
        // Debug: Log the full component data structure
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('umbral_render_component received: ' . print_r($component_data, true));
        }
        
        // Check if component_data is an array
        if (!is_array($component_data)) {
            return '<p>Invalid component data - Expected array, got: ' . esc_html(gettype($component_data)) . '</p>';
        }
        
        $category = $component_data['category'] ?? '';
        $component = $component_data['component'] ?? '';
        $component_fields = $component_data['fields'] ?? [];
        
        if (!$category || !$component) {
            return '<p>Invalid component data - Category: "' . esc_html($category) . '", Component: "' . esc_html($component) . '"<br>Available keys: ' . esc_html(implode(', ', array_keys($component_data))) . '</p>';
        }
        
        $render_file = $active_dir . "/editor/components/{$category}/{$component}/render.php";
        
        if (!file_exists($render_file)) {
            return "<p>Component render file not found: {$category}/{$component} at {$render_file}</p>";
        }
        
        // Make variables available to the render file
        $component_data = $component_fields; // Pass the fields data as component_data
        
        // Include and execute the render file
        ob_start();
        include $render_file;
        $rendered = ob_get_clean();
        
        if (empty($rendered)) {
            return "<p>Component rendered but returned empty content: {$category}/{$component}</p>";
        }
        
        return $rendered;
    }
}

// Prepare Timber context
$context = Timber::context();

// Check for component parameter to render a specific component
$component_param = isset($_GET['component']) ? sanitize_text_field($_GET['component']) : null;

if ($component_param) {
    // Parse component parameter in format "category-component"
    if (strpos($component_param, '-') !== false) {
        $parts = explode('-', $component_param, 2);
        $category = $parts[0];
        $component_name = $parts[1];
        
        // Check if component exists
        $active_dir = umbral_get_active_directory();
        if ($active_dir) {
            $render_file = $active_dir . "/editor/components/{$category}/{$component_name}/render.php";
            
            if (file_exists($render_file)) {
                // Debug: Log single component render
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Umbral Block Render - Single component mode: ' . $component_param);
                    error_log('Umbral Block Render - Component file: ' . $render_file);
                }
                
                // Get the real component data from source_id
                $source_id = isset($_GET['source_id']) ? intval($_GET['source_id']) : null;
                $post_id = $source_id ? $source_id : $context['post']->ID;
                $components_raw = get_post_meta($post_id, 'components', true);
                
                // Decode JSON string to array if needed
                if (is_string($components_raw)) {
                    $components = json_decode($components_raw, true);
                } else {
                    $components = $components_raw;
                }
                
                // Ensure components is an array
                if (!is_array($components)) {
                    $components = [];
                }
                
                // Find the specific component data that matches category-component
                $single_component_data = null;
                foreach ($components as $component_data) {
                    if (is_array($component_data) && 
                        isset($component_data['category']) && 
                        isset($component_data['component']) &&
                        $component_data['category'] === $category && 
                        $component_data['component'] === $component_name) {
                        $single_component_data = $component_data;
                        break;
                    }
                }
                
                // If no matching component found, create mock with empty fields
                if (!$single_component_data) {
                    $single_component_data = [
                        'category' => $category,
                        'component' => $component_name,
                        'fields' => [] // Empty fields - component should use defaults
                    ];
                }
                
                // Get breakpoints for responsive styles
                $breakpoints_manager = UmbralEditor_Breakpoints::getInstance();
                $breakpoints = $breakpoints_manager->getBreakpoints();
                
                // Add custom context data
                $context['block_id'] = 'umbral-components-' . uniqid();
                $context['breakpoints'] = $breakpoints;
                
                $wrapper_attributes = get_block_wrapper_attributes([
                    'class' => 'umbral-components-block umbral-single-component',
                    'id' => $context['block_id'],
                    'data-component' => $component_param
                ]);
                
                // Render only the single component
                $output = umbral_render_component($single_component_data, $context, $breakpoints);
                
                ?>
                <div <?php echo $wrapper_attributes; ?>>
                    <?php echo $output; ?>
                </div>
                <?php
                return; // Exit early - don't process normal components
            } else {
                // Component file not found
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Umbral Block Render - Component file not found: ' . $render_file);
                }
                
                $wrapper_attributes = get_block_wrapper_attributes([
                    'class' => 'umbral-components-block umbral-error',
                ]);
                
                ?>
                <div <?php echo $wrapper_attributes; ?>>
                    <p style="text-align: center; padding: 2rem; color: #d63638; background: #fcf0f1; border: 1px solid #d63638; border-radius: 4px;">
                        Component not found: <?php echo esc_html($component_param); ?><br>
                        <small>Looking for: <?php echo esc_html("{$category}/{$component_name}"); ?></small>
                    </p>
                </div>
                <?php
                return; // Exit early
            }
        }
    } else {
        // Invalid component parameter format
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Umbral Block Render - Invalid component parameter format: ' . $component_param);
        }
        
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => 'umbral-components-block umbral-error',
        ]);
        
        ?>
        <div <?php echo $wrapper_attributes; ?>>
            <p style="text-align: center; padding: 2rem; color: #d63638; background: #fcf0f1; border: 1px solid #d63638; border-radius: 4px;">
                Invalid component format: <?php echo esc_html($component_param); ?><br>
                <small>Expected format: category-component (e.g., Heroes-hero-1)</small>
            </p>
        </div>
        <?php
        return; // Exit early
    }
}

// Normal component processing continues here...
// Get components from the page's CMB2 field
// Check for source_id parameter first, fallback to current post ID
$source_id = isset($_GET['source_id']) ? intval($_GET['source_id']) : null;
$post_id = $source_id ? $source_id : $context['post']->ID;
$components_raw = get_post_meta($post_id, 'components', true);

// Debug: Log source information
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Umbral Block Render - Source ID param: ' . ($source_id ?: 'none'));
    error_log('Umbral Block Render - Using Post ID: ' . $post_id);
    error_log('Umbral Block Render - Current Post ID: ' . $context['post']->ID);
}

// Decode JSON string to array if needed
if (is_string($components_raw)) {
    $components = json_decode($components_raw, true);
} else {
    $components = $components_raw;
}

// Ensure components is an array
if (!is_array($components)) {
    $components = [];
}

// Fallback logic: If no components found on current post, check block's source_id attribute
if (empty($components) && isset($attributes['source_id']) && $attributes['source_id'] && $attributes['source_id'] !== $post_id) {
    $fallback_post_id = intval($attributes['source_id']);
    $fallback_components_raw = get_post_meta($fallback_post_id, 'components', true);
    
    // Debug: Log fallback attempt
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Umbral Block Render - No components found on post ' . $post_id . ', trying fallback to block source_id: ' . $fallback_post_id);
    }
    
    // Decode fallback components
    if (is_string($fallback_components_raw)) {
        $fallback_components = json_decode($fallback_components_raw, true);
    } else {
        $fallback_components = $fallback_components_raw;
    }
    
    // Use fallback components if they exist
    if (is_array($fallback_components) && !empty($fallback_components)) {
        $components = $fallback_components;
        $post_id = $fallback_post_id; // Update post_id for debug output
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Umbral Block Render - Using fallback components from post ' . $fallback_post_id . ' (' . count($components) . ' components)');
        }
    }
}

// Debug: Log what we're getting
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Umbral Debug - Post ID: ' . $post_id);
    error_log('Umbral Debug - Components decoded: ' . print_r($components, true));
    error_log('Umbral Debug - Components count: ' . (is_array($components) ? count($components) : 'not array'));
    error_log('Umbral Debug - Components empty: ' . (empty($components) ? 'yes' : 'no'));
}

// Generate debug component test links (isolated function)
if (!function_exists('umbral_get_debug_component_links')) {
    function umbral_get_debug_component_links() {
    $debug_available = [];
    $debug_dir = umbral_get_active_directory();
    if ($debug_dir) {
        $debug_components_dir = $debug_dir . '/editor/components';
        if (is_dir($debug_components_dir)) {
            $debug_categories = glob($debug_components_dir . '/*', GLOB_ONLYDIR);
            foreach ($debug_categories as $debug_category_path) {
                $debug_category_name = basename($debug_category_path);
                $debug_component_dirs = glob($debug_category_path . '/*', GLOB_ONLYDIR);
                foreach ($debug_component_dirs as $debug_component_path) {
                    $debug_component_name = basename($debug_component_path);
                    $debug_render_file = $debug_component_path . '/render.php';
                    if (file_exists($debug_render_file)) {
                        $debug_component_id = $debug_category_name . '-' . $debug_component_name;
                        $debug_available[$debug_category_name][] = [
                            'name' => $debug_component_name,
                            'id' => $debug_component_id
                        ];
                    }
                }
            }
        }
    }

    $links_html = '';
    if (!empty($debug_available)) {
        $debug_current_url = strtok($_SERVER['REQUEST_URI'], '?');
        
        foreach ($debug_available as $debug_category => $debug_components) {
            $links_html .= '<div style="margin-bottom: 1rem;">';
            $links_html .= '<strong style="color: #333; display: block; margin-bottom: 0.5rem;">' . esc_html($debug_category) . ':</strong>';
            $links_html .= '<div style="margin-left: 1rem;">';
            
            foreach ($debug_components as $debug_component) {
                $debug_test_url = $debug_current_url . '?component=' . urlencode($debug_component['id']);
                $links_html .= '<a href="' . esc_url($debug_test_url) . '" style="color: #0073aa; text-decoration: none; margin-right: 1rem; display: inline-block; margin-bottom: 0.25rem;" target="_blank">' . esc_html($debug_component['name']) . '</a>';
            }
            
            $links_html .= '</div></div>';
        }
    } else {
        $links_html = '<p style="color: #666; font-style: italic;">No components found in umbral directory.</p>';
    }
    
    return $links_html;
    }
}

$component_links_html = umbral_get_debug_component_links();

// Generate editor URL function
if (!function_exists('umbral_get_editor_url')) {
    function umbral_get_editor_url($source_id, $url_params) {
        $params = [
            'umbral' => 'editor',
            'source_id' => $source_id ?: get_the_ID() ?: 0
        ];
        
        // Add source_url - use current page URL without query params
        $current_url = home_url($_SERVER['REQUEST_URI']);
        $clean_url = strtok($current_url, '?'); // Remove existing query params
        $params['source_url'] = $clean_url;
        
        // Add mode-specific parameters if they exist
        if (isset($url_params['mode'])) {
            $params['mode'] = sanitize_text_field($url_params['mode']);
        }
        if (isset($url_params['post_type'])) {
            $params['post_type'] = sanitize_text_field($url_params['post_type']);
        }
        if (isset($url_params['preview_post_id'])) {
            $params['preview_post_id'] = intval($url_params['preview_post_id']);
        }
        if (isset($url_params['core_page'])) {
            $params['core_page'] = sanitize_text_field($url_params['core_page']);
        }
        
        return home_url('/?' . http_build_query($params));
    }
}

// Only show debugger for logged-in admins
$show_debugger = is_user_logged_in() && current_user_can('manage_options');

// Prepare debug data for web component
$debug_data = [];
if ($show_debugger) {
    $debug_data = [
        'currentPostId' => $context['post']->ID,
        'sourceIdParam' => $source_id ?: 'none',
        'componentParam' => $component_param ?: 'none',
        'componentsFromPostId' => $post_id,
        'blockSourceIdAttribute' => isset($attributes['source_id']) ? $attributes['source_id'] : 'none',
        'rawDataType' => gettype($components_raw),
        'decodedComponentsCount' => is_array($components) ? count($components) : 'Not an array',
        'componentsEmptyCheck' => empty($components) ? 'YES (empty)' : 'NO (has data)',
        'blockAttributes' => $attributes,
        'urlParameters' => $_GET,
        'rawComponentsData' => $components_raw,
        'decodedComponentsData' => $components,
        'timberContext' => $context,
        'componentTestLinks' => $component_links_html,
        'editorUrl' => umbral_get_editor_url($source_id ?: $context['post']->ID, $_GET)
    ];
}

// Create web component for debugging (only for admins)
$debug_output = '';
if ($show_debugger) {
    $debug_output = '<!-- Load Ace Editor for code display -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.42.0/ace.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.42.0/mode-json.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.42.0/theme-monokai.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<umbral-debugger 
    debug-data="' . esc_attr(base64_encode(json_encode($debug_data))) . '"
></umbral-debugger>';
} else {
    $debug_output = '';
}

// Get breakpoints for responsive styles
$breakpoints_manager = UmbralEditor_Breakpoints::getInstance();
$breakpoints = $breakpoints_manager->getBreakpoints();

// Add custom context data
$context['block_id'] = 'umbral-components-' . uniqid();
$context['breakpoints'] = $breakpoints;

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'umbral-components-block',
    'id' => $context['block_id']
]);

$output = '';

// Add debug output first if enabled
$output .= $debug_output;

if (empty($components)) {
    $output .= '<p style="text-align: center; padding: 2rem; color: #666;">No components selected. Add components using the Umbral Editor.</p>';
} else {
    foreach ($components as $index => $component_data) {
        // Debug: Log each component's structure
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Umbral Block Render - Component ' . $index . ' data: ' . print_r($component_data, true));
        }
        
        // Ensure component_data is properly structured
        if (is_array($component_data)) {
            $output .= umbral_render_component($component_data, $context, $breakpoints);
        } else {
            $output .= '<p>Invalid component data at index ' . $index . ': ' . esc_html(gettype($component_data)) . '</p>';
        }
    }
}

?>
<div <?php echo $wrapper_attributes; ?>>
    <?php echo $output; ?>
</div>

<?php if ($show_debugger): ?>
<style>
umbral-debugger {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 999999;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.umbral-debug-toggle {
    background: #1e1e1e;
    color: #fff;
    border: none;
    border-radius: 8px 8px 0 0;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    transition: all 0.2s ease;
    min-width: 160px;
}

.umbral-toggle-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 8px;
}

.umbral-toggle-icon {
    font-size: 14px;
}

.umbral-toggle-text {
    flex: 1;
    text-align: center;
}

.umbral-quick-editor-btn {
    background: #0073aa;
    color: white;
    text-decoration: none;
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 11px;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
}

.umbral-quick-editor-btn:hover {
    background: #005177;
    color: white;
    text-decoration: none;
}

.umbral-debug-toggle:hover {
    background: #333;
    transform: translateY(-2px);
}

.umbral-debug-panel {
    background: #1e1e1e;
    border-radius: 8px 8px 0 0;
    width: 450px;
    max-height: 500px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.umbral-debug-panel.open {
    display: flex;
}

.umbral-debug-header {
    background: #2d2d2d;
    color: #fff;
    padding: 12px 16px;
    border-bottom: 1px solid #404040;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 600;
}

.umbral-debug-close {
    background: none;
    border: none;
    color: #888;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    font-size: 16px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.umbral-debug-close:hover {
    background: #404040;
    color: #fff;
}

.umbral-debug-content {
    overflow-y: auto;
    max-height: 400px;
    font-size: 12px;
}

.umbral-debug-section {
    border-bottom: 1px solid #404040;
}

.umbral-debug-section:last-child {
    border-bottom: none;
}

.umbral-debug-section-header {
    background: #2d2d2d;
    color: #fff;
    padding: 8px 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background-color 0.2s ease;
}

.umbral-debug-section-header:hover {
    background: #404040;
}

.umbral-debug-section-content {
    background: #1e1e1e;
    color: #e0e0e0;
    padding: 12px 16px;
    display: none;
    line-height: 1.4;
}

.umbral-debug-section-content.open {
    display: block;
}

.umbral-debug-info {
    margin-bottom: 8px;
}

.umbral-debug-info:last-child {
    margin-bottom: 0;
}

.umbral-debug-label {
    color: #888;
    font-weight: 600;
}

.umbral-debug-value {
    color: #4CAF50;
    font-family: "Monaco", "Menlo", monospace;
}

.umbral-debug-arrow {
    transition: transform 0.2s ease;
}

.umbral-debug-section.open .umbral-debug-arrow {
    transform: rotate(90deg);
}

.umbral-test-links {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.umbral-test-category {
    margin-bottom: 12px;
}

.umbral-test-category:last-child {
    margin-bottom: 0;
}

.umbral-test-category-title {
    color: #4CAF50;
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

.umbral-test-links-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-left: 12px;
}

.umbral-test-link {
    background: #2196F3;
    color: #fff;
    text-decoration: none;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    transition: background-color 0.2s ease;
    white-space: nowrap;
}

.umbral-test-link:hover {
    background: #1976D2;
    color: #fff;
    text-decoration: none;
}
</style>

<script>
class UmbralDebugger extends HTMLElement {
    constructor() {
        super();
        this.isOpen = false;
        this.openSections = new Set();
    }
    
    connectedCallback() {
        try {
            const debugDataAttr = this.getAttribute('debug-data');
            if (debugDataAttr) {
                this.debugData = JSON.parse(atob(debugDataAttr));
            } else {
                this.debugData = {};
            }
        } catch (e) {
            console.error('Umbral Debugger: Failed to parse debug data', e);
            this.debugData = {};
        }
        
        this.render();
        this.attachEventListeners();
    }
    
    render() {
        this.innerHTML = `
            <div class="umbral-debug-toggle" id="umbral-debug-toggle">
                <div class="umbral-toggle-content">
                    <span class="umbral-toggle-icon">⚙️</span>
                    <span class="umbral-toggle-text">Umbral Console</span>
                    <a href="${this.debugData.editorUrl || '#'}" target="_blank" class="umbral-quick-editor-btn" id="umbral-quick-editor" title="Open Umbral Editor">
                        ✏️
                    </a>
                </div>
            </div>
            <div class="umbral-debug-panel" id="umbral-debug-panel">
                <div class="umbral-debug-header">
                    <span>⚙️ Umbral Console</span>
                    <button class="umbral-debug-close" id="umbral-debug-close">×</button>
                </div>
                <div class="umbral-debug-content">
                    ${this.renderSections()}
                </div>
            </div>
        `;
    }
    
    renderSections() {
        const sections = [
            {
                id: 'editor-link',
                title: '✏️ Open Editor',
                content: this.renderEditorLink()
            },
            {
                id: 'basic-info',
                title: 'Basic Info',
                content: this.renderBasicInfo()
            },
            {
                id: 'test-components',
                title: '🧪 Test Components',
                content: this.renderTestComponents()
            },
            {
                id: 'timber-context',
                title: 'Timber Context',
                content: this.renderCodeData(this.debugData.timberContext, 'json')
            },
            {
                id: 'block-attributes',
                title: 'Block Attributes',
                content: this.renderCodeData(this.debugData.blockAttributes, 'json')
            },
            {
                id: 'url-parameters',
                title: 'URL Parameters',
                content: this.renderCodeData(this.debugData.urlParameters, 'json')
            },
            {
                id: 'raw-components',
                title: 'Raw Components Data',
                content: this.renderCodeData(this.debugData.rawComponentsData, 'json')
            },
            {
                id: 'decoded-components',
                title: 'Decoded Components Data',
                content: this.renderCodeData(this.debugData.decodedComponentsData, 'json')
            }
        ];
        
        return sections.map(section => `
            <div class="umbral-debug-section ${this.openSections.has(section.id) ? 'open' : ''}" data-section="${section.id}">
                <div class="umbral-debug-section-header">
                    <span>${section.title}</span>
                    <span class="umbral-debug-arrow">▶</span>
                </div>
                <div class="umbral-debug-section-content ${this.openSections.has(section.id) ? 'open' : ''}">
                    ${section.content}
                </div>
            </div>
        `).join('');
    }
    
    renderBasicInfo() {
        const data = this.debugData;
        return `
            <div class="umbral-debug-info">
                <span class="umbral-debug-label">Current Post ID:</span> 
                <span class="umbral-debug-value">${data.currentPostId || 'N/A'}</span>
            </div>
            <div class="umbral-debug-info">
                <span class="umbral-debug-label">Source ID (URL param):</span> 
                <span class="umbral-debug-value">${data.sourceIdParam || 'none'}</span>
            </div>
            <div class="umbral-debug-info">
                <span class="umbral-debug-label">Component Param:</span> 
                <span class="umbral-debug-value">${data.componentParam || 'none'}</span>
            </div>
            <div class="umbral-debug-info">
                <span class="umbral-debug-label">Components From Post ID:</span> 
                <span class="umbral-debug-value">${data.componentsFromPostId || 'N/A'}</span>
            </div>
            <div class="umbral-debug-info">
                <span class="umbral-debug-label">Block Source ID Attribute:</span> 
                <span class="umbral-debug-value">${data.blockSourceIdAttribute || 'none'}</span>
            </div>
            <div class="umbral-debug-info">
                <span class="umbral-debug-label">Raw Data Type:</span> 
                <span class="umbral-debug-value">${data.rawDataType || 'N/A'}</span>
            </div>
            <div class="umbral-debug-info">
                <span class="umbral-debug-label">Decoded Components Count:</span> 
                <span class="umbral-debug-value">${data.decodedComponentsCount || 'N/A'}</span>
            </div>
            <div class="umbral-debug-info">
                <span class="umbral-debug-label">Components Empty Check:</span> 
                <span class="umbral-debug-value">${data.componentsEmptyCheck || 'N/A'}</span>
            </div>
        `;
    }
    
    renderEditorLink() {
        const editorUrl = this.debugData.editorUrl;
        return `
            <div style="text-align: center; padding: 16px;">
                <a href="${editorUrl || '#'}" target="_blank" style="
                    display: inline-block;
                    background: #0073aa;
                    color: white;
                    text-decoration: none;
                    padding: 12px 24px;
                    border-radius: 6px;
                    font-weight: 600;
                    font-size: 14px;
                    transition: background-color 0.2s ease;
                " onmouseover="this.style.backgroundColor='#005177'" onmouseout="this.style.backgroundColor='#0073aa'">
                    🎨 Open Umbral Editor
                </a>
                <p style="margin: 12px 0 0 0; font-size: 11px; color: #888;">
                    Opens in a new tab
                </p>
            </div>
        `;
    }
    
    renderTestComponents() {
        return this.debugData.componentTestLinks || '<p>No component test links available</p>';
    }
    
    renderCodeData(data, mode = 'json') {
        if (!data) return '<p>No data available</p>';
        
        const editorId = 'ace-editor-' + Math.random().toString(36).substr(2, 9);
        
        // Return the editor container
        return `<div id="${editorId}" class="umbral-ace-editor" style="height: 300px; width: 100%; border: 1px solid #404040; border-radius: 4px;"></div>`;
    }
    
    initializeAceEditors() {
        // Wait a bit for the DOM to settle, then initialize all ace editors
        setTimeout(() => {
            this.querySelectorAll('.umbral-ace-editor').forEach(editorDiv => {
                if (editorDiv.aceEditor) return; // Already initialized
                
                const editor = ace.edit(editorDiv.id);
                editor.setTheme('ace/theme/monokai');
                editor.session.setMode('ace/mode/json');
                editor.setReadOnly(true);
                editor.setShowPrintMargin(false);
                editor.renderer.setShowGutter(true);
                editor.setFontSize(11);
                
                // Get the data for this editor from the section
                const section = editorDiv.closest('.umbral-debug-section');
                const sectionId = section?.dataset?.section;
                
                let data = null;
                switch(sectionId) {
                    case 'timber-context':
                        data = this.debugData.timberContext;
                        break;
                    case 'block-attributes':
                        data = this.debugData.blockAttributes;
                        break;
                    case 'url-parameters':
                        data = this.debugData.urlParameters;
                        break;
                    case 'raw-components':
                        data = this.debugData.rawComponentsData;
                        break;
                    case 'decoded-components':
                        data = this.debugData.decodedComponentsData;
                        break;
                }
                
                if (data) {
                    let formatted;
                    if (typeof data === 'object') {
                        formatted = JSON.stringify(data, null, 2);
                    } else {
                        formatted = String(data);
                    }
                    editor.setValue(formatted, -1);
                }
                
                // Store reference to prevent re-initialization
                editorDiv.aceEditor = editor;
            });
        }, 100);
    }
    
    attachEventListeners() {
        const toggle = this.querySelector('#umbral-debug-toggle');
        const close = this.querySelector('#umbral-debug-close');
        const panel = this.querySelector('#umbral-debug-panel');
        const quickEditor = this.querySelector('#umbral-quick-editor');
        
        toggle?.addEventListener('click', (e) => {
            // Don't toggle panel if clicking the quick editor button
            if (e.target.closest('.umbral-quick-editor-btn')) {
                e.stopPropagation();
                return;
            }
            this.togglePanel();
        });
        close?.addEventListener('click', () => this.closePanel());
        
        // Quick editor button is handled by its href, no additional JS needed
        
        // Section toggles
        this.querySelectorAll('.umbral-debug-section-header').forEach(header => {
            header.addEventListener('click', () => {
                const section = header.closest('.umbral-debug-section');
                const sectionId = section.dataset.section;
                this.toggleSection(sectionId);
            });
        });
    }
    
    togglePanel() {
        this.isOpen = !this.isOpen;
        const panel = this.querySelector('#umbral-debug-panel');
        const toggle = this.querySelector('#umbral-debug-toggle');
        
        if (this.isOpen) {
            panel.classList.add('open');
            toggle.style.display = 'none';
        } else {
            panel.classList.remove('open');
            toggle.style.display = 'flex';
        }
    }
    
    closePanel() {
        this.isOpen = false;
        const panel = this.querySelector('#umbral-debug-panel');
        const toggle = this.querySelector('#umbral-debug-toggle');
        
        panel.classList.remove('open');
        toggle.style.display = 'flex';
    }
    
    toggleSection(sectionId) {
        const section = this.querySelector(`[data-section="${sectionId}"]`);
        const content = section.querySelector('.umbral-debug-section-content');
        
        if (this.openSections.has(sectionId)) {
            this.openSections.delete(sectionId);
            section.classList.remove('open');
            content.classList.remove('open');
        } else {
            this.openSections.add(sectionId);
            section.classList.add('open');
            content.classList.add('open');
            
            // Initialize ace editors when section is opened
            this.initializeAceEditors();
        }
    }
}

customElements.define('umbral-debugger', UmbralDebugger);
</script>
<?php endif; ?>