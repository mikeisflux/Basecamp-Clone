<?php
/**
 * ProjectFOB Activation Debugger
 *
 * Place this file in your WordPress root directory and access it via browser:
 * https://yourdomain.com/debug-activation.php
 *
 * This will log every step of plugin activation to help identify fatal errors.
 */

// Start output buffering
ob_start();

// Set up error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

$log_file = __DIR__ . '/projectfob-activation-debug.log';
$debug_output = [];

function debug_log($message, $level = 'INFO') {
    global $debug_output;
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[{$timestamp}] [{$level}] {$message}";
    $debug_output[] = $entry;
    error_log($entry . "\n", 3, $GLOBALS['log_file']);
}

debug_log("=== ProjectFOB Activation Debug Started ===");
debug_log("PHP Version: " . PHP_VERSION);
debug_log("WordPress Root: " . __DIR__);

// Check if WordPress is loaded
if (!file_exists(__DIR__ . '/wp-load.php')) {
    debug_log("ERROR: wp-load.php not found. This script must be in WordPress root.", 'ERROR');
    die("ERROR: Place this file in your WordPress root directory.");
}

debug_log("Loading WordPress...");
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

debug_log("WordPress loaded successfully");
debug_log("WordPress Version: " . get_bloginfo('version'));

// Plugin path
$plugin_dir = WP_PLUGIN_DIR . '/projectfob/';
$plugin_file = $plugin_dir . 'projectfob.php';

debug_log("Plugin Directory: " . $plugin_dir);
debug_log("Plugin File: " . $plugin_file);

if (!file_exists($plugin_file)) {
    debug_log("ERROR: Plugin file not found at: " . $plugin_file, 'ERROR');
    die("ERROR: Plugin not found. Make sure projectfob plugin is uploaded to wp-content/plugins/projectfob/");
}

debug_log("Plugin file exists");

// Define plugin constants
if (!defined('PFOB_VERSION')) {
    define('PFOB_VERSION', '2.1.1');
    debug_log("Defined PFOB_VERSION: 2.1.1");
}

if (!defined('PFOB_PLUGIN_DIR')) {
    define('PFOB_PLUGIN_DIR', $plugin_dir);
    debug_log("Defined PFOB_PLUGIN_DIR: " . $plugin_dir);
}

if (!defined('PFOB_PLUGIN_URL')) {
    define('PFOB_PLUGIN_URL', plugins_url('/', $plugin_file));
    debug_log("Defined PFOB_PLUGIN_URL: " . PFOB_PLUGIN_URL);
}

if (!defined('PFOB_PLUGIN_BASENAME')) {
    define('PFOB_PLUGIN_BASENAME', plugin_basename($plugin_file));
    debug_log("Defined PFOB_PLUGIN_BASENAME: " . PFOB_PLUGIN_BASENAME);
}

debug_log("=== Testing File Existence ===");

// Check all required files
$required_files = [
    'includes/database/class-pfob-database.php',
    'includes/models/class-pfob-project.php',
    'includes/models/class-pfob-message.php',
    'includes/models/class-pfob-todo.php',
    'includes/models/class-pfob-event.php',
    'includes/models/class-pfob-document.php',
    'includes/models/class-pfob-chat.php',
    'includes/models/class-pfob-card.php',
    'includes/models/class-pfob-comment.php',
    'includes/models/class-pfob-activity.php',
    'includes/models/class-pfob-subscription.php',
    'includes/models/class-pfob-billing-history.php',
    'includes/models/class-pfob-usage.php',
    'includes/services/class-pfob-auth-service.php',
    'includes/services/class-pfob-permission-service.php',
    'includes/services/class-pfob-paypal-service.php',
    'includes/services/class-pfob-r2-storage-service.php',
    'includes/services/class-pfob-notification-service.php',
    'includes/services/class-pfob-email-service.php',
    'includes/services/class-pfob-search-service.php',
    'includes/services/class-pfob-analytics-service.php',
    'includes/services/class-pfob-digest-service.php',
    'includes/services/class-pfob-file-service.php',
    'includes/services/class-pfob-google-calendar-service.php',
    'includes/services/class-pfob-websocket-service.php',
    'includes/frontend/class-pfob-router.php',
    'includes/frontend/class-pfob-assets.php',
    'includes/frontend/class-pfob-template.php',
    'includes/api/class-pfob-rest-api.php',
    'includes/api/class-pfob-projects-endpoint.php',
    'includes/api/class-pfob-messages-endpoint.php',
    'includes/api/class-pfob-todos-endpoint.php',
    'includes/api/class-pfob-chat-endpoint.php',
    'includes/api/class-pfob-activities-endpoint.php',
    'includes/api/class-pfob-search-endpoint.php',
    'includes/api/class-pfob-events-endpoint.php',
    'includes/api/class-pfob-import-export-endpoint.php',
    'includes/api/class-pfob-settings-endpoint.php',
    'includes/api/class-pfob-analytics-endpoint.php',
    'includes/api/class-pfob-calendar-integration-endpoint.php',
    'includes/api/class-pfob-notifications-endpoint.php',
    'includes/api/class-pfob-paypal-webhook-endpoint.php',
    'includes/api/class-pfob-subscription-endpoint.php',
    'includes/class-pfob-core.php',
    'includes/class-pfob-activator.php',
];

$missing_files = [];
foreach ($required_files as $file) {
    $full_path = PFOB_PLUGIN_DIR . $file;
    if (file_exists($full_path)) {
        debug_log("✓ Found: " . $file);
    } else {
        debug_log("✗ MISSING: " . $file, 'ERROR');
        $missing_files[] = $file;
    }
}

if (!empty($missing_files)) {
    debug_log("FATAL: Missing " . count($missing_files) . " required files", 'ERROR');
    foreach ($missing_files as $file) {
        debug_log("  - " . $file, 'ERROR');
    }
    die("ERROR: Missing required files. Check the log.");
}

debug_log("=== All required files exist ===");

debug_log("=== Testing File Loading ===");

// Try to load each file individually
foreach ($required_files as $file) {
    $full_path = PFOB_PLUGIN_DIR . $file;
    debug_log("Loading: " . $file);

    try {
        require_once $full_path;
        debug_log("✓ Loaded successfully: " . $file);
    } catch (Throwable $e) {
        debug_log("✗ FAILED to load: " . $file, 'ERROR');
        debug_log("Error: " . $e->getMessage(), 'ERROR');
        debug_log("File: " . $e->getFile() . ':' . $e->getLine(), 'ERROR');
        debug_log("Trace: " . $e->getTraceAsString(), 'ERROR');
        die("FATAL ERROR loading file. Check log for details.");
    }
}

debug_log("=== All files loaded successfully ===");

debug_log("=== Testing Class Instantiation ===");

// Test instantiating Core class
try {
    debug_log("Instantiating PFOB_Core...");
    $core = new PFOB_Core();
    debug_log("✓ PFOB_Core instantiated successfully");
} catch (Throwable $e) {
    debug_log("✗ FAILED to instantiate PFOB_Core", 'ERROR');
    debug_log("Error: " . $e->getMessage(), 'ERROR');
    debug_log("File: " . $e->getFile() . ':' . $e->getLine(), 'ERROR');
    debug_log("Trace: " . $e->getTraceAsString(), 'ERROR');
}

// Test running the plugin
try {
    debug_log("Running plugin...");
    $core->run();
    debug_log("✓ Plugin run() executed successfully");
} catch (Throwable $e) {
    debug_log("✗ FAILED to run plugin", 'ERROR');
    debug_log("Error: " . $e->getMessage(), 'ERROR');
    debug_log("File: " . $e->getFile() . ':' . $e->getLine(), 'ERROR');
    debug_log("Trace: " . $e->getTraceAsString(), 'ERROR');
}

debug_log("=== Testing Activation Hook ===");

// Test activation
try {
    debug_log("Loading activator...");
    require_once PFOB_PLUGIN_DIR . 'includes/class-pfob-activator.php';
    debug_log("✓ Activator loaded");

    debug_log("Calling PFOB_Activator::activate()...");
    PFOB_Activator::activate();
    debug_log("✓ Activation completed successfully");

} catch (Throwable $e) {
    debug_log("✗ ACTIVATION FAILED", 'ERROR');
    debug_log("Error: " . $e->getMessage(), 'ERROR');
    debug_log("File: " . $e->getFile() . ':' . $e->getLine(), 'ERROR');
    debug_log("Trace: " . $e->getTraceAsString(), 'ERROR');
}

debug_log("=== Debug Complete ===");

// Display results
?>
<!DOCTYPE html>
<html>
<head>
    <title>ProjectFOB Activation Debug</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4fc3f7;
            border-bottom: 2px solid #4fc3f7;
            padding-bottom: 10px;
        }
        .log-entry {
            padding: 5px;
            margin: 2px 0;
            border-left: 3px solid transparent;
        }
        .log-entry.info {
            border-left-color: #4fc3f7;
        }
        .log-entry.error {
            background: #3c1f1e;
            border-left-color: #f48771;
            color: #f48771;
        }
        .log-entry.success {
            border-left-color: #8bc34a;
            color: #8bc34a;
        }
        .summary {
            background: #2d2d2d;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .download-log {
            background: #4fc3f7;
            color: #1e1e1e;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        pre {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 ProjectFOB Activation Debug Report</h1>

        <div class="summary">
            <h2>Summary</h2>
            <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
            <p><strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?></p>
            <p><strong>Plugin Directory:</strong> <?php echo $plugin_dir; ?></p>
            <p><strong>Log File:</strong> <code><?php echo $log_file; ?></code></p>
            <p><strong>Total Log Entries:</strong> <?php echo count($debug_output); ?></p>
        </div>

        <h2>Debug Log</h2>
        <div class="log">
            <?php foreach ($debug_output as $entry): ?>
                <?php
                $class = 'info';
                if (strpos($entry, '[ERROR]') !== false) {
                    $class = 'error';
                } elseif (strpos($entry, '✓') !== false) {
                    $class = 'success';
                }
                ?>
                <div class="log-entry <?php echo $class; ?>"><?php echo htmlspecialchars($entry); ?></div>
            <?php endforeach; ?>
        </div>

        <div class="summary">
            <h2>Next Steps</h2>
            <ol>
                <li>Review the log above for any ERROR entries</li>
                <li>The full log has been saved to: <code><?php echo basename($log_file); ?></code></li>
                <li>Share this log with support to diagnose the activation issue</li>
                <li>Check WordPress debug.log for additional errors</li>
            </ol>

            <p><strong>Log file location:</strong><br>
            <code><?php echo $log_file; ?></code></p>
        </div>

        <a href="<?php echo basename($log_file); ?>" class="download-log" download>📥 Download Full Log File</a>
    </div>
</body>
</html>
<?php
ob_end_flush();
