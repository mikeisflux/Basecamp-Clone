<?php
/**
 * Notification Settings Page
 */

BCWP_Template::header( 'Notification Settings' );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-notifications-settings">

        <header class="bcwp-page-header">
            <h1>Notification Settings</h1>
            <p class="bcwp-subtitle">Manage your email notifications and digest preferences</p>
        </header>

        <div class="bcwp-settings-container">

            <form id="notification-settings-form">

                <!-- Email Digest -->
                <section class="bcwp-settings-section">
                    <div class="bcwp-settings-icon">📬</div>
                    <h2>Email Digest</h2>
                    <p class="bcwp-section-description">
                        Get a summary of project activity delivered to your inbox
                    </p>

                    <div class="bcwp-form-group">
                        <label class="bcwp-radio-label">
                            <input type="radio" name="digest_frequency" value="none">
                            <div class="bcwp-radio-content">
                                <strong>Don't send digests</strong>
                                <p>You won't receive scheduled email digests</p>
                            </div>
                        </label>

                        <label class="bcwp-radio-label">
                            <input type="radio" name="digest_frequency" value="daily">
                            <div class="bcwp-radio-content">
                                <strong>Daily digest</strong>
                                <p>Receive a daily summary every morning at 9:00 AM</p>
                            </div>
                        </label>

                        <label class="bcwp-radio-label">
                            <input type="radio" name="digest_frequency" value="weekly">
                            <div class="bcwp-radio-content">
                                <strong>Weekly digest</strong>
                                <p>Receive a weekly summary every Monday at 9:00 AM</p>
                            </div>
                        </label>
                    </div>

                    <button type="button" class="bcwp-btn bcwp-btn-secondary bcwp-btn-sm" id="send-test-digest">
                        Send Test Digest
                    </button>
                </section>

                <!-- Real-time Notifications -->
                <section class="bcwp-settings-section">
                    <div class="bcwp-settings-icon">🔔</div>
                    <h2>Real-time Email Notifications</h2>
                    <p class="bcwp-section-description">
                        Get notified immediately when things happen
                    </p>

                    <div class="bcwp-form-group">
                        <label class="bcwp-radio-label">
                            <input type="radio" name="email_notifications" value="all">
                            <div class="bcwp-radio-content">
                                <strong>All activity</strong>
                                <p>Get notified about all project activity</p>
                            </div>
                        </label>

                        <label class="bcwp-radio-label">
                            <input type="radio" name="email_notifications" value="mentions">
                            <div class="bcwp-radio-content">
                                <strong>Only mentions and assignments</strong>
                                <p>Get notified when you're mentioned or assigned</p>
                            </div>
                        </label>

                        <label class="bcwp-radio-label">
                            <input type="radio" name="email_notifications" value="none">
                            <div class="bcwp-radio-content">
                                <strong>None</strong>
                                <p>Don't send real-time notifications</p>
                            </div>
                        </label>
                    </div>
                </section>

                <!-- Specific Notification Types -->
                <section class="bcwp-settings-section">
                    <div class="bcwp-settings-icon">⚙️</div>
                    <h2>Notification Types</h2>
                    <p class="bcwp-section-description">
                        Choose which types of events trigger email notifications
                    </p>

                    <div class="bcwp-checkbox-group">
                        <label class="bcwp-checkbox-label">
                            <input type="checkbox" id="notification_mentions" name="notification_mentions">
                            <div class="bcwp-checkbox-content">
                                <strong>@Mentions</strong>
                                <p>When someone mentions you in a comment or message</p>
                            </div>
                        </label>

                        <label class="bcwp-checkbox-label">
                            <input type="checkbox" id="notification_assignments" name="notification_assignments">
                            <div class="bcwp-checkbox-content">
                                <strong>Assignments</strong>
                                <p>When you're assigned to a to-do</p>
                            </div>
                        </label>

                        <label class="bcwp-checkbox-label">
                            <input type="checkbox" id="notification_comments" name="notification_comments">
                            <div class="bcwp-checkbox-content">
                                <strong>Comments</strong>
                                <p>When someone comments on your posts</p>
                            </div>
                        </label>
                    </div>
                </section>

                <div class="bcwp-settings-actions">
                    <button type="submit" class="bcwp-btn bcwp-btn-primary">
                        Save Settings
                    </button>
                    <a href="<?php echo home_url( '/projectfob/dashboard' ); ?>" class="bcwp-btn bcwp-btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>

            <div id="save-message" class="bcwp-save-message" style="display: none;"></div>

        </div>

    </main>
</div>

<script>
const bcwpData = {
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

// Load current settings
async function loadSettings() {
    try {
        const response = await fetch(`${bcwpData.restUrl}/settings`, {
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            const settings = result.data;

            // Set digest frequency
            const digestRadio = document.querySelector(`input[name="digest_frequency"][value="${settings.digest_frequency}"]`);
            if (digestRadio) digestRadio.checked = true;

            // Set email notifications
            const emailRadio = document.querySelector(`input[name="email_notifications"][value="${settings.email_notifications}"]`);
            if (emailRadio) emailRadio.checked = true;

            // Set specific notification types
            document.getElementById('notification_mentions').checked = settings.notification_mentions;
            document.getElementById('notification_assignments').checked = settings.notification_assignments;
            document.getElementById('notification_comments').checked = settings.notification_comments;
        }
    } catch (error) {
        console.error('Failed to load settings:', error);
    }
}

// Save settings
document.getElementById('notification-settings-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        digest_frequency: formData.get('digest_frequency'),
        email_notifications: formData.get('email_notifications'),
        notification_mentions: document.getElementById('notification_mentions').checked,
        notification_assignments: document.getElementById('notification_assignments').checked,
        notification_comments: document.getElementById('notification_comments').checked,
    };

    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    try {
        const response = await fetch(`${bcwpData.restUrl}/settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bcwpData.nonce
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showMessage('Settings saved successfully!', 'success');
        } else {
            showMessage('Failed to save settings', 'error');
        }
    } catch (error) {
        console.error('Failed to save settings:', error);
        showMessage('Failed to save settings', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Settings';
    }
});

// Send test digest
document.getElementById('send-test-digest').addEventListener('click', async () => {
    const btn = document.getElementById('send-test-digest');
    btn.disabled = true;
    btn.textContent = 'Sending...';

    try {
        const response = await fetch(`${bcwpData.restUrl}/settings/test-digest`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });

        const result = await response.json();

        if (result.success) {
            showMessage(result.message, 'success');
        } else {
            showMessage(result.message || 'Failed to send test digest', 'info');
        }
    } catch (error) {
        console.error('Failed to send test digest:', error);
        showMessage('Failed to send test digest', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Send Test Digest';
    }
});

function showMessage(message, type) {
    const messageDiv = document.getElementById('save-message');
    messageDiv.textContent = message;
    messageDiv.className = 'bcwp-save-message bcwp-message-' + type;
    messageDiv.style.display = 'block';

    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}

// Load settings on page load
loadSettings();
</script>

<style>
.bcwp-notifications-settings {
    max-width: 800px;
    margin: 0 auto;
}

.bcwp-settings-container {
    background: white;
    border-radius: 8px;
    padding: 40px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.bcwp-settings-section {
    margin-bottom: 50px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
}

.bcwp-settings-section:last-of-type {
    border-bottom: none;
}

.bcwp-settings-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.bcwp-settings-section h2 {
    margin: 0 0 10px 0;
    font-size: 22px;
}

.bcwp-section-description {
    color: #666;
    margin-bottom: 25px;
}

.bcwp-radio-label,
.bcwp-checkbox-label {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    margin-bottom: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.bcwp-radio-label:hover,
.bcwp-checkbox-label:hover {
    border-color: #2d9061;
    background-color: #f0f8f4;
}

.bcwp-radio-label input[type="radio"],
.bcwp-checkbox-label input[type="checkbox"] {
    margin-top: 4px;
    margin-right: 15px;
    flex-shrink: 0;
}

.bcwp-radio-label input[type="radio"]:checked ~ .bcwp-radio-content,
.bcwp-checkbox-label input[type="checkbox"]:checked ~ .bcwp-checkbox-content {
    color: #2d9061;
}

.bcwp-radio-content strong,
.bcwp-checkbox-content strong {
    display: block;
    margin-bottom: 4px;
    font-size: 15px;
}

.bcwp-radio-content p,
.bcwp-checkbox-content p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.bcwp-checkbox-group {
    margin-bottom: 20px;
}

.bcwp-settings-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.bcwp-save-message {
    padding: 15px 20px;
    border-radius: 6px;
    margin-top: 20px;
    font-weight: 500;
}

.bcwp-message-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.bcwp-message-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.bcwp-message-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

@media (max-width: 768px) {
    .bcwp-settings-container {
        padding: 20px;
    }

    .bcwp-settings-actions {
        flex-direction: column;
    }

    .bcwp-settings-actions .bcwp-btn {
        width: 100%;
    }
}
</style>

<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
