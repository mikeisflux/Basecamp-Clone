<?php
/**
 * Subscription model class.
 *
 * @package    Warcampaign
 * @subpackage Warcampaign/includes/models
 */

class WC_Subscription {

    /**
     * Create a new subscription.
     *
     * @param array $data Subscription data.
     * @return int|false Subscription ID on success, false on failure.
     */
    public static function create( $data ) {
        global $wpdb;

        // Ensure user doesn't already have an active subscription
        $existing = self::get_by_user_id( $data['user_id'] );
        if ( $existing && in_array( $existing->status, array( 'active', 'trialing', 'pending' ) ) ) {
            return false;
        }

        // Set default values
        $data['status'] = $data['status'] ?? 'pending';

        // Encode metadata as JSON if it's an array
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        $table_name = $wpdb->prefix . 'wc_subscriptions';

        $wpdb->insert(
            $table_name,
            array_merge(
                $data,
                array(
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' ),
                )
            )
        );

        $subscription_id = $wpdb->insert_id;

        if ( $subscription_id ) {
            // Log activity
            BCWP_Database::insert( 'activities', array(
                'user_id'      => $data['user_id'],
                'action_type'  => 'subscription.created',
                'subject_type' => 'subscription',
                'subject_id'   => $subscription_id,
                'description'  => sprintf( 'Subscribed to %s plan', $data['plan_id'] ),
            ) );
        }

        return $subscription_id;
    }

    /**
     * Get subscription by ID.
     *
     * @param int $subscription_id Subscription ID.
     * @return object|null Subscription object or null if not found.
     */
    public static function get( $subscription_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wc_subscriptions';
        $subscription = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $subscription_id )
        );

        if ( $subscription && ! empty( $subscription->metadata ) ) {
            $subscription->metadata = json_decode( $subscription->metadata, true );
        }

        return $subscription;
    }

    /**
     * Get subscription by user ID.
     *
     * @param int $user_id User ID.
     * @return object|null Subscription object or null if not found.
     */
    public static function get_by_user_id( $user_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wc_subscriptions';
        $subscription = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC LIMIT 1", $user_id )
        );

        if ( $subscription && ! empty( $subscription->metadata ) ) {
            $subscription->metadata = json_decode( $subscription->metadata, true );
        }

        return $subscription;
    }

    /**
     * Get subscription by PayPal subscription ID.
     *
     * @param string $paypal_subscription_id PayPal subscription ID.
     * @return object|null Subscription object or null if not found.
     */
    public static function get_by_paypal_id( $paypal_subscription_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wc_subscriptions';
        $subscription = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE paypal_subscription_id = %s", $paypal_subscription_id )
        );

        if ( $subscription && ! empty( $subscription->metadata ) ) {
            $subscription->metadata = json_decode( $subscription->metadata, true );
        }

        return $subscription;
    }

    /**
     * Update subscription.
     *
     * @param int   $subscription_id Subscription ID.
     * @param array $data            Update data.
     * @return int|false Number of rows updated, false on error.
     */
    public static function update( $subscription_id, $data ) {
        global $wpdb;

        // Encode metadata as JSON if it's an array
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        $data['updated_at'] = current_time( 'mysql' );

        $table_name = $wpdb->prefix . 'wc_subscriptions';

        return $wpdb->update(
            $table_name,
            $data,
            array( 'id' => $subscription_id )
        );
    }

    /**
     * Cancel subscription.
     *
     * @param int $subscription_id Subscription ID.
     * @return int|false Number of rows updated, false on error.
     */
    public static function cancel( $subscription_id ) {
        return self::update( $subscription_id, array(
            'status' => 'canceled',
            'canceled_at' => current_time( 'mysql' ),
        ) );
    }

    /**
     * Get all active subscriptions.
     *
     * @return array Array of subscription objects.
     */
    public static function get_active_subscriptions() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wc_subscriptions';

        return $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE status IN ('active', 'trialing') ORDER BY created_at DESC"
        );
    }

    /**
     * Get subscriptions expiring soon (within X days).
     *
     * @param int $days Number of days.
     * @return array Array of subscription objects.
     */
    public static function get_expiring_soon( $days = 7 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wc_subscriptions';
        $future_date = date( 'Y-m-d H:i:s', strtotime( "+{$days} days" ) );

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE status = 'active'
                AND current_period_end <= %s
                ORDER BY current_period_end ASC",
                $future_date
            )
        );
    }

    /**
     * Check if subscription is active.
     *
     * @param int $user_id User ID.
     * @return bool True if active, false otherwise.
     */
    public static function is_active( $user_id ) {
        $subscription = self::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return false;
        }

        return in_array( $subscription->status, array( 'active', 'trialing' ) );
    }

    /**
     * Get subscription plan.
     *
     * @param int $user_id User ID.
     * @return array|null Plan configuration or null if no subscription.
     */
    public static function get_user_plan( $user_id ) {
        $subscription = self::get_by_user_id( $user_id );

        if ( ! $subscription || ! in_array( $subscription->status, array( 'active', 'trialing' ) ) ) {
            return null;
        }

        $plans = include WC_PLUGIN_DIR . 'includes/config/subscription-plans.php';

        return $plans[ $subscription->plan_id ] ?? null;
    }

    /**
     * Check if user can access a feature.
     *
     * @param int    $user_id User ID.
     * @param string $feature Feature key.
     * @return bool True if user can access, false otherwise.
     */
    public static function can_access_feature( $user_id, $feature ) {
        $plan = self::get_user_plan( $user_id );

        if ( ! $plan ) {
            return false;
        }

        return isset( $plan['features'][ $feature ] ) && $plan['features'][ $feature ];
    }

    /**
     * Get feature limit for user.
     *
     * @param int    $user_id User ID.
     * @param string $feature Feature key (e.g., 'projects', 'users', 'storage_gb').
     * @return int|null Limit value or null if unlimited.
     */
    public static function get_feature_limit( $user_id, $feature ) {
        $plan = self::get_user_plan( $user_id );

        if ( ! $plan ) {
            return 0;
        }

        $value = $plan['features'][ $feature ] ?? 0;

        // 999999 means unlimited
        if ( $value === 999999 ) {
            return null;
        }

        return $value;
    }

    /**
     * Get subscription statistics.
     *
     * @return array Statistics array.
     */
    public static function get_statistics() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wc_subscriptions';

        $stats = array();

        // Total active subscriptions
        $stats['active'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table_name} WHERE status IN ('active', 'trialing')"
        );

        // Total canceled subscriptions
        $stats['canceled'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table_name} WHERE status = 'canceled'"
        );

        // Subscriptions by plan
        $stats['by_plan'] = $wpdb->get_results(
            "SELECT plan_id, COUNT(*) as count FROM {$table_name} WHERE status IN ('active', 'trialing') GROUP BY plan_id"
        );

        // Monthly recurring revenue (MRR)
        $plans = include WC_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $mrr = 0;
        foreach ( $stats['by_plan'] as $plan_stat ) {
            if ( isset( $plans[ $plan_stat->plan_id ] ) ) {
                $mrr += $plans[ $plan_stat->plan_id ]['price'] * $plan_stat->count;
            }
        }
        $stats['mrr'] = $mrr;

        return $stats;
    }
}
