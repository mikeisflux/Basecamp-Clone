<?php
/**
 * Settings REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Settings_Endpoint extends PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get user settings
        register_rest_route( $this->namespace, '/settings', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_settings' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Update user settings
        register_rest_route( $this->namespace, '/settings', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'update_settings' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Send test digest
        register_rest_route( $this->namespace, '/settings/test-digest', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'send_test_digest' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Upload company logo
        register_rest_route( $this->namespace, '/settings/upload-logo', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'upload_logo' ),
            'permission_callback' => array( $this, 'check_subscriber_permission' ),
        ) );

        // Remove company logo
        register_rest_route( $this->namespace, '/settings/remove-logo', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'remove_logo' ),
            'permission_callback' => array( $this, 'check_subscriber_permission' ),
        ) );

        // Save brand colors
        register_rest_route( $this->namespace, '/settings/brand-colors', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'save_brand_colors' ),
            'permission_callback' => array( $this, 'check_subscriber_permission' ),
        ) );
    }

    /**
     * Check if user has permission.
     */
    public function check_permission( $request ) {
        return is_user_logged_in();
    }

    /**
     * Get user settings.
     */
    public function get_settings( $request ) {
        $user_id = get_current_user_id();

        $settings = array(
            'digest_frequency' => PFOB_Digest_Service::get_user_preference( $user_id ),
            'email_notifications' => get_user_meta( $user_id, 'pfob_email_notifications', true ) ?: 'all',
            'notification_mentions' => get_user_meta( $user_id, 'pfob_notify_mentions', true ) !== '0',
            'notification_assignments' => get_user_meta( $user_id, 'pfob_notify_assignments', true ) !== '0',
            'notification_comments' => get_user_meta( $user_id, 'pfob_notify_comments', true ) !== '0',
        );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $settings,
        ), 200 );
    }

    /**
     * Update user settings.
     */
    public function update_settings( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        // Update digest frequency
        if ( isset( $params['digest_frequency'] ) ) {
            PFOB_Digest_Service::set_user_preference( $user_id, $params['digest_frequency'] );
        }

        // Update email notification preference
        if ( isset( $params['email_notifications'] ) ) {
            $allowed = array( 'all', 'mentions', 'none' );
            if ( in_array( $params['email_notifications'], $allowed ) ) {
                update_user_meta( $user_id, 'pfob_email_notifications', $params['email_notifications'] );
            }
        }

        // Update specific notification types
        if ( isset( $params['notification_mentions'] ) ) {
            update_user_meta( $user_id, 'pfob_notify_mentions', $params['notification_mentions'] ? '1' : '0' );
        }

        if ( isset( $params['notification_assignments'] ) ) {
            update_user_meta( $user_id, 'pfob_notify_assignments', $params['notification_assignments'] ? '1' : '0' );
        }

        if ( isset( $params['notification_comments'] ) ) {
            update_user_meta( $user_id, 'pfob_notify_comments', $params['notification_comments'] ? '1' : '0' );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Settings updated successfully',
        ), 200 );
    }

    /**
     * Send test digest.
     */
    public function send_test_digest( $request ) {
        $user_id = get_current_user_id();

        $result = PFOB_Digest_Service::send_test_digest( $user_id );

        if ( $result ) {
            return new WP_REST_Response( array(
                'success' => true,
                'message' => 'Test digest sent to your email',
            ), 200 );
        } else {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'No activity to report, or email sending failed',
            ), 200 );
        }
    }

    /**
     * Check if user is a subscriber (account owner).
     */
    public function check_subscriber_permission( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user_id = get_current_user_id();
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );

        return $subscription && in_array( $subscription->status, array( 'active', 'trialing' ) );
    }

    /**
     * Upload company logo.
     */
    public function upload_logo( $request ) {
        $user_id = get_current_user_id();

        // Check if file was uploaded
        $files = $request->get_file_params();
        if ( empty( $files['logo'] ) ) {
            return new WP_Error( 'no_file', 'No file uploaded', array( 'status' => 400 ) );
        }

        $file = $files['logo'];

        // Validate file type
        $allowed_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp' );
        if ( ! in_array( $file['type'], $allowed_types ) ) {
            return new WP_Error( 'invalid_type', 'Invalid file type. Only images are allowed.', array( 'status' => 400 ) );
        }

        // Validate file size (max 2MB)
        if ( $file['size'] > 2 * 1024 * 1024 ) {
            return new WP_Error( 'file_too_large', 'File size must be less than 2MB', array( 'status' => 400 ) );
        }

        // Use WordPress file upload handling
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Upload file
        $upload = wp_handle_upload( $file, array( 'test_form' => false ) );

        if ( isset( $upload['error'] ) ) {
            return new WP_Error( 'upload_error', $upload['error'], array( 'status' => 500 ) );
        }

        // Store logo URL in user meta
        update_user_meta( $user_id, 'pfob_company_logo', $upload['url'] );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'url' => $upload['url'],
        ), 200 );
    }

    /**
     * Remove company logo.
     */
    public function remove_logo( $request ) {
        $user_id = get_current_user_id();

        // Get current logo URL
        $logo_url = get_user_meta( $user_id, 'pfob_company_logo', true );

        if ( $logo_url ) {
            // Delete the file if it's in the uploads directory
            $upload_dir = wp_upload_dir();
            if ( strpos( $logo_url, $upload_dir['baseurl'] ) === 0 ) {
                $file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $logo_url );
                if ( file_exists( $file_path ) ) {
                    @unlink( $file_path );
                }
            }
        }

        // Remove logo URL from user meta
        delete_user_meta( $user_id, 'pfob_company_logo' );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Logo removed successfully',
        ), 200 );
    }

    /**
     * Save brand colors.
     */
    public function save_brand_colors( $request ) {
        $user_id = get_current_user_id();

        // Check if user has Business or Enterprise tier (custom_branding feature)
        $subscription = PFOB_Subscription::get_active_subscription( $user_id );
        if ( ! $subscription ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'No active subscription found',
            ), 403 );
        }

        $plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

        if ( ! $plan || empty( $plan['features']['custom_branding'] ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Brand colors are only available for Business and Enterprise tiers',
            ), 403 );
        }

        $params = $request->get_json_params();

        if ( ! isset( $params['colors'] ) || ! is_array( $params['colors'] ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Invalid colors data',
            ), 400 );
        }

        $colors = $params['colors'];
        $allowed_keys = array( 'primary', 'secondary', 'background', 'tile', 'text', 'accent' );
        $validated_colors = array();

        // Validate and sanitize each color
        foreach ( $allowed_keys as $key ) {
            if ( isset( $colors[ $key ] ) ) {
                $color = sanitize_text_field( $colors[ $key ] );
                // Validate hex color format
                if ( preg_match( '/^#[0-9A-F]{6}$/i', $color ) ) {
                    $validated_colors[ $key ] = $color;
                } else {
                    return new WP_REST_Response( array(
                        'success' => false,
                        'message' => "Invalid color format for {$key}. Use hex format (e.g., #2d9061)",
                    ), 400 );
                }
            }
        }

        // Save colors to user meta
        update_user_meta( $user_id, 'pfob_brand_colors', $validated_colors );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Brand colors saved successfully',
            'data' => $validated_colors,
        ), 200 );
    }
}
