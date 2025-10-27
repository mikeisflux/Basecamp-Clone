<?php
/**
 * REST API base class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/api
 */

class BCWP_REST_API {

    protected $namespace = 'bcwp/v1';

    public function register_routes() {
        $endpoints = array(
            new BCWP_Projects_Endpoint(),
            new BCWP_Messages_Endpoint(),
            new BCWP_Todos_Endpoint(),
            new BCWP_Chat_Endpoint(),
            new BCWP_Activities_Endpoint(),
            new BCWP_Search_Endpoint(),
        );

        foreach ( $endpoints as $endpoint ) {
            $endpoint->register_routes();
        }
    }

    /**
     * Check if user is authenticated.
     */
    public function check_permission( $request ) {
        return is_user_logged_in();
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
