<?php
/**
 * API Keys Management REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_API_Keys_Endpoint extends PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // List API keys
        register_rest_route( $this->namespace, '/api-keys', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'list_keys' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Create API key
        register_rest_route( $this->namespace, '/api-keys', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_key' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
            'args'                => array(
                'name' => array(
                    'required' => true,
                    'type'     => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'description' => array(
                    'required' => false,
                    'type'     => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
            ),
        ) );

        // Revoke API key
        register_rest_route( $this->namespace, '/api-keys/(?P<key_id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( $this, 'revoke_key' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Get API key stats
        register_rest_route( $this->namespace, '/api-keys/stats', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_stats' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );
    }

    /**
     * List API keys.
     */
    public function list_keys( $request ) {
        $user_id = get_current_user_id();

        // Check if user has API access feature
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );
        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription', array( 'status' => 403 ) );
        }

        $plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

        if ( ! $plan || empty( $plan['features']['api_access'] ) ) {
            return new WP_Error( 'feature_unavailable', 'API Access is only available on Enterprise plan', array( 'status' => 403 ) );
        }

        $keys = PFOB_API_Key::get_by_user( $user_id );

        // Mask the keys for security
        foreach ( $keys as &$key ) {
            $key->key_masked = substr( $key->api_key, 0, 12 ) . '...' . substr( $key->api_key, -4 );
            unset( $key->api_key ); // Don't send full key
        }

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => $keys,
        ), 200 );
    }

    /**
     * Create API key.
     */
    public function create_key( $request ) {
        $user_id = get_current_user_id();

        // Check if user has API access feature
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );
        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription', array( 'status' => 403 ) );
        }

        $plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

        if ( ! $plan || empty( $plan['features']['api_access'] ) ) {
            return new WP_Error( 'feature_unavailable', 'API Access is only available on Enterprise plan', array( 'status' => 403 ) );
        }

        $params = $request->get_params();

        // Generate secure API key
        $api_key = 'pfob_' . bin2hex( random_bytes( 32 ) );

        $key_id = PFOB_API_Key::create( array(
            'user_id'     => $user_id,
            'name'        => $params['name'],
            'description' => $params['description'] ?? '',
            'api_key'     => hash( 'sha256', $api_key ), // Store hash
            'status'      => 'active',
        ) );

        if ( ! $key_id ) {
            return new WP_Error( 'creation_failed', 'Failed to create API key', array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => array(
                'id'  => $key_id,
                'key' => $api_key, // Return plain key ONLY on creation
            ),
        ), 201 );
    }

    /**
     * Revoke API key.
     */
    public function revoke_key( $request ) {
        $user_id = get_current_user_id();
        $key_id = $request->get_param( 'key_id' );

        // Check if user has API access feature
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );
        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription', array( 'status' => 403 ) );
        }

        $plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

        if ( ! $plan || empty( $plan['features']['api_access'] ) ) {
            return new WP_Error( 'feature_unavailable', 'API Access is only available on Enterprise plan', array( 'status' => 403 ) );
        }

        // Verify ownership
        $key = PFOB_API_Key::get( $key_id );
        if ( ! $key || $key->user_id != $user_id ) {
            return new WP_Error( 'not_found', 'API key not found', array( 'status' => 404 ) );
        }

        // Update status to revoked
        PFOB_API_Key::update( $key_id, array(
            'status' => 'revoked',
        ) );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'API key revoked successfully',
        ), 200 );
    }

    /**
     * Get API key usage stats.
     */
    public function get_stats( $request ) {
        $user_id = get_current_user_id();

        // Check if user has API access feature
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );
        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription', array( 'status' => 403 ) );
        }

        $plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

        if ( ! $plan || empty( $plan['features']['api_access'] ) ) {
            return new WP_Error( 'feature_unavailable', 'API Access is only available on Enterprise plan', array( 'status' => 403 ) );
        }

        // Get stats from database
        $stats = PFOB_API_Key::get_usage_stats( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => $stats,
        ), 200 );
    }
}
