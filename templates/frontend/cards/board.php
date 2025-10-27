<?php
/**
 * Card Table - Kanban Board
 */

global $bcwp_project;

BCWP_Template::header( $bcwp_project->name . ' - Card Table' );

$columns = BCWP_Card::get_project_columns( $bcwp_project->id );

// Create default columns if none exist
if ( empty( $columns ) ) {
    $default_columns = array(
        array( 'name' => 'To Do', 'color' => '#E3E3E3' ),
        array( 'name' => 'In Progress', 'color' => '#FFE3A3' ),
        array( 'name' => 'Done', 'color' => '#C6EFCE' ),
    );

    foreach ( $default_columns as $index => $col ) {
        BCWP_Database::insert( 'card_columns', array(
            'project_id' => $bcwp_project->id,
            'name'       => $col['name'],
            'position'   => $index,
            'color'      => $col['color'],
        ) );
    }

    $columns = BCWP_Card::get_project_columns( $bcwp_project->id );
}

?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-card-table">

        <div class="bcwp-page-header">
            <h1>Card Table</h1>
            <button class="bcwp-btn bcwp-btn-primary" id="new-column-btn">
                Add Column
            </button>
        </div>

        <div class="bcwp-kanban-board" id="kanban-board">
            <?php foreach ( $columns as $column ) : ?>
                <?php $cards = BCWP_Card::get_column_cards( $column->id ); ?>
                <div class="bcwp-kanban-column" data-column-id="<?php echo $column->id; ?>">
                    <div class="bcwp-column-header" style="background-color: <?php echo esc_attr( $column->color ); ?>">
                        <h3><?php echo esc_html( $column->name ); ?></h3>
                        <div class="bcwp-column-actions">
                            <span class="bcwp-card-count"><?php echo count( $cards ); ?></span>
                            <button class="bcwp-btn-icon bcwp-add-card-btn"
                                    data-column-id="<?php echo $column->id; ?>"
                                    title="Add card">
                                +
                            </button>
                            <button class="bcwp-btn-icon bcwp-column-menu-btn"
                                    title="Column options">
                                ⋮
                            </button>
                        </div>
                    </div>

                    <div class="bcwp-column-cards"
                         data-column-id="<?php echo $column->id; ?>"
                         ondragover="event.preventDefault()"
                         ondrop="handleCardDrop(event)">

                        <?php if ( empty( $cards ) ) : ?>
                            <p class="bcwp-empty-column">No cards yet</p>
                        <?php else : ?>
                            <?php foreach ( $cards as $card ) : ?>
                                <div class="bcwp-kanban-card"
                                     data-card-id="<?php echo $card->id; ?>"
                                     draggable="true"
                                     ondragstart="handleCardDragStart(event)">

                                    <div class="bcwp-card-content">
                                        <h4><?php echo esc_html( $card->title ); ?></h4>

                                        <?php if ( $card->description ) : ?>
                                            <p class="bcwp-card-description">
                                                <?php echo esc_html( wp_trim_words( $card->description, 15 ) ); ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="bcwp-card-meta">
                                            <?php if ( $card->assignee_id ) : ?>
                                                <div class="bcwp-card-assignee">
                                                    <?php echo BCWP_Template::user_avatar( $card->assignee_id, 24 ); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ( $card->due_date ) : ?>
                                                <span class="bcwp-card-due <?php echo strtotime( $card->due_date ) < time() ? 'bcwp-overdue' : ''; ?>">
                                                    <?php echo date( 'M j', strtotime( $card->due_date ) ); ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php
                                            $tags = ! empty( $card->tags ) ? json_decode( $card->tags, true ) : array();
                                            if ( ! empty( $tags ) ) :
                                            ?>
                                                <div class="bcwp-card-tags">
                                                    <?php foreach ( $tags as $tag ) : ?>
                                                        <span class="bcwp-tag"><?php echo esc_html( $tag ); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="bcwp-card-actions">
                                        <button class="bcwp-btn-icon bcwp-edit-card-btn"
                                                onclick="editCard(<?php echo $card->id; ?>)"
                                                title="Edit">
                                            ✎
                                        </button>
                                        <button class="bcwp-btn-icon bcwp-delete-card-btn"
                                                onclick="deleteCard(<?php echo $card->id; ?>)"
                                                title="Delete">
                                            ×
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>
</div>

<script>
const bcwpData = {
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $bcwp_project->id; ?>,
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};

let draggedCard = null;

function handleCardDragStart(e) {
    draggedCard = e.target;
    e.target.classList.add('bcwp-dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', e.target.innerHTML);
}

async function handleCardDrop(e) {
    e.preventDefault();
    e.stopPropagation();

    if (!draggedCard) return;

    const targetColumn = e.currentTarget;
    const columnId = targetColumn.dataset.columnId;
    const cardId = draggedCard.dataset.cardId;

    // Move card to new column
    try {
        const response = await fetch(`${bcwpData.restUrl}/cards/${cardId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bcwpData.nonce
            },
            body: JSON.stringify({
                column_id: columnId
            })
        });

        if (response.ok) {
            // Remove "no cards" message if it exists
            const emptyMessage = targetColumn.querySelector('.bcwp-empty-column');
            if (emptyMessage) {
                emptyMessage.remove();
            }

            // Append card to new column
            targetColumn.appendChild(draggedCard);
            draggedCard.classList.remove('bcwp-dragging');

            // Update card counts
            updateCardCounts();
        }
    } catch (error) {
        console.error('Failed to move card:', error);
    }

    draggedCard = null;
}

function updateCardCounts() {
    document.querySelectorAll('.bcwp-kanban-column').forEach(column => {
        const count = column.querySelectorAll('.bcwp-kanban-card').length;
        column.querySelector('.bcwp-card-count').textContent = count;
    });
}

// Add card buttons
document.querySelectorAll('.bcwp-add-card-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const columnId = btn.dataset.columnId;
        showCreateCardModal(columnId);
    });
});

// New column button
document.getElementById('new-column-btn')?.addEventListener('click', () => {
    showCreateColumnModal();
});

function showCreateCardModal(columnId) {
    const modal = document.createElement('div');
    modal.className = 'bcwp-modal';
    modal.innerHTML = `
        <div class="bcwp-modal-content">
            <div class="bcwp-modal-header">
                <h2>New Card</h2>
                <button class="bcwp-modal-close">&times;</button>
            </div>
            <div class="bcwp-modal-body">
                <form id="create-card-form">
                    <div class="bcwp-form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required placeholder="Card title">
                    </div>
                    <div class="bcwp-form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Card description"></textarea>
                    </div>
                    <div class="bcwp-form-group">
                        <label>Assign to</label>
                        <select name="assignee_id">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                    <div class="bcwp-form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date">
                    </div>
                    <div class="bcwp-form-group">
                        <label>Tags (comma-separated)</label>
                        <input type="text" name="tags" placeholder="bug, feature, urgent">
                    </div>
                    <div class="bcwp-form-actions">
                        <button type="submit" class="bcwp-btn bcwp-btn-primary">Create Card</button>
                        <button type="button" class="bcwp-btn bcwp-btn-secondary bcwp-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelectorAll('.bcwp-modal-close').forEach(btn => {
        btn.addEventListener('click', () => modal.remove());
    });

    // Load project members
    loadProjectMembers(modal.querySelector('select[name="assignee_id"]'));

    document.getElementById('create-card-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        const tags = formData.get('tags')
            ? formData.get('tags').split(',').map(t => t.trim()).filter(t => t)
            : [];

        const data = {
            column_id: columnId,
            title: formData.get('title'),
            description: formData.get('description'),
            assignee_id: formData.get('assignee_id') || null,
            due_date: formData.get('due_date') || null,
            tags: tags
        };

        try {
            const response = await fetch(`${bcwpData.restUrl}/projects/${bcwpData.projectId}/cards`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': bcwpData.nonce
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                modal.remove();
                window.location.reload();
            }
        } catch (error) {
            console.error('Failed to create card:', error);
        }
    });
}

function showCreateColumnModal() {
    const modal = document.createElement('div');
    modal.className = 'bcwp-modal';
    modal.innerHTML = `
        <div class="bcwp-modal-content">
            <div class="bcwp-modal-header">
                <h2>New Column</h2>
                <button class="bcwp-modal-close">&times;</button>
            </div>
            <div class="bcwp-modal-body">
                <form id="create-column-form">
                    <div class="bcwp-form-group">
                        <label>Column Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Testing">
                    </div>
                    <div class="bcwp-form-group">
                        <label>Color</label>
                        <input type="color" name="color" value="#E3E3E3">
                    </div>
                    <div class="bcwp-form-actions">
                        <button type="submit" class="bcwp-btn bcwp-btn-primary">Create Column</button>
                        <button type="button" class="bcwp-btn bcwp-btn-secondary bcwp-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelectorAll('.bcwp-modal-close').forEach(btn => {
        btn.addEventListener('click', () => modal.remove());
    });

    document.getElementById('create-column-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        const data = {
            name: formData.get('name'),
            color: formData.get('color')
        };

        try {
            const response = await fetch(`${bcwpData.restUrl}/projects/${bcwpData.projectId}/columns`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': bcwpData.nonce
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                modal.remove();
                window.location.reload();
            }
        } catch (error) {
            console.error('Failed to create column:', error);
        }
    });
}

async function loadProjectMembers(selectElement) {
    try {
        const response = await fetch(`${bcwpData.restUrl}/projects/${bcwpData.projectId}/members`, {
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });

        const result = await response.json();

        if (result.success && result.data) {
            result.data.forEach(member => {
                const option = document.createElement('option');
                option.value = member.ID;
                option.textContent = member.display_name;
                selectElement.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load members:', error);
    }
}

async function editCard(cardId) {
    // Implementation for card editing modal
    console.log('Edit card:', cardId);
}

async function deleteCard(cardId) {
    if (!confirm('Delete this card?')) return;

    try {
        const response = await fetch(`${bcwpData.restUrl}/cards/${cardId}`, {
            method: 'DELETE',
            headers: { 'X-WP-Nonce': bcwpData.nonce }
        });

        if (response.ok) {
            document.querySelector(`[data-card-id="${cardId}"]`).remove();
            updateCardCounts();
        }
    } catch (error) {
        console.error('Failed to delete card:', error);
    }
}
</script>
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
