<?php
/**
 * User data REST API endpoint
 */

class UmbralEditor_User_Endpoint {
    
    /**
     * Register the user endpoint
     */
    public function register() {
        register_rest_route('umbral-editor/v1', '/user', [
            'methods' => 'GET',
            'callback' => [$this, 'getUserData'],
            'permission_callback' => [$this, 'permissionCheck']
        ]);
    }
    
    /**
     * Permission check - any logged in user can access their own data
     */
    public function permissionCheck() {
        return is_user_logged_in();
    }
    
    /**
     * Get current user data
     */
    public function getUserData($request) {
        // Verify nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
        }
        
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $user_role = !empty($user_roles) ? $user_roles[0] : 'subscriber';
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'userRole' => $user_role,
                'userId' => $current_user->ID,
                'userName' => $current_user->display_name,
                'userEmail' => $current_user->user_email,
                'isAdmin' => current_user_can('manage_options'),
                'capabilities' => array_keys($current_user->allcaps),
                'registeredDate' => $current_user->user_registered
            ]
        ]);
    }
}