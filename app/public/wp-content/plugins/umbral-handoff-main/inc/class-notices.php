<?php
/**
 * Admin notices for Umbral Editor
 */

class UmbralEditor_Notices {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_notices', [$this, 'showSetupNotice']);
        add_action('wp_ajax_umbral_dismiss_notice', [$this, 'dismissNotice']);
    }
    
    /**
     * Show setup notice
     */
    public function showSetupNotice() {
        // Only show on relevant admin pages
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['edit-page', 'page', 'dashboard'])) {
            return;
        }
        
        // Check if notice was dismissed
        if (get_option('umbral_editor_setup_notice_dismissed')) {
            return;
        }
        
        // Check if CMB2 is active
        if (!class_exists('CMB2')) {
            $this->showCMB2Notice();
            return;
        }
        
        $this->showWelcomeNotice();
    }
    
    /**
     * Show CMB2 requirement notice
     */
    private function showCMB2Notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Umbral Editor:</strong> This plugin requires CMB2. 
                <a href="<?php echo admin_url('plugins.php'); ?>">Install CMB2</a> or run 
                <code>composer require cmb2/cmb2</code> to get started.
            </p>
        </div>
        <?php
    }
    
    /**
     * Show welcome notice
     */
    private function showWelcomeNotice() {
        ?>
        <div class="notice notice-success is-dismissible" data-notice="umbral-setup">
            <p>
                <strong>🎉 Umbral Editor is ready!</strong> 
                The Components Field has been added to all pages. 
                <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>">Create a page</a> 
                and try the new Page Builder, or 
                <a href="<?php echo admin_url('edit.php?post_type=page'); ?>">edit an existing page</a> 
                to see the Components Field in action.
            </p>
            <p>
                <em>Use the "Add Component" button to choose from Hero sections, Testimonials, Content blocks, and more!</em>
            </p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.notice[data-notice="umbral-setup"] .notice-dismiss').on('click', function() {
                $.post(ajaxurl, {
                    action: 'umbral_dismiss_notice',
                    notice: 'setup',
                    nonce: '<?php echo wp_create_nonce('umbral_dismiss_notice'); ?>'
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle notice dismissal
     */
    public function dismissNotice() {
        if (!wp_verify_nonce($_POST['nonce'], 'umbral_dismiss_notice')) {
            wp_die('Invalid nonce');
        }
        
        $notice = sanitize_text_field($_POST['notice']);
        
        if ($notice === 'setup') {
            update_option('umbral_editor_setup_notice_dismissed', true);
        }
        
        wp_send_json_success();
    }
}