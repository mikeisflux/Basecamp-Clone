<?php
/**
 * Adminland - Account Management Dashboard
 *
 * Central hub for account owners and administrators to manage
 * their ProjectFOB account, users, settings, and billing.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

error_log( '[Adminland] Page load started' );

$user_id = get_current_user_id();
error_log( '[Adminland] User ID: ' . $user_id );

$user = wp_get_current_user();
error_log( '[Adminland] User display name: ' . $user->display_name );

// Only THE SUBSCRIBER (account owner who pays) can access this page
// Invited users should not be able to access adminland at all
$subscription = PFOB_Subscription::get_by_user_id( $user_id );
error_log( '[Adminland] Subscription retrieved: ' . ( $subscription ? 'Yes (ID: ' . $subscription->id . ')' : 'No' ) );

if ( ! $subscription || ! in_array( $subscription->status, array( 'active', 'trialing' ) ) ) {
    wp_die( __( 'Access denied. You must be the account subscriber to access Adminland.', 'projectfob' ) );
}

// Get subscription plan configuration
error_log( '[Adminland] Loading subscription plans config' );
$plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
error_log( '[Adminland] Subscription plan_id: ' . $subscription->plan_id );

$plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

if ( ! $plan ) {
    error_log( '[Adminland] WARNING: Invalid plan_id "' . $subscription->plan_id . '", using professional as fallback' );
    // Fallback to professional plan if plan_id is invalid
    $plan = $plans_config['professional'];
} else {
    error_log( '[Adminland] Plan loaded: ' . $plan['name'] );
}

// Check for add-ons (from subscription metadata) and get billing interval
error_log( '[Adminland] Checking add-ons from metadata' );
$metadata = array();
if ( ! empty( $subscription->metadata ) ) {
    // Metadata is already decoded as an array in get_by_user_id()
    if ( is_array( $subscription->metadata ) ) {
        $metadata = $subscription->metadata;
        error_log( '[Adminland] Metadata (already array): ' . print_r( $metadata, true ) );
    } else {
        // Fallback: if it's still a string, decode it
        error_log( '[Adminland] Metadata is string, decoding...' );
        $decoded = json_decode( $subscription->metadata, true );
        if ( is_array( $decoded ) ) {
            $metadata = $decoded;
            error_log( '[Adminland] Decoded metadata: ' . print_r( $metadata, true ) );
        } else {
            error_log( '[Adminland] WARNING: Failed to decode metadata JSON' );
        }
    }
}
$has_timesheet = isset( $metadata['addon_timesheet'] ) && $metadata['addon_timesheet'] === true;
$has_admin_pro = isset( $metadata['addon_admin_pro'] ) && $metadata['addon_admin_pro'] === true;
error_log( '[Adminland] Has Timesheet: ' . ( $has_timesheet ? 'Yes' : 'No' ) );
error_log( '[Adminland] Has Admin Pro: ' . ( $has_admin_pro ? 'Yes' : 'No' ) );

// Get billing interval from metadata (default to monthly for old subscriptions)
$billing_interval = $metadata['billing_interval'] ?? 'monthly';
error_log( '[Adminland] Billing interval: ' . $billing_interval );

// Calculate costs based on billing interval
$subscription_cost = 0.00;
$cost_label = '/month';
if ( $billing_interval === 'yearly' ) {
    $subscription_cost = $plan['yearly_price'] ?? $plan['monthly_price'] ?? 0.00;
    $cost_label = '/year';
} else {
    $subscription_cost = $plan['monthly_price'] ?? 0.00;
    $cost_label = '/month';
}

// Add-ons are always monthly
$monthly_cost = ( $billing_interval === 'yearly' ) ? ( $subscription_cost / 12 ) : $subscription_cost;
if ( $has_timesheet ) {
    $monthly_cost += 50;
}
if ( $has_admin_pro ) {
    $monthly_cost += 50;
}

// Next payment date
$next_payment_date = $subscription->current_period_end ? date( 'F j, Y', strtotime( $subscription->current_period_end ) ) : 'N/A';

// Get organization name
$organization = get_user_meta( $user_id, 'pfob_organization', true ) ?: get_bloginfo( 'name' );

error_log( '[Adminland] Calling template header' );
PFOB_Template::header( 'Adminland' );
error_log( '[Adminland] Template header loaded successfully' );
?>

<div class="admin-page-wrapper">
    <h1 class="admin-page-title">🔧 Adminland</h1>
    <p class="admin-page-subtitle">Manage your ProjectFOB account</p>

    <!-- Subscription Section -->
    <div class="admin-section">
        <h2 class="section-title">Your Subscription</h2>

        <div class="info-row">
            <strong>Plan:</strong> <?php echo esc_html( $plan['name'] ); ?> ($<?php echo esc_html( number_format( $subscription_cost, 2 ) ); ?><?php echo esc_html( $cost_label ); ?>)
        </div>

        <div class="info-row">
            <strong>Status:</strong> <span style="color: <?php echo $subscription->status === 'active' ? '#10b981' : '#f59e0b'; ?>;"><?php echo esc_html( ucfirst( $subscription->status ) ); ?></span>
        </div>

        <div class="info-row">
            <strong>Next payment:</strong> <?php echo esc_html( $next_payment_date ); ?>
        </div>

        <div class="info-row">
            <strong>Monthly cost:</strong> $<?php echo esc_html( number_format( $monthly_cost, 2 ) ); ?>
        </div>

        <?php if ( isset( $plan['features'] ) && is_array( $plan['features'] ) ) : ?>
        <div class="plan-features">
            <h3 class="features-title">Your Plan Includes:</h3>
            <div class="feature-item">Projects: <?php echo ( isset( $plan['features']['projects'] ) && $plan['features']['projects'] == 999999 ) ? 'Unlimited' : ( $plan['features']['projects'] ?? '0' ); ?></div>
            <div class="feature-item">Users: <?php echo ( isset( $plan['features']['users'] ) && $plan['features']['users'] == 999999 ) ? 'Unlimited' : ( $plan['features']['users'] ?? '0' ); ?></div>
            <div class="feature-item">Storage: <?php echo ( isset( $plan['features']['storage_gb'] ) && $plan['features']['storage_gb'] == 999999 ) ? 'Unlimited' : ( ( $plan['features']['storage_gb'] ?? '0' ) . ' GB' ); ?></div>
            <?php if ( ! empty( $plan['features']['google_calendar'] ) ) : ?>
            <div class="feature-item">Google Calendar Integration</div>
            <?php endif; ?>
            <?php if ( ! empty( $plan['features']['advanced_analytics'] ) ) : ?>
            <div class="feature-item">Advanced Analytics</div>
            <?php endif; ?>
            <?php if ( ! empty( $plan['features']['custom_branding'] ) ) : ?>
            <div class="feature-item">Custom Branding</div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Custom Branding -->
    <?php if ( ! empty( $plan['features']['custom_branding'] ) ) : ?>
    <div class="admin-section">
        <h2 class="section-title">🎨 Custom Branding</h2>
        <p style="margin-bottom: 20px;">Upload your company logo to personalize your workspace.</p>

        <?php
        $current_logo = get_user_meta( $user_id, 'pfob_company_logo', true );
        ?>

        <div class="logo-upload-section">
            <?php if ( $current_logo ) : ?>
                <div class="current-logo-preview">
                    <strong>Current Logo:</strong><br>
                    <img src="<?php echo esc_url( $current_logo ); ?>" alt="Company Logo" style="max-width: 300px; max-height: 100px; margin: 10px 0; display: block;">
                    <button class="button-secondary" id="remove-logo-btn">Remove Logo</button>
                </div>
            <?php endif; ?>

            <div class="logo-upload-form" style="margin-top: 20px;">
                <strong><?php echo $current_logo ? 'Update Logo:' : 'Upload Logo:'; ?></strong>
                <input type="file" id="logo-upload-input" accept="image/*" style="margin: 10px 0; display: block;">
                <button class="button-primary" id="upload-logo-btn">Upload Logo</button>
                <p style="color: #666; font-size: 13px; margin-top: 8px;">Recommended: PNG or SVG, max 200px height, transparent background works best</p>
            </div>

            <div id="logo-upload-status" style="margin-top: 10px;"></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Admin Capabilities -->
    <div class="admin-section">
        <h3 class="section-intro">You're an admin, so you can…</h3>

        <a href="<?php echo home_url( '/projectfob/people' ); ?>" class="action-link">Manage people</a>
        <a href="<?php echo home_url( '/projectfob/adminland/administrators' ); ?>" class="action-link">Add/remove administrators</a>
        <a href="<?php echo home_url( '/projectfob/adminland/invite-link' ); ?>" class="action-link">Invite coworkers with a link</a>
        <a href="<?php echo home_url( '/projectfob/adminland/groups' ); ?>" class="action-link">Manage groups</a>
        <a href="<?php echo home_url( '/projectfob/adminland/companies' ); ?>" class="action-link">Manage companies</a>
        <a href="#" class="action-link" data-action="rename-tools">Rename project tools</a>
        <a href="#" class="action-link" data-action="message-categories">Change message categories</a>
        <a href="#" class="action-link" data-action="merge-people">Merge people</a>
    </div>

    <!-- Account Owner Capabilities -->
    <div class="admin-section">
        <h2 class="section-title">Account Owners</h2>
        <h3 class="section-intro">You're an account owner, so you can…</h3>

        <a href="<?php echo home_url( '/projectfob/adminland/billing' ); ?>" class="action-link">
            <strong>Handle billing, invoices, packages, and upgrades</strong><br>
            <small style="color: #666; font-style: italic;">Your next payment: $<?php echo number_format( $monthly_cost, 0 ); ?> on <?php echo $next_payment_date; ?>.</small>
        </a>

        <a href="<?php echo home_url( '/projectfob/adminland/storage' ); ?>" class="action-link">Manage storage</a>
        <a href="#" class="action-link" data-action="manage-owners">Add/remove account owners</a>
        <a href="#" class="action-link" data-action="rename-account">Rename this account (<?php echo esc_html( $organization ); ?>)</a>
        <a href="<?php echo home_url( '/projectfob/adminland/trash' ); ?>" class="action-link">View everything in the trash</a>
        <a href="#" class="action-link" data-action="reassign-todos">Reassign someone's to-dos</a>
        <a href="<?php echo home_url( '/projectfob' ); ?>" class="action-link">Access any project</a>
        <a href="<?php echo home_url( '/projectfob/adminland/export' ); ?>" class="action-link">Export data from this account</a>
        <a href="<?php echo home_url( '/projectfob/adminland/public-items' ); ?>" class="action-link">Manage public items</a>
        <a href="#" class="action-link" data-action="pause-account">Pause or cancel this account</a>
    </div>

    <!-- Timesheet Features or Upgrade -->
    <?php if ( $has_timesheet ) : ?>
    <div class="admin-section addon-section">
        <h2 class="section-title">⏱️ Timesheet <span class="addon-badge">Active</span></h2>
        <p style="margin-bottom: 16px;">Track time spent on projects and generate detailed reports.</p>

        <a href="<?php echo home_url( '/projectfob/timesheet' ); ?>" class="action-link">View timesheet dashboard</a>
        <a href="<?php echo home_url( '/projectfob/timesheet/reports' ); ?>" class="action-link">Time tracking reports</a>
        <a href="<?php echo home_url( '/projectfob/timesheet/settings' ); ?>" class="action-link">Timesheet settings</a>
        <a href="<?php echo home_url( '/projectfob/timesheet/export' ); ?>" class="action-link">Export time data</a>
    </div>
    <?php else : ?>
    <div class="admin-section upgrade-section">
        <h2 class="section-title">Timesheet <span class="upgrade-tag">Upgrade</span></h2>
        <p>Give your team the power to track time spent on projects.</p>
        <a href="<?php echo home_url( '/projectfob/adminland/upgrades' ); ?>" class="upgrade-button">Check out Timesheet</a>
    </div>
    <?php endif; ?>

    <!-- Admin Pro Pack Features or Upgrade -->
    <?php if ( $has_admin_pro ) : ?>
    <div class="admin-section addon-section">
        <h2 class="section-title">🔐 Admin Pro Pack <span class="addon-badge">Active</span></h2>
        <p style="margin-bottom: 16px;">Advanced permissions, access controls, and administrative features.</p>

        <a href="<?php echo home_url( '/projectfob/admin-pro/permissions' ); ?>" class="action-link">Advanced permissions settings</a>
        <a href="<?php echo home_url( '/projectfob/admin-pro/access-logs' ); ?>" class="action-link">User access logs</a>
        <a href="<?php echo home_url( '/projectfob/admin-pro/approval-workflows' ); ?>" class="action-link">Approval workflows</a>
        <a href="<?php echo home_url( '/projectfob/admin-pro/custom-roles' ); ?>" class="action-link">Custom user roles</a>
    </div>
    <?php else : ?>
    <div class="admin-section upgrade-section">
        <h2 class="section-title">Admin Pro Pack <span class="upgrade-tag">Upgrade</span></h2>
        <p>The Admin Pro Pack is an upgrade for your account that gives you more control over permissions and access.</p>
        <a href="<?php echo home_url( '/projectfob/adminland/upgrades' ); ?>" class="upgrade-button">Check out the Admin Pro Pack</a>
    </div>
    <?php endif; ?>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.admin-page-wrapper {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.admin-page-title {
    font-size: 32px;
    margin: 0 0 8px 0;
    color: #333;
}

.admin-page-subtitle {
    font-size: 16px;
    color: #666;
    margin: 0 0 32px 0;
}

.admin-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 24px;
    margin: 0 0 16px 0;
    color: #333;
}

.section-intro {
    font-size: 16px;
    margin: 0 0 16px 0;
    color: #444;
    font-weight: normal;
}

.info-row {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 15px;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row strong {
    display: inline-block;
    min-width: 120px;
    color: #666;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.plan-features {
    margin-top: 20px;
    background: #f8f9fa;
    padding: 16px;
    border-radius: 6px;
}

.features-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: #333;
}

.feature-item {
    padding: 6px 0;
    font-size: 14px;
    color: #666;
}

.action-link {
    display: block;
    padding: 12px 0;
    color: #0066cc;
    text-decoration: none;
    font-size: 15px;
    line-height: 1.6;
    border-bottom: 1px solid #f0f0f0;
}

.action-link:last-child {
    border-bottom: none;
}

.action-link:hover {
    text-decoration: underline;
    background: #f8f9fa;
    padding-left: 8px;
    margin-left: -8px;
}

.addon-section {
    background: #ecfdf5;
    border-color: #a7f3d0;
    border-left: 4px solid #10b981;
}

.addon-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #065f46;
    background: #d1fae5;
    padding: 3px 8px;
    border-radius: 3px;
    margin-left: 6px;
}

.upgrade-section {
    background: #fffbeb;
    border-color: #fde68a;
    border-left: 4px solid #f59e0b;
}

.upgrade-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #f59e0b;
    background: #fef3c7;
    padding: 3px 8px;
    border-radius: 3px;
    margin-left: 6px;
}

.upgrade-button {
    display: inline-block;
    padding: 10px 20px;
    background: #0066cc;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 500;
    margin-top: 12px;
}

.upgrade-button:hover {
    background: #0052a3;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle capability card clicks
    document.querySelectorAll('.action-link[data-action]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            handleAdminAction(action);
        });
    });

    async function handleAdminAction(action) {
        // Route to appropriate handler based on action
        switch(action) {
            case 'manage-administrators':
                showManageAdministrators();
                break;
            case 'invite-link':
                showInviteLinkGenerator();
                break;
            case 'manage-groups':
                showManageGroups();
                break;
            case 'manage-companies':
                showManageCompanies();
                break;
            case 'rename-tools':
                showRenameTools();
                break;
            case 'message-categories':
                showMessageCategories();
                break;
            case 'move-projects':
                showMoveProjects();
                break;
            case 'merge-people':
                showMergePeople();
                break;
            case 'manage-storage':
                showStorageManagement();
                break;
            case 'manage-owners':
                showManageOwners();
                break;
            case 'rename-account':
                showRenameAccount();
                break;
            case 'view-trash':
                window.location.href = '<?php echo home_url( "/projectfob/trash" ); ?>';
                break;
            case 'reassign-todos':
                showReassignTodos();
                break;
            case 'access-projects':
                window.location.href = '<?php echo home_url( "/projectfob/" ); ?>';
                break;
            case 'export-data':
                showDataExport();
                break;
            case 'manage-public':
                showManagePublic();
                break;
            case 'pause-account':
                showPauseAccount();
                break;
        }
    }

    function showModal(title, content) {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';

        const modalContent = document.createElement('div');
        modalContent.style.cssText = 'background: white; padding: 32px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;';
        modalContent.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">${title}</h2>
                <button class="modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <div>${content}</div>
        `;

        modal.appendChild(modalContent);
        document.body.appendChild(modal);

        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.remove();
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    // Implementation of each admin function
    function showManageAdministrators() {
        showModal('Manage Administrators', '<p>Manage who has administrator access to your account.</p>');
    }

    function showInviteLinkGenerator() {
        showModal('Invite with Link', '<p>Generate a special link to invite multiple team members at once.</p>');
    }

    function showManageGroups() {
        showModal('Manage Groups', '<p>Create and manage groups to organize people in your account.</p>');
    }

    function showManageCompanies() {
        showModal('Manage Companies', '<p>Manage external companies and organizations.</p>');
    }

    function showRenameTools() {
        showModal('Rename Project Tools', '<p>Customize the names of project tools to match your workflow.</p>');
    }

    function showMessageCategories() {
        showModal('Message Categories', '<p>Customize message categories for your account.</p>');
    }

    function showMoveProjects() {
        showModal('Move Projects', '<p>Move projects from Basecamp 2 to Basecamp 4.</p>');
    }

    function showMergePeople() {
        showModal('Merge People', '<p>Merge duplicate user accounts into a single account.</p>');
    }

    function showStorageManagement() {
        showModal('Manage Storage', '<p>Manage your account\'s storage allocation and usage.</p>');
    }

    function showManageOwners() {
        showModal('Manage Account Owners', `
            <p style="margin-bottom: 20px; color: #666;">Add or remove account owners. Account owners have full access to billing and account management.</p>

            <div style="margin-bottom: 24px;">
                <h3 style="font-size: 16px; margin-bottom: 12px;">Current Account Owners</h3>
                <div id="owners-list" style="border: 1px solid #ddd; border-radius: 4px; padding: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background: #f8f9fa; border-radius: 4px; margin-bottom: 8px;">
                        <div>
                            <div style="font-weight: 600;"><?php echo esc_js( $user->display_name ); ?></div>
                            <div style="font-size: 13px; color: #666;"><?php echo esc_js( $user->user_email ); ?></div>
                        </div>
                        <span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;">Primary</span>
                    </div>
                    <div id="additional-owners"></div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 16px; margin-bottom: 12px;">Add Account Owner</h3>
                <div style="display: flex; gap: 8px;">
                    <input type="email" id="new-owner-email" placeholder="Enter email address"
                           style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <button onclick="addAccountOwner()"
                            style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap;">
                        Add Owner
                    </button>
                </div>
                <div id="add-owner-message" style="margin-top: 8px; font-size: 14px;"></div>
            </div>
        `);

        loadAccountOwners();
    }

    function loadAccountOwners() {
        // This would normally fetch from the API
        // For now, show placeholder
        const container = document.getElementById('additional-owners');
        if (container) {
            container.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">No additional owners yet</div>';
        }
    }

    function addAccountOwner() {
        const emailInput = document.getElementById('new-owner-email');
        const messageDiv = document.getElementById('add-owner-message');
        const email = emailInput.value.trim();

        if (!email) {
            messageDiv.style.color = '#dc3545';
            messageDiv.textContent = 'Please enter an email address';
            return;
        }

        if (!email.includes('@')) {
            messageDiv.style.color = '#dc3545';
            messageDiv.textContent = 'Please enter a valid email address';
            return;
        }

        messageDiv.style.color = '#0066cc';
        messageDiv.textContent = 'Sending invitation...';

        // Simulate API call
        setTimeout(() => {
            messageDiv.style.color = '#10b981';
            messageDiv.textContent = '✓ Invitation sent to ' + email;
            emailInput.value = '';

            setTimeout(() => {
                messageDiv.textContent = '';
            }, 3000);
        }, 500);
    }

    function showRenameAccount() {
        showModal('Rename Account', `
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Account name</label>
                <input type="text" value="<?php echo esc_js( $organization ); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <button style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer;">Save Changes</button>
        `);
    }

    function showReassignTodos() {
        showModal('Reassign To-dos', `
            <p style="margin-bottom: 20px; color: #666;">Reassign all to-dos from one person to another. This will update all incomplete to-dos assigned to the selected person.</p>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">From (current assignee)</label>
                <select id="reassign-from" onchange="updateTodoCount()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Select a person...</option>
                    <option value="<?php echo esc_js( $user_id ); ?>"><?php echo esc_js( $user->display_name ); ?> (You)</option>
                    <option value="2">John Smith</option>
                    <option value="3">Jane Doe</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">To (new assignee)</label>
                <select id="reassign-to" onchange="updateTodoCount()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Select a person...</option>
                    <option value="<?php echo esc_js( $user_id ); ?>"><?php echo esc_js( $user->display_name ); ?> (You)</option>
                    <option value="2">John Smith</option>
                    <option value="3">Jane Doe</option>
                </select>
            </div>

            <div id="todo-count-info" style="background: #f8f9fa; padding: 16px; border-radius: 4px; margin-bottom: 20px; display: none;">
                <div style="font-size: 14px; color: #666; margin-bottom: 4px;">To-dos to be reassigned:</div>
                <div style="font-size: 24px; font-weight: 700; color: #0066cc;"><span id="todo-count">0</span> to-dos</div>
            </div>

            <div id="reassign-message" style="margin-bottom: 16px; padding: 12px; border-radius: 4px; display: none;"></div>

            <div style="display: flex; gap: 12px;">
                <button onclick="executeReassign()" id="reassign-button" disabled
                        style="flex: 1; padding: 12px 24px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                    Reassign To-dos
                </button>
                <button onclick="document.querySelector('.modal-close').click()"
                        style="padding: 12px 24px; background: #e5e7eb; color: #333; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        `);
    }

    function updateTodoCount() {
        const fromSelect = document.getElementById('reassign-from');
        const toSelect = document.getElementById('reassign-to');
        const countInfo = document.getElementById('todo-count-info');
        const countSpan = document.getElementById('todo-count');
        const button = document.getElementById('reassign-button');
        const messageDiv = document.getElementById('reassign-message');

        messageDiv.style.display = 'none';

        if (fromSelect.value && toSelect.value) {
            if (fromSelect.value === toSelect.value) {
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#fee2e2';
                messageDiv.style.color = '#dc3545';
                messageDiv.textContent = '⚠️ Cannot reassign to the same person';
                countInfo.style.display = 'none';
                button.disabled = true;
                button.style.opacity = '0.5';
                button.style.cursor = 'not-allowed';
                return;
            }

            // Simulate fetching count (would be API call)
            const mockCount = Math.floor(Math.random() * 20) + 5;
            countSpan.textContent = mockCount;
            countInfo.style.display = 'block';
            button.disabled = false;
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
        } else {
            countInfo.style.display = 'none';
            button.disabled = true;
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
        }
    }

    function executeReassign() {
        const fromSelect = document.getElementById('reassign-from');
        const toSelect = document.getElementById('reassign-to');
        const countSpan = document.getElementById('todo-count');
        const messageDiv = document.getElementById('reassign-message');
        const button = document.getElementById('reassign-button');

        const fromName = fromSelect.options[fromSelect.selectedIndex].text;
        const toName = toSelect.options[toSelect.selectedIndex].text;
        const count = countSpan.textContent;

        button.disabled = true;
        button.textContent = 'Reassigning...';

        // Simulate API call
        setTimeout(() => {
            messageDiv.style.display = 'block';
            messageDiv.style.background = '#d1fae5';
            messageDiv.style.color = '#10b981';
            messageDiv.innerHTML = `✓ Successfully reassigned ${count} to-dos from <strong>${fromName}</strong> to <strong>${toName}</strong>`;

            button.textContent = 'Done!';

            setTimeout(() => {
                document.querySelector('.modal-close').click();
            }, 2000);
        }, 1000);
    }

    function showDataExport() {
        showModal('Export Data', '<p>Export all data from your account in various formats.</p>');
    }

    function showManagePublic() {
        showModal('Manage Public Items', '<p>Manage publicly accessible items in your account.</p>');
    }

    function showPauseAccount() {
        showModal('Pause or Cancel Account', '<p><strong>Warning:</strong> Pausing will stop billing but you won\'t be able to access your account until you resume.</p>');
    }

    // Logo upload functionality
    const uploadLogoBtn = document.getElementById('upload-logo-btn');
    const removeLogoBtn = document.getElementById('remove-logo-btn');
    const logoInput = document.getElementById('logo-upload-input');
    const statusDiv = document.getElementById('logo-upload-status');

    if (uploadLogoBtn) {
        uploadLogoBtn.addEventListener('click', async function() {
            const file = logoInput.files[0];
            if (!file) {
                statusDiv.innerHTML = '<p style="color: #dc3232;">Please select a file first</p>';
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                statusDiv.innerHTML = '<p style="color: #dc3232;">Please select an image file</p>';
                return;
            }

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                statusDiv.innerHTML = '<p style="color: #dc3232;">File size must be less than 2MB</p>';
                return;
            }

            statusDiv.innerHTML = '<p style="color: #0066cc;">Uploading...</p>';
            uploadLogoBtn.disabled = true;

            const formData = new FormData();
            formData.append('logo', file);

            try {
                const response = await fetch('/wp-json/projectfob/v1/settings/upload-logo', {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    statusDiv.innerHTML = '<p style="color: #46b450;">✓ Logo uploaded successfully! Refreshing...</p>';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    statusDiv.innerHTML = '<p style="color: #dc3232;">Error: ' + (result.message || 'Upload failed') + '</p>';
                    uploadLogoBtn.disabled = false;
                }
            } catch (error) {
                statusDiv.innerHTML = '<p style="color: #dc3232;">Error uploading logo. Please try again.</p>';
                uploadLogoBtn.disabled = false;
            }
        });
    }

    if (removeLogoBtn) {
        removeLogoBtn.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to remove your company logo?')) {
                return;
            }

            statusDiv.innerHTML = '<p style="color: #0066cc;">Removing...</p>';
            removeLogoBtn.disabled = true;

            try {
                const response = await fetch('/wp-json/projectfob/v1/settings/remove-logo', {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    statusDiv.innerHTML = '<p style="color: #46b450;">✓ Logo removed successfully! Refreshing...</p>';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    statusDiv.innerHTML = '<p style="color: #dc3232;">Error: ' + (result.message || 'Failed to remove logo') + '</p>';
                    removeLogoBtn.disabled = false;
                }
            } catch (error) {
                statusDiv.innerHTML = '<p style="color: #dc3232;">Error removing logo. Please try again.</p>';
                removeLogoBtn.disabled = false;
            }
        });
    }
});
</script>

<?php
error_log( '[Adminland] Rendering complete, calling footer' );
PFOB_Template::footer();
error_log( '[Adminland] Page fully rendered' );
