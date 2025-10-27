<?php
/**
 * Activities REST API Endpoint
 *
 * Handles activity feed and polling
 */

class BCWP_Activities_Endpoint {

    /**
     * Register routes
     */
    public function register_routes() {
        // Get activities
        register_rest_route( 'bcwp/v1', '/activities', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_activities' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Poll for new activities
        register_rest_route( 'bcwp/v1', '/activities/poll', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'poll_activities' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );
    }

    /**
     * Get activities
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_activities( $request ) {
        $user_id = get_current_user_id();

        $limit = $request->get_param( 'limit' ) ?: 50;
        $offset = $request->get_param( 'offset' ) ?: 0;
        $project_id = $request->get_param( 'project_id' );
        $type = $request->get_param( 'type' );
        $action = $request->get_param( 'action' );

        $args = array(
            'limit'  => min( $limit, 100 ),
            'offset' => $offset,
        );

        if ( $project_id ) {
            $args['project_id'] = $project_id;
        }

        if ( $type ) {
            $args['subject_type'] = $type;
        }

        if ( $action ) {
            $args['action_type'] = $action;
        }

        $activities = BCWP_Activity::get_user_timeline( $user_id, $args['limit'] );

        // Format activities for frontend
        $formatted = array_map( array( $this, 'format_activity' ), $activities );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $formatted,
        ) );
    }

    /**
     * Poll for new activities
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function poll_activities( $request ) {
        $user_id = get_current_user_id();
        $since_id = $request->get_param( 'since_id' ) ?: 0;

        $activities = BCWP_Activity::get_since( $since_id, $user_id );

        // Format activities for frontend
        $formatted = array_map( array( $this, 'format_activity' ), $activities );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $formatted,
        ) );
    }

    /**
     * Format activity for API response
     *
     * @param object $activity Activity object
     * @return array Formatted activity
     */
    private function format_activity( $activity ) {
        $project = null;
        if ( $activity->project_id ) {
            $project = BCWP_Project::get( $activity->project_id );
        }

        return array(
            'id'           => $activity->id,
            'project_id'   => $activity->project_id,
            'user_id'      => $activity->user_id,
            'user_name'    => BCWP_Auth_Service::get_user_display_name( $activity->user_id ),
            'user_avatar'  => get_avatar_url( $activity->user_id, array( 'size' => 32 ) ),
            'action_type'  => $activity->action_type,
            'subject_type' => $activity->subject_type,
            'subject_id'   => $activity->subject_id,
            'description'  => BCWP_Activity::format_description( $activity ),
            'icon'         => BCWP_Activity::get_icon( $activity ),
            'link'         => BCWP_Activity::get_link( $activity ),
            'project_name' => $project ? $project->name : null,
            'project_url'  => $project ? home_url( '/basecamp/projects/' . $project->slug ) : null,
            'time_ago'     => BCWP_Template::format_date( $activity->created_at ),
            'created_at'   => $activity->created_at,
        );
    }

    /**
     * Check if user has permission
     *
     * @return bool
     */
    public function check_permission() {
        return is_user_logged_in();
    }
}
