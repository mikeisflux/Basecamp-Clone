<?php
/**
 * Notifications REST API Endpoint
 *
 * Handles user notifications
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Notifications_Endpoint extends PFOB_REST_API {

    /**
     * Register routes
     */
    public function register_routes() {
        // Get notifications
        register_rest_route( $this->namespace, '/notifications', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_notifications' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'unread_only' => array(
                    'default'           => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'limit' => array(
                    'default'           => 20,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ) );

        // Mark notification as read
        register_rest_route( $this->namespace, '/notifications/(?P<id>\d+)/read', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'mark_as_read' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Mark all notifications as read
        register_rest_route( $this->namespace, '/notifications/mark-all-read', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'mark_all_as_read' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );
    }

    /**
     * Get notifications for current user
     */
    public function get_notifications( $request ) {
        $user_id = get_current_user_id();
        $unread_only = $request->get_param( 'unread_only' );
        $limit = $request->get_param( 'limit' );

        global $wpdb;
        $table_name = $wpdb->prefix . 'pfob_notifications';

        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            // Table doesn't exist yet, return empty array
            return rest_ensure_response( array(
                'success' => true,
                'data'    => array(),
            ) );
        }

        $where = "WHERE user_id = %d";
        $params = array( $user_id );

        if ( $unread_only ) {
            $where .= " AND is_read = 0";
        }

        $query = "SELECT * FROM {$table_name} {$where} ORDER BY created_at DESC LIMIT %d";
        $params[] = $limit;

        $notifications = $wpdb->get_results(
            $wpdb->prepare( $query, $params )
        );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $notifications ? $notifications : array(),
        ) );
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read( $request ) {
        $notification_id = $request->get_param( 'id' );
        $user_id = get_current_user_id();

        global $wpdb;
        $table_name = $wpdb->prefix . 'pfob_notifications';

        // Verify notification belongs to user
        $notification = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d AND user_id = %d",
                $notification_id,
                $user_id
            )
        );

        if ( ! $notification ) {
            return new WP_Error( 'not_found', 'Notification not found', array( 'status' => 404 ) );
        }

        $wpdb->update(
            $table_name,
            array( 'is_read' => 1 ),
            array( 'id' => $notification_id )
        );

        return rest_ensure_response( array(
            'success' => true,
            'message' => 'Notification marked as read',
        ) );
    }

    /**
     * Mark all notifications as read
     */
    public function mark_all_as_read( $request ) {
        $user_id = get_current_user_id();

        global $wpdb;
        $table_name = $wpdb->prefix . 'pfob_notifications';

        $wpdb->update(
            $table_name,
            array( 'is_read' => 1 ),
            array( 'user_id' => $user_id, 'is_read' => 0 )
        );

        return rest_ensure_response( array(
            'success' => true,
            'message' => 'All notifications marked as read',
        ) );
    }
}
