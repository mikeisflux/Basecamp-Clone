<?php
/**
 * Test Activation Script
 * Simulates plugin activation to find fatal errors
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== ProjectFOB Activation Test ===\n\n";

// Simulate WordPress environment minimally
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Define plugin constants
define('PFOB_VERSION', '2.0.7');
define('PFOB_PLUGIN_DIR', __DIR__ . '/');
define('PFOB_PLUGIN_URL', 'http://example.com/wp-content/plugins/projectfob/');

echo "Step 1: Loading database helper...\n";
try {
    require_once PFOB_PLUGIN_DIR . 'includes/database/class-pfob-database.php';
    echo "✓ Database class loaded\n";
} catch (Throwable $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nStep 2: Loading models...\n";
$models = [
    'includes/models/class-pfob-project.php',
    'includes/models/class-pfob-message.php',
    'includes/models/class-pfob-todo.php',
    'includes/models/class-pfob-todolist.php',
    'includes/models/class-pfob-document.php',
    'includes/models/class-pfob-chat-room.php',
    'includes/models/class-pfob-chat-message.php',
    'includes/models/class-pfob-event.php',
    'includes/models/class-pfob-card.php',
    'includes/models/class-pfob-card-column.php',
    'includes/models/class-pfob-comment.php',
    'includes/models/class-pfob-activity.php',
];

foreach ($models as $model) {
    try {
        require_once PFOB_PLUGIN_DIR . $model;
        echo "✓ " . basename($model) . "\n";
    } catch (Throwable $e) {
        echo "✗ FAILED on " . basename($model) . ": " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
}

echo "\nStep 3: Loading services...\n";
$services = [
    'includes/services/class-pfob-auth-service.php',
    'includes/services/class-pfob-permission-service.php',
    'includes/services/class-pfob-notification-service.php',
    'includes/services/class-pfob-activity-service.php',
    'includes/services/class-pfob-search-service.php',
    'includes/services/class-pfob-email-service.php',
    'includes/services/class-pfob-digest-service.php',
    'includes/services/class-pfob-subscription-service.php',
    'includes/services/class-pfob-paypal-service.php',
    'includes/services/class-pfob-storage-service.php',
    'includes/services/class-pfob-file-service.php',
    'includes/services/class-pfob-google-calendar-service.php',
    'includes/services/class-pfob-websocket-service.php',
];

foreach ($services as $service) {
    try {
        require_once PFOB_PLUGIN_DIR . $service;
        echo "✓ " . basename($service) . "\n";
    } catch (Throwable $e) {
        echo "✗ FAILED on " . basename($service) . ": " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
}

echo "\nStep 4: Loading frontend helpers...\n";
$frontend = [
    'includes/frontend/class-pfob-router.php',
    'includes/frontend/class-pfob-assets.php',
    'includes/frontend/class-pfob-template.php',
];

foreach ($frontend as $file) {
    try {
        require_once PFOB_PLUGIN_DIR . $file;
        echo "✓ " . basename($file) . "\n";
    } catch (Throwable $e) {
        echo "✗ FAILED on " . basename($file) . ": " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
}

echo "\nStep 5: Loading REST API base...\n";
try {
    require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-rest-api.php';
    echo "✓ REST API base loaded\n";
} catch (Throwable $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\nStep 6: Loading REST API endpoints...\n";
$endpoints = [
    'includes/api/class-pfob-projects-endpoint.php',
    'includes/api/class-pfob-messages-endpoint.php',
    'includes/api/class-pfob-todos-endpoint.php',
    'includes/api/class-pfob-documents-endpoint.php',
    'includes/api/class-pfob-chat-endpoint.php',
    'includes/api/class-pfob-schedule-endpoint.php',
    'includes/api/class-pfob-cards-endpoint.php',
    'includes/api/class-pfob-activities-endpoint.php',
    'includes/api/class-pfob-comments-endpoint.php',
    'includes/api/class-pfob-search-endpoint.php',
    'includes/api/class-pfob-events-endpoint.php',
    'includes/api/class-pfob-import-export-endpoint.php',
    'includes/api/class-pfob-settings-endpoint.php',
    'includes/api/class-pfob-analytics-endpoint.php',
    'includes/api/class-pfob-calendar-integration-endpoint.php',
    'includes/api/class-pfob-notifications-endpoint.php',
    'includes/api/class-pfob-paypal-webhook-endpoint.php',
    'includes/api/class-pfob-subscription-endpoint.php',
];

foreach ($endpoints as $endpoint) {
    try {
        require_once PFOB_PLUGIN_DIR . $endpoint;
        echo "✓ " . basename($endpoint) . "\n";
    } catch (Throwable $e) {
        echo "✗ FAILED on " . basename($endpoint) . ": " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
}

echo "\nStep 7: Loading Core class...\n";
try {
    require_once PFOB_PLUGIN_DIR . 'includes/class-pfob-core.php';
    echo "✓ Core class loaded\n";
} catch (Throwable $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\nStep 8: Instantiating Core class (THIS IS WHERE WORDPRESS ACTIVATES)...\n";
try {
    $plugin = new PFOB_Core();
    echo "✓ Core instantiated successfully!\n";
} catch (Throwable $e) {
    echo "✗ FATAL ERROR during instantiation: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nStep 9: Running plugin...\n";
try {
    $plugin->run();
    echo "✓ Plugin run successfully!\n";
} catch (Throwable $e) {
    echo "✗ FAILED during run: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== ALL TESTS PASSED ===\n";
echo "The plugin should activate successfully in WordPress.\n";
