<?php
/**
 * White Label Settings REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_White_Label_Endpoint extends PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get white label settings
        register_rest_route( $this->namespace, '/settings/white-label', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_settings' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Update white label settings
        register_rest_route( $this->namespace, '/settings/white-label', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'update_settings' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
            'args'                => array(
                'app_name' => array(
                    'required' => false,
                    'type'     => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'primary_color' => array(
                    'required' => false,
                    'type'     => 'string',
                    'sanitize_callback' => 'sanitize_hex_color',
                ),
                'secondary_color' => array(
                    'required' => false,
                    'type'     => 'string',
                    'sanitize_callback' => 'sanitize_hex_color',
                ),
                'hide_branding' => array(
                    'required' => false,
                    'type'     => 'boolean',
                ),
                'custom_footer' => array(
                    'required' => false,
                    'type'     => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ) );
    }

    /**
     * Get white label settings.
     */
    public function get_settings( $request ) {
        $user_id = get_current_user_id();

        // Check if user has white label feature
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );
        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription', array( 'status' => 403 ) );
        }

        $plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

        if ( ! $plan || empty( $plan['features']['white_label'] ) ) {
            return new WP_Error( 'feature_unavailable', 'White Label is only available on Enterprise plan', array( 'status' => 403 ) );
        }

        // Get settings from user meta
        $settings = array(
            'app_name'        => get_user_meta( $user_id, 'pfob_white_label_app_name', true ) ?: 'ProjectFOB',
            'primary_color'   => get_user_meta( $user_id, 'pfob_white_label_primary_color', true ) ?: '#2d9061',
            'secondary_color' => get_user_meta( $user_id, 'pfob_white_label_secondary_color', true ) ?: '#1f6b48',
            'hide_branding'   => get_user_meta( $user_id, 'pfob_white_label_hide_branding', true ) === '1',
            'custom_footer'   => get_user_meta( $user_id, 'pfob_white_label_custom_footer', true ) ?: '',
        );

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => $settings,
        ), 200 );
    }

    /**
     * Update white label settings.
     */
    public function update_settings( $request ) {
        $user_id = get_current_user_id();

        // Check if user has white label feature
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );
        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription', array( 'status' => 403 ) );
        }

        $plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

        if ( ! $plan || empty( $plan['features']['white_label'] ) ) {
            return new WP_Error( 'feature_unavailable', 'White Label is only available on Enterprise plan', array( 'status' => 403 ) );
        }

        // Update settings
        $params = $request->get_params();

        if ( isset( $params['app_name'] ) ) {
            update_user_meta( $user_id, 'pfob_white_label_app_name', $params['app_name'] );
        }

        if ( isset( $params['primary_color'] ) ) {
            update_user_meta( $user_id, 'pfob_white_label_primary_color', $params['primary_color'] );
        }

        if ( isset( $params['secondary_color'] ) ) {
            update_user_meta( $user_id, 'pfob_white_label_secondary_color', $params['secondary_color'] );
        }

        if ( isset( $params['hide_branding'] ) ) {
            update_user_meta( $user_id, 'pfob_white_label_hide_branding', $params['hide_branding'] ? '1' : '0' );
        }

        if ( isset( $params['custom_footer'] ) ) {
            update_user_meta( $user_id, 'pfob_white_label_custom_footer', $params['custom_footer'] );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'White label settings updated successfully',
        ), 200 );
    }
}
