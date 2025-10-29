<?php
/**
 * Plugin Name: ProjectFOB
 * Plugin URI: https://projectfob.com
 * Description: Every great plan deploys from the FOB. Complete project management and team collaboration SaaS platform with real-time features, subscriptions, and cloud storage.
 * Version: 1.0.0
 * Author: Divinity Comics Inc
 * Author URI: https://projectfob.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: projectfob
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
define( 'PFOB_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'PFOB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'PFOB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'PFOB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Cloudflare R2 Configuration
 */
define( 'PFOB_R2_ENDPOINT', 'https://e17dbcdbd648aab85b2e0e8391896b12.r2.cloudflarestorage.com' );
define( 'PFOB_R2_BUCKET', 'projectfob' );

/**
 * Subscription Plans
 */
define( 'PFOB_PLAN_STARTER', 'starter' );
define( 'PFOB_PLAN_PROFESSIONAL', 'professional' );
define( 'PFOB_PLAN_BUSINESS', 'business' );
define( 'PFOB_PLAN_ENTERPRISE', 'enterprise' );

/**
 * The code that runs during plugin activation.
 */
function activate_projectfob() {
    require_once PFOB_PLUGIN_DIR . 'includes/class-pfob-activator.php';
    PFOB_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_projectfob() {
    require_once PFOB_PLUGIN_DIR . 'includes/class-pfob-deactivator.php';
    PFOB_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_projectfob' );
register_deactivation_hook( __FILE__, 'deactivate_projectfob' );

/**
 * Load database helper classes
 */
require_once PFOB_PLUGIN_DIR . 'includes/database/class-pfob-database.php';

/**
 * Load all model classes
 */
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-project.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-message.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-todo.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-event.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-document.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-chat.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-card.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-comment.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-activity.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-subscription.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-billing-history.php';
require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-usage.php';

/**
 * Load all service classes
 */
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-auth-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-permission-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-paypal-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-r2-storage-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-notification-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-email-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-search-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-analytics-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-digest-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-file-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-google-calendar-service.php';
require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-websocket-service.php';

/**
 * Load REST API base class
 */
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-rest-api.php';

/**
 * Load all REST API endpoint classes
 */
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-projects-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-messages-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-todos-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-chat-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-activities-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-search-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-events-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-import-export-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-settings-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-analytics-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-calendar-integration-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-paypal-webhook-endpoint.php';
require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-subscription-endpoint.php';

/**
 * The core plugin class.
 */
require PFOB_PLUGIN_DIR . 'includes/class-pfob-core.php';

/**
 * Load admin settings (if in admin area)
 */
if ( is_admin() ) {
    require_once PFOB_PLUGIN_DIR . 'includes/admin/class-pfob-admin-settings.php';
}

/**
 * Begins execution of the plugin.
 */
function run_projectfob() {
    $plugin = new PFOB_Core();
    $plugin->run();
}

run_projectfob();
