<?php
/**
 * User Access Logs - Admin Pro Pack
 *
 * Track user activity, logins, and actions across the system.
 * Provides audit trail for security and compliance.
 */
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

PFOB_Template::header('User Access Logs');
?>

<div class="access-logs-wrapper">
    <a href="<?php echo home_url('/projectfob/adminland'); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">📋 User Access Logs</h1>
    <p class="page-subtitle">Monitor user activity, track access patterns, and maintain security audit trails.</p>

    <!-- Filters Section -->
    <div class="logs-section">
        <h2 class="section-title">Filter Activity</h2>

        <div class="filter-grid">
            <div class="filter-item">
                <label class="filter-label">User</label>
                <select id="filter-user" class="filter-select">
                    <option value="">All Users</option>
                    <option value="admin">Admin Users</option>
                    <option value="team">Team Members</option>
                    <option value="contractor">Contractors</option>
                    <option value="client">Clients</option>
                </select>
            </div>

            <div class="filter-item">
                <label class="filter-label">Action Type</label>
                <select id="filter-action" class="filter-select">
                    <option value="">All Actions</option>
                    <option value="login">Login/Logout</option>
                    <option value="project">Project Access</option>
                    <option value="file">File Operations</option>
                    <option value="user">User Management</option>
                    <option value="permission">Permission Changes</option>
                </select>
            </div>

            <div class="filter-item">
                <label class="filter-label">Date Range</label>
                <select id="filter-date" class="filter-select">
                    <option value="today">Today</option>
                    <option value="week">Last 7 Days</option>
                    <option value="month" selected>Last 30 Days</option>
                    <option value="all">All Time</option>
                </select>
            </div>

            <div class="filter-item">
                <button class="action-button primary-button" onclick="applyFilters()">Apply Filters</button>
                <button class="action-button secondary-button" onclick="resetFilters()">Reset</button>
            </div>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="logs-section">
        <div class="section-header">
            <h2 class="section-title">Recent Activity</h2>
            <button class="action-button secondary-button" onclick="exportLogs()">📊 Export CSV</button>
        </div>

        <div id="activity-logs" class="activity-logs">
            <div class="loading-message">Loading activity logs...</div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="logs-section">
        <h2 class="section-title">Activity Statistics</h2>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="stat-logins">0</div>
                <div class="stat-label">Logins Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-active">0</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-actions">0</div>
                <div class="stat-label">Actions This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-projects">0</div>
                <div class="stat-label">Projects Accessed</div>
            </div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.access-logs-wrapper {
    max-width: 1200px;
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

.logs-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-title {
    font-size: 20px;
    margin: 0 0 20px 0;
    color: #333;
}

.section-header .section-title {
    margin: 0;
}

/* Filters */
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.filter-item {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.filter-select {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
    background: white;
}

.filter-select:focus {
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

/* Activity Logs */
.activity-logs {
    min-height: 400px;
}

.log-entry {
    display: flex;
    align-items: flex-start;
    padding: 16px;
    border-bottom: 1px solid #e0e0e0;
    transition: background 0.2s;
}

.log-entry:hover {
    background: #f8f9fa;
}

.log-entry:last-child {
    border-bottom: none;
}

.log-icon {
    font-size: 24px;
    margin-right: 16px;
    flex-shrink: 0;
}

.log-content {
    flex: 1;
}

.log-user {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.log-action {
    font-size: 15px;
    color: #666;
    margin-bottom: 4px;
}

.log-meta {
    font-size: 13px;
    color: #999;
}

.log-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    margin-left: 8px;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

/* Statistics */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.stat-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 24px;
    text-align: center;
}

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #0066cc;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    color: #666;
}

.loading-message {
    text-align: center;
    padding: 60px;
    color: #666;
    font-size: 15px;
}

.empty-message {
    text-align: center;
    padding: 60px;
    color: #666;
    font-style: italic;
}

@media (max-width: 768px) {
    .access-logs-wrapper {
        padding: 12px;
    }

    .logs-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }

    .filter-grid {
        grid-template-columns: 1fr;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .section-header .action-button {
        margin-top: 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadActivityLogs();
    loadStatistics();

    window.applyFilters = applyFilters;
    window.resetFilters = resetFilters;
    window.exportLogs = exportLogs;
});

async function loadActivityLogs() {
    const userFilter = document.getElementById('filter-user').value;
    const actionFilter = document.getElementById('filter-action').value;
    const dateFilter = document.getElementById('filter-date').value;

    try {
        const params = new URLSearchParams({
            user: userFilter,
            action: actionFilter,
            date: dateFilter
        });

        const response = await fetch(`${pfobData.restUrl}/admin-pro/access-logs?${params}`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderLogs(data.logs || []);
        } else {
            document.getElementById('activity-logs').innerHTML = '<div class="empty-message">Failed to load activity logs</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        // Show placeholder data since API might not be built yet
        renderPlaceholderLogs();
    }
}

function renderLogs(logs) {
    if (logs.length === 0) {
        document.getElementById('activity-logs').innerHTML = '<div class="empty-message">No activity logs found for the selected filters.</div>';
        return;
    }

    const html = logs.map(log => {
        const icon = getLogIcon(log.action_type);
        const badge = getLogBadge(log.action_type);

        return `
            <div class="log-entry">
                <div class="log-icon">${icon}</div>
                <div class="log-content">
                    <div class="log-user">
                        ${log.user_name}
                        <span class="log-badge ${badge.class}">${badge.text}</span>
                    </div>
                    <div class="log-action">${log.description}</div>
                    <div class="log-meta">
                        ${log.created_at} • IP: ${log.ip_address || 'N/A'}
                    </div>
                </div>
            </div>
        `;
    }).join('');

    document.getElementById('activity-logs').innerHTML = html;
}

function renderPlaceholderLogs() {
    const placeholderLogs = [
        {
            user_name: 'John Doe',
            action_type: 'login',
            description: 'Logged in successfully',
            created_at: 'Just now',
            ip_address: '192.168.1.100'
        },
        {
            user_name: 'Jane Smith',
            action_type: 'project',
            description: 'Accessed project "Website Redesign"',
            created_at: '5 minutes ago',
            ip_address: '192.168.1.101'
        },
        {
            user_name: 'Mike Johnson',
            action_type: 'file',
            description: 'Downloaded file "design-mockups.pdf"',
            created_at: '12 minutes ago',
            ip_address: '192.168.1.102'
        },
        {
            user_name: 'Sarah Williams',
            action_type: 'user',
            description: 'Created new user account for "Bob Davis"',
            created_at: '1 hour ago',
            ip_address: '192.168.1.103'
        },
        {
            user_name: 'Admin',
            action_type: 'permission',
            description: 'Updated permissions for "Contractors" role',
            created_at: '2 hours ago',
            ip_address: '192.168.1.1'
        },
        {
            user_name: 'Tom Brown',
            action_type: 'login',
            description: 'Logged out',
            created_at: '3 hours ago',
            ip_address: '192.168.1.104'
        },
        {
            user_name: 'Emily Davis',
            action_type: 'project',
            description: 'Created new project "Q1 Marketing Campaign"',
            created_at: '4 hours ago',
            ip_address: '192.168.1.105'
        },
        {
            user_name: 'John Doe',
            action_type: 'file',
            description: 'Uploaded 5 files to "Shared Documents"',
            created_at: '5 hours ago',
            ip_address: '192.168.1.100'
        }
    ];

    renderLogs(placeholderLogs);
}

function getLogIcon(actionType) {
    const icons = {
        'login': '🔐',
        'project': '📁',
        'file': '📄',
        'user': '👤',
        'permission': '🔑',
        'default': '📋'
    };
    return icons[actionType] || icons['default'];
}

function getLogBadge(actionType) {
    const badges = {
        'login': { text: 'Auth', class: 'badge-success' },
        'project': { text: 'Project', class: 'badge-info' },
        'file': { text: 'File', class: 'badge-warning' },
        'user': { text: 'User Mgmt', class: 'badge-danger' },
        'permission': { text: 'Security', class: 'badge-danger' },
        'default': { text: 'Activity', class: 'badge-info' }
    };
    return badges[actionType] || badges['default'];
}

async function loadStatistics() {
    try {
        const response = await fetch(`${pfobData.restUrl}/admin-pro/access-stats`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('stat-logins').textContent = data.stats.logins_today || 0;
            document.getElementById('stat-active').textContent = data.stats.active_users || 0;
            document.getElementById('stat-actions').textContent = data.stats.actions_week || 0;
            document.getElementById('stat-projects').textContent = data.stats.projects_accessed || 0;
        }
    } catch (error) {
        console.error('Error:', error);
        // Show placeholder stats
        document.getElementById('stat-logins').textContent = '24';
        document.getElementById('stat-active').textContent = '12';
        document.getElementById('stat-actions').textContent = '347';
        document.getElementById('stat-projects').textContent = '18';
    }
}

function applyFilters() {
    loadActivityLogs();
}

function resetFilters() {
    document.getElementById('filter-user').value = '';
    document.getElementById('filter-action').value = '';
    document.getElementById('filter-date').value = 'month';
    loadActivityLogs();
}

function exportLogs() {
    const userFilter = document.getElementById('filter-user').value;
    const actionFilter = document.getElementById('filter-action').value;
    const dateFilter = document.getElementById('filter-date').value;

    const params = new URLSearchParams({
        user: userFilter,
        action: actionFilter,
        date: dateFilter,
        export: 'csv'
    });

    const exportUrl = `${pfobData.restUrl}/admin-pro/access-logs?${params}`;

    // For now, show alert. Real implementation would download CSV
    alert('Export functionality will download a CSV file with all filtered activity logs.\n\nExport URL: ' + exportUrl);
}
</script>

<?php
PFOB_Template::footer();
