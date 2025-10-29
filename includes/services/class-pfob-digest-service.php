<?php
/**
 * Email Digest Service
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_Digest_Service {

    /**
     * Initialize digest scheduling.
     */
    public static function init() {
        // Schedule digest cron jobs
        add_action( 'pfob_daily_digest', array( __CLASS__, 'send_daily_digests' ) );
        add_action( 'pfob_weekly_digest', array( __CLASS__, 'send_weekly_digests' ) );

        // Schedule events if not already scheduled
        if ( ! wp_next_scheduled( 'pfob_daily_digest' ) ) {
            wp_schedule_event( strtotime( 'tomorrow 9:00am' ), 'daily', 'pfob_daily_digest' );
        }

        if ( ! wp_next_scheduled( 'pfob_weekly_digest' ) ) {
            wp_schedule_event( strtotime( 'next Monday 9:00am' ), 'weekly', 'pfob_weekly_digest' );
        }
    }

    /**
     * Send daily digests to all users who have it enabled.
     */
    public static function send_daily_digests() {
        $users = get_users( array(
            'meta_key'   => 'pfob_digest_frequency',
            'meta_value' => 'daily',
        ) );

        foreach ( $users as $user ) {
            self::send_user_digest( $user->ID, 'daily' );
        }
    }

    /**
     * Send weekly digests to all users who have it enabled.
     */
    public static function send_weekly_digests() {
        $users = get_users( array(
            'meta_key'   => 'pfob_digest_frequency',
            'meta_value' => 'weekly',
        ) );

        foreach ( $users as $user ) {
            self::send_user_digest( $user->ID, 'weekly' );
        }
    }

    /**
     * Send digest email to a specific user.
     */
    public static function send_user_digest( $user_id, $frequency = 'daily' ) {
        $user = get_user_by( 'ID', $user_id );
        if ( ! $user ) {
            return false;
        }

        // Get time range based on frequency
        $since = $frequency === 'daily'
            ? gmdate( 'Y-m-d H:i:s', strtotime( '-1 day' ) )
            : gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );

        // Get user's projects
        $projects = PFOB_Project::get_user_projects( $user_id );

        if ( empty( $projects ) ) {
            return false; // No projects to digest
        }

        // Collect activities across all projects
        $digest_data = array(
            'new_messages' => array(),
            'new_todos' => array(),
            'completed_todos' => array(),
            'upcoming_events' => array(),
            'your_assignments' => array(),
        );

        foreach ( $projects as $project ) {
            // New messages
            $messages = self::get_recent_messages( $project->id, $since );
            foreach ( $messages as $message ) {
                $digest_data['new_messages'][] = array(
                    'project' => $project,
                    'message' => $message,
                );
            }

            // New todos
            $todos = self::get_recent_todos( $project->id, $since );
            foreach ( $todos as $todo ) {
                $digest_data['new_todos'][] = array(
                    'project' => $project,
                    'todo' => $todo,
                );
            }

            // Completed todos
            $completed = self::get_recently_completed_todos( $project->id, $since );
            foreach ( $completed as $todo ) {
                $digest_data['completed_todos'][] = array(
                    'project' => $project,
                    'todo' => $todo,
                );
            }

            // Your assignments
            $assignments = self::get_user_assignments( $project->id, $user_id, $since );
            foreach ( $assignments as $todo ) {
                $digest_data['your_assignments'][] = array(
                    'project' => $project,
                    'todo' => $todo,
                );
            }

            // Upcoming events
            $events = self::get_upcoming_events( $project->id, 7 );
            foreach ( $events as $event ) {
                $digest_data['upcoming_events'][] = array(
                    'project' => $project,
                    'event' => $event,
                );
            }
        }

        // Check if there's any activity to report
        $has_activity = false;
        foreach ( $digest_data as $section ) {
            if ( ! empty( $section ) ) {
                $has_activity = true;
                break;
            }
        }

        if ( ! $has_activity ) {
            return false; // No activity to report
        }

        // Generate and send email
        $subject = self::get_digest_subject( $frequency );
        $body = self::generate_digest_html( $user, $digest_data, $frequency );

        return self::send_digest_email( $user->user_email, $subject, $body );
    }

    /**
     * Get recent messages for a project.
     */
    private static function get_recent_messages( $project_id, $since ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pfob_messages
            WHERE project_id = %d
            AND created_at >= %s
            ORDER BY created_at DESC
            LIMIT 10",
            $project_id,
            $since
        ) );
    }

    /**
     * Get recent todos for a project.
     */
    private static function get_recent_todos( $project_id, $since ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pfob_todos
            WHERE project_id = %d
            AND created_at >= %s
            AND is_completed = 0
            ORDER BY created_at DESC
            LIMIT 10",
            $project_id,
            $since
        ) );
    }

    /**
     * Get recently completed todos.
     */
    private static function get_recently_completed_todos( $project_id, $since ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pfob_todos
            WHERE project_id = %d
            AND is_completed = 1
            AND updated_at >= %s
            ORDER BY updated_at DESC
            LIMIT 10",
            $project_id,
            $since
        ) );
    }

    /**
     * Get user's new assignments.
     */
    private static function get_user_assignments( $project_id, $user_id, $since ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pfob_todos
            WHERE project_id = %d
            AND assigned_to = %d
            AND created_at >= %s
            AND is_completed = 0
            ORDER BY created_at DESC
            LIMIT 10",
            $project_id,
            $user_id,
            $since
        ) );
    }

    /**
     * Get upcoming events.
     */
    private static function get_upcoming_events( $project_id, $days_ahead = 7 ) {
        global $wpdb;

        $start = gmdate( 'Y-m-d H:i:s' );
        $end = gmdate( 'Y-m-d H:i:s', strtotime( "+{$days_ahead} days" ) );

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pfob_events
            WHERE project_id = %d
            AND start_datetime >= %s
            AND start_datetime <= %s
            ORDER BY start_datetime ASC
            LIMIT 10",
            $project_id,
            $start,
            $end
        ) );
    }

    /**
     * Get digest subject line.
     */
    private static function get_digest_subject( $frequency ) {
        $site_name = get_bloginfo( 'name' );
        $period = $frequency === 'daily' ? 'Daily' : 'Weekly';
        return sprintf( '[%s] Your %s ProjectFOB Digest', $site_name, $period );
    }

    /**
     * Generate HTML email body.
     */
    private static function generate_digest_html( $user, $data, $frequency ) {
        $base_url = home_url( '/projectfob/' );
        $period = $frequency === 'daily' ? 'the last day' : 'the last week';

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .header { background: #2d9061; color: white; padding: 30px 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 30px; }
                .section { margin-bottom: 30px; }
                .section h2 { color: #2d9061; font-size: 18px; margin: 0 0 15px 0; border-bottom: 2px solid #2d9061; padding-bottom: 5px; }
                .item { background: #f8f9fa; padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 3px solid #2d9061; }
                .item-title { font-weight: bold; color: #333; margin-bottom: 5px; }
                .item-meta { font-size: 14px; color: #666; }
                .item-project { display: inline-block; background: #e0e0e0; padding: 2px 8px; border-radius: 3px; font-size: 12px; margin-right: 10px; }
                .empty-section { color: #999; font-style: italic; padding: 10px 0; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 14px; color: #666; border-top: 1px solid #e0e0e0; }
                .btn { display: inline-block; background: #2d9061; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; margin: 10px 0; }
                a { color: #2d9061; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📊 Your ProjectFOB Digest</h1>
                    <p>Here's what happened in <?php echo esc_html( $period ); ?></p>
                </div>

                <div class="content">
                    <p>Hi <?php echo esc_html( $user->display_name ); ?>,</p>

                    <?php if ( ! empty( $data['your_assignments'] ) ) : ?>
                        <div class="section">
                            <h2>🎯 Tasks Assigned to You (<?php echo count( $data['your_assignments'] ); ?>)</h2>
                            <?php foreach ( $data['your_assignments'] as $item ) : ?>
                                <div class="item">
                                    <div class="item-title">
                                        <span class="item-project"><?php echo esc_html( $item['project']->name ); ?></span>
                                        <?php echo esc_html( $item['todo']->title ); ?>
                                    </div>
                                    <div class="item-meta">
                                        <?php if ( $item['todo']->due_date ) : ?>
                                            Due: <?php echo esc_html( date( 'M j, Y', strtotime( $item['todo']->due_date ) ) ); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $data['new_messages'] ) ) : ?>
                        <div class="section">
                            <h2>💬 New Messages (<?php echo count( $data['new_messages'] ); ?>)</h2>
                            <?php foreach ( $data['new_messages'] as $item ) : ?>
                                <div class="item">
                                    <div class="item-title">
                                        <span class="item-project"><?php echo esc_html( $item['project']->name ); ?></span>
                                        <?php echo esc_html( $item['message']->title ); ?>
                                    </div>
                                    <div class="item-meta">
                                        By <?php echo esc_html( PFOB_Auth_Service::get_user_display_name( $item['message']->created_by ) ); ?>
                                        · <?php echo esc_html( date( 'M j, g:ia', strtotime( $item['message']->created_at ) ) ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $data['new_todos'] ) ) : ?>
                        <div class="section">
                            <h2>✅ New To-dos (<?php echo count( $data['new_todos'] ); ?>)</h2>
                            <?php foreach ( $data['new_todos'] as $item ) : ?>
                                <div class="item">
                                    <div class="item-title">
                                        <span class="item-project"><?php echo esc_html( $item['project']->name ); ?></span>
                                        <?php echo esc_html( $item['todo']->title ); ?>
                                    </div>
                                    <div class="item-meta">
                                        <?php if ( $item['todo']->assigned_to ) : ?>
                                            Assigned to <?php echo esc_html( PFOB_Auth_Service::get_user_display_name( $item['todo']->assigned_to ) ); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $data['completed_todos'] ) ) : ?>
                        <div class="section">
                            <h2>🎉 Completed (<?php echo count( $data['completed_todos'] ); ?>)</h2>
                            <?php foreach ( $data['completed_todos'] as $item ) : ?>
                                <div class="item">
                                    <div class="item-title">
                                        <span class="item-project"><?php echo esc_html( $item['project']->name ); ?></span>
                                        <?php echo esc_html( $item['todo']->title ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $data['upcoming_events'] ) ) : ?>
                        <div class="section">
                            <h2>📅 Upcoming Events (<?php echo count( $data['upcoming_events'] ); ?>)</h2>
                            <?php foreach ( $data['upcoming_events'] as $item ) : ?>
                                <div class="item">
                                    <div class="item-title">
                                        <span class="item-project"><?php echo esc_html( $item['project']->name ); ?></span>
                                        <?php echo esc_html( $item['event']->title ); ?>
                                    </div>
                                    <div class="item-meta">
                                        <?php echo esc_html( date( 'M j, Y \a\t g:ia', strtotime( $item['event']->start_datetime ) ) ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div style="text-align: center; margin-top: 40px;">
                        <a href="<?php echo esc_url( $base_url ); ?>" class="btn">
                            View Dashboard
                        </a>
                    </div>
                </div>

                <div class="footer">
                    <p>You're receiving this because you enabled <?php echo esc_html( $frequency ); ?> digests.</p>
                    <p>
                        <a href="<?php echo esc_url( home_url( '/projectfob/settings/notifications' ) ); ?>">
                            Update notification preferences
                        </a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Send digest email.
     */
    private static function send_digest_email( $to, $subject, $body ) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
        );

        return wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * Get user's digest preference.
     */
    public static function get_user_preference( $user_id ) {
        return get_user_meta( $user_id, 'pfob_digest_frequency', true ) ?: 'none';
    }

    /**
     * Set user's digest preference.
     */
    public static function set_user_preference( $user_id, $frequency ) {
        $allowed = array( 'none', 'daily', 'weekly' );

        if ( ! in_array( $frequency, $allowed ) ) {
            return false;
        }

        return update_user_meta( $user_id, 'pfob_digest_frequency', $frequency );
    }

    /**
     * Send test digest to a user.
     */
    public static function send_test_digest( $user_id ) {
        return self::send_user_digest( $user_id, 'daily' );
    }

    /**
     * Clear scheduled events (for deactivation).
     */
    public static function clear_schedules() {
        $timestamp = wp_next_scheduled( 'pfob_daily_digest' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'pfob_daily_digest' );
        }

        $timestamp = wp_next_scheduled( 'pfob_weekly_digest' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'pfob_weekly_digest' );
        }
    }
}
