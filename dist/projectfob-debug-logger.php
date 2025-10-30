<?php
/**
 * Plugin Name: ProjectFOB Debug Logger
 * Description: Real-time debugging and logging for ProjectFOB routing issues. Logs every request, route match, and error.
 * Version: 1.0
 * Author: Debug Tool
 */

if (!defined('ABSPATH')) {
    exit;
}

class ProjectFOB_Debug_Logger {

    private $log_file;
    private $enabled = true;

    public function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/projectfob-debug.log';

        // Initialize hooks
        add_action('init', array($this, 'log_request'), 1);
        add_action('parse_request', array($this, 'log_parse_request'), 1);
        add_action('template_redirect', array($this, 'log_template_redirect'), 1);
        add_action('rest_api_init', array($this, 'log_rest_api_init'), 1);
        add_filter('rest_pre_dispatch', array($this, 'log_rest_pre_dispatch'), 10, 3);
        add_action('wp', array($this, 'log_wp_query'), 1);

        // Admin menu to view logs
        add_action('admin_menu', array($this, 'add_debug_menu'));

        // Log PHP errors
        set_error_handler(array($this, 'log_php_error'));
        register_shutdown_function(array($this, 'log_fatal_error'));

        $this->log('=== DEBUG LOGGER INITIALIZED ===');
        $this->log('Request URI: ' . $_SERVER['REQUEST_URI']);
        $this->log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
        $this->log('User Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
    }

    public function log($message, $context = array()) {
        if (!$this->enabled) return;

        $timestamp = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($backtrace[1]) ? basename($backtrace[1]['file']) . ':' . $backtrace[1]['line'] : 'unknown';

        $log_entry = "[{$timestamp}] [{$caller}] {$message}";

        if (!empty($context)) {
            $log_entry .= "\n    Context: " . json_encode($context, JSON_PRETTY_PRINT);
        }

        $log_entry .= "\n";

        error_log($log_entry, 3, $this->log_file);
    }

    public function log_request() {
        global $wp_rewrite;

        $this->log('--- INIT HOOK ---');
        $this->log('Current URL: ' . home_url($_SERVER['REQUEST_URI']));
        $this->log('Query String: ' . ($_SERVER['QUERY_STRING'] ?? 'none'));

        // Check if this is a ProjectFOB URL
        if (strpos($_SERVER['REQUEST_URI'], 'projectfob') !== false) {
            $this->log('🎯 PROJECTFOB URL DETECTED');

            // Log rewrite rules
            $rewrite_rules = get_option('rewrite_rules');
            if (is_array($rewrite_rules)) {
                $matching_rules = array();
                foreach ($rewrite_rules as $pattern => $rewrite) {
                    if (strpos($pattern, 'projectfob') !== false || strpos($rewrite, 'pfob') !== false) {
                        $matching_rules[$pattern] = $rewrite;
                    }
                }

                if (!empty($matching_rules)) {
                    $this->log('ProjectFOB Rewrite Rules Found:', $matching_rules);
                } else {
                    $this->log('⚠️ WARNING: No ProjectFOB rewrite rules found!');
                }
            }
        }

        // Check if this is a REST API request
        if (strpos($_SERVER['REQUEST_URI'], 'wp-json') !== false) {
            $this->log('🔌 REST API REQUEST DETECTED');
            $this->log('REST URL: ' . rest_url());
        }
    }

    public function log_parse_request($wp) {
        $this->log('--- PARSE_REQUEST HOOK ---');
        $this->log('Matched Query: ' . $wp->matched_query);
        $this->log('Matched Rule: ' . $wp->matched_rule);
        $this->log('Request: ' . $wp->request);
        $this->log('Query Vars:', $wp->query_vars);
    }

    public function log_template_redirect() {
        global $wp_query;

        $this->log('--- TEMPLATE_REDIRECT HOOK ---');
        $this->log('Is 404: ' . ($wp_query->is_404() ? 'YES' : 'NO'));
        $this->log('Queried Object: ' . get_class($wp_query->get_queried_object() ?: new stdClass()));

        // Check for ProjectFOB query vars
        $pfob_page = get_query_var('pfob_page');
        $pfob_public_page = get_query_var('pfob_public_page');

        if ($pfob_page) {
            $this->log('✅ ProjectFOB Page Found: ' . $pfob_page);
        } elseif ($pfob_public_page) {
            $this->log('✅ ProjectFOB Public Page Found: ' . $pfob_public_page);
        } else {
            $this->log('❌ No ProjectFOB query vars found');
        }
    }

    public function log_wp_query() {
        global $wp_query;

        $this->log('--- WP HOOK (Main Query) ---');
        $this->log('Query Vars:', $wp_query->query_vars);
        $this->log('Is Main Query: ' . ($wp_query->is_main_query() ? 'YES' : 'NO'));
        $this->log('Is 404: ' . ($wp_query->is_404() ? 'YES' : 'NO'));
    }

    public function log_rest_api_init() {
        $this->log('--- REST_API_INIT HOOK ---');

        // Get all registered routes
        $server = rest_get_server();
        $routes = $server->get_routes();

        // Filter ProjectFOB routes
        $pfob_routes = array();
        foreach ($routes as $route => $handlers) {
            if (strpos($route, 'projectfob') !== false) {
                $pfob_routes[] = $route;
            }
        }

        if (!empty($pfob_routes)) {
            $this->log('✅ ProjectFOB REST Routes Registered:', $pfob_routes);
        } else {
            $this->log('❌ WARNING: No ProjectFOB REST routes found!');
        }
    }

    public function log_rest_pre_dispatch($result, $server, $request) {
        $route = $request->get_route();

        if (strpos($route, 'projectfob') !== false) {
            $this->log('--- REST REQUEST ---');
            $this->log('Route: ' . $route);
            $this->log('Method: ' . $request->get_method());
            $this->log('Params:', $request->get_params());
            $this->log('Headers:', $request->get_headers());
        }

        return $result;
    }

    public function log_php_error($errno, $errstr, $errfile, $errline) {
        // Only log errors related to ProjectFOB
        if (strpos($errfile, 'projectfob') !== false || strpos($errstr, 'projectfob') !== false || strpos($errstr, 'PFOB') !== false) {
            $this->log("PHP ERROR [{$errno}]: {$errstr} in {$errfile}:{$errline}");
        }

        // Return false to continue with normal error handler
        return false;
    }

    public function log_fatal_error() {
        $error = error_get_last();
        if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR)) {
            if (strpos($error['file'], 'projectfob') !== false || strpos($error['message'], 'projectfob') !== false || strpos($error['message'], 'PFOB') !== false) {
                $this->log("FATAL ERROR: {$error['message']} in {$error['file']}:{$error['line']}");
            }
        }
    }

    public function add_debug_menu() {
        add_menu_page(
            'ProjectFOB Debug Log',
            'Debug Log',
            'manage_options',
            'projectfob-debug-log',
            array($this, 'display_log_page'),
            'dashicons-search',
            100
        );
    }

    public function display_log_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Handle actions
        if (isset($_POST['clear_log']) && check_admin_referer('pfob_debug_clear')) {
            file_put_contents($this->log_file, '');
            echo '<div class="notice notice-success"><p>Log cleared!</p></div>';
        }

        if (isset($_POST['disable_debug']) && check_admin_referer('pfob_debug_disable')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_redirect(admin_url('plugins.php'));
            exit;
        }

        // Read log file
        $log_contents = '';
        if (file_exists($this->log_file)) {
            $log_contents = file_get_contents($this->log_file);
            $log_size = filesize($this->log_file);
            $log_size_mb = number_format($log_size / 1024 / 1024, 2);
        } else {
            $log_contents = 'No log file found yet. Click some ProjectFOB links to generate logs.';
            $log_size_mb = '0.00';
        }

        // Get last 100 lines
        $lines = explode("\n", $log_contents);
        $recent_lines = array_slice($lines, -500);
        $recent_log = implode("\n", $recent_lines);

        ?>
        <div class="wrap">
            <h1>ProjectFOB Debug Log</h1>

            <div class="notice notice-info">
                <p><strong>How to use this debugger:</strong></p>
                <ol>
                    <li>Click on ProjectFOB links in your site (dashboard, pricing, create project, etc.)</li>
                    <li>Return to this page and refresh to see the logs</li>
                    <li>Look for errors, warnings, or "No route found" messages</li>
                    <li>Copy the entire log and send it for analysis</li>
                </ol>
            </div>

            <div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #ccc; border-radius: 4px;">
                <h2>Log Information</h2>
                <p><strong>Log File:</strong> <code><?php echo esc_html($this->log_file); ?></code></p>
                <p><strong>File Size:</strong> <?php echo esc_html($log_size_mb); ?> MB</p>
                <p><strong>Showing:</strong> Last 500 lines</p>
            </div>

            <div style="margin: 20px 0;">
                <form method="post" style="display: inline-block; margin-right: 10px;">
                    <?php wp_nonce_field('pfob_debug_clear'); ?>
                    <button type="submit" name="clear_log" class="button" onclick="return confirm('Clear all logs?')">Clear Log</button>
                </form>

                <a href="<?php echo esc_url(content_url('projectfob-debug.log')); ?>" class="button" download>Download Full Log</a>

                <button onclick="location.reload()" class="button button-primary">Refresh Log</button>

                <form method="post" style="display: inline-block; margin-left: 10px;">
                    <?php wp_nonce_field('pfob_debug_disable'); ?>
                    <button type="submit" name="disable_debug" class="button button-secondary" onclick="return confirm('Disable debug logging?')">Disable Debugger</button>
                </form>
            </div>

            <div style="background: #1e1e1e; color: #d4d4d4; padding: 20px; font-family: 'Courier New', monospace; font-size: 13px; overflow-x: auto; max-height: 600px; overflow-y: auto; border-radius: 4px;">
                <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo esc_html($recent_log); ?></pre>
            </div>

            <div style="margin-top: 20px; background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px;">
                <h3 style="margin-top: 0;">⚠️ Important Notes</h3>
                <ul>
                    <li>This debugger logs ALL requests to ProjectFOB URLs</li>
                    <li>Logs include query vars, rewrite rules, REST API calls, and errors</li>
                    <li>The log file can grow large - clear it regularly</li>
                    <li><strong>Disable this plugin when you're done debugging</strong></li>
                    <li>Log file location: <code>wp-content/projectfob-debug.log</code></li>
                </ul>
            </div>

            <div style="margin-top: 20px; background: #e7f3ff; border: 1px solid #2196F3; padding: 15px; border-radius: 4px;">
                <h3 style="margin-top: 0;">🔍 What to Look For</h3>
                <ul>
                    <li><strong>"No ProjectFOB rewrite rules found"</strong> - Rules not registered</li>
                    <li><strong>"No ProjectFOB query vars found"</strong> - Routes not matching</li>
                    <li><strong>"Is 404: YES"</strong> - Page not found</li>
                    <li><strong>"REST API REQUEST DETECTED"</strong> - REST API calls</li>
                    <li><strong>PHP ERROR or FATAL ERROR</strong> - Code errors</li>
                    <li><strong>"No ProjectFOB REST routes found"</strong> - API not registered</li>
                </ul>
            </div>
        </div>
        <?php
    }
}

// Initialize the debug logger
new ProjectFOB_Debug_Logger();
