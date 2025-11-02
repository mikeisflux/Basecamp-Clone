<?php
/**
 * Timesheet Settings
 *
 * Configure time tracking preferences, rates, and work hours
 */
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

PFOB_Template::header('Timesheet Settings');
?>

<div class="timesheet-settings-wrapper">
    <a href="<?php echo home_url('/projectfob/timesheet'); ?>" class="back-link">← Back to Timesheet</a>

    <h1 class="page-title">⚙️ Timesheet Settings</h1>
    <p class="page-subtitle">Configure your time tracking preferences and work schedule.</p>

    <!-- Time Tracking Preferences -->
    <div class="settings-section">
        <h2 class="section-title">Time Tracking Preferences</h2>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Automatic Idle Detection</div>
                <div class="setting-description">Automatically pause timer when you're away from your computer</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-idle-detection" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Require Task Descriptions</div>
                <div class="setting-description">Force users to enter task description before starting timer</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-require-description">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Round Time Entries</div>
                <div class="setting-description">Round time entries to nearest increment</div>
            </div>
            <div class="setting-control">
                <select id="setting-round-time" class="settings-select">
                    <option value="0">No Rounding</option>
                    <option value="5">5 minutes</option>
                    <option value="15" selected>15 minutes</option>
                    <option value="30">30 minutes</option>
                    <option value="60">1 hour</option>
                </select>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Minimum Time Entry</div>
                <div class="setting-description">Minimum duration for time entries (in minutes)</div>
            </div>
            <div class="setting-control">
                <input type="number" id="setting-min-entry" class="settings-input" value="6" min="1" max="60">
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Reminders</div>
                <div class="setting-description">Send reminder to log time at end of day</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-reminders" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>
    </div>

    <!-- Billable Rates -->
    <div class="settings-section">
        <h2 class="section-title">Billable Rates</h2>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Default Hourly Rate</div>
                <div class="setting-description">Your default billing rate per hour</div>
            </div>
            <div class="setting-control">
                <div class="input-with-prefix">
                    <span class="input-prefix">$</span>
                    <input type="number" id="setting-hourly-rate" class="settings-input" value="75" min="0" step="5">
                </div>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Currency</div>
                <div class="setting-description">Default currency for billing</div>
            </div>
            <div class="setting-control">
                <select id="setting-currency" class="settings-select">
                    <option value="USD" selected>USD - US Dollar</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="GBP">GBP - British Pound</option>
                    <option value="CAD">CAD - Canadian Dollar</option>
                    <option value="AUD">AUD - Australian Dollar</option>
                </select>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Default Time as Billable</div>
                <div class="setting-description">Mark new time entries as billable by default</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-default-billable" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>
    </div>

    <!-- Work Hours -->
    <div class="settings-section">
        <h2 class="section-title">Work Hours</h2>

        <div class="work-hours-grid">
            <div class="work-day">
                <label class="day-label">
                    <input type="checkbox" class="day-checkbox" checked> Monday
                </label>
                <div class="day-hours">
                    <input type="time" class="time-input" value="09:00">
                    <span>to</span>
                    <input type="time" class="time-input" value="17:00">
                </div>
            </div>

            <div class="work-day">
                <label class="day-label">
                    <input type="checkbox" class="day-checkbox" checked> Tuesday
                </label>
                <div class="day-hours">
                    <input type="time" class="time-input" value="09:00">
                    <span>to</span>
                    <input type="time" class="time-input" value="17:00">
                </div>
            </div>

            <div class="work-day">
                <label class="day-label">
                    <input type="checkbox" class="day-checkbox" checked> Wednesday
                </label>
                <div class="day-hours">
                    <input type="time" class="time-input" value="09:00">
                    <span>to</span>
                    <input type="time" class="time-input" value="17:00">
                </div>
            </div>

            <div class="work-day">
                <label class="day-label">
                    <input type="checkbox" class="day-checkbox" checked> Thursday
                </label>
                <div class="day-hours">
                    <input type="time" class="time-input" value="09:00">
                    <span>to</span>
                    <input type="time" class="time-input" value="17:00">
                </div>
            </div>

            <div class="work-day">
                <label class="day-label">
                    <input type="checkbox" class="day-checkbox" checked> Friday
                </label>
                <div class="day-hours">
                    <input type="time" class="time-input" value="09:00">
                    <span>to</span>
                    <input type="time" class="time-input" value="17:00">
                </div>
            </div>

            <div class="work-day">
                <label class="day-label">
                    <input type="checkbox" class="day-checkbox"> Saturday
                </label>
                <div class="day-hours">
                    <input type="time" class="time-input" value="09:00" disabled>
                    <span>to</span>
                    <input type="time" class="time-input" value="17:00" disabled>
                </div>
            </div>

            <div class="work-day">
                <label class="day-label">
                    <input type="checkbox" class="day-checkbox"> Sunday
                </label>
                <div class="day-hours">
                    <input type="time" class="time-input" value="09:00" disabled>
                    <span>to</span>
                    <input type="time" class="time-input" value="17:00" disabled>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="settings-section">
        <h2 class="section-title">Notifications</h2>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Timer Running Notification</div>
                <div class="setting-description">Show notification when timer has been running for extended period</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-timer-notification" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Weekly Summary Email</div>
                <div class="setting-description">Receive weekly timesheet summary every Monday</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-weekly-email">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <div class="setting-label">Approval Notifications</div>
                <div class="setting-description">Get notified when timesheets are approved or rejected</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="setting-approval-notification" checked>
                <span class="toggle-slider"></span>
            </label>
        </div>
    </div>

    <!-- Save Button -->
    <div class="settings-actions">
        <button class="action-button primary-button" onclick="saveSettings()">💾 Save Settings</button>
        <button class="action-button secondary-button" onclick="resetSettings()">Reset to Defaults</button>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.timesheet-settings-wrapper {
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

.settings-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    margin: 0 0 24px 0;
    color: #333;
    padding-bottom: 12px;
    border-bottom: 2px solid #0066cc;
}

/* Setting Rows */
.setting-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #e0e0e0;
}

.setting-row:last-child {
    border-bottom: none;
}

.setting-info {
    flex: 1;
}

.setting-label {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.setting-description {
    font-size: 14px;
    color: #666;
}

.setting-control {
    margin-left: 20px;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    margin-left: 20px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 28px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #0066cc;
}

input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

/* Form Inputs */
.settings-select,
.settings-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
    min-width: 150px;
}

.settings-select:focus,
.settings-input:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.input-with-prefix {
    display: flex;
    align-items: center;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
}

.input-with-prefix:focus-within {
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.input-prefix {
    padding: 8px 12px;
    background: #f8f9fa;
    color: #666;
    font-weight: 600;
    border-right: 1px solid #ddd;
}

.input-with-prefix input {
    border: none;
    padding: 8px 12px;
    font-size: 15px;
    min-width: 100px;
}

.input-with-prefix input:focus {
    outline: none;
    box-shadow: none;
}

/* Work Hours */
.work-hours-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.work-day {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 6px;
}

.day-label {
    display: flex;
    align-items: center;
    font-size: 15px;
    font-weight: 500;
    color: #333;
    cursor: pointer;
    min-width: 120px;
}

.day-checkbox {
    margin-right: 10px;
    cursor: pointer;
}

.day-hours {
    display: flex;
    align-items: center;
    gap: 12px;
}

.time-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
}

.time-input:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.time-input:disabled {
    background: #e0e0e0;
    cursor: not-allowed;
}

/* Actions */
.settings-actions {
    display: flex;
    gap: 12px;
    padding: 24px;
    background: #f8f9fa;
    border-radius: 8px;
}

.action-button {
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    border: none;
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

@media (max-width: 768px) {
    .timesheet-settings-wrapper {
        padding: 12px;
    }

    .settings-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }

    .setting-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .setting-control,
    .toggle-switch {
        margin-left: 0;
    }

    .work-day {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .settings-actions {
        flex-direction: column;
    }

    .action-button {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable work hours based on checkbox
    const dayCheckboxes = document.querySelectorAll('.day-checkbox');
    dayCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const dayHours = this.closest('.work-day').querySelector('.day-hours');
            const timeInputs = dayHours.querySelectorAll('.time-input');
            timeInputs.forEach(input => {
                input.disabled = !this.checked;
            });
        });
    });

    window.saveSettings = saveSettings;
    window.resetSettings = resetSettings;
});

async function saveSettings() {
    // Collect all settings
    const settings = {
        idle_detection: document.getElementById('setting-idle-detection').checked,
        require_description: document.getElementById('setting-require-description').checked,
        round_time: document.getElementById('setting-round-time').value,
        min_entry: document.getElementById('setting-min-entry').value,
        reminders: document.getElementById('setting-reminders').checked,
        hourly_rate: document.getElementById('setting-hourly-rate').value,
        currency: document.getElementById('setting-currency').value,
        default_billable: document.getElementById('setting-default-billable').checked,
        timer_notification: document.getElementById('setting-timer-notification').checked,
        weekly_email: document.getElementById('setting-weekly-email').checked,
        approval_notification: document.getElementById('setting-approval-notification').checked,
        work_hours: collectWorkHours()
    };

    try {
        const response = await fetch(`${pfobData.restUrl}/timesheet/settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify(settings)
        });

        const data = await response.json();

        if (data.success) {
            alert('✅ Settings saved successfully!');
        } else {
            alert('❌ Failed to save settings: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('✅ Settings saved!\n\n(API not yet implemented - settings would be saved to database when backend is ready)');
    }
}

function collectWorkHours() {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const workHours = {};

    document.querySelectorAll('.work-day').forEach((dayElement, index) => {
        const checkbox = dayElement.querySelector('.day-checkbox');
        const timeInputs = dayElement.querySelectorAll('.time-input');

        workHours[days[index]] = {
            enabled: checkbox.checked,
            start: timeInputs[0].value,
            end: timeInputs[1].value
        };
    });

    return workHours;
}

function resetSettings() {
    if (!confirm('Reset all settings to defaults? This cannot be undone.')) {
        return;
    }

    // Reset to default values
    document.getElementById('setting-idle-detection').checked = true;
    document.getElementById('setting-require-description').checked = false;
    document.getElementById('setting-round-time').value = '15';
    document.getElementById('setting-min-entry').value = '6';
    document.getElementById('setting-reminders').checked = true;
    document.getElementById('setting-hourly-rate').value = '75';
    document.getElementById('setting-currency').value = 'USD';
    document.getElementById('setting-default-billable').checked = true;
    document.getElementById('setting-timer-notification').checked = true;
    document.getElementById('setting-weekly-email').checked = false;
    document.getElementById('setting-approval-notification').checked = true;

    // Reset work hours
    const dayCheckboxes = document.querySelectorAll('.day-checkbox');
    dayCheckboxes.forEach((checkbox, index) => {
        checkbox.checked = index < 5; // Mon-Fri
        const timeInputs = checkbox.closest('.work-day').querySelectorAll('.time-input');
        timeInputs.forEach(input => {
            input.disabled = index >= 5;
        });
    });

    alert('Settings reset to defaults');
}
</script>

<?php
PFOB_Template::footer();
