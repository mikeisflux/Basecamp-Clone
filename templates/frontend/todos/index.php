<?php
/**
 * To-dos - Task management
 */

global $bcwp_project;

BCWP_Template::header( $bcwp_project->name . ' - To-dos' );

$todo_lists = BCWP_Todo::get_project_lists( $bcwp_project->id );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-todos">

        <div class="bcwp-page-header">
            <h1>To-dos</h1>
            <button class="bcwp-btn bcwp-btn-primary" id="new-todo-list-btn">
                New List
            </button>
        </div>

        <div class="bcwp-todo-lists" id="todo-lists-container">
            <?php if ( empty( $todo_lists ) ) : ?>
                <div class="bcwp-empty-state">
                    <h3>No to-do lists yet</h3>
                    <p>Create your first to-do list to start tracking tasks.</p>
                    <button class="bcwp-btn bcwp-btn-primary" onclick="document.getElementById('new-todo-list-btn').click()">
                        Create First List
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $todo_lists as $list ) : ?>
                    <?php
                    $items = BCWP_Todo::get_list_items( $list->id );
                    $completed_count = count( array_filter( $items, function( $item ) {
                        return $item->is_completed;
                    } ) );
                    $total_count = count( $items );
                    ?>
                    <div class="bcwp-todo-list-card" data-list-id="<?php echo $list->id; ?>">
                        <div class="bcwp-list-header">
                            <div>
                                <h3><?php echo esc_html( $list->name ); ?></h3>
                                <?php if ( $list->description ) : ?>
                                    <p class="bcwp-list-description"><?php echo esc_html( $list->description ); ?></p>
                                <?php endif; ?>
                                <span class="bcwp-list-progress"><?php echo $completed_count; ?>/<?php echo $total_count; ?> completed</span>
                            </div>
                            <button class="bcwp-btn-icon bcwp-add-todo-btn" data-list-id="<?php echo $list->id; ?>" title="Add to-do">
                                +
                            </button>
                        </div>

                        <div class="bcwp-todo-items" id="todo-items-<?php echo $list->id; ?>">
                            <?php if ( empty( $items ) ) : ?>
                                <p class="bcwp-empty-text">No items yet</p>
                            <?php else : ?>
                                <?php foreach ( $items as $item ) : ?>
                                    <div class="bcwp-todo-item <?php echo $item->is_completed ? 'bcwp-completed' : ''; ?>"
                                         data-todo-id="<?php echo $item->id; ?>"
                                         draggable="true">
                                        <div class="bcwp-todo-main">
                                            <input type="checkbox"
                                                   class="bcwp-todo-checkbox"
                                                   <?php checked( $item->is_completed, 1 ); ?>>
                                            <div class="bcwp-todo-content">
                                                <span class="bcwp-todo-text"><?php echo esc_html( $item->content ); ?></span>
                                                <?php if ( $item->assignee_id ) : ?>
                                                    <span class="bcwp-todo-assignee">
                                                        <?php echo BCWP_Template::user_avatar( $item->assignee_id, 24 ); ?>
                                                        <?php echo esc_html( BCWP_Auth_Service::get_user_display_name( $item->assignee_id ) ); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ( $item->due_date ) : ?>
                                                    <span class="bcwp-todo-due">Due <?php echo date( 'M j', strtotime( $item->due_date ) ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="bcwp-todo-actions">
                                            <button class="bcwp-btn-icon bcwp-edit-todo-btn" title="Edit">✎</button>
                                            <button class="bcwp-btn-icon bcwp-delete-todo-btn" title="Delete">×</button>
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
const bcwpData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $bcwp_project->id; ?>,
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};
</script>
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/todos.js"></script>
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
