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

        <div class="pfob-my-stuff-grid">

            <!-- Assigned To-dos -->
            <section class="pfob-my-stuff-section">
                <div class="pfob-section-header">
                    <h2>Assigned to me</h2>
                    <span class="pfob-count"><?php echo count( $assigned_todos ); ?></span>
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
                                    <span class="pfob-todo-project"><?php echo esc_html( $todo->list_name ); ?></span>
                                    <?php if ( $todo->due_date ) : ?>
                                        <span class="pfob-todo-due">Due <?php echo PFOB_Template::format_date( $todo->due_date ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Recent Activity -->
            <section class="pfob-my-stuff-section">
                <div class="pfob-section-header">
                    <h2>Recent Activity</h2>
                </div>
                <div id="my-recent-activity">
                    <!-- Loaded via JavaScript -->
                </div>
            </section>

            <!-- Bookmarks -->
            <section class="pfob-my-stuff-section">
                <div class="pfob-section-header">
                    <h2>Bookmarks</h2>
                    <button class="pfob-btn-icon" id="add-bookmark-btn" title="Add bookmark">+</button>
                </div>
                <div id="my-bookmarks">
                    <p class="pfob-empty-text">No bookmarks yet</p>
                </div>
            </section>

            <!-- Drafts -->
            <section class="pfob-my-stuff-section">
                <div class="pfob-section-header">
                    <h2>Drafts</h2>
                </div>
                <div id="my-drafts">
                    <p class="pfob-empty-text">No drafts saved</p>
                </div>
            </section>

            <!-- Schedule -->
            <section class="pfob-my-stuff-section pfob-full-width">
                <div class="pfob-section-header">
                    <h2>My Schedule</h2>
                    <a href="<?php echo home_url( '/projectfob/schedule/' ); ?>" class="pfob-link">View all</a>
                </div>
                <div id="my-schedule">
                    <!-- Loaded via JavaScript -->
                </div>
            </section>

        </div>

    </main>
</div>

<script>
const pfobData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'pfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    currentUser: <?php echo json_encode( array(
        'id' => $user_id,
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};

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
    }
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

loadMyActivity();
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
