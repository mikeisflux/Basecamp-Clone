<?php
/**
 * Manage Public Items Page
 *
 * Allows account owners to manage items that are publicly accessible
 * without requiring login (e.g., public message boards, shared files).
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'Manage Public Items' );
?>

<div class="public-items-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Manage Public Items</h1>
    <p class="page-subtitle">Control what content is publicly accessible without requiring a login.</p>

    <!-- Public Access Settings -->
    <div class="public-section">
        <h2 class="section-title">Public Access Settings</h2>

        <div class="setting-row">
            <label class="setting-checkbox">
                <input type="checkbox" id="allow-public-access" checked>
                <span class="setting-label">Allow public access to items</span>
            </label>
            <p class="setting-description">When enabled, you can make specific items publicly accessible via unique URLs.</p>
        </div>

        <div class="setting-row">
            <label class="setting-checkbox">
                <input type="checkbox" id="require-password">
                <span class="setting-label">Require password for public items</span>
            </label>
            <p class="setting-description">Visitors must enter a password to view public items.</p>
        </div>

        <div class="setting-row" style="margin-left: 30px; display: none;" id="password-field">
            <label class="form-label">Public Access Password</label>
            <input type="text" class="form-input" placeholder="Enter password" style="max-width: 300px;">
        </div>
    </div>

    <!-- Public Items List -->
    <div class="public-section">
        <h2 class="section-title">Currently Public Items</h2>

        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All</button>
            <button class="filter-tab" data-filter="messages">Messages</button>
            <button class="filter-tab" data-filter="documents">Documents</button>
            <button class="filter-tab" data-filter="projects">Projects</button>
        </div>

        <div id="public-items-list" class="public-items-list">
            <div class="loading-message">Loading public items...</div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.public-items-page-wrapper {
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

.public-section {
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

.setting-row {
    margin-bottom: 20px;
}

.setting-checkbox {
    display: block;
    cursor: pointer;
}

.setting-checkbox input[type="checkbox"] {
    margin-right: 10px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.setting-label {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.setting-description {
    margin: 8px 0 0 28px;
    font-size: 14px;
    color: #666;
}

.form-label {
    display: block;
    font-size: 15px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.filter-tabs {
    margin-bottom: 20px;
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

.public-items-list {
    min-height: 200px;
}

.public-item {
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 16px;
}

.item-header {
    margin-bottom: 12px;
}

.item-type {
    display: inline-block;
    padding: 4px 10px;
    background: #0066cc;
    color: white;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 3px;
    margin-bottom: 8px;
}

.item-type.message {
    background: #10b981;
}

.item-type.document {
    background: #f59e0b;
}

.item-type.project {
    background: #6366f1;
}

.item-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.item-project {
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
}

.item-url {
    margin-bottom: 12px;
}

.url-label {
    font-size: 13px;
    font-weight: 500;
    color: #666;
    margin-bottom: 4px;
}

.url-input {
    display: block;
    width: 100%;
    padding: 8px 12px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    font-family: monospace;
    color: #0066cc;
}

.item-stats {
    font-size: 13px;
    color: #666;
    margin-bottom: 12px;
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

.action-copy {
    background: #0066cc;
    color: white;
}

.action-copy:hover {
    background: #0052a3;
}

.action-revoke {
    background: #dc3545;
    color: white;
}

.action-revoke:hover {
    background: #c82333;
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
    .public-items-page-wrapper {
        padding: 12px;
    }

    .public-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFilter = 'all';

    loadPublicItems();

    // Password field toggle
    document.getElementById('require-password').addEventListener('change', function() {
        document.getElementById('password-field').style.display = this.checked ? 'block' : 'none';
    });

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            loadPublicItems();
        });
    });

    window.copyPublicUrl = copyPublicUrl;
    window.revokePublicAccess = revokePublicAccess;
});

async function loadPublicItems() {
    try {
        const response = await fetch(`${pfobData.restUrl}/account/public-items`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderPublicItems(data.items || []);
        } else {
            document.getElementById('public-items-list').innerHTML = '<div class="empty-message">No public items found</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        // Show placeholder data
        renderPlaceholderItems();
    }
}

function renderPublicItems(items) {
    if (items.length === 0) {
        document.getElementById('public-items-list').innerHTML = '<div class="empty-message">No public items. Make items public from their settings pages.</div>';
        return;
    }

    const html = items.map(item => `
        <div class="public-item">
            <div class="item-header">
                <span class="item-type ${item.type}">${item.type}</span>
                <div class="item-title">${item.title}</div>
                ${item.project ? `<div class="item-project">in ${item.project}</div>` : ''}
            </div>

            <div class="item-url">
                <div class="url-label">Public URL:</div>
                <input type="text" class="url-input" value="${item.public_url}" readonly onclick="this.select()">
            </div>

            <div class="item-stats">
                Made public ${item.made_public_at} • ${item.views || 0} views
            </div>

            <div class="item-actions">
                <button class="item-action-button action-copy" onclick="copyPublicUrl('${item.public_url}')">Copy URL</button>
                <button class="item-action-button action-revoke" onclick="revokePublicAccess(${item.id})">Revoke Access</button>
            </div>
        </div>
    `).join('');

    document.getElementById('public-items-list').innerHTML = html;
}

function renderPlaceholderItems() {
    const placeholderItems = [
        {
            id: 1,
            type: 'message',
            title: 'Project Kickoff Meeting Notes',
            project: 'Website Redesign',
            public_url: 'https://projectfob.com/public/abc123',
            made_public_at: 'January 15, 2025',
            views: 42
        },
        {
            id: 2,
            type: 'document',
            title: 'Brand Guidelines.pdf',
            project: 'Marketing Materials',
            public_url: 'https://projectfob.com/public/xyz789',
            made_public_at: 'January 10, 2025',
            views: 18
        }
    ];

    renderPublicItems(placeholderItems);
}

function copyPublicUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('URL copied to clipboard!');
    }).catch(() => {
        prompt('Copy this URL:', url);
    });
}

function revokePublicAccess(itemId) {
    if (!confirm('Are you sure you want to revoke public access to this item? The public URL will stop working.')) {
        return;
    }

    alert('Revoke access functionality coming soon. This will make the item private again.');
    // TODO: API call to revoke access
}
</script>

<?php
PFOB_Template::footer();
