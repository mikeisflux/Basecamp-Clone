<?php
/**
 * Authentication service class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/services
 */

class BCWP_Auth_Service {

    /**
     * Check if user is logged in.
     *
     * @return bool
     */
    public static function is_logged_in() {
        return is_user_logged_in();
    }

    /**
     * Get current user ID.
     *
     * @return int User ID, 0 if not logged in.
     */
    public static function get_current_user_id() {
        return get_current_user_id();
    }

    /**
     * Get current user data.
     *
     * @return WP_User|false User object or false if not logged in.
     */
    public static function get_current_user() {
        return wp_get_current_user();
    }

    /**
     * Check if current user is admin.
     *
     * @return bool
     */
    public static function is_admin() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Require authentication.
     * Redirects to login if not authenticated.
     */
    public static function require_auth() {
        if ( ! self::is_logged_in() ) {
            wp_redirect( wp_login_url( self::get_current_url() ) );
            exit;
        }
    }

    /**
     * Get current URL.
     *
     * @return string Current URL.
     */
    private static function get_current_url() {
        return ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Generate user avatar HTML.
     *
     * @param int    $user_id User ID.
     * @param int    $size    Avatar size.
     * @param string $class   CSS class.
     * @return string Avatar HTML.
     */
    public static function get_user_avatar( $user_id, $size = 40, $class = 'bcwp-avatar' ) {
        return get_avatar( $user_id, $size, '', '', array( 'class' => $class ) );
    }

    /**
     * Get user display name.
     *
     * @param int $user_id User ID.
     * @return string Display name.
     */
    public static function get_user_display_name( $user_id ) {
        $user = get_userdata( $user_id );
        return $user ? $user->display_name : __( 'Unknown User', 'basecamp-wp-pro' );
    }
}
