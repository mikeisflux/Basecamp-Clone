<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes
 */

class BCWP_Deactivator {

    /**
     * Plugin deactivation handler.
     *
     * Flushes rewrite rules and performs cleanup.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear any scheduled cron jobs
        wp_clear_scheduled_hook( 'bcwp_daily_cleanup' );
        wp_clear_scheduled_hook( 'bcwp_email_digest' );

        // Set deactivation timestamp
        update_option( 'bcwp_deactivation_time', current_time( 'timestamp' ) );
    }
}
