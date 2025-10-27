<?php
/**
 * Message board template
 */

global $bcwp_project;

BCWP_Template::header( $bcwp_project->name . ' - Messages' );

$messages = BCWP_Message::get_project_messages( $bcwp_project->id );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-messages">

        <div class="bcwp-page-header">
            <h1>Message Board</h1>
            <button class="bcwp-btn bcwp-btn-primary" id="new-message-btn">
                New Message
            </button>
        </div>

        <div class="bcwp-messages-list">
            <?php if ( empty( $messages ) ) : ?>
                <div class="bcwp-empty-state">
                    <h3>No messages yet</h3>
                    <p>Post the first message to get the conversation started.</p>
                    <button class="bcwp-btn bcwp-btn-primary" onclick="document.getElementById('new-message-btn').click()">
                        Post First Message
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $messages as $message ) : ?>
                    <div class="bcwp-message-card <?php echo $message->is_pinned ? 'bcwp-pinned' : ''; ?>">
                        <div class="bcwp-message-header">
                            <div class="bcwp-message-author">
                                <?php echo BCWP_Template::user_avatar( $message->author_id, 40 ); ?>
                                <div>
                                    <strong><?php echo esc_html( BCWP_Auth_Service::get_user_display_name( $message->author_id ) ); ?></strong>
                                    <span class="bcwp-message-date"><?php echo BCWP_Template::format_date( $message->created_at ); ?></span>
                                </div>
                            </div>
                            <?php if ( $message->is_pinned ) : ?>
                                <span class="bcwp-pinned-badge">Pinned</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="bcwp-message-title">
                            <a href="<?php echo home_url( '/basecamp/projects/' . $bcwp_project->slug . '/messages/' . $message->id . '/' ); ?>">
                                <?php echo esc_html( $message->title ); ?>
                            </a>
                        </h3>
                        <div class="bcwp-message-excerpt">
                            <?php echo wp_trim_words( $message->content, 50 ); ?>
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
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
