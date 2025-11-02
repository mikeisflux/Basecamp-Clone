<?php
/**
 * Custom User Roles - Admin Pro Pack
 *
 * Create and manage custom user roles with specific permissions
 * beyond the default Team Member, Contractor, and Client roles.
 */
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

PFOB_Template::header('Custom User Roles');
?>

<div class="custom-roles-wrapper">
    <a href="<?php echo home_url('/projectfob/adminland'); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">👥 Custom User Roles</h1>
    <p class="page-subtitle">Create custom roles with specific permissions tailored to your organization's needs.</p>

    <!-- Create New Role -->
    <div class="roles-section">
        <h2 class="section-title">Create New Role</h2>

        <div class="create-role-form">
            <div class="form-row">
                <label class="form-label">Role Name</label>
                <input type="text" id="role-name" class="form-input" placeholder="e.g., Content Editor">
            </div>

            <div class="form-row">
                <label class="form-label">Display Color</label>
                <div class="color-picker-group">
                    <input type="color" id="role-color" class="color-input" value="#0066cc">
                    <span class="color-preview" id="color-preview">#0066cc</span>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">Clone From (Optional)</label>
                <select id="role-clone" class="form-select">
                    <option value="">Start from scratch</option>
                    <option value="team_member">Clone Team Member permissions</option>
                    <option value="contractor">Clone Contractor permissions</option>
                    <option value="client">Clone Client permissions</option>
                </select>
            </div>

            <div class="form-row">
                <label class="form-label">Description</label>
                <textarea id="role-description" class="form-textarea" rows="2" placeholder="Describe what this role is for..."></textarea>
            </div>

            <div class="permissions-section">
                <label class="form-label">Permissions</label>

                <div class="permission-category">
                    <h3 class="category-title">Project Management</h3>
                    <div class="permissions-grid">
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="create_projects">
                            <span>Create projects</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="edit_projects">
                            <span>Edit projects</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="delete_projects">
                            <span>Delete projects</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="archive_projects">
                            <span>Archive projects</span>
                        </label>
                    </div>
                </div>

                <div class="permission-category">
                    <h3 class="category-title">User Management</h3>
                    <div class="permissions-grid">
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="invite_users">
                            <span>Invite users</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="remove_users">
                            <span>Remove users</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="edit_user_roles">
                            <span>Edit user roles</span>
                        </label>
                    </div>
                </div>

                <div class="permission-category">
                    <h3 class="category-title">Content</h3>
                    <div class="permissions-grid">
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="create_content" checked>
                            <span>Create content</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="edit_own_content" checked>
                            <span>Edit own content</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="edit_others_content">
                            <span>Edit others' content</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="delete_own_content" checked>
                            <span>Delete own content</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="delete_others_content">
                            <span>Delete others' content</span>
                        </label>
                    </div>
                </div>

                <div class="permission-category">
                    <h3 class="category-title">Files & Documents</h3>
                    <div class="permissions-grid">
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="upload_files" checked>
                            <span>Upload files</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="download_files" checked>
                            <span>Download files</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="delete_files">
                            <span>Delete files</span>
                        </label>
                    </div>
                </div>

                <div class="permission-category">
                    <h3 class="category-title">Tools Access</h3>
                    <div class="permissions-grid">
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="access_messages" checked>
                            <span>Message Boards</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="access_todos" checked>
                            <span>To-dos</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="access_docs" checked>
                            <span>Docs & Files</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="access_chat">
                            <span>Chat</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="access_schedule">
                            <span>Schedule</span>
                        </label>
                        <label class="permission-item">
                            <input type="checkbox" class="permission-checkbox" data-perm="access_timesheet">
                            <span>Timesheet</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button class="action-button primary-button" onclick="createRole()">Create Role</button>
                <button class="action-button secondary-button" onclick="resetForm()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Existing Roles -->
    <div class="roles-section">
        <h2 class="section-title">Custom Roles</h2>

        <div id="roles-list" class="roles-list">
            <div class="loading-message">Loading custom roles...</div>
        </div>
    </div>

    <!-- Default Roles Reference -->
    <div class="roles-section">
        <h2 class="section-title">Default Roles (Reference)</h2>
        <p style="color: #666; font-size: 14px; margin-bottom: 16px;">These are the built-in roles. You cannot edit them, but you can clone their permissions when creating custom roles.</p>

        <div class="default-roles-grid">
            <div class="role-reference-card">
                <div class="role-ref-header">
                    <span class="role-ref-name">Team Member</span>
                    <span class="role-ref-badge" style="background: #d4edda; color: #155724;">Default</span>
                </div>
                <div class="role-ref-perms">
                    ✓ Create projects<br>
                    ✓ Invite people<br>
                    ✓ Full tool access<br>
                    ✓ Manage own content
                </div>
            </div>

            <div class="role-reference-card">
                <div class="role-ref-header">
                    <span class="role-ref-name">Contractor</span>
                    <span class="role-ref-badge" style="background: #fff3cd; color: #856404;">Default</span>
                </div>
                <div class="role-ref-perms">
                    ✓ Limited project access<br>
                    ✓ Selected tools only<br>
                    ✓ Manage own content<br>
                    ✗ Cannot invite people
                </div>
            </div>

            <div class="role-reference-card">
                <div class="role-ref-header">
                    <span class="role-ref-name">Client</span>
                    <span class="role-ref-badge" style="background: #d1ecf1; color: #0c5460;">Default</span>
                </div>
                <div class="role-ref-perms">
                    ✓ View-only access<br>
                    ✓ Comment on content<br>
                    ✗ Cannot create projects<br>
                    ✗ Limited tool access
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.custom-roles-wrapper {
    max-width: 1000px;
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

.roles-section {
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

/* Form Styles */
.create-role-form {
    max-width: 800px;
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

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.form-textarea {
    resize: vertical;
}

/* Color Picker */
.color-picker-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.color-input {
    width: 60px;
    height: 40px;
    border: 1px solid #ddd;
    border-radius: 6px;
    cursor: pointer;
}

.color-preview {
    font-family: monospace;
    font-size: 14px;
    color: #666;
}

/* Permissions */
.permissions-section {
    margin-bottom: 20px;
}

.permission-category {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 16px;
}

.category-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #0066cc;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}

.permission-item {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    color: #333;
}

.permission-checkbox {
    margin-right: 8px;
    cursor: pointer;
}

/* Action Buttons */
.action-button {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: none;
    margin-right: 8px;
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

.form-actions {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e0e0e0;
}

/* Roles List */
.roles-list {
    min-height: 200px;
}

.role-card {
    background: #f8f9fa;
    border-left: 4px solid #0066cc;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 16px;
}

.role-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.role-name {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.role-description {
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
}

.role-meta {
    font-size: 13px;
    color: #999;
    margin-bottom: 12px;
}

.role-permissions {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 12px;
    margin-bottom: 12px;
}

.perm-category-block {
    margin-bottom: 12px;
}

.perm-category-block:last-child {
    margin-bottom: 0;
}

.perm-category-name {
    font-size: 13px;
    font-weight: 600;
    color: #555;
    margin-bottom: 6px;
}

.perm-list {
    font-size: 13px;
    color: #666;
    line-height: 1.6;
}

.role-actions {
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
}

.role-actions .action-button {
    padding: 6px 12px;
    font-size: 14px;
    margin-bottom: 8px;
}

/* Default Roles Reference */
.default-roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.role-reference-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 16px;
}

.role-ref-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e0e0e0;
}

.role-ref-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.role-ref-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.role-ref-perms {
    font-size: 13px;
    color: #666;
    line-height: 1.8;
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
    .custom-roles-wrapper {
        padding: 12px;
    }

    .roles-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }

    .permissions-grid {
        grid-template-columns: 1fr;
    }

    .default-roles-grid {
        grid-template-columns: 1fr;
    }

    .role-header {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCustomRoles();

    // Color picker preview
    document.getElementById('role-color').addEventListener('input', function(e) {
        document.getElementById('color-preview').textContent = e.target.value;
    });

    window.createRole = createRole;
    window.resetForm = resetForm;
    window.editRole = editRole;
    window.deleteRole = deleteRole;
    window.assignUsers = assignUsers;
});

async function createRole() {
    const name = document.getElementById('role-name').value.trim();
    const color = document.getElementById('role-color').value;
    const cloneFrom = document.getElementById('role-clone').value;
    const description = document.getElementById('role-description').value.trim();

    if (!name) {
        alert('Please enter a role name');
        return;
    }

    // Collect permissions
    const permissions = {};
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        permissions[checkbox.dataset.perm] = checkbox.checked;
    });

    try {
        const response = await fetch(`${pfobData.restUrl}/admin-pro/roles`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({
                name: name,
                color: color,
                clone_from: cloneFrom,
                description: description,
                permissions: permissions
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Custom role created successfully!');
            resetForm();
            loadCustomRoles();
        } else {
            alert('Failed to create role: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Custom role created!\n\n(API not yet implemented - this is a demo. Your role would be saved when the backend is ready.)');
        resetForm();
        loadCustomRoles();
    }
}

function resetForm() {
    document.getElementById('role-name').value = '';
    document.getElementById('role-color').value = '#0066cc';
    document.getElementById('color-preview').textContent = '#0066cc';
    document.getElementById('role-clone').value = '';
    document.getElementById('role-description').value = '';

    // Reset checkboxes to default
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        const defaults = ['create_content', 'edit_own_content', 'delete_own_content', 'upload_files', 'download_files', 'access_messages', 'access_todos', 'access_docs'];
        checkbox.checked = defaults.includes(checkbox.dataset.perm);
    });
}

async function loadCustomRoles() {
    try {
        const response = await fetch(`${pfobData.restUrl}/admin-pro/roles`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderRoles(data.roles || []);
        } else {
            document.getElementById('roles-list').innerHTML = '<div class="empty-message">Failed to load custom roles</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        // Show placeholder roles
        renderPlaceholderRoles();
    }
}

function renderRoles(roles) {
    if (roles.length === 0) {
        document.getElementById('roles-list').innerHTML = '<div class="empty-message">No custom roles yet. Create your first role above!</div>';
        return;
    }

    const html = roles.map(role => `
        <div class="role-card" style="border-left-color: ${role.color || '#0066cc'}">
            <div class="role-header">
                <div>
                    <div class="role-name">${role.name}</div>
                    ${role.description ? `<div class="role-description">${role.description}</div>` : ''}
                </div>
            </div>

            <div class="role-meta">
                ${role.user_count || 0} users assigned • Created ${role.created_at}
            </div>

            <div class="role-permissions">
                ${Object.entries(role.permission_categories || {}).map(([category, perms]) => `
                    <div class="perm-category-block">
                        <div class="perm-category-name">${category}</div>
                        <div class="perm-list">${perms.join(', ')}</div>
                    </div>
                `).join('')}
            </div>

            <div class="role-actions">
                <button class="action-button primary-button" onclick="assignUsers(${role.id})">Assign Users</button>
                <button class="action-button secondary-button" onclick="editRole(${role.id})">Edit</button>
                <button class="action-button secondary-button" style="background:#dc3545;color:white;border:none" onclick="deleteRole(${role.id})">Delete</button>
            </div>
        </div>
    `).join('');

    document.getElementById('roles-list').innerHTML = html;
}

function renderPlaceholderRoles() {
    const placeholderRoles = [
        {
            id: 1,
            name: 'Content Editor',
            color: '#28a745',
            description: 'Can create and edit content but cannot manage users or projects',
            user_count: 3,
            created_at: 'January 15, 2025',
            permission_categories: {
                'Project Management': ['View projects'],
                'Content': ['Create content', 'Edit own content', 'Edit others\' content', 'Delete own content'],
                'Files': ['Upload files', 'Download files'],
                'Tools': ['Message Boards', 'Docs & Files']
            }
        },
        {
            id: 2,
            name: 'Project Viewer',
            color: '#ffc107',
            description: 'Read-only access to all projects with ability to comment',
            user_count: 5,
            created_at: 'January 20, 2025',
            permission_categories: {
                'Project Management': ['View projects'],
                'Content': ['Create comments'],
                'Files': ['Download files'],
                'Tools': ['Message Boards', 'Docs & Files', 'Schedule']
            }
        },
        {
            id: 3,
            name: 'Timesheet Manager',
            color: '#dc3545',
            description: 'Can manage timesheets and approve time entries',
            user_count: 2,
            created_at: 'January 25, 2025',
            permission_categories: {
                'Project Management': ['View projects'],
                'Content': ['View all content'],
                'Tools': ['Timesheet', 'Reports'],
                'Special': ['Approve timesheets', 'Export time data']
            }
        }
    ];

    renderRoles(placeholderRoles);
}

function editRole(roleId) {
    alert('Edit role functionality coming soon. This will allow you to modify role name, color, and permissions.');
}

function deleteRole(roleId) {
    if (confirm('Are you sure you want to delete this role? Users assigned to this role will be moved to the default Team Member role.')) {
        alert('Role deleted! (API not yet implemented - this would remove the custom role from the database.)');
        loadCustomRoles();
    }
}

function assignUsers(roleId) {
    alert('Assign users functionality coming soon. This will show a list of all users and let you assign them to this custom role.');
}
</script>

<?php
PFOB_Template::footer();
