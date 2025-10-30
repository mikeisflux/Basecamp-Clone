<?php
/**
 * Subscription Management Admin Page
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/admin
 */

class PFOB_Subscription_Manager {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_subscription_menu'));
        add_action('admin_init', array($this, 'handle_subscription_actions'));
    }

    public function add_subscription_menu() {
        add_submenu_page(
            'projectfob',
            'Subscription Management',
            'Subscriptions',
            'manage_options',
            'projectfob-subscriptions',
            array($this, 'subscriptions_page')
        );
    }

    public function handle_subscription_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Change subscription plan
        if (isset($_POST['change_plan']) && check_admin_referer('pfob_change_plan')) {
            $user_id = intval($_POST['user_id']);
            $new_plan = sanitize_text_field($_POST['new_plan']);

            $result = $this->change_user_plan($user_id, $new_plan);

            if ($result) {
                add_settings_error('pfob_subscriptions', 'plan_changed', 'Subscription plan changed successfully!', 'success');
            } else {
                add_settings_error('pfob_subscriptions', 'plan_change_failed', 'Failed to change subscription plan.', 'error');
            }
        }

        // Update subscription status
        if (isset($_POST['update_status']) && check_admin_referer('pfob_update_status')) {
            $user_id = intval($_POST['user_id']);
            $new_status = sanitize_text_field($_POST['new_status']);

            $result = $this->update_subscription_status($user_id, $new_status);

            if ($result) {
                add_settings_error('pfob_subscriptions', 'status_updated', 'Subscription status updated successfully!', 'success');
            } else {
                add_settings_error('pfob_subscriptions', 'status_update_failed', 'Failed to update subscription status.', 'error');
            }
        }

        // Cancel subscription
        if (isset($_POST['cancel_subscription']) && check_admin_referer('pfob_cancel_subscription')) {
            $user_id = intval($_POST['user_id']);

            $result = $this->cancel_user_subscription($user_id);

            if ($result) {
                add_settings_error('pfob_subscriptions', 'subscription_cancelled', 'Subscription cancelled successfully!', 'success');
            } else {
                add_settings_error('pfob_subscriptions', 'cancel_failed', 'Failed to cancel subscription.', 'error');
            }
        }

        // Reset usage
        if (isset($_POST['reset_usage']) && check_admin_referer('pfob_reset_usage')) {
            $user_id = intval($_POST['user_id']);

            $result = $this->reset_user_usage($user_id);

            if ($result) {
                add_settings_error('pfob_subscriptions', 'usage_reset', 'Usage statistics reset successfully!', 'success');
            } else {
                add_settings_error('pfob_subscriptions', 'reset_failed', 'Failed to reset usage.', 'error');
            }
        }
    }

    public function subscriptions_page() {
        // Handle viewing individual subscriber
        if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['user_id'])) {
            $this->view_subscriber_page(intval($_GET['user_id']));
            return;
        }

        // List all subscribers
        $this->list_subscribers_page();
    }

    private function list_subscribers_page() {
        global $wpdb;

        settings_errors('pfob_subscriptions');

        // Get all subscribers
        $subscribers = $wpdb->get_results(
            "SELECT s.*, u.user_email, u.display_name, u.user_registered
             FROM {$wpdb->prefix}pfob_subscriptions s
             LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
             ORDER BY s.created_at DESC"
        );

        // Get plan details
        $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';

        ?>
        <div class="wrap">
            <h1>Subscription Management</h1>

            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 4px; border: 1px solid #ccc;">
                <h2>All Subscribers (<?php echo count($subscribers); ?>)</h2>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>Next Billing</th>
                            <th>PayPal ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subscribers)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    No subscribers found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subscribers as $sub): ?>
                                <?php
                                $plan = $plans[$sub->plan_id] ?? null;
                                $plan_name = $plan ? $plan['name'] : $sub->plan_id;

                                $status_colors = array(
                                    'active' => '#46b450',
                                    'pending' => '#ffb900',
                                    'cancelled' => '#dc3232',
                                    'expired' => '#999'
                                );
                                $status_color = $status_colors[$sub->status] ?? '#999';
                                ?>
                                <tr>
                                    <td><?php echo $sub->id; ?></td>
                                    <td>
                                        <strong><?php echo esc_html($sub->display_name); ?></strong><br>
                                        <small><?php echo esc_html($sub->user_email); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($plan_name); ?></strong><br>
                                        <small>$<?php echo $plan ? number_format($plan['price'], 2) : '0.00'; ?>/mo</small>
                                    </td>
                                    <td>
                                        <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                            <?php echo strtoupper($sub->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $sub->created_at ? date('M j, Y', strtotime($sub->created_at)) : 'N/A'; ?></td>
                                    <td><?php echo $sub->current_period_end ? date('M j, Y', strtotime($sub->current_period_end)) : 'N/A'; ?></td>
                                    <td>
                                        <small style="font-family: monospace;">
                                            <?php echo $sub->paypal_subscription_id ? esc_html(substr($sub->paypal_subscription_id, 0, 20)) . '...' : 'None'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=projectfob-subscriptions&action=view&user_id=' . $sub->user_id); ?>"
                                           class="button button-small">Manage</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="background: #e7f3ff; border: 1px solid #2196F3; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <h3 style="margin-top: 0;">💡 Subscription Management Features</h3>
                <ul>
                    <li><strong>View Subscriber</strong> - See full details, usage, and history</li>
                    <li><strong>Change Plan</strong> - Upgrade or downgrade subscription plans</li>
                    <li><strong>Update Status</strong> - Activate, suspend, or cancel subscriptions</li>
                    <li><strong>Reset Usage</strong> - Reset monthly usage counters</li>
                    <li><strong>Manual Override</strong> - Bypass PayPal for testing or special cases</li>
                </ul>
            </div>
        </div>
        <?php
    }

    private function view_subscriber_page($user_id) {
        global $wpdb;

        $user = get_userdata($user_id);
        if (!$user) {
            wp_die('User not found.');
        }

        $subscription = PFOB_Subscription::get_by_user_id($user_id);
        if (!$subscription) {
            wp_die('No subscription found for this user.');
        }

        $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        $current_plan = $plans[$subscription->plan_id] ?? null;

        // Get usage statistics
        $usage = PFOB_Usage::get_all_current($user_id);

        // Get billing history
        $billing_history = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pfob_billing_history
             WHERE user_id = %d
             ORDER BY transaction_date DESC
             LIMIT 10",
            $user_id
        ));

        settings_errors('pfob_subscriptions');

        ?>
        <div class="wrap">
            <h1>
                <a href="<?php echo admin_url('admin.php?page=projectfob-subscriptions'); ?>" class="page-title-action">← Back</a>
                Manage Subscriber
            </h1>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
                <!-- Left Column -->
                <div>
                    <!-- User Info -->
                    <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ccc;">
                        <h2>User Information</h2>
                        <table class="form-table">
                            <tr>
                                <th>Name:</th>
                                <td><?php echo esc_html($user->display_name); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo esc_html($user->user_email); ?></td>
                            </tr>
                            <tr>
                                <th>Registered:</th>
                                <td><?php echo date('F j, Y', strtotime($user->user_registered)); ?></td>
                            </tr>
                            <tr>
                                <th>WordPress ID:</th>
                                <td><?php echo $user_id; ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Subscription Details -->
                    <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ccc;">
                        <h2>Subscription Details</h2>
                        <table class="form-table">
                            <tr>
                                <th>Current Plan:</th>
                                <td>
                                    <strong><?php echo $current_plan ? $current_plan['name'] : $subscription->plan_id; ?></strong>
                                    <br><small>$<?php echo $current_plan ? number_format($current_plan['price'], 2) : '0.00'; ?>/month</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <strong style="color: <?php echo $subscription->status === 'active' ? '#46b450' : '#dc3232'; ?>;">
                                        <?php echo strtoupper($subscription->status); ?>
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Start Date:</th>
                                <td><?php echo $subscription->created_at ? date('F j, Y', strtotime($subscription->created_at)) : 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Current Period:</th>
                                <td>
                                    <?php if ($subscription->current_period_start && $subscription->current_period_end): ?>
                                        <?php echo date('M j', strtotime($subscription->current_period_start)); ?> -
                                        <?php echo date('M j, Y', strtotime($subscription->current_period_end)); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>PayPal Subscription ID:</th>
                                <td>
                                    <code style="font-size: 11px;"><?php echo $subscription->paypal_subscription_id ?: 'None'; ?></code>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Usage Statistics -->
                    <?php if ($current_plan): ?>
                    <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ccc;">
                        <h2>Usage Statistics</h2>
                        <table class="form-table">
                            <tr>
                                <th>Projects:</th>
                                <td>
                                    <?php
                                    $project_usage = $usage['projects'] ?? 0;
                                    $project_limit = $current_plan['features']['projects'];
                                    $project_limit_text = $project_limit === 999999 ? 'Unlimited' : $project_limit;
                                    ?>
                                    <strong><?php echo $project_usage; ?></strong> / <?php echo $project_limit_text; ?>
                                    <?php if ($project_limit !== 999999): ?>
                                        <div style="background: #ddd; height: 10px; border-radius: 5px; margin-top: 5px;">
                                            <div style="background: #46b450; height: 10px; border-radius: 5px; width: <?php echo min(100, ($project_usage / $project_limit) * 100); ?>%;"></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Storage:</th>
                                <td>
                                    <?php
                                    $storage_usage = ($usage['storage_gb'] ?? 0);
                                    $storage_limit = $current_plan['features']['storage_gb'];
                                    $storage_limit_text = $storage_limit === 999999 ? 'Unlimited' : $storage_limit . ' GB';
                                    ?>
                                    <strong><?php echo number_format($storage_usage, 2); ?> GB</strong> / <?php echo $storage_limit_text; ?>
                                    <?php if ($storage_limit !== 999999): ?>
                                        <div style="background: #ddd; height: 10px; border-radius: 5px; margin-top: 5px;">
                                            <div style="background: #46b450; height: 10px; border-radius: 5px; width: <?php echo min(100, ($storage_usage / $storage_limit) * 100); ?>%;"></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- Billing History -->
                    <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ccc;">
                        <h2>Recent Billing History</h2>
                        <?php if (empty($billing_history)): ?>
                            <p>No billing history found.</p>
                        <?php else: ?>
                            <table class="wp-list-table widefat">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Transaction ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($billing_history as $bill): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($bill->transaction_date)); ?></td>
                                            <td><?php echo esc_html($bill->transaction_type); ?></td>
                                            <td>$<?php echo number_format($bill->amount, 2); ?></td>
                                            <td><?php echo esc_html($bill->status); ?></td>
                                            <td><small><?php echo esc_html($bill->transaction_id); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column - Actions -->
                <div>
                    <!-- Change Plan -->
                    <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ccc;">
                        <h3 style="margin-top: 0;">Change Plan</h3>
                        <form method="post">
                            <?php wp_nonce_field('pfob_change_plan'); ?>
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                            <select name="new_plan" class="widefat" style="margin-bottom: 10px;">
                                <?php foreach ($plans as $plan_id => $plan): ?>
                                    <option value="<?php echo esc_attr($plan_id); ?>" <?php selected($plan_id, $subscription->plan_id); ?>>
                                        <?php echo esc_html($plan['name']); ?> - $<?php echo number_format($plan['price'], 2); ?>/mo
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" name="change_plan" class="button button-primary widefat">
                                Change Plan
                            </button>
                        </form>
                    </div>

                    <!-- Update Status -->
                    <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ccc;">
                        <h3 style="margin-top: 0;">Update Status</h3>
                        <form method="post">
                            <?php wp_nonce_field('pfob_update_status'); ?>
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                            <select name="new_status" class="widefat" style="margin-bottom: 10px;">
                                <option value="active" <?php selected('active', $subscription->status); ?>>Active</option>
                                <option value="pending" <?php selected('pending', $subscription->status); ?>>Pending</option>
                                <option value="cancelled" <?php selected('cancelled', $subscription->status); ?>>Cancelled</option>
                                <option value="expired" <?php selected('expired', $subscription->status); ?>>Expired</option>
                            </select>

                            <button type="submit" name="update_status" class="button button-primary widefat">
                                Update Status
                            </button>
                        </form>
                    </div>

                    <!-- Reset Usage -->
                    <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ccc;">
                        <h3 style="margin-top: 0;">Reset Usage</h3>
                        <p style="font-size: 13px; color: #666;">
                            Reset monthly usage counters for this user.
                        </p>
                        <form method="post" onsubmit="return confirm('Reset all usage statistics for this user?');">
                            <?php wp_nonce_field('pfob_reset_usage'); ?>
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <button type="submit" name="reset_usage" class="button widefat">
                                Reset Usage
                            </button>
                        </form>
                    </div>

                    <!-- Cancel Subscription -->
                    <div style="background: #fef7f7; padding: 20px; border-radius: 4px; border: 1px solid #dc3232;">
                        <h3 style="margin-top: 0; color: #dc3232;">Danger Zone</h3>
                        <p style="font-size: 13px; color: #666;">
                            Cancel this subscription. This will also cancel in PayPal if connected.
                        </p>
                        <form method="post" onsubmit="return confirm('Cancel this subscription? This action cannot be undone!');">
                            <?php wp_nonce_field('pfob_cancel_subscription'); ?>
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <button type="submit" name="cancel_subscription" class="button button-secondary widefat" style="color: #dc3232;">
                                Cancel Subscription
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function change_user_plan($user_id, $new_plan_id) {
        $subscription = PFOB_Subscription::get_by_user_id($user_id);
        if (!$subscription) {
            return false;
        }

        $plans = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
        if (!isset($plans[$new_plan_id])) {
            return false;
        }

        // Update subscription
        $result = PFOB_Subscription::update($subscription->id, array(
            'plan_id' => $new_plan_id,
            'updated_at' => current_time('mysql')
        ));

        // Log activity
        if ($result) {
            PFOB_Database::insert('pfob_activities', array(
                'user_id' => $user_id,
                'action_type' => 'subscription.plan_changed',
                'subject_type' => 'subscription',
                'subject_id' => $subscription->id,
                'description' => "Plan changed to {$new_plan_id}",
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ));
        }

        return $result;
    }

    private function update_subscription_status($user_id, $new_status) {
        $subscription = PFOB_Subscription::get_by_user_id($user_id);
        if (!$subscription) {
            return false;
        }

        $result = PFOB_Subscription::update($subscription->id, array(
            'status' => $new_status,
            'updated_at' => current_time('mysql')
        ));

        // Log activity
        if ($result) {
            PFOB_Database::insert('pfob_activities', array(
                'user_id' => $user_id,
                'action_type' => 'subscription.status_changed',
                'subject_type' => 'subscription',
                'subject_id' => $subscription->id,
                'description' => "Status changed to {$new_status}",
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ));
        }

        return $result;
    }

    private function cancel_user_subscription($user_id) {
        $subscription = PFOB_Subscription::get_by_user_id($user_id);
        if (!$subscription) {
            return false;
        }

        // Cancel in PayPal if connected
        if ($subscription->paypal_subscription_id) {
            PFOB_PayPal_Service::cancel_subscription($subscription->paypal_subscription_id);
        }

        // Update local subscription
        $result = PFOB_Subscription::cancel($subscription->id);

        // Log activity
        if ($result) {
            PFOB_Database::insert('pfob_activities', array(
                'user_id' => $user_id,
                'action_type' => 'subscription.cancelled',
                'subject_type' => 'subscription',
                'subject_id' => $subscription->id,
                'description' => "Subscription cancelled by admin",
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ));
        }

        return $result;
    }

    private function reset_user_usage($user_id) {
        global $wpdb;

        $result = $wpdb->delete(
            $wpdb->prefix . 'pfob_usage_tracking',
            array('user_id' => $user_id),
            array('%d')
        );

        // Log activity
        if ($result !== false) {
            PFOB_Database::insert('pfob_activities', array(
                'user_id' => $user_id,
                'action_type' => 'subscription.usage_reset',
                'subject_type' => 'subscription',
                'subject_id' => $user_id,
                'description' => "Usage statistics reset by admin",
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ));
        }

        return $result !== false;
    }
}
