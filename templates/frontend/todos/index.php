<?php
/**
 * To-dos - Task management
 */

global $pfob_project;

PFOB_Template::header( $pfob_project->name . ' - To-dos' );

$todo_lists = PFOB_Todo::get_project_lists( $pfob_project->id );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-todos">

        <div class="pfob-page-header">
            <h1>To-dos</h1>
            <button class="pfob-btn pfob-btn-primary" id="new-todo-list-btn">
                New List
            </button>
        </div>

        <div class="pfob-todo-lists" id="todo-lists-container">
            <?php if ( empty( $todo_lists ) ) : ?>
                <div class="pfob-empty-state">
                    <h3>No to-do lists yet</h3>
                    <p>Create your first to-do list to start tracking tasks.</p>
                    <button class="pfob-btn pfob-btn-primary" onclick="document.getElementById('new-todo-list-btn').click()">
                        Create First List
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $todo_lists as $list ) : ?>
                    <?php
                    $items = PFOB_Todo::get_list_items( $list->id );
                    $completed_count = count( array_filter( $items, function( $item ) {
                        return $item->is_completed;
                    } ) );
                    $total_count = count( $items );
                    ?>
                    <div class="pfob-todo-list-card" data-list-id="<?php echo $list->id; ?>">
                        <div class="pfob-list-header">
                            <div>
                                <h3><?php echo esc_html( $list->name ); ?></h3>
                                <?php if ( $list->description ) : ?>
                                    <p class="pfob-list-description"><?php echo esc_html( $list->description ); ?></p>
                                <?php endif; ?>
                                <span class="pfob-list-progress"><?php echo $completed_count; ?>/<?php echo $total_count; ?> completed</span>
                            </div>
                            <button class="pfob-btn-icon pfob-add-todo-btn" data-list-id="<?php echo $list->id; ?>" title="Add to-do">
                                +
                            </button>
                        </div>

                        <div class="pfob-todo-items" id="todo-items-<?php echo $list->id; ?>">
                            <?php if ( empty( $items ) ) : ?>
                                <p class="pfob-empty-text">No items yet</p>
                            <?php else : ?>
                                <?php foreach ( $items as $item ) : ?>
                                    <div class="pfob-todo-item <?php echo $item->is_completed ? 'pfob-completed' : ''; ?>"
                                         data-todo-id="<?php echo $item->id; ?>"
                                         draggable="true">
                                        <div class="pfob-todo-main">
                                            <input type="checkbox"
                                                   class="pfob-todo-checkbox"
                                                   <?php checked( $item->is_completed, 1 ); ?>>
                                            <div class="pfob-todo-content">
                                                <span class="pfob-todo-text"><?php echo esc_html( $item->content ); ?></span>
                                                <?php if ( $item->assignee_id ) : ?>
                                                    <span class="pfob-todo-assignee">
                                                        <?php echo PFOB_Template::user_avatar( $item->assignee_id, 24 ); ?>
                                                        <?php echo esc_html( PFOB_Auth_Service::get_user_display_name( $item->assignee_id ) ); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ( $item->due_date ) : ?>
                                                    <span class="pfob-todo-due">Due <?php echo date( 'M j', strtotime( $item->due_date ) ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="pfob-todo-actions">
                                            <button class="pfob-btn-icon pfob-edit-todo-btn" title="Edit">✎</button>
                                            <button class="pfob-btn-icon pfob-delete-todo-btn" title="Delete">×</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
const pfobData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $pfob_project->id; ?>,
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/todos.js"></script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
