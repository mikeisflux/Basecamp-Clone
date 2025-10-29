<?php
/**
 * Calendar Integration REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Calendar_Integration_Endpoint {

    protected $namespace = 'pfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get integration status
        register_rest_route( $this->namespace, '/calendar-integration/status', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_status' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Get authorization URL
        register_rest_route( $this->namespace, '/calendar-integration/auth-url', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_auth_url' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Disconnect
        register_rest_route( $this->namespace, '/calendar-integration/disconnect', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'disconnect' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Sync event
        register_rest_route( $this->namespace, '/calendar-integration/sync-event/(?P<event_id>\d+)', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'sync_event' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Update settings
        register_rest_route( $this->namespace, '/calendar-integration/settings', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'update_settings' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );
    }

    /**
     * Check if user has permission.
     */
    public function check_permission( $request ) {
        return is_user_logged_in();
    }

    /**
     * Get integration status.
     */
    public function get_status( $request ) {
        $user_id = get_current_user_id();
        $settings = PFOB_Google_Calendar_Service::get_settings( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $settings,
        ), 200 );
    }

    /**
     * Get authorization URL.
     */
    public function get_auth_url( $request ) {
        $user_id = get_current_user_id();
        $url = PFOB_Google_Calendar_Service::get_authorization_url( $user_id );

        if ( ! $url ) {
            return new WP_Error( 'not_configured', 'Google Calendar integration not configured', array( 'status' => 400 ) );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'data' => array( 'auth_url' => $url ),
        ), 200 );
    }

    /**
     * Disconnect Google Calendar.
     */
    public function disconnect( $request ) {
        $user_id = get_current_user_id();
        PFOB_Google_Calendar_Service::disconnect( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Disconnected from Google Calendar',
        ), 200 );
    }

    /**
     * Sync event to Google Calendar.
     */
    public function sync_event( $request ) {
        $event_id = $request->get_param( 'event_id' );
        $user_id = get_current_user_id();

        // Get event
        $event = PFOB_Event::get( $event_id );

        if ( ! $event ) {
            return new WP_Error( 'not_found', 'Event not found', array( 'status' => 404 ) );
        }

        // Sync to Google
        $result = PFOB_Google_Calendar_Service::sync_event_to_google( $event, $user_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Event synced to Google Calendar',
        ), 200 );
    }

    /**
     * Update settings.
     */
    public function update_settings( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        PFOB_Google_Calendar_Service::update_settings( $user_id, $params );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Settings updated',
        ), 200 );
    }
}
