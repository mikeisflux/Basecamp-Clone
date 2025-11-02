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

// Get all invited users (people the subscriber has invited to their account)
error_log( '[Adminland] Fetching invited users' );
$invited_users = get_users( array(
    'meta_query' => array(
        array(
            'key'   => 'pfob_account_owner',
            'value' => $user_id,
        ),
    ),
) );
error_log( '[Adminland] Found ' . count( $invited_users ) . ' invited users' );

error_log( '[Adminland] Calling template header' );
PFOB_Template::header( 'Adminland' );
error_log( '[Adminland] Template header loaded successfully' );
?>

<div class="pfob-container pfob-adminland">
    <div class="pfob-page-header">
        <h1>🔧 Adminland</h1>
        <p>Manage your ProjectFOB account</p>
    </div>

    <!-- Subscription Information -->
    <div class="pfob-adminland-section pfob-subscription-info">
        <h2>Your Subscription</h2>
        <div class="pfob-subscription-details">
            <div class="pfob-detail-item">
                <span class="pfob-label">Plan:</span>
                <span class="pfob-value"><?php echo esc_html( $plan['name'] ); ?> ($<?php echo esc_html( $plan['price'] ); ?>/month)</span>
            </div>
            <div class="pfob-detail-item">
                <span class="pfob-label">Status:</span>
                <span class="pfob-value pfob-status-<?php echo esc_attr( $subscription->status ); ?>"><?php echo esc_html( ucfirst( $subscription->status ) ); ?></span>
            </div>
            <div class="pfob-detail-item">
                <span class="pfob-label">Next payment:</span>
                <span class="pfob-value"><?php echo esc_html( $next_payment_date ); ?></span>
            </div>
            <div class="pfob-detail-item">
                <span class="pfob-label">Monthly cost:</span>
                <span class="pfob-value">$<?php echo esc_html( number_format( $monthly_cost, 2 ) ); ?></span>
            </div>
        </div>

        <?php if ( isset( $plan['features'] ) && is_array( $plan['features'] ) ) : ?>
        <div class="pfob-plan-limits">
            <h3>Your Plan Includes:</h3>
            <ul>
                <li>Projects: <?php echo ( isset( $plan['features']['projects'] ) && $plan['features']['projects'] == 999999 ) ? 'Unlimited' : ( $plan['features']['projects'] ?? '0' ); ?></li>
                <li>Users: <?php echo ( isset( $plan['features']['users'] ) && $plan['features']['users'] == 999999 ) ? 'Unlimited' : ( $plan['features']['users'] ?? '0' ); ?></li>
                <li>Storage: <?php echo ( isset( $plan['features']['storage_gb'] ) && $plan['features']['storage_gb'] == 999999 ) ? 'Unlimited' : ( ( $plan['features']['storage_gb'] ?? '0' ) . ' GB' ); ?></li>
                <?php if ( ! empty( $plan['features']['google_calendar'] ) ) : ?>
                <li>Google Calendar Integration</li>
                <?php endif; ?>
                <?php if ( ! empty( $plan['features']['advanced_analytics'] ) ) : ?>
                <li>Advanced Analytics</li>
                <?php endif; ?>
                <?php if ( ! empty( $plan['features']['custom_branding'] ) ) : ?>
                <li>Custom Branding</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <!-- Administration Section -->
    <div class="pfob-adminland-section">
        <h3 class="pfob-section-intro">You're an admin, so you can…</h3>

        <div class="pfob-capabilities-list">
            <a href="<?php echo home_url( '/projectfob/people' ); ?>" class="pfob-capability-card">
                <span class="pfob-label">Manage people</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="manage-administrators">
                <span class="pfob-label">Add/remove administrators</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="invite-link">
                <span class="pfob-label">Invite coworkers with a link</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="manage-groups">
                <span class="pfob-label">Manage groups</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="manage-companies">
                <span class="pfob-label">Manage companies</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="rename-tools">
                <span class="pfob-label">Rename project tools</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="message-categories">
                <span class="pfob-label">Change message categories</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="move-projects">
                <span class="pfob-label">Move projects from Basecamp 2 to Basecamp 4</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="merge-people">
                <span class="pfob-label">Merge people</span>
            </a>
        </div>
    </div>

    <!-- Account Owners Section -->
    <div class="pfob-adminland-section">
        <h2>Account Owners</h2>
        <h3 class="pfob-section-intro">You're an account owner, so you can…</h3>

        <div class="pfob-capabilities-list">
            <a href="<?php echo home_url( '/projectfob/adminland/billing' ); ?>" class="pfob-capability-card pfob-billing-card">
                <div>
                    <div class="pfob-label">Handle billing, invoices, packages, and upgrades</div>
                    <div class="pfob-sublabel">Your next payment: $<?php echo number_format( $monthly_cost, 0 ); ?> on <?php echo $next_payment_date; ?>.</div>
                </div>
            </a>
            <a href="#" class="pfob-capability-card" data-action="manage-storage">
                <span class="pfob-label">Manage storage</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="manage-owners">
                <span class="pfob-label">Add/remove account owners</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="rename-account">
                <span class="pfob-label">Rename this account (<?php echo esc_html( $organization ); ?>)</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="view-trash">
                <span class="pfob-label">View everything in the trash</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="reassign-todos">
                <span class="pfob-label">Reassign someone's to-dos</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="access-projects">
                <span class="pfob-label">Access any project</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="export-data">
                <span class="pfob-label">Export data from this account</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="manage-public">
                <span class="pfob-label">Manage public items</span>
            </a>
            <a href="#" class="pfob-capability-card" data-action="pause-account">
                <span class="pfob-label">Pause or cancel this account</span>
            </a>
        </div>
    </div>

    <!-- Timesheet Upgrade -->
    <?php if ( ! $has_timesheet ) : ?>
    <div class="pfob-adminland-section pfob-upgrade-section">
        <h2>Timesheet <span class="pfob-upgrade-badge">Upgrade</span></h2>
        <p>Give your team the power to track time spent on projects.</p>
        <a href="<?php echo home_url( '/projectfob/adminland/upgrades' ); ?>" class="pfob-btn pfob-btn-secondary">Check out Timesheet</a>
    </div>
    <?php endif; ?>

    <!-- Admin Pro Pack Upgrade -->
    <?php if ( ! $has_admin_pro ) : ?>
    <div class="pfob-adminland-section pfob-upgrade-section">
        <h2>Admin Pro Pack <span class="pfob-upgrade-badge">Upgrade</span></h2>
        <p>The Admin Pro Pack is an upgrade for your account that gives you more control over permissions and access.</p>
        <a href="<?php echo home_url( '/projectfob/adminland/upgrades' ); ?>" class="pfob-btn pfob-btn-secondary">Check out the Admin Pro Pack</a>
    </div>
    <?php endif; ?>
</div>

<style>
.pfob-adminland {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.pfob-page-header {
    margin-bottom: 32px;
}

.pfob-page-header h1 {
    margin: 0 0 8px 0;
    font-size: 32px;
    color: #333;
}

.pfob-page-header p {
    margin: 0;
    font-size: 16px;
    color: #666;
}

.pfob-subscription-details {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-bottom: 24px;
}

.pfob-detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.pfob-detail-item .pfob-label {
    font-size: 12px;
    text-transform: uppercase;
    color: #999;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.pfob-detail-item .pfob-value {
    font-size: 18px;
    color: #333;
    font-weight: 500;
}

.pfob-status-active {
    color: #10b981;
}

.pfob-status-trialing {
    color: #f59e0b;
}

.pfob-status-inactive {
    color: #ef4444;
}

.pfob-plan-limits {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    margin-top: 20px;
}

.pfob-plan-limits h3 {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: #333;
}

.pfob-plan-limits ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pfob-plan-limits li {
    padding: 8px 0;
    color: #666;
    font-size: 14px;
    border-bottom: 1px solid #f0f0f0;
}

.pfob-plan-limits li:last-child {
    border-bottom: none;
}

.pfob-empty-state {
    color: #999;
    font-style: italic;
}

.pfob-empty-state a {
    color: #0066cc;
    text-decoration: none;
}

.pfob-empty-state a:hover {
    text-decoration: underline;
}

.pfob-upgrades-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 24px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.pfob-upgrades-banner h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
}

.pfob-upgrades-banner p {
    margin: 0;
    opacity: 0.9;
}

.pfob-adminland-section {
    background: white;
    padding: 32px;
    border-radius: 8px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pfob-owner-section {
    border-left: 4px solid #ffd700;
}

.pfob-adminland-section h2 {
    margin: 0 0 20px 0;
    font-size: 24px;
    color: #333;
}

.pfob-users-grid {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 30px;
    align-items: center;
}

.pfob-user-avatar {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pfob-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #6b46c1;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.pfob-avatar.owner {
    background: linear-gradient(135deg, #ffd700 0%, #ffaa00 100%);
    color: #333;
}

.pfob-section-intro {
    margin: 0 0 12px 0;
    font-size: 15px;
    color: #444;
    font-weight: normal;
}

.pfob-capabilities-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pfob-capability-card {
    display: block;
    padding: 10px 0;
    text-decoration: none;
    color: #0066cc;
    font-size: 15px;
    line-height: 1.5;
}

.pfob-capability-card:hover {
    text-decoration: underline;
}

.pfob-capability-card .pfob-label {
    color: #0066cc;
}

.pfob-billing-card .pfob-label {
    color: #0066cc;
    display: block;
    margin-bottom: 4px;
}

.pfob-billing-card .pfob-sublabel {
    font-size: 14px;
    color: #666;
    font-style: italic;
}

.pfob-upgrade-section {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-left: 4px solid #f59e0b;
}

.pfob-upgrade-section h2 {
    margin: 0 0 8px 0;
}

.pfob-upgrade-section p {
    color: #666;
    margin: 0 0 16px 0;
}

.pfob-upgrade-badge {
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

.pfob-btn-secondary {
    display: inline-block;
    padding: 10px 20px;
    background: #0066cc;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 500;
}

.pfob-btn-secondary:hover {
    background: #0052a3;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle capability card clicks
    document.querySelectorAll('.pfob-capability-card[data-action]').forEach(card => {
        card.addEventListener('click', function(e) {
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
        modal.className = 'pfob-modal';
        modal.innerHTML = `
            <div class="pfob-modal-content">
                <div class="pfob-modal-header">
                    <h2>${title}</h2>
                    <button class="pfob-modal-close">&times;</button>
                </div>
                <div class="pfob-modal-body">
                    ${content}
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        modal.querySelector('.pfob-modal-close').addEventListener('click', () => {
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
        const content = `
            <p>Manage who has administrator access to your account.</p>
            <div id="admins-list"></div>
            <button class="pfob-btn pfob-btn-primary" id="add-admin-btn">Add Administrator</button>
        `;
        showModal('Manage Administrators', content);
    }

    function showInviteLinkGenerator() {
        const content = `
            <p>Generate a special link to invite multiple team members at once.</p>
            <button class="pfob-btn pfob-btn-primary" id="generate-link-btn">Generate Invite Link</button>
            <div id="invite-link-display" style="margin-top: 20px; display: none;">
                <input type="text" id="invite-link" readonly style="width: 100%; padding: 10px;">
                <button class="pfob-btn pfob-btn-secondary" onclick="navigator.clipboard.writeText(document.getElementById('invite-link').value)">Copy Link</button>
            </div>
        `;
        showModal('Invite with Link', content);
    }

    function showManageGroups() {
        const content = `
            <p>Create and manage groups to organize people in your account.</p>
            <button class="pfob-btn pfob-btn-primary">Create New Group</button>
        `;
        showModal('Manage Groups', content);
    }

    function showManageCompanies() {
        const content = `
            <p>Manage external companies and organizations.</p>
            <button class="pfob-btn pfob-btn-primary">Add Company</button>
        `;
        showModal('Manage Companies', content);
    }

    function showRenameTools() {
        const content = `
            <p>Customize the names of project tools to match your workflow.</p>
            <div class="pfob-form-group">
                <label>Messages board</label>
                <input type="text" value="Messages" class="pfob-input">
            </div>
            <div class="pfob-form-group">
                <label>To-dos</label>
                <input type="text" value="To-dos" class="pfob-input">
            </div>
            <button class="pfob-btn pfob-btn-primary">Save Changes</button>
        `;
        showModal('Rename Project Tools', content);
    }

    function showMessageCategories() {
        const content = `
            <p>Customize message categories for your account.</p>
            <button class="pfob-btn pfob-btn-primary">Add Category</button>
        `;
        showModal('Message Categories', content);
    }

    function showMoveProjects() {
        const content = `
            <p>Move projects from Basecamp 2 to Basecamp 4.</p>
            <div class="pfob-form-group">
                <label>Select projects to migrate</label>
                <p style="color: #666; font-size: 14px;">Connect your Basecamp 2 account to view available projects.</p>
            </div>
            <button class="pfob-btn pfob-btn-primary">Connect Basecamp 2</button>
        `;
        showModal('Move Projects', content);
    }

    function showMergePeople() {
        const content = `
            <p>Merge duplicate user accounts into a single account.</p>
            <div class="pfob-form-group">
                <label>Select primary account</label>
                <select class="pfob-input"><option>Choose...</option></select>
            </div>
            <div class="pfob-form-group">
                <label>Select account to merge</label>
                <select class="pfob-input"><option>Choose...</option></select>
            </div>
            <button class="pfob-btn pfob-btn-primary">Merge Accounts</button>
        `;
        showModal('Merge People', content);
    }

    function showStorageManagement() {
        const content = `
            <h3>Storage Usage</h3>
            <p>Manage your account's storage allocation and usage.</p>
            <div class="pfob-storage-stats">
                <p>Loading storage information...</p>
            </div>
        `;
        showModal('Manage Storage', content);
    }

    function showManageOwners() {
        const content = `
            <p>Add or remove account owners. Account owners have full access to billing and account management.</p>
            <div id="owners-list"></div>
            <button class="pfob-btn pfob-btn-primary">Add Owner</button>
        `;
        showModal('Manage Account Owners', content);
    }

    function showRenameAccount() {
        const content = `
            <div class="pfob-form-group">
                <label>Account name</label>
                <input type="text" value="<?php echo esc_js( $organization ); ?>" id="account-name" class="pfob-input">
            </div>
            <button class="pfob-btn pfob-btn-primary" id="save-account-name">Save Changes</button>
        `;
        showModal('Rename Account', content);
    }

    function showReassignTodos() {
        const content = `
            <p>Reassign all to-dos from one person to another.</p>
            <div class="pfob-form-group">
                <label>From</label>
                <select class="pfob-input"><option>Choose person...</option></select>
            </div>
            <div class="pfob-form-group">
                <label>To</label>
                <select class="pfob-input"><option>Choose person...</option></select>
            </div>
            <button class="pfob-btn pfob-btn-primary">Reassign To-dos</button>
        `;
        showModal('Reassign To-dos', content);
    }

    function showDataExport() {
        const content = `
            <p>Export all data from your account in various formats.</p>
            <div class="pfob-form-group">
                <label>Export format</label>
                <select class="pfob-input">
                    <option>JSON</option>
                    <option>CSV</option>
                    <option>XML</option>
                </select>
            </div>
            <button class="pfob-btn pfob-btn-primary">Start Export</button>
        `;
        showModal('Export Data', content);
    }

    function showManagePublic() {
        const content = `
            <p>Manage publicly accessible items in your account.</p>
            <div id="public-items-list"></div>
        `;
        showModal('Manage Public Items', content);
    }

    function showPauseAccount() {
        const content = `
            <p>Pause or cancel your ProjectFOB subscription.</p>
            <div class="pfob-warning">
                <p><strong>Warning:</strong> Pausing will stop billing but you won't be able to access your account until you resume.</p>
            </div>
            <button class="pfob-btn pfob-btn-warning">Pause Account</button>
            <button class="pfob-btn pfob-btn-danger">Cancel Account</button>
        `;
        showModal('Pause or Cancel Account', content);
    }
});
</script>

<?php
error_log( '[Adminland] Rendering complete, calling footer' );
PFOB_Template::footer();
error_log( '[Adminland] Page fully rendered' );
