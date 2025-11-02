<?php
/**
 * Timesheet Export
 *
 * Export time tracking data in multiple formats
 */
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

PFOB_Template::header('Export Time Data');
?>

<div class="timesheet-export-wrapper">
    <a href="<?php echo home_url('/projectfob/timesheet'); ?>" class="back-link">← Back to Timesheet</a>

    <h1 class="page-title">📥 Export Time Data</h1>
    <p class="page-subtitle">Download your time tracking data in various formats for reporting and analysis.</p>

    <!-- Export Configuration -->
    <div class="export-section">
        <h2 class="section-title">Export Configuration</h2>

        <!-- Format Selection -->
        <div class="config-row">
            <div class="config-label">Export Format</div>
            <div class="format-options">
                <label class="format-option">
                    <input type="radio" name="format" value="csv" checked>
                    <div class="format-card">
                        <div class="format-icon">📄</div>
                        <div class="format-name">CSV</div>
                        <div class="format-desc">Comma-separated values for Excel, Google Sheets</div>
                    </div>
                </label>

                <label class="format-option">
                    <input type="radio" name="format" value="excel">
                    <div class="format-card">
                        <div class="format-icon">📊</div>
                        <div class="format-name">Excel (.xlsx)</div>
                        <div class="format-desc">Formatted Excel workbook with charts</div>
                    </div>
                </label>

                <label class="format-option">
                    <input type="radio" name="format" value="pdf">
                    <div class="format-card">
                        <div class="format-icon">📑</div>
                        <div class="format-name">PDF Report</div>
                        <div class="format-desc">Professional formatted PDF document</div>
                    </div>
                </label>

                <label class="format-option">
                    <input type="radio" name="format" value="json">
                    <div class="format-card">
                        <div class="format-icon">💾</div>
                        <div class="format-name">JSON</div>
                        <div class="format-desc">Raw data for API integrations</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Date Range -->
        <div class="config-row">
            <div class="config-label">Date Range</div>
            <div class="date-range-options">
                <select id="date-preset" class="config-select" onchange="handlePresetChange()">
                    <option value="">Quick Select...</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="week">This Week</option>
                    <option value="last_week">Last Week</option>
                    <option value="month" selected>This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range...</option>
                </select>

                <div class="custom-dates">
                    <div class="date-input-group">
                        <label class="date-label">From</label>
                        <input type="date" id="date-from" class="date-input" value="2025-10-01">
                    </div>
                    <div class="date-input-group">
                        <label class="date-label">To</label>
                        <input type="date" id="date-to" class="date-input" value="2025-10-31">
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="config-row">
            <div class="config-label">Filters</div>
            <div class="filter-options">
                <div class="filter-item">
                    <label class="filter-label">Project</label>
                    <select id="filter-project" class="config-select">
                        <option value="">All Projects</option>
                        <option value="1">Website Redesign</option>
                        <option value="2">Mobile App</option>
                        <option value="3">Marketing Campaign</option>
                        <option value="4">Internal Tools</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label class="filter-label">Team Member</label>
                    <select id="filter-user" class="config-select">
                        <option value="">All Team Members</option>
                        <option value="1">John Doe</option>
                        <option value="2">Jane Smith</option>
                        <option value="3">Mike Johnson</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label class="filter-label">Billable</label>
                    <select id="filter-billable" class="config-select">
                        <option value="">All Time</option>
                        <option value="yes">Billable Only</option>
                        <option value="no">Non-Billable Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="config-row">
            <div class="config-label">Include in Export</div>
            <div class="export-options">
                <label class="export-option">
                    <input type="checkbox" checked> Time Entries
                </label>
                <label class="export-option">
                    <input type="checkbox" checked> Summary Statistics
                </label>
                <label class="export-option">
                    <input type="checkbox"> Charts & Graphs
                </label>
                <label class="export-option">
                    <input type="checkbox"> Task Descriptions
                </label>
                <label class="export-option">
                    <input type="checkbox" checked> Billable Amounts
                </label>
                <label class="export-option">
                    <input type="checkbox"> User Details
                </label>
            </div>
        </div>
    </div>

    <!-- Preview -->
    <div class="export-section">
        <h2 class="section-title">Export Preview</h2>

        <div class="preview-stats">
            <div class="preview-stat">
                <div class="preview-stat-value" id="preview-entries">0</div>
                <div class="preview-stat-label">Time Entries</div>
            </div>
            <div class="preview-stat">
                <div class="preview-stat-value" id="preview-hours">0h</div>
                <div class="preview-stat-label">Total Hours</div>
            </div>
            <div class="preview-stat">
                <div class="preview-stat-value" id="preview-amount">$0</div>
                <div class="preview-stat-label">Billable Amount</div>
            </div>
            <div class="preview-stat">
                <div class="preview-stat-value" id="preview-projects">0</div>
                <div class="preview-stat-label">Projects</div>
            </div>
        </div>

        <div class="preview-table">
            <h3 class="preview-title">Sample Data (First 5 Entries)</h3>
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
                <tbody id="preview-tbody">
                    <tr>
                        <td colspan="6" class="loading-cell">Click "Generate Export" to preview data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="export-actions">
        <button class="action-button primary-button" onclick="generateExport()">🚀 Generate Export</button>
        <button class="action-button secondary-button" onclick="previewData()">👁️ Preview Data</button>
        <button class="action-button secondary-button" onclick="resetExport()">🔄 Reset</button>
    </div>

    <!-- Recent Exports -->
    <div class="export-section">
        <h2 class="section-title">Recent Exports</h2>

        <div class="recent-exports">
            <div class="export-item">
                <div class="export-info">
                    <div class="export-name">📄 October_2025_Timesheet.csv</div>
                    <div class="export-meta">Generated on Nov 1, 2025 • 156 entries • 132.5 hours</div>
                </div>
                <button class="action-button secondary-button small-button">Download Again</button>
            </div>

            <div class="export-item">
                <div class="export-info">
                    <div class="export-name">📊 Q3_2025_Report.xlsx</div>
                    <div class="export-meta">Generated on Oct 15, 2025 • 487 entries • 398.0 hours</div>
                </div>
                <button class="action-button secondary-button small-button">Download Again</button>
            </div>

            <div class="export-item">
                <div class="export-info">
                    <div class="export-name">📑 Client_Billing_September.pdf</div>
                    <div class="export-meta">Generated on Oct 1, 2025 • 98 entries • 89.5 hours</div>
                </div>
                <button class="action-button secondary-button small-button">Download Again</button>
            </div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.timesheet-export-wrapper {
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

.export-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    margin: 0 0 24px 0;
    color: #333;
    padding-bottom: 12px;
    border-bottom: 2px solid #0066cc;
}

/* Configuration Rows */
.config-row {
    margin-bottom: 32px;
}

.config-row:last-child {
    margin-bottom: 0;
}

.config-label {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 16px;
}

/* Format Options */
.format-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.format-option {
    cursor: pointer;
}

.format-option input[type="radio"] {
    display: none;
}

.format-card {
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: all 0.2s;
}

.format-option input[type="radio"]:checked + .format-card {
    border-color: #0066cc;
    background: #f0f7ff;
}

.format-card:hover {
    border-color: #0066cc;
}

.format-icon {
    font-size: 36px;
    margin-bottom: 12px;
}

.format-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.format-desc {
    font-size: 13px;
    color: #666;
}

/* Date Range */
.date-range-options {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.custom-dates {
    display: flex;
    gap: 16px;
}

.date-input-group {
    flex: 1;
}

.date-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.date-input,
.config-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
}

.date-input:focus,
.config-select:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

/* Filters */
.filter-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
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

/* Export Options */
.export-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.export-option {
    display: flex;
    align-items: center;
    font-size: 15px;
    color: #333;
    cursor: pointer;
}

.export-option input {
    margin-right: 8px;
    cursor: pointer;
}

/* Preview Stats */
.preview-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.preview-stat {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
}

.preview-stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #0066cc;
    margin-bottom: 8px;
}

.preview-stat-label {
    font-size: 13px;
    color: #666;
}

/* Preview Table */
.preview-table {
    overflow-x: auto;
}

.preview-title {
    font-size: 14px;
    font-weight: 600;
    color: #666;
    margin-bottom: 12px;
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
    font-size: 14px;
}

.entries-table td {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
    color: #333;
    font-size: 14px;
}

.entries-table tr:hover {
    background: #f8f9fa;
}

.loading-cell {
    text-align: center;
    padding: 40px !important;
    color: #666;
    font-style: italic;
}

/* Actions */
.export-actions {
    display: flex;
    gap: 12px;
    padding: 24px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 24px;
}

.action-button {
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    border: none;
}

.primary-button {
    background: #10b981;
    color: white;
}

.primary-button:hover {
    background: #059669;
}

.secondary-button {
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
}

.secondary-button:hover {
    background: #d1d5db;
}

.small-button {
    padding: 6px 12px;
    font-size: 14px;
}

/* Recent Exports */
.recent-exports {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.export-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 6px;
}

.export-info {
    flex: 1;
}

.export-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.export-meta {
    font-size: 13px;
    color: #666;
}

@media (max-width: 768px) {
    .timesheet-export-wrapper {
        padding: 12px;
    }

    .export-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }

    .format-options {
        grid-template-columns: 1fr;
    }

    .custom-dates {
        flex-direction: column;
    }

    .filter-options {
        grid-template-columns: 1fr;
    }

    .export-actions {
        flex-direction: column;
    }

    .action-button {
        width: 100%;
    }

    .export-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .small-button {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.generateExport = generateExport;
    window.previewData = previewData;
    window.resetExport = resetExport;
    window.handlePresetChange = handlePresetChange;

    // Initial preview
    previewData();
});

function handlePresetChange() {
    const preset = document.getElementById('date-preset').value;
    const today = new Date();
    let fromDate, toDate;

    switch (preset) {
        case 'today':
            fromDate = toDate = today;
            break;
        case 'yesterday':
            fromDate = toDate = new Date(today.setDate(today.getDate() - 1));
            break;
        case 'week':
            fromDate = new Date(today.setDate(today.getDate() - today.getDay()));
            toDate = new Date();
            break;
        case 'month':
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
            toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        default:
            return;
    }

    document.getElementById('date-from').value = formatDate(fromDate);
    document.getElementById('date-to').value = formatDate(toDate);

    previewData();
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function previewData() {
    // Update preview stats with placeholder data
    document.getElementById('preview-entries').textContent = '156';
    document.getElementById('preview-hours').textContent = '132.5h';
    document.getElementById('preview-amount').textContent = '$9,937.50';
    document.getElementById('preview-projects').textContent = '5';

    // Update preview table
    const sampleData = [
        { date: 'Oct 31, 2025', user: 'John Doe', project: 'Website Redesign', task: 'Frontend Development', duration: '4.5h', billable: '$337.50' },
        { date: 'Oct 31, 2025', user: 'Jane Smith', project: 'Mobile App', task: 'UI Design', duration: '3.0h', billable: '$225.00' },
        { date: 'Oct 30, 2025', user: 'Mike Johnson', project: 'Marketing Campaign', task: 'Content Writing', duration: '2.5h', billable: '$0.00' },
        { date: 'Oct 30, 2025', user: 'John Doe', project: 'Website Redesign', task: 'Bug Fixes', duration: '5.0h', billable: '$375.00' },
        { date: 'Oct 29, 2025', user: 'Jane Smith', project: 'Mobile App', task: 'User Testing', duration: '4.0h', billable: '$300.00' }
    ];

    const tbody = document.getElementById('preview-tbody');
    tbody.innerHTML = sampleData.map(entry => `
        <tr>
            <td>${entry.date}</td>
            <td>${entry.user}</td>
            <td>${entry.project}</td>
            <td>${entry.task}</td>
            <td><strong>${entry.duration}</strong></td>
            <td><strong>${entry.billable}</strong></td>
        </tr>
    `).join('');
}

async function generateExport() {
    const format = document.querySelector('input[name="format"]:checked').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    const project = document.getElementById('filter-project').value;
    const user = document.getElementById('filter-user').value;
    const billable = document.getElementById('filter-billable').value;

    const exportData = {
        format: format,
        date_from: dateFrom,
        date_to: dateTo,
        project: project,
        user: user,
        billable: billable
    };

    try {
        const response = await fetch(`${pfobData.restUrl}/timesheet/export`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(exportData)
        });

        const data = await response.json();

        if (data.success && data.download_url) {
            window.location.href = data.download_url;
        } else {
            throw new Error('Export failed');
        }
    } catch (error) {
        console.error('Error:', error);

        // Simulate download
        const formatNames = {
            'csv': 'Timesheet_Export.csv',
            'excel': 'Timesheet_Export.xlsx',
            'pdf': 'Timesheet_Report.pdf',
            'json': 'Timesheet_Data.json'
        };

        const filename = formatNames[format] || 'export.txt';

        alert(`🚀 Export generated successfully!\n\n` +
            `Format: ${format.toUpperCase()}\n` +
            `Filename: ${filename}\n` +
            `Date Range: ${dateFrom} to ${dateTo}\n` +
            `Entries: 156\n` +
            `Total Hours: 132.5h\n` +
            `Billable Amount: $9,937.50\n\n` +
            `(Download functionality will be available when backend API is implemented)`);
    }
}

function resetExport() {
    document.querySelector('input[name="format"][value="csv"]').checked = true;
    document.getElementById('date-preset').value = 'month';
    document.getElementById('date-from').value = '2025-10-01';
    document.getElementById('date-to').value = '2025-10-31';
    document.getElementById('filter-project').value = '';
    document.getElementById('filter-user').value = '';
    document.getElementById('filter-billable').value = '';

    previewData();
}
</script>

<?php
PFOB_Template::footer();
