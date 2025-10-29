<?php
/**
 * Usage Tracking model class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/models
 */

class PFOB_Usage {

    /**
     * Record or update usage metric.
     *
     * @param int    $user_id     User ID.
     * @param string $metric_type Metric type (e.g., 'projects', 'storage', 'users').
     * @param int    $value       Metric value.
     * @return int|false Usage record ID on success, false on failure.
     */
    public static function record( $user_id, $metric_type, $value ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_usage_tracking';

        // Get current period (month)
        $period_start = date( 'Y-m-01 00:00:00' );
        $period_end = date( 'Y-m-t 23:59:59' );

        // Check if record exists for this period
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE user_id = %d
                AND metric_type = %s
                AND period_start = %s",
                $user_id,
                $metric_type,
                $period_start
            )
        );

        if ( $existing ) {
            // Update existing record
            $wpdb->update(
                $table_name,
                array(
                    'metric_value' => $value,
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( 'id' => $existing->id )
            );

            return $existing->id;
        } else {
            // Create new record
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'metric_type' => $metric_type,
                    'metric_value' => $value,
                    'period_start' => $period_start,
                    'period_end' => $period_end,
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' ),
                )
            );

            return $wpdb->insert_id;
        }
    }

    /**
     * Get current usage for a metric.
     *
     * @param int    $user_id     User ID.
     * @param string $metric_type Metric type.
     * @return int Current usage value.
     */
    public static function get_current( $user_id, $metric_type ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_usage_tracking';
        $period_start = date( 'Y-m-01 00:00:00' );

        $value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT metric_value FROM {$table_name}
                WHERE user_id = %d
                AND metric_type = %s
                AND period_start = %s",
                $user_id,
                $metric_type,
                $period_start
            )
        );

        return (int) $value;
    }

    /**
     * Get all current usage metrics for a user.
     *
     * @param int $user_id User ID.
     * @return array Associative array of metric_type => value.
     */
    public static function get_all_current( $user_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_usage_tracking';
        $period_start = date( 'Y-m-01 00:00:00' );

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT metric_type, metric_value FROM {$table_name}
                WHERE user_id = %d
                AND period_start = %s",
                $user_id,
                $period_start
            ),
            ARRAY_A
        );

        $usage = array();
        foreach ( $results as $row ) {
            $usage[ $row['metric_type'] ] = (int) $row['metric_value'];
        }

        return $usage;
    }

    /**
     * Increment usage counter.
     *
     * @param int    $user_id     User ID.
     * @param string $metric_type Metric type.
     * @param int    $increment   Amount to increment by (default: 1).
     * @return int|false New value on success, false on failure.
     */
    public static function increment( $user_id, $metric_type, $increment = 1 ) {
        $current = self::get_current( $user_id, $metric_type );
        $new_value = $current + $increment;
        self::record( $user_id, $metric_type, $new_value );

        return $new_value;
    }

    /**
     * Decrement usage counter.
     *
     * @param int    $user_id     User ID.
     * @param string $metric_type Metric type.
     * @param int    $decrement   Amount to decrement by (default: 1).
     * @return int|false New value on success, false on failure.
     */
    public static function decrement( $user_id, $metric_type, $decrement = 1 ) {
        $current = self::get_current( $user_id, $metric_type );
        $new_value = max( 0, $current - $decrement );
        self::record( $user_id, $metric_type, $new_value );

        return $new_value;
    }

    /**
     * Check if user has reached their limit for a metric.
     *
     * @param int    $user_id     User ID.
     * @param string $metric_type Metric type.
     * @return bool True if limit reached, false otherwise.
     */
    public static function is_limit_reached( $user_id, $metric_type ) {
        $limit = PFOB_Subscription::get_feature_limit( $user_id, $metric_type );

        // Null means unlimited
        if ( $limit === null ) {
            return false;
        }

        $current = self::get_current( $user_id, $metric_type );

        return $current >= $limit;
    }

    /**
     * Get remaining allowance for a metric.
     *
     * @param int    $user_id     User ID.
     * @param string $metric_type Metric type.
     * @return int|null Remaining allowance or null if unlimited.
     */
    public static function get_remaining( $user_id, $metric_type ) {
        $limit = PFOB_Subscription::get_feature_limit( $user_id, $metric_type );

        // Null means unlimited
        if ( $limit === null ) {
            return null;
        }

        $current = self::get_current( $user_id, $metric_type );

        return max( 0, $limit - $current );
    }

    /**
     * Get usage percentage for a metric.
     *
     * @param int    $user_id     User ID.
     * @param string $metric_type Metric type.
     * @return float|null Percentage (0-100) or null if unlimited.
     */
    public static function get_usage_percentage( $user_id, $metric_type ) {
        $limit = PFOB_Subscription::get_feature_limit( $user_id, $metric_type );

        // Null means unlimited
        if ( $limit === null ) {
            return null;
        }

        if ( $limit === 0 ) {
            return 100.0;
        }

        $current = self::get_current( $user_id, $metric_type );

        return min( 100.0, ( $current / $limit ) * 100 );
    }

    /**
     * Get usage history for a metric.
     *
     * @param int    $user_id     User ID.
     * @param string $metric_type Metric type.
     * @param int    $months      Number of months to look back.
     * @return array Array of usage records.
     */
    public static function get_history( $user_id, $metric_type, $months = 12 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_usage_tracking';
        $start_date = date( 'Y-m-01 00:00:00', strtotime( "-{$months} months" ) );

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE user_id = %d
                AND metric_type = %s
                AND period_start >= %s
                ORDER BY period_start DESC",
                $user_id,
                $metric_type,
                $start_date
            )
        );
    }

    /**
     * Update storage usage from actual file sizes.
     *
     * @param int $user_id User ID.
     * @return int Total storage used in bytes.
     */
    public static function update_storage_usage( $user_id ) {
        // Get total storage from R2
        if ( PFOB_R2_Storage_Service::is_configured() ) {
            $storage_bytes = PFOB_R2_Storage_Service::get_user_storage_usage( $user_id );
        } else {
            // Fallback to local files
            $storage_bytes = self::calculate_local_storage_usage( $user_id );
        }

        // Convert to GB for storage metric
        $storage_gb = round( $storage_bytes / ( 1024 * 1024 * 1024 ), 2 );

        self::record( $user_id, 'storage_gb', $storage_gb );

        return $storage_bytes;
    }

    /**
     * Calculate local storage usage (fallback if R2 not configured).
     *
     * @param int $user_id User ID.
     * @return int Total storage used in bytes.
     */
    private static function calculate_local_storage_usage( $user_id ) {
        global $wpdb;

        $documents_table = PFOB_Database::get_table_name( 'documents' );

        // Get all documents for projects the user is a member of
        $total_size = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(d.file_size)
                FROM {$documents_table} d
                INNER JOIN " . PFOB_Database::get_table_name( 'project_members' ) . " pm
                ON d.project_id = pm.project_id
                WHERE pm.user_id = %d
                AND d.file_size IS NOT NULL",
                $user_id
            )
        );

        return (int) $total_size;
    }

    /**
     * Update project count usage.
     *
     * @param int $user_id User ID.
     * @return int Total projects.
     */
    public static function update_project_count( $user_id ) {
        global $wpdb;

        $project_members_table = PFOB_Database::get_table_name( 'project_members' );
        $projects_table = PFOB_Database::get_table_name( 'projects' );

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT pm.project_id)
                FROM {$project_members_table} pm
                INNER JOIN {$projects_table} p ON pm.project_id = p.id
                WHERE pm.user_id = %d
                AND p.status = 'active'
                AND pm.role = 'admin'",
                $user_id
            )
        );

        self::record( $user_id, 'projects', (int) $count );

        return (int) $count;
    }

    /**
     * Update all usage metrics for a user.
     *
     * @param int $user_id User ID.
     * @return array All updated metrics.
     */
    public static function update_all_metrics( $user_id ) {
        self::update_storage_usage( $user_id );
        self::update_project_count( $user_id );

        return self::get_all_current( $user_id );
    }

    /**
     * Get users nearing their limits.
     *
     * @param string $metric_type Metric type.
     * @param int    $threshold   Percentage threshold (default: 80).
     * @return array Array of user IDs nearing limits.
     */
    public static function get_users_nearing_limits( $metric_type, $threshold = 80 ) {
        global $wpdb;

        $subscriptions_table = $wpdb->prefix . 'pfob_subscriptions';
        $usage_table = $wpdb->prefix . 'pfob_usage_tracking';

        $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';

        $users_nearing_limit = array();

        // Get all active subscriptions
        $subscriptions = $wpdb->get_results(
            "SELECT user_id, plan_id FROM {$subscriptions_table}
            WHERE status IN ('active', 'trialing')"
        );

        foreach ( $subscriptions as $subscription ) {
            if ( ! isset( $plans[ $subscription->plan_id ] ) ) {
                continue;
            }

            $limit = $plans[ $subscription->plan_id ]['features'][ $metric_type ] ?? 0;

            // Skip unlimited plans
            if ( $limit === 999999 ) {
                continue;
            }

            $current = self::get_current( $subscription->user_id, $metric_type );
            $percentage = ( $limit > 0 ) ? ( $current / $limit ) * 100 : 0;

            if ( $percentage >= $threshold ) {
                $users_nearing_limit[] = array(
                    'user_id' => $subscription->user_id,
                    'current' => $current,
                    'limit' => $limit,
                    'percentage' => $percentage,
                );
            }
        }

        return $users_nearing_limit;
    }
}
