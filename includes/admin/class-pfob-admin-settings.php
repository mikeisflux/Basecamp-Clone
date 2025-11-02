<?php
/**
 * Admin Settings Page
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/admin
 */

class PFOB_Admin_Settings {

    /**
     * Initialize the admin settings
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_menu', array( $this, 'hide_admin_menu_for_non_subscribers' ), 999 );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'admin_notices', array( $this, 'show_activation_notice' ) );

        // AJAX handlers
        add_action( 'wp_ajax_pfob_test_paypal_connection', array( $this, 'test_paypal_connection' ) );
        add_action( 'wp_ajax_pfob_test_r2_connection', array( $this, 'test_r2_connection' ) );
        add_action( 'wp_ajax_pfob_sync_paypal_plans', array( $this, 'sync_paypal_plans' ) );
        add_action( 'wp_ajax_pfob_sync_users', array( $this, 'ajax_sync_users' ) );
        add_action( 'wp_ajax_pfob_flush_rewrite_rules', array( $this, 'ajax_flush_rewrite_rules' ) );
        add_action( 'wp_ajax_pfob_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            'ProjectFOB',
            'ProjectFOB',
            'read',
            'projectfob',
            array( $this, 'dashboard_page' ),
            'dashicons-groups',
            30
        );

        // Dashboard
        add_submenu_page(
            'projectfob',
            'Dashboard',
            'Dashboard',
            'read',
            'projectfob',
            array( $this, 'dashboard_page' )
        );

        // Settings
        add_submenu_page(
            'projectfob',
            'Settings',
            'Settings',
            'read',
            'projectfob-settings',
            array( $this, 'settings_page' )
        );

        // Users
        add_submenu_page(
            'projectfob',
            'Users & Subscriptions',
            'Users',
            'read',
            'projectfob-users',
            array( $this, 'users_page' )
        );

        // Billing
        add_submenu_page(
            'projectfob',
            'Billing & Revenue',
            'Billing',
            'read',
            'projectfob-billing',
            array( $this, 'billing_page' )
        );
    }

    /**
     * Hide admin menu for users without active subscriptions
     */
    public function hide_admin_menu_for_non_subscribers() {
        // Don't hide for WordPress admins
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        // Hide menu if user doesn't have active subscription
        if ( ! PFOB_Subscription::is_active( get_current_user_id() ) ) {
            remove_menu_page( 'projectfob' );
        }
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // PayPal Settings
        register_setting( 'pfob_paypal_settings', 'pfob_paypal_client_id' );
        register_setting( 'pfob_paypal_settings', 'pfob_paypal_client_secret' );
        register_setting( 'pfob_paypal_settings', 'pfob_paypal_sandbox_mode' );
        register_setting( 'pfob_paypal_settings', 'pfob_paypal_webhook_id' );

        // R2 Settings
        register_setting( 'pfob_r2_settings', 'pfob_r2_access_key_id' );
        register_setting( 'pfob_r2_settings', 'pfob_r2_secret_access_key' );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts( $hook ) {
        // Only load on our admin pages
        if ( strpos( $hook, 'projectfob' ) === false ) {
            return;
        }

        wp_enqueue_style( 'pfob-admin-styles', PFOB_PLUGIN_URL . 'assets/css/admin.css', array(), PFOB_VERSION );
        wp_enqueue_script( 'pfob-admin-scripts', PFOB_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), PFOB_VERSION, true );

        wp_localize_script( 'pfob-admin-scripts', 'pfobAdmin', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'pfob_admin_nonce' ),
        ) );
    }

    /**
     * Dashboard page
     */
    public function dashboard_page() {
        // Check if user has active subscription or is WordPress admin
        if ( ! PFOB_Subscription::is_active( get_current_user_id() ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to access this page. An active subscription is required.', 'projectfob' ) );
        }

        include PFOB_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    /**
     * Settings page
     */
    public function settings_page() {
        // Check if user has active subscription or is WordPress admin
        if ( ! PFOB_Subscription::is_active( get_current_user_id() ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to access this page. An active subscription is required.', 'projectfob' ) );
        }

        // Save settings if submitted
        if ( isset( $_POST['pfob_save_settings'] ) && check_admin_referer( 'pfob_settings_save' ) ) {
            $this->save_settings();
        }

        include PFOB_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Users page
     */
    public function users_page() {
        // Check if user has active subscription or is WordPress admin
        if ( ! PFOB_Subscription::is_active( get_current_user_id() ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to access this page. An active subscription is required.', 'projectfob' ) );
        }

        include PFOB_PLUGIN_DIR . 'templates/admin/users.php';
    }

    /**
     * Billing page
     */
    public function billing_page() {
        // Check if user has active subscription or is WordPress admin
        if ( ! PFOB_Subscription::is_active( get_current_user_id() ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to access this page. An active subscription is required.', 'projectfob' ) );
        }

        include PFOB_PLUGIN_DIR . 'templates/admin/billing.php';
    }

    /**
     * Save settings
     */
    private function save_settings() {
        // PayPal settings
        if ( isset( $_POST['pfob_paypal_client_id'] ) ) {
            update_option( 'pfob_paypal_client_id', sanitize_text_field( $_POST['pfob_paypal_client_id'] ) );
        }
        if ( isset( $_POST['pfob_paypal_client_secret'] ) ) {
            update_option( 'pfob_paypal_client_secret', sanitize_text_field( $_POST['pfob_paypal_client_secret'] ) );
        }
        if ( isset( $_POST['pfob_paypal_sandbox_mode'] ) ) {
            update_option( 'pfob_paypal_sandbox_mode', '1' );
        } else {
            update_option( 'pfob_paypal_sandbox_mode', '0' );
        }
        if ( isset( $_POST['pfob_paypal_webhook_id'] ) ) {
            update_option( 'pfob_paypal_webhook_id', sanitize_text_field( $_POST['pfob_paypal_webhook_id'] ) );
        }

        // R2 settings
        if ( isset( $_POST['pfob_r2_access_key_id'] ) ) {
            update_option( 'pfob_r2_access_key_id', sanitize_text_field( $_POST['pfob_r2_access_key_id'] ) );
        }
        if ( isset( $_POST['pfob_r2_secret_access_key'] ) ) {
            update_option( 'pfob_r2_secret_access_key', sanitize_text_field( $_POST['pfob_r2_secret_access_key'] ) );
        }

        add_settings_error( 'pfob_settings', 'settings_saved', 'Settings saved successfully!', 'success' );
    }

    /**
     * Test PayPal connection (AJAX)
     */
    public function test_paypal_connection() {
        check_ajax_referer( 'pfob_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        // Try to get access token
        $result = PFOB_PayPal_Service::get_access_token();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => 'Connection failed: ' . $result->get_error_message(),
            ) );
        } else {
            wp_send_json_success( array(
                'message' => 'PayPal connection successful!',
            ) );
        }
    }

    /**
     * Test R2 connection (AJAX)
     */
    public function test_r2_connection() {
        check_ajax_referer( 'pfob_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        // Try to list objects
        $result = PFOB_R2_Storage_Service::list_objects( '', 1 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => 'Connection failed: ' . $result->get_error_message(),
            ) );
        } else {
            wp_send_json_success( array(
                'message' => 'R2 connection successful! Bucket accessible.',
            ) );
        }
    }

    /**
     * Sync PayPal plans (AJAX)
     */
    public function sync_paypal_plans() {
        check_ajax_referer( 'pfob_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $result = PFOB_PayPal_Service::sync_subscription_plans();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => 'Sync failed: ' . $result->get_error_message(),
            ) );
        } else {
            wp_send_json_success( array(
                'message' => 'Successfully synced ' . count( $result ) . ' subscription plans to PayPal!',
                'plans' => $result,
            ) );
        }
    }

    /**
     * Sync WordPress users with subscription system (AJAX)
     */
    public function ajax_sync_users() {
        check_ajax_referer( 'pfob_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'pfob_subscriptions';

        // Get all WordPress users
        $users = get_users( array(
            'fields' => array( 'ID', 'user_email' ),
        ) );

        $synced_count = 0;

        foreach ( $users as $user ) {
            // Check if user already has a subscription record
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE user_id = %d",
                    $user->ID
                )
            );

            // Skip if subscription already exists
            if ( $existing ) {
                continue;
            }

            // Determine subscription status and plan based on user role
            $user_obj = get_userdata( $user->ID );

            if ( in_array( 'administrator', $user_obj->roles ) ) {
                // WordPress admins get active enterprise subscription
                $status = 'active';
                $plan_id = 'enterprise';
            } else {
                // Regular users get active professional subscription
                $status = 'active';
                $plan_id = 'professional';
            }

            // Create subscription record
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user->ID,
                    'plan_id' => $plan_id,
                    'status' => $status,
                    'current_period_start' => current_time( 'mysql' ),
                    'current_period_end' => date( 'Y-m-d H:i:s', strtotime( '+1 month' ) ),
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' ),
                    'metadata' => wp_json_encode( array(
                        'synced_manually' => true,
                        'original_user_role' => implode( ',', $user_obj->roles ),
                    ) ),
                )
            );

            $synced_count++;
        }

        if ( $synced_count > 0 ) {
            wp_send_json_success( array(
                'message' => "Successfully synced {$synced_count} user(s) with the subscription system!",
                'count' => $synced_count,
            ) );
        } else {
            wp_send_json_success( array(
                'message' => 'All users are already synced with the subscription system.',
                'count' => 0,
            ) );
        }
    }

    /**
     * Show activation notice with flush rewrite rules button
     */
    public function show_activation_notice() {
        // Check if notice has been dismissed
        if ( get_option( 'pfob_activation_notice_dismissed' ) ) {
            return;
        }

        // Check if plugin was just activated or rewrite rules need flushing
        if ( ! get_option( 'pfob_show_activation_notice' ) && ! get_option( 'pfob_flush_rewrite_rules' ) ) {
            return;
        }

        ?>
        <div class="notice notice-info is-dismissible pfob-activation-notice" id="pfob-activation-notice">
            <p>
                <strong>ℹ️ ProjectFOB:</strong>
                If your links (like <code>/projectfob/</code>, <code>/projectfob/pricing/</code>, or project pages) don't work,
                <a href="#" id="pfob-flush-rewrite-rules-btn" class="button button-primary" style="margin: 0 5px;">Click here to flush rewrite rules</a>
                or go to <strong>Settings → Permalinks</strong> and click "Save Changes".
            </p>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Flush rewrite rules button
            $('#pfob-flush-rewrite-rules-btn').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var originalText = $btn.text();

                $btn.prop('disabled', true).text('Flushing...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pfob_flush_rewrite_rules',
                        nonce: '<?php echo wp_create_nonce( 'pfob_flush_rewrite_rules' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.text('✓ Done!').removeClass('button-primary').addClass('button-secondary');
                            $('#pfob-activation-notice p').html(
                                '<strong>✅ Success!</strong> Rewrite rules have been flushed. Your links should work now. ' +
                                '<a href="#" class="pfob-dismiss-notice">Dismiss this notice</a>'
                            );

                            // Auto-dismiss after 5 seconds
                            setTimeout(function() {
                                $('#pfob-activation-notice').fadeOut(function() {
                                    $(this).remove();
                                });
                            }, 5000);
                        } else {
                            alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
                            $btn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        alert('AJAX request failed. Please try going to Settings → Permalinks manually.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Dismiss notice button
            $(document).on('click', '.pfob-dismiss-notice', function(e) {
                e.preventDefault();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pfob_dismiss_notice',
                        nonce: '<?php echo wp_create_nonce( 'pfob_dismiss_notice' ); ?>'
                    },
                    success: function() {
                        $('#pfob-activation-notice').fadeOut(function() {
                            $(this).remove();
                        });
                    }
                });
            });

            // Handle WordPress native dismiss button
            $('#pfob-activation-notice').on('click', '.notice-dismiss', function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pfob_dismiss_notice',
                        nonce: '<?php echo wp_create_nonce( 'pfob_dismiss_notice' ); ?>'
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX handler to flush rewrite rules
     */
    public function ajax_flush_rewrite_rules() {
        check_ajax_referer( 'pfob_flush_rewrite_rules', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }

        // Register routes
        require_once PFOB_PLUGIN_DIR . 'includes/frontend/class-pfob-router.php';
        PFOB_Router::register_rewrite_rules();

        // Flush
        flush_rewrite_rules();

        // Clear the flags
        delete_option( 'pfob_flush_rewrite_rules' );
        delete_option( 'pfob_show_activation_notice' );

        wp_send_json_success( array(
            'message' => 'Rewrite rules flushed successfully!'
        ) );
    }

    /**
     * AJAX handler to dismiss notice
     */
    public function ajax_dismiss_notice() {
        check_ajax_referer( 'pfob_dismiss_notice', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }

        update_option( 'pfob_activation_notice_dismissed', '1' );
        delete_option( 'pfob_show_activation_notice' );

        wp_send_json_success();
    }
}
