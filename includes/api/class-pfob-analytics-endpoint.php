<?php
/**
 * Analytics REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Analytics_Endpoint extends PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get project analytics
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/analytics', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_project_analytics' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Get user analytics
        register_rest_route( $this->namespace, '/analytics/user', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_user_analytics' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Get workspace analytics
        register_rest_route( $this->namespace, '/analytics/workspace', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_workspace_analytics' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Get all projects overview
        register_rest_route( $this->namespace, '/analytics/projects', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_all_projects_analytics' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Get advanced analytics (Business+ plans)
        register_rest_route( $this->namespace, '/analytics/advanced', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_advanced_analytics' ),
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
     * Get project analytics.
     */
    public function get_project_analytics( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $days = $request->get_param( 'days' ) ?: 30;

        $project = PFOB_Project::get( $project_id );

        if ( ! $project ) {
            return new WP_Error( 'not_found', 'Project not found', array( 'status' => 404 ) );
        }

        // Check permission
        if ( ! PFOB_Permission_Service::can_view_project( get_current_user_id(), $project_id ) ) {
            return new WP_Error( 'forbidden', 'No permission', array( 'status' => 403 ) );
        }

        $analytics = PFOB_Analytics_Service::get_project_analytics( $project_id, $days );

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

        $analytics = PFOB_Analytics_Service::get_user_analytics( $user_id, $days );

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

        $analytics = PFOB_Analytics_Service::get_workspace_analytics( $days );

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

        $analytics = PFOB_Analytics_Service::get_all_projects_analytics( $user_id, $days );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $analytics,
        ), 200 );
    }

    /**
     * Get advanced analytics (Business+ plans only).
     */
    public function get_advanced_analytics( $request ) {
        $user_id = get_current_user_id();

        // Check if user has advanced analytics feature
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );
        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription', array( 'status' => 403 ) );
        }

        $plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

        if ( ! $plan || empty( $plan['features']['advanced_analytics'] ) ) {
            return new WP_Error( 'feature_unavailable', 'Advanced Analytics is only available on Business and Enterprise plans', array( 'status' => 403 ) );
        }

        $days = $request->get_param( 'days' ) ?: 30;
        $start_date = $request->get_param( 'start' );
        $end_date = $request->get_param( 'end' );

        $analytics = PFOB_Analytics_Service::get_advanced_analytics( $user_id, $days, $start_date, $end_date );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $analytics,
        ), 200 );
    }
}
