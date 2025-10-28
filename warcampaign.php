<?php
/**
 * Plugin Name: Warcampaign
 * Plugin URI: https://warcampaign.com
 * Description: Complete project management and team collaboration SaaS platform with real-time features, subscriptions, and cloud storage.
 * Version: 2.0.0
 * Author: Divinity Comics Inc
 * Author URI: https://warcampaign.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: warcampaign
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'WC_VERSION', '2.0.0' );

/**
 * Plugin directory path.
 */
define( 'WC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'WC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'WC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Cloudflare R2 Configuration
 */
define( 'WC_R2_ENDPOINT', 'https://e17dbcdbd648aab85b2e0e8391896b12.r2.cloudflarestorage.com' );
define( 'WC_R2_BUCKET', 'warcampaign' );

/**
 * Subscription Plans
 */
define( 'WC_PLAN_STARTER', 'starter' );
define( 'WC_PLAN_PROFESSIONAL', 'professional' );
define( 'WC_PLAN_BUSINESS', 'business' );
define( 'WC_PLAN_ENTERPRISE', 'enterprise' );

/**
 * The code that runs during plugin activation.
 */
function activate_warcampaign() {
    require_once WC_PLUGIN_DIR . 'includes/class-wc-activator.php';
    WC_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_warcampaign() {
    require_once WC_PLUGIN_DIR . 'includes/class-wc-deactivator.php';
    WC_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_warcampaign' );
register_deactivation_hook( __FILE__, 'deactivate_warcampaign' );

/**
 * The core plugin class.
 */
require WC_PLUGIN_DIR . 'includes/class-wc-core.php';

/**
 * Begins execution of the plugin.
 */
function run_warcampaign() {
    $plugin = new WC_Core();
    $plugin->run();
}

run_warcampaign();
