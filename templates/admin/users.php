<?php
/**
 * Admin Users & Subscriptions Page
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get all subscriptions
global $wpdb;
$subscriptions_table = $wpdb->prefix . 'pfob_subscriptions';
$subscriptions = $wpdb->get_results(
    "SELECT * FROM {$subscriptions_table} ORDER BY created_at DESC"
);

$plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
?>

<div class="wrap pfob-admin-users">
    <h1>Users & Subscriptions</h1>

    <div class="pfob-admin-section">
        <?php if ( ! empty( $subscriptions ) ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Subscribed</th>
                        <th>Next Billing</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $subscriptions as $subscription ) : ?>
                        <?php
                        $user = get_userdata( $subscription->user_id );
                        $plan = $plans[ $subscription->plan_id ] ?? array();
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo $user ? esc_html( $user->display_name ) : 'Unknown'; ?></strong>
                            </td>
                            <td><?php echo $user ? esc_html( $user->user_email ) : 'N/A'; ?></td>
                            <td>
                                <strong><?php echo esc_html( $plan['name'] ?? ucfirst( $subscription->plan_id ) ); ?></strong>
                                <br>
                                <span style="color: #666;">$<?php echo number_format( $plan['price'] ?? 0, 2 ); ?>/mo</span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr( $subscription->status ); ?>">
                                    <?php echo esc_html( ucfirst( $subscription->status ) ); ?>
                                </span>
                            </td>
                            <td><?php echo date( 'M j, Y', strtotime( $subscription->created_at ) ); ?></td>
                            <td>
                                <?php
                                if ( $subscription->current_period_end ) {
                                    echo date( 'M j, Y', strtotime( $subscription->current_period_end ) );
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo admin_url( 'user-edit.php?user_id=' . $subscription->user_id ); ?>" class="button button-small">
                                    View User
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="notice notice-info">
                <p>No subscriptions yet. <a href="<?php echo site_url( '/projectfob/pricing' ); ?>">Test the signup flow</a> to create your first subscriber.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pfob-admin-section {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-top: 20px;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-active,
.status-trialing {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-canceled,
.status-suspended,
.status-expired {
    background: #f8d7da;
    color: #721c24;
}
</style>
