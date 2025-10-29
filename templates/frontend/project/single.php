<?php
/**
 * Single project template
 */

global $pfob_project;

PFOB_Template::header( $pfob_project->name );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-project-home">

        <div class="pfob-page-header">
            <h1><?php echo esc_html( $pfob_project->name ); ?></h1>
            <?php if ( PFOB_Permission_Service::can_edit_project( $pfob_project->id ) ) : ?>
                <button class="pfob-btn pfob-btn-secondary" id="edit-project-btn">
                    Edit Project
                </button>
            <?php endif; ?>
        </div>

        <?php if ( $pfob_project->description ) : ?>
            <div class="pfob-project-description">
                <p><?php echo esc_html( $pfob_project->description ); ?></p>
            </div>
        <?php endif; ?>

        <div class="pfob-project-tools-grid">
            <?php
            $tools = PFOB_Project::get_tools( $pfob_project->id );
            $tool_info = array(
                'messages'  => array( 'title' => 'Message Board', 'icon' => '💬', 'desc' => 'Announcements and discussions' ),
                'todos'     => array( 'title' => 'To-dos', 'icon' => '✓', 'desc' => 'Task lists and assignments' ),
                'documents' => array( 'title' => 'Docs & Files', 'icon' => '📁', 'desc' => 'Document storage' ),
                'chat'      => array( 'title' => 'Chat', 'icon' => '💭', 'desc' => 'Real-time messaging' ),
                'schedule'  => array( 'title' => 'Schedule', 'icon' => '📅', 'desc' => 'Calendar and events' ),
                'cards'     => array( 'title' => 'Card Table', 'icon' => '📋', 'desc' => 'Kanban workflow' ),
            );

            foreach ( $tools as $tool ) :
                if ( ! $tool->is_enabled ) continue;
                $info = $tool_info[ $tool->tool_type ] ?? array( 'title' => ucfirst( $tool->tool_type ), 'icon' => '•', 'desc' => '' );
            ?>
                <a href="<?php echo PFOB_Template::get_tool_url( $pfob_project, $tool->tool_type ); ?>" class="pfob-tool-card">
                    <div class="pfob-tool-icon-large"><?php echo $info['icon']; ?></div>
                    <h3><?php echo esc_html( $info['title'] ); ?></h3>
                    <p><?php echo esc_html( $info['desc'] ); ?></p>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="pfob-project-activity">
            <h2>Recent Activity</h2>
            <div id="recent-activity">
                <!-- Loaded via JavaScript -->
            </div>
        </div>

    </main>
</div>

<script>
const pfobData = {
    ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    restUrl: '<?php echo rest_url( 'pfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $pfob_project->id; ?>,
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
    ) ); ?>
};
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
