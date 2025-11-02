<?php
/**
 * Subscription Upgrades Page
 *
 * Allows account owners to add Timesheet and Admin Pro Pack upgrades.
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

// Get base plan cost
$current_cost = $plan['price'];

// Get add-ons from subscription metadata
// Note: metadata is already decoded as an array in get_by_user_id()
$metadata = array();
if ( ! empty( $subscription->metadata ) ) {
    if ( is_array( $subscription->metadata ) ) {
        $metadata = $subscription->metadata;
    } else {
        // Fallback if still a string
        $decoded = json_decode( $subscription->metadata, true );
        if ( is_array( $decoded ) ) {
            $metadata = $decoded;
        }
    }
}
$has_timesheet = isset( $metadata['addon_timesheet'] ) && $metadata['addon_timesheet'] === true;
$has_admin_pro = isset( $metadata['addon_admin_pro'] ) && $metadata['addon_admin_pro'] === true;

// Calculate total cost with add-ons
if ( $has_timesheet ) {
    $current_cost += 50;
}
if ( $has_admin_pro ) {
    $current_cost += 50;
}

$next_billing = date( 'F j, Y', strtotime( $subscription->current_period_end ) );

PFOB_Template::header( 'Upgrades' );
?>

<div class="upgrade-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Upgrades</h1>
    <p class="page-subtitle">Enhance your ProjectFOB account with these powerful add-ons.</p>

    <!-- Timesheet Upgrade -->
    <div class="upgrade-card <?php echo $has_timesheet ? 'is-active' : ''; ?>">
        <div class="upgrade-header">
            <h2 class="upgrade-name">⏱️ Timesheet</h2>
            <div class="upgrade-price">$50/month</div>
        </div>

        <p class="upgrade-description">Give your team the power to track time spent on projects.</p>

        <h3 class="features-heading">What's included:</h3>

        <div class="feature-item">✓ Track time on projects, to-dos, and more</div>
        <div class="feature-item">✓ View total hours by project or person</div>
        <div class="feature-item">✓ Create custom reports</div>
        <div class="feature-item">✓ Export timesheets in CSV format</div>
        <div class="feature-item">✓ Integration with popular accounting software</div>
        <div class="feature-item">✓ Automated time tracking reminders</div>

        <?php if ( ! $has_timesheet ) : ?>
            <div class="pricing-box">
                <p>You currently pay <strong>$<?php echo $current_cost; ?>/month</strong>, so your new total will be <strong>$<?php echo $current_cost + 50; ?>/month</strong>.</p>
                <p class="billing-note">You won't be charged until <?php echo $next_billing; ?>. Your account will be instantly updated, and you can remove this upgrade any time.</p>
            </div>
            <button class="buy-button" data-upgrade="timesheet">Buy Timesheet</button>
        <?php else : ?>
            <div class="active-badge">✓ Active</div>
            <button class="remove-button" data-remove="timesheet">Remove Timesheet</button>
        <?php endif; ?>
    </div>

    <!-- Admin Pro Pack Upgrade -->
    <div class="upgrade-card <?php echo $has_admin_pro ? 'is-active' : ''; ?>">
        <div class="upgrade-header">
            <h2 class="upgrade-name">🛡️ Admin Pro Pack</h2>
            <div class="upgrade-price">$50/month</div>
        </div>

        <p class="upgrade-description">Advanced administrative controls for account owners and administrators.</p>

        <h3 class="features-heading">What's included:</h3>

        <div class="feature-item">✓ Choose who can send pings</div>
        <div class="feature-item">✓ Choose who can turn on public links</div>
        <div class="feature-item">✓ Choose who can archive and delete projects, docs, and more</div>
        <div class="feature-item">✓ Choose who can change the people on a project</div>
        <div class="feature-item">✓ Choose who can change project settings</div>
        <div class="feature-item">✓ Limit editing comments and chats to 15 minutes</div>
        <div class="feature-item">✓ Clean Sweep: Archive completed to-dos and cards automatically</div>
        <div class="feature-item">✓ Set Out of Office for others</div>
        <div class="feature-item">✓ Require two-factor authentication</div>
        <div class="feature-item">✓ Change Ping & Chat history settings</div>

        <?php if ( ! $has_admin_pro ) : ?>
            <div class="pricing-box">
                <p>You currently pay <strong>$<?php echo $current_cost; ?>/month</strong>, so your new total will be <strong>$<?php echo $current_cost + 50; ?>/month</strong>.</p>
                <p class="billing-note">You won't be charged until <?php echo $next_billing; ?>. Your account will be instantly updated, and you can remove this upgrade any time.</p>
            </div>
            <button class="buy-button" data-upgrade="admin-pro">Buy Admin Pro Pack</button>
        <?php else : ?>
            <div class="active-badge">✓ Active</div>
            <button class="remove-button" data-remove="admin-pro">Remove Admin Pro Pack</button>
        <?php endif; ?>
    </div>

    <div class="tax-notice">
        <p>💡 A number of localities require us to collect sales tax on ProjectFOB subscriptions.</p>
        <p>If your company is officially tax-exempt, contact our support team to request an exemption.</p>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.upgrade-page-wrapper {
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

.upgrade-card {
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 32px;
}

.upgrade-card.is-active {
    border-color: #10b981;
    background: #f0fdf4;
}

.upgrade-header {
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f0f0;
}

.upgrade-name {
    font-size: 24px;
    margin: 0 0 8px 0;
    color: #333;
}

.upgrade-price {
    font-size: 20px;
    font-weight: 600;
    color: #0066cc;
    margin-top: 8px;
}

.upgrade-description {
    font-size: 16px;
    color: #666;
    margin: 0 0 24px 0;
    line-height: 1.6;
}

.features-heading {
    font-size: 16px;
    font-weight: 600;
    margin: 24px 0 12px 0;
    color: #333;
}

.feature-item {
    padding: 8px 0;
    font-size: 15px;
    color: #333;
    border-bottom: 1px solid #f5f5f5;
}

.feature-item:last-of-type {
    border-bottom: none;
    margin-bottom: 24px;
}

.pricing-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    margin: 24px 0 20px 0;
}

.pricing-box p {
    margin: 8px 0;
    font-size: 15px;
    color: #333;
}

.billing-note {
    font-size: 14px !important;
    color: #666 !important;
}

.buy-button {
    display: inline-block;
    padding: 12px 24px;
    background: #0066cc;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
}

.buy-button:hover {
    background: #0052a3;
}

.buy-button:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.active-badge {
    display: inline-block;
    background: #10b981;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    margin-bottom: 12px;
}

.remove-button {
    display: inline-block;
    padding: 10px 20px;
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
}

.remove-button:hover {
    background: #d1d5db;
}

.remove-button:disabled {
    background: #f3f4f6;
    cursor: not-allowed;
}

.tax-notice {
    background: #fffbeb;
    padding: 20px;
    border-radius: 6px;
    border-left: 4px solid #f59e0b;
    margin-top: 32px;
}

.tax-notice p {
    margin: 8px 0;
    font-size: 14px;
    color: #666;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle upgrade purchases
    document.querySelectorAll('[data-upgrade]').forEach(btn => {
        btn.addEventListener('click', async function() {
            const addon = this.dataset.upgrade;

            if (!confirm(`Are you sure you want to add ${addon === 'timesheet' ? 'Timesheet' : 'Admin Pro Pack'} for $50/month?`)) {
                return;
            }

            this.disabled = true;
            this.textContent = 'Processing...';

            try {
                const response = await fetch(`${pfobData.restUrl}/subscription/add-addon`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': pfobData.nonce
                    },
                    body: JSON.stringify({ addon })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Failed to add upgrade');
                    this.disabled = false;
                    this.textContent = addon === 'timesheet' ? 'Buy Timesheet' : 'Buy Admin Pro Pack';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
                this.disabled = false;
                this.textContent = addon === 'timesheet' ? 'Buy Timesheet' : 'Buy Admin Pro Pack';
            }
        });
    });

    // Handle upgrade removal
    document.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', async function() {
            const addon = this.dataset.remove;

            if (!confirm(`Are you sure you want to remove ${addon === 'timesheet' ? 'Timesheet' : 'Admin Pro Pack'}?`)) {
                return;
            }

            this.disabled = true;
            this.textContent = 'Processing...';

            try {
                const response = await fetch(`${pfobData.restUrl}/subscription/remove-addon`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': pfobData.nonce
                    },
                    body: JSON.stringify({ addon })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Failed to remove upgrade');
                    this.disabled = false;
                    this.textContent = addon === 'timesheet' ? 'Remove Timesheet' : 'Remove Admin Pro Pack';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
                this.disabled = false;
                this.textContent = addon === 'timesheet' ? 'Remove Timesheet' : 'Remove Admin Pro Pack';
            }
        });
    });
});
</script>

<?php
PFOB_Template::footer();
