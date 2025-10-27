<?php
/**
 * Analytics REST API Endpoint
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/api
 */

class BCWP_Analytics_Endpoint {

    protected $namespace = 'bcwp/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get project analytics
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/analytics', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_project_analytics' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Get user analytics
        register_rest_route( $this->namespace, '/analytics/user', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_user_analytics' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Get workspace analytics
        register_rest_route( $this->namespace, '/analytics/workspace', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_workspace_analytics' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Get all projects overview
        register_rest_route( $this->namespace, '/analytics/projects', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_all_projects_analytics' ),
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
     * Get project analytics.
     */
    public function get_project_analytics( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $days = $request->get_param( 'days' ) ?: 30;

        $project = BCWP_Project::get( $project_id );

        if ( ! $project ) {
            return new WP_Error( 'not_found', 'Project not found', array( 'status' => 404 ) );
        }

        // Check permission
        if ( ! BCWP_Permission_Service::can_view_project( get_current_user_id(), $project_id ) ) {
            return new WP_Error( 'forbidden', 'No permission', array( 'status' => 403 ) );
        }

        $analytics = BCWP_Analytics_Service::get_project_analytics( $project_id, $days );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $analytics,
        ), 200 );
    }

    /**
     * Get user analytics.
     */
    public function get_user_analytics( $request ) {
        $days = $request->get_param( 'days' ) ?: 30;
        $user_id = get_current_user_id();

        $analytics = BCWP_Analytics_Service::get_user_analytics( $user_id, $days );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $analytics,
        ), 200 );
    }

    /**
     * Get workspace analytics.
     */
    public function get_workspace_analytics( $request ) {
        $days = $request->get_param( 'days' ) ?: 30;

        $analytics = BCWP_Analytics_Service::get_workspace_analytics( $days );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $analytics,
        ), 200 );
    }

    /**
     * Get all projects analytics.
     */
    public function get_all_projects_analytics( $request ) {
        $days = $request->get_param( 'days' ) ?: 30;
        $user_id = get_current_user_id();

        $analytics = BCWP_Analytics_Service::get_all_projects_analytics( $user_id, $days );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $analytics,
        ), 200 );
    }
}
