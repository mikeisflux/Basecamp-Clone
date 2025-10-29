<?php
/**
 * Analytics Dashboard
 */

$user_id = get_current_user_id();

PFOB_Template::header( 'Analytics Dashboard' );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-analytics">

        <header class="pfob-page-header">
            <h1>📊 Analytics Dashboard</h1>
            <div class="pfob-analytics-controls">
                <select id="time-range-select" class="pfob-select">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
        </header>

        <div id="loading-state" class="pfob-loading">
            <div class="pfob-spinner"></div>
            <p>Loading analytics...</p>
        </div>

        <div id="analytics-content" style="display: none;">

            <!-- Workspace Overview -->
            <section class="pfob-analytics-section">
                <h2>Workspace Overview</h2>
                <div class="pfob-stats-grid" id="workspace-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>
            </section>

            <!-- Your Personal Stats -->
            <section class="pfob-analytics-section">
                <h2>Your Performance</h2>
                <div class="pfob-stats-grid" id="user-stats">
                    <!-- Stats will be populated by JavaScript -->
                </div>
            </section>

            <!-- Project Breakdown -->
            <section class="pfob-analytics-section">
                <h2>Projects Overview</h2>
                <div class="pfob-projects-table" id="projects-table">
                    <!-- Table will be populated by JavaScript -->
                </div>
            </section>

        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'pfob/v1' ); ?>',
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
            fetch(`${pfobData.restUrl}/analytics/workspace?days=${currentDays}`, {
                headers: { 'X-WP-Nonce': pfobData.nonce }
            }).then(r => r.json()),
            fetch(`${pfobData.restUrl}/analytics/user?days=${currentDays}`, {
                headers: { 'X-WP-Nonce': pfobData.nonce }
            }).then(r => r.json()),
            fetch(`${pfobData.restUrl}/analytics/projects?days=${currentDays}`, {
                headers: { 'X-WP-Nonce': pfobData.nonce }
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
        document.getElementById('loading-state').innerHTML = '<p class="pfob-error">Failed to load analytics</p>';
    }
}

function renderWorkspaceStats(data) {
    const container = document.getElementById('workspace-stats');
    container.innerHTML = `
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">📁</div>
            <div class="pfob-stat-value">${data.total_projects}</div>
            <div class="pfob-stat-label">Total Projects</div>
        </div>
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">👥</div>
            <div class="pfob-stat-value">${data.total_users}</div>
            <div class="pfob-stat-label">Team Members</div>
        </div>
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">💬</div>
            <div class="pfob-stat-value">${data.total_messages}</div>
            <div class="pfob-stat-label">Total Messages</div>
        </div>
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">✅</div>
            <div class="pfob-stat-value">${data.completed_todos}/${data.total_todos}</div>
            <div class="pfob-stat-label">Completed To-dos</div>
        </div>
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">⚡</div>
            <div class="pfob-stat-value">${data.recent_activity}</div>
            <div class="pfob-stat-label">Recent Activities</div>
        </div>
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">🔥</div>
            <div class="pfob-stat-value">${data.most_active_project || 'None'}</div>
            <div class="pfob-stat-label">Most Active Project</div>
        </div>
    `;
}

function renderUserStats(data) {
    const container = document.getElementById('user-stats');
    container.innerHTML = `
        <div class="pfob-stat-card pfob-stat-card-highlight">
            <div class="pfob-stat-icon">🎯</div>
            <div class="pfob-stat-value">${data.assigned_todos}</div>
            <div class="pfob-stat-label">Assigned To-dos</div>
        </div>
        <div class="pfob-stat-card pfob-stat-card-success">
            <div class="pfob-stat-icon">✓</div>
            <div class="pfob-stat-value">${data.completed_todos}</div>
            <div class="pfob-stat-label">Completed</div>
        </div>
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">📈</div>
            <div class="pfob-stat-value">${data.completion_rate}%</div>
            <div class="pfob-stat-label">Completion Rate</div>
        </div>
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">📝</div>
            <div class="pfob-stat-value">${data.messages_created}</div>
            <div class="pfob-stat-label">Messages Created</div>
        </div>
        <div class="pfob-stat-card">
            <div class="pfob-stat-icon">💭</div>
            <div class="pfob-stat-value">${data.comments_made}</div>
            <div class="pfob-stat-label">Comments Made</div>
        </div>
    `;
}

function renderProjectsTable(data) {
    const container = document.getElementById('projects-table');

    if (data.length === 0) {
        container.innerHTML = '<p class="pfob-empty">No projects found</p>';
        return;
    }

    let html = `
        <table class="pfob-table">
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
                    <div class="pfob-progress-bar">
                        <div class="pfob-progress-fill" style="width: ${completionRate}%"></div>
                        <span class="pfob-progress-text">${completionRate}%</span>
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
.pfob-analytics {
    max-width: 1400px;
    margin: 0 auto;
}

.pfob-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.pfob-analytics-controls {
    display: flex;
    gap: 15px;
    align-items: center;
}

.pfob-loading {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.pfob-spinner {
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

.pfob-analytics-section {
    background: white;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pfob-analytics-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    color: #333;
}

.pfob-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.pfob-stat-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    border: 2px solid #e0e0e0;
    transition: all 0.3s;
}

.pfob-stat-card:hover {
    border-color: #2d9061;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.pfob-stat-card-highlight {
    background: #fff3cd;
    border-color: #ffc107;
}

.pfob-stat-card-success {
    background: #d4edda;
    border-color: #28a745;
}

.pfob-stat-icon {
    font-size: 36px;
    margin-bottom: 10px;
}

.pfob-stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2d9061;
    margin-bottom: 5px;
}

.pfob-stat-label {
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pfob-chart-container {
    position: relative;
    height: 300px;
    margin-top: 20px;
}

.pfob-projects-table {
    overflow-x: auto;
}

.pfob-table {
    width: 100%;
    border-collapse: collapse;
}

.pfob-table th,
.pfob-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.pfob-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #666;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pfob-table tbody tr:hover {
    background: #f8f9fa;
}

.pfob-progress-bar {
    position: relative;
    height: 24px;
    background: #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    min-width: 100px;
}

.pfob-progress-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.5s ease;
}

.pfob-progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 12px;
    font-weight: bold;
    color: #333;
}

.pfob-empty {
    text-align: center;
    padding: 40px;
    color: #999;
    font-style: italic;
}

.pfob-error {
    text-align: center;
    padding: 40px;
    color: #dc3545;
}

@media (max-width: 768px) {
    .pfob-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .pfob-stats-grid {
        grid-template-columns: 1fr;
    }

    .pfob-analytics-section {
        padding: 20px;
    }

    .pfob-table {
        font-size: 14px;
    }

    .pfob-table th,
    .pfob-table td {
        padding: 8px;
    }
}
</style>

<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
