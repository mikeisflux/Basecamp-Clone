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
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            wp_die( __( 'ProjectFOB requires PHP 7.4 or higher.', 'projectfob' ) );
        }

        // Create database tables
        require_once PFOB_PLUGIN_DIR . 'includes/database/class-pfob-schema.php';
        PFOB_Schema::create_tables();

        // Set default options
        self::set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();

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
}
