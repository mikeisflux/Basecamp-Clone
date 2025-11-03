<?php
/**
 * REST API base class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

    public function register_routes() {
        $endpoints = array(
            new PFOB_Projects_Endpoint(),
            new PFOB_Messages_Endpoint(),
            new PFOB_Todos_Endpoint(),
            new PFOB_Chat_Endpoint(),
            new PFOB_Activities_Endpoint(),
            new PFOB_Search_Endpoint(),
            new PFOB_Events_Endpoint(),
            new PFOB_Import_Export_Endpoint(),
            new PFOB_Settings_Endpoint(),
            new PFOB_Analytics_Endpoint(),
            new PFOB_Calendar_Integration_Endpoint(),
            new PFOB_Notifications_Endpoint(),
            new PFOB_PayPal_Webhook_Endpoint(),
            new PFOB_Subscription_Endpoint(),
            new PFOB_User_Endpoint(),
            new PFOB_Import_Endpoint(),
            new PFOB_Invitations_Endpoint(),
            new PFOB_White_Label_Endpoint(),
            new PFOB_API_Keys_Endpoint(),
            new PFOB_Integrations_Endpoint(),
        );

        foreach ( $endpoints as $endpoint ) {
            $endpoint->register_routes();
        }

        // Register user settings endpoint (static methods)
        PFOB_User_Settings_Endpoint::register_routes();
    }

    /**
     * Check if user is authenticated.
     */
    public function check_permission( $request ) {
        return is_user_logged_in();
    }

    /**
     * Check if user is authenticated (alias for check_permission).
     */
    public function check_user_permission( $request ) {
        return is_user_logged_in();
    }

    /**
     * Check if user is authenticated and has active subscription.
     */
    public function check_permission_with_subscription( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        return PFOB_Auth_Service::has_active_subscription();
    }

    /**
     * Send success response.
     */
    protected function success_response( $data, $message = '', $status = 200 ) {
        return new WP_REST_Response( array(
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ), $status );
    }

    /**
     * Send error response.
     */
    protected function error_response( $message, $code = 'error', $status = 400 ) {
        return new WP_Error( $code, $message, array( 'status' => $status ) );
    }
}
