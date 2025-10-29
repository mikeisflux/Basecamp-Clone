<?php
/**
 * Settings REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Settings_Endpoint {

    protected $namespace = 'pfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get user settings
        register_rest_route( $this->namespace, '/settings', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_settings' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Update user settings
        register_rest_route( $this->namespace, '/settings', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'update_settings' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Send test digest
        register_rest_route( $this->namespace, '/settings/test-digest', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'send_test_digest' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );
    }

    /**
     * Check if user has permission.
     */
    public function check_permission( $request ) {
        return is_user_logged_in();
    }

    /**
     * Get user settings.
     */
    public function get_settings( $request ) {
        $user_id = get_current_user_id();

        $settings = array(
            'digest_frequency' => PFOB_Digest_Service::get_user_preference( $user_id ),
            'email_notifications' => get_user_meta( $user_id, 'pfob_email_notifications', true ) ?: 'all',
            'notification_mentions' => get_user_meta( $user_id, 'pfob_notify_mentions', true ) !== '0',
            'notification_assignments' => get_user_meta( $user_id, 'pfob_notify_assignments', true ) !== '0',
            'notification_comments' => get_user_meta( $user_id, 'pfob_notify_comments', true ) !== '0',
        );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $settings,
        ), 200 );
    }

    /**
     * Update user settings.
     */
    public function update_settings( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        // Update digest frequency
        if ( isset( $params['digest_frequency'] ) ) {
            PFOB_Digest_Service::set_user_preference( $user_id, $params['digest_frequency'] );
        }

        // Update email notification preference
        if ( isset( $params['email_notifications'] ) ) {
            $allowed = array( 'all', 'mentions', 'none' );
            if ( in_array( $params['email_notifications'], $allowed ) ) {
                update_user_meta( $user_id, 'pfob_email_notifications', $params['email_notifications'] );
            }
        }

        // Update specific notification types
        if ( isset( $params['notification_mentions'] ) ) {
            update_user_meta( $user_id, 'pfob_notify_mentions', $params['notification_mentions'] ? '1' : '0' );
        }

        if ( isset( $params['notification_assignments'] ) ) {
            update_user_meta( $user_id, 'pfob_notify_assignments', $params['notification_assignments'] ? '1' : '0' );
        }

        if ( isset( $params['notification_comments'] ) ) {
            update_user_meta( $user_id, 'pfob_notify_comments', $params['notification_comments'] ? '1' : '0' );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Settings updated successfully',
        ), 200 );
    }

    /**
     * Send test digest.
     */
    public function send_test_digest( $request ) {
        $user_id = get_current_user_id();

        $result = PFOB_Digest_Service::send_test_digest( $user_id );

        if ( $result ) {
            return new WP_REST_Response( array(
                'success' => true,
                'message' => 'Test digest sent to your email',
            ), 200 );
        } else {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'No activity to report, or email sending failed',
            ), 200 );
        }
    }
}
