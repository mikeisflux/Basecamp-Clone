<?php
/**
 * HostGator Managed WordPress Compatibility Test
 *
 * INSTRUCTIONS:
 * 1. Upload this file to your WordPress root directory
 * 2. Access it in browser: https://yourdomain.com/test-hosting-compatibility.php
 * 3. Review the results
 * 4. DELETE this file after testing
 */

// Load WordPress
require_once( __DIR__ . '/wp-load.php' );

?>
<!DOCTYPE html>
<html>
<head>
    <title>HostGator Managed WordPress Compatibility Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #0073aa;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #ccc;
            background: #f9f9f9;
        }
        .test-item.pass {
            border-left-color: #46b450;
            background: #f0f9f0;
        }
        .test-item.fail {
            border-left-color: #dc3232;
            background: #fef7f7;
        }
        .test-item.warn {
            border-left-color: #ffb900;
            background: #fff8e5;
        }
        .status {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        .status.pass { color: #46b450; }
        .status.fail { color: #dc3232; }
        .status.warn { color: #ffb900; }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        pre {
            background: #272822;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 13px;
        }
        .info-box {
            background: #e7f2fa;
            border: 1px solid #b8daf0;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .critical {
            background: #fef7f7;
            border: 2px solid #dc3232;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 HostGator Managed WordPress Compatibility Test</h1>
        <p><strong>Testing ProjectFOB compatibility with HostGator Managed WordPress Hosting</strong></p>

        <?php
        $tests = array();
        $critical_issues = array();
        $warnings = array();

        // Test 1: Web Server Detection
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        $is_nginx = stripos($server_software, 'nginx') !== false;
        $is_apache = stripos($server_software, 'apache') !== false;

        $tests[] = array(
            'name' => 'Web Server',
            'status' => 'pass',
            'message' => "Server: {$server_software}",
            'details' => $is_nginx ? 'Using Nginx - This is common on managed WordPress hosting.' : 'Using Apache or other server.'
        );

        // Test 2: PHP Version
        $php_version = PHP_VERSION;
        $php_ok = version_compare($php_version, '8.3', '>=');

        $tests[] = array(
            'name' => 'PHP Version',
            'status' => $php_ok ? 'pass' : 'fail',
            'message' => "PHP {$php_version}",
            'details' => $php_ok ? 'Compatible with ProjectFOB requirements' : 'ProjectFOB requires PHP 8.3 or higher'
        );

        if (!$php_ok) {
            $critical_issues[] = 'PHP version is too low. Contact HostGator to upgrade to PHP 8.3.';
        }

        // Test 3: Permalink Structure
        $permalink_structure = get_option('permalink_structure');
        $permalink_ok = !empty($permalink_structure);

        $tests[] = array(
            'name' => 'Permalink Structure',
            'status' => $permalink_ok ? 'pass' : 'fail',
            'message' => $permalink_ok ? "Custom permalinks enabled: {$permalink_structure}" : 'Using PLAIN permalinks',
            'details' => $permalink_ok ? 'Good for custom routes' : 'ProjectFOB REQUIRES custom permalinks. Go to Settings → Permalinks and select "Post name"'
        );

        if (!$permalink_ok) {
            $critical_issues[] = 'Permalinks are set to PLAIN. Change to "Post name" in Settings → Permalinks.';
        }

        // Test 4: REST API Accessibility
        $rest_url = rest_url();
        $rest_accessible = false;
        $rest_response = wp_remote_get($rest_url);

        if (!is_wp_error($rest_response)) {
            $rest_code = wp_remote_retrieve_response_code($rest_response);
            $rest_accessible = ($rest_code == 200 || $rest_code == 301 || $rest_code == 302);
        }

        $tests[] = array(
            'name' => 'REST API',
            'status' => $rest_accessible ? 'pass' : 'fail',
            'message' => $rest_accessible ? 'REST API is accessible' : 'REST API is NOT accessible',
            'details' => "REST URL: {$rest_url} - " . ($rest_accessible ? 'Working correctly' : 'Cannot be accessed - this will break ProjectFOB')
        );

        if (!$rest_accessible) {
            $critical_issues[] = 'REST API is blocked or inaccessible. This WILL break ProjectFOB. Contact HostGator support.';
        }

        // Test 5: ProjectFOB Plugin Status
        $projectfob_active = is_plugin_active('projectfob/projectfob.php');

        $tests[] = array(
            'name' => 'ProjectFOB Plugin',
            'status' => $projectfob_active ? 'pass' : 'warn',
            'message' => $projectfob_active ? 'Plugin is ACTIVE' : 'Plugin is NOT active',
            'details' => $projectfob_active ? 'ProjectFOB is installed and activated' : 'Activate ProjectFOB plugin to continue testing'
        );

        // Test 6: Test ProjectFOB REST Endpoint (if active)
        if ($projectfob_active) {
            $pfob_rest_url = rest_url('projectfob/v1/subscription/plans');
            $pfob_rest_response = wp_remote_get($pfob_rest_url, array('timeout' => 10));
            $pfob_rest_ok = false;

            if (!is_wp_error($pfob_rest_response)) {
                $pfob_code = wp_remote_retrieve_response_code($pfob_rest_response);
                $pfob_rest_ok = ($pfob_code == 200);
            }

            $tests[] = array(
                'name' => 'ProjectFOB REST Endpoint',
                'status' => $pfob_rest_ok ? 'pass' : 'fail',
                'message' => $pfob_rest_ok ? 'ProjectFOB API endpoints are working' : 'ProjectFOB API endpoints are NOT responding',
                'details' => "Testing: {$pfob_rest_url} - " . ($pfob_rest_ok ? 'Success!' : 'Failed - check if plugin is properly activated')
            );

            if (!$pfob_rest_ok) {
                $critical_issues[] = 'ProjectFOB REST API endpoints are not working. Try deactivating and reactivating the plugin.';
            }
        }

        // Test 7: Rewrite Rules
        global $wp_rewrite;
        $rewrite_rules = get_option('rewrite_rules');
        $has_projectfob_rules = false;

        if (is_array($rewrite_rules)) {
            foreach ($rewrite_rules as $rule => $rewrite) {
                if (strpos($rule, 'projectfob') !== false || strpos($rewrite, 'pfob') !== false) {
                    $has_projectfob_rules = true;
                    break;
                }
            }
        }

        $tests[] = array(
            'name' => 'Rewrite Rules',
            'status' => $has_projectfob_rules ? 'pass' : 'warn',
            'message' => $has_projectfob_rules ? 'ProjectFOB routes are registered' : 'ProjectFOB routes NOT found in rewrite rules',
            'details' => $has_projectfob_rules ? 'Custom routes should work' : 'Try: Deactivate plugin → Reactivate plugin → Go to Settings → Permalinks → Save'
        );

        if ($projectfob_active && !$has_projectfob_rules) {
            $warnings[] = 'ProjectFOB routes not registered. Flush permalinks: Settings → Permalinks → Save Changes';
        }

        // Test 8: Caching Detection
        $cache_plugins = array(
            'wp-super-cache/wp-cache.php' => 'WP Super Cache',
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
            'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
            'wp-optimize/wp-optimize.php' => 'WP-Optimize',
            'autoptimize/autoptimize.php' => 'Autoptimize',
        );

        $active_cache = array();
        foreach ($cache_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $active_cache[] = $plugin_name;
            }
        }

        // Check for HostGator caching
        if (function_exists('endurance_cache_clear')) {
            $active_cache[] = 'HostGator Endurance Cache';
        }

        $has_caching = !empty($active_cache);

        $tests[] = array(
            'name' => 'Caching',
            'status' => 'warn',
            'message' => $has_caching ? 'Caching detected: ' . implode(', ', $active_cache) : 'No major caching plugins detected',
            'details' => $has_caching ? 'Caching can interfere with dynamic content. You may need to exclude ProjectFOB URLs from cache.' : 'HostGator may still have server-level caching.'
        );

        if ($has_caching) {
            $warnings[] = 'Caching detected. If you experience issues, exclude /projectfob/* from caching.';
        }

        // Test 9: File Permissions (wp-content)
        $wp_content_writable = is_writable(WP_CONTENT_DIR);

        $tests[] = array(
            'name' => 'File Permissions',
            'status' => $wp_content_writable ? 'pass' : 'warn',
            'message' => $wp_content_writable ? 'wp-content directory is writable' : 'wp-content directory is NOT writable',
            'details' => $wp_content_writable ? 'Plugin can write files if needed' : 'May limit plugin functionality'
        );

        // Display Results
        echo '<h2>📊 Test Results</h2>';

        foreach ($tests as $test) {
            $class = $test['status'];
            echo '<div class="test-item ' . esc_attr($class) . '">';
            echo '<div><strong>' . esc_html($test['name']) . '</strong> ';
            echo '<span class="status ' . esc_attr($class) . '">' . strtoupper($test['status']) . '</span></div>';
            echo '<div style="margin-top: 5px;">' . esc_html($test['message']) . '</div>';
            if (!empty($test['details'])) {
                echo '<div style="margin-top: 5px; color: #666; font-size: 13px;">' . esc_html($test['details']) . '</div>';
            }
            echo '</div>';
        }

        // Critical Issues
        if (!empty($critical_issues)) {
            echo '<div class="critical">';
            echo '<h2 style="color: #dc3232; margin-top: 0;">🚨 CRITICAL ISSUES - Must Fix</h2>';
            echo '<ul>';
            foreach ($critical_issues as $issue) {
                echo '<li><strong>' . esc_html($issue) . '</strong></li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // Warnings
        if (!empty($warnings)) {
            echo '<div class="info-box">';
            echo '<h3 style="margin-top: 0; color: #ffb900;">⚠️ Warnings</h3>';
            echo '<ul>';
            foreach ($warnings as $warning) {
                echo '<li>' . esc_html($warning) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // HostGator Specific Recommendations
        echo '<h2>🎯 HostGator Managed WordPress Recommendations</h2>';
        echo '<div class="info-box">';
        echo '<h3 style="margin-top: 0;">Steps to Fix "No route was found" Error:</h3>';
        echo '<ol>';
        echo '<li><strong>Set Permalinks to "Post name"</strong><br>';
        echo 'Go to WordPress Admin → Settings → Permalinks → Select "Post name" → Save Changes</li>';
        echo '<li><strong>Flush Rewrite Rules</strong><br>';
        echo 'Deactivate ProjectFOB plugin → Reactivate it → Go to Settings → Permalinks → Save again</li>';
        echo '<li><strong>Clear All Caches</strong><br>';
        echo 'HostGator has multiple cache layers: Plugin cache, Server cache, Browser cache. Clear them all.</li>';
        echo '<li><strong>Exclude ProjectFOB from Caching</strong><br>';
        echo 'If using cache plugins, exclude these URLs from caching:<br>';
        echo '<code>/projectfob/*</code><br>';
        echo '<code>/wp-json/projectfob/*</code></li>';
        echo '<li><strong>Contact HostGator Support if REST API is Blocked</strong><br>';
        echo 'Some managed hosting blocks REST API by default. Ask them to whitelist it.</li>';
        echo '</ol>';
        echo '</div>';

        // Environment Info
        echo '<h2>🔧 Environment Information</h2>';
        echo '<pre>';
        echo 'WordPress Version:  ' . get_bloginfo('version') . "\n";
        echo 'PHP Version:        ' . PHP_VERSION . "\n";
        echo 'Web Server:         ' . $server_software . "\n";
        echo 'Document Root:      ' . $_SERVER['DOCUMENT_ROOT'] . "\n";
        echo 'REST API URL:       ' . rest_url() . "\n";
        echo 'Site URL:           ' . site_url() . "\n";
        echo 'Home URL:           ' . home_url() . "\n";
        echo 'Permalink Struct:   ' . ($permalink_structure ?: 'PLAIN (NOT GOOD)') . "\n";
        echo 'Multisite:          ' . (is_multisite() ? 'Yes' : 'No') . "\n";
        echo '</pre>';

        ?>

        <div style="margin-top: 30px; padding: 20px; background: #fffbea; border: 1px solid #ffd966; border-radius: 4px;">
            <h3 style="margin-top: 0;">⚠️ Security Reminder</h3>
            <p><strong>DELETE this file after testing!</strong></p>
            <p>File location: <code>test-hosting-compatibility.php</code> in your WordPress root directory</p>
        </div>
    </div>
</body>
</html>
