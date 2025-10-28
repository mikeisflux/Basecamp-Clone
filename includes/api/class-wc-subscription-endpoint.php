<?php
/**
 * Subscription Management REST API Endpoint
 *
 * @package    Warcampaign
 * @subpackage Warcampaign/includes/api
 */

class WC_Subscription_Endpoint {

    protected $namespace = 'warcampaign/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Get available subscription plans
        register_rest_route( $this->namespace, '/subscription/plans', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_plans' ),
            'permission_callback' => '__return_true', // Public endpoint
        ) );

        // Create new user account and subscription
        register_rest_route( $this->namespace, '/subscription/register', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'register_user' ),
            'permission_callback' => '__return_true', // Public endpoint
            'args'                => array(
                'email' => array(
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                    'sanitize_callback' => 'sanitize_email',
                ),
                'password' => array(
                    'required' => true,
                    'type' => 'string',
                    'minLength' => 8,
                ),
                'first_name' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'last_name' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'plan_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array( 'starter', 'professional', 'business', 'enterprise' ),
                ),
            ),
        ) );

        // Complete subscription after PayPal approval
        register_rest_route( $this->namespace, '/subscription/complete', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'complete_subscription' ),
            'permission_callback' => 'is_user_logged_in',
            'args'                => array(
                'subscription_id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ) );

        // Get current user's subscription
        register_rest_route( $this->namespace, '/subscription/current', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_current_subscription' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        // Cancel subscription
        register_rest_route( $this->namespace, '/subscription/cancel', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'cancel_subscription' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        // Get billing history
        register_rest_route( $this->namespace, '/subscription/billing-history', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_billing_history' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        // Get usage statistics
        register_rest_route( $this->namespace, '/subscription/usage', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_usage' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        // Upgrade/downgrade subscription
        register_rest_route( $this->namespace, '/subscription/change-plan', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'change_plan' ),
            'permission_callback' => 'is_user_logged_in',
            'args'                => array(
                'new_plan_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array( 'starter', 'professional', 'business', 'enterprise' ),
                ),
            ),
        ) );
    }

    /**
     * Get available subscription plans.
     */
    public function get_plans( $request ) {
        $plans = include WC_PLUGIN_DIR . 'includes/config/subscription-plans.php';

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $plans,
        ), 200 );
    }

    /**
     * Register new user and create subscription.
     */
    public function register_user( $request ) {
        $params = $request->get_params();

        // Check if email already exists
        if ( email_exists( $params['email'] ) ) {
            return new WP_Error( 'email_exists', 'An account with this email already exists.', array( 'status' => 400 ) );
        }

        // Validate plan
        $plans = include WC_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        if ( ! isset( $plans[ $params['plan_id'] ] ) ) {
            return new WP_Error( 'invalid_plan', 'Invalid subscription plan.', array( 'status' => 400 ) );
        }

        // Create WordPress user
        $user_id = wp_create_user(
            $params['email'],
            $params['password'],
            $params['email']
        );

        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }

        // Update user meta
        wp_update_user( array(
            'ID' => $user_id,
            'first_name' => $params['first_name'],
            'last_name' => $params['last_name'],
            'display_name' => $params['first_name'] . ' ' . $params['last_name'],
        ) );

        // Create subscription record
        $subscription_id = WC_Subscription::create( array(
            'user_id' => $user_id,
            'plan_id' => $params['plan_id'],
            'status' => 'pending',
        ) );

        if ( ! $subscription_id ) {
            // Rollback user creation
            wp_delete_user( $user_id );
            return new WP_Error( 'subscription_error', 'Failed to create subscription.', array( 'status' => 500 ) );
        }

        // Create PayPal subscription
        $return_url = site_url( '/warcampaign/subscription/success' );
        $cancel_url = site_url( '/warcampaign/subscription/cancel' );

        $paypal_result = WC_PayPal_Service::create_subscription(
            $user_id,
            $params['plan_id'],
            $return_url,
            $cancel_url
        );

        if ( is_wp_error( $paypal_result ) ) {
            // Rollback
            WC_Subscription::update( $subscription_id, array( 'status' => 'failed' ) );
            return $paypal_result;
        }

        // Update subscription with PayPal ID
        WC_Subscription::update( $subscription_id, array(
            'paypal_subscription_id' => $paypal_result['subscription_id'],
        ) );

        // Auto-login user
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => array(
                'user_id' => $user_id,
                'subscription_id' => $subscription_id,
                'approval_url' => $paypal_result['approval_url'],
            ),
        ), 201 );
    }

    /**
     * Complete subscription after PayPal approval.
     */
    public function complete_subscription( $request ) {
        $user_id = get_current_user_id();
        $paypal_subscription_id = $request->get_param( 'subscription_id' );

        // Get subscription
        $subscription = WC_Subscription::get_by_paypal_id( $paypal_subscription_id );

        if ( ! $subscription || $subscription->user_id != $user_id ) {
            return new WP_Error( 'invalid_subscription', 'Invalid subscription.', array( 'status' => 404 ) );
        }

        // Get details from PayPal
        $paypal_details = WC_PayPal_Service::get_subscription( $paypal_subscription_id );

        if ( is_wp_error( $paypal_details ) ) {
            return $paypal_details;
        }

        // Update subscription status
        WC_Subscription::update( $subscription->id, array(
            'status' => strtolower( $paypal_details['status'] ),
        ) );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => array(
                'status' => $paypal_details['status'],
                'redirect' => site_url( '/warcampaign/' ),
            ),
        ), 200 );
    }

    /**
     * Get current user's subscription.
     */
    public function get_current_subscription( $request ) {
        $user_id = get_current_user_id();
        $subscription = WC_Subscription::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription found.', array( 'status' => 404 ) );
        }

        $plan = WC_Subscription::get_user_plan( $user_id );
        $usage = WC_Usage::get_all_current( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => array(
                'subscription' => $subscription,
                'plan' => $plan,
                'usage' => $usage,
            ),
        ), 200 );
    }

    /**
     * Cancel subscription.
     */
    public function cancel_subscription( $request ) {
        $user_id = get_current_user_id();
        $subscription = WC_Subscription::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription found.', array( 'status' => 404 ) );
        }

        // Cancel in PayPal
        if ( $subscription->paypal_subscription_id ) {
            $result = WC_PayPal_Service::cancel_subscription( $subscription->paypal_subscription_id );

            if ( is_wp_error( $result ) ) {
                return $result;
            }
        }

        // Update local subscription
        WC_Subscription::cancel( $subscription->id );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Subscription canceled successfully.',
        ), 200 );
    }

    /**
     * Get billing history.
     */
    public function get_billing_history( $request ) {
        $user_id = get_current_user_id();
        $history = WC_Billing_History::get_by_user_id( $user_id, 50, 0 );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $history,
        ), 200 );
    }

    /**
     * Get usage statistics.
     */
    public function get_usage( $request ) {
        $user_id = get_current_user_id();

        // Update all metrics
        WC_Usage::update_all_metrics( $user_id );

        $usage = WC_Usage::get_all_current( $user_id );
        $plan = WC_Subscription::get_user_plan( $user_id );

        $limits = array();
        if ( $plan ) {
            foreach ( $plan['features'] as $feature => $limit ) {
                if ( is_numeric( $limit ) ) {
                    $limits[ $feature ] = array(
                        'limit' => $limit === 999999 ? null : $limit,
                        'current' => $usage[ $feature ] ?? 0,
                        'remaining' => WC_Usage::get_remaining( $user_id, $feature ),
                        'percentage' => WC_Usage::get_usage_percentage( $user_id, $feature ),
                    );
                }
            }
        }

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $limits,
        ), 200 );
    }

    /**
     * Change subscription plan.
     */
    public function change_plan( $request ) {
        $user_id = get_current_user_id();
        $new_plan_id = $request->get_param( 'new_plan_id' );

        $subscription = WC_Subscription::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription found.', array( 'status' => 404 ) );
        }

        // Validate new plan
        $plans = include WC_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        if ( ! isset( $plans[ $new_plan_id ] ) ) {
            return new WP_Error( 'invalid_plan', 'Invalid subscription plan.', array( 'status' => 400 ) );
        }

        // For now, require canceling current subscription and creating a new one
        // In production, this should use PayPal's subscription revision API
        return new WP_Error( 'not_implemented', 'Plan changes coming soon. Please cancel your current subscription and sign up for a new plan.', array( 'status' => 501 ) );
    }
}
