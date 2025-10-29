<?php
/**
 * Card Table - Kanban Board
 */

global $pfob_project;

PFOB_Template::header( $pfob_project->name . ' - Card Table' );

$columns = PFOB_Card::get_project_columns( $pfob_project->id );

// Create default columns if none exist
if ( empty( $columns ) ) {
    $default_columns = array(
        array( 'name' => 'To Do', 'color' => '#E3E3E3' ),
        array( 'name' => 'In Progress', 'color' => '#FFE3A3' ),
        array( 'name' => 'Done', 'color' => '#C6EFCE' ),
    );

    foreach ( $default_columns as $index => $col ) {
        PFOB_Database::insert( 'card_columns', array(
            'project_id' => $pfob_project->id,
            'name'       => $col['name'],
            'position'   => $index,
            'color'      => $col['color'],
        ) );
    }

    $columns = PFOB_Card::get_project_columns( $pfob_project->id );
}

?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-card-table">

        <div class="pfob-page-header">
            <h1>Card Table</h1>
            <button class="pfob-btn pfob-btn-primary" id="new-column-btn">
                Add Column
            </button>
        </div>

        <div class="pfob-kanban-board" id="kanban-board">
            <?php foreach ( $columns as $column ) : ?>
                <?php $cards = PFOB_Card::get_column_cards( $column->id ); ?>
                <div class="pfob-kanban-column" data-column-id="<?php echo $column->id; ?>">
                    <div class="pfob-column-header" style="background-color: <?php echo esc_attr( $column->color ); ?>">
                        <h3><?php echo esc_html( $column->name ); ?></h3>
                        <div class="pfob-column-actions">
                            <span class="pfob-card-count"><?php echo count( $cards ); ?></span>
                            <button class="pfob-btn-icon pfob-add-card-btn"
                                    data-column-id="<?php echo $column->id; ?>"
                                    title="Add card">
                                +
                            </button>
                            <button class="pfob-btn-icon pfob-column-menu-btn"
                                    title="Column options">
                                ⋮
                            </button>
                        </div>
                    </div>

                    <div class="pfob-column-cards"
                         data-column-id="<?php echo $column->id; ?>"
                         ondragover="event.preventDefault()"
                         ondrop="handleCardDrop(event)">

                        <?php if ( empty( $cards ) ) : ?>
                            <p class="pfob-empty-column">No cards yet</p>
                        <?php else : ?>
                            <?php foreach ( $cards as $card ) : ?>
                                <div class="pfob-kanban-card"
                                     data-card-id="<?php echo $card->id; ?>"
                                     draggable="true"
                                     ondragstart="handleCardDragStart(event)">

                                    <div class="pfob-card-content">
                                        <h4><?php echo esc_html( $card->title ); ?></h4>

                                        <?php if ( $card->description ) : ?>
                                            <p class="pfob-card-description">
                                                <?php echo esc_html( wp_trim_words( $card->description, 15 ) ); ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="pfob-card-meta">
                                            <?php if ( $card->assignee_id ) : ?>
                                                <div class="pfob-card-assignee">
                                                    <?php echo PFOB_Template::user_avatar( $card->assignee_id, 24 ); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ( $card->due_date ) : ?>
                                                <span class="pfob-card-due <?php echo strtotime( $card->due_date ) < time() ? 'pfob-overdue' : ''; ?>">
                                                    <?php echo date( 'M j', strtotime( $card->due_date ) ); ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php
                                            $tags = ! empty( $card->tags ) ? json_decode( $card->tags, true ) : array();
                                            if ( ! empty( $tags ) ) :
                                            ?>
                                                <div class="pfob-card-tags">
                                                    <?php foreach ( $tags as $tag ) : ?>
                                                        <span class="pfob-tag"><?php echo esc_html( $tag ); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="pfob-card-actions">
                                        <button class="pfob-btn-icon pfob-edit-card-btn"
                                                onclick="editCard(<?php echo $card->id; ?>)"
                                                title="Edit">
                                            ✎
                                        </button>
                                        <button class="pfob-btn-icon pfob-delete-card-btn"
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
const pfobData = {
    restUrl: '<?php echo rest_url( 'pfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $pfob_project->id; ?>,
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};

let draggedCard = null;

function handleCardDragStart(e) {
    draggedCard = e.target;
    e.target.classList.add('pfob-dragging');
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
        const response = await fetch(`${pfobData.restUrl}/cards/${cardId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({
                column_id: columnId
            })
        });

        if (response.ok) {
            // Remove "no cards" message if it exists
            const emptyMessage = targetColumn.querySelector('.pfob-empty-column');
            if (emptyMessage) {
                emptyMessage.remove();
            }

            // Append card to new column
            targetColumn.appendChild(draggedCard);
            draggedCard.classList.remove('pfob-dragging');

            // Update card counts
            updateCardCounts();
        }
    } catch (error) {
        console.error('Failed to move card:', error);
    }

    draggedCard = null;
}

function updateCardCounts() {
    document.querySelectorAll('.pfob-kanban-column').forEach(column => {
        const count = column.querySelectorAll('.pfob-kanban-card').length;
        column.querySelector('.pfob-card-count').textContent = count;
    });
}

// Add card buttons
document.querySelectorAll('.pfob-add-card-btn').forEach(btn => {
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
    modal.className = 'pfob-modal';
    modal.innerHTML = `
        <div class="pfob-modal-content">
            <div class="pfob-modal-header">
                <h2>New Card</h2>
                <button class="pfob-modal-close">&times;</button>
            </div>
            <div class="pfob-modal-body">
                <form id="create-card-form">
                    <div class="pfob-form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required placeholder="Card title">
                    </div>
                    <div class="pfob-form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Card description"></textarea>
                    </div>
                    <div class="pfob-form-group">
                        <label>Assign to</label>
                        <select name="assignee_id">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                    <div class="pfob-form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date">
                    </div>
                    <div class="pfob-form-group">
                        <label>Tags (comma-separated)</label>
                        <input type="text" name="tags" placeholder="bug, feature, urgent">
                    </div>
                    <div class="pfob-form-actions">
                        <button type="submit" class="pfob-btn pfob-btn-primary">Create Card</button>
                        <button type="button" class="pfob-btn pfob-btn-secondary pfob-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelectorAll('.pfob-modal-close').forEach(btn => {
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
            const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/cards`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
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
    modal.className = 'pfob-modal';
    modal.innerHTML = `
        <div class="pfob-modal-content">
            <div class="pfob-modal-header">
                <h2>New Column</h2>
                <button class="pfob-modal-close">&times;</button>
            </div>
            <div class="pfob-modal-body">
                <form id="create-column-form">
                    <div class="pfob-form-group">
                        <label>Column Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Testing">
                    </div>
                    <div class="pfob-form-group">
                        <label>Color</label>
                        <input type="color" name="color" value="#E3E3E3">
                    </div>
                    <div class="pfob-form-actions">
                        <button type="submit" class="pfob-btn pfob-btn-primary">Create Column</button>
                        <button type="button" class="pfob-btn pfob-btn-secondary pfob-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelectorAll('.pfob-modal-close').forEach(btn => {
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
            const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/columns`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pfobData.nonce
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
        const response = await fetch(`${pfobData.restUrl}/projects/${pfobData.projectId}/members`, {
            headers: { 'X-WP-Nonce': pfobData.nonce }
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
        const response = await fetch(`${pfobData.restUrl}/cards/${cardId}`, {
            method: 'DELETE',
            headers: { 'X-WP-Nonce': pfobData.nonce }
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
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
