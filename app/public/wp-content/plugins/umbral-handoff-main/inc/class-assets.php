<?php
/**
 * Asset management for Umbral Editor
 */

class UmbralEditor_Assets {
    
    /**
     * Initialize asset functionality
     */
    public function init() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
        
        // Add web component to footers (commented out - not using demo panel currently)
        // add_action('wp_footer', [$this, 'renderWebComponent']);
        // add_action('admin_footer', [$this, 'renderWebComponent']);
    }
    
    /**
     * Enqueue frontend and admin assets
     */
    public function enqueueAssets() {
        // Enqueue the main React build
        $js_file = UMBRAL_EDITOR_DIR . 'dist/js/umbral-editor.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'umbral-editor-js',
                UMBRAL_EDITOR_URL . 'dist/js/umbral-editor.js',
                [],
                filemtime($js_file),
                true
            );
        }
        
        // Enqueue Components Field script on admin pages with CMB2
        if (is_admin() && $this->shouldLoadComponentsField()) {
            $components_js_file = UMBRAL_EDITOR_DIR . 'dist/js/umbral-components-field.js';
            if (file_exists($components_js_file)) {
                wp_enqueue_script(
                    'umbral-components-field-js',
                    UMBRAL_EDITOR_URL . 'dist/js/umbral-components-field.js',
                    [],
                    filemtime($components_js_file),
                    true
                );
            }
        }
    }
    
    /**
     * Enqueue block editor specific assets
     */
    public function enqueueBlockEditorAssets() {
        $this->enqueueAssets();
        
        // Add CSS directly to the head for immediate application
        add_action('admin_head', function() {
            echo '<style id="umbral-block-editor-styles">' . $this->getBlockEditorCSS() . '</style>';
        });
    }
    
    /**
     * Get CSS for block editor inspector controls
     */
    private function getBlockEditorCSS() {
        return '
        /* Umbral Components Block Inspector Styles */
        .umbral-mode-tabs {
            margin-bottom: 16px !important;
        }
        
        .umbral-mode-tabs .components-tab-panel__tabs {
            display: flex !important;
            border-bottom: 1px solid #ddd !important;
            margin-bottom: 16px !important;
            background: #fff !important;
        }
        
        .umbral-mode-tabs .components-tab-panel__tabs-item {
            flex: 1 !important;
            padding: 10px 12px !important;
            background: #f7f7f7 !important;
            border: 1px solid #ddd !important;
            border-bottom: 2px solid #ddd !important;
            cursor: pointer !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            text-align: center !important;
            transition: all 0.15s ease !important;
            margin-right: -1px !important;
        }
        
        .umbral-mode-tabs .components-tab-panel__tabs-item:first-child {
            border-top-left-radius: 4px !important;
        }
        
        .umbral-mode-tabs .components-tab-panel__tabs-item:last-child {
            border-top-right-radius: 4px !important;
            margin-right: 0 !important;
        }
        
        .umbral-mode-tabs .components-tab-panel__tabs-item:hover {
            background: #f0f0f0 !important;
        }
        
        .umbral-mode-tabs .components-tab-panel__tabs-item.active-tab,
        .umbral-mode-tabs .components-tab-panel__tabs-item[aria-selected="true"] {
            border-bottom-color: #007cba !important;
            color: #007cba !important;
            background: #fff !important;
            border-bottom-width: 3px !important;
            position: relative !important;
            z-index: 1 !important;
        }
        
        .umbral-mode-tabs .components-tab-panel__tab-content {
            padding: 16px 0 !important;
            border: 1px solid #ddd !important;
            border-top: none !important;
            background: #fff !important;
            padding: 12px !important;
            border-radius: 0 0 4px 4px !important;
        }
        
        .umbral-mode-tabs .tab-content {
            padding: 0 !important;
        }
        
        .umbral-mode-tabs .components-base-control {
            margin-bottom: 16px !important;
        }
        
        .umbral-mode-tabs .components-base-control:last-child {
            margin-bottom: 0 !important;
        }
        
        .umbral-mode-tabs .components-base-control__label {
            font-size: 11px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            margin-bottom: 6px !important;
            color: #1e1e1e !important;
            letter-spacing: 0.5px !important;
        }
        
        .umbral-mode-tabs .components-select-control__input,
        .umbral-mode-tabs .components-text-control__input {
            min-height: 36px !important;
            border: 1px solid #949494 !important;
            border-radius: 2px !important;
            padding: 6px 8px !important;
            font-size: 13px !important;
        }
        
        .umbral-mode-tabs .components-text-control__input[readonly] {
            background: #f6f7f7 !important;
            color: #757575 !important;
            border-color: #c3c4c7 !important;
        }
        
        .umbral-mode-tabs .components-select-control__input:focus,
        .umbral-mode-tabs .components-text-control__input:focus {
            border-color: #007cba !important;
            box-shadow: 0 0 0 1px #007cba !important;
        }
        
        /* Better spacing in panel */
        .components-panel__body .umbral-mode-tabs {
            margin-left: -16px !important;
            margin-right: -16px !important;
            margin-bottom: 0 !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 781px) {
            .umbral-mode-tabs .components-tab-panel__tabs-item {
                padding: 8px 6px !important;
                font-size: 12px !important;
            }
        }
        ';
    }
    
    /**
     * Render web component in footer with only REST nonce
     */
    public function renderWebComponent() {
        // Don't add if we're on the admin settings page (it's already there)
        if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'umbral-editor-settings') {
            return;
        }
        
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (!document.querySelector('umbral-editor-panel')) {
                    const panel = document.createElement('umbral-editor-panel');
                    
                    // Only pass the REST nonce - everything else fetched via API
                    panel.setAttribute('rest-nonce', '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>');
                    
                    document.body.appendChild(panel);
                }
            });
        </script>
        <?php
    }
    
    /**
     * Check if we should load the Components Field script
     */
    private function shouldLoadComponentsField() {
        global $pagenow;
        
        // Always load on admin pages for now (we can optimize later)
        if (is_admin()) {
            error_log('Umbral Editor: Loading Components Field script on admin page: ' . $pagenow);
            return true;
        }
        
        return false;
    }
}