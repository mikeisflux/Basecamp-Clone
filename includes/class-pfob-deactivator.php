<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes
 */

class PFOB_Deactivator {

    /**
     * Plugin deactivation handler.
     *
     * Flushes rewrite rules and performs cleanup.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear any scheduled cron jobs
        wp_clear_scheduled_hook( 'pfob_daily_cleanup' );
        wp_clear_scheduled_hook( 'pfob_email_digest' );

        // Set deactivation timestamp
        update_option( 'pfob_deactivation_time', current_time( 'timestamp' ) );
    }
}
