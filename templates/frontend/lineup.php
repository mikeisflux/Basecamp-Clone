<?php
/**
 * Lineup - Timeline of upcoming work
 */

$user_id = get_current_user_id();

PFOB_Template::header( 'Lineup' );

// Get user's projects
$user_projects = PFOB_Project::get_user_projects( $user_id );

// Get upcoming items (next 90 days)
$start_date = date( 'Y-m-d' );
$end_date = date( 'Y-m-d', strtotime( '+90 days' ) );

$lineup_items = array();

// Get todos with due dates
foreach ( $user_projects as $project ) {
    $todos = PFOB_Todo::get_user_assigned_items( $user_id, $project->id );
    foreach ( $todos as $todo ) {
        if ( ! empty( $todo->due_date ) && ! $todo->is_completed ) {
            $lineup_items[] = array(
                'type'       => 'todo',
                'date'       => $todo->due_date,
                'title'      => $todo->title,
                'project_id' => $project->id,
                'project'    => $project->name,
                'project_slug' => $project->slug,
                'id'         => $todo->id,
                'url'        => home_url( "/projectfob/projects/{$project->slug}/todos" ),
            );
        }
    }
}

// Get events
foreach ( $user_projects as $project ) {
    $events = PFOB_Event::get_project_events( $project->id, $start_date, $end_date );
    foreach ( $events as $event ) {
        $lineup_items[] = array(
            'type'       => 'event',
            'date'       => substr( $event->start_datetime, 0, 10 ),
            'time'       => date( 'g:i a', strtotime( $event->start_datetime ) ),
            'title'      => $event->title,
            'project_id' => $project->id,
            'project'    => $project->name,
            'project_slug' => $project->slug,
            'id'         => $event->id,
            'location'   => $event->location,
            'url'        => home_url( "/projectfob/projects/{$project->slug}/schedule" ),
        );
    }
}

// Sort by date
usort( $lineup_items, function( $a, $b ) {
    return strtotime( $a['date'] ) - strtotime( $b['date'] );
} );

// Group by date period
$grouped = array(
    'overdue'    => array(),
    'today'      => array(),
    'tomorrow'   => array(),
    'this_week'  => array(),
    'next_week'  => array(),
    'later'      => array(),
);

$today = strtotime( 'today' );
$tomorrow = strtotime( 'tomorrow' );
$end_of_week = strtotime( 'next sunday' );
$end_of_next_week = strtotime( 'next sunday + 1 week' );

foreach ( $lineup_items as $item ) {
    $item_date = strtotime( $item['date'] );

    if ( $item_date < $today ) {
        $grouped['overdue'][] = $item;
    } elseif ( $item_date >= $today && $item_date < $tomorrow ) {
        $grouped['today'][] = $item;
    } elseif ( $item_date >= $tomorrow && $item_date < strtotime( 'tomorrow +1 day' ) ) {
        $grouped['tomorrow'][] = $item;
    } elseif ( $item_date >= strtotime( 'tomorrow +1 day' ) && $item_date <= $end_of_week ) {
        $grouped['this_week'][] = $item;
    } elseif ( $item_date > $end_of_week && $item_date <= $end_of_next_week ) {
        $grouped['next_week'][] = $item;
    } else {
        $grouped['later'][] = $item;
    }
}
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-lineup">

        <div class="pfob-page-header">
            <h1>Lineup</h1>
            <p class="pfob-subtitle">Your upcoming work across all projects</p>
        </div>

        <div class="pfob-lineup-filters">
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
                <option value="todo">To-dos</option>
                <option value="event">Events</option>
            </select>
        </div>

        <?php if ( empty( $lineup_items ) ) : ?>
            <div class="pfob-empty-state">
                <p>Nothing scheduled yet. Add due dates to your to-dos or create events!</p>
            </div>
        <?php else : ?>

            <?php if ( ! empty( $grouped['overdue'] ) ) : ?>
                <div class="pfob-lineup-section pfob-lineup-overdue">
                    <h2 class="pfob-lineup-heading">
                        <span class="pfob-lineup-icon">⚠️</span>
                        Overdue (<?php echo count( $grouped['overdue'] ); ?>)
                    </h2>
                    <div class="pfob-lineup-items">
                        <?php foreach ( $grouped['overdue'] as $item ) : ?>
                            <?php include PFOB_PLUGIN_DIR . 'templates/partials/lineup-item.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $grouped['today'] ) ) : ?>
                <div class="pfob-lineup-section">
                    <h2 class="pfob-lineup-heading">
                        <span class="pfob-lineup-icon">📍</span>
                        Today - <?php echo date( 'l, F j' ); ?>
                    </h2>
                    <div class="pfob-lineup-items">
                        <?php foreach ( $grouped['today'] as $item ) : ?>
                            <?php include PFOB_PLUGIN_DIR . 'templates/partials/lineup-item.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $grouped['tomorrow'] ) ) : ?>
                <div class="pfob-lineup-section">
                    <h2 class="pfob-lineup-heading">
                        <span class="pfob-lineup-icon">📌</span>
                        Tomorrow - <?php echo date( 'l, F j', strtotime( 'tomorrow' ) ); ?>
                    </h2>
                    <div class="pfob-lineup-items">
                        <?php foreach ( $grouped['tomorrow'] as $item ) : ?>
                            <?php include PFOB_PLUGIN_DIR . 'templates/partials/lineup-item.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $grouped['this_week'] ) ) : ?>
                <div class="pfob-lineup-section">
                    <h2 class="pfob-lineup-heading">
                        <span class="pfob-lineup-icon">📅</span>
                        This Week
                    </h2>
                    <div class="pfob-lineup-items">
                        <?php foreach ( $grouped['this_week'] as $item ) : ?>
                            <?php include PFOB_PLUGIN_DIR . 'templates/partials/lineup-item.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $grouped['next_week'] ) ) : ?>
                <div class="pfob-lineup-section">
                    <h2 class="pfob-lineup-heading">
                        <span class="pfob-lineup-icon">📆</span>
                        Next Week
                    </h2>
                    <div class="pfob-lineup-items">
                        <?php foreach ( $grouped['next_week'] as $item ) : ?>
                            <?php include PFOB_PLUGIN_DIR . 'templates/partials/lineup-item.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $grouped['later'] ) ) : ?>
                <div class="pfob-lineup-section">
                    <h2 class="pfob-lineup-heading">
                        <span class="pfob-lineup-icon">🔮</span>
                        Later (<?php echo count( $grouped['later'] ); ?>)
                    </h2>
                    <div class="pfob-lineup-items">
                        <?php foreach ( $grouped['later'] as $item ) : ?>
                            <?php include PFOB_PLUGIN_DIR . 'templates/partials/lineup-item.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

// Filter functionality
document.getElementById('project-filter').addEventListener('change', (e) => {
    applyFilters();
});

document.getElementById('type-filter').addEventListener('change', (e) => {
    applyFilters();
});

function applyFilters() {
    const projectId = document.getElementById('project-filter').value;
    const type = document.getElementById('type-filter').value;
    const items = document.querySelectorAll('.pfob-lineup-item');

    items.forEach(item => {
        let show = true;

        if (projectId && item.dataset.projectId !== projectId) {
            show = false;
        }

        if (type && item.dataset.type !== type) {
            show = false;
        }

        item.style.display = show ? 'flex' : 'none';
    });

    // Hide empty sections
    document.querySelectorAll('.pfob-lineup-section').forEach(section => {
        const visibleItems = Array.from(section.querySelectorAll('.pfob-lineup-item'))
            .filter(item => item.style.display !== 'none');
        section.style.display = visibleItems.length > 0 ? 'block' : 'none';
    });
}
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
