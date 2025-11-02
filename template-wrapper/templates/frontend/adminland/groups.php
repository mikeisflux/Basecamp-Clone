<?php
/**
 * Groups Management Page
 *
 * Allows administrators to create and manage groups for organizing people.
 * Groups make it easier to assign multiple people to projects at once.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'Manage Groups' );
?>

<div class="groups-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Manage Groups</h1>
    <p class="page-subtitle">Organize your people into groups for easier project assignment and management.</p>

    <!-- Create New Group -->
    <div class="groups-section">
        <h2 class="section-title">Create New Group</h2>

        <div class="create-group-form">
            <div class="form-row">
                <label class="form-label">Group Name</label>
                <input type="text" id="new-group-name" class="form-input" placeholder="e.g., Marketing Team, Designers, Developers">
            </div>

            <div class="form-row">
                <label class="form-label">Description (Optional)</label>
                <textarea id="new-group-description" class="form-textarea" rows="3" placeholder="What is this group for?"></textarea>
            </div>

            <button class="action-button primary-button" onclick="createGroup()">Create Group</button>
        </div>
    </div>

    <!-- Existing Groups -->
    <div class="groups-section">
        <h2 class="section-title">Your Groups</h2>

        <div id="groups-list" class="groups-list">
            <div class="loading-message">Loading groups...</div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.groups-page-wrapper {
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

.groups-section {
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

.create-group-form {
    max-width: 600px;
}

.form-row {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 15px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
    resize: vertical;
}

.form-textarea:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.action-button {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.primary-button {
    background: #0066cc;
    color: white;
}

.primary-button:hover {
    background: #0052a3;
}

.secondary-button {
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
}

.secondary-button:hover {
    background: #d1d5db;
}

.danger-button {
    background: #dc3545;
    color: white;
}

.danger-button:hover {
    background: #c82333;
}

.groups-list {
    min-height: 200px;
}

.group-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 16px;
}

.group-header {
    margin-bottom: 12px;
}

.group-name {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.group-description {
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
}

.group-meta {
    font-size: 13px;
    color: #666;
    margin-bottom: 12px;
}

.group-members {
    margin-bottom: 12px;
}

.members-label {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.member-list {
    padding-left: 20px;
    margin-bottom: 12px;
}

.member-item {
    font-size: 14px;
    color: #666;
    margin-bottom: 4px;
}

.group-actions {
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
}

.group-actions .action-button {
    margin-right: 8px;
    margin-bottom: 8px;
    padding: 6px 12px;
    font-size: 14px;
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

@media (max-width: 768px) {
    .groups-page-wrapper {
        padding: 12px;
    }

    .groups-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadGroups();

    window.createGroup = createGroup;
    window.editGroup = editGroup;
    window.deleteGroup = deleteGroup;
    window.manageMembers = manageMembers;
});

async function loadGroups() {
    try {
        const response = await fetch(`${pfobData.restUrl}/account/groups`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderGroups(data.groups || []);
        } else {
            document.getElementById('groups-list').innerHTML = '<div class="empty-message">Failed to load groups</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        // For now, show placeholder since API might not be built yet
        renderPlaceholderGroups();
    }
}

function renderGroups(groups) {
    if (groups.length === 0) {
        document.getElementById('groups-list').innerHTML = '<div class="empty-message">No groups yet. Create your first group above!</div>';
        return;
    }

    const html = groups.map(group => `
        <div class="group-card">
            <div class="group-header">
                <div class="group-name">${group.name}</div>
                ${group.description ? `<div class="group-description">${group.description}</div>` : ''}
            </div>

            <div class="group-meta">
                Created ${group.created_at} • ${group.member_count || 0} member${group.member_count !== 1 ? 's' : ''}
            </div>

            ${group.members && group.members.length > 0 ? `
                <div class="group-members">
                    <div class="members-label">Members:</div>
                    <div class="member-list">
                        ${group.members.map(m => `<div class="member-item">• ${m.name}</div>`).join('')}
                    </div>
                </div>
            ` : ''}

            <div class="group-actions">
                <button class="action-button primary-button" onclick="manageMembers(${group.id})">Manage Members</button>
                <button class="action-button secondary-button" onclick="editGroup(${group.id})">Edit</button>
                <button class="action-button danger-button" onclick="deleteGroup(${group.id})">Delete</button>
            </div>
        </div>
    `).join('');

    document.getElementById('groups-list').innerHTML = html;
}

function renderPlaceholderGroups() {
    // Show example groups since API is not built yet
    const placeholderGroups = [
        {
            id: 1,
            name: 'Marketing Team',
            description: 'All marketing department members',
            created_at: 'January 15, 2025',
            member_count: 5,
            members: [
                { name: 'John Smith' },
                { name: 'Jane Doe' },
                { name: 'Bob Wilson' }
            ]
        },
        {
            id: 2,
            name: 'Developers',
            description: 'Engineering team',
            created_at: 'December 10, 2024',
            member_count: 8,
            members: []
        }
    ];

    renderGroups(placeholderGroups);
}

async function createGroup() {
    const name = document.getElementById('new-group-name').value.trim();
    const description = document.getElementById('new-group-description').value.trim();

    if (!name) {
        alert('Please enter a group name');
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/account/groups`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({ name, description })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('new-group-name').value = '';
            document.getElementById('new-group-description').value = '';
            loadGroups();
            alert('Group created successfully!');
        } else {
            alert('Failed to create group: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Group creation API not yet implemented. This will create a new group when the backend is ready.');
    }
}

function editGroup(groupId) {
    alert('Edit group functionality coming soon. This will allow you to rename the group and update its description.');
}

function deleteGroup(groupId) {
    if (!confirm('Are you sure you want to delete this group? This will not delete the members.')) {
        return;
    }

    alert('Delete group functionality coming soon. This will remove the group but keep all members in your account.');
}

function manageMembers(groupId) {
    alert('Manage members functionality coming soon. This will let you add or remove people from this group.');
}
</script>

<?php
PFOB_Template::footer();
