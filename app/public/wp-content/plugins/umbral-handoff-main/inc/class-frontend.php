<?php
/**
 * Frontend display handler for Umbral Editor
 */

class UmbralEditor_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_filter('the_content', [$this, 'maybeReplaceContent'], 10);
        add_action('wp_head', [$this, 'addFrontendStyles']);
        add_action('wp_footer', [$this, 'maybeAddEditorToast']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueEditorAssets']);
        
        // Handle preview template
        add_action('template_redirect', [$this, 'maybeLoadPreviewTemplate']);
        
        // Register preview template hooks
        add_action('um_the_content', [$this, 'renderPreviewContent']);
    }
    
    /**
     * Maybe replace or modify content based on display mode
     */
    public function maybeReplaceContent($content) {
        // Only process on singular pages
        if (!is_singular('page') || is_admin()) {
            return $content;
        }
        
        
        $post_id = get_the_ID();
        $components = get_post_meta($post_id, 'page_components', true);
        $display_mode = get_post_meta($post_id, 'components_display_mode', true) ?: 'replace';
        
        // If no components, return original content
        if (!$components || !is_array($components) || empty($components)) {
            return $content;
        }
        
        // Render components
        $components_html = UmbralEditor_Field_Renderer::render($components, [
            'echo' => false,
            'wrapper_class' => 'umbral-page-components',
            'component_class' => 'umbral-page-component'
        ]);
        
        // Apply display mode
        switch ($display_mode) {
            case 'replace':
                return $components_html;
                
            case 'prepend':
                return $components_html . $content;
                
            case 'append':
                return $content . $components_html;
                
            case 'manual':
            default:
                return $content;
        }
    }
    
    /**
     * Add basic frontend styles
     */
    public function addFrontendStyles() {
        if (!is_singular('page')) {
            return;
        }
        
        $post_id = get_the_ID();
        $components = get_post_meta($post_id, 'page_components', true);
        
        if (!$components || !is_array($components) || empty($components)) {
            return;
        }
        
        ?>
        <style>
        /* Umbral Editor Frontend Styles */
        .umbral-page-components {
            margin: 0;
        }
        
        .umbral-page-component {
            margin-bottom: 0;
        }
        
        /* Hero Components */
        .hero-banner {
            position: relative;
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            overflow: hidden;
            background: #333;
        }
        
        .hero-banner.text-left {
            text-align: left;
            justify-content: flex-start;
            padding-left: 5%;
        }
        
        .hero-banner.text-right {
            text-align: right;
            justify-content: flex-end;
            padding-right: 5%;
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        
        .hero-banner > * {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: clamp(2rem, 5vw, 4rem);
            margin: 0 0 1rem 0;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: clamp(1rem, 2.5vw, 1.5rem);
            margin: 0 0 2rem 0;
            opacity: 0.9;
            max-width: 600px;
        }
        
        .hero-button {
            display: inline-block;
            padding: 1rem 2rem;
            background: #007cba;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .hero-button:hover {
            background: #005a87;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
        }
        
        .hero-video {
            padding: 4rem 0;
            text-align: center;
            background: #f8f9fa;
        }
        
        .hero-video .hero-title {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .hero-video .hero-subtitle {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .hero-video-embed {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero-video-embed iframe {
            width: 100%;
            height: 450px;
            border-radius: 8px;
        }
        
        /* Testimonial Components */
        .testimonial-single {
            padding: 3rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            margin: 2rem auto;
        }
        
        .testimonial-quote {
            font-size: 1.25rem;
            font-style: italic;
            margin: 0 0 2rem 0;
            color: #333;
            line-height: 1.6;
        }
        
        .testimonial-quote::before {
            content: '"';
            font-size: 2rem;
            color: #ddd;
        }
        
        .testimonial-quote::after {
            content: '"';
            font-size: 2rem;
            color: #ddd;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        
        .author-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .author-name {
            font-weight: 600;
            color: #333;
            font-style: normal;
        }
        
        .author-title {
            color: #666;
            font-size: 0.9rem;
        }
        
        .testimonial-rating {
            color: #ffc107;
            font-size: 1.2rem;
            margin-top: 0.5rem;
        }
        
        .testimonial-grid {
            padding: 4rem 2rem;
            background: #f8f9fa;
        }
        
        .testimonial-grid-title {
            text-align: center;
            font-size: 2.5rem;
            margin: 0 0 3rem 0;
            color: #333;
        }
        
        .testimonial-grid-items {
            display: grid;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .testimonial-grid.columns-1 .testimonial-grid-items {
            grid-template-columns: 1fr;
        }
        
        .testimonial-grid.columns-2 .testimonial-grid-items {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
        
        .testimonial-grid.columns-3 .testimonial-grid-items {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        
        .testimonial-grid.columns-4 .testimonial-grid-items {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
        
        .testimonial-item {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .testimonial-item .testimonial-quote {
            font-size: 1rem;
            margin-bottom: 1rem;
            text-align: left;
        }
        
        .testimonial-item .testimonial-author {
            justify-content: flex-start;
            text-align: left;
        }
        
        /* Content Components */
        .text-block {
            padding: 3rem 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .text-block.text-center {
            text-align: center;
        }
        
        .text-block.text-right {
            text-align: right;
        }
        
        .text-block-title {
            font-size: 2.5rem;
            margin: 0 0 1.5rem 0;
            color: #333;
            line-height: 1.3;
        }
        
        .text-block-content {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #555;
        }
        
        .text-block-content h1,
        .text-block-content h2,
        .text-block-content h3,
        .text-block-content h4,
        .text-block-content h5,
        .text-block-content h6 {
            color: #333;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .image-text {
            display: grid;
            gap: 2rem;
            padding: 3rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }
        
        .image-text.layout-image_left {
            grid-template-columns: 1fr 2fr;
        }
        
        .image-text.layout-image_right {
            grid-template-columns: 2fr 1fr;
        }
        
        .image-text.layout-image_top {
            grid-template-columns: 1fr;
        }
        
        .image-text.image-size-small.layout-image_left {
            grid-template-columns: 1fr 3fr;
        }
        
        .image-text.image-size-small.layout-image_right {
            grid-template-columns: 3fr 1fr;
        }
        
        .image-text.image-size-large.layout-image_left {
            grid-template-columns: 2fr 1fr;
        }
        
        .image-text.image-size-large.layout-image_right {
            grid-template-columns: 1fr 2fr;
        }
        
        .image-text.layout-image_right .image-text-image {
            order: 2;
        }
        
        .image-text-image img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        
        .image-text-title {
            font-size: 2rem;
            margin: 0 0 1rem 0;
            color: #333;
        }
        
        .image-text-text {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #555;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-banner {
                min-height: 50vh;
                padding: 2rem 1rem;
            }
            
            .hero-banner.text-left,
            .hero-banner.text-right {
                text-align: center;
                justify-content: center;
                padding: 2rem 1rem;
            }
            
            .image-text.layout-image_left,
            .image-text.layout-image_right {
                grid-template-columns: 1fr;
            }
            
            .image-text.layout-image_right .image-text-image {
                order: 0;
            }
            
            .testimonial-grid-items {
                grid-template-columns: 1fr !important;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Enqueue editor assets when needed
     */
    public function enqueueEditorAssets() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Umbral Editor Debug: enqueueEditorAssets() called");
        }
        
        // Check if we should show any editor functionality (toast or full editor)
        $should_show = $this->shouldShowEditorInterface();
        
        if (!$should_show) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: shouldShowEditorInterface() returned false, not enqueuing assets");
            }
            return;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Umbral Editor Debug: Enqueuing frontend editor assets");
        }
        
        // Enqueue the frontend editor React component
        $editor_js = UMBRAL_EDITOR_URL . 'dist/js/umbral-frontend-editor.js';
        $editor_file = UMBRAL_EDITOR_DIR . 'dist/js/umbral-frontend-editor.js';
        $editor_version = file_exists($editor_file) ? filemtime($editor_file) : UMBRAL_EDITOR_VERSION;
        
        wp_enqueue_script(
            'umbral-frontend-editor',
            $editor_js,
            ['wp-element'],
            $editor_version,
            true
        );
        
        // Also enqueue the components field web component for the full editor
        $components_js = UMBRAL_EDITOR_URL . 'dist/js/umbral-components-field.js';
        $components_file = UMBRAL_EDITOR_DIR . 'dist/js/umbral-components-field.js';
        $components_version = file_exists($components_file) ? filemtime($components_file) : UMBRAL_EDITOR_VERSION;
        
        wp_enqueue_script(
            'umbral-components-field',
            $components_js,
            ['wp-element'],
            $components_version,
            true
        );
        
        // If we're in full editor mode, enqueue WordPress media library
        $is_editor_mode = isset($_GET['umbral']) && $_GET['umbral'] === 'editor';
        if ($is_editor_mode) {
            // Enqueue WordPress media library scripts and styles
            wp_enqueue_media();
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: Enqueued WordPress media library for full editor mode");
            }
        }
        
        // Get source_id from URL parameters
        $source_id = isset($_GET['source_id']) ? intval($_GET['source_id']) : get_the_ID();
        
        // Localize script with editor data
        wp_localize_script('umbral-frontend-editor', 'umbralFrontendEditor', [
            'postId' => $source_id,
            'editUrl' => $this->getEditorUrl(),
            'closeUrl' => $this->getCloseUrl(),
            'restUrl' => rest_url(),
            'restNonce' => wp_create_nonce('wp_rest'),
            'userCanEdit' => current_user_can('edit_post', $source_id),
            'isEditorMode' => isset($_GET['umbral']) && $_GET['umbral'] === 'editor'
        ]);
    }
    
    /**
     * Maybe add the editor toast for authorized users
     */
    public function maybeAddEditorToast() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Umbral Editor Debug: maybeAddEditorToast() called");
        }
        
        if (!$this->shouldShowEditorInterface()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: shouldShowEditorInterface() returned false, not adding interface");
            }
            return;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Umbral Editor Debug: Adding editor interface");
        }
        
        $is_editor_mode = isset($_GET['umbral']) && $_GET['umbral'] === 'editor';
        
        if ($is_editor_mode) {
            // Full editor interface
            $this->renderFullEditor();
        } else {
            // Just the floating toast (only if not preview mode)
            if (!isset($_GET['umbral']) || $_GET['umbral'] !== 'preview') {
                $this->renderEditorToast();
            }
        }
    }
    
    /**
     * Check if we should show the editor toast
     */
    private function shouldShowEditorToast() {
        $post_id = get_the_ID();
        $umbral_mode = isset($_GET['umbral']) ? $_GET['umbral'] : null;
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Umbral Editor Debug: shouldShowEditorToast() called");
            error_log("Umbral Editor Debug: umbral_mode: " . ($umbral_mode ?: 'none'));
            error_log("Umbral Editor Debug: is_singular('page'): " . (is_singular('page') ? 'true' : 'false'));
            error_log("Umbral Editor Debug: is_admin(): " . (is_admin() ? 'true' : 'false'));
            error_log("Umbral Editor Debug: is_user_logged_in(): " . (is_user_logged_in() ? 'true' : 'false'));
            error_log("Umbral Editor Debug: current_user_can('edit_post', {$post_id}): " . (current_user_can('edit_post', $post_id) ? 'true' : 'false'));
        }
        
        // Don't show toast in preview mode
        if ($umbral_mode === 'preview') {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: Preview mode - not showing toast");
            }
            return false;
        }
        
        // Must be a singular page
        if (!is_singular('page') || is_admin()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: Failed singular page check");
            }
            return false;
        }
        
        // User must be logged in and able to edit this post
        if (!is_user_logged_in() || !current_user_can('edit_post', $post_id)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: Failed user permission check");
            }
            return false;
        }
        
        // Page must have the components field registered
        $has_field = $this->pageHasComponentsField();
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Umbral Editor Debug: pageHasComponentsField(): " . ($has_field ? 'true' : 'false'));
        }
        
        return $has_field;
    }
    
    /**
     * Check if we should show any editor interface (toast or full editor)
     */
    private function shouldShowEditorInterface() {
        $umbral_mode = isset($_GET['umbral']) ? $_GET['umbral'] : null;
        $source_id = isset($_GET['source_id']) ? intval($_GET['source_id']) : null;
        $source_url = isset($_GET['source_url']) ? $_GET['source_url'] : null;
        
        // Must be frontend (not admin)
        if (is_admin()) {
            return false;
        }
        
        // Must have source_id parameter in URL
        if (!$source_id) {
            return false;
        }
        
        // For archives, must also have source_url parameter
        if (is_archive() && !$source_url) {
            return false;
        }
        
        // User must be logged in and able to edit the source post
        if (!is_user_logged_in() || !current_user_can('edit_post', $source_id)) {
            return false;
        }
        
        // Source page must have the components field registered
        if (!$this->pageHasComponentsField($source_id)) {
            return false;
        }
        
        // Show interface for normal mode (toast) or editor mode (full editor)
        // Don't show for preview mode
        return $umbral_mode !== 'preview';
    }
    
    /**
     * Check if specified page has components field registered
     */
    private function pageHasComponentsField($post_id = null) {
        global $wp_meta_boxes;
        
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        $post_type = get_post_type($post_id);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Umbral Editor Debug: pageHasComponentsField() - Post ID: {$post_id}, Post Type: {$post_type}");
            error_log("Umbral Editor Debug: CMB2_Boxes class exists: " . (class_exists('CMB2_Boxes') ? 'true' : 'false'));
        }
        
        // Method 1: Check for existing page_components meta (simple fallback)
        $existing_meta = get_post_meta($post_id, 'page_components', true);
        if (!empty($existing_meta)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: Found existing page_components meta");
            }
            return true;
        }
        
        // Method 2: Check if we have any CMB2 metaboxes with components_field type
        if (class_exists('CMB2_Boxes')) {
            $cmb2_boxes = CMB2_Boxes::get_all();
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: Found " . count($cmb2_boxes) . " CMB2 boxes");
            }
            
            foreach ($cmb2_boxes as $cmb2_id => $cmb2) {
                $object_types = $cmb2->object_types();
                
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Umbral Editor Debug: Box '{$cmb2_id}' object types: " . implode(', ', $object_types));
                }
                
                if (in_array($post_type, $object_types)) {
                    $fields = $cmb2->prop('fields');
                    
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("Umbral Editor Debug: Box '{$cmb2_id}' has " . count($fields) . " fields");
                    }
                    
                    foreach ($fields as $field_id => $field) {
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            $field_type = isset($field['type']) ? $field['type'] : 'unknown';
                            error_log("Umbral Editor Debug: Field '{$field_id}' type: {$field_type}");
                        }
                        
                        if (isset($field['type']) && $field['type'] === 'components_field') {
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                error_log("Umbral Editor Debug: Found components_field! Field ID: {$field_id}");
                            }
                            return true;
                        }
                    }
                }
            }
        }
        
        // Method 3: Check if post type is enabled in admin settings
        $enabled_post_types = UmbralEditor_Admin::getEnabledPostTypes();
        if (in_array($post_type, $enabled_post_types)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Umbral Editor Debug: Post type '{$post_type}' is enabled in settings");
            }
            return true;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Umbral Editor Debug: No components_field found");
        }
        
        return false;
    }
    
    /**
     * Get the editor URL
     */
    private function getEditorUrl() {
        $url = add_query_arg('umbral', 'editor', get_permalink());
        
        // Preserve source_id and source_url parameters if they exist
        if (isset($_GET['source_id'])) {
            $url = add_query_arg('source_id', $_GET['source_id'], $url);
        }
        if (isset($_GET['source_url'])) {
            $url = add_query_arg('source_url', $_GET['source_url'], $url);
        }
        
        return $url;
    }
    
    /**
     * Get the close URL (remove editor param)
     */
    private function getCloseUrl() {
        $url = remove_query_arg('umbral', get_permalink());
        
        // Preserve source_id and source_url parameters if they exist
        if (isset($_GET['source_id'])) {
            $url = add_query_arg('source_id', $_GET['source_id'], $url);
        }
        if (isset($_GET['source_url'])) {
            $url = add_query_arg('source_url', $_GET['source_url'], $url);
        }
        
        return $url;
    }
    
    /**
     * Render the floating editor toast
     */
    private function renderEditorToast() {
        ?>
        <div id="umbral-editor-toast-container"></div>
        <!-- Umbral Editor Debug: Toast container rendered -->
        <style>
        #umbral-editor-toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 99999;
        }
        
        /* Debug fallback - show if web component doesn't load */
        #umbral-editor-toast-container:empty::after {
            content: "Umbral Editor Loading...";
            display: block;
            background: #f0f0f0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
        }
        </style>
        <?php
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo "<!-- Umbral Editor Debug: renderEditorToast() executed -->\n";
        }
    }
    
    /**
     * Render the full editor interface
     */
    private function renderFullEditor() {
        ?>
        <div id="umbral-full-editor-container"></div>
        <style>
        /* Hide the page content when in editor mode */
        body.umbral-editor-mode {
            overflow: hidden;
        }
        
        body.umbral-editor-mode #page,
        body.umbral-editor-mode #content,
        body.umbral-editor-mode .site-content,
        body.umbral-editor-mode main {
            display: none !important;
        }
        
        #umbral-full-editor-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: 99999;
            background: #ffffff;
        }
        </style>
        <script>
        // Add body class for editor mode
        document.body.classList.add('umbral-editor-mode');
        
        // Prevent default WordPress admin bar from interfering
        if (document.querySelector('#wpadminbar')) {
            document.querySelector('#wpadminbar').style.display = 'none';
        }
        </script>
        <?php
    }
    
    
    /**
     * Maybe load the preview template
     */
    public function maybeLoadPreviewTemplate() {
        // Check if we're in preview mode
        if (!isset($_GET['umbral']) || $_GET['umbral'] !== 'preview') {
            return;
        }
        
        // Check if we have a source_id parameter
        $source_id = isset($_GET['source_id']) ? intval($_GET['source_id']) : 0;
        if (!$source_id) {
            return;
        }
        
        // Load the preview template for any page type (not just singular pages)
        $template_path = UMBRAL_EDITOR_DIR . 'inc/editor/preview-template.php';
        if (file_exists($template_path)) {
            include $template_path;
            exit;
        }
    }
    
    /**
     * Render content for preview template
     */
    public function renderPreviewContent() {
        // Only render if we're in preview mode
        if (!isset($_GET['umbral']) || $_GET['umbral'] !== 'preview') {
            return;
        }
        
        // Get source_id from URL parameters - this is the page that contains the components
        $source_id = isset($_GET['source_id']) ? intval($_GET['source_id']) : get_the_ID();
        
        if (!$source_id) {
            echo '<p>No source page specified for preview.</p>';
            return;
        }
        
        // Get the source post content (where the blocks live)
        $source_post = get_post($source_id);
        
        if (!$source_post) {
            echo '<p>Source post not found (ID: ' . $source_id . ').</p>';
            return;
        }
        
        // Debug information
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Umbral Preview Content: Loading blocks from source post=' . $source_id . ' (' . $source_post->post_title . ')');
        }
        
        // Get the blocks content from the source post and render it
        $source_content = $source_post->post_content;
        
        // Parse and render the blocks from the source post
        $rendered_content = do_blocks($source_content);
        
        // Apply content filters to ensure proper formatting
        $rendered_content = apply_filters('the_content', $rendered_content);
        
        // Output the rendered blocks content
        echo $rendered_content;
    }
    
    /**
     * Get Umbral context data for JavaScript
     */
    public function getUmbralContext($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$post_id) {
            return [];
        }
        
        $components = get_post_meta($post_id, 'page_components', true);
        $display_mode = get_post_meta($post_id, 'components_display_mode', true) ?: 'replace';
        
        // Get query parameters for template control
        $show_header = !isset($_GET['header']) || $_GET['header'] !== 'false';
        $show_footer = !isset($_GET['footer']) || $_GET['footer'] !== 'false';
        $umbral_mode = isset($_GET['umbral']) ? $_GET['umbral'] : null;
        
        return [
            'postId' => $post_id,
            'components' => $components ?: [],
            'displayMode' => $display_mode,
            'showHeader' => $show_header,
            'showFooter' => $show_footer,
            'umbralMode' => $umbral_mode,
            'timestamp' => current_time('timestamp'),
            'url' => get_permalink($post_id),
            'editUrl' => current_user_can('edit_post', $post_id) ? add_query_arg('umbral', 'editor', get_permalink($post_id)) : null,
            'previewUrl' => add_query_arg('umbral', 'preview', get_permalink($post_id))
        ];
    }
    
    /**
     * Render Umbral context script tag
     */
    public function renderUmbralContextScript($post_id = null) {
        $context_data = $this->getUmbralContext($post_id);
        ?>
        <script id="um-context" type="application/json">
        <?php echo wp_json_encode($context_data, JSON_PRETTY_PRINT); ?>
        </script>
        <?php
    }
}