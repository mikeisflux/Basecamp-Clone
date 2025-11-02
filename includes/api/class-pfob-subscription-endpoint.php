<?php
/**
 * Subscription Management REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Subscription_Endpoint extends PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

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

        // Add add-on to subscription
        register_rest_route( $this->namespace, '/subscription/add-addon', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'add_addon' ),
            'permission_callback' => 'is_user_logged_in',
            'args'                => array(
                'addon' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array( 'timesheet', 'admin-pro' ),
                ),
            ),
        ) );

        // Remove add-on from subscription
        register_rest_route( $this->namespace, '/subscription/remove-addon', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'remove_addon' ),
            'permission_callback' => 'is_user_logged_in',
            'args'                => array(
                'addon' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array( 'timesheet', 'admin-pro' ),
                ),
            ),
        ) );
    }

    /**
     * Get available subscription plans.
     */
    public function get_plans( $request ) {
        $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';

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
        $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
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
        $subscription_id = PFOB_Subscription::create( array(
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
        $return_url = site_url( '/projectfob/subscription/success' );
        $cancel_url = site_url( '/projectfob/subscription/cancel' );

        $paypal_result = PFOB_PayPal_Service::create_subscription(
            $user_id,
            $params['plan_id'],
            $return_url,
            $cancel_url
        );

        if ( is_wp_error( $paypal_result ) ) {
            // Rollback
            PFOB_Subscription::update( $subscription_id, array( 'status' => 'failed' ) );
            return $paypal_result;
        }

        // Update subscription with PayPal ID
        PFOB_Subscription::update( $subscription_id, array(
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
        $subscription = PFOB_Subscription::get_by_paypal_id( $paypal_subscription_id );

        if ( ! $subscription || $subscription->user_id != $user_id ) {
            return new WP_Error( 'invalid_subscription', 'Invalid subscription.', array( 'status' => 404 ) );
        }

        // Get details from PayPal
        $paypal_details = PFOB_PayPal_Service::get_subscription( $paypal_subscription_id );

        if ( is_wp_error( $paypal_details ) ) {
            return $paypal_details;
        }

        // Update subscription status
        PFOB_Subscription::update( $subscription->id, array(
            'status' => strtolower( $paypal_details['status'] ),
        ) );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => array(
                'status' => $paypal_details['status'],
                'redirect' => site_url( '/projectfob/' ),
            ),
        ), 200 );
    }

    /**
     * Get current user's subscription.
     */
    public function get_current_subscription( $request ) {
        $user_id = get_current_user_id();
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription found.', array( 'status' => 404 ) );
        }

        $plan = PFOB_Subscription::get_user_plan( $user_id );
        $usage = PFOB_Usage::get_all_current( $user_id );

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
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription found.', array( 'status' => 404 ) );
        }

        // Cancel in PayPal
        if ( $subscription->paypal_subscription_id ) {
            $result = PFOB_PayPal_Service::cancel_subscription( $subscription->paypal_subscription_id );

            if ( is_wp_error( $result ) ) {
                return $result;
            }
        }

        // Update local subscription
        PFOB_Subscription::cancel( $subscription->id );

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
        $history = PFOB_Billing_History::get_by_user_id( $user_id, 50, 0 );

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
        PFOB_Usage::update_all_metrics( $user_id );

        $usage = PFOB_Usage::get_all_current( $user_id );
        $plan = PFOB_Subscription::get_user_plan( $user_id );

        $limits = array();
        if ( $plan ) {
            foreach ( $plan['features'] as $feature => $limit ) {
                if ( is_numeric( $limit ) ) {
                    $limits[ $feature ] = array(
                        'limit' => $limit === 999999 ? null : $limit,
                        'current' => $usage[ $feature ] ?? 0,
                        'remaining' => PFOB_Usage::get_remaining( $user_id, $feature ),
                        'percentage' => PFOB_Usage::get_usage_percentage( $user_id, $feature ),
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

        $subscription = PFOB_Subscription::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription found.', array( 'status' => 404 ) );
        }

        // Validate new plan
        $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        if ( ! isset( $plans[ $new_plan_id ] ) ) {
            return new WP_Error( 'invalid_plan', 'Invalid subscription plan.', array( 'status' => 400 ) );
        }

        $new_plan = $plans[ $new_plan_id ];
        $old_plan_id = $subscription->plan_id;

        // Update subscription plan immediately
        $result = PFOB_Subscription::update( $subscription->id, array(
            'plan_id' => $new_plan_id,
            'updated_at' => current_time( 'mysql' )
        ) );

        if ( ! $result ) {
            return new WP_Error( 'update_failed', 'Failed to update subscription plan.', array( 'status' => 500 ) );
        }

        // Log activity
        PFOB_Database::insert( 'pfob_activities', array(
            'user_id' => $user_id,
            'action_type' => 'subscription.plan_changed',
            'subject_type' => 'subscription',
            'subject_id' => $subscription->id,
            'description' => "Changed plan from {$old_plan_id} to {$new_plan_id}",
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' )
        ) );

        // Note: PayPal subscription updates would need to be handled separately
        // For now, plan changes are manual and admin can coordinate with PayPal

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Subscription plan updated successfully!',
            'data' => array(
                'old_plan' => $old_plan_id,
                'new_plan' => $new_plan_id,
                'plan_name' => $new_plan['name'],
                'price' => $new_plan['price'],
            ),
        ), 200 );
    }

    /**
     * Add add-on to subscription.
     */
    public function add_addon( $request ) {
        $user_id = get_current_user_id();
        $addon = $request->get_param( 'addon' );

        $subscription = PFOB_Subscription::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription found.', array( 'status' => 404 ) );
        }

        // Get current metadata
        $metadata = $subscription->metadata ? json_decode( $subscription->metadata, true ) : array();

        // Add-on mapping
        $addon_map = array(
            'timesheet' => 'addon_timesheet',
            'admin-pro' => 'addon_admin_pro',
        );

        $addon_names = array(
            'timesheet' => 'Timesheet',
            'admin-pro' => 'Admin Pro Pack',
        );

        if ( ! isset( $addon_map[ $addon ] ) ) {
            return new WP_Error( 'invalid_addon', 'Invalid add-on.', array( 'status' => 400 ) );
        }

        $addon_key = $addon_map[ $addon ];

        // Check if already added
        if ( isset( $metadata[ $addon_key ] ) && $metadata[ $addon_key ] ) {
            return new WP_Error( 'addon_exists', 'This add-on is already active.', array( 'status' => 400 ) );
        }

        // Add the add-on
        $metadata[ $addon_key ] = true;
        $metadata[ $addon_key . '_added_at' ] = current_time( 'mysql' );

        // Update subscription
        $result = PFOB_Subscription::update( $subscription->id, array(
            'metadata' => wp_json_encode( $metadata ),
            'updated_at' => current_time( 'mysql' ),
        ) );

        if ( ! $result ) {
            return new WP_Error( 'update_failed', 'Failed to add add-on.', array( 'status' => 500 ) );
        }

        // Log activity
        PFOB_Database::insert( 'pfob_activities', array(
            'user_id' => $user_id,
            'action_type' => 'subscription.addon_added',
            'subject_type' => 'subscription',
            'subject_id' => $subscription->id,
            'description' => "Added {$addon_names[$addon]} add-on (+$50/month)",
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' ),
        ) );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => "{$addon_names[$addon]} has been added to your subscription! Your account has been instantly updated.",
        ), 200 );
    }

    /**
     * Remove add-on from subscription.
     */
    public function remove_addon( $request ) {
        $user_id = get_current_user_id();
        $addon = $request->get_param( 'addon' );

        $subscription = PFOB_Subscription::get_by_user_id( $user_id );

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', 'No active subscription found.', array( 'status' => 404 ) );
        }

        // Get current metadata
        $metadata = $subscription->metadata ? json_decode( $subscription->metadata, true ) : array();

        // Add-on mapping
        $addon_map = array(
            'timesheet' => 'addon_timesheet',
            'admin-pro' => 'addon_admin_pro',
        );

        $addon_names = array(
            'timesheet' => 'Timesheet',
            'admin-pro' => 'Admin Pro Pack',
        );

        if ( ! isset( $addon_map[ $addon ] ) ) {
            return new WP_Error( 'invalid_addon', 'Invalid add-on.', array( 'status' => 400 ) );
        }

        $addon_key = $addon_map[ $addon ];

        // Check if addon is active
        if ( ! isset( $metadata[ $addon_key ] ) || ! $metadata[ $addon_key ] ) {
            return new WP_Error( 'addon_not_active', 'This add-on is not active.', array( 'status' => 400 ) );
        }

        // Remove the add-on
        $metadata[ $addon_key ] = false;
        $metadata[ $addon_key . '_removed_at' ] = current_time( 'mysql' );

        // Update subscription
        $result = PFOB_Subscription::update( $subscription->id, array(
            'metadata' => wp_json_encode( $metadata ),
            'updated_at' => current_time( 'mysql' ),
        ) );

        if ( ! $result ) {
            return new WP_Error( 'update_failed', 'Failed to remove add-on.', array( 'status' => 500 ) );
        }

        // Log activity
        PFOB_Database::insert( 'pfob_activities', array(
            'user_id' => $user_id,
            'action_type' => 'subscription.addon_removed',
            'subject_type' => 'subscription',
            'subject_id' => $subscription->id,
            'description' => "Removed {$addon_names[$addon]} add-on (-$50/month)",
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' ),
        ) );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => "{$addon_names[$addon]} has been removed from your subscription.",
        ), 200 );
    }
}
