<?php
/**
 * API Key Model
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/models
 */

class PFOB_API_Key {

    /**
     * Create a new API key.
     */
    public static function create( $data ) {
        $defaults = array(
            'user_id'      => 0,
            'name'         => '',
            'description'  => '',
            'api_key'      => '',
            'status'       => 'active',
            'last_used_at' => null,
            'created_at'   => current_time( 'mysql' ),
        );

        $data = wp_parse_args( $data, $defaults );

        $result = PFOB_Database::insert( 'api_keys', $data );

        if ( $result ) {
            // Log activity
            PFOB_Database::insert( 'activities', array(
                'user_id'    => $data['user_id'],
                'type'       => 'api_key_created',
                'content'    => json_encode( array(
                    'key_name' => $data['name'],
                ) ),
                'created_at' => current_time( 'mysql' ),
            ) );
        }

        return $result;
    }

    /**
     * Get API key by ID.
     */
    public static function get( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfob_api_keys';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ) );
    }

    /**
     * Get all API keys for a user.
     */
    public static function get_by_user( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfob_api_keys';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ) );
    }

    /**
     * Verify API key and return user ID.
     */
    public static function verify( $api_key ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfob_api_keys';

        $key_hash = hash( 'sha256', $api_key );

        $key = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE api_key = %s AND status = 'active'",
            $key_hash
        ) );

        if ( $key ) {
            // Update last used timestamp
            self::update( $key->id, array(
                'last_used_at' => current_time( 'mysql' ),
            ) );

            return $key->user_id;
        }

        return false;
    }

    /**
     * Update API key.
     */
    public static function update( $id, $data ) {
        return PFOB_Database::update(
            'api_keys',
            $data,
            array( 'id' => $id )
        );
    }

    /**
     * Delete API key.
     */
    public static function delete( $id ) {
        return PFOB_Database::delete(
            'api_keys',
            array( 'id' => $id )
        );
    }

    /**
     * Get usage statistics for user's API keys.
     */
    public static function get_usage_stats( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfob_api_keys';

        $stats = array(
            'total_keys'   => 0,
            'active_keys'  => 0,
            'revoked_keys' => 0,
            'requests_today' => 0,
            'requests_week'  => 0,
            'requests_month' => 0,
        );

        // Count keys by status
        $counts = $wpdb->get_results( $wpdb->prepare(
            "SELECT status, COUNT(*) as count FROM {$table} WHERE user_id = %d GROUP BY status",
            $user_id
        ), OBJECT_K );

        $stats['total_keys'] = array_sum( wp_list_pluck( $counts, 'count' ) );
        $stats['active_keys'] = isset( $counts['active'] ) ? $counts['active']->count : 0;
        $stats['revoked_keys'] = isset( $counts['revoked'] ) ? $counts['revoked']->count : 0;

        // Get request counts from API logs (if tracking is implemented)
        $logs_table = $wpdb->prefix . 'pfob_api_logs';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$logs_table}'" ) == $logs_table ) {
            $stats['requests_today'] = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$logs_table}
                WHERE user_id = %d AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)",
                $user_id
            ) );

            $stats['requests_week'] = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$logs_table}
                WHERE user_id = %d AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
                $user_id
            ) );

            $stats['requests_month'] = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$logs_table}
                WHERE user_id = %d AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                $user_id
            ) );
        }

        return $stats;
    }
}
