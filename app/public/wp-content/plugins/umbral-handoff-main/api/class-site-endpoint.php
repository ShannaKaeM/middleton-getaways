<?php
/**
 * Site data REST API endpoint
 */

class UmbralEditor_Site_Endpoint {
    
    /**
     * Register the site endpoint
     */
    public function register() {
        register_rest_route('umbral-editor/v1', '/site', [
            'methods' => 'GET',
            'callback' => [$this, 'getSiteData'],
            'permission_callback' => [$this, 'permissionCheck']
        ]);
    }
    
    /**
     * Permission check - any logged in user can access site data
     */
    public function permissionCheck() {
        return is_user_logged_in();
    }
    
    /**
     * Get site data
     */
    public function getSiteData($request) {
        // Verify nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'siteUrl' => home_url(),
                'siteName' => get_bloginfo('name'),
                'siteDescription' => get_bloginfo('description'),
                'adminUrl' => admin_url(),
                'pluginVersion' => UMBRAL_EDITOR_VERSION,
                'wordpressVersion' => get_bloginfo('version'),
                'theme' => [
                    'name' => wp_get_theme()->get('Name'),
                    'version' => wp_get_theme()->get('Version')
                ],
                'timezone' => get_option('timezone_string') ?: 'UTC'
            ]
        ]);
    }
}