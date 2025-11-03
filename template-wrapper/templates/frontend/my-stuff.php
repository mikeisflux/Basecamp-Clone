<?php
/**
 * My Stuff - Personal workspace
 */

PFOB_Template::header( 'My Stuff' );

$user_id = get_current_user_id();
$assigned_todos = PFOB_Todo::get_user_assigned_items( $user_id );
?>

<div class="pfob-container">
    <main class="pfob-main pfob-my-stuff">

        <div class="pfob-page-header">
            <h1>My Stuff</h1>
            <p class="pfob-subtitle">Your personal workspace across all projects</p>
        </div>

        <div class="pfob-my-stuff-container">
            <!-- Left Sidebar Navigation -->
            <nav class="pfob-my-stuff-nav">
                <button class="pfob-nav-item active" data-section="assigned">
                    <span class="pfob-nav-icon">✓</span>
                    <span class="pfob-nav-label">Assigned to me</span>
                    <span class="pfob-nav-count"><?php echo count( $assigned_todos ); ?></span>
                </button>
                <button class="pfob-nav-item" data-section="activity">
                    <span class="pfob-nav-icon">⚡</span>
                    <span class="pfob-nav-label">Recent Activity</span>
                </button>
                <button class="pfob-nav-item" data-section="bookmarks">
                    <span class="pfob-nav-icon">🔖</span>
                    <span class="pfob-nav-label">Bookmarks</span>
                </button>
                <button class="pfob-nav-item" data-section="drafts">
                    <span class="pfob-nav-icon">📝</span>
                    <span class="pfob-nav-label">Drafts</span>
                </button>
                <button class="pfob-nav-item" data-section="schedule">
                    <span class="pfob-nav-icon">📅</span>
                    <span class="pfob-nav-label">My Schedule</span>
                </button>
            </nav>

            <!-- Right Content Area -->
            <div class="pfob-my-stuff-content">

                <!-- Assigned To-dos Section -->
                <div class="pfob-content-section active" id="section-assigned">
                    <div class="pfob-section-header">
                        <h2>Assigned to me</h2>
                    </div>

                    <div class="pfob-todo-list">
                        <?php if ( empty( $assigned_todos ) ) : ?>
                            <p class="pfob-empty-text">No items assigned to you</p>
                        <?php else : ?>
                            <?php foreach ( $assigned_todos as $todo ) : ?>
                                <div class="pfob-todo-item" data-id="<?php echo $todo->id; ?>">
                                    <input type="checkbox"
                                           class="pfob-todo-checkbox"
                                           data-todo-id="<?php echo $todo->id; ?>"
                                           <?php checked( $todo->is_completed, 1 ); ?>>
                                    <div class="pfob-todo-content">
                                        <span class="pfob-todo-text"><?php echo esc_html( $todo->content ); ?></span>
                                        <div class="pfob-todo-meta">
                                            <span class="pfob-todo-project"><?php echo esc_html( $todo->list_name ); ?></span>
                                            <?php if ( $todo->due_date ) : ?>
                                                <span class="pfob-todo-due">Due <?php echo PFOB_Template::format_date( $todo->due_date ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="pfob-content-section" id="section-activity">
                    <div class="pfob-section-header">
                        <h2>Recent Activity</h2>
                    </div>
                    <div id="my-recent-activity">
                        <div class="pfob-loading">Loading...</div>
                    </div>
                </div>

                <!-- Bookmarks Section -->
                <div class="pfob-content-section" id="section-bookmarks">
                    <div class="pfob-section-header">
                        <h2>Bookmarks</h2>
                        <button class="pfob-btn pfob-btn-secondary pfob-btn-sm" id="add-bookmark-btn">
                            + Add bookmark
                        </button>
                    </div>
                    <div id="my-bookmarks">
                        <p class="pfob-empty-text">No bookmarks yet</p>
                    </div>
                </div>

                <!-- Drafts Section -->
                <div class="pfob-content-section" id="section-drafts">
                    <div class="pfob-section-header">
                        <h2>Drafts</h2>
                    </div>
                    <div id="my-drafts">
                        <p class="pfob-empty-text">No drafts saved</p>
                    </div>
                </div>

                <!-- Schedule Section -->
                <div class="pfob-content-section" id="section-schedule">
                    <div class="pfob-section-header">
                        <h2>My Schedule</h2>
                        <a href="<?php echo home_url( '/projectfob/schedule/' ); ?>" class="pfob-btn pfob-btn-secondary pfob-btn-sm">
                            View full schedule
                        </a>
                    </div>
                    <div id="my-schedule">
                        <div class="pfob-loading">Loading...</div>
                    </div>
                </div>

            </div>
        </div>

    </main>
</div>

<style>
.pfob-my-stuff-container {
    display: flex;
    gap: 0;
    min-height: 500px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Left Navigation Sidebar */
.pfob-my-stuff-nav {
    width: 280px;
    background: #f8f9fa;
    border-right: 1px solid #e0e0e0;
    padding: 20px 0;
    flex-shrink: 0;
}

.pfob-nav-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 24px;
    border: none;
    background: transparent;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.pfob-nav-item:hover {
    background: rgba(45, 144, 97, 0.05);
}

.pfob-nav-item.active {
    background: white;
    border-left-color: #2d9061;
    font-weight: 500;
}

.pfob-nav-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.pfob-nav-label {
    flex: 1;
    font-size: 15px;
    color: #333;
}

.pfob-nav-item.active .pfob-nav-label {
    color: #2d9061;
}

.pfob-nav-count {
    font-size: 13px;
    background: #e0e0e0;
    color: #666;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 600;
}

.pfob-nav-item.active .pfob-nav-count {
    background: #2d9061;
    color: white;
}

/* Right Content Area */
.pfob-my-stuff-content {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
}

.pfob-content-section {
    display: none;
}

.pfob-content-section.active {
    display: block;
}

.pfob-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f0f0;
}

.pfob-section-header h2 {
    margin: 0;
    font-size: 24px;
    color: #333;
}

/* Todo Items */
.pfob-todo-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pfob-todo-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    transition: all 0.2s;
}

.pfob-todo-item:hover {
    border-color: #2d9061;
    background: #f0f8f4;
}

.pfob-todo-item.pfob-completed {
    opacity: 0.6;
}

.pfob-todo-item.pfob-completed .pfob-todo-text {
    text-decoration: line-through;
}

.pfob-todo-checkbox {
    margin-top: 2px;
    width: 20px;
    height: 20px;
    cursor: pointer;
    flex-shrink: 0;
}

.pfob-todo-content {
    flex: 1;
}

.pfob-todo-text {
    display: block;
    font-size: 15px;
    color: #333;
    margin-bottom: 8px;
}

.pfob-todo-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.pfob-todo-project {
    font-size: 13px;
    color: #666;
}

.pfob-todo-due {
    font-size: 13px;
    color: #dc3545;
    font-weight: 500;
}

/* Activity Items */
.pfob-activity-item {
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 12px;
    border-left: 3px solid #2d9061;
}

.pfob-activity-time {
    font-size: 12px;
    color: #999;
    display: block;
    margin-bottom: 6px;
}

.pfob-activity-item p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

/* Empty States */
.pfob-empty-text {
    text-align: center;
    padding: 60px 20px;
    color: #999;
    font-size: 15px;
}

.pfob-loading {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    font-size: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .pfob-my-stuff-container {
        flex-direction: column;
    }

    .pfob-my-stuff-nav {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #e0e0e0;
        padding: 10px 0;
        display: flex;
        overflow-x: auto;
    }

    .pfob-nav-item {
        flex-direction: column;
        padding: 12px 16px;
        text-align: center;
        gap: 6px;
        border-left: none;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }

    .pfob-nav-item.active {
        border-left: none;
        border-bottom-color: #2d9061;
    }

    .pfob-my-stuff-content {
        padding: 20px;
    }
}
</style>

<script>
const pfobData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    currentUser: <?php echo json_encode( array(
        'id' => $user_id,
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};

// Tab Navigation
document.querySelectorAll('.pfob-nav-item').forEach(navItem => {
    navItem.addEventListener('click', function() {
        const section = this.dataset.section;

        // Update nav active state
        document.querySelectorAll('.pfob-nav-item').forEach(item => {
            item.classList.remove('active');
        });
        this.classList.add('active');

        // Update content active state
        document.querySelectorAll('.pfob-content-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById('section-' + section).classList.add('active');

        // Load data if needed
        if (section === 'activity' && !activityLoaded) {
            loadMyActivity();
            activityLoaded = true;
        } else if (section === 'schedule' && !scheduleLoaded) {
            loadMySchedule();
            scheduleLoaded = true;
        }
    });
});

let activityLoaded = false;
let scheduleLoaded = false;

// Handle todo checkbox changes
document.querySelectorAll('.pfob-todo-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', async function() {
        const todoId = this.dataset.todoId;
        const isCompleted = this.checked;

        try {
            const response = await fetch(`${pfobData.restUrl}/todo-items/${todoId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({ is_completed: isCompleted ? 1 : 0 })
            });

            if (response.ok) {
                const todoItem = this.closest('.pfob-todo-item');
                todoItem.classList.toggle('pfob-completed');
            }
        } catch (error) {
            console.error('Failed to update todo:', error);
            this.checked = !isCompleted;
        }
    });
});

// Load recent activity
async function loadMyActivity() {
    try {
        const response = await fetch(`${pfobData.restUrl}/activities?user_id=${pfobData.currentUser.id}&limit=10`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });
        const result = await response.json();

        const container = document.getElementById('my-recent-activity');
        if (result.success && result.data && result.data.length > 0) {
            container.innerHTML = result.data.map(activity => `
                <div class="pfob-activity-item">
                    <span class="pfob-activity-time">${formatDate(activity.created_at)}</span>
                    <p>${activity.description}</p>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="pfob-empty-text">No recent activity</p>';
        }
    } catch (error) {
        console.error('Failed to load activity:', error);
        document.getElementById('my-recent-activity').innerHTML = '<p class="pfob-empty-text">Failed to load activity</p>';
    }
}

// Load schedule
async function loadMySchedule() {
    const container = document.getElementById('my-schedule');
    container.innerHTML = '<p class="pfob-empty-text">No upcoming events</p>';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
}
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
