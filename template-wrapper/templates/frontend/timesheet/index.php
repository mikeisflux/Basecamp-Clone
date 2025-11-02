<?php
/**
 * Timesheet Dashboard
 *
 * Main dashboard for time tracking. Shows recent time entries,
 * quick timer, and weekly summary.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

PFOB_Template::header( 'Timesheet' );
?>

<div class="timesheet-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">⏱️ Timesheet</h1>
    <p class="page-subtitle">Track time spent on projects and tasks.</p>

    <!-- Quick Timer -->
    <div class="timesheet-section timer-section">
        <h2 class="section-title">Quick Timer</h2>

        <div class="quick-timer">
            <div class="timer-display" id="timer-display">00:00:00</div>

            <div class="timer-controls">
                <button class="timer-button start-button" id="start-timer" onclick="startTimer()">
                    ▶️ Start Timer
                </button>
                <button class="timer-button pause-button" id="pause-timer" onclick="pauseTimer()" style="display: none;">
                    ⏸️ Pause
                </button>
                <button class="timer-button stop-button" id="stop-timer" onclick="stopTimer()" style="display: none;">
                    ⏹️ Stop & Save
                </button>
            </div>

            <div class="timer-details" id="timer-details" style="display: none;">
                <div class="detail-row">
                    <label class="detail-label">Project:</label>
                    <select id="timer-project" class="detail-input">
                        <option value="">Select project...</option>
                        <option value="1">Website Redesign</option>
                        <option value="2">Marketing Campaign</option>
                        <option value="3">Product Launch</option>
                    </select>
                </div>

                <div class="detail-row">
                    <label class="detail-label">Task:</label>
                    <input type="text" id="timer-task" class="detail-input" placeholder="What are you working on?">
                </div>

                <div class="detail-row">
                    <label class="detail-label">Notes:</label>
                    <textarea id="timer-notes" class="detail-textarea" rows="2" placeholder="Additional notes (optional)"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Summary -->
    <div class="timesheet-section">
        <h2 class="section-title">This Week</h2>

        <div class="weekly-summary">
            <div class="summary-stat">
                <div class="stat-value">32.5</div>
                <div class="stat-label">Hours Tracked</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value">5</div>
                <div class="stat-label">Projects</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value">18</div>
                <div class="stat-label">Time Entries</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value">6.5</div>
                <div class="stat-label">Avg Hours/Day</div>
            </div>
        </div>

        <div class="week-chart">
            <div class="chart-title">Daily Breakdown</div>
            <div class="chart-bars">
                <div class="chart-bar">
                    <div class="bar-fill" style="height: 80%;"></div>
                    <div class="bar-label">Mon<br>8.0h</div>
                </div>
                <div class="chart-bar">
                    <div class="bar-fill" style="height: 65%;"></div>
                    <div class="bar-label">Tue<br>6.5h</div>
                </div>
                <div class="chart-bar">
                    <div class="bar-fill" style="height: 75%;"></div>
                    <div class="bar-label">Wed<br>7.5h</div>
                </div>
                <div class="chart-bar">
                    <div class="bar-fill" style="height: 60%;"></div>
                    <div class="bar-label">Thu<br>6.0h</div>
                </div>
                <div class="chart-bar">
                    <div class="bar-fill" style="height: 50%;"></div>
                    <div class="bar-label">Fri<br>4.5h</div>
                </div>
                <div class="chart-bar">
                    <div class="bar-fill" style="height: 0%;"></div>
                    <div class="bar-label">Sat<br>0h</div>
                </div>
                <div class="chart-bar">
                    <div class="bar-fill" style="height: 0%;"></div>
                    <div class="bar-label">Sun<br>0h</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Time Entries -->
    <div class="timesheet-section">
        <h2 class="section-title">Recent Time Entries</h2>

        <div id="time-entries" class="time-entries">
            <div class="loading-message">Loading time entries...</div>
        </div>

        <button class="action-button secondary-button" onclick="loadMoreEntries()">Load More</button>
    </div>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.timesheet-page-wrapper {
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

.timesheet-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 24px;
}

.timer-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
}

.section-title {
    font-size: 20px;
    margin: 0 0 20px 0;
    color: #333;
}

.timer-section .section-title {
    color: white;
}

.quick-timer {
    text-align: center;
}

.timer-display {
    font-size: 64px;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    margin-bottom: 24px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.timer-controls {
    margin-bottom: 24px;
}

.timer-button {
    display: inline-block;
    padding: 12px 32px;
    margin: 0 8px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.start-button {
    background: #10b981;
    color: white;
}

.start-button:hover {
    background: #059669;
}

.pause-button {
    background: #f59e0b;
    color: white;
}

.pause-button:hover {
    background: #d97706;
}

.stop-button {
    background: #dc3545;
    color: white;
}

.stop-button:hover {
    background: #c82333;
}

.timer-details {
    max-width: 600px;
    margin: 0 auto;
    text-align: left;
}

.detail-row {
    margin-bottom: 16px;
}

.detail-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    color: white;
}

.detail-input,
.detail-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 6px;
    font-size: 15px;
    font-family: inherit;
    background: rgba(255,255,255,0.9);
}

.detail-input:focus,
.detail-textarea:focus {
    outline: none;
    border-color: white;
    background: white;
}

.weekly-summary {
    margin-bottom: 32px;
}

.summary-stat {
    display: inline-block;
    width: 23%;
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-right: 2%;
    margin-bottom: 12px;
}

.summary-stat:last-child {
    margin-right: 0;
}

.stat-value {
    font-size: 36px;
    font-weight: bold;
    color: #0066cc;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    color: #666;
}

.week-chart {
    background: #f8f9fa;
    padding: 24px;
    border-radius: 8px;
}

.chart-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.chart-bars {
    height: 200px;
    position: relative;
}

.chart-bar {
    display: inline-block;
    width: 13%;
    height: 100%;
    margin-right: 1%;
    vertical-align: bottom;
    position: relative;
}

.bar-fill {
    position: absolute;
    bottom: 30px;
    left: 0;
    right: 0;
    background: #0066cc;
    border-radius: 4px 4px 0 0;
    transition: height 0.3s;
}

.bar-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 12px;
    color: #666;
    line-height: 1.2;
}

.time-entries {
    min-height: 200px;
}

.time-entry {
    padding: 16px;
    background: #f8f9fa;
    border-left: 4px solid #0066cc;
    border-radius: 4px;
    margin-bottom: 12px;
}

.entry-header {
    margin-bottom: 8px;
}

.entry-project {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.entry-task {
    font-size: 14px;
    color: #666;
    margin: 4px 0;
}

.entry-meta {
    font-size: 13px;
    color: #999;
}

.entry-duration {
    display: inline-block;
    padding: 4px 12px;
    background: #0066cc;
    color: white;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    margin-left: 12px;
}

.action-button {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    border: none;
}

.secondary-button {
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
}

.secondary-button:hover {
    background: #d1d5db;
}

.loading-message {
    text-align: center;
    padding: 40px;
    color: #666;
    font-size: 15px;
}

@media (max-width: 768px) {
    .timesheet-page-wrapper {
        padding: 12px;
    }

    .timesheet-section {
        padding: 20px;
    }

    .page-title {
        font-size: 24px;
    }

    .timer-display {
        font-size: 48px;
    }

    .summary-stat {
        width: 48%;
        margin-right: 4%;
    }

    .summary-stat:nth-child(2n) {
        margin-right: 0;
    }
}
</style>

<script>
let timerInterval = null;
let timerSeconds = 0;
let timerRunning = false;

document.addEventListener('DOMContentLoaded', function() {
    loadTimeEntries();

    window.startTimer = startTimer;
    window.pauseTimer = pauseTimer;
    window.stopTimer = stopTimer;
    window.loadMoreEntries = loadMoreEntries;
});

function startTimer() {
    timerRunning = true;
    document.getElementById('start-timer').style.display = 'none';
    document.getElementById('pause-timer').style.display = 'inline-block';
    document.getElementById('stop-timer').style.display = 'inline-block';
    document.getElementById('timer-details').style.display = 'block';

    timerInterval = setInterval(() => {
        timerSeconds++;
        updateTimerDisplay();
    }, 1000);
}

function pauseTimer() {
    timerRunning = false;
    clearInterval(timerInterval);
    document.getElementById('pause-timer').textContent = timerRunning ? '⏸️ Pause' : '▶️ Resume';

    if (!timerRunning) {
        setTimeout(() => {
            timerRunning = true;
            timerInterval = setInterval(() => {
                timerSeconds++;
                updateTimerDisplay();
            }, 1000);
            document.getElementById('pause-timer').textContent = '⏸️ Pause';
        }, 100);
    }
}

async function stopTimer() {
    clearInterval(timerInterval);

    const project = document.getElementById('timer-project').value;
    const task = document.getElementById('timer-task').value;
    const notes = document.getElementById('timer-notes').value;

    if (!project || !task) {
        alert('Please select a project and enter a task description');
        return;
    }

    // Save time entry
    try {
        const response = await fetch(`${pfobData.restUrl}/timesheet/entries`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({
                project_id: project,
                task: task,
                notes: notes,
                duration: timerSeconds
            })
        });

        const data = await response.json();

        if (data.success) {
            alert(`Time entry saved: ${formatDuration(timerSeconds)}`);
            resetTimer();
            loadTimeEntries();
        } else {
            alert('Failed to save time entry: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Time entry saved (simulated): ' + formatDuration(timerSeconds));
        resetTimer();
    }
}

function resetTimer() {
    timerSeconds = 0;
    timerRunning = false;
    clearInterval(timerInterval);
    updateTimerDisplay();

    document.getElementById('start-timer').style.display = 'inline-block';
    document.getElementById('pause-timer').style.display = 'none';
    document.getElementById('stop-timer').style.display = 'none';
    document.getElementById('timer-details').style.display = 'none';

    document.getElementById('timer-project').value = '';
    document.getElementById('timer-task').value = '';
    document.getElementById('timer-notes').value = '';
}

function updateTimerDisplay() {
    const hours = Math.floor(timerSeconds / 3600);
    const minutes = Math.floor((timerSeconds % 3600) / 60);
    const seconds = timerSeconds % 60;

    document.getElementById('timer-display').textContent =
        String(hours).padStart(2, '0') + ':' +
        String(minutes).padStart(2, '0') + ':' +
        String(seconds).padStart(2, '0');
}

function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);

    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
}

async function loadTimeEntries() {
    try {
        const response = await fetch(`${pfobData.restUrl}/timesheet/entries`, {
            headers: {
                'X-WP-Nonce': pfobData.nonce
            }
        });

        const data = await response.json();

        if (data.success) {
            renderTimeEntries(data.entries || []);
        } else {
            document.getElementById('time-entries').innerHTML = '<div class="loading-message">No time entries yet</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        renderPlaceholderEntries();
    }
}

function renderTimeEntries(entries) {
    if (entries.length === 0) {
        document.getElementById('time-entries').innerHTML = '<div class="loading-message">No time entries yet. Start the timer above!</div>';
        return;
    }

    const html = entries.map(entry => `
        <div class="time-entry">
            <div class="entry-header">
                <span class="entry-project">${entry.project_name}</span>
                <span class="entry-duration">${entry.duration_formatted}</span>
            </div>
            <div class="entry-task">${entry.task}</div>
            ${entry.notes ? `<div class="entry-task" style="font-style: italic;">${entry.notes}</div>` : ''}
            <div class="entry-meta">${entry.created_at} • ${entry.user_name}</div>
        </div>
    `).join('');

    document.getElementById('time-entries').innerHTML = html;
}

function renderPlaceholderEntries() {
    const placeholderEntries = [
        {
            project_name: 'Website Redesign',
            task: 'Updated homepage mockups',
            notes: 'Client requested changes to color scheme',
            duration_formatted: '2h 30m',
            created_at: 'Today at 2:15 PM',
            user_name: 'You'
        },
        {
            project_name: 'Marketing Campaign',
            task: 'Wrote blog post draft',
            notes: '',
            duration_formatted: '1h 45m',
            created_at: 'Today at 11:00 AM',
            user_name: 'You'
        }
    ];

    renderTimeEntries(placeholderEntries);
}

function loadMoreEntries() {
    alert('Load more functionality coming soon.');
}
</script>

<?php
PFOB_Template::footer();
