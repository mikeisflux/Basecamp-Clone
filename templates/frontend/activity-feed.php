<?php
/**
 * Activity Feed - Timeline of all project activity
 */

$user_id = get_current_user_id();

PFOB_Template::header( 'Activity' );

// Get user's projects for filtering
$user_projects = PFOB_Project::get_user_projects( $user_id );

// Get recent activities
$activities = PFOB_Activity::get_user_timeline( $user_id, 50 );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-activity-feed">

        <div class="pfob-page-header">
            <h1>Activity</h1>
            <p class="pfob-subtitle">Recent activity across your projects</p>
        </div>

        <div class="pfob-activity-filters">
            <select id="project-filter" class="pfob-select">
                <option value="">All Projects</option>
                <?php foreach ( $user_projects as $project ) : ?>
                    <option value="<?php echo $project->id; ?>">
                        <?php echo esc_html( $project->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="type-filter" class="pfob-select">
                <option value="">All Types</option>
                <option value="message">Messages</option>
                <option value="todo">To-dos</option>
                <option value="document">Documents</option>
                <option value="chat">Chat</option>
                <option value="event">Events</option>
                <option value="card">Cards</option>
                <option value="comment">Comments</option>
            </select>

            <select id="action-filter" class="pfob-select">
                <option value="">All Actions</option>
                <option value="created">Created</option>
                <option value="updated">Updated</option>
                <option value="deleted">Deleted</option>
                <option value="completed">Completed</option>
                <option value="assigned">Assigned</option>
                <option value="commented">Commented</option>
            </select>
        </div>

        <div class="pfob-activity-list" id="activity-list">
            <?php if ( empty( $activities ) ) : ?>
                <div class="pfob-empty-state">
                    <p>No activity yet. Start working on your projects!</p>
                </div>
            <?php else : ?>
                <?php foreach ( $activities as $activity ) : ?>
                    <div class="pfob-activity-item"
                         data-id="<?php echo $activity->id; ?>"
                         data-project-id="<?php echo $activity->project_id; ?>"
                         data-type="<?php echo esc_attr( $activity->subject_type ); ?>"
                         data-action="<?php echo esc_attr( $activity->action_type ); ?>">

                        <div class="pfob-activity-icon">
                            <?php echo PFOB_Activity::get_icon( $activity ); ?>
                        </div>

                        <div class="pfob-activity-content">
                            <div class="pfob-activity-header">
                                <div class="pfob-activity-avatar">
                                    <?php echo PFOB_Template::user_avatar( $activity->user_id, 32 ); ?>
                                </div>
                                <div class="pfob-activity-description">
                                    <?php echo esc_html( PFOB_Activity::format_description( $activity ) ); ?>
                                </div>
                            </div>

                            <?php if ( $activity->project_id ) : ?>
                                <?php $project = PFOB_Project::get( $activity->project_id ); ?>
                                <?php if ( $project ) : ?>
                                    <div class="pfob-activity-project">
                                        in <a href="<?php echo home_url( '/projectfob/projects/' . $project->slug ); ?>">
                                            <?php echo esc_html( $project->name ); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="pfob-activity-meta">
                                <span class="pfob-activity-time">
                                    <?php echo PFOB_Template::format_date( $activity->created_at ); ?>
                                </span>
                                <?php $link = PFOB_Activity::get_link( $activity ); ?>
                                <?php if ( $link ) : ?>
                                    <a href="<?php echo esc_url( $link ); ?>" class="pfob-activity-link">
                                        View
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ( count( $activities ) >= 50 ) : ?>
            <div class="pfob-load-more">
                <button class="pfob-btn pfob-btn-secondary" id="load-more-btn">
                    Load More
                </button>
            </div>
        <?php endif; ?>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'pfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    userId: <?php echo $user_id; ?>
};

let lastActivityId = <?php echo empty( $activities ) ? 0 : $activities[0]->id; ?>;
let currentOffset = <?php echo count( $activities ); ?>;
let currentFilters = {
    projectId: '',
    type: '',
    action: ''
};

// Filter changes
document.getElementById('project-filter').addEventListener('change', (e) => {
    currentFilters.projectId = e.target.value;
    applyFilters();
});

document.getElementById('type-filter').addEventListener('change', (e) => {
    currentFilters.type = e.target.value;
    applyFilters();
});

document.getElementById('action-filter').addEventListener('change', (e) => {
    currentFilters.action = e.target.value;
    applyFilters();
});

function applyFilters() {
    const items = document.querySelectorAll('.pfob-activity-item');

    items.forEach(item => {
        let show = true;

        if (currentFilters.projectId && item.dataset.projectId !== currentFilters.projectId) {
            show = false;
        }

        if (currentFilters.type && item.dataset.type !== currentFilters.type) {
            show = false;
        }

        if (currentFilters.action && item.dataset.action !== currentFilters.action) {
            show = false;
        }

        item.style.display = show ? 'flex' : 'none';
    });

    // Check if any items are visible
    const visibleItems = Array.from(items).filter(item => item.style.display !== 'none');
    const emptyState = document.querySelector('.pfob-empty-state');

    if (visibleItems.length === 0 && !emptyState) {
        const list = document.getElementById('activity-list');
        list.innerHTML = '<div class="pfob-empty-state"><p>No activity matches your filters.</p></div>';
    }
}

// Load more activities
document.getElementById('load-more-btn')?.addEventListener('click', async () => {
    const btn = document.getElementById('load-more-btn');
    btn.disabled = true;
    btn.textContent = 'Loading...';

    try {
        const response = await fetch(
            `${pfobData.restUrl}/activities?offset=${currentOffset}&limit=50`,
            { headers: { 'X-WP-Nonce': pfobData.nonce } }
        );

        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(activity => {
                appendActivity(activity);
            });

            currentOffset += result.data.length;

            if (result.data.length < 50) {
                btn.parentElement.remove();
            } else {
                btn.disabled = false;
                btn.textContent = 'Load More';
            }
        } else {
            btn.parentElement.remove();
        }
    } catch (error) {
        console.error('Failed to load more activities:', error);
        btn.disabled = false;
        btn.textContent = 'Load More';
    }
});

// Poll for new activities
async function pollNewActivities() {
    try {
        const response = await fetch(
            `${pfobData.restUrl}/activities/poll?since_id=${lastActivityId}`,
            { headers: { 'X-WP-Nonce': pfobData.nonce } }
        );

        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            result.data.reverse().forEach(activity => {
                prependActivity(activity);
                lastActivityId = Math.max(lastActivityId, activity.id);
            });
        }
    } catch (error) {
        console.error('Polling error:', error);
    }
}

function prependActivity(activity) {
    const list = document.getElementById('activity-list');
    const emptyState = list.querySelector('.pfob-empty-state');
    if (emptyState) {
        emptyState.remove();
    }

    const item = createActivityElement(activity);
    list.insertBefore(item, list.firstChild);

    // Highlight new item
    item.classList.add('pfob-activity-new');
    setTimeout(() => item.classList.remove('pfob-activity-new'), 2000);
}

function appendActivity(activity) {
    const list = document.getElementById('activity-list');
    const item = createActivityElement(activity);
    list.appendChild(item);
}

function createActivityElement(activity) {
    const div = document.createElement('div');
    div.className = 'pfob-activity-item';
    div.dataset.id = activity.id;
    div.dataset.projectId = activity.project_id || '';
    div.dataset.type = activity.subject_type;
    div.dataset.action = activity.action_type;

    div.innerHTML = `
        <div class="pfob-activity-icon">${escapeHtml(activity.icon)}</div>
        <div class="pfob-activity-content">
            <div class="pfob-activity-header">
                <div class="pfob-activity-avatar">
                    <img src="${activity.user_avatar}" alt="" class="pfob-avatar" width="32" height="32">
                </div>
                <div class="pfob-activity-description">${escapeHtml(activity.description)}</div>
            </div>
            ${activity.project_name ? `
                <div class="pfob-activity-project">
                    in <a href="${activity.project_url}">${escapeHtml(activity.project_name)}</a>
                </div>
            ` : ''}
            <div class="pfob-activity-meta">
                <span class="pfob-activity-time">${activity.time_ago}</span>
                ${activity.link ? `<a href="${activity.link}" class="pfob-activity-link">View</a>` : ''}
            </div>
        </div>
    `;

    return div;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Poll every 10 seconds
setInterval(pollNewActivities, 10000);
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
