<?php
/**
 * Activity Feed - Timeline of all project activity
 */

$user_id = get_current_user_id();

BCWP_Template::header( 'Activity' );

// Get user's projects for filtering
$user_projects = BCWP_Project::get_user_projects( $user_id );

// Get recent activities
$activities = BCWP_Activity::get_user_timeline( $user_id, 50 );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-activity-feed">

        <div class="bcwp-page-header">
            <h1>Activity</h1>
            <p class="bcwp-subtitle">Recent activity across your projects</p>
        </div>

        <div class="bcwp-activity-filters">
            <select id="project-filter" class="bcwp-select">
                <option value="">All Projects</option>
                <?php foreach ( $user_projects as $project ) : ?>
                    <option value="<?php echo $project->id; ?>">
                        <?php echo esc_html( $project->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="type-filter" class="bcwp-select">
                <option value="">All Types</option>
                <option value="message">Messages</option>
                <option value="todo">To-dos</option>
                <option value="document">Documents</option>
                <option value="chat">Chat</option>
                <option value="event">Events</option>
                <option value="card">Cards</option>
                <option value="comment">Comments</option>
            </select>

            <select id="action-filter" class="bcwp-select">
                <option value="">All Actions</option>
                <option value="created">Created</option>
                <option value="updated">Updated</option>
                <option value="deleted">Deleted</option>
                <option value="completed">Completed</option>
                <option value="assigned">Assigned</option>
                <option value="commented">Commented</option>
            </select>
        </div>

        <div class="bcwp-activity-list" id="activity-list">
            <?php if ( empty( $activities ) ) : ?>
                <div class="bcwp-empty-state">
                    <p>No activity yet. Start working on your projects!</p>
                </div>
            <?php else : ?>
                <?php foreach ( $activities as $activity ) : ?>
                    <div class="bcwp-activity-item"
                         data-id="<?php echo $activity->id; ?>"
                         data-project-id="<?php echo $activity->project_id; ?>"
                         data-type="<?php echo esc_attr( $activity->subject_type ); ?>"
                         data-action="<?php echo esc_attr( $activity->action_type ); ?>">

                        <div class="bcwp-activity-icon">
                            <?php echo BCWP_Activity::get_icon( $activity ); ?>
                        </div>

                        <div class="bcwp-activity-content">
                            <div class="bcwp-activity-header">
                                <div class="bcwp-activity-avatar">
                                    <?php echo BCWP_Template::user_avatar( $activity->user_id, 32 ); ?>
                                </div>
                                <div class="bcwp-activity-description">
                                    <?php echo esc_html( BCWP_Activity::format_description( $activity ) ); ?>
                                </div>
                            </div>

                            <?php if ( $activity->project_id ) : ?>
                                <?php $project = BCWP_Project::get( $activity->project_id ); ?>
                                <?php if ( $project ) : ?>
                                    <div class="bcwp-activity-project">
                                        in <a href="<?php echo home_url( '/basecamp/projects/' . $project->slug ); ?>">
                                            <?php echo esc_html( $project->name ); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="bcwp-activity-meta">
                                <span class="bcwp-activity-time">
                                    <?php echo BCWP_Template::format_date( $activity->created_at ); ?>
                                </span>
                                <?php $link = BCWP_Activity::get_link( $activity ); ?>
                                <?php if ( $link ) : ?>
                                    <a href="<?php echo esc_url( $link ); ?>" class="bcwp-activity-link">
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
            <div class="bcwp-load-more">
                <button class="bcwp-btn bcwp-btn-secondary" id="load-more-btn">
                    Load More
                </button>
            </div>
        <?php endif; ?>

    </main>
</div>

<script>
const bcwpData = {
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
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
    const items = document.querySelectorAll('.bcwp-activity-item');

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
    const emptyState = document.querySelector('.bcwp-empty-state');

    if (visibleItems.length === 0 && !emptyState) {
        const list = document.getElementById('activity-list');
        list.innerHTML = '<div class="bcwp-empty-state"><p>No activity matches your filters.</p></div>';
    }
}

// Load more activities
document.getElementById('load-more-btn')?.addEventListener('click', async () => {
    const btn = document.getElementById('load-more-btn');
    btn.disabled = true;
    btn.textContent = 'Loading...';

    try {
        const response = await fetch(
            `${bcwpData.restUrl}/activities?offset=${currentOffset}&limit=50`,
            { headers: { 'X-WP-Nonce': bcwpData.nonce } }
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
            `${bcwpData.restUrl}/activities/poll?since_id=${lastActivityId}`,
            { headers: { 'X-WP-Nonce': bcwpData.nonce } }
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
    const emptyState = list.querySelector('.bcwp-empty-state');
    if (emptyState) {
        emptyState.remove();
    }

    const item = createActivityElement(activity);
    list.insertBefore(item, list.firstChild);

    // Highlight new item
    item.classList.add('bcwp-activity-new');
    setTimeout(() => item.classList.remove('bcwp-activity-new'), 2000);
}

function appendActivity(activity) {
    const list = document.getElementById('activity-list');
    const item = createActivityElement(activity);
    list.appendChild(item);
}

function createActivityElement(activity) {
    const div = document.createElement('div');
    div.className = 'bcwp-activity-item';
    div.dataset.id = activity.id;
    div.dataset.projectId = activity.project_id || '';
    div.dataset.type = activity.subject_type;
    div.dataset.action = activity.action_type;

    div.innerHTML = `
        <div class="bcwp-activity-icon">${escapeHtml(activity.icon)}</div>
        <div class="bcwp-activity-content">
            <div class="bcwp-activity-header">
                <div class="bcwp-activity-avatar">
                    <img src="${activity.user_avatar}" alt="" class="bcwp-avatar" width="32" height="32">
                </div>
                <div class="bcwp-activity-description">${escapeHtml(activity.description)}</div>
            </div>
            ${activity.project_name ? `
                <div class="bcwp-activity-project">
                    in <a href="${activity.project_url}">${escapeHtml(activity.project_name)}</a>
                </div>
            ` : ''}
            <div class="bcwp-activity-meta">
                <span class="bcwp-activity-time">${activity.time_ago}</span>
                ${activity.link ? `<a href="${activity.link}" class="bcwp-activity-link">View</a>` : ''}
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
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
