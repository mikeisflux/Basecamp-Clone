<?php
/**
 * Dashboard template
 */

BCWP_Template::header( 'Dashboard' );

$user_id = get_current_user_id();
$projects = BCWP_Project::get_user_projects( $user_id );
?>

<div class="bcwp-container">
    <main class="bcwp-main bcwp-dashboard">

        <div class="bcwp-page-header">
            <div class="bcwp-company-logo">
                <h1><?php echo esc_html( get_option( 'bcwp_company_name' ) ); ?></h1>
            </div>

            <div class="bcwp-actions">
                <button class="bcwp-btn bcwp-btn-primary" id="create-project-btn">
                    Make a new project
                </button>
                <button class="bcwp-btn bcwp-btn-secondary" id="invite-people-btn">
                    Invite people
                </button>
            </div>
        </div>

        <div class="bcwp-project-filter">
            <button class="bcwp-filter-btn active" data-filter="all">
                View all projects in a list
            </button>
        </div>

        <div class="bcwp-projects-grid" id="projects-container">
            <?php if ( empty( $projects ) ) : ?>
                <div class="bcwp-empty-state">
                    <h3>Welcome to Basecamp!</h3>
                    <p>You don't have any projects yet. Create your first project to get started.</p>
                    <button class="bcwp-btn bcwp-btn-primary" onclick="document.getElementById('create-project-btn').click()">
                        Create Your First Project
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $projects as $project ) : ?>
                    <div class="bcwp-project-card">
                        <h3 class="bcwp-project-title">
                            <a href="<?php echo BCWP_Template::get_project_url( $project ); ?>">
                                <?php echo esc_html( $project->name ); ?>
                            </a>
                        </h3>
                        <?php if ( $project->description ) : ?>
                            <p class="bcwp-project-description"><?php echo esc_html( $project->description ); ?></p>
                        <?php endif; ?>
                        <div class="bcwp-project-meta">
                            <span class="bcwp-project-updated">
                                Updated <?php echo BCWP_Template::format_date( $project->updated_at ); ?>
                            </span>
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
    currentUser: <?php echo json_encode( array(
        'id' => $user_id,
        'name' => wp_get_current_user()->display_name,
        'avatar' => get_avatar_url( $user_id ),
    ) ); ?>
};
</script>
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
