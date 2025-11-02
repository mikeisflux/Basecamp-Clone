<?php
/**
 * PayPal Subscription Service
 *
 * Handles PayPal REST API v2 integration for subscription billing
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_PayPal_Service {

    /**
     * PayPal API base URL
     */
    const API_BASE_URL = 'https://api-m.paypal.com';
    const SANDBOX_API_BASE_URL = 'https://api-m.sandbox.paypal.com';

    /**
     * Get PayPal API credentials from WordPress options
     *
     * @return array|false Array with client_id and client_secret, or false if not set
     */
    private static function get_credentials() {
        $client_id = get_option( 'pfob_paypal_client_id' );
        $client_secret = get_option( 'pfob_paypal_client_secret' );

        if ( empty( $client_id ) || empty( $client_secret ) ) {
            return false;
        }

        return array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'sandbox' => get_option( 'pfob_paypal_sandbox_mode', false ),
        );
    }

    /**
     * Get API base URL (production or sandbox)
     *
     * @return string API base URL
     */
    private static function get_api_base_url() {
        $credentials = self::get_credentials();
        return ( $credentials && $credentials['sandbox'] ) ? self::SANDBOX_API_BASE_URL : self::API_BASE_URL;
    }

    /**
     * Get access token from PayPal
     *
     * @return string|WP_Error Access token or WP_Error on failure
     */
    public static function get_access_token() {
        $credentials = self::get_credentials();

        if ( ! $credentials ) {
            return new WP_Error( 'no_credentials', 'PayPal credentials not configured' );
        }

        // Check cache first
        $cached_token = get_transient( 'pfob_paypal_access_token' );
        if ( $cached_token ) {
            return $cached_token;
        }

        $response = wp_remote_post(
            self::get_api_base_url() . '/v1/oauth2/token',
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en_US',
                    'Authorization' => 'Basic ' . base64_encode( $credentials['client_id'] . ':' . $credentials['client_secret'] ),
                ),
                'body' => array(
                    'grant_type' => 'client_credentials',
                ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            error_log( '[PayPal] Connection error: ' . $response->get_error_message() );
            return new WP_Error( 'connection_error', 'PayPal connection failed: ' . $response->get_error_message() );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Log the response for debugging
        error_log( '[PayPal] Token request status: ' . $status_code );
        error_log( '[PayPal] Token response: ' . wp_remote_retrieve_body( $response ) );

        if ( $status_code !== 200 ) {
            $error_message = isset( $body['error_description'] ) ? $body['error_description'] : 'HTTP ' . $status_code;
            return new WP_Error( 'auth_error', 'PayPal authentication failed: ' . $error_message );
        }

        if ( empty( $body['access_token'] ) ) {
            return new WP_Error( 'token_error', 'No access token in PayPal response' );
        }

        // Cache token for 55 minutes (expires in 60 minutes)
        set_transient( 'pfob_paypal_access_token', $body['access_token'], 55 * MINUTE_IN_SECONDS );

        return $body['access_token'];
    }

    /**
     * Make authenticated API request to PayPal
     *
     * @param string $endpoint API endpoint (e.g., '/v1/billing/subscriptions')
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param array  $data Request body data
     * @return array|WP_Error Response data or WP_Error on failure
     */
    private static function api_request( $endpoint, $method = 'GET', $data = array() ) {
        $access_token = self::get_access_token();

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        $args = array(
            'method' => $method,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ),
            'timeout' => 30,
        );

        if ( ! empty( $data ) && in_array( $method, array( 'POST', 'PATCH', 'PUT' ) ) ) {
            $args['body'] = json_encode( $data );
        }

        $response = wp_remote_request( self::get_api_base_url() . $endpoint, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Handle errors
        if ( $status_code >= 400 ) {
            $error_message = isset( $body['message'] ) ? $body['message'] : 'PayPal API error';
            return new WP_Error( 'paypal_api_error', $error_message, array( 'status' => $status_code ) );
        }

        return $body;
    }

    /**
     * Create or update PayPal subscription plans
     * This should be run once during plugin activation or when plans change
     *
     * @return array|WP_Error Array of plan IDs or WP_Error on failure
     */
    public static function sync_subscription_plans() {
        $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $plan_ids = array();

        foreach ( $plans as $plan_key => $plan_data ) {
            $paypal_plan_id = self::create_or_update_plan( $plan_key, $plan_data );

            if ( is_wp_error( $paypal_plan_id ) ) {
                return $paypal_plan_id;
            }

            $plan_ids[ $plan_key ] = $paypal_plan_id;
        }

        // Store plan IDs in WordPress options
        update_option( 'pfob_paypal_plan_ids', $plan_ids );

        return $plan_ids;
    }

    /**
     * Create or update a single subscription plan in PayPal
     *
     * @param string $plan_key Plan key (starter, professional, etc.)
     * @param array  $plan_data Plan configuration data
     * @return string|WP_Error PayPal plan ID or WP_Error on failure
     */
    private static function create_or_update_plan( $plan_key, $plan_data ) {
        // Check if plan already exists
        $existing_plan_ids = get_option( 'pfob_paypal_plan_ids', array() );

        if ( isset( $existing_plan_ids[ $plan_key ] ) ) {
            // Plan exists, return existing ID
            return $existing_plan_ids[ $plan_key ];
        }

        // Create new product first
        $product_response = self::api_request(
            '/v1/catalogs/products',
            'POST',
            array(
                'name' => 'ProjectFOB ' . $plan_data['name'] . ' Plan',
                'description' => $plan_data['description'],
                'type' => 'SERVICE',
                'category' => 'SOFTWARE',
            )
        );

        if ( is_wp_error( $product_response ) ) {
            return $product_response;
        }

        $product_id = $product_response['id'];

        // Create billing plan
        $plan_response = self::api_request(
            '/v1/billing/plans',
            'POST',
            array(
                'product_id' => $product_id,
                'name' => 'ProjectFOB ' . $plan_data['name'],
                'description' => $plan_data['description'],
                'status' => 'ACTIVE',
                'billing_cycles' => array(
                    array(
                        'frequency' => array(
                            'interval_unit' => 'MONTH',
                            'interval_count' => 1,
                        ),
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0, // Infinite
                        'pricing_scheme' => array(
                            'fixed_price' => array(
                                'value' => number_format( $plan_data['price'], 2, '.', '' ),
                                'currency_code' => $plan_data['currency'],
                            ),
                        ),
                    ),
                ),
                'payment_preferences' => array(
                    'auto_bill_outstanding' => true,
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3,
                ),
            )
        );

        if ( is_wp_error( $plan_response ) ) {
            return $plan_response;
        }

        return $plan_response['id'];
    }

    /**
     * Create a subscription for a user
     *
     * @param int    $user_id User ID
     * @param string $plan_key Plan key (starter, professional, etc.)
     * @param string $return_url URL to return to after approval
     * @param string $cancel_url URL to return to if cancelled
     * @return array|WP_Error Response with approval URL or WP_Error on failure
     */
    public static function create_subscription( $user_id, $plan_key, $return_url, $cancel_url ) {
        $plan_ids = get_option( 'pfob_paypal_plan_ids', array() );

        if ( ! isset( $plan_ids[ $plan_key ] ) ) {
            return new WP_Error( 'invalid_plan', 'Invalid subscription plan' );
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return new WP_Error( 'invalid_user', 'Invalid user ID' );
        }

        $response = self::api_request(
            '/v1/billing/subscriptions',
            'POST',
            array(
                'plan_id' => $plan_ids[ $plan_key ],
                'subscriber' => array(
                    'name' => array(
                        'given_name' => $user->first_name ?: $user->display_name,
                        'surname' => $user->last_name ?: '',
                    ),
                    'email_address' => $user->user_email,
                ),
                'application_context' => array(
                    'brand_name' => 'ProjectFOB',
                    'locale' => 'en-US',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'payment_method' => array(
                        'payer_selected' => 'PAYPAL',
                        'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                    ),
                    'return_url' => $return_url,
                    'cancel_url' => $cancel_url,
                ),
                'custom_id' => strval( $user_id ), // Store user ID for webhook handling
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Extract approval URL
        $approval_url = '';
        if ( isset( $response['links'] ) ) {
            foreach ( $response['links'] as $link ) {
                if ( $link['rel'] === 'approve' ) {
                    $approval_url = $link['href'];
                    break;
                }
            }
        }

        return array(
            'subscription_id' => $response['id'],
            'approval_url' => $approval_url,
            'status' => $response['status'],
        );
    }

    /**
     * Get subscription details
     *
     * @param string $subscription_id PayPal subscription ID
     * @return array|WP_Error Subscription details or WP_Error on failure
     */
    public static function get_subscription( $subscription_id ) {
        return self::api_request( '/v1/billing/subscriptions/' . $subscription_id );
    }

    /**
     * Cancel a subscription
     *
     * @param string $subscription_id PayPal subscription ID
     * @param string $reason Cancellation reason
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function cancel_subscription( $subscription_id, $reason = 'Customer request' ) {
        $response = self::api_request(
            '/v1/billing/subscriptions/' . $subscription_id . '/cancel',
            'POST',
            array(
                'reason' => $reason,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Suspend a subscription
     *
     * @param string $subscription_id PayPal subscription ID
     * @param string $reason Suspension reason
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function suspend_subscription( $subscription_id, $reason = 'Payment failure' ) {
        $response = self::api_request(
            '/v1/billing/subscriptions/' . $subscription_id . '/suspend',
            'POST',
            array(
                'reason' => $reason,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Activate a suspended subscription
     *
     * @param string $subscription_id PayPal subscription ID
     * @param string $reason Activation reason
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function activate_subscription( $subscription_id, $reason = 'Payment received' ) {
        $response = self::api_request(
            '/v1/billing/subscriptions/' . $subscription_id . '/activate',
            'POST',
            array(
                'reason' => $reason,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Revise subscription (requires customer approval for price changes)
     *
     * Used for adding/removing add-ons or changing plans.
     * Customer receives email from PayPal to approve the change.
     *
     * @param string $subscription_id PayPal subscription ID
     * @param string $new_plan_id New plan ID (with add-ons)
     * @param string $reason Reason for revision
     * @return array|WP_Error Revision details or WP_Error on failure
     */
    public static function revise_subscription( $subscription_id, $new_plan_id, $reason = 'Add-on subscription change' ) {
        $response = self::api_request(
            '/v1/billing/subscriptions/' . $subscription_id . '/revise',
            'POST',
            array(
                'plan_id' => $new_plan_id,
                'application_context' => array(
                    'user_action' => 'SUBSCRIBE_NOW', // Requires immediate approval
                    'return_url' => home_url( '/projectfob/adminland/billing?revision=success' ),
                    'cancel_url' => home_url( '/projectfob/adminland/billing?revision=cancelled' ),
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            error_log( '[PayPal] Subscription revision failed: ' . $response->get_error_message() );
            return $response;
        }

        // Response contains approve_link for customer to approve the change
        error_log( '[PayPal] Subscription revision created, approval required' );
        return $response;
    }

    /**
     * Update subscription custom fields (for add-on metadata without plan change)
     *
     * @param string $subscription_id PayPal subscription ID
     * @param array $custom_data Custom data to store
     * @return true|WP_Error True on success or WP_Error on failure
     */
    public static function update_subscription_custom_data( $subscription_id, $custom_data ) {
        $response = self::api_request(
            '/v1/billing/subscriptions/' . $subscription_id,
            'PATCH',
            array(
                array(
                    'op' => 'replace',
                    'path' => '/custom_id',
                    'value' => wp_json_encode( $custom_data ),
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            error_log( '[PayPal] Update custom data failed: ' . $response->get_error_message() );
            return $response;
        }

        return true;
    }

    /**
     * Get subscription transactions (billing history)
     *
     * @param string $subscription_id PayPal subscription ID
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array|WP_Error Transactions or WP_Error on failure
     */
    public static function get_subscription_transactions( $subscription_id, $start_date, $end_date ) {
        return self::api_request(
            '/v1/billing/subscriptions/' . $subscription_id . '/transactions?start_time=' .
            $start_date . 'T00:00:00Z&end_time=' . $end_date . 'T23:59:59Z'
        );
    }

    /**
     * Verify webhook signature to ensure it came from PayPal
     *
     * @param array  $headers Request headers
     * @param string $body Raw request body
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public static function verify_webhook_signature( $headers, $body ) {
        $webhook_id = get_option( 'pfob_paypal_webhook_id' );

        if ( empty( $webhook_id ) ) {
            return new WP_Error( 'no_webhook_id', 'PayPal webhook ID not configured' );
        }

        $response = self::api_request(
            '/v1/notifications/verify-webhook-signature',
            'POST',
            array(
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
                'cert_url' => $headers['PAYPAL-CERT-URL'] ?? '',
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'webhook_id' => $webhook_id,
                'webhook_event' => json_decode( $body, true ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['verification_status'] ) && $response['verification_status'] === 'SUCCESS' ) {
            return true;
        }

        return new WP_Error( 'invalid_signature', 'Invalid webhook signature' );
    }

    /**
     * Save PayPal credentials
     *
     * @param string $client_id PayPal client ID
     * @param string $client_secret PayPal client secret
     * @param bool   $sandbox_mode Whether to use sandbox mode
     * @return bool Success
     */
    public static function save_credentials( $client_id, $client_secret, $sandbox_mode = false ) {
        update_option( 'pfob_paypal_client_id', sanitize_text_field( $client_id ) );
        update_option( 'pfob_paypal_client_secret', sanitize_text_field( $client_secret ) );
        update_option( 'pfob_paypal_sandbox_mode', (bool) $sandbox_mode );

        // Clear cached token
        delete_transient( 'pfob_paypal_access_token' );

        return true;
    }

    /**
     * Check if PayPal is configured
     *
     * @return bool True if configured, false otherwise
     */
    public static function is_configured() {
        return (bool) self::get_credentials();
    }

    /**
     * Get PayPal merchant email
     *
     * @return string Merchant email (divinitycomicsinc@gmail.com)
     */
    public static function get_merchant_email() {
        return 'divinitycomicsinc@gmail.com';
    }
}
