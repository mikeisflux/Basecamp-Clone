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

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Only THE SUBSCRIBER (account owner who pays) can access this page
// Invited users should not be able to access adminland at all
$subscription = PFOB_Subscription::get_by_user_id( $user_id );

if ( ! $subscription || ! in_array( $subscription->status, array( 'active', 'trialing' ) ) ) {
    wp_die( __( 'Access denied. You must be the account subscriber to access Adminland.', 'projectfob' ) );
}

// Get subscription plan configuration
$plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
$plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

if ( ! $plan ) {
    wp_die( __( 'Invalid subscription plan.', 'projectfob' ) );
}

// Calculate costs
$monthly_cost = $plan['price'];
$next_payment_date = $subscription->current_period_end ? date( 'F j, Y', strtotime( $subscription->current_period_end ) ) : 'N/A';

// Check for add-ons (from subscription metadata)
$metadata = $subscription->metadata ? json_decode( $subscription->metadata, true ) : array();
$has_timesheet = isset( $metadata['addon_timesheet'] ) && $metadata['addon_timesheet'];
$has_admin_pro = isset( $metadata['addon_admin_pro'] ) && $metadata['addon_admin_pro'];

if ( $has_timesheet ) {
    $monthly_cost += 50;
}
if ( $has_admin_pro ) {
    $monthly_cost += 50;
}

// Get organization name
$organization = get_user_meta( $user_id, 'pfob_organization', true ) ?: get_bloginfo( 'name' );

// Get all invited users (people the subscriber has invited to their account)
$invited_users = get_users( array(
    'meta_query' => array(
        array(
            'key'   => 'pfob_account_owner',
            'value' => $user_id,
        ),
    ),
) );

PFOB_Template::header( 'Adminland' );
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

        <div class="pfob-plan-limits">
            <h3>Your Plan Includes:</h3>
            <ul>
                <li>Projects: <?php echo $plan['features']['projects'] == 999999 ? 'Unlimited' : $plan['features']['projects']; ?></li>
                <li>Users: <?php echo $plan['features']['users'] == 999999 ? 'Unlimited' : $plan['features']['users']; ?></li>
                <li>Storage: <?php echo $plan['features']['storage_gb'] == 999999 ? 'Unlimited' : $plan['features']['storage_gb'] . ' GB'; ?></li>
                <?php if ( isset( $plan['features']['google_calendar'] ) && $plan['features']['google_calendar'] ) : ?>
                <li>Google Calendar Integration</li>
                <?php endif; ?>
                <?php if ( isset( $plan['features']['advanced_analytics'] ) && $plan['features']['advanced_analytics'] ) : ?>
                <li>Advanced Analytics</li>
                <?php endif; ?>
                <?php if ( isset( $plan['features']['custom_branding'] ) && $plan['features']['custom_branding'] ) : ?>
                <li>Custom Branding</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <?php if ( ! $has_timesheet || ! $has_admin_pro ) : ?>
    <div class="pfob-upgrades-banner">
        <div class="pfob-banner-content">
            <h3>⬆️ Add-ons available</h3>
            <p>Enhance your account with Timesheet ($50/mo) or Admin Pro ($50/mo) add-ons.</p>
        </div>
        <a href="<?php echo home_url( '/projectfob/adminland/upgrades' ); ?>" class="pfob-btn pfob-btn-primary">See add-ons</a>
    </div>
    <?php endif; ?>

    <!-- Invited Users -->
    <div class="pfob-adminland-section">
        <h2>Invited Users</h2>
        <p>People you've invited to use your ProjectFOB account</p>
        <?php if ( ! empty( $invited_users ) ) : ?>
        <div class="pfob-users-grid">
            <?php foreach ( $invited_users as $invited_user ) :
                $initials = strtoupper( substr( $invited_user->display_name, 0, 1 ) . substr( strrchr( $invited_user->display_name, ' ' ), 1, 1 ) );
            ?>
            <div class="pfob-user-avatar">
                <div class="pfob-avatar"><?php echo esc_html( $initials ); ?></div>
                <span><?php echo esc_html( $invited_user->display_name ); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <p class="pfob-empty-state">No invited users yet. <a href="<?php echo home_url( '/projectfob/people/invite' ); ?>">Invite someone</a></p>
        <?php endif; ?>

        <div class="pfob-capabilities-section">
            <h3>As the subscriber, you can:</h3>
            <div class="pfob-capabilities-grid">
                <a href="<?php echo home_url( '/projectfob/people' ); ?>" class="pfob-capability-card">
                    <span class="pfob-icon">👥</span>
                    <span class="pfob-label">Manage people</span>
                </a>
                <a href="<?php echo home_url( '/projectfob/people/invite' ); ?>" class="pfob-capability-card">
                    <span class="pfob-icon">👤</span>
                    <span class="pfob-label">Invite coworkers</span>
                </a>
                <a href="<?php echo home_url( '/projectfob/adminland/billing' ); ?>" class="pfob-capability-card">
                    <span class="pfob-icon">💳</span>
                    <span class="pfob-label">Manage billing</span>
                </a>
                <a href="<?php echo home_url( '/projectfob/adminland/upgrades' ); ?>" class="pfob-capability-card">
                    <span class="pfob-icon">⬆️</span>
                    <span class="pfob-label">Upgrade plan</span>
                </a>
                <a href="<?php echo home_url( '/projectfob/settings/notifications' ); ?>" class="pfob-capability-card">
                    <span class="pfob-icon">🔔</span>
                    <span class="pfob-label">Notification settings</span>
                </a>
                <a href="<?php echo home_url( '/projectfob/analytics' ); ?>" class="pfob-capability-card">
                    <span class="pfob-icon">📊</span>
                    <span class="pfob-label">View analytics</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Account Owner Info -->
    <div class="pfob-adminland-section pfob-owner-section">
        <h2>Account Owner</h2>
        <div class="pfob-users-grid">
            <?php
            $owner_initials = strtoupper( substr( $user->display_name, 0, 1 ) . substr( strrchr( $user->display_name, ' ' ), 1, 1 ) );
            ?>
            <div class="pfob-user-avatar">
                <div class="pfob-avatar owner"><?php echo esc_html( $owner_initials ); ?></div>
                <span><?php echo esc_html( $user->display_name ); ?></span>
            </div>
        </div>

        <div class="pfob-capabilities-section">
            <h3>You're an account owner, so you can:</h3>
            <div class="pfob-capabilities-grid">
                <a href="<?php echo home_url( '/projectfob/adminland/billing' ); ?>" class="pfob-capability-card">
                    <span class="pfob-icon">💰</span>
                    <div>
                        <div class="pfob-label">Handle billing, invoices, packages, and upgrades</div>
                        <div class="pfob-sublabel">Next payment: $<?php echo $monthly_cost; ?> on <?php echo $next_payment_date; ?></div>
                    </div>
                </a>
                <a href="#" class="pfob-capability-card" data-action="manage-storage">
                    <span class="pfob-icon">💾</span>
                    <span class="pfob-label">Manage storage</span>
                </a>
                <a href="#" class="pfob-capability-card" data-action="manage-owners">
                    <span class="pfob-icon">👑</span>
                    <span class="pfob-label">Add/remove account owners</span>
                </a>
                <a href="#" class="pfob-capability-card" data-action="rename-account">
                    <span class="pfob-icon">✏️</span>
                    <span class="pfob-label">Rename this account (<?php echo esc_html( $organization ); ?>)</span>
                </a>
                <a href="#" class="pfob-capability-card" data-action="view-trash">
                    <span class="pfob-icon">🗑️</span>
                    <span class="pfob-label">View everything in the trash</span>
                </a>
                <a href="#" class="pfob-capability-card" data-action="reassign-todos">
                    <span class="pfob-icon">📋</span>
                    <span class="pfob-label">Reassign someone's to-dos</span>
                </a>
                <a href="#" class="pfob-capability-card" data-action="access-projects">
                    <span class="pfob-icon">🔑</span>
                    <span class="pfob-label">Access any project</span>
                </a>
                <a href="#" class="pfob-capability-card" data-action="export-data">
                    <span class="pfob-icon">📥</span>
                    <span class="pfob-label">Export data from this account</span>
                </a>
                <a href="#" class="pfob-capability-card" data-action="manage-public">
                    <span class="pfob-icon">🔧</span>
                    <span class="pfob-label">Manage public items</span>
                </a>
                <a href="#" class="pfob-capability-card" data-action="pause-account">
                    <span class="pfob-icon">⏸️</span>
                    <span class="pfob-label">Pause or cancel this account</span>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.pfob-adminland {
    max-width: 1200px;
    margin: 0 auto;
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

.pfob-capabilities-section h3 {
    margin: 0 0 20px 0;
    font-size: 16px;
    color: #666;
    font-weight: normal;
}

.pfob-capabilities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.pfob-capability-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s;
}

.pfob-capability-card:hover {
    background: #e8e9ea;
    border-color: #0066cc;
    transform: translateY(-2px);
}

.pfob-capability-card .pfob-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.pfob-capability-card .pfob-label {
    font-weight: 500;
    font-size: 14px;
}

.pfob-capability-card .pfob-sublabel {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
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
PFOB_Template::footer();
