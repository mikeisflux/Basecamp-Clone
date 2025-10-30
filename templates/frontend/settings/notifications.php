<?php
/**
 * Notification Settings Page
 */

PFOB_Template::header( 'Notification Settings' );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-notifications-settings">

        <header class="pfob-page-header">
            <h1>Notification Settings</h1>
            <p class="pfob-subtitle">Manage your email notifications and digest preferences</p>
        </header>

        <div class="pfob-settings-container">

            <form id="notification-settings-form">

                <!-- Email Digest -->
                <section class="pfob-settings-section">
                    <div class="pfob-settings-icon">📬</div>
                    <h2>Email Digest</h2>
                    <p class="pfob-section-description">
                        Get a summary of project activity delivered to your inbox
                    </p>

                    <div class="pfob-form-group">
                        <label class="pfob-radio-label">
                            <input type="radio" name="digest_frequency" value="none">
                            <div class="pfob-radio-content">
                                <strong>Don't send digests</strong>
                                <p>You won't receive scheduled email digests</p>
                            </div>
                        </label>

                        <label class="pfob-radio-label">
                            <input type="radio" name="digest_frequency" value="daily">
                            <div class="pfob-radio-content">
                                <strong>Daily digest</strong>
                                <p>Receive a daily summary every morning at 9:00 AM</p>
                            </div>
                        </label>

                        <label class="pfob-radio-label">
                            <input type="radio" name="digest_frequency" value="weekly">
                            <div class="pfob-radio-content">
                                <strong>Weekly digest</strong>
                                <p>Receive a weekly summary every Monday at 9:00 AM</p>
                            </div>
                        </label>
                    </div>

                    <button type="button" class="pfob-btn pfob-btn-secondary pfob-btn-sm" id="send-test-digest">
                        Send Test Digest
                    </button>
                </section>

                <!-- Real-time Notifications -->
                <section class="pfob-settings-section">
                    <div class="pfob-settings-icon">🔔</div>
                    <h2>Real-time Email Notifications</h2>
                    <p class="pfob-section-description">
                        Get notified immediately when things happen
                    </p>

                    <div class="pfob-form-group">
                        <label class="pfob-radio-label">
                            <input type="radio" name="email_notifications" value="all">
                            <div class="pfob-radio-content">
                                <strong>All activity</strong>
                                <p>Get notified about all project activity</p>
                            </div>
                        </label>

                        <label class="pfob-radio-label">
                            <input type="radio" name="email_notifications" value="mentions">
                            <div class="pfob-radio-content">
                                <strong>Only mentions and assignments</strong>
                                <p>Get notified when you're mentioned or assigned</p>
                            </div>
                        </label>

                        <label class="pfob-radio-label">
                            <input type="radio" name="email_notifications" value="none">
                            <div class="pfob-radio-content">
                                <strong>None</strong>
                                <p>Don't send real-time notifications</p>
                            </div>
                        </label>
                    </div>
                </section>

                <!-- Specific Notification Types -->
                <section class="pfob-settings-section">
                    <div class="pfob-settings-icon">⚙️</div>
                    <h2>Notification Types</h2>
                    <p class="pfob-section-description">
                        Choose which types of events trigger email notifications
                    </p>

                    <div class="pfob-checkbox-group">
                        <label class="pfob-checkbox-label">
                            <input type="checkbox" id="notification_mentions" name="notification_mentions">
                            <div class="pfob-checkbox-content">
                                <strong>@Mentions</strong>
                                <p>When someone mentions you in a comment or message</p>
                            </div>
                        </label>

                        <label class="pfob-checkbox-label">
                            <input type="checkbox" id="notification_assignments" name="notification_assignments">
                            <div class="pfob-checkbox-content">
                                <strong>Assignments</strong>
                                <p>When you're assigned to a to-do</p>
                            </div>
                        </label>

                        <label class="pfob-checkbox-label">
                            <input type="checkbox" id="notification_comments" name="notification_comments">
                            <div class="pfob-checkbox-content">
                                <strong>Comments</strong>
                                <p>When someone comments on your posts</p>
                            </div>
                        </label>
                    </div>
                </section>

                <div class="pfob-settings-actions">
                    <button type="submit" class="pfob-btn pfob-btn-primary">
                        Save Settings
                    </button>
                    <a href="<?php echo home_url( '/projectfob/dashboard' ); ?>" class="pfob-btn pfob-btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>

            <div id="save-message" class="pfob-save-message" style="display: none;"></div>

        </div>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

// Load current settings
async function loadSettings() {
    try {
        const response = await fetch(`${pfobData.restUrl}/settings`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
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
        const response = await fetch(`${pfobData.restUrl}/settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
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
        const response = await fetch(`${pfobData.restUrl}/settings/test-digest`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': pfobData.nonce }
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
    messageDiv.className = 'pfob-save-message pfob-message-' + type;
    messageDiv.style.display = 'block';

    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}

// Load settings on page load
loadSettings();
</script>

<style>
.pfob-notifications-settings {
    max-width: 800px;
    margin: 0 auto;
}

.pfob-settings-container {
    background: white;
    border-radius: 8px;
    padding: 40px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pfob-settings-section {
    margin-bottom: 50px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
}

.pfob-settings-section:last-of-type {
    border-bottom: none;
}

.pfob-settings-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.pfob-settings-section h2 {
    margin: 0 0 10px 0;
    font-size: 22px;
}

.pfob-section-description {
    color: #666;
    margin-bottom: 25px;
}

.pfob-radio-label,
.pfob-checkbox-label {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    margin-bottom: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.pfob-radio-label:hover,
.pfob-checkbox-label:hover {
    border-color: #2d9061;
    background-color: #f0f8f4;
}

.pfob-radio-label input[type="radio"],
.pfob-checkbox-label input[type="checkbox"] {
    margin-top: 4px;
    margin-right: 15px;
    flex-shrink: 0;
}

.pfob-radio-label input[type="radio"]:checked ~ .pfob-radio-content,
.pfob-checkbox-label input[type="checkbox"]:checked ~ .pfob-checkbox-content {
    color: #2d9061;
}

.pfob-radio-content strong,
.pfob-checkbox-content strong {
    display: block;
    margin-bottom: 4px;
    font-size: 15px;
}

.pfob-radio-content p,
.pfob-checkbox-content p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.pfob-checkbox-group {
    margin-bottom: 20px;
}

.pfob-settings-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.pfob-save-message {
    padding: 15px 20px;
    border-radius: 6px;
    margin-top: 20px;
    font-weight: 500;
}

.pfob-message-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.pfob-message-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.pfob-message-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

@media (max-width: 768px) {
    .pfob-settings-container {
        padding: 20px;
    }

    .pfob-settings-actions {
        flex-direction: column;
    }

    .pfob-settings-actions .pfob-btn {
        width: 100%;
    }
}
</style>

<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
