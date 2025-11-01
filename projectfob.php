<?php
/**
 * Plugin Name: ProjectFOB
 * Plugin URI: https://projectfob.com
 * Description: Every great plan deploys from the FOB. Complete project management and team collaboration SaaS platform with real-time features, subscriptions, and cloud storage.
 * Version: 2.6.1
 * Author: Divinity Comics Inc
 * Author URI: https://projectfob.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: projectfob
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Debug logging function (only active if ENABLE_DEBUG file exists)
 */
function pfob_debug_log( $message, $level = 'INFO' ) {
    $debug_file = plugin_dir_path( __FILE__ ) . 'ENABLE_DEBUG';
    if ( ! file_exists( $debug_file ) ) {
        return;
    }

    $log_file = plugin_dir_path( __FILE__ ) . 'debug.log';
    $timestamp = date( 'Y-m-d H:i:s' );
    $entry = "[{$timestamp}] [{$level}] {$message}\n";

    // Also log exceptions with stack trace
    if ( is_a( $message, 'Throwable' ) ) {
        $entry = "[{$timestamp}] [ERROR] " . $message->getMessage() . "\n";
        $entry .= "File: " . $message->getFile() . ":" . $message->getLine() . "\n";
        $entry .= "Trace:\n" . $message->getTraceAsString() . "\n";
    }

    error_log( $entry, 3, $log_file );
}

/**
 * Current plugin version.
 */
define( 'PFOB_VERSION', '2.6.1' );

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
    pfob_debug_log( '=== Plugin Activation Started ===' );
    pfob_debug_log( 'PHP Version: ' . PHP_VERSION );
    pfob_debug_log( 'WordPress Version: ' . get_bloginfo( 'version' ) );
    pfob_debug_log( 'Plugin Directory: ' . PFOB_PLUGIN_DIR );

    try {
        pfob_debug_log( 'Loading activator class...' );
        require_once PFOB_PLUGIN_DIR . 'includes/class-pfob-activator.php';
        pfob_debug_log( 'Activator class loaded successfully' );

        pfob_debug_log( 'Calling PFOB_Activator::activate()...' );
        PFOB_Activator::activate();
        pfob_debug_log( '=== Plugin Activation Completed Successfully ===' );

    } catch ( Throwable $e ) {
        pfob_debug_log( $e );
        pfob_debug_log( '=== Plugin Activation FAILED ===' );
        // Re-throw so WordPress shows the error
        throw $e;
    }
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
pfob_debug_log( '=== Loading Plugin Files ===' );

try {
    pfob_debug_log( 'Loading database classes...' );
    require_once PFOB_PLUGIN_DIR . 'includes/database/class-pfob-database.php';
    pfob_debug_log( '✓ Database class loaded' );
} catch ( Throwable $e ) {
    pfob_debug_log( $e );
    throw $e;
}

/**
 * Load all model classes
 */
try {
    pfob_debug_log( 'Loading model classes...' );
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
    pfob_debug_log( '✓ Model classes loaded' );
} catch ( Throwable $e ) {
    pfob_debug_log( $e );
    throw $e;
}

/**
 * Load all service classes
 */
try {
    pfob_debug_log( 'Loading service classes...' );
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
    pfob_debug_log( '✓ Service classes loaded' );
} catch ( Throwable $e ) {
    pfob_debug_log( $e );
    throw $e;
}

/**
 * Load frontend helper classes (needed by Core and API endpoints)
 */
try {
    pfob_debug_log( 'Loading frontend classes...' );
    require_once PFOB_PLUGIN_DIR . 'includes/frontend/class-pfob-router.php';
    require_once PFOB_PLUGIN_DIR . 'includes/frontend/class-pfob-assets.php';
    require_once PFOB_PLUGIN_DIR . 'includes/frontend/class-pfob-template.php';
    pfob_debug_log( '✓ Frontend classes loaded' );
} catch ( Throwable $e ) {
    pfob_debug_log( $e );
    throw $e;
}

/**
 * Load REST API base class
 */
try {
    pfob_debug_log( 'Loading REST API base class...' );
    require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-rest-api.php';
    pfob_debug_log( '✓ REST API base class loaded' );
} catch ( Throwable $e ) {
    pfob_debug_log( $e );
    throw $e;
}

/**
 * Load all REST API endpoint classes
 */
try {
    pfob_debug_log( 'Loading REST API endpoint classes...' );
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
    require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-notifications-endpoint.php';
    require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-paypal-webhook-endpoint.php';
    require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-subscription-endpoint.php';
    pfob_debug_log( '✓ REST API endpoint classes loaded' );
} catch ( Throwable $e ) {
    pfob_debug_log( $e );
    throw $e;
}

/**
 * The core plugin class.
 */
try {
    pfob_debug_log( 'Loading core plugin class...' );
    require PFOB_PLUGIN_DIR . 'includes/class-pfob-core.php';
    pfob_debug_log( '✓ Core class loaded' );
} catch ( Throwable $e ) {
    pfob_debug_log( $e );
    throw $e;
}

/**
 * Load admin settings (if in admin area)
 */
if ( is_admin() ) {
    require_once PFOB_PLUGIN_DIR . 'includes/admin/class-pfob-admin-settings.php';
    require_once PFOB_PLUGIN_DIR . 'includes/admin/class-pfob-debug-logger.php';
    require_once PFOB_PLUGIN_DIR . 'includes/admin/class-pfob-subscription-manager.php';

    // Initialize admin components
    new PFOB_Admin_Settings();
    new PFOB_Debug_Logger();
    new PFOB_Subscription_Manager();
}

/**
 * Begins execution of the plugin.
 */
function run_projectfob() {
    try {
        pfob_debug_log( '=== Initializing Plugin ===' );
        pfob_debug_log( 'Creating PFOB_Core instance...' );
        $plugin = new PFOB_Core();
        pfob_debug_log( '✓ PFOB_Core instance created' );

        pfob_debug_log( 'Running plugin...' );
        $plugin->run();
        pfob_debug_log( '✓ Plugin initialized successfully' );
        pfob_debug_log( '=== Plugin Load Complete ===' );

    } catch ( Throwable $e ) {
        pfob_debug_log( $e );
        pfob_debug_log( '=== Plugin Load FAILED ===' );
        throw $e;
    }
}

run_projectfob();
