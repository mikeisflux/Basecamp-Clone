<?php
/**
 * Documents & Files browser
 */

global $bcwp_project;

BCWP_Template::header( $bcwp_project->name . ' - Docs & Files' );

$current_folder = isset( $_GET['folder'] ) ? intval( $_GET['folder'] ) : null;
$documents = BCWP_Document::get_project_documents( $bcwp_project->id, $current_folder );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-documents">

        <div class="bcwp-page-header">
            <div>
                <h1>Docs & Files</h1>
                <div class="bcwp-breadcrumbs" id="folder-breadcrumbs">
                    <a href="?">Root</a>
                </div>
            </div>
            <div class="bcwp-actions">
                <button class="bcwp-btn bcwp-btn-secondary" id="new-folder-btn">
                    New Folder
                </button>
                <button class="bcwp-btn bcwp-btn-primary" id="upload-file-btn">
                    Upload Files
                </button>
            </div>
        </div>

        <div class="bcwp-documents-grid" id="documents-container">
            <?php if ( empty( $documents ) ) : ?>
                <div class="bcwp-empty-state">
                    <h3>No files yet</h3>
                    <p>Upload your first file or create a folder to get started.</p>
                    <button class="bcwp-btn bcwp-btn-primary" onclick="document.getElementById('upload-file-btn').click()">
                        Upload First File
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $documents as $doc ) : ?>
                    <div class="bcwp-document-item <?php echo $doc->is_folder ? 'bcwp-folder' : 'bcwp-file'; ?>"
                         data-id="<?php echo $doc->id; ?>"
                         data-type="<?php echo $doc->is_folder ? 'folder' : 'file'; ?>">

                        <?php if ( $doc->is_folder ) : ?>
                            <a href="?folder=<?php echo $doc->id; ?>" class="bcwp-document-link">
                                <div class="bcwp-document-icon">📁</div>
                                <div class="bcwp-document-name"><?php echo esc_html( $doc->name ); ?></div>
                            </a>
                        <?php else : ?>
                            <div class="bcwp-document-link" onclick="previewFile(<?php echo $doc->id; ?>)">
                                <div class="bcwp-document-icon">
                                    <?php echo BCWP_File_Service::get_file_icon( $doc->mime_type ); ?>
                                </div>
                                <div class="bcwp-document-info">
                                    <div class="bcwp-document-name"><?php echo esc_html( $doc->name ); ?></div>
                                    <div class="bcwp-document-meta">
                                        <?php echo size_format( $doc->file_size ); ?>
                                        · <?php echo BCWP_Template::format_date( $doc->created_at ); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="bcwp-document-actions">
                            <?php if ( ! $doc->is_folder ) : ?>
                                <a href="<?php echo content_url( $doc->file_path ); ?>"
                                   download
                                   class="bcwp-btn-icon"
                                   title="Download">
                                    ⬇
                                </a>
                            <?php endif; ?>
                            <button class="bcwp-btn-icon bcwp-delete-doc-btn"
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
<div id="file-preview-modal" class="bcwp-modal" style="display:none;">
    <div class="bcwp-modal-content bcwp-modal-large">
        <div class="bcwp-modal-header">
            <h2 id="preview-file-name"></h2>
            <button class="bcwp-modal-close">&times;</button>
        </div>
        <div class="bcwp-modal-body">
            <div id="preview-container"></div>
        </div>
    </div>
</div>

<script>
const bcwpData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $bcwp_project->id; ?>,
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
        const response = await fetch(`${bcwpData.restUrl}/projects/${bcwpData.projectId}/documents/folder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bcwpData.nonce
            },
            body: JSON.stringify({
                name: name,
                parent_id: bcwpData.currentFolder
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
        formData.append('project_id', bcwpData.projectId);
        if (bcwpData.currentFolder) {
            formData.append('parent_id', bcwpData.currentFolder);
        }

        try {
            const response = await fetch(`${bcwpData.restUrl}/projects/${bcwpData.projectId}/documents/upload`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': bcwpData.nonce
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
document.querySelectorAll('.bcwp-delete-doc-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const docId = e.target.dataset.id;

        if (!confirm('Are you sure you want to delete this?')) {
            return;
        }

        try {
            const response = await fetch(`${bcwpData.restUrl}/documents/${docId}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': bcwpData.nonce }
            });

            if (response.ok) {
                e.target.closest('.bcwp-document-item').remove();
            }
        } catch (error) {
            console.error('Failed to delete:', error);
        }
    });
});

// File preview function
async function previewFile(docId) {
    try {
        const response = await fetch(`${bcwpData.restUrl}/documents/${docId}`, {
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data) {
            const doc = result.data;
            const modal = document.getElementById('file-preview-modal');
            const previewContainer = document.getElementById('preview-container');

            document.getElementById('preview-file-name').textContent = doc.name;

            // Handle different file types
            if (doc.mime_type.startsWith('image/')) {
                previewContainer.innerHTML = `<img src="${doc.url}" style="max-width:100%; height:auto;">`;
            } else if (doc.mime_type === 'application/pdf') {
                previewContainer.innerHTML = `<embed src="${doc.url}" type="application/pdf" width="100%" height="600px">`;
            } else {
                previewContainer.innerHTML = `
                    <p>Preview not available for this file type.</p>
                    <a href="${doc.url}" download class="bcwp-btn bcwp-btn-primary">Download File</a>
                `;
            }

            modal.style.display = 'flex';

            modal.querySelector('.bcwp-modal-close').addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
    } catch (error) {
        console.error('Failed to load preview:', error);
    }
}

// Drag and drop file upload
const docsContainer = document.getElementById('documents-container');
if (docsContainer) {
    docsContainer.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        docsContainer.classList.add('bcwp-drag-over');
    });

    docsContainer.addEventListener('dragleave', () => {
        docsContainer.classList.remove('bcwp-drag-over');
    });

    docsContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        docsContainer.classList.remove('bcwp-drag-over');

        const files = Array.from(e.dataTransfer.files);
        if (files.length > 0) {
            uploadFiles(files);
        }
    });
}
</script>
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
