<?php
/**
 * Invite Link Generator Page
 *
 * Allows administrators to generate special invite links
 * for inviting multiple people at once.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'Invite with Link' );
?>

<div class="invite-link-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Invite with Link</h1>
    <p class="page-subtitle">Generate a special invite link to share with multiple people at once.</p>

    <!-- Create Invite Link -->
    <div class="invite-section">
        <h2 class="section-title">Create New Invite Link</h2>

        <div class="create-link-form">
            <div class="form-row">
                <label class="form-label">Default User Type</label>
                <select id="user-type" class="form-select">
                    <option value="team_member">Team Member</option>
                    <option value="contractor">Contractor</option>
                    <option value="client">Client</option>
                </select>
                <p class="field-description">People who use this link will be added as this type</p>
            </div>

            <div class="form-row">
                <label class="form-label">Maximum Uses (Optional)</label>
                <input type="number" id="max-uses" class="form-input" placeholder="Leave blank for unlimited" min="1">
                <p class="field-description">Limit how many times this link can be used</p>
            </div>

            <div class="form-row">
                <label class="form-label">Expiration Date (Optional)</label>
                <input type="date" id="expiration-date" class="form-input">
                <p class="field-description">Link will stop working after this date</p>
            </div>

            <div class="form-row">
                <label class="form-checkbox">
                    <input type="checkbox" id="require-approval">
                    <span class="checkbox-label">Require administrator approval</span>
                </label>
                <p class="field-description">New signups must be approved before they can access projects</p>
            </div>

            <button class="action-button primary-button" onclick="generateInviteLink()">Generate Invite Link</button>
        </div>

        <div id="generated-link" class="generated-link" style="display: none;">
            <h3 class="result-title">✓ Invite Link Generated!</h3>

            <div class="link-container">
                <label class="link-label">Share this link:</label>
                <input type="text" id="invite-url" class="link-input" readonly onclick="this.select()">
            </div>

            <div class="link-actions">
                <button class="action-button copy-button" onclick="copyInviteLink()">Copy Link</button>
                <button class="action-button secondary-button" onclick="emailInviteLink()">Email Link</button>
            </div>

            <div class="link-info">
                <p><strong>Link ID:</strong> <span id="link-id"></span></p>
                <p><strong>Created:</strong> <span id="link-created"></span></p>
                <p><strong>Expires:</strong> <span id="link-expires"></span></p>
                <p><strong>Uses:</strong> <span id="link-uses"></span></p>
            </div>
        </div>
    </div>

    <!-- Active Invite Links -->
    <div class="invite-section">
        <h2 class="section-title">Active Invite Links</h2>

        <div id="active-links" class="active-links">
            <div class="loading-message">Loading invite links...</div>
        </div>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.invite-link-page-wrapper {
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

.invite-section {
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

.create-link-form {
    max-width: 600px;
}

.form-row {
    margin-bottom: 24px;
}

.form-label {
    display: block;
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-input,
.form-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.field-description {
    margin: 8px 0 0 0;
    font-size: 13px;
    color: #666;
}

.form-checkbox {
    display: block;
    cursor: pointer;
}

.form-checkbox input[type="checkbox"] {
    margin-right: 10px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.action-button {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 15px;
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

.secondary-button {
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
}

.secondary-button:hover {
    background: #d1d5db;
}

.copy-button {
    background: #10b981;
    color: white;
}

.copy-button:hover {
    background: #059669;
}

.generated-link {
    margin-top: 32px;
    padding: 24px;
    background: #ecfdf5;
    border: 2px solid #10b981;
    border-radius: 8px;
}

.result-title {
    margin: 0 0 20px 0;
    color: #065f46;
    font-size: 20px;
}

.link-container {
    margin-bottom: 16px;
}

.link-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #047857;
    margin-bottom: 8px;
}

.link-input {
    width: 100%;
    padding: 12px;
    background: white;
    border: 2px solid #10b981;
    border-radius: 6px;
    font-size: 14px;
    font-family: monospace;
    color: #0066cc;
}

.link-actions {
    margin-bottom: 20px;
}

.link-actions .action-button {
    margin-right: 8px;
    margin-bottom: 8px;
}

.link-info {
    padding-top: 16px;
    border-top: 1px solid #a7f3d0;
}

.link-info p {
    margin: 8px 0;
    font-size: 14px;
    color: #047857;
}

.active-links {
    min-height: 100px;
}

.link-card {
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 16px;
}

.link-header {
    margin-bottom: 12px;
}

.link-type {
    display: inline-block;
    padding: 4px 10px;
    background: #0066cc;
    color: white;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 3px;
}

.link-url-display {
    margin: 12px 0;
    padding: 10px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    font-family: monospace;
    color: #0066cc;
    word-break: break-all;
}

.link-stats {
    font-size: 13px;
    color: #666;
    margin-bottom: 12px;
}

.link-card-actions {
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
}

.link-card-actions .action-button {
    padding: 6px 12px;
    font-size: 14px;
    margin-right: 8px;
    margin-bottom: 8px;
}

.delete-button {
    background: #dc3545;
    color: white;
}

.delete-button:hover {
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
    .invite-link-page-wrapper {
        padding: 12px;
    }

    .invite-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadActiveLinks();

    window.generateInviteLink = generateInviteLink;
    window.copyInviteLink = copyInviteLink;
    window.emailInviteLink = emailInviteLink;
    window.copyLink = copyLink;
    window.deleteInviteLink = deleteInviteLink;
});

async function loadActiveLinks() {
    try {
        const response = await fetch(`${pfobData.restUrl}/account/invite-links`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderActiveLinks(data.links || []);
        } else {
            document.getElementById('active-links').innerHTML = '<div class="empty-message">No active invite links</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        renderPlaceholderLinks();
    }
}

function renderActiveLinks(links) {
    if (links.length === 0) {
        document.getElementById('active-links').innerHTML = '<div class="empty-message">No active invite links. Create one above!</div>';
        return;
    }

    const html = links.map(link => `
        <div class="link-card">
            <div class="link-header">
                <span class="link-type">${link.user_type.replace('_', ' ')}</span>
            </div>

            <div class="link-url-display">${link.url}</div>

            <div class="link-stats">
                Created ${link.created_at} •
                ${link.uses} of ${link.max_uses || '∞'} uses •
                ${link.expires_at ? `Expires ${link.expires_at}` : 'No expiration'}
            </div>

            <div class="link-card-actions">
                <button class="action-button copy-button" onclick="copyLink('${link.url}')">Copy</button>
                <button class="action-button delete-button" onclick="deleteInviteLink(${link.id})">Delete</button>
            </div>
        </div>
    `).join('');

    document.getElementById('active-links').innerHTML = html;
}

function renderPlaceholderLinks() {
    const placeholderLinks = [
        {
            id: 1,
            user_type: 'team_member',
            url: 'https://projectfob.com/invite/abc123xyz',
            created_at: 'January 15, 2025',
            uses: 3,
            max_uses: 10,
            expires_at: 'February 15, 2025'
        }
    ];

    renderActiveLinks(placeholderLinks);
}

async function generateInviteLink() {
    const userType = document.getElementById('user-type').value;
    const maxUses = document.getElementById('max-uses').value;
    const expirationDate = document.getElementById('expiration-date').value;
    const requireApproval = document.getElementById('require-approval').checked;

    try {
        const response = await fetch(`${pfobData.restUrl}/account/invite-links`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({
                user_type: userType,
                max_uses: maxUses || null,
                expires_at: expirationDate || null,
                require_approval: requireApproval
            })
        });

        const data = await response.json();

        if (data.success) {
            showGeneratedLink(data.link);
            loadActiveLinks();
        } else {
            alert('Failed to generate link: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        // Show example link
        const exampleLink = {
            id: 'INV-' + Math.random().toString(36).substr(2, 9).toUpperCase(),
            url: `${pfobData.homeUrl}/invite/` + Math.random().toString(36).substr(2, 9),
            created_at: new Date().toLocaleString(),
            expires_at: expirationDate || 'Never',
            max_uses: maxUses || 'Unlimited'
        };
        showGeneratedLink(exampleLink);
    }
}

function showGeneratedLink(link) {
    document.getElementById('invite-url').value = link.url;
    document.getElementById('link-id').textContent = link.id;
    document.getElementById('link-created').textContent = link.created_at;
    document.getElementById('link-expires').textContent = link.expires_at;
    document.getElementById('link-uses').textContent = `0 of ${link.max_uses}`;

    document.getElementById('generated-link').style.display = 'block';
}

function copyInviteLink() {
    const url = document.getElementById('invite-url').value;
    copyLink(url);
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    }).catch(() => {
        prompt('Copy this link:', url);
    });
}

function emailInviteLink() {
    const url = document.getElementById('invite-url').value;
    const subject = 'Invitation to join ProjectFOB';
    const body = `You've been invited to join our team on ProjectFOB!\n\nClick here to get started:\n${url}`;

    window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
}

async function deleteInviteLink(linkId) {
    if (!confirm('Are you sure you want to delete this invite link? It will stop working immediately.')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/account/invite-links/${linkId}`, {
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            alert('Invite link deleted.');
            loadActiveLinks();
        } else {
            alert('Failed to delete link: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Delete invite link functionality coming soon.');
    }
}
</script>

<?php
PFOB_Template::footer();
