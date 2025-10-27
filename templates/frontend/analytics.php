<?php
/**
 * Analytics Dashboard
 */

$user_id = get_current_user_id();

BCWP_Template::header( 'Analytics Dashboard' );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-analytics">

        <header class="bcwp-page-header">
            <h1>📊 Analytics Dashboard</h1>
            <div class="bcwp-analytics-controls">
                <select id="time-range-select" class="bcwp-select">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
        </header>

        <div id="loading-state" class="bcwp-loading">
            <div class="bcwp-spinner"></div>
            <p>Loading analytics...</p>
        </div>

        <div id="analytics-content" style="display: none;">

            <!-- Workspace Overview -->
            <section class="bcwp-analytics-section">
                <h2>Workspace Overview</h2>
                <div class="bcwp-stats-grid" id="workspace-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>
            </section>

            <!-- Your Personal Stats -->
            <section class="bcwp-analytics-section">
                <h2>Your Performance</h2>
                <div class="bcwp-stats-grid" id="user-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>
            </section>

            <!-- Project Breakdown -->
            <section class="bcwp-analytics-section">
                <h2>Projects Overview</h2>
                <div class="bcwp-projects-table" id="projects-table">
                    <!-- Table will be populated by JavaScript -->
                </div>
            </section>

        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
const bcwpData = {
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

let currentDays = 30;
let charts = {};

// Time range change
document.getElementById('time-range-select').addEventListener('change', (e) => {
    currentDays = parseInt(e.target.value);
    loadAnalytics();
});

async function loadAnalytics() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('analytics-content').style.display = 'none';

    try {
        // Load all analytics data in parallel
        const [workspace, user, projects] = await Promise.all([
            fetch(`${bcwpData.restUrl}/analytics/workspace?days=${currentDays}`, {
                headers: { 'X-WP-Nonce': bcwpData.nonce }
            }).then(r => r.json()),
            fetch(`${bcwpData.restUrl}/analytics/user?days=${currentDays}`, {
                headers: { 'X-WP-Nonce': bcwpData.nonce }
            }).then(r => r.json()),
            fetch(`${bcwpData.restUrl}/analytics/projects?days=${currentDays}`, {
                headers: { 'X-WP-Nonce': bcwpData.nonce }
            }).then(r => r.json()),
        ]);

        if (workspace.success && user.success && projects.success) {
            renderWorkspaceStats(workspace.data);
            renderUserStats(user.data);
            renderProjectsTable(projects.data);

            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('analytics-content').style.display = 'block';
        }
    } catch (error) {
        console.error('Failed to load analytics:', error);
        document.getElementById('loading-state').innerHTML = '<p class="bcwp-error">Failed to load analytics</p>';
    }
}

function renderWorkspaceStats(data) {
    const container = document.getElementById('workspace-stats');
    container.innerHTML = `
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">📁</div>
            <div class="bcwp-stat-value">${data.total_projects}</div>
            <div class="bcwp-stat-label">Total Projects</div>
        </div>
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">👥</div>
            <div class="bcwp-stat-value">${data.total_users}</div>
            <div class="bcwp-stat-label">Team Members</div>
        </div>
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">💬</div>
            <div class="bcwp-stat-value">${data.total_messages}</div>
            <div class="bcwp-stat-label">Total Messages</div>
        </div>
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">✅</div>
            <div class="bcwp-stat-value">${data.completed_todos}/${data.total_todos}</div>
            <div class="bcwp-stat-label">Completed To-dos</div>
        </div>
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">⚡</div>
            <div class="bcwp-stat-value">${data.recent_activity}</div>
            <div class="bcwp-stat-label">Recent Activities</div>
        </div>
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">🔥</div>
            <div class="bcwp-stat-value">${data.most_active_project || 'None'}</div>
            <div class="bcwp-stat-label">Most Active Project</div>
        </div>
    `;
}

function renderUserStats(data) {
    const container = document.getElementById('user-stats');
    container.innerHTML = `
        <div class="bcwp-stat-card bcwp-stat-card-highlight">
            <div class="bcwp-stat-icon">🎯</div>
            <div class="bcwp-stat-value">${data.assigned_todos}</div>
            <div class="bcwp-stat-label">Assigned To-dos</div>
        </div>
        <div class="bcwp-stat-card bcwp-stat-card-success">
            <div class="bcwp-stat-icon">✓</div>
            <div class="bcwp-stat-value">${data.completed_todos}</div>
            <div class="bcwp-stat-label">Completed</div>
        </div>
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">📈</div>
            <div class="bcwp-stat-value">${data.completion_rate}%</div>
            <div class="bcwp-stat-label">Completion Rate</div>
        </div>
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">📝</div>
            <div class="bcwp-stat-value">${data.messages_created}</div>
            <div class="bcwp-stat-label">Messages Created</div>
        </div>
        <div class="bcwp-stat-card">
            <div class="bcwp-stat-icon">💭</div>
            <div class="bcwp-stat-value">${data.comments_made}</div>
            <div class="bcwp-stat-label">Comments Made</div>
        </div>
    `;
}

function renderProjectsTable(data) {
    const container = document.getElementById('projects-table');

    if (data.length === 0) {
        container.innerHTML = '<p class="bcwp-empty">No projects found</p>';
        return;
    }

    let html = `
        <table class="bcwp-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Messages</th>
                    <th>To-dos</th>
                    <th>Completed</th>
                    <th>Progress</th>
                    <th>Documents</th>
                    <th>Events</th>
                    <th>Team</th>
                </tr>
            </thead>
            <tbody>
    `;

    data.forEach(item => {
        const stats = item.stats;
        const completionRate = stats.total_todos > 0
            ? Math.round((stats.completed_todos / stats.total_todos) * 100)
            : 0;

        html += `
            <tr>
                <td><strong>${escapeHtml(item.project.name)}</strong></td>
                <td>${stats.total_messages}</td>
                <td>${stats.total_todos}</td>
                <td>${stats.completed_todos}</td>
                <td>
                    <div class="bcwp-progress-bar">
                        <div class="bcwp-progress-fill" style="width: ${completionRate}%"></div>
                        <span class="bcwp-progress-text">${completionRate}%</span>
                    </div>
                </td>
                <td>${stats.total_documents}</td>
                <td>${stats.total_events}</td>
                <td>${stats.total_members}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    container.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load analytics on page load
loadAnalytics();
</script>

<style>
.bcwp-analytics {
    max-width: 1400px;
    margin: 0 auto;
}

.bcwp-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.bcwp-analytics-controls {
    display: flex;
    gap: 15px;
    align-items: center;
}

.bcwp-loading {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.bcwp-spinner {
    width: 40px;
    height: 40px;
    margin: 0 auto 20px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2d9061;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.bcwp-analytics-section {
    background: white;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.bcwp-analytics-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    color: #333;
}

.bcwp-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.bcwp-stat-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    border: 2px solid #e0e0e0;
    transition: all 0.3s;
}

.bcwp-stat-card:hover {
    border-color: #2d9061;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.bcwp-stat-card-highlight {
    background: #fff3cd;
    border-color: #ffc107;
}

.bcwp-stat-card-success {
    background: #d4edda;
    border-color: #28a745;
}

.bcwp-stat-icon {
    font-size: 36px;
    margin-bottom: 10px;
}

.bcwp-stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2d9061;
    margin-bottom: 5px;
}

.bcwp-stat-label {
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bcwp-chart-container {
    position: relative;
    height: 300px;
    margin-top: 20px;
}

.bcwp-projects-table {
    overflow-x: auto;
}

.bcwp-table {
    width: 100%;
    border-collapse: collapse;
}

.bcwp-table th,
.bcwp-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.bcwp-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #666;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bcwp-table tbody tr:hover {
    background: #f8f9fa;
}

.bcwp-progress-bar {
    position: relative;
    height: 24px;
    background: #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    min-width: 100px;
}

.bcwp-progress-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.5s ease;
}

.bcwp-progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 12px;
    font-weight: bold;
    color: #333;
}

.bcwp-empty {
    text-align: center;
    padding: 40px;
    color: #999;
    font-style: italic;
}

.bcwp-error {
    text-align: center;
    padding: 40px;
    color: #dc3545;
}

@media (max-width: 768px) {
    .bcwp-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .bcwp-stats-grid {
        grid-template-columns: 1fr;
    }

    .bcwp-analytics-section {
        padding: 20px;
    }

    .bcwp-table {
        font-size: 14px;
    }

    .bcwp-table th,
    .bcwp-table td {
        padding: 8px;
    }
}
</style>

<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
