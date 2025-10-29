<?php
/**
 * Analytics Service
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_Analytics_Service {

    /**
     * Get project analytics.
     */
    public static function get_project_analytics( $project_id, $days = 30 ) {
        global $wpdb;

        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        return array(
            'overview' => self::get_project_overview( $project_id ),
            'activity_trend' => self::get_activity_trend( $project_id, $days ),
            'todo_completion' => self::get_todo_completion_stats( $project_id, $days ),
            'team_activity' => self::get_team_activity( $project_id, $days ),
            'message_engagement' => self::get_message_engagement( $project_id, $days ),
        );
    }

    /**
     * Get all projects analytics.
     */
    public static function get_all_projects_analytics( $user_id, $days = 30 ) {
        $projects = PFOB_Project::get_user_projects( $user_id );
        $analytics = array();

        foreach ( $projects as $project ) {
            $analytics[] = array(
                'project' => $project,
                'stats' => self::get_project_overview( $project->id ),
            );
        }

        return $analytics;
    }

    /**
     * Get project overview stats.
     */
    private static function get_project_overview( $project_id ) {
        global $wpdb;

        $total_messages = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_messages WHERE project_id = %d",
            $project_id
        ) );

        $total_todos = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos WHERE project_id = %d",
            $project_id
        ) );

        $completed_todos = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos WHERE project_id = %d AND is_completed = 1",
            $project_id
        ) );

        $total_events = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_events WHERE project_id = %d",
            $project_id
        ) );

        $total_documents = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_documents WHERE project_id = %d",
            $project_id
        ) );

        $total_members = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_project_members WHERE project_id = %d",
            $project_id
        ) );

        $completion_rate = $total_todos > 0 ? round( ( $completed_todos / $total_todos ) * 100, 1 ) : 0;

        return array(
            'total_messages' => (int) $total_messages,
            'total_todos' => (int) $total_todos,
            'completed_todos' => (int) $completed_todos,
            'pending_todos' => (int) ( $total_todos - $completed_todos ),
            'total_events' => (int) $total_events,
            'total_documents' => (int) $total_documents,
            'total_members' => (int) $total_members,
            'completion_rate' => $completion_rate,
        );
    }

    /**
     * Get activity trend over time.
     */
    private static function get_activity_trend( $project_id, $days = 30 ) {
        global $wpdb;

        $data = array();
        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        // Get activities grouped by day
        for ( $i = $days - 1; $i >= 0; $i-- ) {
            $date = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
            $next_date = gmdate( 'Y-m-d', strtotime( "-{$i} days + 1 day" ) );

            $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_activities
                WHERE project_id = %d
                AND created_at >= %s
                AND created_at < %s",
                $project_id,
                $date . ' 00:00:00',
                $next_date . ' 00:00:00'
            ) );

            $data[] = array(
                'date' => $date,
                'count' => (int) $count,
            );
        }

        return $data;
    }

    /**
     * Get todo completion stats.
     */
    private static function get_todo_completion_stats( $project_id, $days = 30 ) {
        global $wpdb;

        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $data = array();

        // Completed per day
        for ( $i = $days - 1; $i >= 0; $i-- ) {
            $date = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
            $next_date = gmdate( 'Y-m-d', strtotime( "-{$i} days + 1 day" ) );

            $completed = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos
                WHERE project_id = %d
                AND is_completed = 1
                AND updated_at >= %s
                AND updated_at < %s",
                $project_id,
                $date . ' 00:00:00',
                $next_date . ' 00:00:00'
            ) );

            $data[] = array(
                'date' => $date,
                'completed' => (int) $completed,
            );
        }

        return $data;
    }

    /**
     * Get team activity breakdown.
     */
    private static function get_team_activity( $project_id, $days = 30 ) {
        global $wpdb;

        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT user_id, COUNT(*) as activity_count
            FROM {$wpdb->prefix}pfob_activities
            WHERE project_id = %d
            AND created_at >= %s
            GROUP BY user_id
            ORDER BY activity_count DESC
            LIMIT 10",
            $project_id,
            $since
        ) );

        $data = array();
        foreach ( $results as $row ) {
            $data[] = array(
                'user_id' => $row->user_id,
                'user_name' => PFOB_Auth_Service::get_user_display_name( $row->user_id ),
                'activity_count' => (int) $row->activity_count,
            );
        }

        return $data;
    }

    /**
     * Get message engagement stats.
     */
    private static function get_message_engagement( $project_id, $days = 30 ) {
        global $wpdb;

        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        // Top messages by comments
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT m.id, m.title, m.created_by, COUNT(c.id) as comment_count
            FROM {$wpdb->prefix}pfob_messages m
            LEFT JOIN {$wpdb->prefix}pfob_comments c ON c.subject_id = m.id AND c.subject_type = 'message'
            WHERE m.project_id = %d
            AND m.created_at >= %s
            GROUP BY m.id
            ORDER BY comment_count DESC
            LIMIT 5",
            $project_id,
            $since
        ) );

        $data = array();
        foreach ( $results as $row ) {
            $data[] = array(
                'message_id' => $row->id,
                'message_title' => $row->title,
                'author' => PFOB_Auth_Service::get_user_display_name( $row->created_by ),
                'comment_count' => (int) $row->comment_count,
            );
        }

        return $data;
    }

    /**
     * Get user personal analytics.
     */
    public static function get_user_analytics( $user_id, $days = 30 ) {
        global $wpdb;

        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $assigned_todos = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos
            WHERE assigned_to = %d
            AND created_at >= %s",
            $user_id,
            $since
        ) );

        $completed_todos = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos
            WHERE assigned_to = %d
            AND is_completed = 1
            AND updated_at >= %s",
            $user_id,
            $since
        ) );

        $messages_created = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_messages
            WHERE created_by = %d
            AND created_at >= %s",
            $user_id,
            $since
        ) );

        $comments_made = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_comments
            WHERE user_id = %d
            AND created_at >= %s",
            $user_id,
            $since
        ) );

        $completion_rate = $assigned_todos > 0 ? round( ( $completed_todos / $assigned_todos ) * 100, 1 ) : 0;

        return array(
            'assigned_todos' => (int) $assigned_todos,
            'completed_todos' => (int) $completed_todos,
            'messages_created' => (int) $messages_created,
            'comments_made' => (int) $comments_made,
            'completion_rate' => $completion_rate,
        );
    }

    /**
     * Get workspace-wide analytics.
     */
    public static function get_workspace_analytics( $days = 30 ) {
        global $wpdb;

        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $total_projects = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_projects"
        );

        $total_users = $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}pfob_project_members"
        );

        $total_messages = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_messages"
        );

        $total_todos = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos"
        );

        $completed_todos = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos WHERE is_completed = 1"
        );

        $recent_activity = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_activities WHERE created_at >= %s",
            $since
        ) );

        $most_active_project = $wpdb->get_row(
            "SELECT project_id, COUNT(*) as activity_count
            FROM {$wpdb->prefix}pfob_activities
            GROUP BY project_id
            ORDER BY activity_count DESC
            LIMIT 1"
        );

        $most_active_project_name = null;
        if ( $most_active_project ) {
            $project = PFOB_Project::get( $most_active_project->project_id );
            $most_active_project_name = $project ? $project->name : null;
        }

        return array(
            'total_projects' => (int) $total_projects,
            'total_users' => (int) $total_users,
            'total_messages' => (int) $total_messages,
            'total_todos' => (int) $total_todos,
            'completed_todos' => (int) $completed_todos,
            'recent_activity' => (int) $recent_activity,
            'most_active_project' => $most_active_project_name,
        );
    }
}
