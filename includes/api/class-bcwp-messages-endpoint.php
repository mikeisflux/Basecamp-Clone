<?php
/**
 * Messages REST API endpoint.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/api
 */

class BCWP_Messages_Endpoint extends BCWP_REST_API {

    public function register_routes() {
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/messages', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_messages' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_message' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/messages/(?P<id>\d+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_message' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_message' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_message' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );
    }

    public function get_messages( $request ) {
        $project_id = $request->get_param( 'project_id' );

        if ( ! BCWP_Permission_Service::can_access_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'basecamp-wp-pro' ), 'access_denied', 403 );
        }

        $messages = BCWP_Message::get_project_messages( $project_id );

        return $this->success_response( $messages );
    }

    public function create_message( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $params = $request->get_json_params();

        if ( ! BCWP_Permission_Service::can_create_content( $project_id, 'message' ) ) {
            return $this->error_response( __( 'Access denied.', 'basecamp-wp-pro' ), 'access_denied', 403 );
        }

        $message_data = array(
            'project_id' => $project_id,
            'title'      => sanitize_text_field( $params['title'] ),
            'content'    => wp_kses_post( $params['content'] ),
            'category'   => isset( $params['category'] ) ? sanitize_text_field( $params['category'] ) : null,
        );

        $message_id = BCWP_Message::create( $message_data );

        if ( ! $message_id ) {
            return $this->error_response( __( 'Failed to create message.', 'basecamp-wp-pro' ), 'create_failed' );
        }

        $message = BCWP_Message::get( $message_id );

        return $this->success_response( $message, __( 'Message created successfully.', 'basecamp-wp-pro' ), 201 );
    }

    public function get_message( $request ) {
        $message_id = $request->get_param( 'id' );
        $message = BCWP_Message::get( $message_id );

        if ( ! $message ) {
            return $this->error_response( __( 'Message not found.', 'basecamp-wp-pro' ), 'not_found', 404 );
        }

        if ( ! BCWP_Permission_Service::can_access_project( $message->project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'basecamp-wp-pro' ), 'access_denied', 403 );
        }

        return $this->success_response( $message );
    }

    public function update_message( $request ) {
        $message_id = $request->get_param( 'id' );
        $params = $request->get_json_params();

        if ( ! BCWP_Permission_Service::can_edit_content( 'message', $message_id ) ) {
            return $this->error_response( __( 'Access denied.', 'basecamp-wp-pro' ), 'access_denied', 403 );
        }

        $update_data = array();
        if ( isset( $params['title'] ) ) {
            $update_data['title'] = sanitize_text_field( $params['title'] );
        }
        if ( isset( $params['content'] ) ) {
            $update_data['content'] = wp_kses_post( $params['content'] );
        }

        $result = BCWP_Message::update( $message_id, $update_data );

        return $this->success_response( $result, __( 'Message updated successfully.', 'basecamp-wp-pro' ) );
    }

    public function delete_message( $request ) {
        $message_id = $request->get_param( 'id' );

        if ( ! BCWP_Permission_Service::can_delete_content( 'message', $message_id ) ) {
            return $this->error_response( __( 'Access denied.', 'basecamp-wp-pro' ), 'access_denied', 403 );
        }

        $result = BCWP_Message::delete( $message_id );

        return $this->success_response( null, __( 'Message deleted successfully.', 'basecamp-wp-pro' ) );
    }
}
