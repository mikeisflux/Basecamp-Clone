<?php
/**
 * Trash Viewer Page
 *
 * Allows account owners to view and restore all deleted items
 * across the entire account.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'Trash' );
?>

<div class="trash-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">🗑️ Trash</h1>
    <p class="page-subtitle">View and restore deleted items from across your entire account.</p>

    <!-- Trash Controls -->
    <div class="trash-section">
        <div class="trash-controls">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All Items</button>
                <button class="filter-tab" data-filter="messages">Messages</button>
                <button class="filter-tab" data-filter="todos">To-dos</button>
                <button class="filter-tab" data-filter="documents">Documents</button>
                <button class="filter-tab" data-filter="events">Events</button>
                <button class="filter-tab" data-filter="projects">Projects</button>
            </div>

            <div class="trash-actions">
                <button class="action-button danger-button" onclick="emptyTrash()">Empty Trash</button>
            </div>
        </div>
    </div>

    <!-- Trash Items List -->
    <div class="trash-section">
        <div id="trash-items-list" class="trash-items-list">
            <div class="loading-message">Loading trash...</div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.trash-page-wrapper {
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

.trash-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
}

.trash-controls {
    margin-bottom: 20px;
}

.filter-tabs {
    margin-bottom: 16px;
    border-bottom: 2px solid #e0e0e0;
}

.filter-tab {
    padding: 10px 20px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    color: #666;
    margin-right: 8px;
    margin-bottom: -2px;
}

.filter-tab:hover {
    color: #0066cc;
}

.filter-tab.active {
    color: #0066cc;
    border-bottom-color: #0066cc;
}

.trash-actions {
    padding-top: 12px;
}

.action-button {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
}

.danger-button {
    background: #dc3545;
    color: white;
}

.danger-button:hover {
    background: #c82333;
}

.trash-items-list {
    min-height: 300px;
}

.trash-item {
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 16px;
    border-left: 4px solid #999;
}

.trash-item.message {
    border-left-color: #10b981;
}

.trash-item.todo {
    border-left-color: #f59e0b;
}

.trash-item.document {
    border-left-color: #6366f1;
}

.trash-item.event {
    border-left-color: #ec4899;
}

.trash-item.project {
    border-left-color: #dc3545;
}

.item-header {
    margin-bottom: 12px;
}

.item-type-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #e0e0e0;
    color: #666;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 3px;
    margin-bottom: 8px;
}

.item-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.item-meta {
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
}

.item-project {
    font-size: 14px;
    color: #666;
    font-style: italic;
    margin-bottom: 12px;
}

.item-preview {
    padding: 12px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
    max-height: 100px;
    overflow: hidden;
}

.item-actions {
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
}

.item-action-button {
    display: inline-block;
    padding: 6px 12px;
    margin-right: 8px;
    margin-bottom: 8px;
    border-radius: 4px;
    font-size: 14px;
    text-decoration: none;
    cursor: pointer;
    border: none;
    font-weight: 500;
}

.action-restore {
    background: #10b981;
    color: white;
}

.action-restore:hover {
    background: #059669;
}

.action-delete {
    background: #dc3545;
    color: white;
}

.action-delete:hover {
    background: #c82333;
}

.loading-message {
    text-align: center;
    padding: 60px 40px;
    color: #666;
    font-size: 15px;
}

.empty-message {
    text-align: center;
    padding: 60px 40px;
    color: #666;
}

.empty-message-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.empty-message-text {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
}

.empty-message-subtext {
    font-size: 14px;
    color: #999;
}

@media (max-width: 768px) {
    .trash-page-wrapper {
        padding: 12px;
    }

    .trash-section {
        padding: 16px;
    }

    .page-title {
        font-size: 24px;
    }

    .filter-tabs {
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFilter = 'all';

    loadTrashItems();

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            loadTrashItems();
        });
    });

    window.restoreItem = restoreItem;
    window.permanentlyDelete = permanentlyDelete;
    window.emptyTrash = emptyTrash;
});

async function loadTrashItems() {
    try {
        const response = await fetch(`${pfobData.restUrl}/trash`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderTrashItems(data.items || []);
        } else {
            document.getElementById('trash-items-list').innerHTML = '<div class="empty-message">Failed to load trash</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        // Show placeholder data
        renderPlaceholderItems();
    }
}

function renderTrashItems(items) {
    if (items.length === 0) {
        document.getElementById('trash-items-list').innerHTML = `
            <div class="empty-message">
                <div class="empty-message-icon">🗑️</div>
                <div class="empty-message-text">Trash is empty</div>
                <div class="empty-message-subtext">Deleted items will appear here</div>
            </div>
        `;
        return;
    }

    const html = items.map(item => `
        <div class="trash-item ${item.type}">
            <div class="item-header">
                <span class="item-type-badge">${item.type}</span>
                <div class="item-title">${item.title}</div>
            </div>

            <div class="item-meta">
                Deleted by ${item.deleted_by} on ${item.deleted_at}
                ${item.auto_delete ? ` • Will be permanently deleted on ${item.auto_delete}` : ''}
            </div>

            ${item.project ? `<div class="item-project">from ${item.project}</div>` : ''}

            ${item.preview ? `<div class="item-preview">${item.preview}</div>` : ''}

            <div class="item-actions">
                <button class="item-action-button action-restore" onclick="restoreItem(${item.id}, '${item.type}')">
                    Restore
                </button>
                <button class="item-action-button action-delete" onclick="permanentlyDelete(${item.id}, '${item.type}')">
                    Delete Permanently
                </button>
            </div>
        </div>
    `).join('');

    document.getElementById('trash-items-list').innerHTML = html;
}

function renderPlaceholderItems() {
    const placeholderItems = [
        {
            id: 1,
            type: 'message',
            title: 'Weekly Status Update',
            deleted_by: 'John Smith',
            deleted_at: 'January 20, 2025 at 3:45 PM',
            auto_delete: 'February 20, 2025',
            project: 'Website Redesign',
            preview: 'Hi team, here\'s our progress for this week. We completed the homepage mockups and started working on...'
        },
        {
            id: 2,
            type: 'todo',
            title: 'Review design mockups',
            deleted_by: 'Jane Doe',
            deleted_at: 'January 18, 2025 at 10:20 AM',
            auto_delete: 'February 18, 2025',
            project: 'Website Redesign',
            preview: null
        },
        {
            id: 3,
            type: 'document',
            title: 'Project_Proposal_Draft.pdf',
            deleted_by: 'Bob Wilson',
            deleted_at: 'January 15, 2025 at 2:15 PM',
            auto_delete: 'February 15, 2025',
            project: 'Marketing Campaign',
            preview: null
        }
    ];

    renderTrashItems(placeholderItems);
}

async function restoreItem(itemId, itemType) {
    if (!confirm('Are you sure you want to restore this item?')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/trash/${itemId}/restore`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({ type: itemType })
        });

        const data = await response.json();

        if (data.success) {
            alert('Item restored successfully!');
            loadTrashItems();
        } else {
            alert('Failed to restore item: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Restore functionality coming soon. This will restore the item to its original location.');
    }
}

async function permanentlyDelete(itemId, itemType) {
    if (!confirm('Are you sure you want to PERMANENTLY delete this item? This action cannot be undone!')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/trash/${itemId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({ type: itemType })
        });

        const data = await response.json();

        if (data.success) {
            alert('Item permanently deleted.');
            loadTrashItems();
        } else {
            alert('Failed to delete item: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Permanent delete functionality coming soon.');
    }
}

async function emptyTrash() {
    if (!confirm('Are you sure you want to PERMANENTLY delete ALL items in the trash? This action cannot be undone!')) {
        return;
    }

    if (!confirm('This will delete everything permanently. Are you absolutely sure?')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/trash/empty`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            alert('Trash emptied successfully.');
            loadTrashItems();
        } else {
            alert('Failed to empty trash: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Empty trash functionality coming soon.');
    }
}
</script>

<?php
PFOB_Template::footer();
