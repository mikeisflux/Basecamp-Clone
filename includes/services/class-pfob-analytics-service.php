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

    /**
     * Get advanced analytics (Business+ feature).
     */
    public static function get_advanced_analytics( $user_id, $days = 30, $start_date = null, $end_date = null ) {
        global $wpdb;

        // Determine date range
        if ( $start_date && $end_date ) {
            $since = gmdate( 'Y-m-d H:i:s', strtotime( $start_date ) );
            $until = gmdate( 'Y-m-d H:i:s', strtotime( $end_date . ' +1 day' ) );
        } else {
            $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
            $until = gmdate( 'Y-m-d H:i:s' );
        }

        // Get KPIs with trends
        $kpis = self::get_advanced_kpis( $user_id, $since, $until );

        // Get trend data for charts
        $trends = self::get_advanced_trends( $user_id, $since, $until );

        // Get team performance comparison
        $team = self::get_team_performance( $user_id, $since, $until );

        // Get activity heatmap data
        $heatmap = self::get_activity_heatmap( $user_id, $since, $until );

        // Get detailed reports
        $reports = self::get_detailed_reports( $user_id, $since, $until );

        return array(
            'kpis'     => $kpis,
            'trends'   => $trends,
            'team'     => $team,
            'heatmap'  => $heatmap,
            'reports'  => $reports,
        );
    }

    /**
     * Get advanced KPIs with trend indicators.
     */
    private static function get_advanced_kpis( $user_id, $since, $until ) {
        global $wpdb;

        // Calculate previous period for trend comparison
        $period_length = strtotime( $until ) - strtotime( $since );
        $prev_since = gmdate( 'Y-m-d H:i:s', strtotime( $since ) - $period_length );
        $prev_until = $since;

        // Projects completed
        $projects_completed = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT p.id) FROM {$wpdb->prefix}pfob_projects p
            INNER JOIN {$wpdb->prefix}pfob_project_members pm ON p.id = pm.project_id
            WHERE pm.user_id = %d AND p.status = 'completed'
            AND p.updated_at >= %s AND p.updated_at < %s",
            $user_id, $since, $until
        ) );

        $prev_projects_completed = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT p.id) FROM {$wpdb->prefix}pfob_projects p
            INNER JOIN {$wpdb->prefix}pfob_project_members pm ON p.id = pm.project_id
            WHERE pm.user_id = %d AND p.status = 'completed'
            AND p.updated_at >= %s AND p.updated_at < %s",
            $user_id, $prev_since, $prev_until
        ) );

        // Total activities
        $total_activities = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_activities
            WHERE created_at >= %s AND created_at < %s",
            $since, $until
        ) );

        $prev_activities = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_activities
            WHERE created_at >= %s AND created_at < %s",
            $prev_since, $prev_until
        ) );

        // Team collaboration score
        $collaboration_score = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_messages m
            INNER JOIN {$wpdb->prefix}pfob_projects p ON m.project_id = p.id
            INNER JOIN {$wpdb->prefix}pfob_project_members pm ON p.id = pm.project_id
            WHERE pm.user_id = %d AND m.created_at >= %s AND m.created_at < %s",
            $user_id, $since, $until
        ) );

        $prev_collaboration_score = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_messages m
            INNER JOIN {$wpdb->prefix}pfob_projects p ON m.project_id = p.id
            INNER JOIN {$wpdb->prefix}pfob_project_members pm ON p.id = pm.project_id
            WHERE pm.user_id = %d AND m.created_at >= %s AND m.created_at < %s",
            $user_id, $prev_since, $prev_until
        ) );

        // Calculate trends
        $projects_trend = $prev_projects_completed > 0
            ? round( ( ( $projects_completed - $prev_projects_completed ) / $prev_projects_completed ) * 100, 1 )
            : 0;

        $activities_trend = $prev_activities > 0
            ? round( ( ( $total_activities - $prev_activities ) / $prev_activities ) * 100, 1 )
            : 0;

        $collaboration_trend = $prev_collaboration_score > 0
            ? round( ( ( $collaboration_score - $prev_collaboration_score ) / $prev_collaboration_score ) * 100, 1 )
            : 0;

        return array(
            'projects_completed' => array(
                'value' => (int) $projects_completed,
                'trend' => $projects_trend,
            ),
            'total_activities' => array(
                'value' => (int) $total_activities,
                'trend' => $activities_trend,
            ),
            'collaboration_score' => array(
                'value' => (int) $collaboration_score,
                'trend' => $collaboration_trend,
            ),
        );
    }

    /**
     * Get trend data for charts.
     */
    private static function get_advanced_trends( $user_id, $since, $until ) {
        global $wpdb;

        $data = array();
        $start = strtotime( $since );
        $end = strtotime( $until );
        $days = round( ( $end - $start ) / DAY_IN_SECONDS );

        for ( $i = 0; $i < $days; $i++ ) {
            $date = gmdate( 'Y-m-d', $start + ( $i * DAY_IN_SECONDS ) );
            $next_date = gmdate( 'Y-m-d', $start + ( ( $i + 1 ) * DAY_IN_SECONDS ) );

            $activities = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_activities
                WHERE created_at >= %s AND created_at < %s",
                $date . ' 00:00:00', $next_date . ' 00:00:00'
            ) );

            $data[] = array(
                'date'       => $date,
                'activities' => (int) $activities,
            );
        }

        return $data;
    }

    /**
     * Get team performance comparison.
     */
    private static function get_team_performance( $user_id, $since, $until ) {
        global $wpdb;

        // Get all team members from user's projects
        $members = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT u.ID, u.display_name
            FROM {$wpdb->prefix}users u
            INNER JOIN {$wpdb->prefix}pfob_project_members pm ON u.ID = pm.user_id
            INNER JOIN {$wpdb->prefix}pfob_projects p ON pm.project_id = p.id
            INNER JOIN {$wpdb->prefix}pfob_project_members pm2 ON p.id = pm2.project_id
            WHERE pm2.user_id = %d
            LIMIT 10",
            $user_id
        ) );

        $team_data = array();

        foreach ( $members as $member ) {
            $todos_completed = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos
                WHERE assigned_to = %d AND is_completed = 1
                AND updated_at >= %s AND updated_at < %s",
                $member->ID, $since, $until
            ) );

            $team_data[] = array(
                'name'      => $member->display_name,
                'completed' => (int) $todos_completed,
            );
        }

        return $team_data;
    }

    /**
     * Get activity heatmap data.
     */
    private static function get_activity_heatmap( $user_id, $since, $until ) {
        global $wpdb;

        $heatmap = array();

        for ( $hour = 0; $hour < 24; $hour++ ) {
            $hourly_data = array();

            for ( $day = 0; $day < 7; $day++ ) {
                $count = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_activities
                    WHERE HOUR(created_at) = %d
                    AND DAYOFWEEK(created_at) = %d
                    AND created_at >= %s AND created_at < %s",
                    $hour, $day + 1, $since, $until
                ) );

                $hourly_data[] = (int) $count;
            }

            $heatmap[] = $hourly_data;
        }

        return $heatmap;
    }

    /**
     * Get detailed reports.
     */
    private static function get_detailed_reports( $user_id, $since, $until ) {
        global $wpdb;

        $reports = array();

        // Get all projects with activity in the period
        $projects = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT p.* FROM {$wpdb->prefix}pfob_projects p
            INNER JOIN {$wpdb->prefix}pfob_project_members pm ON p.id = pm.project_id
            INNER JOIN {$wpdb->prefix}pfob_activities a ON p.id = a.project_id
            WHERE pm.user_id = %d AND a.created_at >= %s AND a.created_at < %s
            ORDER BY a.created_at DESC
            LIMIT 20",
            $user_id, $since, $until
        ) );

        foreach ( $projects as $project ) {
            $activities = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_activities
                WHERE project_id = %d AND created_at >= %s AND created_at < %s",
                $project->id, $since, $until
            ) );

            $todos_completed = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_todos
                WHERE project_id = %d AND is_completed = 1
                AND updated_at >= %s AND updated_at < %s",
                $project->id, $since, $until
            ) );

            $messages = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_messages
                WHERE project_id = %d AND created_at >= %s AND created_at < %s",
                $project->id, $since, $until
            ) );

            $reports[] = array(
                'project_name'    => $project->name,
                'activities'      => (int) $activities,
                'todos_completed' => (int) $todos_completed,
                'messages'        => (int) $messages,
            );
        }

        return $reports;
    }
}
