<?php
/**
 * Single Message View with Comments
 */

global $pfob_project, $pfob_item;

$message = $pfob_item;
$comments = PFOB_Comment::get_for_subject( 'message', $message->id );
$comment_count = count( $comments );

PFOB_Template::header( $message->title . ' - ' . $pfob_project->name );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-message-single">

        <div class="pfob-message-header">
            <div class="pfob-breadcrumb">
                <a href="<?php echo home_url( '/projectfob/projects/' . $pfob_project->slug . '/messages' ); ?>">
                    Message Board
                </a>
                <span>/</span>
                <span><?php echo esc_html( $message->title ); ?></span>
            </div>

            <?php if ( $message->created_by == get_current_user_id() ) : ?>
                <div class="pfob-message-actions">
                    <button class="pfob-btn pfob-btn-secondary pfob-btn-sm" id="edit-message-btn">
                        Edit
                    </button>
                    <button class="pfob-btn pfob-btn-danger pfob-btn-sm" id="delete-message-btn">
                        Delete
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <article class="pfob-message-content">
            <header>
                <h1><?php echo esc_html( $message->title ); ?></h1>

                <?php if ( $message->category ) : ?>
                    <span class="pfob-message-category">
                        <?php echo esc_html( $message->category ); ?>
                    </span>
                <?php endif; ?>

                <div class="pfob-message-meta">
                    <div class="pfob-message-author">
                        <?php echo PFOB_Template::user_avatar( $message->created_by, 40 ); ?>
                        <div>
                            <strong><?php echo PFOB_Auth_Service::get_user_display_name( $message->created_by ); ?></strong>
                            <span><?php echo PFOB_Template::format_date( $message->created_at ); ?></span>
                        </div>
                    </div>

                    <?php if ( $message->is_pinned ) : ?>
                        <span class="pfob-message-pinned">📌 Pinned</span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="pfob-message-body">
                <?php echo wpautop( esc_html( $message->content ) ); ?>
            </div>
        </article>

        <section class="pfob-comments-section">
            <h2 class="pfob-comments-heading">
                Comments (<?php echo $comment_count; ?>)
            </h2>

            <div class="pfob-comments-list" id="comments-list">
                <?php if ( empty( $comments ) ) : ?>
                    <p class="pfob-no-comments">No comments yet. Be the first to comment!</p>
                <?php else : ?>
                    <?php foreach ( $comments as $comment ) : ?>
                        <div class="pfob-comment" data-comment-id="<?php echo $comment->id; ?>">
                            <div class="pfob-comment-avatar">
                                <?php echo PFOB_Template::user_avatar( $comment->user_id, 40 ); ?>
                            </div>
                            <div class="pfob-comment-content">
                                <div class="pfob-comment-header">
                                    <strong><?php echo PFOB_Auth_Service::get_user_display_name( $comment->user_id ); ?></strong>
                                    <span class="pfob-comment-time"><?php echo PFOB_Template::format_date( $comment->created_at ); ?></span>
                                </div>
                                <div class="pfob-comment-body">
                                    <?php echo wpautop( esc_html( $comment->content ) ); ?>
                                </div>
                                <?php if ( $comment->user_id == get_current_user_id() ) : ?>
                                    <div class="pfob-comment-actions">
                                        <button class="pfob-comment-edit" data-comment-id="<?php echo $comment->id; ?>">
                                            Edit
                                        </button>
                                        <button class="pfob-comment-delete" data-comment-id="<?php echo $comment->id; ?>">
                                            Delete
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="pfob-comment-form-wrapper">
                <h3>Add a Comment</h3>
                <form id="comment-form">
                    <div class="pfob-form-group">
                        <textarea id="comment-content"
                                  name="content"
                                  rows="4"
                                  placeholder="Write your comment..."
                                  required></textarea>
                    </div>
                    <button type="submit" class="pfob-btn pfob-btn-primary">
                        Post Comment
                    </button>
                </form>
            </div>
        </section>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'pfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    messageId: <?php echo $message->id; ?>,
    projectSlug: '<?php echo $pfob_project->slug; ?>',
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
        'avatar' => get_avatar_url( get_current_user_id() ),
    ) ); ?>
};

// Submit comment
document.getElementById('comment-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const content = document.getElementById('comment-content').value.trim();
    if (!content) return;

    try {
        const response = await fetch(`${pfobData.restUrl}/messages/${pfobData.messageId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': pfobData.nonce
            },
            body: JSON.stringify({ content })
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('comment-content').value = '';
            addCommentToList(result.data);
            updateCommentCount(1);
        }
    } catch (error) {
        console.error('Failed to create comment:', error);
    }
});

// Delete comment
document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('pfob-comment-delete')) {
        if (!confirm('Delete this comment?')) return;

        const commentId = e.target.dataset.commentId;

        try {
            const response = await fetch(`${pfobData.restUrl}/comments/${commentId}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': pfobData.nonce }
            });

            if (response.ok) {
                document.querySelector(`[data-comment-id="${commentId}"]`).remove();
                updateCommentCount(-1);
            }
        } catch (error) {
            console.error('Failed to delete comment:', error);
        }
    }
});

// Edit message
document.getElementById('edit-message-btn')?.addEventListener('click', () => {
    alert('Edit message functionality - to be implemented');
});

// Delete message
document.getElementById('delete-message-btn')?.addEventListener('click', async () => {
    if (!confirm('Delete this message? This cannot be undone.')) return;

    try {
        const response = await fetch(`${pfobData.restUrl}/messages/${pfobData.messageId}`, {
            method: 'DELETE',
            headers: { 'X-WP-Nonce': pfobData.nonce }
        });

        if (response.ok) {
            window.location.href = `/projectfob/projects/${pfobData.projectSlug}/messages`;
        }
    } catch (error) {
        console.error('Failed to delete message:', error);
    }
});

function addCommentToList(comment) {
    const list = document.getElementById('comments-list');
    const noComments = list.querySelector('.pfob-no-comments');
    if (noComments) {
        noComments.remove();
    }

    const div = document.createElement('div');
    div.className = 'pfob-comment';
    div.dataset.commentId = comment.id;
    div.innerHTML = `
        <div class="pfob-comment-avatar">
            <img src="${pfobData.currentUser.avatar}" alt="" class="pfob-avatar" width="40" height="40">
        </div>
        <div class="pfob-comment-content">
            <div class="pfob-comment-header">
                <strong>${escapeHtml(pfobData.currentUser.name)}</strong>
                <span class="pfob-comment-time">just now</span>
            </div>
            <div class="pfob-comment-body">
                <p>${escapeHtml(comment.content)}</p>
            </div>
            <div class="pfob-comment-actions">
                <button class="pfob-comment-edit" data-comment-id="${comment.id}">Edit</button>
                <button class="pfob-comment-delete" data-comment-id="${comment.id}">Delete</button>
            </div>
        </div>
    `;

    list.appendChild(div);
}

function updateCommentCount(delta) {
    const heading = document.querySelector('.pfob-comments-heading');
    const match = heading.textContent.match(/\d+/);
    const count = match ? parseInt(match[0]) + delta : delta;
    heading.textContent = `Comments (${count})`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
<script src="<?php echo PFOB_PLUGIN_URL; ?>assets/js/frontend.js?ver=<?php echo PFOB_VERSION; ?>"></script>

</body>
</html>
