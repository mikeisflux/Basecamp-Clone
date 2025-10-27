<?php
/**
 * Fired during plugin activation.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes
 */

class BCWP_Activator {

    /**
     * Plugin activation handler.
     *
     * Creates database tables, sets up default options, and flushes rewrite rules.
     */
    public static function activate() {
        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
            wp_die( __( 'Basecamp WP Pro requires WordPress 5.8 or higher.', 'basecamp-wp-pro' ) );
        }

        // Check PHP version
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            wp_die( __( 'Basecamp WP Pro requires PHP 7.4 or higher.', 'basecamp-wp-pro' ) );
        }

        // Create database tables
        require_once BCWP_PLUGIN_DIR . 'includes/database/class-bcwp-schema.php';
        BCWP_Schema::create_tables();

        // Set default options
        self::set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation timestamp
        update_option( 'bcwp_activation_time', current_time( 'timestamp' ) );
        update_option( 'bcwp_version', BCWP_VERSION );
    }

    /**
     * Set default plugin options.
     */
    private static function set_default_options() {
        $defaults = array(
            'bcwp_company_name' => get_bloginfo( 'name' ),
            'bcwp_primary_color' => '#2d9061',
            'bcwp_background_color' => '#f7f6f3',
            'bcwp_enable_email_notifications' => '1',
            'bcwp_enable_push_notifications' => '0',
            'bcwp_items_per_page' => '20',
            'bcwp_date_format' => 'F j, Y',
            'bcwp_time_format' => 'g:i a',
            'bcwp_timezone' => wp_timezone_string(),
            'bcwp_max_file_size' => '10485760', // 10MB in bytes
            'bcwp_allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip',
        );

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }
}
