<?php
/**
 * Billing Management Page
 *
 * Allows subscribers to view invoices, manage payment methods, and handle subscriptions.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();
$subscription = PFOB_Subscription::get_by_user_id( $user_id );

if ( ! $subscription ) {
    wp_die( __( 'No active subscription found.', 'projectfob' ) );
}

// Get subscription plan configuration
$plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
$plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

if ( ! $plan ) {
    wp_die( __( 'Invalid subscription plan.', 'projectfob' ) );
}

// Calculate costs
$base_cost = $plan['price'];
$monthly_cost = $base_cost;

// Get add-ons
$metadata = array();
if ( ! empty( $subscription->metadata ) ) {
    if ( is_array( $subscription->metadata ) ) {
        $metadata = $subscription->metadata;
    } else {
        $decoded = json_decode( $subscription->metadata, true );
        if ( is_array( $decoded ) ) {
            $metadata = $decoded;
        }
    }
}

$has_timesheet = isset( $metadata['addon_timesheet'] ) && $metadata['addon_timesheet'] === true;
$has_admin_pro = isset( $metadata['addon_admin_pro'] ) && $metadata['addon_admin_pro'] === true;

if ( $has_timesheet ) {
    $monthly_cost += 50;
}
if ( $has_admin_pro ) {
    $monthly_cost += 50;
}

$next_billing = $subscription->current_period_end ? date( 'F j, Y', strtotime( $subscription->current_period_end ) ) : 'N/A';

// Get billing history
global $wpdb;
$billing_table = $wpdb->prefix . 'pfob_billing_history';
$invoices = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$billing_table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 12",
        $user_id
    )
);

PFOB_Template::header( 'Billing' );
?>

<div class="billing-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Billing & Invoices</h1>
    <p class="page-subtitle">Manage your subscription, view invoices, and update payment methods.</p>

    <!-- Current Subscription -->
    <div class="billing-section">
        <h2 class="section-title">Current Subscription</h2>

        <div class="subscription-summary">
            <div class="summary-row">
                <span class="row-label">Plan:</span>
                <span class="row-value"><?php echo esc_html( $plan['name'] ); ?></span>
            </div>

            <div class="summary-row">
                <span class="row-label">Base Price:</span>
                <span class="row-value">$<?php echo number_format( $base_cost, 2 ); ?>/month</span>
            </div>

            <?php if ( $has_timesheet ) : ?>
            <div class="summary-row">
                <span class="row-label">Timesheet Add-on:</span>
                <span class="row-value">$50.00/month</span>
            </div>
            <?php endif; ?>

            <?php if ( $has_admin_pro ) : ?>
            <div class="summary-row">
                <span class="row-label">Admin Pro Pack:</span>
                <span class="row-value">$50.00/month</span>
            </div>
            <?php endif; ?>

            <div class="summary-row total-row">
                <span class="row-label">Total Monthly:</span>
                <span class="row-value total-value">$<?php echo number_format( $monthly_cost, 2 ); ?></span>
            </div>

            <div class="summary-row">
                <span class="row-label">Next Payment:</span>
                <span class="row-value"><?php echo esc_html( $next_billing ); ?></span>
            </div>

            <div class="summary-row">
                <span class="row-label">Status:</span>
                <span class="status-badge status-<?php echo esc_attr( $subscription->status ); ?>">
                    <?php echo esc_html( ucfirst( $subscription->status ) ); ?>
                </span>
            </div>
        </div>

        <div class="billing-actions">
            <a href="<?php echo home_url( '/projectfob/adminland/upgrades' ); ?>" class="action-button primary-button">
                Manage Add-ons
            </a>
            <a href="#" class="action-button secondary-button" onclick="alert('Change plan functionality coming soon'); return false;">
                Change Plan
            </a>
        </div>
    </div>

    <!-- Payment Method -->
    <div class="billing-section">
        <h2 class="section-title">Payment Method</h2>

        <?php if ( ! empty( $subscription->paypal_subscription_id ) ) : ?>
        <div class="payment-info">
            <div class="payment-row">
                <span class="payment-icon">💳</span>
                <div class="payment-details">
                    <div class="payment-type">PayPal</div>
                    <div class="payment-id">Subscription ID: <?php echo esc_html( $subscription->paypal_subscription_id ); ?></div>
                </div>
            </div>

            <a href="https://www.paypal.com/myaccount/autopay/" target="_blank" class="action-button secondary-button">
                Manage on PayPal →
            </a>
        </div>
        <?php else : ?>
        <p class="empty-message">No payment method on file.</p>
        <?php endif; ?>
    </div>

    <!-- Invoices & Billing History -->
    <div class="billing-section">
        <h2 class="section-title">Invoices & Billing History</h2>

        <?php if ( ! empty( $invoices ) ) : ?>
        <div class="invoices-table">
            <div class="invoice-header">
                <div class="invoice-col">Date</div>
                <div class="invoice-col">Description</div>
                <div class="invoice-col">Amount</div>
                <div class="invoice-col">Status</div>
            </div>

            <?php foreach ( $invoices as $invoice ) : ?>
            <div class="invoice-row">
                <div class="invoice-col">
                    <?php echo date( 'M j, Y', strtotime( $invoice->created_at ) ); ?>
                </div>
                <div class="invoice-col">
                    <?php echo esc_html( $invoice->description ?? 'Monthly Subscription' ); ?>
                </div>
                <div class="invoice-col">
                    $<?php echo number_format( $invoice->amount, 2 ); ?>
                </div>
                <div class="invoice-col">
                    <span class="payment-status status-<?php echo esc_attr( $invoice->status ); ?>">
                        <?php echo esc_html( ucfirst( $invoice->status ) ); ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <p class="empty-message">No billing history available yet.</p>
        <?php endif; ?>
    </div>

    <!-- Danger Zone -->
    <div class="billing-section danger-section">
        <h2 class="section-title">Danger Zone</h2>

        <div class="danger-content">
            <div class="danger-info">
                <h3>Cancel Subscription</h3>
                <p>Once you cancel, you'll lose access at the end of your current billing period.</p>
            </div>
            <button class="action-button danger-button" onclick="confirmCancellation()">
                Cancel Subscription
            </button>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.billing-page-wrapper {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.back-link {
    display: inline-block;
    margin-bottom: 16px;
    color: #0066cc;
    text-decoration: none;
    font-size: 15px;
}

.back-link:hover {
    text-decoration: underline;
}

.page-title {
    font-size: 32px;
    margin: 0 0 8px 0;
    color: #333;
}

.page-subtitle {
    font-size: 16px;
    color: #666;
    margin: 0 0 32px 0;
}

.billing-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    margin: 0 0 20px 0;
    color: #333;
}

.subscription-summary {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f5f5f5;
}

.summary-row:last-child {
    border-bottom: none;
}

.total-row {
    border-top: 2px solid #333;
    margin-top: 8px;
    padding-top: 16px;
}

.row-label {
    color: #666;
    font-size: 15px;
}

.row-value {
    color: #333;
    font-size: 15px;
    font-weight: 500;
}

.total-value {
    font-size: 20px;
    font-weight: 600;
    color: #0066cc;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-trialing {
    background: #fff3cd;
    color: #856404;
}

.status-canceled {
    background: #f8d7da;
    color: #721c24;
}

.billing-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-button {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.primary-button {
    background: #0066cc;
    color: white;
}

.primary-button:hover {
    background: #0052a3;
}

.secondary-button {
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
}

.secondary-button:hover {
    background: #d1d5db;
}

.danger-button {
    background: #dc3545;
    color: white;
}

.danger-button:hover {
    background: #c82333;
}

.payment-info {
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
}

.payment-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.payment-icon {
    font-size: 32px;
}

.payment-details {
    flex: 1;
}

.payment-type {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.payment-id {
    font-size: 13px;
    color: #666;
}

.invoices-table {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
}

.invoice-header {
    display: flex;
    background: #f8f9fa;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.invoice-row {
    display: flex;
    padding: 16px;
    border-top: 1px solid #f0f0f0;
}

.invoice-col {
    flex: 1;
    font-size: 14px;
    color: #333;
}

.invoice-col:first-child {
    flex: 0 0 120px;
}

.invoice-col:nth-child(3),
.invoice-col:nth-child(4) {
    flex: 0 0 100px;
}

.payment-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.payment-status.status-completed,
.payment-status.status-paid {
    background: #d4edda;
    color: #155724;
}

.payment-status.status-pending {
    background: #fff3cd;
    color: #856404;
}

.payment-status.status-failed {
    background: #f8d7da;
    color: #721c24;
}

.empty-message {
    color: #666;
    font-style: italic;
    padding: 20px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 6px;
}

.danger-section {
    border-color: #dc3545;
    border-left: 4px solid #dc3545;
}

.danger-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.danger-info h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
    color: #333;
}

.danger-info p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

@media (max-width: 768px) {
    .billing-actions,
    .danger-content {
        flex-direction: column;
        align-items: stretch;
    }

    .action-button {
        text-align: center;
    }

    .invoice-header {
        display: none;
    }

    .invoice-row {
        flex-direction: column;
        gap: 8px;
    }

    .invoice-col {
        flex: none !important;
    }
}
</style>

<script>
function confirmCancellation() {
    if (confirm('Are you sure you want to cancel your subscription? You will lose access at the end of your current billing period.')) {
        if (confirm('This action cannot be undone. Are you absolutely sure?')) {
            // TODO: Implement cancellation via AJAX
            alert('Cancellation functionality will be implemented soon. Please contact support to cancel your subscription.');
        }
    }
}
</script>

<?php
PFOB_Template::footer();
