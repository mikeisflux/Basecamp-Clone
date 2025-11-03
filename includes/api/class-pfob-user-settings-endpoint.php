<?php
/**
 * User Settings REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_User_Settings_Endpoint {

    /**
     * Register REST API routes.
     */
    public static function register_routes() {
        // Update user account (name, email, password)
        register_rest_route( 'projectfob/v1', '/user/account', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'update_account' ),
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ) );

        // Update user preferences (all other settings)
        register_rest_route( 'projectfob/v1', '/user/preferences', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'update_preferences' ),
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ) );

        // Get user settings
        register_rest_route( 'projectfob/v1', '/user/settings', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_settings' ),
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ) );
    }

    /**
     * Update user account settings (name, email, password).
     */
    public static function update_account( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $user_data = array(
            'ID' => $user_id,
        );

        // Update display name
        if ( isset( $params['display_name'] ) && ! empty( $params['display_name'] ) ) {
            $user_data['display_name'] = sanitize_text_field( $params['display_name'] );
        }

        // Update email
        if ( isset( $params['email'] ) && ! empty( $params['email'] ) ) {
            $email = sanitize_email( $params['email'] );

            // Validate email
            if ( ! is_email( $email ) ) {
                return new WP_Error( 'invalid_email', 'Invalid email address', array( 'status' => 400 ) );
            }

            // Check if email is already in use
            if ( email_exists( $email ) && email_exists( $email ) !== $user_id ) {
                return new WP_Error( 'email_exists', 'Email address is already in use', array( 'status' => 400 ) );
            }

            $user_data['user_email'] = $email;
        }

        // Update password
        if ( isset( $params['new_password'] ) && ! empty( $params['new_password'] ) ) {
            $current_password = $params['current_password'] ?? '';
            $new_password = $params['new_password'];

            // Verify current password
            $user = get_user_by( 'id', $user_id );
            if ( ! wp_check_password( $current_password, $user->user_pass, $user_id ) ) {
                return new WP_Error( 'invalid_password', 'Current password is incorrect', array( 'status' => 400 ) );
            }

            // Validate new password strength
            if ( strlen( $new_password ) < 8 ) {
                return new WP_Error( 'weak_password', 'Password must be at least 8 characters long', array( 'status' => 400 ) );
            }

            $user_data['user_pass'] = $new_password;
        }

        // Update user
        $result = wp_update_user( $user_data );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'update_failed', $result->get_error_message(), array( 'status' => 500 ) );
        }

        // Log activity
        PFOB_Activity::log( array(
            'user_id'      => $user_id,
            'action_type'  => 'account.updated',
            'subject_type' => 'user',
            'subject_id'   => $user_id,
            'description'  => 'Updated account settings',
        ) );

        return rest_ensure_response( array(
            'success' => true,
            'message' => 'Account settings updated successfully',
        ) );
    }

    /**
     * Update user preferences (theme, notifications, privacy, etc.).
     */
    public static function update_preferences( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        // Appearance Settings
        if ( isset( $params['theme_mode'] ) ) {
            $theme_mode = sanitize_text_field( $params['theme_mode'] );
            if ( in_array( $theme_mode, array( 'light', 'dark', 'auto' ) ) ) {
                update_user_meta( $user_id, 'pfob_theme_mode', $theme_mode );
            }
        }

        if ( isset( $params['compact_mode'] ) ) {
            update_user_meta( $user_id, 'pfob_compact_mode', (bool) $params['compact_mode'] );
        }

        if ( isset( $params['show_avatars'] ) ) {
            update_user_meta( $user_id, 'pfob_show_avatars', (bool) $params['show_avatars'] );
        }

        // Notification Settings
        if ( isset( $params['notifications'] ) ) {
            $notifications = array(
                'mentions'        => isset( $params['notifications']['mentions'] ) ? (bool) $params['notifications']['mentions'] : true,
                'comments'        => isset( $params['notifications']['comments'] ) ? (bool) $params['notifications']['comments'] : true,
                'assignments'     => isset( $params['notifications']['assignments'] ) ? (bool) $params['notifications']['assignments'] : true,
                'project_invites' => isset( $params['notifications']['project_invites'] ) ? (bool) $params['notifications']['project_invites'] : true,
            );
            update_user_meta( $user_id, 'pfob_notification_settings', $notifications );
        }

        if ( isset( $params['email_digest_frequency'] ) ) {
            $frequency = sanitize_text_field( $params['email_digest_frequency'] );
            if ( in_array( $frequency, array( 'realtime', 'hourly', 'daily', 'weekly', 'never' ) ) ) {
                update_user_meta( $user_id, 'pfob_email_digest_frequency', $frequency );
            }
        }

        // Calendar Settings
        if ( isset( $params['calendar_sync_enabled'] ) ) {
            update_user_meta( $user_id, 'pfob_calendar_sync_enabled', (bool) $params['calendar_sync_enabled'] );
        }

        if ( isset( $params['calendar_two_way_sync'] ) ) {
            update_user_meta( $user_id, 'pfob_calendar_two_way_sync', (bool) $params['calendar_two_way_sync'] );
        }

        // Privacy Settings
        if ( isset( $params['show_activity'] ) ) {
            update_user_meta( $user_id, 'pfob_show_activity', (bool) $params['show_activity'] );
        }

        if ( isset( $params['show_online_status'] ) ) {
            update_user_meta( $user_id, 'pfob_show_online_status', (bool) $params['show_online_status'] );
        }

        if ( isset( $params['session_timeout'] ) ) {
            update_user_meta( $user_id, 'pfob_session_timeout', (bool) $params['session_timeout'] );
        }

        // Preferences
        if ( isset( $params['timezone'] ) ) {
            $timezone = sanitize_text_field( $params['timezone'] );
            update_user_meta( $user_id, 'pfob_timezone', $timezone );
        }

        if ( isset( $params['default_project_view'] ) ) {
            $view = sanitize_text_field( $params['default_project_view'] );
            if ( in_array( $view, array( 'overview', 'messages', 'todos', 'docs', 'schedule' ) ) ) {
                update_user_meta( $user_id, 'pfob_default_project_view', $view );
            }
        }

        if ( isset( $params['date_format'] ) ) {
            $format = sanitize_text_field( $params['date_format'] );
            update_user_meta( $user_id, 'pfob_date_format', $format );
        }

        if ( isset( $params['enable_shortcuts'] ) ) {
            update_user_meta( $user_id, 'pfob_enable_shortcuts', (bool) $params['enable_shortcuts'] );
        }

        // Single key-value update (for quick saves)
        if ( isset( $params['key'] ) && isset( $params['value'] ) ) {
            $key = sanitize_text_field( $params['key'] );
            $value = $params['value'];

            // Whitelist allowed keys
            $allowed_keys = array(
                'pfob_theme_mode',
                'pfob_compact_mode',
                'pfob_show_avatars',
                'pfob_files_view',
                'pfob_timezone',
                'pfob_date_format',
                'pfob_enable_shortcuts',
            );

            if ( in_array( $key, $allowed_keys ) ) {
                update_user_meta( $user_id, $key, $value );
            }
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => 'Preferences updated successfully',
        ) );
    }

    /**
     * Get all user settings.
     */
    public static function get_settings( $request ) {
        $user_id = get_current_user_id();
        $user = get_user_by( 'id', $user_id );

        // Get all settings
        $settings = array(
            'account' => array(
                'display_name' => $user->display_name,
                'email'        => $user->user_email,
                'username'     => $user->user_login,
            ),
            'appearance' => array(
                'theme_mode'    => get_user_meta( $user_id, 'pfob_theme_mode', true ) ?: 'light',
                'compact_mode'  => (bool) get_user_meta( $user_id, 'pfob_compact_mode', true ),
                'show_avatars'  => get_user_meta( $user_id, 'pfob_show_avatars', true ) !== '0',
            ),
            'notifications' => array(
                'settings'  => get_user_meta( $user_id, 'pfob_notification_settings', true ) ?: array(
                    'mentions'        => true,
                    'comments'        => true,
                    'assignments'     => true,
                    'project_invites' => true,
                ),
                'frequency' => get_user_meta( $user_id, 'pfob_email_digest_frequency', true ) ?: 'daily',
            ),
            'cloud' => array(
                'gdrive_connected'  => !empty( get_user_meta( $user_id, 'pfob_gdrive_access_token', true ) ),
                'dropbox_connected' => !empty( get_user_meta( $user_id, 'pfob_dropbox_access_token', true ) ),
            ),
            'calendar' => array(
                'sync_enabled'   => (bool) get_user_meta( $user_id, 'pfob_calendar_sync_enabled', true ),
                'two_way_sync'   => (bool) get_user_meta( $user_id, 'pfob_calendar_two_way_sync', true ),
            ),
            'privacy' => array(
                'show_activity'      => get_user_meta( $user_id, 'pfob_show_activity', true ) !== '0',
                'show_online_status' => get_user_meta( $user_id, 'pfob_show_online_status', true ) !== '0',
                'session_timeout'    => (bool) get_user_meta( $user_id, 'pfob_session_timeout', true ),
            ),
            'preferences' => array(
                'timezone'             => get_user_meta( $user_id, 'pfob_timezone', true ) ?: 'UTC',
                'default_project_view' => get_user_meta( $user_id, 'pfob_default_project_view', true ) ?: 'overview',
                'date_format'          => get_user_meta( $user_id, 'pfob_date_format', true ) ?: 'm/d/Y',
                'enable_shortcuts'     => get_user_meta( $user_id, 'pfob_enable_shortcuts', true ) !== '0',
            ),
        );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $settings,
        ) );
    }
}
