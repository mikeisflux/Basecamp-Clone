<?php
/**
 * Dashboard template
 */

PFOB_Template::header( 'Dashboard' );

$user_id = get_current_user_id();
$projects = PFOB_Project::get_user_projects( $user_id );

// Get user's preferred view (default: grid/tile)
$view_mode = get_user_meta( $user_id, 'pfob_projects_view', true ) ?: 'grid';

// Get company logo from user meta
$company_logo = get_user_meta( $user_id, 'pfob_company_logo', true );
$company_name = get_option( 'pfob_company_name' ) ?: get_bloginfo( 'name' );

// Check if user has custom branding feature
$subscription = PFOB_Subscription::get_by_user_id( $user_id );
$plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
$plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;
$has_custom_branding = $plan && ! empty( $plan['features']['custom_branding'] );
?>

<div class="pfob-container">
    <main class="pfob-main pfob-dashboard">

        <div class="pfob-page-header">
            <div class="pfob-company-logo">
                <?php if ( $company_logo ) : ?>
                    <img src="<?php echo esc_url( $company_logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="pfob-logo-image">
                <?php else : ?>
                    <h1><?php echo esc_html( $company_name ); ?></h1>
                <?php endif; ?>

                <?php if ( $has_custom_branding ) : ?>
                    <button class="pfob-btn pfob-btn-link" id="manage-logo-btn" style="font-size: 12px; margin-top: 5px;">
                        <?php echo $company_logo ? 'Change logo' : 'Upload logo'; ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="pfob-actions">
                <button class="pfob-btn pfob-btn-primary" id="create-project-btn">
                    Make a new project
                </button>
                <button class="pfob-btn pfob-btn-secondary" id="import-project-btn">
                    Import from other PMS
                </button>
                <button class="pfob-btn pfob-btn-secondary" id="invite-people-btn">
                    Invite people
                </button>
            </div>
        </div>

        <div class="pfob-view-controls">
            <div class="pfob-project-filter">
                <button class="pfob-filter-btn active" data-filter="all">
                    All projects
                </button>
            </div>

            <div class="pfob-view-toggle">
                <button class="pfob-view-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>"
                        data-view="grid"
                        title="Tile View">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="3" y="3" width="8" height="8"/>
                        <rect x="13" y="3" width="8" height="8"/>
                        <rect x="3" y="13" width="8" height="8"/>
                        <rect x="13" y="13" width="8" height="8"/>
                    </svg>
                </button>
                <button class="pfob-view-btn <?php echo $view_mode === 'list' ? 'active' : ''; ?>"
                        data-view="list"
                        title="List View">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="3" y="4" width="18" height="2"/>
                        <rect x="3" y="11" width="18" height="2"/>
                        <rect x="3" y="18" width="18" height="2"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="pfob-projects-container pfob-view-<?php echo $view_mode; ?>" id="projects-container">
            <?php if ( empty( $projects ) ) : ?>
                <div class="pfob-empty-state">
                    <h3>Welcome to ProjectFOB!</h3>
                    <p>You don't have any projects yet. Create your first project to get started.</p>
                    <button class="pfob-btn pfob-btn-primary" onclick="document.getElementById('create-project-btn').click()">
                        Create Your First Project
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $projects as $project ) : ?>
                    <div class="pfob-project-card"
                         data-project-id="<?php echo $project->id; ?>"
                         draggable="true">
                        <div class="pfob-drag-handle" title="Drag to reorder">⋮⋮</div>
                        <h3 class="pfob-project-title">
                            <a href="<?php echo PFOB_Template::get_project_url( $project ); ?>">
                                <?php echo esc_html( $project->name ); ?>
                            </a>
                        </h3>
                        <?php if ( $project->description ) : ?>
                            <p class="pfob-project-description"><?php echo esc_html( $project->description ); ?></p>
                        <?php endif; ?>
                        <div class="pfob-project-meta">
                            <span class="pfob-project-updated">
                                Updated <?php echo PFOB_Template::format_date( $project->updated_at ); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php PFOB_Template::footer(); ?>

<style>
.pfob-view-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
}

.pfob-view-toggle {
    display: flex;
    gap: 5px;
    background: white;
    padding: 4px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pfob-view-btn {
    background: transparent;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
    color: #666;
    transition: all 0.2s;
}

.pfob-view-btn:hover {
    background: #f0f0f0;
}

.pfob-view-btn.active {
    background: #2d9061;
    color: white;
}

/* Grid View (Tiles) */
.pfob-projects-container.pfob-view-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

/* List View */
.pfob-projects-container.pfob-view-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pfob-view-list .pfob-project-card {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s;
}

.pfob-view-list .pfob-project-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.pfob-view-list .pfob-project-title {
    margin: 0;
    flex: 1;
}

.pfob-view-list .pfob-project-description {
    flex: 2;
    margin: 0;
    color: #666;
    font-size: 0.9em;
}

.pfob-view-list .pfob-project-meta {
    flex-shrink: 0;
    margin: 0;
}

/* Drag handle */
.pfob-drag-handle {
    cursor: grab;
    color: #ccc;
    font-size: 18px;
    margin-right: 10px;
    user-select: none;
    padding: 5px;
}

.pfob-drag-handle:active {
    cursor: grabbing;
}

.pfob-project-card[draggable="true"]:hover .pfob-drag-handle {
    color: #2d9061;
}

/* Dragging states */
.pfob-project-card.pfob-dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.pfob-project-card.pfob-drag-over {
    border-top: 3px solid #2d9061;
}

.pfob-projects-container {
    position: relative;
    min-height: 200px;
}

/* Company Logo Styles */
.pfob-company-logo {
    text-align: center;
    margin-bottom: 20px;
}

.pfob-logo-image {
    max-height: 80px;
    max-width: 300px;
    height: auto;
    display: block;
    margin: 0 auto;
}

.pfob-btn-link {
    background: none;
    border: none;
    color: #2d9061;
    text-decoration: underline;
    cursor: pointer;
    padding: 0;
}

.pfob-btn-link:hover {
    color: #1f6b48;
}
</style>

<script>
const pfobData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    currentUser: <?php echo json_encode( array(
        'id' => $user_id,
        'name' => wp_get_current_user()->display_name,
        'avatar' => get_avatar_url( $user_id ),
    ) ); ?>
};

// View toggle functionality
document.querySelectorAll('.pfob-view-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const view = this.dataset.view;
        const container = document.getElementById('projects-container');

        // Update UI
        document.querySelectorAll('.pfob-view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Update container class
        container.className = `pfob-projects-container pfob-view-${view}`;

        // Save preference
        try {
            await fetch(`${pfobData.restUrl}/user/preferences`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({
                    key: 'pfob_projects_view',
                    value: view
                })
            });
        } catch (error) {
            console.error('Failed to save view preference:', error);
        }
    });
});

// Drag and Drop functionality
let draggedElement = null;
let draggedIndex = null;

const projectCards = document.querySelectorAll('.pfob-project-card[draggable="true"]');

projectCards.forEach((card, index) => {
    card.addEventListener('dragstart', function(e) {
        draggedElement = this;
        draggedIndex = index;
        this.classList.add('pfob-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    });

    card.addEventListener('dragend', function() {
        this.classList.remove('pfob-dragging');
        // Remove all drag-over classes
        document.querySelectorAll('.pfob-drag-over').forEach(el => {
            el.classList.remove('pfob-drag-over');
        });
    });

    card.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        if (draggedElement !== this) {
            this.classList.add('pfob-drag-over');
        }

        return false;
    });

    card.addEventListener('dragleave', function() {
        this.classList.remove('pfob-drag-over');
    });

    card.addEventListener('drop', async function(e) {
        e.stopPropagation();
        e.preventDefault();

        if (draggedElement !== this) {
            const container = document.getElementById('projects-container');
            const allCards = Array.from(container.querySelectorAll('.pfob-project-card'));
            const draggedIdx = allCards.indexOf(draggedElement);
            const targetIdx = allCards.indexOf(this);

            // Reorder DOM
            if (draggedIdx < targetIdx) {
                this.parentNode.insertBefore(draggedElement, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedElement, this);
            }

            // Save new order
            await saveProjectOrder();
        }

        this.classList.remove('pfob-drag-over');
        return false;
    });
});

async function saveProjectOrder() {
    const container = document.getElementById('projects-container');
    const cards = container.querySelectorAll('.pfob-project-card');
    const order = Array.from(cards).map(card => card.dataset.projectId);

    try {
        await fetch(`${pfobData.restUrl}/user/project-order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({ order: order })
        });
    } catch (error) {
        console.error('Failed to save project order:', error);
    }
}

// Import Project from other PMS
document.getElementById('import-project-btn')?.addEventListener('click', () => {
    showImportProjectModal();
});

// Invite People
document.getElementById('invite-people-btn')?.addEventListener('click', () => {
    window.location.href = '<?php echo home_url( '/projectfob/people/invite' ); ?>';
});

function showImportProjectModal() {
    const modal = document.createElement('div');
    modal.className = 'pfob-modal';
    modal.id = 'import-project-modal';
    modal.innerHTML = `
        <div class="pfob-modal-content">
            <div class="pfob-modal-header">
                <h2>Import Project from Other PMS</h2>
                <button class="pfob-modal-close">&times;</button>
            </div>
            <div class="pfob-modal-body">
                <form id="import-project-form">
                    <div class="pfob-form-group">
                        <label>Project Name *</label>
                        <input type="text"
                               name="project_name"
                               id="import-project-name"
                               required
                               placeholder="Enter project name">
                        <small style="color: #666; display: block; margin-top: 5px;">
                            This will be the name of your new project
                        </small>
                    </div>

                    <div class="pfob-form-group">
                        <label>Upload Project Files (ZIP) *</label>
                        <div class="pfob-file-upload-area" id="import-file-drop-zone">
                            <input type="file"
                                   name="project_zip"
                                   id="import-project-zip"
                                   accept=".zip"
                                   required
                                   style="display: none;">
                            <div class="pfob-upload-placeholder">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <p><strong>Click to upload</strong> or drag and drop</p>
                                <p style="font-size: 14px; color: #888;">ZIP file containing your project files</p>
                            </div>
                            <div class="pfob-upload-filename" style="display: none;">
                                <span class="filename"></span>
                                <button type="button" class="remove-file">&times;</button>
                            </div>
                        </div>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Upload a ZIP file exported from Basecamp or other project management systems
                        </small>
                    </div>

                    <div class="pfob-import-progress" style="display: none;">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <p class="progress-text">Importing...</p>
                    </div>

                    <div class="pfob-form-actions">
                        <button type="submit" class="pfob-btn pfob-btn-primary" id="import-submit-btn">
                            Import Project
                        </button>
                        <button type="button" class="pfob-btn pfob-btn-secondary pfob-modal-close">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Modal close handlers
    modal.querySelectorAll('.pfob-modal-close').forEach(btn => {
        btn.addEventListener('click', () => modal.remove());
    });

    // File upload handling
    const fileInput = document.getElementById('import-project-zip');
    const dropZone = document.getElementById('import-file-drop-zone');
    const filenameDisplay = dropZone.querySelector('.pfob-upload-filename');
    const placeholder = dropZone.querySelector('.pfob-upload-placeholder');

    dropZone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            displayFile(file);
        }
    });

    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file && file.name.endsWith('.zip')) {
            fileInput.files = e.dataTransfer.files;
            displayFile(file);
        } else {
            alert('Please upload a ZIP file');
        }
    });

    function displayFile(file) {
        placeholder.style.display = 'none';
        filenameDisplay.style.display = 'flex';
        filenameDisplay.querySelector('.filename').textContent = file.name;
    }

    filenameDisplay.querySelector('.remove-file')?.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        placeholder.style.display = 'block';
        filenameDisplay.style.display = 'none';
    });

    // Form submission
    document.getElementById('import-project-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const submitBtn = document.getElementById('import-submit-btn');
        const progressDiv = document.querySelector('.pfob-import-progress');
        const progressFill = document.querySelector('.progress-fill');
        const progressText = document.querySelector('.progress-text');

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Importing...';

        // Show progress
        progressDiv.style.display = 'block';

        try {
            const response = await fetch(`${pfobData.restUrl}/projects/import`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': pfobData.nonce
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.success) {
                progressFill.style.width = '100%';
                progressText.textContent = 'Import successful! Redirecting...';

                // Redirect to new project
                setTimeout(() => {
                    window.location.href = result.data.project_url;
                }, 1500);
            } else {
                throw new Error(result.message || 'Import failed');
            }
        } catch (error) {
            console.error('Failed to import project:', error);
            alert('Failed to import project: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Import Project';
            progressDiv.style.display = 'none';
        }
    });
}

// Logo Upload Modal
document.getElementById('manage-logo-btn')?.addEventListener('click', () => {
    const modal = document.createElement('div');
    modal.className = 'pfob-modal';
    modal.id = 'logo-upload-modal';
    modal.innerHTML = `
        <div class="pfob-modal-content" style="max-width: 500px;">
            <div class="pfob-modal-header">
                <h2>Company Logo</h2>
                <button class="pfob-modal-close">&times;</button>
            </div>
            <div class="pfob-modal-body">
                <?php if ( $company_logo ) : ?>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="<?php echo esc_url( $company_logo ); ?>" alt="Current Logo" style="max-width: 200px; max-height: 80px; display: block; margin: 0 auto 10px;">
                        <button type="button" class="pfob-btn pfob-btn-secondary" id="remove-logo-btn">Remove Logo</button>
                    </div>
                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0;">
                <?php endif; ?>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                        <?php echo $company_logo ? 'Update Logo' : 'Upload Logo'; ?>
                    </label>
                    <input type="file" id="logo-file-input" accept="image/*" style="display: block; margin-bottom: 10px;">
                    <p style="color: #666; font-size: 13px; margin: 0;">
                        Recommended: PNG or SVG, max 200px height, transparent background works best
                    </p>
                </div>

                <div id="logo-upload-status" style="margin-bottom: 16px;"></div>

                <div class="pfob-form-actions">
                    <button type="button" class="pfob-btn pfob-btn-primary" id="upload-logo-submit-btn">
                        Upload Logo
                    </button>
                    <button type="button" class="pfob-btn pfob-btn-secondary pfob-modal-close">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Modal close handlers
    modal.querySelectorAll('.pfob-modal-close').forEach(btn => {
        btn.addEventListener('click', () => modal.remove());
    });

    // Remove logo
    document.getElementById('remove-logo-btn')?.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to remove your company logo?')) {
            return;
        }

        const statusDiv = document.getElementById('logo-upload-status');
        statusDiv.innerHTML = '<p style="color: #0066cc;">Removing logo...</p>';

        try {
            const response = await fetch('<?php echo rest_url( 'projectfob/v1/settings/remove-logo' ); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': pfobData.nonce,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                statusDiv.innerHTML = '<p style="color: #46b450;">✓ Logo removed! Refreshing...</p>';
                setTimeout(() => location.reload(), 1000);
            } else {
                statusDiv.innerHTML = '<p style="color: #dc3232;">Error: ' + (result.message || 'Failed to remove logo') + '</p>';
            }
        } catch (error) {
            statusDiv.innerHTML = '<p style="color: #dc3232;">Error removing logo. Please try again.</p>';
        }
    });

    // Upload logo
    document.getElementById('upload-logo-submit-btn').addEventListener('click', async () => {
        const fileInput = document.getElementById('logo-file-input');
        const file = fileInput.files[0];
        const statusDiv = document.getElementById('logo-upload-status');
        const submitBtn = document.getElementById('upload-logo-submit-btn');

        if (!file) {
            statusDiv.innerHTML = '<p style="color: #dc3232;">Please select a file first</p>';
            return;
        }

        // Validate file type
        if (!file.type.startsWith('image/')) {
            statusDiv.innerHTML = '<p style="color: #dc3232;">Please select an image file</p>';
            return;
        }

        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            statusDiv.innerHTML = '<p style="color: #dc3232;">File size must be less than 2MB</p>';
            return;
        }

        statusDiv.innerHTML = '<p style="color: #0066cc;">Uploading...</p>';
        submitBtn.disabled = true;

        const formData = new FormData();
        formData.append('logo', file);

        try {
            const response = await fetch('<?php echo rest_url( 'projectfob/v1/settings/upload-logo' ); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': pfobData.nonce
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                statusDiv.innerHTML = '<p style="color: #46b450;">✓ Logo uploaded successfully! Refreshing...</p>';
                setTimeout(() => location.reload(), 1000);
            } else {
                statusDiv.innerHTML = '<p style="color: #dc3232;">Error: ' + (result.message || 'Upload failed') + '</p>';
                submitBtn.disabled = false;
            }
        } catch (error) {
            statusDiv.innerHTML = '<p style="color: #dc3232;">Error uploading logo. Please try again.</p>';
            submitBtn.disabled = false;
        }
    });
});
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
