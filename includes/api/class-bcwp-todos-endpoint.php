<?php
/**
 * Todos REST API endpoint.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/api
 */

class BCWP_Todos_Endpoint extends BCWP_REST_API {

    public function register_routes() {
        // Todo lists
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/todo-lists', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_lists' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_list' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        // Todo items
        register_rest_route( $this->namespace, '/todo-lists/(?P<list_id>\d+)/items', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_item' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/todo-items/(?P<id>\d+)', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_item' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/todo-items/(?P<id>\d+)/complete', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'complete_item' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );
    }

    public function get_lists( $request ) {
        $project_id = $request->get_param( 'project_id' );

        if ( ! BCWP_Permission_Service::can_access_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'basecamp-wp-pro' ), 'access_denied', 403 );
        }

        $lists = BCWP_Todo::get_project_lists( $project_id );

        return $this->success_response( $lists );
    }

    public function create_list( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $params = $request->get_json_params();

        if ( ! BCWP_Permission_Service::can_create_content( $project_id, 'todo' ) ) {
            return $this->error_response( __( 'Access denied.', 'basecamp-wp-pro' ), 'access_denied', 403 );
        }

        $list_data = array(
            'project_id'  => $project_id,
            'name'        => sanitize_text_field( $params['name'] ),
            'description' => isset( $params['description'] ) ? sanitize_textarea_field( $params['description'] ) : '',
        );

        $list_id = BCWP_Todo::create_list( $list_data );

        return $this->success_response(
            BCWP_Todo::get_list( $list_id ),
            __( 'Todo list created successfully.', 'basecamp-wp-pro' ),
            201
        );
    }

    public function get_items( $request ) {
        $list_id = $request->get_param( 'list_id' );
        $items = BCWP_Todo::get_list_items( $list_id );

        return $this->success_response( $items );
    }

    public function create_item( $request ) {
        $list_id = $request->get_param( 'list_id' );
        $params = $request->get_json_params();

        $item_data = array(
            'list_id'     => $list_id,
            'content'     => sanitize_text_field( $params['content'] ),
            'description' => isset( $params['description'] ) ? sanitize_textarea_field( $params['description'] ) : '',
            'assignee_id' => isset( $params['assignee_id'] ) ? intval( $params['assignee_id'] ) : null,
            'due_date'    => isset( $params['due_date'] ) ? $params['due_date'] : null,
        );

        $item_id = BCWP_Todo::create_item( $item_data );

        return $this->success_response(
            BCWP_Todo::get_item( $item_id ),
            __( 'Todo item created successfully.', 'basecamp-wp-pro' ),
            201
        );
    }

    public function update_item( $request ) {
        $item_id = $request->get_param( 'id' );
        $params = $request->get_json_params();

        $update_data = array();
        if ( isset( $params['content'] ) ) {
            $update_data['content'] = sanitize_text_field( $params['content'] );
        }
        if ( isset( $params['is_completed'] ) ) {
            $update_data['is_completed'] = intval( $params['is_completed'] );
        }

        $result = BCWP_Todo::update_item( $item_id, $update_data );

        return $this->success_response( $result, __( 'Todo item updated successfully.', 'basecamp-wp-pro' ) );
    }

    public function delete_item( $request ) {
        $item_id = $request->get_param( 'id' );
        $result = BCWP_Todo::delete_item( $item_id );

        return $this->success_response( null, __( 'Todo item deleted successfully.', 'basecamp-wp-pro' ) );
    }

    public function complete_item( $request ) {
        $item_id = $request->get_param( 'id' );
        $result = BCWP_Todo::complete_item( $item_id );

        return $this->success_response( $result, __( 'Todo item completed!', 'basecamp-wp-pro' ) );
    }
}
