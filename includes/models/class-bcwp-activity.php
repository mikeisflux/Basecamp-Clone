<?php
/**
 * Activity Model
 *
 * Handles activity logging and retrieval
 */

class BCWP_Activity {

    /**
     * Log an activity
     *
     * @param array $data Activity data
     * @return int|false Activity ID or false on failure
     */
    public static function log( $data ) {
        global $wpdb;

        $defaults = array(
            'project_id'   => null,
            'user_id'      => get_current_user_id(),
            'action_type'  => 'created',
            'subject_type' => '',
            'subject_id'   => 0,
            'description'  => '',
            'changes'      => null,
            'metadata'     => null,
            'is_public'    => 1,
            'created_at'   => current_time( 'mysql' ),
        );

        $data = wp_parse_args( $data, $defaults );

        // Convert arrays to JSON
        if ( is_array( $data['changes'] ) ) {
            $data['changes'] = json_encode( $data['changes'] );
        }
        if ( is_array( $data['metadata'] ) ) {
            $data['metadata'] = json_encode( $data['metadata'] );
        }

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'bcwp_activities',
            $data,
            array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s' )
        );

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Get recent activities
     *
     * @param array $args Query arguments
     * @return array Activities
     */
    public static function get_recent( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'project_id'   => null,
            'user_id'      => null,
            'action_type'  => null,
            'subject_type' => null,
            'limit'        => 50,
            'offset'       => 0,
            'is_public'    => null,
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $values = array();

        if ( ! is_null( $args['project_id'] ) ) {
            $where[] = 'project_id = %d';
            $values[] = $args['project_id'];
        }

        if ( ! is_null( $args['user_id'] ) ) {
            $where[] = 'user_id = %d';
            $values[] = $args['user_id'];
        }

        if ( ! is_null( $args['action_type'] ) ) {
            $where[] = 'action_type = %s';
            $values[] = $args['action_type'];
        }

        if ( ! is_null( $args['subject_type'] ) ) {
            $where[] = 'subject_type = %s';
            $values[] = $args['subject_type'];
        }

        if ( ! is_null( $args['is_public'] ) ) {
            $where[] = 'is_public = %d';
            $values[] = $args['is_public'];
        }

        $where_clause = implode( ' AND ', $where );

        $values[] = $args['limit'];
        $values[] = $args['offset'];

        $sql = "SELECT * FROM {$wpdb->prefix}bcwp_activities
                WHERE {$where_clause}
                ORDER BY created_at DESC
                LIMIT %d OFFSET %d";

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Get activities for a user's timeline
     *
     * @param int $user_id User ID
     * @param int $limit Limit
     * @return array Activities
     */
    public static function get_user_timeline( $user_id, $limit = 50 ) {
        global $wpdb;

        // Get projects user is a member of
        $project_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT project_id FROM {$wpdb->prefix}bcwp_project_members WHERE user_id = %d",
            $user_id
        ) );

        if ( empty( $project_ids ) ) {
            return array();
        }

        $placeholders = implode( ',', array_fill( 0, count( $project_ids ), '%d' ) );

        $sql = "SELECT * FROM {$wpdb->prefix}bcwp_activities
                WHERE (project_id IN ({$placeholders}) OR project_id IS NULL)
                AND is_public = 1
                ORDER BY created_at DESC
                LIMIT %d";

        $values = array_merge( $project_ids, array( $limit ) );

        return $wpdb->get_results( $wpdb->prepare( $sql, $values ) );
    }

    /**
     * Get activities since a specific ID (for polling)
     *
     * @param int $since_id Last activity ID
     * @param int $user_id User ID
     * @return array New activities
     */
    public static function get_since( $since_id, $user_id ) {
        global $wpdb;

        // Get projects user is a member of
        $project_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT project_id FROM {$wpdb->prefix}bcwp_project_members WHERE user_id = %d",
            $user_id
        ) );

        if ( empty( $project_ids ) ) {
            return array();
        }

        $placeholders = implode( ',', array_fill( 0, count( $project_ids ), '%d' ) );

        $sql = "SELECT * FROM {$wpdb->prefix}bcwp_activities
                WHERE id > %d
                AND (project_id IN ({$placeholders}) OR project_id IS NULL)
                AND is_public = 1
                ORDER BY created_at DESC";

        $values = array_merge( array( $since_id ), $project_ids );

        return $wpdb->get_results( $wpdb->prepare( $sql, $values ) );
    }

    /**
     * Get activity count
     *
     * @param array $args Query arguments
     * @return int Count
     */
    public static function get_count( $args = array() ) {
        global $wpdb;

        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['project_id'] ) ) {
            $where[] = 'project_id = %d';
            $values[] = $args['project_id'];
        }

        if ( ! empty( $args['user_id'] ) ) {
            $where[] = 'user_id = %d';
            $values[] = $args['user_id'];
        }

        $where_clause = implode( ' AND ', $where );

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}bcwp_activities WHERE {$where_clause}";

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Format activity description
     *
     * @param object $activity Activity object
     * @return string Formatted description
     */
    public static function format_description( $activity ) {
        $user_name = BCWP_Auth_Service::get_user_display_name( $activity->user_id );
        $action_map = array(
            'created'   => 'created',
            'updated'   => 'updated',
            'deleted'   => 'deleted',
            'completed' => 'completed',
            'assigned'  => 'assigned',
            'commented' => 'commented on',
            'uploaded'  => 'uploaded',
            'moved'     => 'moved',
        );

        $action = isset( $action_map[ $activity->action_type ] )
            ? $action_map[ $activity->action_type ]
            : $activity->action_type;

        $subject_map = array(
            'project'  => 'a project',
            'message'  => 'a message',
            'todo'     => 'a to-do',
            'list'     => 'a to-do list',
            'document' => 'a file',
            'chat'     => 'a chat message',
            'event'    => 'an event',
            'card'     => 'a card',
            'comment'  => 'a comment',
        );

        $subject = isset( $subject_map[ $activity->subject_type ] )
            ? $subject_map[ $activity->subject_type ]
            : $activity->subject_type;

        if ( ! empty( $activity->description ) ) {
            return $activity->description;
        }

        return "{$user_name} {$action} {$subject}";
    }

    /**
     * Get activity icon
     *
     * @param object $activity Activity object
     * @return string Icon emoji
     */
    public static function get_icon( $activity ) {
        $icons = array(
            'project'  => '📁',
            'message'  => '💬',
            'todo'     => '✅',
            'list'     => '📝',
            'document' => '📄',
            'chat'     => '💬',
            'event'    => '📅',
            'card'     => '🎴',
            'comment'  => '💭',
        );

        return isset( $icons[ $activity->subject_type ] )
            ? $icons[ $activity->subject_type ]
            : '📌';
    }

    /**
     * Get activity link
     *
     * @param object $activity Activity object
     * @return string|null URL to the subject
     */
    public static function get_link( $activity ) {
        $project_id = $activity->project_id;

        if ( ! $project_id ) {
            return null;
        }

        $project = BCWP_Project::get( $project_id );
        if ( ! $project ) {
            return null;
        }

        $base_url = home_url( "/basecamp/projects/{$project->slug}" );

        switch ( $activity->subject_type ) {
            case 'message':
                return "{$base_url}/messages/{$activity->subject_id}";
            case 'todo':
            case 'list':
                return "{$base_url}/todos";
            case 'document':
                return "{$base_url}/documents";
            case 'chat':
                return "{$base_url}/chat";
            case 'event':
                return "{$base_url}/schedule";
            case 'card':
                return "{$base_url}/cards";
            default:
                return $base_url;
        }
    }
}
