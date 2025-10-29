<?php
/**
 * ProjectFOB REST API Diagnostic Tool
 *
 * Place this file in your WordPress root directory and visit it in your browser
 * to see detailed information about REST API routes and class loading.
 *
 * Usage: http://yoursite.com/debug-rest-api.php
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/wp-load.php' );

// Security check - only admins can see this
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You do not have permission to access this page.' );
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ProjectFOB REST API Diagnostics</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 20px; background: #f0f0f1; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { color: #1d2327; margin-top: 0; }
        h2 { color: #2271b1; border-bottom: 2px solid #2271b1; padding-bottom: 10px; margin-top: 30px; }
        .success { color: #00a32a; font-weight: bold; }
        .error { color: #d63638; font-weight: bold; }
        .warning { color: #dba617; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f6f7f7; font-weight: 600; }
        .code { background: #f6f7f7; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        pre { background: #1d2327; color: #f0f0f1; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .info-box { background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 ProjectFOB REST API Diagnostics</h1>

        <?php
        // Check if plugin is active
        echo '<h2>1. Plugin Status</h2>';
        $plugin_file = 'projectfob/projectfob.php';
        if ( is_plugin_active( $plugin_file ) ) {
            echo '<p class="success">✓ ProjectFOB plugin is ACTIVE</p>';
            echo '<p>Plugin Version: <span class="code">' . ( defined( 'PFOB_VERSION' ) ? PFOB_VERSION : 'Unknown' ) . '</span></p>';
            echo '<p>Plugin Directory: <span class="code">' . ( defined( 'PFOB_PLUGIN_DIR' ) ? PFOB_PLUGIN_DIR : 'Not defined' ) . '</span></p>';
        } else {
            echo '<p class="error">✗ ProjectFOB plugin is NOT ACTIVE</p>';
            echo '<p>Please activate the plugin first.</p>';
            exit;
        }

        // Check if required classes are loaded
        echo '<h2>2. Required Classes</h2>';
        $required_classes = array(
            'PFOB_Core' => 'Core Plugin Class',
            'PFOB_Router' => 'Router',
            'PFOB_REST_API' => 'REST API Base',
            'PFOB_Database' => 'Database Helper',
            'PFOB_Project' => 'Project Model',
            'PFOB_Message' => 'Message Model',
            'PFOB_Todo' => 'Todo Model',
            'PFOB_Auth_Service' => 'Auth Service',
            'PFOB_Permission_Service' => 'Permission Service',
            'PFOB_Subscription' => 'Subscription Model',
            'PFOB_Projects_Endpoint' => 'Projects API Endpoint',
            'PFOB_Messages_Endpoint' => 'Messages API Endpoint',
        );

        echo '<table>';
        echo '<thead><tr><th>Class Name</th><th>Description</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        foreach ( $required_classes as $class => $description ) {
            $exists = class_exists( $class );
            $status = $exists ? '<span class="success">✓ Loaded</span>' : '<span class="error">✗ Not Found</span>';
            echo "<tr><td class='code'>$class</td><td>$description</td><td>$status</td></tr>";
        }
        echo '</tbody></table>';

        // Check REST API routes
        echo '<h2>3. ProjectFOB REST API Routes</h2>';

        $rest_server = rest_get_server();
        $namespaces = $rest_server->get_namespaces();

        if ( in_array( 'pfob/v1', $namespaces ) ) {
            echo '<p class="success">✓ ProjectFOB namespace (pfob/v1) is registered</p>';
        } else {
            echo '<p class="error">✗ ProjectFOB namespace (pfob/v1) is NOT registered</p>';
            echo '<p class="warning">This means REST API routes are not being registered. Check if PFOB_REST_API::register_routes() is being called.</p>';
        }

        echo '<h3>All Registered pfob/v1 Routes:</h3>';
        $routes = $rest_server->get_routes();
        $pfob_routes = array();

        foreach ( $routes as $route => $route_data ) {
            if ( strpos( $route, '/pfob/v1' ) === 0 ) {
                $pfob_routes[] = $route;
            }
        }

        if ( ! empty( $pfob_routes ) ) {
            echo '<p>Found <strong>' . count( $pfob_routes ) . '</strong> ProjectFOB routes:</p>';
            echo '<table>';
            echo '<thead><tr><th>Route Pattern</th><th>Methods</th></tr></thead>';
            echo '<tbody>';
            foreach ( $pfob_routes as $route ) {
                $route_data = $routes[ $route ];
                $methods = array();
                foreach ( $route_data as $endpoint ) {
                    if ( isset( $endpoint['methods'] ) ) {
                        $methods = array_merge( $methods, array_keys( $endpoint['methods'] ) );
                    }
                }
                $methods = array_unique( $methods );
                echo '<tr>';
                echo '<td class="code">' . esc_html( $route ) . '</td>';
                echo '<td class="code">' . esc_html( implode( ', ', $methods ) ) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p class="error">No ProjectFOB routes found!</p>';
            echo '<div class="info-box">';
            echo '<strong>Troubleshooting:</strong><br>';
            echo '1. Check if PFOB_REST_API class exists and has register_routes() method<br>';
            echo '2. Check if register_routes() is hooked to rest_api_init action<br>';
            echo '3. Check if endpoint classes are being instantiated in register_routes()<br>';
            echo '4. Check PHP error logs for any fatal errors during plugin initialization';
            echo '</div>';
        }

        // Check rewrite rules
        echo '<h2>4. Rewrite Rules</h2>';
        $rules = get_option( 'rewrite_rules' );
        $projectfob_rules = array();

        if ( is_array( $rules ) ) {
            foreach ( $rules as $pattern => $rewrite ) {
                if ( strpos( $pattern, 'projectfob' ) !== false ) {
                    $projectfob_rules[ $pattern ] = $rewrite;
                }
            }
        }

        if ( ! empty( $projectfob_rules ) ) {
            echo '<p class="success">✓ Found ' . count( $projectfob_rules ) . ' ProjectFOB rewrite rules</p>';
            echo '<table>';
            echo '<thead><tr><th>Pattern</th><th>Rewrite To</th></tr></thead>';
            echo '<tbody>';
            foreach ( $projectfob_rules as $pattern => $rewrite ) {
                echo '<tr>';
                echo '<td class="code">' . esc_html( $pattern ) . '</td>';
                echo '<td class="code">' . esc_html( $rewrite ) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p class="error">✗ No ProjectFOB rewrite rules found</p>';
            echo '<p class="warning">Try visiting Settings > Permalinks in WordPress admin and clicking "Save Changes" to flush rewrite rules.</p>';
        }

        // Check current user subscription
        echo '<h2>5. Current User Subscription Status</h2>';
        $current_user = wp_get_current_user();
        echo '<p>Logged in as: <strong>' . $current_user->display_name . '</strong></p>';
        echo '<p>User ID: <span class="code">' . $current_user->ID . '</span></p>';

        if ( current_user_can( 'manage_options' ) ) {
            echo '<p class="success">✓ You are an admin - subscription checks are bypassed</p>';
        } else {
            if ( class_exists( 'PFOB_Subscription' ) ) {
                $subscription = PFOB_Subscription::get_by_user_id( $current_user->ID );
                if ( $subscription ) {
                    echo '<p>Subscription Status: <span class="code">' . $subscription->status . '</span></p>';
                    echo '<p>Plan: <span class="code">' . $subscription->plan_id . '</span></p>';

                    if ( PFOB_Subscription::is_active( $current_user->ID ) ) {
                        echo '<p class="success">✓ Subscription is ACTIVE</p>';
                    } else {
                        echo '<p class="error">✗ Subscription is NOT ACTIVE</p>';
                    }
                } else {
                    echo '<p class="warning">⚠ No subscription found for this user</p>';
                    echo '<p>Non-admin users without active subscriptions will be redirected to the pricing page.</p>';
                }
            } else {
                echo '<p class="error">✗ PFOB_Subscription class not found</p>';
            }
        }

        // Test REST API endpoint
        echo '<h2>6. REST API Test</h2>';
        echo '<p>Testing a sample REST API call...</p>';

        $test_url = rest_url( 'pfob/v1/projects' );
        echo '<p>Test URL: <span class="code">' . $test_url . '</span></p>';

        $response = wp_remote_get( $test_url, array(
            'headers' => array(
                'X-WP-Nonce' => wp_create_nonce( 'wp_rest' ),
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            echo '<p class="error">✗ Request failed: ' . $response->get_error_message() . '</p>';
        } else {
            $status_code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );

            echo '<p>Response Code: <span class="code">' . $status_code . '</span></p>';

            if ( $status_code === 200 ) {
                echo '<p class="success">✓ REST API endpoint is working!</p>';
            } elseif ( $status_code === 401 || $status_code === 403 ) {
                echo '<p class="warning">⚠ Authentication/permission issue (expected if not logged in or no subscription)</p>';
            } else {
                echo '<p class="error">✗ Unexpected response code</p>';
            }

            echo '<p><strong>Response Body:</strong></p>';
            echo '<pre>' . esc_html( $body ) . '</pre>';
        }

        // Action hooks check
        echo '<h2>7. WordPress Hooks</h2>';
        echo '<p>Checking if ProjectFOB hooks are registered...</p>';

        global $wp_filter;

        $hooks_to_check = array(
            'rest_api_init' => 'REST API initialization',
            'init' => 'WordPress init',
            'template_redirect' => 'Template redirect (for custom routing)',
        );

        echo '<table>';
        echo '<thead><tr><th>Hook</th><th>Description</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        foreach ( $hooks_to_check as $hook => $description ) {
            $has_pfob_hooks = false;
            if ( isset( $wp_filter[ $hook ] ) ) {
                foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
                    foreach ( $callbacks as $callback ) {
                        if ( isset( $callback['function'] ) ) {
                            $function = $callback['function'];
                            if ( is_array( $function ) && isset( $function[0] ) ) {
                                $class_name = is_object( $function[0] ) ? get_class( $function[0] ) : $function[0];
                                if ( strpos( $class_name, 'PFOB_' ) === 0 ) {
                                    $has_pfob_hooks = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            $status = $has_pfob_hooks ? '<span class="success">✓ Hooked</span>' : '<span class="warning">No PFOB hooks</span>';
            echo "<tr><td class='code'>$hook</td><td>$description</td><td>$status</td></tr>";
        }
        echo '</tbody></table>';

        ?>

        <div class="info-box" style="margin-top: 40px;">
            <strong>Next Steps:</strong><br>
            1. If classes are not loaded, check projectfob.php for proper require_once statements<br>
            2. If routes are not registered, check PFOB_Core::define_hooks() in includes/class-pfob-core.php<br>
            3. If rewrite rules are missing, visit Settings > Permalinks and click Save Changes<br>
            4. Check WordPress debug.log for any PHP errors or warnings<br>
            5. Make sure you're logged in as an admin or have an active subscription
        </div>

        <p style="text-align: center; margin-top: 40px; color: #666;">
            <small>ProjectFOB Diagnostic Tool | <?php echo date( 'Y-m-d H:i:s' ); ?></small>
        </p>
    </div>
</body>
</html>
