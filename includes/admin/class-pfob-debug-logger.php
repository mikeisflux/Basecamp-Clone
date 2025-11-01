<?php
/**
 * Debug Logger for ProjectFOB
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/admin
 */

class PFOB_Debug_Logger {

    private $log_file;
    private $enabled = false;

    public function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/projectfob-debug.log';
        $this->enabled = get_option('pfob_debug_enabled', false);

        // Admin hooks
        add_action('admin_menu', array($this, 'add_debug_menu'));
        add_action('admin_init', array($this, 'handle_actions'));

        // Only add logging hooks if debug is enabled
        if ($this->enabled) {
            $this->init_logging_hooks();
        }
    }

    private function init_logging_hooks() {
        add_action('init', array($this, 'log_request'), 1);
        add_action('parse_request', array($this, 'log_parse_request'), 1);
        add_action('template_redirect', array($this, 'log_template_redirect'), 1);
        add_action('rest_api_init', array($this, 'log_rest_api_init'), 1);
        add_filter('rest_pre_dispatch', array($this, 'log_rest_pre_dispatch'), 10, 3);
        add_filter('rest_post_dispatch', array($this, 'log_rest_post_dispatch'), 10, 3);
        add_filter('rest_request_after_callbacks', array($this, 'log_rest_response'), 10, 3);
        add_action('wp', array($this, 'log_wp_query'), 1);

        // Log PHP errors
        set_error_handler(array($this, 'log_php_error'));
        register_shutdown_function(array($this, 'log_fatal_error'));

        $this->log('=== DEBUG LOGGER ACTIVE ===');
        $this->log('Request URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        $this->log('Request Method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
    }

    public function log($message, $context = array()) {
        if (!$this->enabled) return;

        $timestamp = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($backtrace[1]) ? basename($backtrace[1]['file']) . ':' . $backtrace[1]['line'] : 'unknown';

        $log_entry = "[{$timestamp}] [{$caller}] {$message}";

        if (!empty($context)) {
            $log_entry .= "\n    Context: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        $log_entry .= "\n";

        error_log($log_entry, 3, $this->log_file);
    }

    public function log_request() {
        $this->log('--- INIT HOOK ---');
        $this->log('Current URL: ' . home_url($_SERVER['REQUEST_URI'] ?? ''));

        if (strpos($_SERVER['REQUEST_URI'] ?? '', 'projectfob') !== false) {
            $this->log('🎯 PROJECTFOB URL DETECTED');

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

        if (strpos($_SERVER['REQUEST_URI'] ?? '', 'wp-json') !== false) {
            $this->log('🔌 REST API REQUEST DETECTED');
        }
    }

    public function log_parse_request($wp) {
        $this->log('--- PARSE_REQUEST HOOK ---');
        $this->log('Matched Query: ' . ($wp->matched_query ?? 'none'));
        $this->log('Matched Rule: ' . ($wp->matched_rule ?? 'none'));
        $this->log('Request: ' . ($wp->request ?? 'none'));
        if (!empty($wp->query_vars)) {
            $this->log('Query Vars:', $wp->query_vars);
        }
    }

    public function log_template_redirect() {
        global $wp_query;

        $this->log('--- TEMPLATE_REDIRECT HOOK ---');
        $this->log('Is 404: ' . ($wp_query->is_404() ? 'YES' : 'NO'));

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
        $this->log('Is 404: ' . ($wp_query->is_404() ? 'YES' : 'NO'));
    }

    public function log_rest_api_init() {
        $this->log('--- REST_API_INIT HOOK ---');

        $server = rest_get_server();
        $routes = $server->get_routes();

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
        }

        return $result;
    }

    public function log_rest_post_dispatch($response, $server, $request) {
        $route = $request->get_route();

        if (strpos($route, 'projectfob') !== false) {
            $this->log('--- REST RESPONSE ---');

            if (is_wp_error($response)) {
                $this->log('❌ REST API ERROR');
                $this->log('Error Code: ' . $response->get_error_code());
                $this->log('Error Message: ' . $response->get_error_message());
                $this->log('Error Data:', $response->get_error_data());
            } else {
                $status = $response->get_status();
                if ($status >= 400) {
                    $this->log('⚠️ REST API ERROR RESPONSE');
                    $this->log('Status: ' . $status);
                    $this->log('Response Data:', $response->get_data());
                } else {
                    $this->log('✅ REST API SUCCESS');
                    $this->log('Status: ' . $status);
                }
            }
        }

        return $response;
    }

    public function log_rest_response($response, $handler, $request) {
        $route = $request->get_route();

        if (strpos($route, 'projectfob') !== false && is_wp_error($response)) {
            $this->log('--- REST CALLBACK ERROR ---');
            $this->log('Route: ' . $route);
            $this->log('Error: ' . $response->get_error_message());
        }

        return $response;
    }

    public function log_php_error($errno, $errstr, $errfile, $errline) {
        if (strpos($errfile, 'projectfob') !== false || strpos($errstr, 'projectfob') !== false || strpos($errstr, 'PFOB') !== false) {
            $this->log("PHP ERROR [{$errno}]: {$errstr} in {$errfile}:{$errline}");
        }
        return false;
    }

    public function log_fatal_error() {
        $error = error_get_last();
        if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR))) {
            if (strpos($error['file'], 'projectfob') !== false || strpos($error['message'], 'projectfob') !== false) {
                $this->log("FATAL ERROR: {$error['message']} in {$error['file']}:{$error['line']}");
            }
        }
    }

    public function handle_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['pfob_clear_log']) && check_admin_referer('pfob_debug_clear')) {
            file_put_contents($this->log_file, '');
            add_settings_error('pfob_debug', 'log_cleared', 'Debug log cleared!', 'success');
        }

        if (isset($_POST['pfob_enable_debug']) && check_admin_referer('pfob_debug_toggle')) {
            update_option('pfob_debug_enabled', true);
            $this->enabled = true;
            add_settings_error('pfob_debug', 'debug_enabled', 'Debug logging enabled! Click some ProjectFOB links to generate logs.', 'success');
        }

        if (isset($_POST['pfob_disable_debug']) && check_admin_referer('pfob_debug_toggle')) {
            update_option('pfob_debug_enabled', false);
            $this->enabled = false;
            add_settings_error('pfob_debug', 'debug_disabled', 'Debug logging disabled.', 'success');
        }
    }

    public function add_debug_menu() {
        add_submenu_page(
            'projectfob-settings',
            'Debug Logger',
            'Debug Logger',
            'manage_options',
            'projectfob-debug',
            array($this, 'display_log_page')
        );
    }

    public function display_log_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        settings_errors('pfob_debug');

        $log_contents = '';
        $log_size_mb = '0.00';

        if (file_exists($this->log_file)) {
            $log_contents = file_get_contents($this->log_file);
            $log_size = filesize($this->log_file);
            $log_size_mb = number_format($log_size / 1024 / 1024, 2);
        } else {
            $log_contents = 'No log file found yet. Enable debug logging and click some ProjectFOB links to generate logs.';
        }

        $lines = explode("\n", $log_contents);
        $recent_lines = array_slice($lines, -500);
        $recent_log = implode("\n", $recent_lines);

        ?>
        <div class="wrap">
            <h1>ProjectFOB Debug Logger</h1>

            <div class="notice notice-info">
                <p><strong>How to use this debugger:</strong></p>
                <ol>
                    <li>Enable debug logging below</li>
                    <li>Click on ProjectFOB links in your site (dashboard, pricing, create project, etc.)</li>
                    <li>Return to this page and refresh to see the logs</li>
                    <li>Look for errors, warnings, or "No route found" messages</li>
                    <li>Download the log for detailed analysis</li>
                    <li>Disable logging when done to save server resources</li>
                </ol>
            </div>

            <div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #ccc; border-radius: 4px;">
                <h2>Debug Status</h2>
                <p><strong>Status:</strong>
                    <?php if ($this->enabled): ?>
                        <span style="color: #46b450; font-weight: bold;">✓ ENABLED</span>
                    <?php else: ?>
                        <span style="color: #dc3232; font-weight: bold;">✗ DISABLED</span>
                    <?php endif; ?>
                </p>
                <p><strong>Log File:</strong> <code><?php echo esc_html($this->log_file); ?></code></p>
                <p><strong>File Size:</strong> <?php echo esc_html($log_size_mb); ?> MB</p>
                <p><strong>Showing:</strong> Last 500 lines</p>
            </div>

            <div style="margin: 20px 0;">
                <?php if ($this->enabled): ?>
                    <form method="post" style="display: inline-block; margin-right: 10px;">
                        <?php wp_nonce_field('pfob_debug_toggle'); ?>
                        <button type="submit" name="pfob_disable_debug" class="button button-secondary">
                            Disable Debug Logging
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" style="display: inline-block; margin-right: 10px;">
                        <?php wp_nonce_field('pfob_debug_toggle'); ?>
                        <button type="submit" name="pfob_enable_debug" class="button button-primary">
                            Enable Debug Logging
                        </button>
                    </form>
                <?php endif; ?>

                <form method="post" style="display: inline-block; margin-right: 10px;">
                    <?php wp_nonce_field('pfob_debug_clear'); ?>
                    <button type="submit" name="pfob_clear_log" class="button" onclick="return confirm('Clear all logs?')">
                        Clear Log
                    </button>
                </form>

                <a href="<?php echo esc_url(content_url('projectfob-debug.log')); ?>" class="button" download>
                    Download Full Log
                </a>

                <button onclick="location.reload()" class="button">
                    Refresh
                </button>
            </div>

            <div style="background: #1e1e1e; color: #d4d4d4; padding: 20px; font-family: 'Courier New', monospace; font-size: 13px; overflow-x: auto; max-height: 600px; overflow-y: auto; border-radius: 4px;">
                <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo esc_html($recent_log); ?></pre>
            </div>

            <div style="margin-top: 20px; background: #e7f3ff; border: 1px solid #2196F3; padding: 15px; border-radius: 4px;">
                <h3 style="margin-top: 0;">🔍 What to Look For in Logs</h3>
                <ul>
                    <li><strong>"No ProjectFOB rewrite rules found"</strong> - Routes not registered, try flushing permalinks</li>
                    <li><strong>"No ProjectFOB query vars found"</strong> - URL not matching routes</li>
                    <li><strong>"Is 404: YES"</strong> - Page not found error</li>
                    <li><strong>"REST API REQUEST DETECTED"</strong> - REST API calls being made</li>
                    <li><strong>"No ProjectFOB REST routes found"</strong> - API endpoints not registered</li>
                    <li><strong>PHP ERROR or FATAL ERROR</strong> - Code execution errors</li>
                </ul>
            </div>

            <div style="margin-top: 20px; background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px;">
                <h3 style="margin-top: 0;">⚠️ Performance Note</h3>
                <p>Debug logging writes to disk on every request and can impact performance.</p>
                <p><strong>Remember to disable debug logging when you're done troubleshooting!</strong></p>
            </div>
        </div>
        <?php
    }
}
