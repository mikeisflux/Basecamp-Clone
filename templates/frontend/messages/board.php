<?php
/**
 * Message board template
 */

global $pfob_project;

PFOB_Template::header( $pfob_project->name . ' - Messages' );

$messages = PFOB_Message::get_project_messages( $pfob_project->id );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-messages">

        <div class="pfob-page-header">
            <h1>Message Board</h1>
            <button class="pfob-btn pfob-btn-primary" id="new-message-btn">
                New Message
            </button>
        </div>

        <div class="pfob-messages-list">
            <?php if ( empty( $messages ) ) : ?>
                <div class="pfob-empty-state">
                    <h3>No messages yet</h3>
                    <p>Post the first message to get the conversation started.</p>
                    <button class="pfob-btn pfob-btn-primary" onclick="document.getElementById('new-message-btn').click()">
                        Post First Message
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $messages as $message ) : ?>
                    <div class="pfob-message-card <?php echo $message->is_pinned ? 'pfob-pinned' : ''; ?>">
                        <div class="pfob-message-header">
                            <div class="pfob-message-author">
                                <?php echo PFOB_Template::user_avatar( $message->author_id, 40 ); ?>
                                <div>
                                    <strong><?php echo esc_html( PFOB_Auth_Service::get_user_display_name( $message->author_id ) ); ?></strong>
                                    <span class="pfob-message-date"><?php echo PFOB_Template::format_date( $message->created_at ); ?></span>
                                </div>
                            </div>
                            <?php if ( $message->is_pinned ) : ?>
                                <span class="pfob-pinned-badge">Pinned</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="pfob-message-title">
                            <a href="<?php echo home_url( '/projectfob/projects/' . $pfob_project->slug . '/messages/' . $message->id . '/' ); ?>">
                                <?php echo esc_html( $message->title ); ?>
                            </a>
                        </h3>
                        <div class="pfob-message-excerpt">
                            <?php echo wp_trim_words( $message->content, 50 ); ?>
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
