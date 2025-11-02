<?php
/**
 * Export Data Page
 *
 * Allows account owners to export all data from their account.
 * Generates organized tarballs with projects, messages, files, etc.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'Export Data' );
?>

<div class="export-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Export Data</h1>
    <p class="page-subtitle">Download a complete backup of all your ProjectFOB data in an organized archive.</p>

    <!-- Export Options -->
    <div class="export-section">
        <h2 class="section-title">What to Export</h2>

        <div class="export-options">
            <label class="option-checkbox">
                <input type="checkbox" id="export-projects" checked>
                <span class="option-label">Projects</span>
                <span class="option-description">All project data including settings, members, and metadata</span>
            </label>

            <label class="option-checkbox">
                <input type="checkbox" id="export-messages" checked>
                <span class="option-label">Messages & Discussions</span>
                <span class="option-description">All message boards and comment threads</span>
            </label>

            <label class="option-checkbox">
                <input type="checkbox" id="export-todos" checked>
                <span class="option-label">To-dos & Tasks</span>
                <span class="option-description">All to-do lists and completed tasks</span>
            </label>

            <label class="option-checkbox">
                <input type="checkbox" id="export-documents" checked>
                <span class="option-label">Documents & Files</span>
                <span class="option-description">All uploaded files and documents (may be large)</span>
            </label>

            <label class="option-checkbox">
                <input type="checkbox" id="export-schedule" checked>
                <span class="option-label">Schedule & Events</span>
                <span class="option-description">All calendar events and schedules</span>
            </label>

            <label class="option-checkbox">
                <input type="checkbox" id="export-chat" checked>
                <span class="option-label">Chat History</span>
                <span class="option-description">All chat conversations</span>
            </label>

            <label class="option-checkbox">
                <input type="checkbox" id="export-people" checked>
                <span class="option-label">People & Teams</span>
                <span class="option-description">User information, groups, and companies</span>
            </label>
        </div>
    </div>

    <!-- Export Format -->
    <div class="export-section">
        <h2 class="section-title">Export Format</h2>

        <div class="format-options">
            <label class="format-radio">
                <input type="radio" name="export-format" value="json" checked>
                <span class="format-name">JSON + Files</span>
                <span class="format-description">Structured JSON data with separate files folder (recommended)</span>
            </label>

            <label class="format-radio">
                <input type="radio" name="export-format" value="csv">
                <span class="format-name">CSV + Files</span>
                <span class="format-description">Spreadsheet-compatible CSV files with attachments</span>
            </label>

            <label class="format-radio">
                <input type="radio" name="export-format" value="html">
                <span class="format-name">HTML Archive</span>
                <span class="format-description">Browsable HTML version of your data</span>
            </label>
        </div>
    </div>

    <!-- Export Status -->
    <div class="export-section">
        <h2 class="section-title">Start Export</h2>

        <div id="export-status" class="export-status">
            <p>Click the button below to generate your export. This may take several minutes depending on the amount of data.</p>
        </div>

        <button class="export-button primary-button" onclick="startExport()">Generate Export Archive</button>

        <div id="export-progress" class="export-progress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <div class="progress-text" id="progress-text">Preparing export...</div>
        </div>

        <div id="export-result" class="export-result" style="display: none;">
            <div class="result-success">
                <h3>Export Complete!</h3>
                <p>Your data has been exported successfully.</p>
                <div class="download-info">
                    <div class="info-row">
                        <strong>File:</strong> <span id="export-filename"></span>
                    </div>
                    <div class="info-row">
                        <strong>Size:</strong> <span id="export-size"></span>
                    </div>
                    <div class="info-row">
                        <strong>Created:</strong> <span id="export-date"></span>
                    </div>
                </div>
                <a href="#" class="download-button primary-button" id="download-link">Download Archive</a>
            </div>
        </div>
    </div>

    <!-- Recent Exports -->
    <div class="export-section">
        <h2 class="section-title">Recent Exports</h2>

        <div id="recent-exports" class="recent-exports">
            <div class="loading-message">Loading recent exports...</div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.export-page-wrapper {
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

.export-section {
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

.export-options {
    margin-bottom: 16px;
}

.option-checkbox {
    display: block;
    padding: 16px;
    margin-bottom: 12px;
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.option-checkbox:hover {
    border-color: #0066cc;
    background: #f0f7ff;
}

.option-checkbox input[type="checkbox"] {
    margin-right: 12px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.option-label {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.option-description {
    display: block;
    font-size: 14px;
    color: #666;
    margin-left: 30px;
}

.format-options {
    margin-bottom: 16px;
}

.format-radio {
    display: block;
    padding: 16px;
    margin-bottom: 12px;
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.format-radio:hover {
    border-color: #0066cc;
    background: #f0f7ff;
}

.format-radio input[type="radio"] {
    margin-right: 12px;
    cursor: pointer;
}

.format-name {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.format-description {
    display: block;
    font-size: 14px;
    color: #666;
    margin-left: 26px;
}

.export-status {
    margin-bottom: 20px;
    padding: 16px;
    background: #f0f7ff;
    border-left: 4px solid #0066cc;
    border-radius: 4px;
}

.export-status p {
    margin: 0;
    color: #333;
}

.export-button {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.primary-button {
    background: #0066cc;
    color: white;
}

.primary-button:hover {
    background: #0052a3;
}

.export-progress {
    margin-top: 24px;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 12px;
}

.progress-fill {
    height: 100%;
    background: #0066cc;
    width: 0%;
    transition: width 0.3s;
}

.progress-text {
    text-align: center;
    font-size: 14px;
    color: #666;
}

.export-result {
    margin-top: 24px;
}

.result-success {
    padding: 24px;
    background: #ecfdf5;
    border: 2px solid #10b981;
    border-radius: 8px;
}

.result-success h3 {
    margin: 0 0 12px 0;
    color: #065f46;
    font-size: 20px;
}

.result-success p {
    margin: 0 0 16px 0;
    color: #047857;
}

.download-info {
    margin-bottom: 20px;
}

.info-row {
    padding: 8px 0;
    font-size: 14px;
    color: #333;
}

.info-row strong {
    display: inline-block;
    min-width: 80px;
    color: #666;
}

.download-button {
    display: inline-block;
    padding: 12px 24px;
    background: #10b981;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
}

.download-button:hover {
    background: #059669;
}

.recent-exports {
    min-height: 100px;
}

.export-item {
    padding: 16px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 12px;
}

.export-item-header {
    margin-bottom: 8px;
}

.export-filename {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.export-meta {
    font-size: 13px;
    color: #666;
    margin-bottom: 12px;
}

.export-actions a {
    display: inline-block;
    padding: 6px 12px;
    margin-right: 8px;
    background: #0066cc;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
}

.export-actions a:hover {
    background: #0052a3;
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
    .export-page-wrapper {
        padding: 12px;
    }

    .export-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadRecentExports();

    window.startExport = startExport;
});

async function loadRecentExports() {
    try {
        const response = await fetch(`${pfobData.restUrl}/account/exports`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success && data.exports) {
            renderExports(data.exports);
        } else {
            document.getElementById('recent-exports').innerHTML = '<div class="empty-message">No previous exports</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('recent-exports').innerHTML = '<div class="empty-message">No previous exports</div>';
    }
}

function renderExports(exports) {
    if (exports.length === 0) {
        document.getElementById('recent-exports').innerHTML = '<div class="empty-message">No previous exports</div>';
        return;
    }

    const html = exports.map(exp => `
        <div class="export-item">
            <div class="export-item-header">
                <div class="export-filename">${exp.filename}</div>
            </div>
            <div class="export-meta">
                Created ${exp.created_at} • ${exp.size} • ${exp.format.toUpperCase()}
            </div>
            <div class="export-actions">
                <a href="${exp.download_url}">Download</a>
                ${exp.expires_at ? `<span style="font-size: 12px; color: #999;">Expires ${exp.expires_at}</span>` : ''}
            </div>
        </div>
    `).join('');

    document.getElementById('recent-exports').innerHTML = html;
}

async function startExport() {
    // Collect selected options
    const options = {
        projects: document.getElementById('export-projects').checked,
        messages: document.getElementById('export-messages').checked,
        todos: document.getElementById('export-todos').checked,
        documents: document.getElementById('export-documents').checked,
        schedule: document.getElementById('export-schedule').checked,
        chat: document.getElementById('export-chat').checked,
        people: document.getElementById('export-people').checked,
        format: document.querySelector('input[name="export-format"]:checked').value
    };

    // Show progress
    document.getElementById('export-progress').style.display = 'block';
    document.getElementById('export-result').style.display = 'none';
    document.querySelector('.export-button').disabled = true;

    try {
        const response = await fetch(`${pfobData.restUrl}/account/export`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(options)
        });

        const data = await response.json();

        if (data.success) {
            // Simulate progress (in real implementation, use polling)
            simulateProgress(data);
        } else {
            alert('Export failed: ' + (data.message || 'Unknown error'));
            resetExportUI();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Export API not yet implemented. This will generate a complete tarball of your data when the backend is ready.');
        resetExportUI();
    }
}

function simulateProgress(data) {
    let progress = 0;
    const interval = setInterval(() => {
        progress += 5;
        document.getElementById('progress-fill').style.width = progress + '%';

        if (progress >= 30 && progress < 60) {
            document.getElementById('progress-text').textContent = 'Collecting data...';
        } else if (progress >= 60 && progress < 90) {
            document.getElementById('progress-text').textContent = 'Creating archive...';
        } else if (progress >= 90 && progress < 100) {
            document.getElementById('progress-text').textContent = 'Finalizing...';
        }

        if (progress >= 100) {
            clearInterval(interval);
            showExportComplete(data);
        }
    }, 200);
}

function showExportComplete(data) {
    document.getElementById('export-progress').style.display = 'none';
    document.getElementById('export-result').style.display = 'block';

    const filename = data.filename || 'projectfob-export-' + new Date().getTime() + '.tar.gz';
    const size = data.size || '12.5 MB';
    const date = new Date().toLocaleString();

    document.getElementById('export-filename').textContent = filename;
    document.getElementById('export-size').textContent = size;
    document.getElementById('export-date').textContent = date;
    document.getElementById('download-link').href = data.download_url || '#';

    resetExportUI();
    loadRecentExports();
}

function resetExportUI() {
    document.querySelector('.export-button').disabled = false;
    document.getElementById('progress-fill').style.width = '0%';
}
</script>

<?php
PFOB_Template::footer();
