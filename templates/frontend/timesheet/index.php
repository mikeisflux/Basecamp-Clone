<?php
/**
 * Timesheet Dashboard
 *
 * Main dashboard for time tracking. Shows recent time entries,
 * quick timer with popout functionality and idle detection, and weekly summary.
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
        <div class="timer-header">
            <h2 class="section-title">Quick Timer</h2>
            <button class="popout-button" onclick="openPopout()" title="Open in popup window">
                🗗 Pop Out
            </button>
        </div>

        <div class="quick-timer">
            <div class="timer-display" id="timer-display">00:00:00</div>

            <!-- Current Project/Task Display -->
            <div class="current-work" id="current-work" style="display: none;">
                <div class="current-work-label">Currently Working On:</div>
                <div class="current-work-project" id="current-project-name">—</div>
                <div class="current-work-task" id="current-task-name">—</div>
            </div>

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
                    <select id="timer-project" class="detail-input" onchange="updateCurrentWork()">
                        <option value="">Select project...</option>
                        <option value="1">Website Redesign</option>
                        <option value="2">Marketing Campaign</option>
                        <option value="3">Product Launch</option>
                    </select>
                </div>

                <div class="detail-row">
                    <label class="detail-label">Task:</label>
                    <input type="text" id="timer-task" class="detail-input" placeholder="What are you working on?" onchange="updateCurrentWork()">
                </div>

                <div class="detail-row">
                    <label class="detail-label">Notes:</label>
                    <textarea id="timer-notes" class="detail-textarea" rows="2" placeholder="Additional notes (optional)"></textarea>
                </div>

                <div class="detail-row">
                    <label class="detail-label checkbox-label">
                        <input type="checkbox" id="timer-billable" checked>
                        Mark as billable
                    </label>
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

<!-- Idle Confirmation Modal -->
<div id="idle-modal" class="idle-modal" style="display: none;">
    <div class="idle-modal-content">
        <h2 class="idle-modal-title">⏰ Still Working?</h2>
        <p class="idle-modal-message">Your timer has been running for a while. Are you still working on this task?</p>
        <div class="idle-modal-info">
            <strong id="idle-project-name">Website Redesign</strong><br>
            <span id="idle-task-name">Homepage Design</span><br>
            <span class="idle-duration">Time elapsed: <strong id="idle-duration">0:30:00</strong></span>
        </div>
        <div class="idle-modal-actions">
            <button class="action-button primary-button" onclick="confirmStillWorking()">✅ Yes, Still Working</button>
            <button class="action-button secondary-button" onclick="stopTimerFromIdle()">⏹️ Stop Timer</button>
        </div>
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

.timer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-title {
    font-size: 20px;
    margin: 0;
    color: #333;
}

.timer-section .section-title {
    color: white;
}

.popout-button {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.popout-button:hover {
    background: rgba(255, 255, 255, 0.3);
}

.quick-timer {
    text-align: center;
}

.timer-display {
    font-size: 64px;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    margin-bottom: 16px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

/* Current Work Display */
.current-work {
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    padding: 16px;
    margin: 0 auto 24px;
    max-width: 500px;
    backdrop-filter: blur(10px);
}

.current-work-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 8px;
}

.current-work-project {
    font-size: 20px;
    font-weight: 700;
    color: white;
    margin-bottom: 4px;
}

.current-work-task {
    font-size: 16px;
    color: rgba(255, 255, 255, 0.9);
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
    transform: scale(1.05);
}

.pause-button {
    background: #f59e0b;
    color: white;
}

.pause-button:hover {
    background: #d97706;
    transform: scale(1.05);
}

.stop-button {
    background: #dc3545;
    color: white;
}

.stop-button:hover {
    background: #c82333;
    transform: scale(1.05);
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

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-label input {
    margin-right: 8px;
    cursor: pointer;
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

/* Idle Modal */
.idle-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: flash 1s ease-in-out infinite;
}

@keyframes flash {
    0%, 100% { background: rgba(0, 0, 0, 0.7); }
    50% { background: rgba(255, 165, 0, 0.3); }
}

.idle-modal-content {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 500px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: bounce 0.5s ease-out;
}

@keyframes bounce {
    0% { transform: scale(0.8); opacity: 0; }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); opacity: 1; }
}

.idle-modal-title {
    font-size: 28px;
    color: #333;
    margin: 0 0 16px 0;
    text-align: center;
}

.idle-modal-message {
    font-size: 16px;
    color: #666;
    text-align: center;
    margin-bottom: 24px;
}

.idle-modal-info {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    text-align: center;
    line-height: 1.6;
}

.idle-duration {
    color: #666;
    font-size: 14px;
}

.idle-modal-actions {
    display: flex;
    gap: 12px;
}

/* Weekly Summary */
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

.primary-button {
    background: #0066cc;
    color: white;
    flex: 1;
}

.primary-button:hover {
    background: #0052a3;
}

.secondary-button {
    background: #e5e7eb;
    color: #333;
    border: 1px solid #d1d5db;
    flex: 1;
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

    .idle-modal-content {
        margin: 20px;
        padding: 24px;
    }

    .idle-modal-actions {
        flex-direction: column;
    }
}
</style>

<script>
let timerInterval = null;
let timerSeconds = 0;
let timerRunning = false;
let idleCheckInterval = null;
let lastIdleCheck = null;
let titleFlashInterval = null;
let originalTitle = document.title;

const IDLE_CHECK_MINUTES = 30; // Check every 30 minutes
const IDLE_TIMEOUT_SECONDS = 60; // Auto-stop after 60 seconds of no response

// Make all functions globally available immediately
window.startTimer = function() { return startTimer(); };
window.pauseTimer = function() { return pauseTimer(); };
window.stopTimer = function() { return stopTimer(); };
window.loadMoreEntries = function() { return loadMoreEntries(); };
window.openPopout = function() { return openPopout(); };
window.updateCurrentWork = function() { return updateCurrentWork(); };
window.confirmStillWorking = function() { return confirmStillWorking(); };
window.stopTimerFromIdle = function() { return stopTimerFromIdle(); };

document.addEventListener('DOMContentLoaded', function() {
    loadTimeEntries();
});

function updateCurrentWork() {
    const projectSelect = document.getElementById('timer-project');
    const taskInput = document.getElementById('timer-task');
    const currentWork = document.getElementById('current-work');
    const currentProjectName = document.getElementById('current-project-name');
    const currentTaskName = document.getElementById('current-task-name');

    const projectName = projectSelect.options[projectSelect.selectedIndex].text;
    const taskName = taskInput.value;

    if (projectName && taskName) {
        currentProjectName.textContent = projectName;
        currentTaskName.textContent = taskName;
        currentWork.style.display = 'block';
    } else {
        currentWork.style.display = 'none';
    }
}

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

    // Start idle check
    lastIdleCheck = Date.now();
    idleCheckInterval = setInterval(checkIdleStatus, 60000); // Check every minute
}

function checkIdleStatus() {
    const minutesElapsed = (Date.now() - lastIdleCheck) / 1000 / 60;

    if (minutesElapsed >= IDLE_CHECK_MINUTES) {
        showIdleModal();
    }
}

function showIdleModal() {
    clearInterval(timerInterval);

    const modal = document.getElementById('idle-modal');
    const projectSelect = document.getElementById('timer-project');
    const taskInput = document.getElementById('timer-task');

    document.getElementById('idle-project-name').textContent =
        projectSelect.options[projectSelect.selectedIndex].text || 'Unknown Project';
    document.getElementById('idle-task-name').textContent =
        taskInput.value || 'No task description';
    document.getElementById('idle-duration').textContent = formatDuration(timerSeconds);

    modal.style.display = 'flex';

    // Start title flashing
    startTitleFlash();

    // Play notification sound (if permissions allow)
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSeL0fPTgjMGHm7A7+OZSA0PVqzn77BdGAg+ltz0yHkqBSN1xe/glUYLEly57OylUxEJQ5zg87xrJAYniM/z1YU1Bxpr');
        audio.play();
    } catch (e) {
        // Silent fail if audio not supported
    }

    // Auto-stop after timeout
    setTimeout(() => {
        if (document.getElementById('idle-modal').style.display === 'flex') {
            stopTimerFromIdle();
        }
    }, IDLE_TIMEOUT_SECONDS * 1000);
}

function startTitleFlash() {
    let isOriginal = true;
    titleFlashInterval = setInterval(() => {
        document.title = isOriginal ? '⏰ STILL WORKING? ⏰' : originalTitle;
        isOriginal = !isOriginal;
    }, 1000);
}

function stopTitleFlash() {
    clearInterval(titleFlashInterval);
    document.title = originalTitle;
}

function confirmStillWorking() {
    document.getElementById('idle-modal').style.display = 'none';
    stopTitleFlash();

    // Reset idle check
    lastIdleCheck = Date.now();

    // Resume timer
    timerInterval = setInterval(() => {
        timerSeconds++;
        updateTimerDisplay();
    }, 1000);
}

function stopTimerFromIdle() {
    document.getElementById('idle-modal').style.display = 'none';
    stopTitleFlash();
    stopTimer();
}

function pauseTimer() {
    timerRunning = false;
    clearInterval(timerInterval);
    clearInterval(idleCheckInterval);
    document.getElementById('pause-timer').textContent = timerRunning ? '⏸️ Pause' : '▶️ Resume';

    if (!timerRunning) {
        setTimeout(() => {
            timerRunning = true;
            timerInterval = setInterval(() => {
                timerSeconds++;
                updateTimerDisplay();
            }, 1000);
            lastIdleCheck = Date.now();
            idleCheckInterval = setInterval(checkIdleStatus, 60000);
            document.getElementById('pause-timer').textContent = '⏸️ Pause';
        }, 100);
    }
}

async function stopTimer() {
    clearInterval(timerInterval);
    clearInterval(idleCheckInterval);

    const project = document.getElementById('timer-project').value;
    const task = document.getElementById('timer-task').value;
    const notes = document.getElementById('timer-notes').value;
    const billable = document.getElementById('timer-billable').checked;

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
                duration: timerSeconds,
                billable: billable
            })
        });

        const data = await response.json();

        if (data.success) {
            alert(`✅ Time entry saved: ${formatDuration(timerSeconds)}`);
            resetTimer();
            loadTimeEntries();
        } else {
            alert('❌ Failed to save time entry: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert(`✅ Time entry saved (simulated): ${formatDuration(timerSeconds)}\n\n(API will save this when backend is ready)`);
        resetTimer();
    }
}

function resetTimer() {
    timerSeconds = 0;
    timerRunning = false;
    clearInterval(timerInterval);
    clearInterval(idleCheckInterval);
    stopTitleFlash();
    updateTimerDisplay();

    document.getElementById('start-timer').style.display = 'inline-block';
    document.getElementById('pause-timer').style.display = 'none';
    document.getElementById('stop-timer').style.display = 'none';
    document.getElementById('timer-details').style.display = 'none';
    document.getElementById('current-work').style.display = 'none';

    document.getElementById('timer-project').value = '';
    document.getElementById('timer-task').value = '';
    document.getElementById('timer-notes').value = '';
    document.getElementById('timer-billable').checked = true;
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

function openPopout() {
    const width = 400;
    const height = 500;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;

    const popupWindow = window.open(
        '',
        'TimerPopout',
        `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=no`
    );

    if (popupWindow) {
        popupWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>⏱️ Timer - ProjectFOB</title>
                <style>
                    body {
                        margin: 0;
                        padding: 20px;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        min-height: 100vh;
                        box-sizing: border-box;
                    }
                    .timer-popout {
                        text-align: center;
                    }
                    .popout-display {
                        font-size: 48px;
                        font-weight: bold;
                        font-family: 'Courier New', monospace;
                        margin: 20px 0;
                        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                    }
                    .popout-work {
                        background: rgba(255,255,255,0.15);
                        border-radius: 8px;
                        padding: 16px;
                        margin: 20px 0;
                        backdrop-filter: blur(10px);
                    }
                    .popout-project {
                        font-size: 18px;
                        font-weight: 700;
                        margin-bottom: 8px;
                    }
                    .popout-task {
                        font-size: 14px;
                        opacity: 0.9;
                    }
                    .popout-controls button {
                        padding: 10px 20px;
                        margin: 8px;
                        border-radius: 6px;
                        border: none;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                    }
                    .stop-btn {
                        background: #dc3545;
                        color: white;
                    }
                </style>
            </head>
            <body>
                <div class="timer-popout">
                    <h2>⏱️ Time Tracker</h2>
                    <div class="popout-display" id="popout-display">00:00:00</div>
                    <div class="popout-work" id="popout-work">
                        <div class="popout-project" id="popout-project">—</div>
                        <div class="popout-task" id="popout-task">—</div>
                    </div>
                    <div class="popout-controls">
                        <button class="stop-btn" onclick="stopFromPopout()">⏹️ Stop & Close</button>
                    </div>
                </div>
                <script>
                    let seconds = ${timerSeconds};
                    setInterval(() => {
                        if (window.opener && !window.opener.closed) {
                            seconds = window.opener.timerSeconds;
                            updateDisplay();
                        }
                    }, 1000);

                    function updateDisplay() {
                        const h = Math.floor(seconds / 3600);
                        const m = Math.floor((seconds % 3600) / 60);
                        const s = seconds % 60;
                        document.getElementById('popout-display').textContent =
                            String(h).padStart(2, '0') + ':' +
                            String(m).padStart(2, '0') + ':' +
                            String(s).padStart(2, '0');
                    }

                    function stopFromPopout() {
                        if (window.opener && !window.opener.closed) {
                            window.opener.stopTimer();
                            window.close();
                        }
                    }

                    // Sync project/task from parent
                    setInterval(() => {
                        if (window.opener && !window.opener.closed) {
                            const projectEl = window.opener.document.getElementById('current-project-name');
                            const taskEl = window.opener.document.getElementById('current-task-name');
                            if (projectEl && taskEl) {
                                document.getElementById('popout-project').textContent = projectEl.textContent;
                                document.getElementById('popout-task').textContent = taskEl.textContent;
                            }
                        }
                    }, 1000);
                </script>
            </body>
            </html>
        `);
    } else {
        alert('Please allow popups for this site to use the popout timer feature.');
    }
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
