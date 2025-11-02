<?php
/**
 * Import/Export Settings Page
 */

$user_id = get_current_user_id();
$projects = PFOB_Project::get_user_projects( $user_id );

PFOB_Template::header( 'Import & Export' );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-import-export">

        <header class="pfob-page-header">
            <h1>Import & Export</h1>
            <p class="pfob-subtitle">Backup your data or import projects from JSON files</p>
        </header>

        <div class="pfob-settings-grid">

            <!-- Export Section -->
            <section class="pfob-settings-card">
                <div class="pfob-card-icon">📤</div>
                <h2>Export Data</h2>
                <p>Download your project data in JSON or CSV format.</p>

                <div class="pfob-export-options">
                    <h3>Export All Projects Overview</h3>
                    <p class="pfob-help-text">Download a summary of all your projects with statistics.</p>
                    <button class="pfob-btn pfob-btn-primary" id="export-all-btn">
                        Download All Projects (JSON)
                    </button>
                </div>

                <hr class="pfob-divider">

                <div class="pfob-export-options">
                    <h3>Export Individual Project</h3>
                    <p class="pfob-help-text">Export a complete project including messages, todos, documents, events, and team members.</p>

                    <div class="pfob-form-group">
                        <label for="export-project-select">Select Project:</label>
                        <select id="export-project-select" class="pfob-select">
                            <option value="">-- Choose a project --</option>
                            <?php foreach ( $projects as $project ) : ?>
                                <option value="<?php echo $project->id; ?>">
                                    <?php echo esc_html( $project->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pfob-export-buttons">
                        <button class="pfob-btn pfob-btn-primary" id="export-project-json-btn" disabled>
                            Download Project (JSON)
                        </button>
                        <button class="pfob-btn pfob-btn-secondary" id="export-todos-csv-btn" disabled>
                            Export To-dos (CSV)
                        </button>
                        <button class="pfob-btn pfob-btn-secondary" id="export-messages-csv-btn" disabled>
                            Export Messages (CSV)
                        </button>
                    </div>
                </div>
            </section>

            <!-- Import Section -->
            <section class="pfob-settings-card">
                <div class="pfob-card-icon">📥</div>
                <h2>Import Project</h2>
                <p>Import a project from a JSON export file.</p>

                <div class="pfob-import-dropzone" id="import-dropzone">
                    <div class="pfob-dropzone-content">
                        <div class="pfob-dropzone-icon">📁</div>
                        <h3>Drop JSON file here</h3>
                        <p>or click to browse</p>
                        <input type="file" id="import-file-input" accept=".json" hidden>
                    </div>
                </div>

                <div id="import-preview" class="pfob-import-preview" style="display: none;">
                    <h3>Import Preview</h3>
                    <div id="import-preview-content"></div>
                    <div class="pfob-import-actions">
                        <button class="pfob-btn pfob-btn-primary" id="confirm-import-btn">
                            Import Project
                        </button>
                        <button class="pfob-btn pfob-btn-secondary" id="cancel-import-btn">
                            Cancel
                        </button>
                    </div>
                </div>

                <div id="import-result" class="pfob-import-result" style="display: none;"></div>
            </section>

        </div>

        <!-- Data Management Tips -->
        <section class="pfob-settings-card pfob-tips-card">
            <h2>💡 Tips</h2>
            <ul class="pfob-tips-list">
                <li>
                    <strong>JSON Export:</strong> Contains complete project data including all messages, comments, todos, events, and team information. Perfect for full backups.
                </li>
                <li>
                    <strong>CSV Export:</strong> Simple spreadsheet format for todos and messages. Great for reporting and external analysis.
                </li>
                <li>
                    <strong>Import:</strong> Creates a new project from exported JSON. Original project members won't be automatically added - you'll need to add team members manually.
                </li>
                <li>
                    <strong>File Attachments:</strong> Document metadata is exported, but actual file attachments are not included in exports.
                </li>
            </ul>
        </section>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

let selectedProject = null;
let importData = null;

// Project selection
document.getElementById('export-project-select').addEventListener('change', (e) => {
    selectedProject = e.target.value;
    const hasProject = selectedProject !== '';

    document.getElementById('export-project-json-btn').disabled = !hasProject;
    document.getElementById('export-todos-csv-btn').disabled = !hasProject;
    document.getElementById('export-messages-csv-btn').disabled = !hasProject;
});

// Export all projects
document.getElementById('export-all-btn').addEventListener('click', async () => {
    window.location.href = `${pfobData.restUrl}/export/all?_wpnonce=${pfobData.nonce}`;
});

// Export project JSON
document.getElementById('export-project-json-btn').addEventListener('click', async () => {
    if (!selectedProject) return;
    window.location.href = `${pfobData.restUrl}/projects/${selectedProject}/export?_wpnonce=${pfobData.nonce}`;
});

// Export todos CSV
document.getElementById('export-todos-csv-btn').addEventListener('click', async () => {
    if (!selectedProject) return;
    window.location.href = `${pfobData.restUrl}/projects/${selectedProject}/todos/export/csv?_wpnonce=${pfobData.nonce}`;
});

// Export messages CSV
document.getElementById('export-messages-csv-btn').addEventListener('click', async () => {
    if (!selectedProject) return;
    window.location.href = `${pfobData.restUrl}/projects/${selectedProject}/messages/export/csv?_wpnonce=${pfobData.nonce}`;
});

// Import dropzone
const dropzone = document.getElementById('import-dropzone');
const fileInput = document.getElementById('import-file-input');

dropzone.addEventListener('click', () => fileInput.click());

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('pfob-dropzone-active');
});

dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('pfob-dropzone-active');
});

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('pfob-dropzone-active');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFileSelect(files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFileSelect(e.target.files[0]);
    }
});

async function handleFileSelect(file) {
    if (!file.name.endsWith('.json')) {
        alert('Please select a JSON file');
        return;
    }

    try {
        const text = await file.text();
        importData = JSON.parse(text);

        // Validate format
        if (!importData.version || !importData.project) {
            alert('Invalid export file format');
            return;
        }

        // Show preview
        showImportPreview(importData);
    } catch (error) {
        console.error('Error reading file:', error);
        alert('Failed to read file. Please make sure it\'s a valid JSON export.');
    }
}

function showImportPreview(data) {
    const preview = document.getElementById('import-preview');
    const content = document.getElementById('import-preview-content');

    const stats = {
        messages: data.messages?.length || 0,
        todos: data.todos?.length || 0,
        documents: data.documents?.length || 0,
        events: data.events?.length || 0,
        members: data.team_members?.length || 0,
    };

    content.innerHTML = `
        <div class="pfob-preview-details">
            <div class="pfob-preview-row">
                <strong>Project Name:</strong> ${escapeHtml(data.project.name)}
            </div>
            <div class="pfob-preview-row">
                <strong>Description:</strong> ${escapeHtml(data.project.description || 'None')}
            </div>
            <div class="pfob-preview-row">
                <strong>Exported:</strong> ${data.exported_at}
            </div>
            <hr>
            <div class="pfob-preview-stats">
                <div class="pfob-stat-item">
                    <span class="pfob-stat-number">${stats.messages}</span>
                    <span class="pfob-stat-label">Messages</span>
                </div>
                <div class="pfob-stat-item">
                    <span class="pfob-stat-number">${stats.todos}</span>
                    <span class="pfob-stat-label">To-dos</span>
                </div>
                <div class="pfob-stat-item">
                    <span class="pfob-stat-number">${stats.events}</span>
                    <span class="pfob-stat-label">Events</span>
                </div>
                <div class="pfob-stat-item">
                    <span class="pfob-stat-number">${stats.documents}</span>
                    <span class="pfob-stat-label">Documents</span>
                </div>
            </div>
        </div>
    `;

    preview.style.display = 'block';
    dropzone.style.display = 'none';
}

// Confirm import
document.getElementById('confirm-import-btn').addEventListener('click', async () => {
    if (!importData) return;

    const btn = document.getElementById('confirm-import-btn');
    btn.disabled = true;
    btn.textContent = 'Importing...';

    try {
        const response = await fetch(`${pfobData.restUrl}/projects/import`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(importData)
        });

        const result = await response.json();

        if (result.success) {
            showImportResult(true, result.data);
        } else {
            showImportResult(false, result.message);
            btn.disabled = false;
            btn.textContent = 'Import Project';
        }
    } catch (error) {
        console.error('Import failed:', error);
        showImportResult(false, 'Failed to import project. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Import Project';
    }
});

// Cancel import
document.getElementById('cancel-import-btn').addEventListener('click', () => {
    importData = null;
    document.getElementById('import-preview').style.display = 'none';
    dropzone.style.display = 'block';
    fileInput.value = '';
});

function showImportResult(success, data) {
    const resultDiv = document.getElementById('import-result');

    if (success) {
        resultDiv.className = 'pfob-import-result pfob-import-success';
        resultDiv.innerHTML = `
            <h3>✓ Import Successful!</h3>
            <p>Project imported successfully with:</p>
            <ul>
                <li>${data.stats.messages} messages</li>
                <li>${data.stats.todos} to-dos</li>
                <li>${data.stats.events} events</li>
                <li>${data.stats.documents} documents (metadata only)</li>
            </ul>
            <a href="/projectfob/projects/${data.project_slug}/" class="pfob-btn pfob-btn-primary">
                View Project
            </a>
        `;

        // Hide preview
        document.getElementById('import-preview').style.display = 'none';
    } else {
        resultDiv.className = 'pfob-import-result pfob-import-error';
        resultDiv.innerHTML = `
            <h3>✗ Import Failed</h3>
            <p>${escapeHtml(data)}</p>
        `;
    }

    resultDiv.style.display = 'block';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<style>
.pfob-import-export {
    max-width: 1200px;
    margin: 0 auto;
}

.pfob-settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.pfob-settings-card {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pfob-card-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.pfob-settings-card h2 {
    margin: 0 0 10px 0;
    font-size: 24px;
}

.pfob-settings-card > p {
    color: #666;
    margin-bottom: 30px;
}

.pfob-export-options {
    margin-bottom: 20px;
}

.pfob-export-options h3 {
    font-size: 16px;
    margin: 0 0 10px 0;
}

.pfob-help-text {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
}

.pfob-export-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.pfob-divider {
    border: none;
    border-top: 1px solid #e0e0e0;
    margin: 30px 0;
}

.pfob-import-dropzone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 60px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 20px;
}

.pfob-import-dropzone:hover,
.pfob-dropzone-active {
    border-color: #2d9061;
    background-color: #f0f8f4;
}

.pfob-dropzone-icon {
    font-size: 64px;
    margin-bottom: 15px;
}

.pfob-dropzone-content h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.pfob-dropzone-content p {
    color: #666;
    margin: 0;
}

.pfob-import-preview {
    margin-top: 20px;
}

.pfob-preview-details {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 20px;
}

.pfob-preview-row {
    margin-bottom: 10px;
}

.pfob-preview-row strong {
    display: inline-block;
    width: 120px;
}

.pfob-preview-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-top: 20px;
}

.pfob-stat-item {
    text-align: center;
}

.pfob-stat-number {
    display: block;
    font-size: 32px;
    font-weight: bold;
    color: #2d9061;
}

.pfob-stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.pfob-import-actions {
    display: flex;
    gap: 10px;
}

.pfob-import-result {
    padding: 20px;
    border-radius: 6px;
    margin-top: 20px;
}

.pfob-import-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.pfob-import-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.pfob-import-result h3 {
    margin-top: 0;
}

.pfob-import-result ul {
    margin: 15px 0;
}

.pfob-tips-card {
    grid-column: 1 / -1;
}

.pfob-tips-list {
    margin: 0;
    padding-left: 20px;
}

.pfob-tips-list li {
    margin-bottom: 15px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .pfob-settings-grid {
        grid-template-columns: 1fr;
    }

    .pfob-preview-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .pfob-export-buttons {
        flex-direction: column;
    }

    .pfob-export-buttons .pfob-btn {
        width: 100%;
    }
}
</style>

<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
