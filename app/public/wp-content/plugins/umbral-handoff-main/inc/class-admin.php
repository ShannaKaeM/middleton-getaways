<?php
/**
 * Admin functionality for Umbral Editor
 */

class UmbralEditor_Admin {
    
    /**
     * Initialize admin functionality
     */
    public function init() {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
    }
    
    /**
     * Add admin menu
     */
    public function addAdminMenu() {
        add_options_page(
            __('Umbral Editor Settings', 'umbral-editor'),
            __('Umbral Editor', 'umbral-editor'),
            'manage_options',
            'umbral-editor-settings',
            [$this, 'renderAdminPage']
        );
    }
    
    /**
     * Register settings
     */
    public function registerSettings() {
        register_setting('umbral_editor_settings', 'umbral_editor_enabled_post_types');
        register_setting('umbral_editor_settings', 'umbral_editor_builder_opt_in');
    }

    /**
     * Render admin page with settings form
     */
    public function renderAdminPage() {
        // Handle form submission
        if (isset($_POST['submit']) && check_admin_referer('umbral_editor_settings', 'umbral_editor_nonce')) {
            $enabled_post_types = isset($_POST['umbral_editor_enabled_post_types']) ? array_map('sanitize_text_field', $_POST['umbral_editor_enabled_post_types']) : [];
            $builder_opt_in = isset($_POST['umbral_editor_builder_opt_in']) ? 1 : 0;
            
            update_option('umbral_editor_enabled_post_types', $enabled_post_types);
            update_option('umbral_editor_builder_opt_in', $builder_opt_in);
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'umbral-editor') . '</p></div>';
        }

        // Get current settings
        $enabled_post_types = get_option('umbral_editor_enabled_post_types', ['page']);
        $builder_opt_in = get_option('umbral_editor_builder_opt_in', 1);
        
        // Get all post types (excluding media)
        $post_types = get_post_types(['public' => true], 'objects');
        $private_post_types = get_post_types(['public' => false, '_builtin' => false], 'objects');
        $all_post_types = array_merge($post_types, $private_post_types);
        
        // Remove attachment (media) post type
        unset($all_post_types['attachment']);
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('umbral_editor_settings', 'umbral_editor_nonce'); ?>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="umbral_editor_builder_opt_in"><?php _e('Builder Opt-in', 'umbral-editor'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="umbral_editor_builder_opt_in" name="umbral_editor_builder_opt_in" value="1" <?php checked($builder_opt_in, 1); ?> />
                                <label for="umbral_editor_builder_opt_in"><?php _e('Enable Umbral Page Builder', 'umbral-editor'); ?></label>
                                <p class="description"><?php _e('Enable or disable the Umbral page builder functionality.', 'umbral-editor'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label><?php _e('Enabled Post Types', 'umbral-editor'); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><?php _e('Select post types to enable page builder for', 'umbral-editor'); ?></legend>
                                    <?php foreach ($all_post_types as $post_type_key => $post_type_obj): ?>
                                        <label for="post_type_<?php echo esc_attr($post_type_key); ?>">
                                            <input type="checkbox" 
                                                   id="post_type_<?php echo esc_attr($post_type_key); ?>" 
                                                   name="umbral_editor_enabled_post_types[]" 
                                                   value="<?php echo esc_attr($post_type_key); ?>"
                                                   <?php checked(in_array($post_type_key, $enabled_post_types)); ?> />
                                            <?php echo esc_html($post_type_obj->labels->name); ?>
                                            <span class="description">(<?php echo esc_html($post_type_key); ?>)</span>
                                        </label><br>
                                    <?php endforeach; ?>
                                    <p class="description"><?php _e('Select which post types should have the Umbral page builder available.', 'umbral-editor'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <div id="umbral-editor-admin-root">
                <!-- Clean implementation: only pass REST nonce, fetch data via API -->
                <umbral-editor-panel 
                    rest-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
                ></umbral-editor-panel>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get enabled post types
     */
    public static function getEnabledPostTypes() {
        $builder_opt_in = get_option('umbral_editor_builder_opt_in', 1);
        $enabled_post_types = get_option('umbral_editor_enabled_post_types', ['page']);
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Umbral Editor: Builder opt-in: ' . ($builder_opt_in ? 'enabled' : 'disabled'));
            error_log('Umbral Editor: Enabled post types: ' . implode(', ', $enabled_post_types));
        }
        
        if (!$builder_opt_in) {
            return [];
        }
        
        return $enabled_post_types;
    }
}