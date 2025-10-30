<?php
/**
 * Dashboard template
 */

PFOB_Template::header( 'Dashboard' );

$user_id = get_current_user_id();
$projects = PFOB_Project::get_user_projects( $user_id );
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

        <div class="pfob-project-filter">
            <button class="pfob-filter-btn active" data-filter="all">
                View all projects in a list
            </button>
        </div>

        <div class="pfob-projects-grid" id="projects-container">
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
                    <div class="pfob-project-card">
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
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
