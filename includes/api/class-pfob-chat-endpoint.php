<?php
/**
 * Chat REST API endpoint.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Chat_Endpoint extends PFOB_REST_API {

    public function register_routes() {
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/chat', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_messages' ),
                'permission_callback' => array( $this, 'check_permission_with_subscription' ),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_message' ),
                'permission_callback' => array( $this, 'check_permission_with_subscription' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/chat/poll', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'poll_messages' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );
    }

    public function get_messages( $request ) {
        $project_id = $request->get_param( 'project_id' );

        if ( ! PFOB_Permission_Service::can_access_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'projectfob' ), 'access_denied', 403 );
        }

        $limit = $request->get_param( 'limit' ) ?? 50;
        $messages = PFOB_Chat::get_project_messages( $project_id, $limit );

        return $this->success_response( $messages );
    }

    public function create_message( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $params = $request->get_json_params();

        if ( ! PFOB_Permission_Service::can_create_content( $project_id, 'chat' ) ) {
            return $this->error_response( __( 'Access denied.', 'projectfob' ), 'access_denied', 403 );
        }

        $message_data = array(
            'project_id' => $project_id,
            'message'    => sanitize_textarea_field( $params['message'] ),
        );

        $message_id = PFOB_Chat::create_message( $message_data );

        if ( ! $message_id ) {
            return $this->error_response( __( 'Failed to send message.', 'projectfob' ), 'create_failed' );
        }

        $message = PFOB_Chat::get_message( $message_id );

        // Broadcast via WebSocket if enabled
        PFOB_WebSocket_Service::broadcast_chat_message( $project_id, $message );

        return $this->success_response( $message, '', 201 );
    }

    public function poll_messages( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $since_id = $request->get_param( 'since_id' ) ?? 0;

        if ( ! PFOB_Permission_Service::can_access_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'projectfob' ), 'access_denied', 403 );
        }

        $messages = PFOB_Chat::get_messages_since( $project_id, $since_id );

        return $this->success_response( $messages );
    }
}
