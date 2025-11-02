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
                        <th>Add-ons</th>
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
                                <div class="pfob-addons-toggle">
                                    <label class="pfob-addon-checkbox">
                                        <input type="checkbox"
                                               data-subscription-id="<?php echo $subscription->id; ?>"
                                               data-addon="timesheet"
                                               <?php checked( $has_timesheet ); ?>>
                                        Timesheet (+$50)
                                    </label>
                                    <br>
                                    <label class="pfob-addon-checkbox">
                                        <input type="checkbox"
                                               data-subscription-id="<?php echo $subscription->id; ?>"
                                               data-addon="admin-pro"
                                               <?php checked( $has_admin_pro ); ?>>
                                        Admin Pro (+$50)
                                    </label>
                                </div>
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

.pfob-addons-toggle {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.pfob-addon-checkbox {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    cursor: pointer;
}

.pfob-addon-checkbox input[type="checkbox"] {
    cursor: pointer;
}

.pfob-addon-checkbox input[type="checkbox"]:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle add-on toggle
    $('.pfob-addon-checkbox input[type="checkbox"]').on('change', function() {
        var $checkbox = $(this);
        var subscriptionId = $checkbox.data('subscription-id');
        var addon = $checkbox.data('addon');
        var isChecked = $checkbox.prop('checked');

        // Disable checkbox during request
        $checkbox.prop('disabled', true);

        // Determine the action (add or remove)
        var action = isChecked ? 'pfob_admin_add_addon' : 'pfob_admin_remove_addon';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: action,
                subscription_id: subscriptionId,
                addon: addon,
                nonce: '<?php echo wp_create_nonce( 'pfob_admin_addon_toggle' ); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    var message = isChecked ? 'Add-on enabled' : 'Add-on disabled';
                    $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>')
                        .insertAfter('.wrap h1')
                        .delay(3000)
                        .fadeOut();
                } else {
                    // Revert checkbox on error
                    $checkbox.prop('checked', !isChecked);
                    alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
                }
                $checkbox.prop('disabled', false);
            },
            error: function() {
                // Revert checkbox on error
                $checkbox.prop('checked', !isChecked);
                alert('AJAX request failed. Please try again.');
                $checkbox.prop('disabled', false);
            }
        });
    });
});
</script>
