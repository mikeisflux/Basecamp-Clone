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

// Calculate costs
$monthly_cost = $plan['price'];
$next_payment_date = $subscription->current_period_end ? date( 'F j, Y', strtotime( $subscription->current_period_end ) ) : 'N/A';

// Check for add-ons (from subscription metadata)
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

if ( $has_timesheet ) {
    $monthly_cost += 50;
}
if ( $has_admin_pro ) {
    $monthly_cost += 50;
}

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
            <strong>Plan:</strong> <?php echo esc_html( $plan['name'] ); ?> ($<?php echo esc_html( $plan['price'] ); ?>/month)
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

    <!-- Admin Capabilities -->
    <div class="admin-section">
        <h3 class="section-intro">You're an admin, so you can…</h3>

        <a href="<?php echo home_url( '/projectfob/people' ); ?>" class="action-link">Manage people</a>
        <a href="#" class="action-link" data-action="manage-administrators">Add/remove administrators</a>
        <a href="#" class="action-link" data-action="invite-link">Invite coworkers with a link</a>
        <a href="#" class="action-link" data-action="manage-groups">Manage groups</a>
        <a href="#" class="action-link" data-action="manage-companies">Manage companies</a>
        <a href="#" class="action-link" data-action="rename-tools">Rename project tools</a>
        <a href="#" class="action-link" data-action="message-categories">Change message categories</a>
        <a href="#" class="action-link" data-action="move-projects">Move projects from Basecamp 2 to Basecamp 4</a>
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

        <a href="#" class="action-link" data-action="manage-storage">Manage storage</a>
        <a href="#" class="action-link" data-action="manage-owners">Add/remove account owners</a>
        <a href="#" class="action-link" data-action="rename-account">Rename this account (<?php echo esc_html( $organization ); ?>)</a>
        <a href="#" class="action-link" data-action="view-trash">View everything in the trash</a>
        <a href="#" class="action-link" data-action="reassign-todos">Reassign someone's to-dos</a>
        <a href="#" class="action-link" data-action="access-projects">Access any project</a>
        <a href="#" class="action-link" data-action="export-data">Export data from this account</a>
        <a href="#" class="action-link" data-action="manage-public">Manage public items</a>
        <a href="#" class="action-link" data-action="pause-account">Pause or cancel this account</a>
    </div>

    <!-- Timesheet Upgrade -->
    <?php if ( ! $has_timesheet ) : ?>
    <div class="admin-section upgrade-section">
        <h2 class="section-title">Timesheet <span class="upgrade-tag">Upgrade</span></h2>
        <p>Give your team the power to track time spent on projects.</p>
        <a href="<?php echo home_url( '/projectfob/adminland/upgrades' ); ?>" class="upgrade-button">Check out Timesheet</a>
    </div>
    <?php endif; ?>

    <!-- Admin Pro Pack Upgrade -->
    <?php if ( ! $has_admin_pro ) : ?>
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
        showModal('Manage Account Owners', '<p>Add or remove account owners. Account owners have full access to billing and account management.</p>');
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
        showModal('Reassign To-dos', '<p>Reassign all to-dos from one person to another.</p>');
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
});
</script>

<?php
error_log( '[Adminland] Rendering complete, calling footer' );
PFOB_Template::footer();
error_log( '[Adminland] Page fully rendered' );
