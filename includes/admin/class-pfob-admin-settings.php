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
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // AJAX handlers
        add_action( 'wp_ajax_pfob_test_paypal_connection', array( $this, 'test_paypal_connection' ) );
        add_action( 'wp_ajax_pfob_test_r2_connection', array( $this, 'test_r2_connection' ) );
        add_action( 'wp_ajax_pfob_sync_paypal_plans', array( $this, 'sync_paypal_plans' ) );
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            'ProjectFOB',
            'ProjectFOB',
            'manage_options',
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
            'manage_options',
            'projectfob',
            array( $this, 'dashboard_page' )
        );

        // Settings
        add_submenu_page(
            'projectfob',
            'Settings',
            'Settings',
            'manage_options',
            'projectfob-settings',
            array( $this, 'settings_page' )
        );

        // Users
        add_submenu_page(
            'projectfob',
            'Users & Subscriptions',
            'Users',
            'manage_options',
            'projectfob-users',
            array( $this, 'users_page' )
        );

        // Billing
        add_submenu_page(
            'projectfob',
            'Billing & Revenue',
            'Billing',
            'manage_options',
            'projectfob-billing',
            array( $this, 'billing_page' )
        );
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
        include PFOB_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    /**
     * Settings page
     */
    public function settings_page() {
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
        include PFOB_PLUGIN_DIR . 'templates/admin/users.php';
    }

    /**
     * Billing page
     */
    public function billing_page() {
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
}

// Initialize admin settings
if ( is_admin() ) {
    new PFOB_Admin_Settings();
}
