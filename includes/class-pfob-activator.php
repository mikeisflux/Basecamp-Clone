<?php
/**
 * Fired during plugin activation.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes
 */

class PFOB_Activator {

    /**
     * Plugin activation handler.
     *
     * Creates database tables, sets up default options, and flushes rewrite rules.
     */
    public static function activate() {
        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
            wp_die( __( 'ProjectFOB requires WordPress 5.8 or higher.', 'projectfob' ) );
        }

        // Check PHP version
        if ( version_compare( PHP_VERSION, '8.3', '<' ) ) {
            wp_die( __( 'ProjectFOB requires PHP 8.3 or higher.', 'projectfob' ) );
        }

        // Create database tables
        require_once PFOB_PLUGIN_DIR . 'includes/database/class-pfob-schema.php';
        PFOB_Schema::create_tables();

        // Set default options
        self::set_default_options();

        // Sync existing WordPress users with subscription system
        self::sync_existing_users();

        // Register rewrite rules and flush
        require_once PFOB_PLUGIN_DIR . 'includes/frontend/class-pfob-router.php';
        PFOB_Router::register_rewrite_rules();
        flush_rewrite_rules();

        // Set a flag to flush rewrite rules on next load
        update_option( 'pfob_flush_rewrite_rules', '1' );

        // Show activation notice with flush button
        update_option( 'pfob_show_activation_notice', '1' );
        delete_option( 'pfob_activation_notice_dismissed' ); // Reset dismissal in case of re-activation

        // Set activation timestamp
        update_option( 'pfob_activation_time', current_time( 'timestamp' ) );
        update_option( 'pfob_version', PFOB_VERSION );
    }

    /**
     * Set default plugin options.
     */
    private static function set_default_options() {
        $defaults = array(
            'pfob_company_name' => get_bloginfo( 'name' ),
            'pfob_primary_color' => '#2d9061',
            'pfob_background_color' => '#f7f6f3',
            'pfob_enable_email_notifications' => '1',
            'pfob_enable_push_notifications' => '0',
            'pfob_items_per_page' => '20',
            'pfob_date_format' => 'F j, Y',
            'pfob_time_format' => 'g:i a',
            'pfob_timezone' => wp_timezone_string(),
            'pfob_max_file_size' => '10485760', // 10MB in bytes
            'pfob_allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip',
        );

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }

    /**
     * Sync existing WordPress users with subscription system.
     *
     * This runs on plugin activation to ensure all existing users have subscription records.
     * Critical for uninstall/reinstall scenarios where users exist but subscriptions don't.
     */
    private static function sync_existing_users() {
        global $wpdb;

        // Load subscription model
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-subscription.php';

        // Get all WordPress users
        $users = get_users( array(
            'fields' => array( 'ID', 'user_email' ),
        ) );

        $synced_count = 0;
        $table_name = $wpdb->prefix . 'pfob_subscriptions';

        foreach ( $users as $user ) {
            // Check if user already has a subscription record
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE user_id = %d",
                    $user->ID
                )
            );

            // Skip if subscription already exists
            if ( $existing ) {
                continue;
            }

            // Determine subscription status and plan based on user role
            $user_obj = get_userdata( $user->ID );

            if ( in_array( 'administrator', $user_obj->roles ) ) {
                // WordPress admins get active enterprise subscription
                $status = 'active';
                $plan_id = 'enterprise';
            } else {
                // Regular users get active professional subscription
                // (You can change this to 'trialing' or 'pending' if preferred)
                $status = 'active';
                $plan_id = 'professional';
            }

            // Create subscription record
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user->ID,
                    'plan_id' => $plan_id,
                    'status' => $status,
                    'current_period_start' => current_time( 'mysql' ),
                    'current_period_end' => date( 'Y-m-d H:i:s', strtotime( '+1 month' ) ),
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' ),
                    'metadata' => wp_json_encode( array(
                        'synced_on_activation' => true,
                        'original_user_role' => implode( ',', $user_obj->roles ),
                    ) ),
                )
            );

            $synced_count++;
        }

        // Store sync results for admin notice
        if ( $synced_count > 0 ) {
            update_option( 'pfob_users_synced_on_activation', $synced_count );
        }
    }
}
