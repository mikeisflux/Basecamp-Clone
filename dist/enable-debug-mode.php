<?php
/**
 * ProjectFOB Debug Mode Enabler
 *
 * INSTRUCTIONS:
 * 1. Upload this file to your WordPress root directory
 * 2. Access it in your browser: https://yourdomain.com/enable-debug-mode.php
 * 3. This will enable debug logging in the plugin
 * 4. Try to activate the plugin again
 * 5. Check the log file: wp-content/plugins/projectfob/debug.log
 */

if (!file_exists(__DIR__ . '/wp-load.php')) {
    die('ERROR: This file must be placed in your WordPress root directory.');
}

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

$plugin_dir = WP_PLUGIN_DIR . '/projectfob/';
$debug_file = $plugin_dir . 'ENABLE_DEBUG';

// Check if plugin directory exists
if (!is_dir($plugin_dir)) {
    die("ERROR: Plugin directory not found at: {$plugin_dir}\n\n" .
        "Please make sure you have:\n" .
        "1. Uploaded and extracted projectfob-2.1.1.zip to wp-content/plugins/\n" .
        "2. The plugin folder exists at wp-content/plugins/projectfob/\n\n" .
        "Current plugins directory: " . WP_PLUGIN_DIR);
}

// Check if directory is writable
if (!is_writable($plugin_dir)) {
    die("ERROR: Plugin directory is not writable: {$plugin_dir}\n\n" .
        "Please check file permissions. The directory must be writable by the web server.");
}

// Create debug flag file
if (file_put_contents($debug_file, date('Y-m-d H:i:s'))) {
    echo "✓ Debug mode enabled for ProjectFOB\n\n";
    echo "Now try to activate the plugin through WordPress Admin.\n\n";
    echo "Debug log will be written to:\n";
    echo $plugin_dir . "debug.log\n\n";
    echo "After activation attempt, download the debug.log file and check for errors.";
} else {
    echo "ERROR: Could not create debug file. Unknown error.";
}
