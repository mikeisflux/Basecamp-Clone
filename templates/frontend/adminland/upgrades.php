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
$metadata = array();
if ( ! empty( $subscription->metadata ) ) {
    $decoded = json_decode( $subscription->metadata, true );
    if ( is_array( $decoded ) ) {
        $metadata = $decoded;
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

<div class="pfob-container pfob-upgrades-page">
    <div class="pfob-page-header">
        <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="pfob-back-link">← Back to Adminland</a>
        <h1>Upgrades</h1>
        <p>Enhance your ProjectFOB account with these powerful add-ons.</p>
    </div>

    <div class="pfob-upgrade-cards">
        <!-- Timesheet Add-on -->
        <div class="pfob-upgrade-card <?php echo $has_timesheet ? 'active' : ''; ?>">
            <div class="pfob-upgrade-header">
                <h2>⏱️ Timesheet</h2>
                <div class="pfob-price">$50/month</div>
            </div>

            <p class="pfob-description">Give your team the power to track time spent on projects.</p>

            <h3>What's included:</h3>
            <ul class="pfob-features-list">
                <li>✓ Track time on projects, to-dos, and more</li>
                <li>✓ View total hours by project or person</li>
                <li>✓ Create custom reports</li>
                <li>✓ Export timesheets in CSV format</li>
                <li>✓ Integration with popular accounting software</li>
                <li>✓ Automated time tracking reminders</li>
            </ul>

            <?php if ( ! $has_timesheet ) : ?>
                <div class="pfob-pricing-info">
                    <p>You currently pay <strong>$<?php echo $current_cost; ?>/month</strong>, so your new total will be <strong>$<?php echo $current_cost + 50; ?>/month</strong>.</p>
                    <p class="pfob-billing-note">You won't be charged until <?php echo $next_billing; ?>. Your account will be instantly updated, and you can remove this upgrade any time.</p>
                </div>
                <button class="pfob-btn pfob-btn-primary pfob-btn-large" data-upgrade="timesheet">
                    Buy Timesheet
                </button>
            <?php else : ?>
                <div class="pfob-active-badge">
                    <span>✓ Active</span>
                </div>
                <button class="pfob-btn pfob-btn-secondary" data-remove="timesheet">
                    Remove Timesheet
                </button>
            <?php endif; ?>
        </div>

        <!-- Admin Pro Pack -->
        <div class="pfob-upgrade-card <?php echo $has_admin_pro ? 'active' : ''; ?>">
            <div class="pfob-upgrade-header">
                <h2>🛡️ Admin Pro Pack</h2>
                <div class="pfob-price">$50/month</div>
            </div>

            <p class="pfob-description">Advanced administrative controls for account owners and administrators.</p>

            <h3>What's included:</h3>
            <ul class="pfob-features-list">
                <li>✓ Choose who can send pings</li>
                <li>✓ Choose who can turn on public links</li>
                <li>✓ Choose who can archive and delete projects, docs, and more</li>
                <li>✓ Choose who can change the people on a project</li>
                <li>✓ Choose who can change project settings</li>
                <li>✓ Limit editing comments and chats to 15 minutes</li>
                <li>✓ Clean Sweep: Archive completed to-dos and cards automatically</li>
                <li>✓ Set Out of Office for others</li>
                <li>✓ Require two-factor authentication</li>
                <li>✓ Change Ping & Chat history settings</li>
            </ul>

            <?php if ( ! $has_admin_pro ) : ?>
                <div class="pfob-pricing-info">
                    <p>You currently pay <strong>$<?php echo $current_cost; ?>/month</strong>, so your new total will be <strong>$<?php echo $current_cost + 50; ?>/month</strong>.</p>
                    <p class="pfob-billing-note">You won't be charged until <?php echo $next_billing; ?>. Your account will be instantly updated, and you can remove this upgrade any time.</p>
                </div>
                <button class="pfob-btn pfob-btn-primary pfob-btn-large" data-upgrade="admin-pro">
                    Buy Admin Pro Pack
                </button>
            <?php else : ?>
                <div class="pfob-active-badge">
                    <span>✓ Active</span>
                </div>
                <button class="pfob-btn pfob-btn-secondary" data-remove="admin-pro">
                    Remove Admin Pro Pack
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="pfob-tax-info">
        <p>💡 A number of localities require us to collect sales tax on ProjectFOB subscriptions.</p>
        <p>If your company is officially tax-exempt, contact our support team to request an exemption.</p>
    </div>
</div>

<style>
.pfob-upgrades-page {
    max-width: 1200px;
    margin: 0 auto;
}

.pfob-back-link {
    display: inline-block;
    margin-bottom: 16px;
    color: #0066cc;
    text-decoration: none;
}

.pfob-back-link:hover {
    text-decoration: underline;
}

.pfob-upgrade-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 32px;
    margin: 32px 0;
}

.pfob-upgrade-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.pfob-upgrade-card.active {
    border-color: #10b981;
    background: #f0fdf4;
}

.pfob-upgrade-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.pfob-upgrade-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.pfob-upgrade-header h2 {
    margin: 0;
    font-size: 24px;
}

.pfob-price {
    font-size: 20px;
    font-weight: bold;
    color: #0066cc;
}

.pfob-description {
    color: #666;
    margin-bottom: 24px;
    font-size: 16px;
}

.pfob-upgrade-card h3 {
    font-size: 16px;
    margin: 24px 0 12px 0;
    color: #333;
}

.pfob-features-list {
    list-style: none;
    padding: 0;
    margin: 0 0 24px 0;
}

.pfob-features-list li {
    padding: 8px 0;
    color: #333;
}

.pfob-pricing-info {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.pfob-pricing-info p {
    margin: 8px 0;
    font-size: 14px;
}

.pfob-billing-note {
    color: #666;
    font-size: 13px !important;
}

.pfob-active-badge {
    display: inline-block;
    background: #10b981;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    margin-bottom: 12px;
}

.pfob-tax-info {
    background: #fff9e6;
    padding: 20px;
    border-radius: 6px;
    border-left: 4px solid #ffcc00;
    margin-top: 32px;
}

.pfob-tax-info p {
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
