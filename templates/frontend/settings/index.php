<?php
/**
 * User Settings - All-in-One Page
 * Personal settings for each user on a single page
 */

PFOB_Template::header( 'Settings' );

$user_id = get_current_user_id();
$user = wp_get_current_user();
$is_subscriber = PFOB_Subscription::is_active( $user_id );

// Get current user settings
$gdrive_connected = !empty( get_user_meta( $user_id, 'pfob_gdrive_access_token', true ) );
$dropbox_connected = !empty( get_user_meta( $user_id, 'pfob_dropbox_access_token', true ) );
$notification_settings = get_user_meta( $user_id, 'pfob_notification_settings', true ) ?: array();
$theme_mode = get_user_meta( $user_id, 'pfob_theme_mode', true ) ?: 'light';
$timezone = get_user_meta( $user_id, 'pfob_timezone', true ) ?: 'UTC';
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-settings-page">

        <div class="pfob-page-header">
            <div>
                <h1>⚙️ Settings</h1>
                <p class="pfob-subtitle">Manage all your personal preferences in one place</p>
            </div>
        </div>

        <!-- Settings Navigation -->
        <div class="pfob-settings-nav">
            <button class="pfob-settings-tab active" data-section="account">👤 Account</button>
            <button class="pfob-settings-tab" data-section="appearance">🎨 Appearance</button>
            <button class="pfob-settings-tab" data-section="notifications">🔔 Notifications</button>
            <button class="pfob-settings-tab" data-section="cloud">☁️ Cloud Storage</button>
            <button class="pfob-settings-tab" data-section="calendar">📅 Calendar</button>
            <button class="pfob-settings-tab" data-section="privacy">🔒 Privacy</button>
            <button class="pfob-settings-tab" data-section="preferences">⚡ Preferences</button>
        </div>

        <div class="pfob-settings-content">

            <!-- ACCOUNT SECTION -->
            <div class="pfob-settings-section active" id="section-account">
                <h2>👤 Account Settings</h2>
                <p class="pfob-section-desc">Manage your personal information and credentials</p>

                <div class="pfob-setting-group">
                    <label class="pfob-setting-label">
                        <span>Display Name</span>
                        <input type="text"
                               class="pfob-input"
                               id="display-name"
                               value="<?php echo esc_attr( $user->display_name ); ?>"
                               placeholder="Your Name">
                    </label>

                    <label class="pfob-setting-label">
                        <span>Email Address</span>
                        <input type="email"
                               class="pfob-input"
                               id="user-email"
                               value="<?php echo esc_attr( $user->user_email ); ?>"
                               placeholder="your@email.com">
                    </label>

                    <label class="pfob-setting-label">
                        <span>Username</span>
                        <input type="text"
                               class="pfob-input"
                               value="<?php echo esc_attr( $user->user_login ); ?>"
                               disabled>
                        <small>Username cannot be changed</small>
                    </label>
                </div>

                <div class="pfob-setting-group">
                    <h3>Change Password</h3>
                    <label class="pfob-setting-label">
                        <span>Current Password</span>
                        <input type="password" class="pfob-input" id="current-password">
                    </label>
                    <label class="pfob-setting-label">
                        <span>New Password</span>
                        <input type="password" class="pfob-input" id="new-password">
                    </label>
                    <label class="pfob-setting-label">
                        <span>Confirm New Password</span>
                        <input type="password" class="pfob-input" id="confirm-password">
                    </label>
                </div>

                <div class="pfob-setting-actions">
                    <button class="pfob-btn pfob-btn-primary" id="save-account">Save Account Changes</button>
                </div>
            </div>

            <!-- APPEARANCE SECTION -->
            <div class="pfob-settings-section" id="section-appearance">
                <h2>🎨 Appearance</h2>
                <p class="pfob-section-desc">Customize how ProjectFOB looks to you</p>

                <div class="pfob-setting-group">
                    <h3>Theme Mode</h3>
                    <div class="pfob-theme-selector">
                        <label class="pfob-theme-option <?php echo $theme_mode === 'light' ? 'active' : ''; ?>">
                            <input type="radio" name="theme-mode" value="light" <?php checked( $theme_mode, 'light' ); ?>>
                            <div class="pfob-theme-preview pfob-theme-light">
                                <div class="pfob-theme-name">☀️ Light</div>
                            </div>
                        </label>
                        <label class="pfob-theme-option <?php echo $theme_mode === 'dark' ? 'active' : ''; ?>">
                            <input type="radio" name="theme-mode" value="dark" <?php checked( $theme_mode, 'dark' ); ?>>
                            <div class="pfob-theme-preview pfob-theme-dark">
                                <div class="pfob-theme-name">🌙 Dark</div>
                            </div>
                        </label>
                        <label class="pfob-theme-option <?php echo $theme_mode === 'auto' ? 'active' : ''; ?>">
                            <input type="radio" name="theme-mode" value="auto" <?php checked( $theme_mode, 'auto' ); ?>>
                            <div class="pfob-theme-preview pfob-theme-auto">
                                <div class="pfob-theme-name">🌗 Auto</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pfob-setting-group">
                    <h3>Display Preferences</h3>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="compact-mode">
                        <span>Compact Mode - Reduce spacing for more content</span>
                    </label>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="show-avatars">
                        <span>Show avatars in lists and feeds</span>
                    </label>
                </div>

                <div class="pfob-setting-actions">
                    <button class="pfob-btn pfob-btn-primary" id="save-appearance">Save Appearance</button>
                </div>
            </div>

            <!-- NOTIFICATIONS SECTION -->
            <div class="pfob-settings-section" id="section-notifications">
                <h2>🔔 Notifications</h2>
                <p class="pfob-section-desc">Choose when and how you want to be notified</p>

                <div class="pfob-setting-group">
                    <h3>Email Notifications</h3>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="notify-mentions" checked>
                        <span><strong>@Mentions</strong> - When someone mentions you</span>
                    </label>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="notify-comments" checked>
                        <span><strong>Comments</strong> - New comments on items you're following</span>
                    </label>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="notify-assignments" checked>
                        <span><strong>Assignments</strong> - When you're assigned to a task</span>
                    </label>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="notify-project-invites" checked>
                        <span><strong>Project Invites</strong> - When added to a project</span>
                    </label>
                </div>

                <div class="pfob-setting-group">
                    <h3>Email Frequency</h3>
                    <label class="pfob-setting-label">
                        <span>Send me email digests</span>
                        <select class="pfob-select" id="email-digest-frequency">
                            <option value="realtime">Immediately</option>
                            <option value="hourly">Every Hour</option>
                            <option value="daily" selected>Daily Summary</option>
                            <option value="weekly">Weekly Summary</option>
                            <option value="never">Never</option>
                        </select>
                    </label>
                </div>

                <div class="pfob-setting-actions">
                    <button class="pfob-btn pfob-btn-primary" id="save-notifications">Save Notification Settings</button>
                </div>
            </div>

            <!-- CLOUD STORAGE SECTION -->
            <div class="pfob-settings-section" id="section-cloud">
                <h2>☁️ Cloud Storage</h2>
                <p class="pfob-section-desc">Connect your personal Google Drive and Dropbox accounts</p>

                <div class="pfob-cloud-connections">
                    <!-- Google Drive -->
                    <div class="pfob-cloud-service">
                        <div class="pfob-cloud-header">
                            <svg width="48" height="48" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M8.5 6.5L15.5 6.5 20.25 15 15.5 23.5 3.75 23.5 8.5 15z"/>
                                <path fill="#34A853" d="M8.5 6.5L1.75 15 8.5 23.5 15.5 23.5z"/>
                                <path fill="#FBBC04" d="M15.5 6.5L8.5 6.5 8.5 23.5 15.5 23.5z"/>
                            </svg>
                            <div>
                                <h3>Google Drive</h3>
                                <p>Upload files from your personal Google Drive</p>
                            </div>
                        </div>
                        <?php if ( $gdrive_connected ) : ?>
                            <div class="pfob-cloud-status connected">✓ Connected</div>
                            <button class="pfob-btn pfob-btn-secondary" id="disconnect-gdrive">Disconnect</button>
                        <?php else : ?>
                            <div class="pfob-cloud-status disconnected">Not Connected</div>
                            <button class="pfob-btn pfob-btn-primary" id="connect-gdrive">Connect Google Drive</button>
                        <?php endif; ?>
                    </div>

                    <!-- Dropbox -->
                    <div class="pfob-cloud-service">
                        <div class="pfob-cloud-header">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="#0061FF">
                                <path d="M6 1.807L0 5.629l6 3.822 6.001-3.822L6 1.807zM18 1.807l-6 3.822 6 3.822 6-3.822-6-3.822zM0 13.274l6 3.822 6.001-3.822L6 9.452l-6 3.822zm12.001 0l6 3.822 6-3.822-6-3.822-6 3.822z"/>
                            </svg>
                            <div>
                                <h3>Dropbox</h3>
                                <p>Upload files from your personal Dropbox</p>
                            </div>
                        </div>
                        <?php if ( $dropbox_connected ) : ?>
                            <div class="pfob-cloud-status connected">✓ Connected</div>
                            <button class="pfob-btn pfob-btn-secondary" id="disconnect-dropbox">Disconnect</button>
                        <?php else : ?>
                            <div class="pfob-cloud-status disconnected">Not Connected</div>
                            <button class="pfob-btn pfob-btn-primary" id="connect-dropbox">Connect Dropbox</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- CALENDAR SECTION -->
            <div class="pfob-settings-section" id="section-calendar">
                <h2>📅 Calendar Integration</h2>
                <p class="pfob-section-desc">Sync ProjectFOB events with your Google Calendar</p>

                <div class="pfob-setting-group">
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="calendar-sync-enabled">
                        <span>Enable Google Calendar sync</span>
                    </label>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="calendar-two-way-sync">
                        <span>Two-way sync (import calendar events to ProjectFOB)</span>
                    </label>
                </div>

                <div class="pfob-setting-actions">
                    <button class="pfob-btn pfob-btn-primary" id="save-calendar">Save Calendar Settings</button>
                </div>
            </div>

            <!-- PRIVACY SECTION -->
            <div class="pfob-settings-section" id="section-privacy">
                <h2>🔒 Privacy & Security</h2>
                <p class="pfob-section-desc">Control your privacy and security settings</p>

                <div class="pfob-setting-group">
                    <h3>Activity Visibility</h3>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="show-activity" checked>
                        <span>Show my activity in team feeds</span>
                    </label>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="show-online-status" checked>
                        <span>Show when I'm online</span>
                    </label>
                </div>

                <div class="pfob-setting-group">
                    <h3>Security Options</h3>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="two-factor-auth">
                        <span>Enable two-factor authentication (Coming Soon)</span>
                    </label>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="session-timeout">
                        <span>Auto-logout after 30 minutes of inactivity</span>
                    </label>
                </div>

                <div class="pfob-setting-actions">
                    <button class="pfob-btn pfob-btn-primary" id="save-privacy">Save Privacy Settings</button>
                </div>
            </div>

            <!-- PREFERENCES SECTION -->
            <div class="pfob-settings-section" id="section-preferences">
                <h2>⚡ Preferences</h2>
                <p class="pfob-section-desc">Customize your workflow and experience</p>

                <div class="pfob-setting-group">
                    <label class="pfob-setting-label">
                        <span>Timezone</span>
                        <select class="pfob-select" id="user-timezone">
                            <option value="UTC">UTC (GMT+0)</option>
                            <option value="America/New_York">Eastern Time (GMT-5)</option>
                            <option value="America/Chicago">Central Time (GMT-6)</option>
                            <option value="America/Denver">Mountain Time (GMT-7)</option>
                            <option value="America/Los_Angeles">Pacific Time (GMT-8)</option>
                            <option value="Europe/London">London (GMT+0)</option>
                            <option value="Europe/Paris">Paris (GMT+1)</option>
                        </select>
                    </label>

                    <label class="pfob-setting-label">
                        <span>Default Project View</span>
                        <select class="pfob-select" id="default-project-view">
                            <option value="overview">Overview</option>
                            <option value="messages">Message Board</option>
                            <option value="todos">To-Dos</option>
                            <option value="docs">Docs & Files</option>
                        </select>
                    </label>

                    <label class="pfob-setting-label">
                        <span>Date Format</span>
                        <select class="pfob-select" id="date-format">
                            <option value="m/d/Y">MM/DD/YYYY (12/31/2025)</option>
                            <option value="d/m/Y">DD/MM/YYYY (31/12/2025)</option>
                            <option value="Y-m-d">YYYY-MM-DD (2025-12-31)</option>
                        </select>
                    </label>
                </div>

                <div class="pfob-setting-group">
                    <h3>Keyboard Shortcuts</h3>
                    <label class="pfob-checkbox-label">
                        <input type="checkbox" id="enable-shortcuts" checked>
                        <span>Enable keyboard shortcuts</span>
                    </label>
                    <div class="pfob-shortcuts-info">
                        <p><kbd>N</kbd> - New Project</p>
                        <p><kbd>F</kbd> - Search</p>
                        <p><kbd>H</kbd> - Home</p>
                        <p><kbd>?</kbd> - Show all shortcuts</p>
                    </div>
                </div>

                <div class="pfob-setting-actions">
                    <button class="pfob-btn pfob-btn-primary" id="save-preferences">Save Preferences</button>
                </div>
            </div>

        </div>

        <?php if ( $is_subscriber ) : ?>
        <div class="pfob-subscriber-notice">
            <strong>👑 Account Owner:</strong> For billing, user management, and company settings, visit <a href="<?php echo home_url( '/projectfob/adminland/' ); ?>">Adminland</a>
        </div>
        <?php endif; ?>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    userId: <?php echo $user_id; ?>
};

// Tab Navigation
document.querySelectorAll('.pfob-settings-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const section = this.dataset.section;

        // Update tabs
        document.querySelectorAll('.pfob-settings-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        // Update sections
        document.querySelectorAll('.pfob-settings-section').forEach(s => s.classList.remove('active'));
        document.getElementById('section-' + section).classList.add('active');

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

// Save Account Settings
document.getElementById('save-account')?.addEventListener('click', async () => {
    const data = {
        display_name: document.getElementById('display-name').value,
        email: document.getElementById('user-email').value
    };

    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (newPassword) {
        if (newPassword !== confirmPassword) {
            alert('New passwords do not match');
            return;
        }
        data.current_password = currentPassword;
        data.new_password = newPassword;
    }

    try {
        const response = await fetch(`${pfobData.restUrl}/user/account`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            alert('Account settings saved!');
            if (newPassword) {
                document.getElementById('current-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            }
        } else {
            alert('Failed to save: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Failed to save account settings');
        console.error(error);
    }
});

// Save Appearance
document.getElementById('save-appearance')?.addEventListener('click', async () => {
    const themeMode = document.querySelector('input[name="theme-mode"]:checked').value;

    try {
        const response = await fetch(`${pfobData.restUrl}/user/preferences`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({
                theme_mode: themeMode
            })
        });

        if (response.ok) {
            alert('Appearance settings saved!');
            // Apply theme immediately
            document.body.setAttribute('data-theme', themeMode);
        }
    } catch (error) {
        alert('Failed to save appearance settings');
        console.error(error);
    }
});

// Cloud Storage Connections
document.getElementById('connect-gdrive')?.addEventListener('click', async () => {
    window.location.href = '<?php echo home_url( '/projectfob/settings/cloud-storage/' ); ?>';
});

document.getElementById('connect-dropbox')?.addEventListener('click', async () => {
    window.location.href = '<?php echo home_url( '/projectfob/settings/cloud-storage/' ); ?>';
});

// Save Notifications
document.getElementById('save-notifications')?.addEventListener('click', async () => {
    const data = {
        notifications: {
            mentions: document.getElementById('notify-mentions').checked,
            comments: document.getElementById('notify-comments').checked,
            assignments: document.getElementById('notify-assignments').checked,
            project_invites: document.getElementById('notify-project-invites').checked,
        },
        email_digest_frequency: document.getElementById('email-digest-frequency').value
    };

    try {
        const response = await fetch(`${pfobData.restUrl}/user/preferences`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Notification settings saved!');
        } else {
            alert('Failed to save notification settings');
        }
    } catch (error) {
        alert('Failed to save notification settings');
        console.error(error);
    }
});

// Save Calendar
document.getElementById('save-calendar')?.addEventListener('click', async () => {
    const data = {
        calendar_sync_enabled: document.getElementById('calendar-sync-enabled').checked,
        calendar_two_way_sync: document.getElementById('calendar-two-way-sync').checked
    };

    try {
        const response = await fetch(`${pfobData.restUrl}/user/preferences`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Calendar settings saved!');
        } else {
            alert('Failed to save calendar settings');
        }
    } catch (error) {
        alert('Failed to save calendar settings');
        console.error(error);
    }
});

// Save Privacy
document.getElementById('save-privacy')?.addEventListener('click', async () => {
    const data = {
        show_activity: document.getElementById('show-activity').checked,
        show_online_status: document.getElementById('show-online-status').checked,
        session_timeout: document.getElementById('session-timeout').checked
    };

    try {
        const response = await fetch(`${pfobData.restUrl}/user/preferences`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Privacy settings saved!');
        } else {
            alert('Failed to save privacy settings');
        }
    } catch (error) {
        alert('Failed to save privacy settings');
        console.error(error);
    }
});

// Save Preferences
document.getElementById('save-preferences')?.addEventListener('click', async () => {
    const data = {
        timezone: document.getElementById('user-timezone').value,
        default_project_view: document.getElementById('default-project-view').value,
        date_format: document.getElementById('date-format').value,
        enable_shortcuts: document.getElementById('enable-shortcuts').checked
    };

    try {
        const response = await fetch(`${pfobData.restUrl}/user/preferences`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Preferences saved!');
        } else {
            alert('Failed to save preferences');
        }
    } catch (error) {
        alert('Failed to save preferences');
        console.error(error);
    }
});
</script>

<style>
.pfob-settings-page {
    max-width: 1200px;
    margin: 0 auto;
}

.pfob-settings-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 30px;
    border-bottom: 2px solid #e0e0e0;
    overflow-x: auto;
    padding-bottom: 0;
}

.pfob-settings-tab {
    padding: 12px 20px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 15px;
    color: #666;
    white-space: nowrap;
    transition: all 0.2s;
}

.pfob-settings-tab:hover {
    color: #2d9061;
    background: #f8f9fa;
}

.pfob-settings-tab.active {
    color: #2d9061;
    border-bottom-color: #2d9061;
    font-weight: 600;
}

.pfob-settings-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pfob-settings-section {
    display: none;
}

.pfob-settings-section.active {
    display: block;
}

.pfob-settings-section h2 {
    margin: 0 0 8px 0;
    font-size: 28px;
    color: #333;
}

.pfob-section-desc {
    margin: 0 0 30px 0;
    color: #666;
    font-size: 15px;
}

.pfob-setting-group {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
}

.pfob-setting-group:last-of-type {
    border-bottom: none;
}

.pfob-setting-group h3 {
    margin: 0 0 15px 0;
    font-size: 18px;
    color: #333;
}

.pfob-setting-label {
    display: block;
    margin-bottom: 20px;
}

.pfob-setting-label span {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.pfob-setting-label small {
    display: block;
    margin-top: 5px;
    color: #888;
    font-size: 13px;
}

.pfob-input,
.pfob-select {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.2s;
}

.pfob-input:focus,
.pfob-select:focus {
    outline: none;
    border-color: #2d9061;
}

.pfob-input:disabled {
    background: #f5f5f5;
    cursor: not-allowed;
}

.pfob-checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}

.pfob-checkbox-label:hover {
    background: #f8f9fa;
}

.pfob-checkbox-label input[type="checkbox"] {
    margin-top: 3px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.pfob-checkbox-label span {
    flex: 1;
    line-height: 1.5;
}

.pfob-theme-selector {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.pfob-theme-option {
    flex: 1;
    min-width: 150px;
    cursor: pointer;
}

.pfob-theme-option input[type="radio"] {
    display: none;
}

.pfob-theme-preview {
    padding: 40px 20px;
    border: 3px solid #e0e0e0;
    border-radius: 8px;
    text-align: center;
    transition: all 0.2s;
}

.pfob-theme-option.active .pfob-theme-preview,
.pfob-theme-preview:hover {
    border-color: #2d9061;
    box-shadow: 0 4px 12px rgba(45, 144, 97, 0.2);
}

.pfob-theme-light {
    background: #ffffff;
    color: #333;
}

.pfob-theme-dark {
    background: #1a1a1a;
    color: #ffffff;
}

.pfob-theme-auto {
    background: linear-gradient(90deg, #ffffff 50%, #1a1a1a 50%);
    color: #333;
}

.pfob-theme-name {
    font-weight: 600;
    font-size: 16px;
}

.pfob-cloud-connections {
    display: grid;
    gap: 20px;
}

.pfob-cloud-service {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
}

.pfob-cloud-header {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
}

.pfob-cloud-header h3 {
    margin: 0 0 4px 0;
    font-size: 18px;
}

.pfob-cloud-header p {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.pfob-cloud-status {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
}

.pfob-cloud-status.connected {
    background: #d4edda;
    color: #155724;
}

.pfob-cloud-status.disconnected {
    background: #f8d7da;
    color: #721c24;
}

.pfob-shortcuts-info {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.pfob-shortcuts-info p {
    margin: 8px 0;
    font-size: 14px;
}

.pfob-shortcuts-info kbd {
    display: inline-block;
    padding: 3px 8px;
    background: white;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-family: monospace;
    font-size: 13px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.pfob-setting-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e0e0e0;
}

.pfob-subscriber-notice {
    margin-top: 30px;
    padding: 15px 20px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 6px;
}

.pfob-subscriber-notice a {
    color: #2d9061;
    font-weight: 600;
}

@media (max-width: 768px) {
    .pfob-settings-nav {
        flex-wrap: nowrap;
    }

    .pfob-settings-content {
        padding: 20px;
    }

    .pfob-theme-selector {
        flex-direction: column;
    }

    .pfob-cloud-service {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
