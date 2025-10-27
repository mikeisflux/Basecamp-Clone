<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package    Basecamp_WP_Pro
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Load database schema class
require_once plugin_dir_path( __FILE__ ) . 'includes/database/class-bcwp-schema.php';

// Drop all tables
BCWP_Schema::drop_tables();

// Delete all options
$options = array(
    'bcwp_company_name',
    'bcwp_company_logo',
    'bcwp_primary_color',
    'bcwp_background_color',
    'bcwp_enable_email_notifications',
    'bcwp_enable_push_notifications',
    'bcwp_items_per_page',
    'bcwp_date_format',
    'bcwp_time_format',
    'bcwp_timezone',
    'bcwp_max_file_size',
    'bcwp_allowed_file_types',
    'bcwp_activation_time',
    'bcwp_deactivation_time',
    'bcwp_version',
    'bcwp_db_version',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// Delete uploaded files
$upload_dir = WP_CONTENT_DIR . '/uploads/bcwp/';
if ( file_exists( $upload_dir ) ) {
    // Recursive delete function
    function bcwp_delete_directory( $dir ) {
        if ( ! file_exists( $dir ) ) {
            return true;
        }

        if ( ! is_dir( $dir ) ) {
            return unlink( $dir );
        }

        foreach ( scandir( $dir ) as $item ) {
            if ( $item == '.' || $item == '..' ) {
                continue;
            }

            if ( ! bcwp_delete_directory( $dir . DIRECTORY_SEPARATOR . $item ) ) {
                return false;
            }
        }

        return rmdir( $dir );
    }

    bcwp_delete_directory( $upload_dir );
}

// Clear any cached data
wp_cache_flush();
