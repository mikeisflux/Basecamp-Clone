<?php
/**
 * Plugin Name: Basecamp WP Pro
 * Plugin URI: https://github.com/mikeisflux/Basecamp-Clone
 * Description: A complete Basecamp clone built as a single WordPress plugin with no third-party dependencies.
 * Version: 1.0.0
 * Author: Mike
 * Author URI: https://github.com/mikeisflux
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: basecamp-wp-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'BCWP_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'BCWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'BCWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'BCWP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_basecamp_wp_pro() {
    require_once BCWP_PLUGIN_DIR . 'includes/class-bcwp-activator.php';
    BCWP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_basecamp_wp_pro() {
    require_once BCWP_PLUGIN_DIR . 'includes/class-bcwp-deactivator.php';
    BCWP_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_basecamp_wp_pro' );
register_deactivation_hook( __FILE__, 'deactivate_basecamp_wp_pro' );

/**
 * The core plugin class.
 */
require BCWP_PLUGIN_DIR . 'includes/class-bcwp-core.php';

/**
 * Begins execution of the plugin.
 */
function run_basecamp_wp_pro() {
    $plugin = new BCWP_Core();
    $plugin->run();
}

run_basecamp_wp_pro();
