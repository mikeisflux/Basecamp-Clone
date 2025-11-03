<?php
/**
 * Advanced Analytics Dashboard
 *
 * Required: Business or Enterprise plan
 */

// Check if user has Advanced Analytics feature in their plan
$user_id = get_current_user_id();
$subscription = PFOB_Subscription::get_by_user_id( $user_id );

if ( ! $subscription ) {
    wp_die( __( 'Access denied. You must have an active subscription to access this page.', 'projectfob' ) );
}

$plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
$plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

// Check if plan includes Advanced Analytics feature
if ( ! $plan || empty( $plan['features']['advanced_analytics'] ) ) {
    wp_die( __( 'Access denied. Advanced Analytics is only available on Business and Enterprise plans. <a href="' . home_url( '/projectfob/adminland/billing' ) . '">Upgrade your plan</a>', 'projectfob' ) );
}

PFOB_Template::header( 'Advanced Analytics' );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-advanced-analytics">

        <header class="pfob-page-header">
            <div>
                <h1>📊 Advanced Analytics</h1>
                <p class="pfob-subtitle">Powerful insights and detailed reports for your workspace</p>
            </div>
            <div class="pfob-header-actions">
                <button id="export-csv-btn" class="pfob-btn pfob-btn-secondary">
                    📥 Export CSV
                </button>
                <button id="export-pdf-btn" class="pfob-btn pfob-btn-primary">
                    📄 Export PDF
                </button>
            </div>
        </header>

        <!-- Date Range Selector -->
        <div class="pfob-controls-bar">
            <div class="pfob-control-group">
                <label>Date Range:</label>
                <select id="date-range-select" class="pfob-select">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="180">Last 6 months</option>
                    <option value="365">Last year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div id="custom-date-range" style="display: none;">
                <input type="date" id="start-date" class="pfob-input">
                <span>to</span>
                <input type="date" id="end-date" class="pfob-input">
                <button id="apply-custom-range" class="pfob-btn pfob-btn-sm">Apply</button>
            </div>
        </div>

        <div id="loading-state" class="pfob-loading">
            <div class="pfob-spinner"></div>
            <p>Loading advanced analytics...</p>
        </div>

        <div id="analytics-content" style="display: none;">

            <!-- Key Performance Indicators -->
            <section class="pfob-analytics-section">
                <h2>📈 Key Performance Indicators</h2>
                <div class="pfob-kpi-grid" id="kpi-grid">
                    <!-- KPIs will be populated by JavaScript -->
                </div>
            </section>

            <!-- Trend Analysis -->
            <section class="pfob-analytics-section">
                <h2>📉 Trend Analysis</h2>
                <div class="pfob-chart-container">
                    <canvas id="trend-chart"></canvas>
                </div>
            </section>

            <!-- Team Performance -->
            <section class="pfob-analytics-section">
                <h2>👥 Team Performance Comparison</h2>
                <div class="pfob-chart-container">
                    <canvas id="team-chart"></canvas>
                </div>
            </section>

            <!-- Project Activity Heatmap -->
            <section class="pfob-analytics-section">
                <h2>🔥 Activity Heatmap</h2>
                <div class="pfob-heatmap" id="activity-heatmap">
                    <!-- Heatmap will be populated by JavaScript -->
                </div>
            </section>

            <!-- Detailed Reports Table -->
            <section class="pfob-analytics-section">
                <h2>📋 Detailed Reports</h2>
                <div class="pfob-table-container" id="detailed-reports">
                    <!-- Reports will be populated by JavaScript -->
                </div>
            </section>

        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

let currentDays = 30;
let customStartDate = null;
let customEndDate = null;
let charts = {};
let analyticsData = {};

// Date range selection
document.getElementById('date-range-select').addEventListener('change', (e) => {
    const value = e.target.value;
    const customRange = document.getElementById('custom-date-range');

    if (value === 'custom') {
        customRange.style.display = 'flex';
    } else {
        customRange.style.display = 'none';
        currentDays = parseInt(value);
        loadAnalytics();
    }
});

document.getElementById('apply-custom-range')?.addEventListener('click', () => {
    customStartDate = document.getElementById('start-date').value;
    customEndDate = document.getElementById('end-date').value;

    if (customStartDate && customEndDate) {
        loadAnalytics();
    } else {
        alert('Please select both start and end dates');
    }
});

// Export buttons
document.getElementById('export-csv-btn').addEventListener('click', exportToCSV);
document.getElementById('export-pdf-btn').addEventListener('click', exportToPDF);

async function loadAnalytics() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('analytics-content').style.display = 'none';

    try {
        let url = `${pfobData.restUrl}/analytics/advanced?days=${currentDays}`;

        if (customStartDate && customEndDate) {
            url += `&start=${customStartDate}&end=${customEndDate}`;
        }

        const response = await fetch(url, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            analyticsData = result.data;
            renderKPIs(result.data.kpis);
            renderTrendChart(result.data.trends);
            renderTeamChart(result.data.team);
            renderHeatmap(result.data.heatmap);
            renderDetailedReports(result.data.reports);

            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('analytics-content').style.display = 'block';
        }
    } catch (error) {
        console.error('Failed to load analytics:', error);
        document.getElementById('loading-state').innerHTML = '<p class="pfob-error">Failed to load analytics</p>';
    }
}

function renderKPIs(kpis) {
    const container = document.getElementById('kpi-grid');
    container.innerHTML = Object.entries(kpis).map(([key, data]) => `
        <div class="pfob-kpi-card ${data.trend > 0 ? 'trending-up' : 'trending-down'}">
            <div class="pfob-kpi-icon">${data.icon}</div>
            <div class="pfob-kpi-value">${data.value}</div>
            <div class="pfob-kpi-label">${data.label}</div>
            <div class="pfob-kpi-trend">
                <span class="trend-icon">${data.trend > 0 ? '↑' : '↓'}</span>
                <span class="trend-value">${Math.abs(data.trend)}%</span>
                <span class="trend-label">vs last period</span>
            </div>
        </div>
    `).join('');
}

function renderTrendChart(data) {
    const ctx = document.getElementById('trend-chart').getContext('2d');

    if (charts.trend) charts.trend.destroy();

    charts.trend = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Messages',
                data: data.messages,
                borderColor: '#2d9061',
                backgroundColor: 'rgba(45, 144, 97, 0.1)',
                tension: 0.4
            }, {
                label: 'To-dos Completed',
                data: data.todos,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }, {
                label: 'Activities',
                data: data.activities,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                title: { display: false }
            }
        }
    });
}

function renderTeamChart(data) {
    const ctx = document.getElementById('team-chart').getContext('2d');

    if (charts.team) charts.team.destroy();

    charts.team = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.members,
            datasets: [{
                label: 'Tasks Completed',
                data: data.completed,
                backgroundColor: '#2d9061'
            }, {
                label: 'Messages Posted',
                data: data.messages,
                backgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
}

function renderHeatmap(data) {
    const container = document.getElementById('activity-heatmap');
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const hours = Array.from({length: 24}, (_, i) => i);

    let html = '<div class="heatmap-grid">';
    html += '<div class="heatmap-label"></div>';
    hours.forEach(h => {
        html += `<div class="heatmap-hour">${h}:00</div>`;
    });

    days.forEach((day, dayIndex) => {
        html += `<div class="heatmap-day-label">${day}</div>`;
        hours.forEach(hour => {
            const value = data[dayIndex]?.[hour] || 0;
            const intensity = Math.min(value / 10, 1);
            html += `<div class="heatmap-cell" style="background-color: rgba(45, 144, 97, ${intensity})" title="${value} activities"></div>`;
        });
    });

    html += '</div>';
    container.innerHTML = html;
}

function renderDetailedReports(reports) {
    const container = document.getElementById('detailed-reports');

    let html = `
        <table class="pfob-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Total Activities</th>
                    <th>Messages</th>
                    <th>To-dos</th>
                    <th>Completion Rate</th>
                    <th>Avg Response Time</th>
                    <th>Most Active Member</th>
                </tr>
            </thead>
            <tbody>
    `;

    reports.forEach(report => {
        html += `
            <tr>
                <td><strong>${escapeHtml(report.project)}</strong></td>
                <td>${report.total_activities}</td>
                <td>${report.messages}</td>
                <td>${report.todos_completed}/${report.todos_total}</td>
                <td>
                    <div class="progress-mini">
                        <div class="progress-fill" style="width: ${report.completion_rate}%"></div>
                        ${report.completion_rate}%
                    </div>
                </td>
                <td>${report.avg_response_time}</td>
                <td>${escapeHtml(report.most_active)}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    container.innerHTML = html;
}

function exportToCSV() {
    if (!analyticsData.reports) return;

    let csv = 'Project,Total Activities,Messages,To-dos Completed,To-dos Total,Completion Rate,Avg Response Time,Most Active Member\n';

    analyticsData.reports.forEach(report => {
        csv += `"${report.project}",${report.total_activities},${report.messages},${report.todos_completed},${report.todos_total},${report.completion_rate}%,"${report.avg_response_time}","${report.most_active}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `advanced-analytics-${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(20);
    doc.text('Advanced Analytics Report', 20, 20);

    doc.setFontSize(12);
    doc.text(`Generated: ${new Date().toLocaleDateString()}`, 20, 30);
    doc.text(`Date Range: ${currentDays} days`, 20, 37);

    let y = 50;

    if (analyticsData.kpis) {
        doc.setFontSize(16);
        doc.text('Key Performance Indicators', 20, y);
        y += 10;

        doc.setFontSize(10);
        Object.entries(analyticsData.kpis).forEach(([key, data]) => {
            doc.text(`${data.label}: ${data.value} (${data.trend > 0 ? '+' : ''}${data.trend}%)`, 30, y);
            y += 7;
        });
    }

    doc.save(`advanced-analytics-${new Date().toISOString().split('T')[0]}.pdf`);
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
.pfob-advanced-analytics {
    max-width: 1600px;
    margin: 0 auto;
}

.pfob-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.pfob-subtitle {
    color: #666;
    margin-top: 5px;
}

.pfob-header-actions {
    display: flex;
    gap: 10px;
}

.pfob-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.pfob-btn-primary {
    background: #2d9061;
    color: white;
}

.pfob-btn-primary:hover {
    background: #247d52;
}

.pfob-btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.pfob-btn-secondary:hover {
    background: #e5e7eb;
}

.pfob-controls-bar {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    display: flex;
    gap: 20px;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pfob-control-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

#custom-date-range {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pfob-select, .pfob-input {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
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
    color: #111827;
}

.pfob-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.pfob-kpi-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 25px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s;
}

.pfob-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.pfob-kpi-card.trending-up {
    border-color: #10b981;
}

.pfob-kpi-card.trending-down {
    border-color: #ef4444;
}

.pfob-kpi-icon {
    font-size: 40px;
    margin-bottom: 10px;
}

.pfob-kpi-value {
    font-size: 36px;
    font-weight: bold;
    color: #111827;
    margin-bottom: 5px;
}

.pfob-kpi-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 10px;
}

.pfob-kpi-trend {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
}

.trending-up .trend-icon {
    color: #10b981;
}

.trending-down .trend-icon {
    color: #ef4444;
}

.pfob-chart-container {
    position: relative;
    height: 350px;
    margin-top: 20px;
}

.heatmap-grid {
    display: grid;
    grid-template-columns: 50px repeat(24, 1fr);
    gap: 2px;
}

.heatmap-label, .heatmap-hour {
    font-size: 10px;
    color: #6b7280;
    text-align: center;
    padding: 5px 2px;
}

.heatmap-day-label {
    font-size: 11px;
    color: #374151;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.heatmap-cell {
    aspect-ratio: 1;
    border-radius: 3px;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
}

.heatmap-cell:hover {
    transform: scale(1.1);
    z-index: 10;
}

.pfob-table-container {
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
    border-bottom: 1px solid #e5e7eb;
}

.pfob-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
}

.pfob-table tbody tr:hover {
    background: #f9fafb;
}

.progress-mini {
    display: flex;
    align-items: center;
    gap: 8px;
}

.progress-fill {
    height: 20px;
    background: #2d9061;
    border-radius: 10px;
    transition: width 0.3s;
}

.pfob-loading {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
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

@media (max-width: 768px) {
    .pfob-page-header {
        flex-direction: column;
        gap: 15px;
    }

    .pfob-kpi-grid {
        grid-template-columns: 1fr;
    }

    .pfob-controls-bar {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php PFOB_Template::footer(); ?>
