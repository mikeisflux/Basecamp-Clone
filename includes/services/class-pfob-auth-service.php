<?php
/**
 * Authentication service class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_Auth_Service {

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
     * Check if user has an active subscription.
     *
     * @param int $user_id User ID (optional, defaults to current user).
     * @return bool True if user has active subscription, false otherwise.
     */
    public static function has_active_subscription( $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = self::get_current_user_id();
        }

        // Admins always have access
        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        return PFOB_Subscription::is_active( $user_id );
    }

    /**
     * Require active subscription.
     * Redirects to pricing page if no active subscription.
     */
    public static function require_subscription() {
        $user_id = self::get_current_user_id();

        // Admins always have access
        if ( user_can( $user_id, 'manage_options' ) ) {
            return;
        }

        if ( ! PFOB_Subscription::is_active( $user_id ) ) {
            wp_redirect( home_url( '/projectfob/pricing/' ) );
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
    public static function get_user_avatar( $user_id, $size = 40, $class = 'pfob-avatar' ) {
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
        return $user ? $user->display_name : __( 'Unknown User', 'projectfob' );
    }
}
