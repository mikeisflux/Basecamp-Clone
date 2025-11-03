<?php
/**
 * Documents & Files browser
 */

global $pfob_project;

PFOB_Template::header( $pfob_project->name . ' - Docs & Files' );

$current_folder = isset( $_GET['folder'] ) ? intval( $_GET['folder'] ) : null;
$documents = PFOB_Document::get_project_documents( $pfob_project->id, $current_folder );

// Get user's preferred view (default: grid/tile)
$user_id = get_current_user_id();
$view_mode = get_user_meta( $user_id, 'pfob_files_view', true ) ?: 'grid';
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-documents">

        <div class="pfob-page-header">
            <div>
                <h1>Docs & Files</h1>
                <div class="pfob-breadcrumbs" id="folder-breadcrumbs">
                    <a href="?">Root</a>
                </div>
            </div>
            <div class="pfob-actions">
                <button class="pfob-btn pfob-btn-secondary" id="new-folder-btn">
                    New Folder
                </button>
                <div class="pfob-upload-dropdown">
                    <button class="pfob-btn pfob-btn-primary" id="upload-menu-btn">
                        Upload Files ▾
                    </button>
                    <div class="pfob-upload-menu" id="upload-menu" style="display:none;">
                        <button class="pfob-upload-option" id="upload-local-btn">
                            💻 Upload from Computer
                        </button>
                        <button class="pfob-upload-option" id="upload-gdrive-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" style="vertical-align: middle;">
                                <path fill="#4285F4" d="M8.5 6.5L15.5 6.5 20.25 15 15.5 23.5 3.75 23.5 8.5 15z"/>
                                <path fill="#34A853" d="M8.5 6.5L1.75 15 8.5 23.5 15.5 23.5z"/>
                                <path fill="#FBBC04" d="M15.5 6.5L8.5 6.5 8.5 23.5 15.5 23.5z"/>
                            </svg>
                            Upload from Google Drive
                        </button>
                        <button class="pfob-upload-option" id="upload-dropbox-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#0061FF" style="vertical-align: middle;">
                                <path d="M6 1.807L0 5.629l6 3.822 6.001-3.822L6 1.807zM18 1.807l-6 3.822 6 3.822 6-3.822-6-3.822zM0 13.274l6 3.822 6.001-3.822L6 9.452l-6 3.822zm12.001 0l6 3.822 6-3.822-6-3.822-6 3.822z"/>
                            </svg>
                            Upload from Dropbox
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="pfob-view-controls" style="margin: 20px 0;">
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

        <div class="pfob-documents-container pfob-view-<?php echo $view_mode; ?>" id="documents-container">
            <?php if ( empty( $documents ) ) : ?>
                <div class="pfob-empty-state">
                    <h3>No files yet</h3>
                    <p>Upload your first file or create a folder to get started.</p>
                    <button class="pfob-btn pfob-btn-primary" onclick="document.getElementById('upload-menu-btn').click()">
                        Upload First File
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $documents as $doc ) : ?>
                    <div class="pfob-document-item <?php echo $doc->is_folder ? 'pfob-folder' : 'pfob-file'; ?>"
                         data-id="<?php echo $doc->id; ?>"
                         data-type="<?php echo $doc->is_folder ? 'folder' : 'file'; ?>"
                         draggable="true">
                        <div class="pfob-drag-handle" title="Drag to reorder">⋮⋮</div>

                        <?php if ( $doc->is_folder ) : ?>
                            <a href="?folder=<?php echo $doc->id; ?>" class="pfob-document-link">
                                <div class="pfob-document-icon">📁</div>
                                <div class="pfob-document-name"><?php echo esc_html( $doc->name ); ?></div>
                            </a>
                        <?php else : ?>
                            <div class="pfob-document-link" onclick="previewFile(<?php echo $doc->id; ?>)">
                                <div class="pfob-document-icon">
                                    <?php echo PFOB_File_Service::get_file_icon( $doc->mime_type ); ?>
                                </div>
                                <div class="pfob-document-info">
                                    <div class="pfob-document-name"><?php echo esc_html( $doc->name ); ?></div>
                                    <div class="pfob-document-meta">
                                        <?php echo size_format( $doc->file_size ); ?>
                                        · <?php echo PFOB_Template::format_date( $doc->created_at ); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="pfob-document-actions">
                            <?php if ( ! $doc->is_folder ) : ?>
                                <a href="<?php echo content_url( $doc->file_path ); ?>"
                                   download
                                   class="pfob-btn-icon"
                                   title="Download">
                                    ⬇
                                </a>
                            <?php endif; ?>
                            <button class="pfob-btn-icon pfob-delete-doc-btn"
                                    data-id="<?php echo $doc->id; ?>"
                                    title="Delete">
                                ×
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Hidden file input -->
        <input type="file" id="file-input" multiple style="display:none;">

    </main>
</div>

<!-- File Preview Modal -->
<div id="file-preview-modal" class="pfob-modal" style="display:none;">
    <div class="pfob-modal-content pfob-modal-large">
        <div class="pfob-modal-header">
            <h2 id="preview-file-name"></h2>
            <button class="pfob-modal-close">&times;</button>
        </div>
        <div class="pfob-modal-body">
            <div id="preview-container"></div>
        </div>
    </div>
</div>

<script>
const pfobData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $pfob_project->id; ?>,
    currentFolder: <?php echo $current_folder ? $current_folder : 'null'; ?>,
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};

// New folder button
document.getElementById('new-folder-btn')?.addEventListener('click', () => {
    const folderName = prompt('Folder name:');
    if (folderName) {
        createFolder(folderName);
    }
});

// Upload dropdown menu
document.getElementById('upload-menu-btn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    const menu = document.getElementById('upload-menu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
});

// Close menu when clicking outside
document.addEventListener('click', () => {
    document.getElementById('upload-menu').style.display = 'none';
});

// Local computer upload
document.getElementById('upload-local-btn')?.addEventListener('click', () => {
    document.getElementById('file-input').click();
    document.getElementById('upload-menu').style.display = 'none';
});

// Google Drive upload
document.getElementById('upload-gdrive-btn')?.addEventListener('click', async () => {
    document.getElementById('upload-menu').style.display = 'none';
    await openGoogleDrivePicker();
});

// Dropbox upload
document.getElementById('upload-dropbox-btn')?.addEventListener('click', async () => {
    document.getElementById('upload-menu').style.display = 'none';
    await openDropboxPicker();
});

// File input change
document.getElementById('file-input')?.addEventListener('change', (e) => {
    const files = Array.from(e.target.files);
    uploadFiles(files);
});

async function createFolder(name) {
    try {
        const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/documents/folder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({
                name: name,
                parent_id: pfobData.currentFolder
            })
        });

        if (response.ok) {
            window.location.reload();
        }
    } catch (error) {
        console.error('Failed to create folder:', error);
        alert('Failed to create folder');
    }
}

async function uploadFiles(files) {
    let successCount = 0;
    let errorCount = 0;

    for (const file of files) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('project_id', pfobData.projectId);
        if (pfobData.currentFolder) {
            formData.append('parent_id', pfobData.currentFolder);
        }

        try {
            const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/documents/upload`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': pfobData.nonce
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                console.log(`✓ Uploaded: ${file.name}`);
                successCount++;
            } else {
                console.error(`✗ Failed: ${file.name} - ${result.message}`);
                errorCount++;
                alert(`Failed to upload ${file.name}: ${result.message}`);
            }
        } catch (error) {
            console.error(`✗ Error uploading ${file.name}:`, error);
            errorCount++;
            alert(`Error uploading ${file.name}: ${error.message}`);
        }
    }

    // Reload page if at least one file uploaded successfully
    if (successCount > 0) {
        console.log(`Uploaded ${successCount} file(s). Reloading...`);
        setTimeout(() => window.location.reload(), 1000);
    } else if (errorCount > 0) {
        console.error(`All ${errorCount} file(s) failed to upload`);
    }
}

// Delete document
document.querySelectorAll('.pfob-delete-doc-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const docId = e.target.dataset.id;

        if (!confirm('Are you sure you want to delete this?')) {
            return;
        }

        try {
            const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/documents/${docId}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': pfobData.nonce }
            });

            if (response.ok) {
                e.target.closest('.pfob-document-item').remove();
            }
        } catch (error) {
            console.error('Failed to delete:', error);
        }
    });
});

// File preview function with advanced support
async function previewFile(docId) {
    try {
        const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/documents/${docId}`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data) {
            const doc = result.data;
            const modal = document.getElementById('file-preview-modal');
            const previewContainer = document.getElementById('preview-container');

            document.getElementById('preview-file-name').textContent = doc.name;

            let previewHTML = '';
            const mimeType = doc.mime_type || '';
            const fileName = doc.name.toLowerCase();

            // Check if file is actually an image despite .pdf extension
            const isPDFNamedImage = fileName.endsWith('.pdf') && mimeType.startsWith('image/');

            // Handle different file types
            if (mimeType.startsWith('image/') || isPDFNamedImage) {
                // Image gallery with zoom
                previewHTML = `
                    <div class="pfob-image-preview">
                        <img src="${doc.url}"
                             alt="${escapeHtml(doc.name)}"
                             onclick="this.classList.toggle('pfob-zoomed')"
                             style="max-width:100%; height:auto; cursor:zoom-in;">
                        <div class="pfob-preview-hint">Click image to zoom</div>
                    </div>
                `;
            } else if (mimeType === 'application/pdf') {
                // Enhanced PDF viewer (works for both text PDFs and scanned image PDFs)
                previewHTML = `
                    <div class="pfob-pdf-preview">
                        <iframe src="${doc.url}"
                                width="100%"
                                height="700px"
                                style="border:none; border-radius:8px;">
                        </iframe>
                        <div class="pfob-preview-hint" style="margin-top: 10px; color: #666; font-size: 13px;">
                            Displays both document PDFs and scanned image PDFs
                        </div>
                        <div class="pfob-preview-actions" style="margin-top: 10px;">
                            <a href="${doc.url}" target="_blank" class="pfob-btn pfob-btn-secondary">
                                Open in New Tab
                            </a>
                            <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                Download PDF
                            </a>
                        </div>
                    </div>
                `;
            } else if (mimeType.startsWith('video/')) {
                // Video player
                previewHTML = `
                    <div class="pfob-video-preview">
                        <video controls style="width:100%; max-height:600px; border-radius:8px;">
                            <source src="${doc.url}" type="${mimeType}">
                            Your browser does not support the video tag.
                        </video>
                        <div class="pfob-preview-actions">
                            <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                Download Video
                            </a>
                        </div>
                    </div>
                `;
            } else if (mimeType.startsWith('audio/')) {
                // Audio player
                previewHTML = `
                    <div class="pfob-audio-preview">
                        <div class="pfob-audio-icon">🎵</div>
                        <h3>${escapeHtml(doc.name)}</h3>
                        <audio controls style="width:100%; margin:20px 0;">
                            <source src="${doc.url}" type="${mimeType}">
                            Your browser does not support the audio element.
                        </audio>
                        <div class="pfob-preview-actions">
                            <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                Download Audio
                            </a>
                        </div>
                    </div>
                `;
            } else if (isOfficeDocument(mimeType)) {
                // Microsoft Office documents using Office Online Viewer
                const encodedUrl = encodeURIComponent(doc.url);
                previewHTML = `
                    <div class="pfob-office-preview">
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=${encodedUrl}"
                                width="100%"
                                height="700px"
                                frameborder="0"
                                style="border-radius:8px;">
                        </iframe>
                        <div class="pfob-preview-hint">
                            Microsoft Office Online Viewer
                        </div>
                        <div class="pfob-preview-actions">
                            <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                Download ${getFileExtension(doc.name).toUpperCase()}
                            </a>
                        </div>
                    </div>
                `;
            } else if (mimeType === 'text/plain' || mimeType.includes('text/')) {
                // Text file preview
                fetch(doc.url)
                    .then(r => r.text())
                    .then(text => {
                        previewContainer.innerHTML = `
                            <div class="pfob-text-preview">
                                <pre style="white-space:pre-wrap; padding:20px; background:#f9fafb; border-radius:8px; max-height:600px; overflow:auto;">${escapeHtml(text.substring(0, 10000))}</pre>
                                ${text.length > 10000 ? '<p class="pfob-preview-hint">Preview truncated. Download full file.</p>' : ''}
                                <div class="pfob-preview-actions">
                                    <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                        Download File
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                previewHTML = '<div class="pfob-loading">Loading preview...</div>';
            } else {
                // Fallback for unsupported types
                previewHTML = `
                    <div class="pfob-preview-unsupported">
                        <div class="pfob-file-icon-large">${PFOB_File_Service_getFileIcon(mimeType)}</div>
                        <h3>${escapeHtml(doc.name)}</h3>
                        <p>Preview not available for this file type.</p>
                        <p class="pfob-file-meta">
                            ${mimeType}<br>
                            ${formatFileSize(doc.file_size)}
                        </p>
                        <div class="pfob-preview-actions">
                            <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                Download File
                            </a>
                        </div>
                    </div>
                `;
            }

            previewContainer.innerHTML = previewHTML;
            modal.style.display = 'flex';

            modal.querySelector('.pfob-modal-close').addEventListener('click', () => {
                modal.style.display = 'none';
            });

            // Close on background click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    } catch (error) {
        console.error('Failed to load preview:', error);
        alert('Failed to load file preview');
    }
}

// Helper functions
function isOfficeDocument(mimeType) {
    const officeTypes = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    ];
    return officeTypes.includes(mimeType);
}

function getFileExtension(filename) {
    return filename.split('.').pop() || '';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function PFOB_File_Service_getFileIcon(mimeType) {
    if (!mimeType) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#9CA3AF"/><path d="M18 12H26L32 18V36H18V12Z M26 12V18H32" stroke="white" stroke-width="2" fill="none"/></svg>';
    }

    // Image files
    if (mimeType.includes('image')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#10B981"/><path d="M16 20L20 24L28 16L36 24V34H12V24L16 20Z" fill="white"/><circle cx="20" cy="18" r="3" fill="white"/></svg>';
    }

    // PDF files
    if (mimeType.includes('pdf')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#DC2626"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">PDF</text></svg>';
    }

    // Word documents
    if (mimeType.includes('word') || mimeType.includes('document') || mimeType.includes('msword')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#2B5797"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">DOC</text></svg>';
    }

    // Excel spreadsheets
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#16A34A"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">XLS</text></svg>';
    }

    // PowerPoint presentations
    if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#EA580C"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">PPT</text></svg>';
    }

    // Text files
    if (mimeType.includes('text')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#6B7280"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">TXT</text></svg>';
    }

    // Zip/Archive files
    if (mimeType.includes('zip') || mimeType.includes('compressed') || mimeType.includes('archive')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#F59E0B"/><text x="24" y="30" font-family="sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">ZIP</text></svg>';
    }

    // Video files
    if (mimeType.includes('video')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#7C3AED"/><polygon points="20,16 20,32 32,24" fill="white"/></svg>';
    }

    // Audio files
    if (mimeType.includes('audio')) {
        return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#EC4899"/><path d="M20 14H22V28C22 30 20 32 18 32C16 32 14 30 14 28C14 26 16 24 18 24C19 24 20 24.5 20 25V14Z M26 12L32 14V24C32 26 30 28 28 28C26 28 24 26 24 24C24 22 26 20 28 20C29 20 30 20.5 30 21V16L26 15V12Z" fill="white"/></svg>';
    }

    // Generic file
    return '<svg width="80" height="80" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="8" fill="#9CA3AF"/><path d="M18 12H26L32 18V36H18V12Z M26 12V18H32" stroke="white" stroke-width="2" fill="none"/></svg>';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Drag and drop file upload
const docsContainer = document.getElementById('documents-container');
if (docsContainer) {
    docsContainer.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        docsContainer.classList.add('pfob-drag-over');
    });

    docsContainer.addEventListener('dragleave', () => {
        docsContainer.classList.remove('pfob-drag-over');
    });

    docsContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        docsContainer.classList.remove('pfob-drag-over');

        const files = Array.from(e.dataTransfer.files);
        if (files.length > 0) {
            uploadFiles(files);
        }
    });
}

// View toggle functionality
document.querySelectorAll('.pfob-view-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const view = this.dataset.view;
        const container = document.getElementById('documents-container');

        // Update UI
        document.querySelectorAll('.pfob-view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Update container class
        container.className = `pfob-documents-container pfob-view-${view}`;

        // Save preference
        try {
            await fetch(`${pfobData.restUrl}/user/preferences`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({
                    key: 'pfob_files_view',
                    value: view
                })
            });
        } catch (error) {
            console.error('Failed to save view preference:', error);
        }
    });
});

// Drag and Drop for reordering
let draggedElement = null;

document.querySelectorAll('.pfob-document-item[draggable="true"]').forEach((item) => {
    item.addEventListener('dragstart', function(e) {
        draggedElement = this;
        this.classList.add('pfob-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    });

    item.addEventListener('dragend', function() {
        this.classList.remove('pfob-dragging');
        document.querySelectorAll('.pfob-drag-over').forEach(el => {
            el.classList.remove('pfob-drag-over');
        });
    });

    item.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        if (draggedElement !== this) {
            this.classList.add('pfob-drag-over');
        }
        return false;
    });

    item.addEventListener('dragleave', function() {
        this.classList.remove('pfob-drag-over');
    });

    item.addEventListener('drop', async function(e) {
        e.stopPropagation();
        e.preventDefault();

        if (draggedElement !== this) {
            const container = document.getElementById('documents-container');
            const allItems = Array.from(container.querySelectorAll('.pfob-document-item'));
            const draggedIdx = allItems.indexOf(draggedElement);
            const targetIdx = allItems.indexOf(this);

            // Reorder DOM
            if (draggedIdx < targetIdx) {
                this.parentNode.insertBefore(draggedElement, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedElement, this);
            }

            // Save new order
            await saveDocumentOrder();
        }

        this.classList.remove('pfob-drag-over');
        return false;
    });
});

async function saveDocumentOrder() {
    const container = document.getElementById('documents-container');
    const items = container.querySelectorAll('.pfob-document-item');
    const order = Array.from(items).map(item => item.dataset.id);

    try {
        await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/documents/order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({
                folder_id: pfobData.currentFolder,
                order: order
            })
        });
    } catch (error) {
        console.error('Failed to save document order:', error);
    }
}

// Google Drive Picker - Uses current user's personal Google Drive
async function openGoogleDrivePicker() {
    try {
        // Check if user has connected their personal Google Drive
        const statusResponse = await fetch(`${pfobData.restUrl}/integrations/gdrive/status`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const status = await statusResponse.json();

        if (!status.success || !status.data.is_connected) {
            if (confirm('You need to connect your personal Google Drive first. Go to settings now?')) {
                window.location.href = '<?php echo home_url('/projectfob/settings/cloud-storage'); ?>';
            }
            return;
        }

        // Get picker token for current user
        const tokenResponse = await fetch(`${pfobData.restUrl}/integrations/gdrive/picker-token`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const tokenData = await tokenResponse.json();

        if (!tokenData.success) {
            alert('Failed to access your Google Drive. Please reconnect in settings.');
            return;
        }

        // Load Google Picker API
        gapi.load('picker', () => {
            const picker = new google.picker.PickerBuilder()
                .addView(google.picker.ViewId.DOCS)
                .setOAuthToken(tokenData.data.access_token)
                .setCallback(async (data) => {
                    if (data.action === google.picker.Action.PICKED) {
                        const files = data.docs;
                        await importGoogleDriveFiles(files);
                    }
                })
                .build();
            picker.setVisible(true);
        });

    } catch (error) {
        console.error('Google Drive picker error:', error);
        alert('Failed to open Google Drive picker');
    }
}

// Dropbox Picker - Uses current user's personal Dropbox
async function openDropboxPicker() {
    try {
        // Check if user has connected their personal Dropbox
        const statusResponse = await fetch(`${pfobData.restUrl}/integrations/dropbox/status`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const status = await statusResponse.json();

        if (!status.success || !status.data.is_connected) {
            if (confirm('You need to connect your personal Dropbox first. Go to settings now?')) {
                window.location.href = '<?php echo home_url('/projectfob/settings/cloud-storage'); ?>';
            }
            return;
        }

        // Open Dropbox Chooser for current user
        Dropbox.choose({
            success: async (files) => {
                await importDropboxFiles(files);
            },
            cancel: () => {
                console.log('Dropbox picker cancelled');
            },
            linkType: 'direct',
            multiselect: true,
            extensions: [],
        });

    } catch (error) {
        console.error('Dropbox picker error:', error);
        alert('Failed to open Dropbox picker. Make sure you have connected your Dropbox account.');
    }
}

// Import files from Google Drive (user's personal files)
async function importGoogleDriveFiles(files) {
    for (const file of files) {
        try {
            const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/documents/import/gdrive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({
                    file_id: file.id,
                    file_name: file.name,
                    mime_type: file.mimeType,
                    parent_id: pfobData.currentFolder
                })
            });

            if (response.ok) {
                console.log(`Imported from Google Drive: ${file.name}`);
            }
        } catch (error) {
            console.error(`Failed to import ${file.name}:`, error);
        }
    }

    setTimeout(() => window.location.reload(), 1000);
}

// Import files from Dropbox (user's personal files)
async function importDropboxFiles(files) {
    for (const file of files) {
        try {
            const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/documents/import/dropbox`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({
                    file_link: file.link,
                    file_name: file.name,
                    file_size: file.bytes,
                    parent_id: pfobData.currentFolder
                })
            });

            if (response.ok) {
                console.log(`Imported from Dropbox: ${file.name}`);
            }
        } catch (error) {
            console.error(`Failed to import ${file.name}:`, error);
        }
    }

    setTimeout(() => window.location.reload(), 1000);
}
</script>

<!-- Load Google Picker API -->
<script src="https://apis.google.com/js/api.js"></script>

<!-- Load Dropbox Chooser -->
<script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="<?php echo esc_attr( get_option( 'pfob_dropbox_app_key', '' ) ); ?>"></script>

<style>
/* Upload Dropdown Menu */
.pfob-upload-dropdown {
    position: relative;
    display: inline-block;
}

.pfob-upload-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    min-width: 250px;
    z-index: 1000;
    padding: 8px 0;
}

.pfob-upload-option {
    display: block;
    width: 100%;
    padding: 12px 16px;
    text-align: left;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
    color: #333;
    transition: background 0.2s;
}

.pfob-upload-option:hover {
    background: #f0f8f4;
}

.pfob-upload-option svg {
    margin-right: 10px;
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
.pfob-documents-container.pfob-view-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

/* List View */
.pfob-documents-container.pfob-view-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.pfob-view-list .pfob-document-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    background: white;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s;
}

.pfob-view-list .pfob-document-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.pfob-view-list .pfob-document-link {
    display: flex;
    align-items: center;
    flex: 1;
    gap: 15px;
}

.pfob-view-list .pfob-document-icon {
    flex-shrink: 0;
    font-size: 24px;
}

.pfob-view-list .pfob-document-info {
    flex: 1;
}

.pfob-view-list .pfob-document-name {
    font-weight: 600;
    margin-bottom: 4px;
}

.pfob-view-list .pfob-document-meta {
    font-size: 0.85em;
    color: #888;
}

.pfob-view-list .pfob-document-actions {
    flex-shrink: 0;
    display: flex;
    gap: 8px;
}

/* Drag handle */
.pfob-drag-handle {
    cursor: grab;
    color: #ccc;
    font-size: 18px;
    margin-right: 8px;
    user-select: none;
    padding: 5px;
}

.pfob-drag-handle:active {
    cursor: grabbing;
}

.pfob-document-item[draggable="true"]:hover .pfob-drag-handle {
    color: #2d9061;
}

/* Dragging states */
.pfob-document-item.pfob-dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.pfob-document-item.pfob-drag-over {
    border-top: 3px solid #2d9061;
}
</style>

<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
