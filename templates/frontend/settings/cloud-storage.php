<?php
/**
 * Cloud Storage Integration (Dropbox & Google Drive)
 */

PFOB_Template::header( 'Cloud Storage Integration' );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-cloud-storage">

        <header class="pfob-page-header">
            <div>
                <h1>☁️ Cloud Storage Integration</h1>
                <p class="pfob-subtitle">Connect Dropbox and Google Drive to sync your project files</p>
            </div>
        </header>

        <!-- Dropbox Integration -->
        <div class="pfob-settings-section">
            <div class="integration-card">
                <div class="integration-header">
                    <div class="integration-logo">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="#0061FF">
                            <path d="M6 1.807L0 5.629l6 3.822 6.001-3.822L6 1.807zM18 1.807l-6 3.822 6 3.822 6-3.822-6-3.822zM0 13.274l6 3.822 6.001-3.822L6 9.452l-6 3.822zm12.001 0l6 3.822 6-3.822-6-3.822-6 3.822zM6 18.371l6.001 3.822 6-3.822-6.001-3.822L6 18.371z"/>
                        </svg>
                    </div>
                    <div class="integration-info">
                        <h2>Dropbox</h2>
                        <p class="integration-description">Sync files between ProjectFOB and your Dropbox account</p>
                    </div>
                </div>

                <div id="dropbox-status" class="integration-status">
                    <div class="status-loading">Checking connection...</div>
                </div>

                <div id="dropbox-connected" class="integration-connected" style="display: none;">
                    <div class="connection-info">
                        <div class="connection-icon">✓</div>
                        <div class="connection-details">
                            <strong>Connected</strong>
                            <p id="dropbox-account-info"></p>
                        </div>
                    </div>

                    <div class="integration-features">
                        <h3>Dropbox Features</h3>
                        <div class="feature-toggles">
                            <label class="toggle-label">
                                <input type="checkbox" id="dropbox-auto-sync" class="toggle-checkbox">
                                <div class="toggle-content">
                                    <strong>Auto-sync Documents</strong>
                                    <p>Automatically backup all project documents to Dropbox</p>
                                </div>
                            </label>
                            <label class="toggle-label">
                                <input type="checkbox" id="dropbox-folder-sync" class="toggle-checkbox">
                                <div class="toggle-content">
                                    <strong>Folder Organization</strong>
                                    <p>Create separate folders for each project</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="integration-actions">
                        <button class="pfob-btn pfob-btn-secondary" id="view-dropbox-folder">
                            View Dropbox Folder
                        </button>
                        <button class="pfob-btn pfob-btn-danger" id="disconnect-dropbox">
                            Disconnect
                        </button>
                    </div>
                </div>

                <div id="dropbox-disconnected" class="integration-disconnected" style="display: none;">
                    <div class="disconnected-message">
                        <p>Connect your Dropbox account to:</p>
                        <ul>
                            <li>Automatically backup project files</li>
                            <li>Share files directly from Dropbox</li>
                            <li>Sync documents across devices</li>
                            <li>Access files offline through Dropbox</li>
                        </ul>
                    </div>
                    <button class="pfob-btn pfob-btn-primary" id="connect-dropbox">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 8px;">
                            <path d="M6 1.807L0 5.629l6 3.822 6.001-3.822L6 1.807zM18 1.807l-6 3.822 6 3.822 6-3.822-6-3.822zM0 13.274l6 3.822 6.001-3.822L6 9.452l-6 3.822zm12.001 0l6 3.822 6-3.822-6-3.822-6 3.822zM6 18.371l6.001 3.822 6-3.822-6.001-3.822L6 18.371z"/>
                        </svg>
                        Connect Dropbox
                    </button>
                </div>
            </div>
        </div>

        <!-- Google Drive Integration -->
        <div class="pfob-settings-section">
            <div class="integration-card">
                <div class="integration-header">
                    <div class="integration-logo">
                        <svg width="60" height="60" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M8.5 6.5L15.5 6.5 20.25 15 15.5 23.5 3.75 23.5 8.5 15z"/>
                            <path fill="#34A853" d="M8.5 6.5L1.75 15 8.5 23.5 15.5 23.5z"/>
                            <path fill="#FBBC04" d="M15.5 6.5L8.5 6.5 8.5 23.5 15.5 23.5z"/>
                            <path fill="#EA4335" d="M12 1L8.5 6.5 15.5 6.5z"/>
                        </svg>
                    </div>
                    <div class="integration-info">
                        <h2>Google Drive</h2>
                        <p class="integration-description">Connect Google Drive to access and share files</p>
                    </div>
                </div>

                <div id="gdrive-status" class="integration-status">
                    <div class="status-loading">Checking connection...</div>
                </div>

                <div id="gdrive-connected" class="integration-connected" style="display: none;">
                    <div class="connection-info">
                        <div class="connection-icon">✓</div>
                        <div class="connection-details">
                            <strong>Connected</strong>
                            <p id="gdrive-account-info"></p>
                        </div>
                    </div>

                    <div class="integration-features">
                        <h3>Google Drive Features</h3>
                        <div class="feature-toggles">
                            <label class="toggle-label">
                                <input type="checkbox" id="gdrive-auto-sync" class="toggle-checkbox">
                                <div class="toggle-content">
                                    <strong>Auto-sync Documents</strong>
                                    <p>Automatically backup all project documents to Google Drive</p>
                                </div>
                            </label>
                            <label class="toggle-label">
                                <input type="checkbox" id="gdrive-folder-sync" class="toggle-checkbox">
                                <div class="toggle-content">
                                    <strong>Folder Organization</strong>
                                    <p>Create separate folders for each project</p>
                                </div>
                            </label>
                            <label class="toggle-label">
                                <input type="checkbox" id="gdrive-sharing" class="toggle-checkbox">
                                <div class="toggle-content">
                                    <strong>Enable Sharing</strong>
                                    <p>Allow team members to access shared Drive files</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="integration-actions">
                        <button class="pfob-btn pfob-btn-secondary" id="view-gdrive-folder">
                            View Google Drive Folder
                        </button>
                        <button class="pfob-btn pfob-btn-danger" id="disconnect-gdrive">
                            Disconnect
                        </button>
                    </div>
                </div>

                <div id="gdrive-disconnected" class="integration-disconnected" style="display: none;">
                    <div class="disconnected-message">
                        <p>Connect your Google Drive account to:</p>
                        <ul>
                            <li>Automatically backup project files</li>
                            <li>Share files with Google Drive sharing</li>
                            <li>Collaborate using Google Docs/Sheets</li>
                            <li>Access files from any device</li>
                        </ul>
                    </div>
                    <button class="pfob-btn pfob-btn-primary" id="connect-gdrive">
                        <svg width="20" height="20" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 8px;">
                            <path fill="currentColor" d="M8.5 6.5L15.5 6.5 20.25 15 15.5 23.5 3.75 23.5 8.5 15z"/>
                        </svg>
                        Connect Google Drive
                    </button>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

// Load connection status on page load
loadDropboxStatus();
loadGDriveStatus();

async function loadDropboxStatus() {
    const statusDiv = document.getElementById('dropbox-status');
    const connectedDiv = document.getElementById('dropbox-connected');
    const disconnectedDiv = document.getElementById('dropbox-disconnected');

    try {
        const response = await fetch(`${pfobData.restUrl}/integrations/dropbox/status`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        statusDiv.style.display = 'none';

        if (result.success && result.data.is_connected) {
            connectedDiv.style.display = 'block';
            disconnectedDiv.style.display = 'none';

            document.getElementById('dropbox-account-info').textContent = result.data.account_email || 'Connected';
            document.getElementById('dropbox-auto-sync').checked = result.data.auto_sync || false;
            document.getElementById('dropbox-folder-sync').checked = result.data.folder_sync || false;
        } else {
            connectedDiv.style.display = 'none';
            disconnectedDiv.style.display = 'block';
        }
    } catch (error) {
        console.error('Failed to load Dropbox status:', error);
        statusDiv.innerHTML = '<div class="status-error">Failed to load connection status</div>';
    }
}

async function loadGDriveStatus() {
    const statusDiv = document.getElementById('gdrive-status');
    const connectedDiv = document.getElementById('gdrive-connected');
    const disconnectedDiv = document.getElementById('gdrive-disconnected');

    try {
        const response = await fetch(`${pfobData.restUrl}/integrations/gdrive/status`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        statusDiv.style.display = 'none';

        if (result.success && result.data.is_connected) {
            connectedDiv.style.display = 'block';
            disconnectedDiv.style.display = 'none';

            document.getElementById('gdrive-account-info').textContent = result.data.account_email || 'Connected';
            document.getElementById('gdrive-auto-sync').checked = result.data.auto_sync || false;
            document.getElementById('gdrive-folder-sync').checked = result.data.folder_sync || false;
            document.getElementById('gdrive-sharing').checked = result.data.sharing_enabled || false;
        } else {
            connectedDiv.style.display = 'none';
            disconnectedDiv.style.display = 'block';
        }
    } catch (error) {
        console.error('Failed to load Google Drive status:', error);
        statusDiv.innerHTML = '<div class="status-error">Failed to load connection status</div>';
    }
}

// Dropbox connection
document.getElementById('connect-dropbox').addEventListener('click', async () => {
    try {
        const response = await fetch(`${pfobData.restUrl}/integrations/dropbox/auth-url`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data.auth_url) {
            window.location.href = result.data.auth_url;
        } else {
            alert('Failed to get Dropbox authorization URL');
        }
    } catch (error) {
        console.error('Failed to connect Dropbox:', error);
        alert('Failed to connect Dropbox');
    }
});

// Dropbox disconnection
document.getElementById('disconnect-dropbox').addEventListener('click', async () => {
    if (!confirm('Are you sure you want to disconnect Dropbox? This will not delete any files.')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/integrations/dropbox/disconnect`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            loadDropboxStatus();
        } else {
            alert('Failed to disconnect Dropbox');
        }
    } catch (error) {
        console.error('Failed to disconnect Dropbox:', error);
        alert('Failed to disconnect Dropbox');
    }
});

// Google Drive connection
document.getElementById('connect-gdrive').addEventListener('click', async () => {
    try {
        const response = await fetch(`${pfobData.restUrl}/integrations/gdrive/auth-url`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data.auth_url) {
            window.location.href = result.data.auth_url;
        } else {
            alert('Failed to get Google Drive authorization URL');
        }
    } catch (error) {
        console.error('Failed to connect Google Drive:', error);
        alert('Failed to connect Google Drive');
    }
});

// Google Drive disconnection
document.getElementById('disconnect-gdrive').addEventListener('click', async () => {
    if (!confirm('Are you sure you want to disconnect Google Drive? This will not delete any files.')) {
        return;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/integrations/gdrive/disconnect`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            loadGDriveStatus();
        } else {
            alert('Failed to disconnect Google Drive');
        }
    } catch (error) {
        console.error('Failed to disconnect Google Drive:', error);
        alert('Failed to disconnect Google Drive');
    }
});

// Toggle handlers
['dropbox-auto-sync', 'dropbox-folder-sync', 'gdrive-auto-sync', 'gdrive-folder-sync', 'gdrive-sharing'].forEach(id => {
    document.getElementById(id).addEventListener('change', async (e) => {
        const service = id.startsWith('dropbox') ? 'dropbox' : 'gdrive';
        const setting = id.replace(`${service}-`, '');

        try {
            const response = await fetch(`${pfobData.restUrl}/integrations/${service}/settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({
                    [setting]: e.target.checked
                })
            });

            const result = await response.json();

            if (!result.success) {
                alert('Failed to update settings');
                e.target.checked = !e.target.checked; // Revert
            }
        } catch (error) {
            console.error('Failed to update settings:', error);
            alert('Failed to update settings');
            e.target.checked = !e.target.checked; // Revert
        }
    });
});

// View folder buttons
document.getElementById('view-dropbox-folder').addEventListener('click', () => {
    window.open('https://www.dropbox.com/home/Apps/ProjectFOB', '_blank');
});

document.getElementById('view-gdrive-folder').addEventListener('click', async () => {
    try {
        const response = await fetch(`${pfobData.restUrl}/integrations/gdrive/folder-url`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data.folder_url) {
            window.open(result.data.folder_url, '_blank');
        } else {
            alert('Failed to get folder URL');
        }
    } catch (error) {
        console.error('Failed to get folder URL:', error);
        alert('Failed to get folder URL');
    }
});

// Handle OAuth callback
const urlParams = new URLSearchParams(window.location.search);
const code = urlParams.get('code');
const state = urlParams.get('state');
const service = urlParams.get('service');

if (code && state && service) {
    (async () => {
        try {
            const response = await fetch(`${pfobData.restUrl}/integrations/${service}/callback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({ code, state })
            });

            const result = await response.json();

            // Remove OAuth parameters from URL
            window.history.replaceState({}, document.title, window.location.pathname);

            if (result.success) {
                if (service === 'dropbox') {
                    loadDropboxStatus();
                } else {
                    loadGDriveStatus();
                }
            } else {
                alert(`Failed to connect ${service}: ${result.message || 'Unknown error'}`);
            }
        } catch (error) {
            console.error('OAuth callback error:', error);
            alert('Failed to complete authorization');
        }
    })();
}
</script>

<style>
.pfob-cloud-storage {
    max-width: 1000px;
    margin: 0 auto;
}

.pfob-settings-section {
    margin-bottom: 30px;
}

.integration-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.integration-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.integration-logo {
    flex-shrink: 0;
}

.integration-info h2 {
    margin: 0 0 8px 0;
    font-size: 24px;
    color: #333;
}

.integration-description {
    margin: 0;
    color: #666;
    font-size: 15px;
}

.integration-status {
    text-align: center;
    padding: 20px;
}

.status-loading,
.status-error {
    padding: 15px;
    border-radius: 6px;
    background: #f8f9fa;
    color: #666;
}

.status-error {
    background: #fff3cd;
    color: #856404;
}

.connection-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #d4edda;
    border-radius: 8px;
    margin-bottom: 25px;
}

.connection-icon {
    width: 40px;
    height: 40px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    flex-shrink: 0;
}

.connection-details strong {
    display: block;
    color: #155724;
    font-size: 16px;
    margin-bottom: 4px;
}

.connection-details p {
    margin: 0;
    color: #155724;
    opacity: 0.9;
    font-size: 14px;
}

.integration-features {
    margin-bottom: 25px;
}

.integration-features h3 {
    font-size: 18px;
    margin: 0 0 15px 0;
    color: #333;
}

.feature-toggles {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.toggle-label {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-label:hover {
    border-color: #2d9061;
    background: #f0f8f4;
}

.toggle-checkbox {
    margin-top: 4px;
    margin-right: 15px;
    flex-shrink: 0;
}

.toggle-content strong {
    display: block;
    margin-bottom: 4px;
    font-size: 15px;
    color: #333;
}

.toggle-content p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.integration-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.disconnected-message {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 20px;
}

.disconnected-message p {
    margin: 0 0 15px 0;
    font-size: 15px;
    color: #666;
}

.disconnected-message ul {
    margin: 0;
    padding-left: 25px;
    color: #666;
}

.disconnected-message li {
    margin-bottom: 8px;
}

.pfob-btn-danger {
    background: #dc3545;
    color: white;
}

.pfob-btn-danger:hover {
    background: #c82333;
}

@media (max-width: 768px) {
    .integration-card {
        padding: 20px;
    }

    .integration-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .integration-actions {
        flex-direction: column;
    }

    .integration-actions button {
        width: 100%;
    }
}
</style>

<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
