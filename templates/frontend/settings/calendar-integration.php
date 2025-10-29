<?php
/**
 * Calendar Integration Settings Page
 */

BCWP_Template::header( 'Calendar Integration' );

// Handle OAuth callback
if ( isset( $_GET['code'] ) && isset( $_GET['state'] ) ) {
    $result = BCWP_Google_Calendar_Service::handle_oauth_callback(
        sanitize_text_field( $_GET['code'] ),
        sanitize_text_field( $_GET['state'] ),
        get_current_user_id()
    );

    if ( is_wp_error( $result ) ) {
        $error_message = $result->get_error_message();
    } else {
        $success_message = 'Successfully connected to Google Calendar!';
    }
}
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-calendar-integration">

        <header class="bcwp-page-header">
            <h1>📅 Calendar Integration</h1>
            <p class="bcwp-subtitle">Connect your Google Calendar to sync Basecamp events</p>
        </header>

        <?php if ( isset( $success_message ) ) : ?>
            <div class="bcwp-alert bcwp-alert-success">
                <?php echo esc_html( $success_message ); ?>
            </div>
        <?php endif; ?>

        <?php if ( isset( $error_message ) ) : ?>
            <div class="bcwp-alert bcwp-alert-error">
                <?php echo esc_html( $error_message ); ?>
            </div>
        <?php endif; ?>

        <div class="bcwp-settings-container">

            <div id="connection-status" class="bcwp-settings-section">
                <h2>Connection Status</h2>
                <div id="status-content">
                    <div class="bcwp-spinner"></div>
                    <p>Checking connection status...</p>
                </div>
            </div>

            <div id="integration-settings" class="bcwp-settings-section" style="display: none;">
                <h2>⚙️ Integration Settings</h2>

                <label class="bcwp-checkbox-label">
                    <input type="checkbox" id="auto-sync-checkbox">
                    <div class="bcwp-checkbox-content">
                        <strong>Auto-sync events</strong>
                        <p>Automatically sync new Basecamp events to Google Calendar</p>
                    </div>
                </label>

                <button class="bcwp-btn bcwp-btn-primary" id="save-settings-btn">
                    Save Settings
                </button>
            </div>

            <div class="bcwp-settings-section">
                <h2>💡 How it Works</h2>
                <ul class="bcwp-info-list">
                    <li>
                        <strong>iCal Export:</strong> Download your project events as an .ics file from the Schedule page and import it into any calendar application.
                    </li>
                    <li>
                        <strong>Google Calendar (Advanced):</strong> For administrators: Configure Google OAuth credentials in WordPress admin settings to enable direct Google Calendar synchronization.
                    </li>
                    <li>
                        <strong>Two-way Sync:</strong> Currently supports one-way sync from Basecamp to Google Calendar. Events created in Basecamp will appear in Google Calendar.
                    </li>
                </ul>
            </div>

            <div class="bcwp-settings-section">
                <h2>🔧 Setup Instructions</h2>
                <div class="bcwp-setup-steps">
                    <div class="bcwp-step">
                        <div class="bcwp-step-number">1</div>
                        <div class="bcwp-step-content">
                            <h3>Create Google Cloud Project</h3>
                            <p>Go to <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a> and create a new project.</p>
                        </div>
                    </div>

                    <div class="bcwp-step">
                        <div class="bcwp-step-number">2</div>
                        <div class="bcwp-step-content">
                            <h3>Enable Google Calendar API</h3>
                            <p>In your project, enable the Google Calendar API from the API Library.</p>
                        </div>
                    </div>

                    <div class="bcwp-step">
                        <div class="bcwp-step-number">3</div>
                        <div class="bcwp-step-content">
                            <h3>Create OAuth Credentials</h3>
                            <p>Create OAuth 2.0 credentials and set the authorized redirect URI to:</p>
                            <code class="bcwp-code-block"><?php echo esc_url( home_url( '/projectfob/settings/calendar-integration' ) ); ?></code>
                        </div>
                    </div>

                    <div class="bcwp-step">
                        <div class="bcwp-step-number">4</div>
                        <div class="bcwp-step-content">
                            <h3>Configure WordPress</h3>
                            <p>Add your Google OAuth credentials to WordPress admin settings:</p>
                            <ul>
                                <li>Settings → Basecamp WP Pro → Calendar Integration</li>
                                <li>Enter your Client ID and Client Secret</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>

<script>
const bcwpData = {
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

async function loadStatus() {
    try {
        const response = await fetch(`${bcwpData.restUrl}/calendar-integration/status`, {
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            displayStatus(result.data);
        }
    } catch (error) {
        console.error('Failed to load status:', error);
        document.getElementById('status-content').innerHTML = '<p class="bcwp-error">Failed to load status</p>';
    }
}

function displayStatus(data) {
    const container = document.getElementById('status-content');

    if (data.is_connected) {
        container.innerHTML = `
            <div class="bcwp-status-connected">
                <div class="bcwp-status-icon">✓</div>
                <div>
                    <h3>Connected to Google Calendar</h3>
                    <p>Your Basecamp events can be synced to Google Calendar</p>
                </div>
            </div>
            <button class="bcwp-btn bcwp-btn-danger" id="disconnect-btn">
                Disconnect Google Calendar
            </button>
        `;

        document.getElementById('disconnect-btn').addEventListener('click', disconnectCalendar);

        // Show settings
        document.getElementById('integration-settings').style.display = 'block';
        document.getElementById('auto-sync-checkbox').checked = data.auto_sync;
    } else {
        container.innerHTML = `
            <div class="bcwp-status-disconnected">
                <div class="bcwp-status-icon">○</div>
                <div>
                    <h3>Not Connected</h3>
                    <p>Connect your Google Calendar to sync events automatically</p>
                </div>
            </div>
            <button class="bcwp-btn bcwp-btn-primary" id="connect-btn">
                Connect Google Calendar
            </button>
            <p class="bcwp-note">Note: Requires administrator configuration (see Setup Instructions below)</p>
        `;

        document.getElementById('connect-btn').addEventListener('click', connectCalendar);
        document.getElementById('integration-settings').style.display = 'none';
    }
}

async function connectCalendar() {
    try {
        const response = await fetch(`${bcwpData.restUrl}/calendar-integration/auth-url`, {
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data.auth_url) {
            window.location.href = result.data.auth_url;
        } else {
            alert('Google Calendar integration not configured. Please contact your administrator.');
        }
    } catch (error) {
        console.error('Failed to get auth URL:', error);
        alert('Failed to initiate connection');
    }
}

async function disconnectCalendar() {
    if (!confirm('Disconnect from Google Calendar? You can reconnect at any time.')) {
        return;
    }

    try {
        const response = await fetch(`${bcwpData.restUrl}/calendar-integration/disconnect`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            loadStatus();
        }
    } catch (error) {
        console.error('Failed to disconnect:', error);
    }
}

document.getElementById('save-settings-btn')?.addEventListener('click', async () => {
    const autoSync = document.getElementById('auto-sync-checkbox').checked;

    try {
        const response = await fetch(`${bcwpData.restUrl}/calendar-integration/settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bcwpData.nonce
            },
            body: JSON.stringify({ auto_sync: autoSync })
        });

        const result = await response.json();

        if (result.success) {
            alert('Settings saved successfully!');
        }
    } catch (error) {
        console.error('Failed to save settings:', error);
        alert('Failed to save settings');
    }
});

loadStatus();
</script>

<style>
.bcwp-calendar-integration {
    max-width: 900px;
    margin: 0 auto;
}

.bcwp-alert {
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.bcwp-alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.bcwp-alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.bcwp-settings-container {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.bcwp-settings-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
}

.bcwp-settings-section:last-child {
    border-bottom: none;
}

.bcwp-settings-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
}

.bcwp-status-connected,
.bcwp-status-disconnected {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 20px;
}

.bcwp-status-icon {
    font-size: 48px;
    font-weight: bold;
}

.bcwp-status-connected .bcwp-status-icon {
    color: #28a745;
}

.bcwp-status-disconnected .bcwp-status-icon {
    color: #999;
}

.bcwp-status-connected h3,
.bcwp-status-disconnected h3 {
    margin: 0 0 5px 0;
}

.bcwp-status-connected p,
.bcwp-status-disconnected p {
    margin: 0;
    color: #666;
}

.bcwp-checkbox-label {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    margin-bottom: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
}

.bcwp-checkbox-label input[type="checkbox"] {
    margin-top: 4px;
    margin-right: 15px;
}

.bcwp-info-list {
    margin: 0;
    padding-left: 20px;
}

.bcwp-info-list li {
    margin-bottom: 15px;
    line-height: 1.6;
}

.bcwp-setup-steps {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.bcwp-step {
    display: flex;
    gap: 20px;
}

.bcwp-step-number {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: #2d9061;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.bcwp-step-content h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.bcwp-step-content p {
    margin: 0 0 10px 0;
    color: #666;
}

.bcwp-code-block {
    display: block;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 14px;
    overflow-x: auto;
}

.bcwp-note {
    font-size: 14px;
    color: #666;
    font-style: italic;
    margin-top: 10px;
}

@media (max-width: 768px) {
    .bcwp-settings-container {
        padding: 20px;
    }
}
</style>

<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
