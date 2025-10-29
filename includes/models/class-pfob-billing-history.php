<?php
/**
 * Billing History model class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/models
 */

class PFOB_Billing_History {

    /**
     * Create a new billing record.
     *
     * @param array $data Billing data.
     * @return int|false Billing record ID on success, false on failure.
     */
    public static function create( $data ) {
        global $wpdb;

        // Encode metadata as JSON if it's an array
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        // Set transaction date if not provided
        if ( ! isset( $data['transaction_date'] ) ) {
            $data['transaction_date'] = current_time( 'mysql' );
        }

        $table_name = $wpdb->prefix . 'pfob_billing_history';

        $wpdb->insert(
            $table_name,
            array_merge(
                $data,
                array(
                    'created_at' => current_time( 'mysql' ),
                )
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * Get billing record by ID.
     *
     * @param int $id Billing record ID.
     * @return object|null Billing record or null if not found.
     */
    public static function get( $id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';
        $record = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id )
        );

        if ( $record && ! empty( $record->metadata ) ) {
            $record->metadata = json_decode( $record->metadata, true );
        }

        return $record;
    }

    /**
     * Get billing history for a user.
     *
     * @param int $user_id User ID.
     * @param int $limit   Limit number of results.
     * @param int $offset  Offset for pagination.
     * @return array Array of billing records.
     */
    public static function get_by_user_id( $user_id, $limit = 50, $offset = 0 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE user_id = %d
                ORDER BY transaction_date DESC
                LIMIT %d OFFSET %d",
                $user_id,
                $limit,
                $offset
            )
        );

        // Decode metadata
        foreach ( $results as $record ) {
            if ( ! empty( $record->metadata ) ) {
                $record->metadata = json_decode( $record->metadata, true );
            }
        }

        return $results;
    }

    /**
     * Get billing history for a subscription.
     *
     * @param int $subscription_id Subscription ID.
     * @return array Array of billing records.
     */
    public static function get_by_subscription_id( $subscription_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE subscription_id = %d
                ORDER BY transaction_date DESC",
                $subscription_id
            )
        );

        // Decode metadata
        foreach ( $results as $record ) {
            if ( ! empty( $record->metadata ) ) {
                $record->metadata = json_decode( $record->metadata, true );
            }
        }

        return $results;
    }

    /**
     * Get billing record by transaction ID.
     *
     * @param string $transaction_id Transaction ID.
     * @return object|null Billing record or null if not found.
     */
    public static function get_by_transaction_id( $transaction_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';
        $record = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE transaction_id = %s", $transaction_id )
        );

        if ( $record && ! empty( $record->metadata ) ) {
            $record->metadata = json_decode( $record->metadata, true );
        }

        return $record;
    }

    /**
     * Update billing record.
     *
     * @param int   $id   Billing record ID.
     * @param array $data Update data.
     * @return int|false Number of rows updated, false on error.
     */
    public static function update( $id, $data ) {
        global $wpdb;

        // Encode metadata as JSON if it's an array
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        $table_name = $wpdb->prefix . 'pfob_billing_history';

        return $wpdb->update(
            $table_name,
            $data,
            array( 'id' => $id )
        );
    }

    /**
     * Get total revenue.
     *
     * @param string $start_date Start date (YYYY-MM-DD).
     * @param string $end_date   End date (YYYY-MM-DD).
     * @return float Total revenue.
     */
    public static function get_total_revenue( $start_date = null, $end_date = null ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';

        $sql = "SELECT SUM(amount) FROM {$table_name} WHERE status = 'completed'";

        if ( $start_date && $end_date ) {
            $sql .= $wpdb->prepare( ' AND transaction_date BETWEEN %s AND %s', $start_date . ' 00:00:00', $end_date . ' 23:59:59' );
        } elseif ( $start_date ) {
            $sql .= $wpdb->prepare( ' AND transaction_date >= %s', $start_date . ' 00:00:00' );
        } elseif ( $end_date ) {
            $sql .= $wpdb->prepare( ' AND transaction_date <= %s', $end_date . ' 23:59:59' );
        }

        return (float) $wpdb->get_var( $sql );
    }

    /**
     * Get revenue by date range.
     *
     * @param string $start_date Start date (YYYY-MM-DD).
     * @param string $end_date   End date (YYYY-MM-DD).
     * @param string $group_by   Group by period ('day', 'week', 'month').
     * @return array Array of revenue data grouped by period.
     */
    public static function get_revenue_by_period( $start_date, $end_date, $group_by = 'month' ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';

        $date_format = array(
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
        );

        $format = $date_format[ $group_by ] ?? '%Y-%m';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    DATE_FORMAT(transaction_date, %s) as period,
                    SUM(amount) as revenue,
                    COUNT(*) as transaction_count
                FROM {$table_name}
                WHERE status = 'completed'
                AND transaction_date BETWEEN %s AND %s
                GROUP BY period
                ORDER BY period ASC",
                $format,
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            )
        );
    }

    /**
     * Get failed payments.
     *
     * @param int $days Number of days to look back.
     * @return array Array of failed payment records.
     */
    public static function get_failed_payments( $days = 30 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';
        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE status = 'failed'
                AND transaction_date >= %s
                ORDER BY transaction_date DESC",
                $date
            )
        );
    }

    /**
     * Get recent transactions.
     *
     * @param int $limit Limit number of results.
     * @return array Array of recent transactions.
     */
    public static function get_recent( $limit = 20 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                ORDER BY transaction_date DESC
                LIMIT %d",
                $limit
            )
        );

        // Decode metadata
        foreach ( $results as $record ) {
            if ( ! empty( $record->metadata ) ) {
                $record->metadata = json_decode( $record->metadata, true );
            }
        }

        return $results;
    }

    /**
     * Get billing statistics.
     *
     * @param string $start_date Start date (YYYY-MM-DD).
     * @param string $end_date   End date (YYYY-MM-DD).
     * @return array Statistics array.
     */
    public static function get_statistics( $start_date = null, $end_date = null ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pfob_billing_history';

        $where = array( "status = 'completed'" );
        $params = array();

        if ( $start_date && $end_date ) {
            $where[] = 'transaction_date BETWEEN %s AND %s';
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
        }

        $where_clause = implode( ' AND ', $where );
        $sql = "SELECT
            COUNT(*) as total_transactions,
            SUM(amount) as total_revenue,
            AVG(amount) as average_transaction,
            MIN(amount) as min_transaction,
            MAX(amount) as max_transaction
        FROM {$table_name}
        WHERE {$where_clause}";

        if ( ! empty( $params ) ) {
            $sql = $wpdb->prepare( $sql, $params );
        }

        return $wpdb->get_row( $sql, ARRAY_A );
    }
}
