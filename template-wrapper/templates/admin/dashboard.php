<?php
/**
 * Admin Dashboard Template
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get statistics
$subscription_stats = PFOB_Subscription::get_statistics();
$active_subscriptions = $subscription_stats['active'] ?? 0;
$canceled_subscriptions = $subscription_stats['canceled'] ?? 0;
$mrr = $subscription_stats['mrr'] ?? 0;
$by_plan = $subscription_stats['by_plan'] ?? array();

// Get recent transactions
$recent_transactions = PFOB_Billing_History::get_recent( 10 );

// Check configuration status
$paypal_configured = PFOB_PayPal_Service::is_configured();
$r2_configured = PFOB_R2_Storage_Service::is_configured();
?>

<div class="wrap pfob-admin-dashboard">
    <h1>ProjectFOB Dashboard</h1>

    <!-- Configuration Status -->
    <?php if ( ! $paypal_configured || ! $r2_configured ) : ?>
        <div class="notice notice-warning">
            <p><strong>Setup Required:</strong> Please configure the following to start accepting subscriptions:</p>
            <ul>
                <?php if ( ! $paypal_configured ) : ?>
                    <li><a href="<?php echo admin_url( 'admin.php?page=projectfob-settings' ); ?>">Configure PayPal credentials</a></li>
                <?php endif; ?>
                <?php if ( ! $r2_configured ) : ?>
                    <li><a href="<?php echo admin_url( 'admin.php?page=projectfob-settings' ); ?>">Configure Cloudflare R2 storage</a></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Stats Grid -->
    <div class="pfob-stats-grid">
        <div class="pfob-stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Subscriptions</div>
                <div class="stat-value"><?php echo number_format( $active_subscriptions ); ?></div>
            </div>
        </div>

        <div class="pfob-stat-card">
            <div class="stat-icon" style="background: #dc3545;">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Canceled</div>
                <div class="stat-value"><?php echo number_format( $canceled_subscriptions ); ?></div>
            </div>
        </div>

        <div class="pfob-stat-card">
            <div class="stat-icon" style="background: #28a745;">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Monthly Recurring Revenue</div>
                <div class="stat-value">$<?php echo number_format( $mrr, 2 ); ?></div>
            </div>
        </div>

        <div class="pfob-stat-card">
            <div class="stat-icon" style="background: #ffc107;">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Annual Run Rate</div>
                <div class="stat-value">$<?php echo number_format( $mrr * 12, 2 ); ?></div>
            </div>
        </div>
    </div>

    <!-- Subscriptions by Plan -->
    <?php if ( ! empty( $by_plan ) ) : ?>
        <div class="pfob-dashboard-section">
            <h2>Subscriptions by Plan</h2>
            <div class="pfob-plan-grid">
                <?php
                $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
                foreach ( $by_plan as $plan_data ) :
                    $plan_key = $plan_data->plan_id;
                    $plan_info = $plans[ $plan_key ] ?? array();
                    $count = $plan_data->count ?? 0;
                    ?>
                    <div class="pfob-plan-card">
                        <h3><?php echo esc_html( $plan_info['name'] ?? ucfirst( $plan_key ) ); ?></h3>
                        <div class="plan-price">$<?php echo number_format( $plan_info['price'] ?? 0, 2 ); ?>/mo</div>
                        <div class="plan-subscribers">
                            <strong><?php echo number_format( $count ); ?></strong> subscriber<?php echo $count !== 1 ? 's' : ''; ?>
                        </div>
                        <div class="plan-revenue">
                            Revenue: <strong>$<?php echo number_format( ( $plan_info['price'] ?? 0 ) * $count, 2 ); ?>/mo</strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recent Transactions -->
    <div class="pfob-dashboard-section">
        <h2>Recent Transactions</h2>
        <?php if ( ! empty( $recent_transactions ) ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Transaction ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $recent_transactions as $transaction ) : ?>
                        <tr>
                            <td><?php echo date( 'M j, Y g:i a', strtotime( $transaction->transaction_date ) ); ?></td>
                            <td>
                                <?php
                                $user = get_userdata( $transaction->user_id );
                                echo $user ? esc_html( $user->display_name ) : 'Unknown';
                                ?>
                            </td>
                            <td><?php echo esc_html( ucfirst( $transaction->transaction_type ) ); ?></td>
                            <td>
                                <strong style="color: <?php echo $transaction->amount < 0 ? '#dc3545' : '#28a745'; ?>;">
                                    <?php echo $transaction->amount < 0 ? '-' : ''; ?>$<?php echo number_format( abs( $transaction->amount ), 2 ); ?>
                                </strong>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr( $transaction->status ); ?>">
                                    <?php echo esc_html( ucfirst( $transaction->status ) ); ?>
                                </span>
                            </td>
                            <td>
                                <code><?php echo esc_html( $transaction->transaction_id ?: 'N/A' ); ?></code>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No transactions yet. <a href="<?php echo site_url( '/projectfob/pricing' ); ?>">Visit pricing page</a> to test the signup flow.</p>
        <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="pfob-dashboard-section">
        <h2>Quick Links</h2>
        <div class="pfob-quick-links">
            <a href="<?php echo admin_url( 'admin.php?page=projectfob-settings' ); ?>" class="pfob-link-card">
                <span class="dashicons dashicons-admin-settings"></span>
                <span>Settings</span>
            </a>
            <a href="<?php echo admin_url( 'admin.php?page=projectfob-users' ); ?>" class="pfob-link-card">
                <span class="dashicons dashicons-groups"></span>
                <span>Manage Users</span>
            </a>
            <a href="<?php echo admin_url( 'admin.php?page=projectfob-billing' ); ?>" class="pfob-link-card">
                <span class="dashicons dashicons-money-alt"></span>
                <span>Billing & Revenue</span>
            </a>
            <a href="#" class="pfob-link-card" id="pfob-sync-users-btn">
                <span class="dashicons dashicons-update"></span>
                <span>Sync Users</span>
            </a>
            <a href="<?php echo site_url( '/projectfob/pricing' ); ?>" class="pfob-link-card" target="_blank">
                <span class="dashicons dashicons-external"></span>
                <span>View Pricing Page</span>
            </a>
            <a href="<?php echo site_url( '/projectfob/' ); ?>" class="pfob-link-card" target="_blank">
                <span class="dashicons dashicons-dashboard"></span>
                <span>User Dashboard</span>
            </a>
        </div>
        <p class="description" style="margin-top: 15px;">
            <strong>Sync Users:</strong> Click this button to sync all WordPress users with the subscription system.
            This is useful after uninstalling/reinstalling the plugin or when adding new users.
        </p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Sync users button handler
    $('#pfob-sync-users-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalHtml = $btn.html();

        if ($btn.hasClass('syncing')) {
            return; // Already syncing
        }

        $btn.addClass('syncing').html('<span class="dashicons dashicons-update spinning"></span><span>Syncing...</span>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pfob_sync_users',
                nonce: '<?php echo wp_create_nonce( 'pfob_admin_nonce' ); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $btn.removeClass('syncing').addClass('success').html('<span class="dashicons dashicons-yes"></span><span>' + response.data.message + '</span>');

                    // Reload page after 2 seconds to show updated stats
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
                    $btn.removeClass('syncing').html(originalHtml);
                }
            },
            error: function() {
                alert('AJAX request failed. Please try again.');
                $btn.removeClass('syncing').html(originalHtml);
            }
        });
    });
});
</script>

<style>
.pfob-link-card.syncing {
    pointer-events: none;
    opacity: 0.7;
}
.pfob-link-card.success {
    border-color: #28a745;
    background: #d4edda;
    color: #155724;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.spinning {
    animation: spin 1s linear infinite;
}
</style>

<style>
.pfob-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.pfob-stat-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: #2271b1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon .dashicons {
    color: white;
    font-size: 30px;
    width: 30px;
    height: 30px;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 13px;
    color: #646970;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1d2327;
}

.pfob-dashboard-section {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.pfob-dashboard-section h2 {
    margin-top: 0;
}

.pfob-plan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.pfob-plan-card {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.pfob-plan-card h3 {
    margin: 0 0 10px 0;
    color: #2271b1;
}

.plan-price {
    font-size: 24px;
    font-weight: 700;
    margin: 10px 0;
}

.plan-subscribers {
    font-size: 18px;
    margin: 10px 0;
}

.plan-revenue {
    font-size: 14px;
    color: #646970;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-failed {
    background: #f8d7da;
    color: #721c24;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.pfob-quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.pfob-link-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-decoration: none;
    color: #2271b1;
    transition: all 0.3s ease;
}

.pfob-link-card:hover {
    border-color: #2271b1;
    background: #f6f7f7;
    transform: translateY(-2px);
}

.pfob-link-card .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
}

.pfob-link-card span:last-child {
    font-weight: 600;
}
</style>
