<?php
/**
 * Administrators Management Page
 *
 * Allows account owners to add or remove administrator privileges
 * from people in their account.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'Manage Administrators' );
?>

<div class="admin-mgmt-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Manage Administrators</h1>
    <p class="page-subtitle">Administrators can manage people, create projects, and configure account settings (but cannot access billing).</p>

    <!-- Current Administrators -->
    <div class="admin-section">
        <h2 class="section-title">Current Administrators</h2>

        <div id="current-admins" class="current-admins">
            <div class="loading-message">Loading administrators...</div>
        </div>
    </div>

    <!-- Add Administrator -->
    <div class="admin-section">
        <h2 class="section-title">Grant Administrator Access</h2>

        <div id="available-people" class="available-people">
            <div class="loading-message">Loading people...</div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.admin-mgmt-page-wrapper {
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

.admin-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    margin: 0 0 20px 0;
    color: #333;
}

.current-admins {
    min-height: 100px;
}

.admin-card {
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 16px;
}

.admin-header {
    margin-bottom: 12px;
}

.admin-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #6b46c1;
    color: white;
    text-align: center;
    line-height: 48px;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 12px;
    display: inline-block;
}

.admin-name {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.admin-email {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.admin-badge {
    display: inline-block;
    padding: 4px 12px;
    background: #0066cc;
    color: white;
    font-size: 12px;
    font-weight: 600;
    border-radius: 12px;
}

.admin-meta {
    font-size: 13px;
    color: #666;
    margin-top: 12px;
}

.admin-actions {
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
    margin-top: 12px;
}

.action-button {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
}

.revoke-button {
    background: #dc3545;
    color: white;
}

.revoke-button:hover {
    background: #c82333;
}

.grant-button {
    background: #10b981;
    color: white;
}

.grant-button:hover {
    background: #059669;
}

.available-people {
    min-height: 100px;
}

.person-card {
    padding: 16px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 12px;
}

.person-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.person-email {
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
}

.loading-message {
    text-align: center;
    padding: 40px;
    color: #666;
    font-size: 15px;
}

.empty-message {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}

.info-box {
    padding: 16px;
    background: #f0f7ff;
    border-left: 4px solid #0066cc;
    border-radius: 4px;
    margin-bottom: 20px;
}

.info-box p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

@media (max-width: 768px) {
    .admin-mgmt-page-wrapper {
        padding: 12px;
    }

    .admin-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAdministrators();
    loadAvailablePeople();

    window.revokeAdmin = revokeAdmin;
    window.grantAdmin = grantAdmin;
});

async function loadAdministrators() {
    try {
        const response = await fetch(`${pfobData.restUrl}/account/administrators`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderAdministrators(data.admins || []);
        } else {
            document.getElementById('current-admins').innerHTML = '<div class="empty-message">No administrators found</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        renderPlaceholderAdmins();
    }
}

function renderAdministrators(admins) {
    if (admins.length === 0) {
        document.getElementById('current-admins').innerHTML = '<div class="empty-message">No administrators besides the account owner</div>';
        return;
    }

    const html = admins.map(admin => {
        const initials = getInitials(admin.name);
        const isOwner = admin.is_owner;

        return `
            <div class="admin-card">
                <div class="admin-header">
                    <div class="admin-avatar">${initials}</div>
                </div>
                <div class="admin-name">${admin.name}</div>
                <div class="admin-email">${admin.email}</div>
                <span class="admin-badge">${isOwner ? 'Owner + Admin' : 'Administrator'}</span>

                <div class="admin-meta">
                    ${admin.granted_at ? `Administrator since ${admin.granted_at}` : ''}
                    ${admin.granted_by ? ` • Granted by ${admin.granted_by}` : ''}
                </div>

                ${!isOwner ? `
                    <div class="admin-actions">
                        <button class="action-button revoke-button" onclick="revokeAdmin(${admin.id})">
                            Revoke Administrator Access
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');

    document.getElementById('current-admins').innerHTML = html;
}

function renderPlaceholderAdmins() {
    const placeholderAdmins = [
        {
            id: 1,
            name: 'John Smith',
            email: 'john@example.com',
            is_owner: true,
            granted_at: null,
            granted_by: null
        },
        {
            id: 2,
            name: 'Jane Doe',
            email: 'jane@example.com',
            is_owner: false,
            granted_at: 'January 10, 2025',
            granted_by: 'John Smith'
        }
    ];

    renderAdministrators(placeholderAdmins);
}

async function loadAvailablePeople() {
    try {
        const response = await fetch(`${pfobData.restUrl}/account/users?exclude_admins=1`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderAvailablePeople(data.users || []);
        } else {
            document.getElementById('available-people').innerHTML = '<div class="empty-message">No people available</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        renderPlaceholderPeople();
    }
}

function renderAvailablePeople(people) {
    if (people.length === 0) {
        document.getElementById('available-people').innerHTML = '<div class="empty-message">Everyone in your account is already an administrator</div>';
        return;
    }

    const html = `
        <div class="info-box">
            <p>Select people below to grant them administrator access. They'll be able to manage projects and people, but won't have access to billing.</p>
        </div>
        ${people.map(person => `
            <div class="person-card">
                <div class="person-name">${person.name}</div>
                <div class="person-email">${person.email}</div>
                <button class="action-button grant-button" onclick="grantAdmin(${person.id})">
                    Grant Administrator Access
                </button>
            </div>
        `).join('')}
    `;

    document.getElementById('available-people').innerHTML = html;
}

function renderPlaceholderPeople() {
    const placeholderPeople = [
        {
            id: 3,
            name: 'Bob Wilson',
            email: 'bob@example.com'
        },
        {
            id: 4,
            name: 'Alice Johnson',
            email: 'alice@example.com'
        }
    ];

    renderAvailablePeople(placeholderPeople);
}

function getInitials(name) {
    const parts = name.trim().split(' ');
    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

async function grantAdmin(userId) {
    if (!confirm('Are you sure you want to grant administrator access to this person?')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/account/administrators`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({ user_id: userId })
        });

        const data = await response.json();

        if (data.success) {
            alert('Administrator access granted successfully!');
            loadAdministrators();
            loadAvailablePeople();
        } else {
            alert('Failed to grant access: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Grant administrator functionality coming soon.');
    }
}

async function revokeAdmin(userId) {
    if (!confirm('Are you sure you want to revoke administrator access from this person?')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/account/administrators/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            alert('Administrator access revoked successfully.');
            loadAdministrators();
            loadAvailablePeople();
        } else {
            alert('Failed to revoke access: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Revoke administrator functionality coming soon.');
    }
}
</script>

<?php
PFOB_Template::footer();
