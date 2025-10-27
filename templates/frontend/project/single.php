<?php
/**
 * Single project template
 */

global $bcwp_project;

BCWP_Template::header( $bcwp_project->name );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-project-home">

        <div class="bcwp-page-header">
            <h1><?php echo esc_html( $bcwp_project->name ); ?></h1>
            <?php if ( BCWP_Permission_Service::can_edit_project( $bcwp_project->id ) ) : ?>
                <button class="bcwp-btn bcwp-btn-secondary" id="edit-project-btn">
                    Edit Project
                </button>
            <?php endif; ?>
        </div>

        <?php if ( $bcwp_project->description ) : ?>
            <div class="bcwp-project-description">
                <p><?php echo esc_html( $bcwp_project->description ); ?></p>
            </div>
        <?php endif; ?>

        <div class="bcwp-project-tools-grid">
            <?php
            $tools = BCWP_Project::get_tools( $bcwp_project->id );
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
                <a href="<?php echo BCWP_Template::get_tool_url( $bcwp_project, $tool->tool_type ); ?>" class="bcwp-tool-card">
                    <div class="bcwp-tool-icon-large"><?php echo $info['icon']; ?></div>
                    <h3><?php echo esc_html( $info['title'] ); ?></h3>
                    <p><?php echo esc_html( $info['desc'] ); ?></p>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="bcwp-project-activity">
            <h2>Recent Activity</h2>
            <div id="recent-activity">
                <!-- Loaded via JavaScript -->
            </div>
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
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
