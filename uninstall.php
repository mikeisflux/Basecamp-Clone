<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package    ProjectFOB
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Load database schema class
require_once plugin_dir_path( __FILE__ ) . 'includes/database/class-pfob-schema.php';

// Drop all tables
PFOB_Schema::drop_tables();

// Delete all options
$options = array(
    'pfob_company_name',
    'pfob_company_logo',
    'pfob_primary_color',
    'pfob_background_color',
    'pfob_enable_email_notifications',
    'pfob_enable_push_notifications',
    'pfob_items_per_page',
    'pfob_date_format',
    'pfob_time_format',
    'pfob_timezone',
    'pfob_max_file_size',
    'pfob_allowed_file_types',
    'pfob_activation_time',
    'pfob_deactivation_time',
    'pfob_version',
    'pfob_db_version',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// Delete uploaded files
$upload_dir = WP_CONTENT_DIR . '/uploads/bcwp/';
if ( file_exists( $upload_dir ) ) {
    // Recursive delete function
    function pfob_delete_directory( $dir ) {
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

            if ( ! pfob_delete_directory( $dir . DIRECTORY_SEPARATOR . $item ) ) {
                return false;
            }
        }

        return rmdir( $dir );
    }

    pfob_delete_directory( $upload_dir );
}

// Clear any cached data
wp_cache_flush();
