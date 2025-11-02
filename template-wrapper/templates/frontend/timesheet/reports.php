<?php
/**
 * Timesheet Reports
 *
 * Comprehensive time tracking reports and analytics
 */
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

PFOB_Template::header('Time Reports');
?>

<div class="timesheet-reports-wrapper">
    <a href="<?php echo home_url('/projectfob/timesheet'); ?>" class="back-link">← Back to Timesheet</a>

    <h1 class="page-title">📊 Time Reports</h1>
    <p class="page-subtitle">Comprehensive time tracking reports and analytics for your projects.</p>

    <!-- Filters -->
    <div class="reports-section">
        <h2 class="section-title">Report Filters</h2>

        <div class="filters-grid">
            <div class="filter-item">
                <label class="filter-label">Date Range</label>
                <select id="filter-date" class="filter-select">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month" selected>This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range...</option>
                </select>
            </div>

            <div class="filter-item">
                <label class="filter-label">Project</label>
                <select id="filter-project" class="filter-select">
                    <option value="">All Projects</option>
                    <option value="1">Website Redesign</option>
                    <option value="2">Mobile App</option>
                    <option value="3">Marketing Campaign</option>
                    <option value="4">Internal Tools</option>
                </select>
            </div>

            <div class="filter-item">
                <label class="filter-label">Team Member</label>
                <select id="filter-user" class="filter-select">
                    <option value="">All Team Members</option>
                    <option value="1">John Doe</option>
                    <option value="2">Jane Smith</option>
                    <option value="3">Mike Johnson</option>
                </select>
            </div>

            <div class="filter-item">
                <label class="filter-label">Billable</label>
                <select id="filter-billable" class="filter-select">
                    <option value="">All Time</option>
                    <option value="yes">Billable Only</option>
                    <option value="no">Non-Billable Only</option>
                </select>
            </div>
        </div>

        <div class="filter-actions">
            <button class="action-button primary-button" onclick="applyFilters()">Apply Filters</button>
            <button class="action-button secondary-button" onclick="resetFilters()">Reset</button>
            <button class="action-button secondary-button" onclick="exportReport()">📥 Export</button>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="reports-section">
        <h2 class="section-title">Summary Statistics</h2>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">⏱️</div>
                <div class="stat-value" id="stat-total-hours">0h</div>
                <div class="stat-label">Total Hours</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value" id="stat-billable-hours">0h</div>
                <div class="stat-label">Billable Hours</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-value" id="stat-projects">0</div>
                <div class="stat-label">Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value" id="stat-team-members">0</div>
                <div class="stat-label">Team Members</div>
            </div>
        </div>
    </div>

    <!-- Time by Project Chart -->
    <div class="reports-section">
        <h2 class="section-title">Time by Project</h2>
        <div id="project-chart" class="chart-container">
            <canvas id="projectChart"></canvas>
        </div>
    </div>

    <!-- Time by Day Chart -->
    <div class="reports-section">
        <h2 class="section-title">Daily Breakdown</h2>
        <div id="daily-chart" class="chart-container">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

    <!-- Top Users -->
    <div class="reports-section">
        <h2 class="section-title">Top Contributors</h2>

        <div class="users-list">
            <div class="user-row">
                <div class="user-info">
                    <div class="user-avatar">JD</div>
                    <div class="user-details">
                        <div class="user-name">John Doe</div>
                        <div class="user-role">Senior Developer</div>
                    </div>
                </div>
                <div class="user-stats">
                    <div class="user-hours">42.5 hrs</div>
                    <div class="user-bar">
                        <div class="user-bar-fill" style="width: 85%"></div>
                    </div>
                </div>
            </div>

            <div class="user-row">
                <div class="user-info">
                    <div class="user-avatar">JS</div>
                    <div class="user-details">
                        <div class="user-name">Jane Smith</div>
                        <div class="user-role">Project Manager</div>
                    </div>
                </div>
                <div class="user-stats">
                    <div class="user-hours">38.0 hrs</div>
                    <div class="user-bar">
                        <div class="user-bar-fill" style="width: 76%"></div>
                    </div>
                </div>
            </div>

            <div class="user-row">
                <div class="user-info">
                    <div class="user-avatar">MJ</div>
                    <div class="user-details">
                        <div class="user-name">Mike Johnson</div>
                        <div class="user-role">Designer</div>
                    </div>
                </div>
                <div class="user-stats">
                    <div class="user-hours">35.5 hrs</div>
                    <div class="user-bar">
                        <div class="user-bar-fill" style="width: 71%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Time Entries -->
    <div class="reports-section">
        <h2 class="section-title">Detailed Time Entries</h2>

        <div class="entries-table-wrapper">
            <table class="entries-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Project</th>
                        <th>Task</th>
                        <th>Duration</th>
                        <th>Billable</th>
                    </tr>
                </thead>
                <tbody id="entries-tbody">
                    <tr>
                        <td colspan="6" class="loading-cell">Loading entries...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.timesheet-reports-wrapper {
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

.reports-section {
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

/* Filters */
.filters-grid {
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

.filter-actions {
    padding-top: 16px;
    border-top: 1px solid #e0e0e0;
}

.action-button {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    margin-right: 8px;
    margin-bottom: 8px;
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

.stat-icon {
    font-size: 36px;
    margin-bottom: 12px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #0066cc;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    color: #666;
}

/* Charts */
.chart-container {
    min-height: 300px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 6px;
}

/* Users List */
.users-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.user-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 6px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #0066cc;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
}

.user-details {
    flex: 1;
}

.user-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.user-role {
    font-size: 14px;
    color: #666;
}

.user-stats {
    text-align: right;
    min-width: 150px;
}

.user-hours {
    font-size: 18px;
    font-weight: 600;
    color: #0066cc;
    margin-bottom: 8px;
}

.user-bar {
    width: 150px;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.user-bar-fill {
    height: 100%;
    background: #0066cc;
    border-radius: 4px;
}

/* Table */
.entries-table-wrapper {
    overflow-x: auto;
}

.entries-table {
    width: 100%;
    border-collapse: collapse;
}

.entries-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
}

.entries-table td {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
    color: #333;
}

.entries-table tr:hover {
    background: #f8f9fa;
}

.loading-cell {
    text-align: center;
    padding: 40px !important;
    color: #666;
}

.billable-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.billable-yes {
    background: #d4edda;
    color: #155724;
}

.billable-no {
    background: #f8d7da;
    color: #721c24;
}

@media (max-width: 768px) {
    .timesheet-reports-wrapper {
        padding: 12px;
    }

    .reports-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }

    .user-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .user-stats {
        width: 100%;
    }

    .user-bar {
        width: 100%;
    }

    .entries-table {
        font-size: 14px;
    }

    .entries-table th,
    .entries-table td {
        padding: 8px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadReportData();
    initializeCharts();

    window.applyFilters = applyFilters;
    window.resetFilters = resetFilters;
    window.exportReport = exportReport;
});

async function loadReportData() {
    try {
        const response = await fetch(`${pfobData.restUrl}/timesheet/reports`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            updateStatistics(data.stats);
            updateEntries(data.entries);
        } else {
            // Load placeholder data
            loadPlaceholderData();
        }
    } catch (error) {
        console.error('Error:', error);
        loadPlaceholderData();
    }
}

function loadPlaceholderData() {
    // Update statistics
    document.getElementById('stat-total-hours').textContent = '132.5h';
    document.getElementById('stat-billable-hours').textContent = '98.0h';
    document.getElementById('stat-projects').textContent = '5';
    document.getElementById('stat-team-members').textContent = '8';

    // Update entries table
    const entries = [
        { date: 'Nov 2, 2025', user: 'John Doe', project: 'Website Redesign', task: 'Frontend Development', duration: '4.5h', billable: true },
        { date: 'Nov 2, 2025', user: 'Jane Smith', project: 'Mobile App', task: 'UI Design', duration: '3.0h', billable: true },
        { date: 'Nov 1, 2025', user: 'Mike Johnson', project: 'Marketing Campaign', task: 'Content Writing', duration: '2.5h', billable: false },
        { date: 'Nov 1, 2025', user: 'John Doe', project: 'Website Redesign', task: 'Bug Fixes', duration: '5.0h', billable: true },
        { date: 'Nov 1, 2025', user: 'Sarah Williams', project: 'Internal Tools', task: 'Database Optimization', duration: '6.0h', billable: false },
        { date: 'Oct 31, 2025', user: 'Jane Smith', project: 'Mobile App', task: 'User Testing', duration: '4.0h', billable: true },
        { date: 'Oct 31, 2025', user: 'Tom Brown', project: 'Website Redesign', task: 'Testing', duration: '3.5h', billable: true },
        { date: 'Oct 30, 2025', user: 'John Doe', project: 'Website Redesign', task: 'Code Review', duration: '2.0h', billable: true }
    ];

    const tbody = document.getElementById('entries-tbody');
    tbody.innerHTML = entries.map(entry => `
        <tr>
            <td>${entry.date}</td>
            <td>${entry.user}</td>
            <td>${entry.project}</td>
            <td>${entry.task}</td>
            <td><strong>${entry.duration}</strong></td>
            <td><span class="billable-badge ${entry.billable ? 'billable-yes' : 'billable-no'}">${entry.billable ? 'Billable' : 'Non-Billable'}</span></td>
        </tr>
    `).join('');
}

function initializeCharts() {
    // Simple bar chart for projects
    const projectCtx = document.getElementById('projectChart');
    if (projectCtx) {
        const projectData = {
            'Website Redesign': 45.5,
            'Mobile App': 32.0,
            'Marketing Campaign': 28.5,
            'Internal Tools': 18.0,
            'Client Portal': 8.5
        };

        renderBarChart(projectCtx, projectData, 'Hours by Project');
    }

    // Daily breakdown
    const dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx) {
        const dailyData = {
            'Mon': 26.5,
            'Tue': 28.0,
            'Wed': 25.5,
            'Thu': 30.0,
            'Fri': 22.5,
            'Sat': 0,
            'Sun': 0
        };

        renderBarChart(dailyCtx, dailyData, 'Hours by Day');
    }
}

function renderBarChart(canvas, data, title) {
    const ctx = canvas.getContext('2d');
    const labels = Object.keys(data);
    const values = Object.values(data);
    const max = Math.max(...values);

    const width = canvas.parentElement.clientWidth - 40;
    const height = 250;
    canvas.width = width;
    canvas.height = height;

    const barWidth = (width - 60) / labels.length;
    const padding = 40;

    // Draw bars
    labels.forEach((label, i) => {
        const barHeight = (values[i] / max) * (height - 60);
        const x = padding + (i * barWidth);
        const y = height - padding - barHeight;

        // Bar
        ctx.fillStyle = '#0066cc';
        ctx.fillRect(x, y, barWidth - 10, barHeight);

        // Label
        ctx.fillStyle = '#333';
        ctx.font = '12px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(label, x + (barWidth - 10) / 2, height - 20);

        // Value
        ctx.fillStyle = '#666';
        ctx.font = '11px sans-serif';
        ctx.fillText(values[i] + 'h', x + (barWidth - 10) / 2, y - 5);
    });

    // Title
    ctx.fillStyle = '#333';
    ctx.font = 'bold 14px sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText(title, 10, 20);
}

function updateStatistics(stats) {
    document.getElementById('stat-total-hours').textContent = stats.total_hours + 'h';
    document.getElementById('stat-billable-hours').textContent = stats.billable_hours + 'h';
    document.getElementById('stat-projects').textContent = stats.projects;
    document.getElementById('stat-team-members').textContent = stats.team_members;
}

function updateEntries(entries) {
    const tbody = document.getElementById('entries-tbody');

    if (entries.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="loading-cell">No entries found for the selected filters.</td></tr>';
        return;
    }

    tbody.innerHTML = entries.map(entry => `
        <tr>
            <td>${entry.date}</td>
            <td>${entry.user}</td>
            <td>${entry.project}</td>
            <td>${entry.task}</td>
            <td><strong>${entry.duration}</strong></td>
            <td><span class="billable-badge ${entry.billable ? 'billable-yes' : 'billable-no'}">${entry.billable ? 'Billable' : 'Non-Billable'}</span></td>
        </tr>
    `).join('');
}

function applyFilters() {
    loadReportData();
}

function resetFilters() {
    document.getElementById('filter-date').value = 'month';
    document.getElementById('filter-project').value = '';
    document.getElementById('filter-user').value = '';
    document.getElementById('filter-billable').value = '';
    loadReportData();
}

function exportReport() {
    const dateRange = document.getElementById('filter-date').value;
    const project = document.getElementById('filter-project').value;
    const user = document.getElementById('filter-user').value;
    const billable = document.getElementById('filter-billable').value;

    alert('Export functionality will generate a downloadable report with:\n\n' +
        '• All filtered time entries\n' +
        '• Summary statistics\n' +
        '• Charts and graphs\n\n' +
        'Format: CSV or Excel');
}
</script>

<?php
PFOB_Template::footer();
