<?php
/**
 * Projects REST API endpoint.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Projects_Endpoint extends PFOB_REST_API {

    public function register_routes() {
        // Get all projects
        register_rest_route( $this->namespace, '/projects', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_projects' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Create project
        register_rest_route( $this->namespace, '/projects', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'create_project' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Get single project
        register_rest_route( $this->namespace, '/projects/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_project' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Update project
        register_rest_route( $this->namespace, '/projects/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => array( $this, 'update_project' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Delete project
        register_rest_route( $this->namespace, '/projects/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array( $this, 'delete_project' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Get project members
        register_rest_route( $this->namespace, '/projects/(?P<id>\d+)/members', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_members' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Add project member
        register_rest_route( $this->namespace, '/projects/(?P<id>\d+)/members', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'add_member' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );
    }

    public function get_projects( $request ) {
        $user_id = get_current_user_id();
        $projects = PFOB_Project::get_user_projects( $user_id );

        return $this->success_response( $projects );
    }

    public function create_project( $request ) {
        $params = $request->get_json_params();

        // Validate required fields
        if ( empty( $params['name'] ) ) {
            return $this->error_response( __( 'Project name is required.', 'projectfob' ), 'missing_name' );
        }

        $project_data = array(
            'name'        => sanitize_text_field( $params['name'] ),
            'description' => isset( $params['description'] ) ? sanitize_textarea_field( $params['description'] ) : '',
            'start_date'  => isset( $params['start_date'] ) ? $params['start_date'] : null,
            'end_date'    => isset( $params['end_date'] ) ? $params['end_date'] : null,
        );

        $project_id = PFOB_Project::create( $project_data );

        if ( ! $project_id ) {
            return $this->error_response( __( 'Failed to create project.', 'projectfob' ), 'create_failed' );
        }

        $project = PFOB_Project::get( $project_id );

        return $this->success_response(
            $project,
            __( 'Project created successfully.', 'projectfob' ),
            201
        );
    }

    public function get_project( $request ) {
        $project_id = $request->get_param( 'id' );

        // Check permission
        if ( ! PFOB_Permission_Service::can_access_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'projectfob' ), 'access_denied', 403 );
        }

        $project = PFOB_Project::get( $project_id );

        if ( ! $project ) {
            return $this->error_response( __( 'Project not found.', 'projectfob' ), 'not_found', 404 );
        }

        return $this->success_response( $project );
    }

    public function update_project( $request ) {
        $project_id = $request->get_param( 'id' );
        $params = $request->get_json_params();

        // Check permission
        if ( ! PFOB_Permission_Service::can_edit_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'projectfob' ), 'access_denied', 403 );
        }

        $update_data = array();

        if ( isset( $params['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $params['name'] );
        }
        if ( isset( $params['description'] ) ) {
            $update_data['description'] = sanitize_textarea_field( $params['description'] );
        }
        if ( isset( $params['status'] ) ) {
            $update_data['status'] = sanitize_text_field( $params['status'] );
        }

        $result = PFOB_Project::update( $project_id, $update_data );

        if ( $result === false ) {
            return $this->error_response( __( 'Failed to update project.', 'projectfob' ), 'update_failed' );
        }

        $project = PFOB_Project::get( $project_id );

        return $this->success_response(
            $project,
            __( 'Project updated successfully.', 'projectfob' )
        );
    }

    public function delete_project( $request ) {
        $project_id = $request->get_param( 'id' );

        // Check permission
        if ( ! PFOB_Permission_Service::can_edit_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'projectfob' ), 'access_denied', 403 );
        }

        $result = PFOB_Project::delete( $project_id );

        if ( $result === false ) {
            return $this->error_response( __( 'Failed to delete project.', 'projectfob' ), 'delete_failed' );
        }

        return $this->success_response(
            null,
            __( 'Project deleted successfully.', 'projectfob' )
        );
    }

    public function get_members( $request ) {
        $project_id = $request->get_param( 'id' );

        // Check permission
        if ( ! PFOB_Permission_Service::can_access_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'projectfob' ), 'access_denied', 403 );
        }

        $members = PFOB_Project::get_members( $project_id );

        return $this->success_response( $members );
    }

    public function add_member( $request ) {
        $project_id = $request->get_param( 'id' );
        $params = $request->get_json_params();

        // Check permission
        if ( ! PFOB_Permission_Service::can_edit_project( $project_id ) ) {
            return $this->error_response( __( 'Access denied.', 'projectfob' ), 'access_denied', 403 );
        }

        if ( empty( $params['user_id'] ) ) {
            return $this->error_response( __( 'User ID is required.', 'projectfob' ), 'missing_user_id' );
        }

        $role = isset( $params['role'] ) ? $params['role'] : 'member';

        $result = PFOB_Project::add_member( $project_id, $params['user_id'], $role );

        if ( ! $result ) {
            return $this->error_response( __( 'Failed to add member.', 'projectfob' ), 'add_failed' );
        }

        return $this->success_response(
            null,
            __( 'Member added successfully.', 'projectfob' )
        );
    }
}
