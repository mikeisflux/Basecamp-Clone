<?php
/**
 * Dashboard template
 */

PFOB_Template::header( 'Dashboard' );

$user_id = get_current_user_id();
$projects = PFOB_Project::get_user_projects( $user_id );

// Get user's preferred view (default: grid/tile)
$view_mode = get_user_meta( $user_id, 'pfob_projects_view', true ) ?: 'grid';
?>

<div class="pfob-container">
    <main class="pfob-main pfob-dashboard">

        <div class="pfob-page-header">
            <div class="pfob-company-logo">
                <h1><?php echo esc_html( get_option( 'pfob_company_name' ) ); ?></h1>
            </div>

            <div class="pfob-actions">
                <button class="pfob-btn pfob-btn-primary" id="create-project-btn">
                    Make a new project
                </button>
                <button class="pfob-btn pfob-btn-secondary" id="invite-people-btn">
                    Invite people
                </button>
            </div>
        </div>

        <div class="pfob-view-controls">
            <div class="pfob-project-filter">
                <button class="pfob-filter-btn active" data-filter="all">
                    All projects
                </button>
            </div>

            <div class="pfob-view-toggle">
                <button class="pfob-view-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>"
                        data-view="grid"
                        title="Tile View">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="3" y="3" width="8" height="8"/>
                        <rect x="13" y="3" width="8" height="8"/>
                        <rect x="3" y="13" width="8" height="8"/>
                        <rect x="13" y="13" width="8" height="8"/>
                    </svg>
                </button>
                <button class="pfob-view-btn <?php echo $view_mode === 'list' ? 'active' : ''; ?>"
                        data-view="list"
                        title="List View">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="3" y="4" width="18" height="2"/>
                        <rect x="3" y="11" width="18" height="2"/>
                        <rect x="3" y="18" width="18" height="2"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="pfob-projects-container pfob-view-<?php echo $view_mode; ?>" id="projects-container">
            <?php if ( empty( $projects ) ) : ?>
                <div class="pfob-empty-state">
                    <h3>Welcome to ProjectFOB!</h3>
                    <p>You don't have any projects yet. Create your first project to get started.</p>
                    <button class="pfob-btn pfob-btn-primary" onclick="document.getElementById('create-project-btn').click()">
                        Create Your First Project
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $projects as $project ) : ?>
                    <div class="pfob-project-card"
                         data-project-id="<?php echo $project->id; ?>"
                         draggable="true">
                        <div class="pfob-drag-handle" title="Drag to reorder">⋮⋮</div>
                        <h3 class="pfob-project-title">
                            <a href="<?php echo PFOB_Template::get_project_url( $project ); ?>">
                                <?php echo esc_html( $project->name ); ?>
                            </a>
                        </h3>
                        <?php if ( $project->description ) : ?>
                            <p class="pfob-project-description"><?php echo esc_html( $project->description ); ?></p>
                        <?php endif; ?>
                        <div class="pfob-project-meta">
                            <span class="pfob-project-updated">
                                Updated <?php echo PFOB_Template::format_date( $project->updated_at ); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<style>
.pfob-view-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
}

.pfob-view-toggle {
    display: flex;
    gap: 5px;
    background: white;
    padding: 4px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pfob-view-btn {
    background: transparent;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
    color: #666;
    transition: all 0.2s;
}

.pfob-view-btn:hover {
    background: #f0f0f0;
}

.pfob-view-btn.active {
    background: #2d9061;
    color: white;
}

/* Grid View (Tiles) */
.pfob-projects-container.pfob-view-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

/* List View */
.pfob-projects-container.pfob-view-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pfob-view-list .pfob-project-card {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s;
}

.pfob-view-list .pfob-project-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.pfob-view-list .pfob-project-title {
    margin: 0;
    flex: 1;
}

.pfob-view-list .pfob-project-description {
    flex: 2;
    margin: 0;
    color: #666;
    font-size: 0.9em;
}

.pfob-view-list .pfob-project-meta {
    flex-shrink: 0;
    margin: 0;
}

/* Drag handle */
.pfob-drag-handle {
    cursor: grab;
    color: #ccc;
    font-size: 18px;
    margin-right: 10px;
    user-select: none;
    padding: 5px;
}

.pfob-drag-handle:active {
    cursor: grabbing;
}

.pfob-project-card[draggable="true"]:hover .pfob-drag-handle {
    color: #2d9061;
}

/* Dragging states */
.pfob-project-card.pfob-dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.pfob-project-card.pfob-drag-over {
    border-top: 3px solid #2d9061;
}

.pfob-projects-container {
    position: relative;
    min-height: 200px;
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
        'avatar' => get_avatar_url( $user_id ),
    ) ); ?>
};

// View toggle functionality
document.querySelectorAll('.pfob-view-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const view = this.dataset.view;
        const container = document.getElementById('projects-container');

        // Update UI
        document.querySelectorAll('.pfob-view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Update container class
        container.className = `pfob-projects-container pfob-view-${view}`;

        // Save preference
        try {
            await fetch(`${pfobData.restUrl}/user/preferences`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
                },
                body: JSON.stringify({
                    key: 'pfob_projects_view',
                    value: view
                })
            });
        } catch (error) {
            console.error('Failed to save view preference:', error);
        }
    });
});

// Drag and Drop functionality
let draggedElement = null;
let draggedIndex = null;

const projectCards = document.querySelectorAll('.pfob-project-card[draggable="true"]');

projectCards.forEach((card, index) => {
    card.addEventListener('dragstart', function(e) {
        draggedElement = this;
        draggedIndex = index;
        this.classList.add('pfob-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    });

    card.addEventListener('dragend', function() {
        this.classList.remove('pfob-dragging');
        // Remove all drag-over classes
        document.querySelectorAll('.pfob-drag-over').forEach(el => {
            el.classList.remove('pfob-drag-over');
        });
    });

    card.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        if (draggedElement !== this) {
            this.classList.add('pfob-drag-over');
        }

        return false;
    });

    card.addEventListener('dragleave', function() {
        this.classList.remove('pfob-drag-over');
    });

    card.addEventListener('drop', async function(e) {
        e.stopPropagation();
        e.preventDefault();

        if (draggedElement !== this) {
            const container = document.getElementById('projects-container');
            const allCards = Array.from(container.querySelectorAll('.pfob-project-card'));
            const draggedIdx = allCards.indexOf(draggedElement);
            const targetIdx = allCards.indexOf(this);

            // Reorder DOM
            if (draggedIdx < targetIdx) {
                this.parentNode.insertBefore(draggedElement, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedElement, this);
            }

            // Save new order
            await saveProjectOrder();
        }

        this.classList.remove('pfob-drag-over');
        return false;
    });
});

async function saveProjectOrder() {
    const container = document.getElementById('projects-container');
    const cards = container.querySelectorAll('.pfob-project-card');
    const order = Array.from(cards).map(card => card.dataset.projectId);

    try {
        await fetch(`${pfobData.restUrl}/user/project-order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({ order: order })
        });
    } catch (error) {
        console.error('Failed to save project order:', error);
    }
}
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
