<?php
/**
 * Documents & Files browser
 */

global $pfob_project;

PFOB_Template::header( $pfob_project->name . ' - Docs & Files' );

$current_folder = isset( $_GET['folder'] ) ? intval( $_GET['folder'] ) : null;
$documents = PFOB_Document::get_project_documents( $pfob_project->id, $current_folder );
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
                <button class="pfob-btn pfob-btn-primary" id="upload-file-btn">
                    Upload Files
                </button>
            </div>
        </div>

        <div class="pfob-documents-grid" id="documents-container">
            <?php if ( empty( $documents ) ) : ?>
                <div class="pfob-empty-state">
                    <h3>No files yet</h3>
                    <p>Upload your first file or create a folder to get started.</p>
                    <button class="pfob-btn pfob-btn-primary" onclick="document.getElementById('upload-file-btn').click()">
                        Upload First File
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $documents as $doc ) : ?>
                    <div class="pfob-document-item <?php echo $doc->is_folder ? 'pfob-folder' : 'pfob-file'; ?>"
                         data-id="<?php echo $doc->id; ?>"
                         data-type="<?php echo $doc->is_folder ? 'folder' : 'file'; ?>">

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

// Upload button
document.getElementById('upload-file-btn')?.addEventListener('click', () => {
    document.getElementById('file-input').click();
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

            if (response.ok) {
                console.log(`Uploaded: ${file.name}`);
            }
        } catch (error) {
            console.error(`Failed to upload ${file.name}:`, error);
        }
    }

    // Reload page after all uploads
    setTimeout(() => window.location.reload(), 1000);
}

// Delete document
document.querySelectorAll('.pfob-delete-doc-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const docId = e.target.dataset.id;

        if (!confirm('Are you sure you want to delete this?')) {
            return;
        }

        try {
            const response = await fetch(`${pfobData.restUrl}/documents/${docId}`, {
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
        const response = await fetch(`${pfobData.restUrl}/documents/${docId}`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data) {
            const doc = result.data;
            const modal = document.getElementById('file-preview-modal');
            const previewContainer = document.getElementById('preview-container');

            document.getElementById('preview-file-name').textContent = doc.name;

            let previewHTML = '';

            // Handle different file types
            if (doc.mime_type.startsWith('image/')) {
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
            } else if (doc.mime_type === 'application/pdf') {
                // Enhanced PDF viewer
                previewHTML = `
                    <div class="pfob-pdf-preview">
                        <iframe src="${doc.url}"
                                width="100%"
                                height="700px"
                                style="border:none; border-radius:8px;">
                        </iframe>
                        <div class="pfob-preview-actions">
                            <a href="${doc.url}" target="_blank" class="pfob-btn pfob-btn-secondary">
                                Open in New Tab
                            </a>
                            <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                Download PDF
                            </a>
                        </div>
                    </div>
                `;
            } else if (doc.mime_type.startsWith('video/')) {
                // Video player
                previewHTML = `
                    <div class="pfob-video-preview">
                        <video controls style="width:100%; max-height:600px; border-radius:8px;">
                            <source src="${doc.url}" type="${doc.mime_type}">
                            Your browser does not support the video tag.
                        </video>
                        <div class="pfob-preview-actions">
                            <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                Download Video
                            </a>
                        </div>
                    </div>
                `;
            } else if (doc.mime_type.startsWith('audio/')) {
                // Audio player
                previewHTML = `
                    <div class="pfob-audio-preview">
                        <div class="pfob-audio-icon">🎵</div>
                        <h3>${escapeHtml(doc.name)}</h3>
                        <audio controls style="width:100%; margin:20px 0;">
                            <source src="${doc.url}" type="${doc.mime_type}">
                            Your browser does not support the audio element.
                        </audio>
                        <div class="pfob-preview-actions">
                            <a href="${doc.url}" download class="pfob-btn pfob-btn-primary">
                                Download Audio
                            </a>
                        </div>
                    </div>
                `;
            } else if (isOfficeDocument(doc.mime_type)) {
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
            } else if (doc.mime_type === 'text/plain' || doc.mime_type.includes('text/')) {
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
                        <div class="pfob-file-icon-large">${PFOB_File_Service_getFileIcon(doc.mime_type)}</div>
                        <h3>${escapeHtml(doc.name)}</h3>
                        <p>Preview not available for this file type.</p>
                        <p class="pfob-file-meta">
                            ${doc.mime_type}<br>
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
    if (mimeType.startsWith('image/')) return '🖼️';
    if (mimeType.startsWith('video/')) return '🎥';
    if (mimeType.startsWith('audio/')) return '🎵';
    if (mimeType === 'application/pdf') return '📄';
    if (isOfficeDocument(mimeType)) return '📊';
    return '📎';
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
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
