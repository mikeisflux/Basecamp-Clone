<?php
/**
 * My Stuff - Personal workspace
 */

BCWP_Template::header( 'My Stuff' );

$user_id = get_current_user_id();
$assigned_todos = BCWP_Todo::get_user_assigned_items( $user_id );
?>

<div class="bcwp-container">
    <main class="bcwp-main bcwp-my-stuff">

        <div class="bcwp-page-header">
            <h1>My Stuff</h1>
            <p class="bcwp-subtitle">Your personal workspace across all projects</p>
        </div>

        <div class="bcwp-my-stuff-grid">

            <!-- Assigned To-dos -->
            <section class="bcwp-my-stuff-section">
                <div class="bcwp-section-header">
                    <h2>Assigned to me</h2>
                    <span class="bcwp-count"><?php echo count( $assigned_todos ); ?></span>
                </div>

                <div class="bcwp-todo-list">
                    <?php if ( empty( $assigned_todos ) ) : ?>
                        <p class="bcwp-empty-text">No items assigned to you</p>
                    <?php else : ?>
                        <?php foreach ( $assigned_todos as $todo ) : ?>
                            <div class="bcwp-todo-item" data-id="<?php echo $todo->id; ?>">
                                <input type="checkbox"
                                       class="bcwp-todo-checkbox"
                                       data-todo-id="<?php echo $todo->id; ?>"
                                       <?php checked( $todo->is_completed, 1 ); ?>>
                                <div class="bcwp-todo-content">
                                    <span class="bcwp-todo-text"><?php echo esc_html( $todo->content ); ?></span>
                                    <span class="bcwp-todo-project"><?php echo esc_html( $todo->list_name ); ?></span>
                                    <?php if ( $todo->due_date ) : ?>
                                        <span class="bcwp-todo-due">Due <?php echo BCWP_Template::format_date( $todo->due_date ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Recent Activity -->
            <section class="bcwp-my-stuff-section">
                <div class="bcwp-section-header">
                    <h2>Recent Activity</h2>
                </div>
                <div id="my-recent-activity">
                    <!-- Loaded via JavaScript -->
                </div>
            </section>

            <!-- Bookmarks -->
            <section class="bcwp-my-stuff-section">
                <div class="bcwp-section-header">
                    <h2>Bookmarks</h2>
                    <button class="bcwp-btn-icon" id="add-bookmark-btn" title="Add bookmark">+</button>
                </div>
                <div id="my-bookmarks">
                    <p class="bcwp-empty-text">No bookmarks yet</p>
                </div>
            </section>

            <!-- Drafts -->
            <section class="bcwp-my-stuff-section">
                <div class="bcwp-section-header">
                    <h2>Drafts</h2>
                </div>
                <div id="my-drafts">
                    <p class="bcwp-empty-text">No drafts saved</p>
                </div>
            </section>

            <!-- Schedule -->
            <section class="bcwp-my-stuff-section bcwp-full-width">
                <div class="bcwp-section-header">
                    <h2>My Schedule</h2>
                    <a href="<?php echo home_url( '/basecamp/schedule/' ); ?>" class="bcwp-link">View all</a>
                </div>
                <div id="my-schedule">
                    <!-- Loaded via JavaScript -->
                </div>
            </section>

        </div>

    </main>
</div>

<script>
const bcwpData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    currentUser: <?php echo json_encode( array(
        'id' => $user_id,
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};

// Handle todo checkbox changes
document.querySelectorAll('.bcwp-todo-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', async function() {
        const todoId = this.dataset.todoId;
        const isCompleted = this.checked;

        try {
            const response = await fetch(`${bcwpData.restUrl}/todo-items/${todoId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': bcwpData.nonce
                },
                body: JSON.stringify({ is_completed: isCompleted ? 1 : 0 })
            });

            if (response.ok) {
                const todoItem = this.closest('.bcwp-todo-item');
                todoItem.classList.toggle('bcwp-completed');
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
        const response = await fetch(`${bcwpData.restUrl}/activities?user_id=${bcwpData.currentUser.id}&limit=10`, {
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });
        const result = await response.json();

        const container = document.getElementById('my-recent-activity');
        if (result.success && result.data && result.data.length > 0) {
            container.innerHTML = result.data.map(activity => `
                <div class="bcwp-activity-item">
                    <span class="bcwp-activity-time">${formatDate(activity.created_at)}</span>
                    <p>${activity.description}</p>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="bcwp-empty-text">No recent activity</p>';
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
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
