<?php
/**
 * PayPal Webhook REST API Endpoint
 *
 * Handles PayPal subscription webhook events
 *
 * @package    Warcampaign
 * @subpackage Warcampaign/includes/api
 */

class WC_PayPal_Webhook_Endpoint {

    protected $namespace = 'warcampaign/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // PayPal webhook receiver
        register_rest_route( $this->namespace, '/webhooks/paypal', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_webhook' ),
            'permission_callback' => '__return_true', // PayPal doesn't authenticate, we verify signature
        ) );
    }

    /**
     * Handle incoming PayPal webhook.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function handle_webhook( $request ) {
        $headers = $request->get_headers();
        $body = $request->get_body();

        // Verify webhook signature
        $verification = WC_PayPal_Service::verify_webhook_signature( $headers, $body );

        if ( is_wp_error( $verification ) ) {
            error_log( 'PayPal webhook signature verification failed: ' . $verification->get_error_message() );
            return new WP_REST_Response( array(
                'error' => 'Invalid signature',
            ), 401 );
        }

        $event = json_decode( $body, true );

        if ( ! isset( $event['event_type'] ) ) {
            return new WP_REST_Response( array(
                'error' => 'Invalid event',
            ), 400 );
        }

        // Log the webhook event
        error_log( 'PayPal Webhook Event: ' . $event['event_type'] );

        // Handle different event types
        switch ( $event['event_type'] ) {
            case 'BILLING.SUBSCRIPTION.CREATED':
                $this->handle_subscription_created( $event );
                break;

            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $this->handle_subscription_activated( $event );
                break;

            case 'BILLING.SUBSCRIPTION.UPDATED':
                $this->handle_subscription_updated( $event );
                break;

            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $this->handle_subscription_cancelled( $event );
                break;

            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                $this->handle_subscription_suspended( $event );
                break;

            case 'BILLING.SUBSCRIPTION.EXPIRED':
                $this->handle_subscription_expired( $event );
                break;

            case 'PAYMENT.SALE.COMPLETED':
                $this->handle_payment_completed( $event );
                break;

            case 'PAYMENT.SALE.REFUNDED':
                $this->handle_payment_refunded( $event );
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                $this->handle_payment_failed( $event );
                break;

            default:
                error_log( 'Unhandled PayPal webhook event: ' . $event['event_type'] );
                break;
        }

        return new WP_REST_Response( array(
            'success' => true,
        ), 200 );
    }

    /**
     * Handle subscription created event.
     *
     * @param array $event Event data.
     */
    private function handle_subscription_created( $event ) {
        $resource = $event['resource'] ?? array();
        $subscription_id = $resource['id'] ?? '';
        $user_id = $resource['custom_id'] ?? 0;

        if ( ! $user_id || ! $subscription_id ) {
            error_log( 'PayPal subscription created: missing user_id or subscription_id' );
            return;
        }

        // Subscription is created in pending state
        // Will be activated when first payment is completed
        error_log( "PayPal subscription {$subscription_id} created for user {$user_id}" );
    }

    /**
     * Handle subscription activated event.
     *
     * @param array $event Event data.
     */
    private function handle_subscription_activated( $event ) {
        $resource = $event['resource'] ?? array();
        $paypal_subscription_id = $resource['id'] ?? '';

        if ( ! $paypal_subscription_id ) {
            return;
        }

        $subscription = WC_Subscription::get_by_paypal_id( $paypal_subscription_id );

        if ( ! $subscription ) {
            error_log( "PayPal subscription {$paypal_subscription_id} activated but not found in database" );
            return;
        }

        // Update subscription status
        WC_Subscription::update( $subscription->id, array(
            'status' => 'active',
            'current_period_start' => current_time( 'mysql' ),
            'current_period_end' => date( 'Y-m-d H:i:s', strtotime( '+1 month' ) ),
        ) );

        // Log activity
        BCWP_Database::insert( 'activities', array(
            'user_id'      => $subscription->user_id,
            'action_type'  => 'subscription.activated',
            'subject_type' => 'subscription',
            'subject_id'   => $subscription->id,
            'description'  => 'Subscription activated',
        ) );

        error_log( "Subscription {$subscription->id} activated for user {$subscription->user_id}" );
    }

    /**
     * Handle subscription updated event.
     *
     * @param array $event Event data.
     */
    private function handle_subscription_updated( $event ) {
        $resource = $event['resource'] ?? array();
        $paypal_subscription_id = $resource['id'] ?? '';

        if ( ! $paypal_subscription_id ) {
            return;
        }

        $subscription = WC_Subscription::get_by_paypal_id( $paypal_subscription_id );

        if ( ! $subscription ) {
            return;
        }

        // Fetch latest subscription details from PayPal
        $paypal_details = WC_PayPal_Service::get_subscription( $paypal_subscription_id );

        if ( is_wp_error( $paypal_details ) ) {
            error_log( 'Failed to fetch PayPal subscription details: ' . $paypal_details->get_error_message() );
            return;
        }

        // Update local subscription
        $update_data = array();

        if ( isset( $paypal_details['status'] ) ) {
            $update_data['status'] = strtolower( $paypal_details['status'] );
        }

        if ( ! empty( $update_data ) ) {
            WC_Subscription::update( $subscription->id, $update_data );
        }
    }

    /**
     * Handle subscription cancelled event.
     *
     * @param array $event Event data.
     */
    private function handle_subscription_cancelled( $event ) {
        $resource = $event['resource'] ?? array();
        $paypal_subscription_id = $resource['id'] ?? '';

        if ( ! $paypal_subscription_id ) {
            return;
        }

        $subscription = WC_Subscription::get_by_paypal_id( $paypal_subscription_id );

        if ( ! $subscription ) {
            return;
        }

        // Update subscription
        WC_Subscription::update( $subscription->id, array(
            'status' => 'canceled',
            'canceled_at' => current_time( 'mysql' ),
            'ended_at' => current_time( 'mysql' ),
        ) );

        // Log activity
        BCWP_Database::insert( 'activities', array(
            'user_id'      => $subscription->user_id,
            'action_type'  => 'subscription.canceled',
            'subject_type' => 'subscription',
            'subject_id'   => $subscription->id,
            'description'  => 'Subscription canceled',
        ) );

        error_log( "Subscription {$subscription->id} canceled for user {$subscription->user_id}" );
    }

    /**
     * Handle subscription suspended event.
     *
     * @param array $event Event data.
     */
    private function handle_subscription_suspended( $event ) {
        $resource = $event['resource'] ?? array();
        $paypal_subscription_id = $resource['id'] ?? '';

        if ( ! $paypal_subscription_id ) {
            return;
        }

        $subscription = WC_Subscription::get_by_paypal_id( $paypal_subscription_id );

        if ( ! $subscription ) {
            return;
        }

        // Update subscription
        WC_Subscription::update( $subscription->id, array(
            'status' => 'suspended',
        ) );

        // Log activity
        BCWP_Database::insert( 'activities', array(
            'user_id'      => $subscription->user_id,
            'action_type'  => 'subscription.suspended',
            'subject_type' => 'subscription',
            'subject_id'   => $subscription->id,
            'description'  => 'Subscription suspended due to payment failure',
        ) );

        error_log( "Subscription {$subscription->id} suspended for user {$subscription->user_id}" );
    }

    /**
     * Handle subscription expired event.
     *
     * @param array $event Event data.
     */
    private function handle_subscription_expired( $event ) {
        $resource = $event['resource'] ?? array();
        $paypal_subscription_id = $resource['id'] ?? '';

        if ( ! $paypal_subscription_id ) {
            return;
        }

        $subscription = WC_Subscription::get_by_paypal_id( $paypal_subscription_id );

        if ( ! $subscription ) {
            return;
        }

        // Update subscription
        WC_Subscription::update( $subscription->id, array(
            'status' => 'expired',
            'ended_at' => current_time( 'mysql' ),
        ) );

        // Log activity
        BCWP_Database::insert( 'activities', array(
            'user_id'      => $subscription->user_id,
            'action_type'  => 'subscription.expired',
            'subject_type' => 'subscription',
            'subject_id'   => $subscription->id,
            'description'  => 'Subscription expired',
        ) );

        error_log( "Subscription {$subscription->id} expired for user {$subscription->user_id}" );
    }

    /**
     * Handle payment completed event.
     *
     * @param array $event Event data.
     */
    private function handle_payment_completed( $event ) {
        $resource = $event['resource'] ?? array();
        $billing_agreement_id = $resource['billing_agreement_id'] ?? '';
        $transaction_id = $resource['id'] ?? '';
        $amount = $resource['amount']['total'] ?? 0;
        $currency = $resource['amount']['currency'] ?? 'USD';

        if ( ! $billing_agreement_id ) {
            error_log( 'Payment completed but no billing_agreement_id found' );
            return;
        }

        $subscription = WC_Subscription::get_by_paypal_id( $billing_agreement_id );

        if ( ! $subscription ) {
            error_log( "Payment completed for unknown subscription {$billing_agreement_id}" );
            return;
        }

        // Create billing history record
        WC_Billing_History::create( array(
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'transaction_id' => $transaction_id,
            'transaction_type' => 'payment',
            'amount' => floatval( $amount ),
            'currency' => $currency,
            'status' => 'completed',
            'payment_method' => 'paypal',
            'billing_reason' => 'subscription_payment',
            'transaction_date' => current_time( 'mysql' ),
        ) );

        // Update subscription period
        WC_Subscription::update( $subscription->id, array(
            'current_period_start' => current_time( 'mysql' ),
            'current_period_end' => date( 'Y-m-d H:i:s', strtotime( '+1 month' ) ),
        ) );

        error_log( "Payment of {$amount} {$currency} completed for subscription {$subscription->id}" );
    }

    /**
     * Handle payment refunded event.
     *
     * @param array $event Event data.
     */
    private function handle_payment_refunded( $event ) {
        $resource = $event['resource'] ?? array();
        $sale_id = $resource['sale_id'] ?? '';
        $refund_id = $resource['id'] ?? '';
        $amount = $resource['amount']['total'] ?? 0;
        $currency = $resource['amount']['currency'] ?? 'USD';

        // Find the original payment
        $original_payment = WC_Billing_History::get_by_transaction_id( $sale_id );

        if ( ! $original_payment ) {
            error_log( "Refund for unknown transaction {$sale_id}" );
            return;
        }

        // Create refund record
        WC_Billing_History::create( array(
            'subscription_id' => $original_payment->subscription_id,
            'user_id' => $original_payment->user_id,
            'transaction_id' => $refund_id,
            'transaction_type' => 'refund',
            'amount' => -floatval( $amount ), // Negative amount for refund
            'currency' => $currency,
            'status' => 'completed',
            'payment_method' => 'paypal',
            'billing_reason' => 'refund',
            'transaction_date' => current_time( 'mysql' ),
            'metadata' => wp_json_encode( array( 'original_transaction_id' => $sale_id ) ),
        ) );

        error_log( "Refund of {$amount} {$currency} processed for transaction {$sale_id}" );
    }

    /**
     * Handle payment failed event.
     *
     * @param array $event Event data.
     */
    private function handle_payment_failed( $event ) {
        $resource = $event['resource'] ?? array();
        $paypal_subscription_id = $resource['id'] ?? '';

        if ( ! $paypal_subscription_id ) {
            return;
        }

        $subscription = WC_Subscription::get_by_paypal_id( $paypal_subscription_id );

        if ( ! $subscription ) {
            return;
        }

        // Create failed payment record
        WC_Billing_History::create( array(
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'transaction_type' => 'payment',
            'amount' => 0,
            'currency' => 'USD',
            'status' => 'failed',
            'payment_method' => 'paypal',
            'billing_reason' => 'subscription_payment_failed',
            'transaction_date' => current_time( 'mysql' ),
        ) );

        // Log activity
        BCWP_Database::insert( 'activities', array(
            'user_id'      => $subscription->user_id,
            'action_type'  => 'subscription.payment_failed',
            'subject_type' => 'subscription',
            'subject_id'   => $subscription->id,
            'description'  => 'Subscription payment failed',
        ) );

        error_log( "Payment failed for subscription {$subscription->id}" );
    }
}
