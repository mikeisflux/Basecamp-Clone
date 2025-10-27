<?php
/**
 * Chat room - Real-time messaging
 */

global $bcwp_project;

BCWP_Template::header( $bcwp_project->name . ' - Chat' );

$messages = BCWP_Chat::get_project_messages( $bcwp_project->id, 50 );
?>

<div class="bcwp-container">
    <?php BCWP_Template::navigation(); ?>

    <main class="bcwp-main bcwp-chat">

        <div class="bcwp-page-header">
            <h1>Chat</h1>
            <p class="bcwp-subtitle">Casual, real-time team conversation</p>
        </div>

        <div class="bcwp-chat-container">
            <div class="bcwp-chat-messages" id="chat-messages">
                <?php foreach ( $messages as $msg ) : ?>
                    <div class="bcwp-chat-message" data-id="<?php echo $msg->id; ?>">
                        <div class="bcwp-chat-avatar">
                            <?php echo BCWP_Template::user_avatar( $msg->user_id, 40 ); ?>
                        </div>
                        <div class="bcwp-chat-content">
                            <div class="bcwp-chat-header">
                                <strong><?php echo esc_html( BCWP_Auth_Service::get_user_display_name( $msg->user_id ) ); ?></strong>
                                <span class="bcwp-chat-time"><?php echo BCWP_Template::format_date( $msg->created_at ); ?></span>
                            </div>
                            <p><?php echo esc_html( $msg->message ); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bcwp-chat-input-container">
                <form id="chat-form">
                    <textarea id="chat-input"
                              placeholder="Type your message..."
                              rows="1"></textarea>
                    <button type="submit" class="bcwp-btn bcwp-btn-primary">Send</button>
                </form>
            </div>
        </div>

    </main>
</div>

<script>
const bcwpData = {
    restUrl: '<?php echo rest_url( 'bcwp/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
    projectId: <?php echo $bcwp_project->id; ?>,
    currentUser: <?php echo json_encode( array(
        'id' => get_current_user_id(),
        'name' => wp_get_current_user()->display_name,
        'avatar' => get_avatar_url( get_current_user_id() ),
    ) ); ?>
};

let lastMessageId = <?php echo empty( $messages ) ? 0 : $messages[count($messages)-1]->id; ?>;

document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const message = input.value.trim();

    if (!message) return;

    try {
        const response = await fetch(`${bcwpData.restUrl}/projects/${bcwpData.projectId}/chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bcwpData.nonce
            },
            body: JSON.stringify({ message })
        });

        if (response.ok) {
            input.value = '';
            input.style.height = 'auto';
            loadNewMessages();
        }
    } catch (error) {
        console.error('Failed to send message:', error);
    }
});

document.getElementById('chat-input').addEventListener('input', (e) => {
    e.target.style.height = 'auto';
    e.target.style.height = (e.target.scrollHeight) + 'px';
});

async function loadNewMessages() {
    try {
        const response = await fetch(
            `${bcwpData.restUrl}/projects/${bcwpData.projectId}/chat/poll?since_id=${lastMessageId}`,
            { headers: { 'X-WP-Nonce': bcwpData.nonce } }
        );

        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(msg => {
                appendMessage(msg);
                lastMessageId = Math.max(lastMessageId, msg.id);
            });
        }
    } catch (error) {
        console.error('Polling error:', error);
    }
}

function appendMessage(msg) {
    const container = document.getElementById('chat-messages');
    const messageEl = document.createElement('div');
    messageEl.className = 'bcwp-chat-message';
    messageEl.dataset.id = msg.id;
    messageEl.innerHTML = `
        <div class="bcwp-chat-avatar">
            <img src="${bcwpData.currentUser.avatar}" alt="" class="bcwp-avatar">
        </div>
        <div class="bcwp-chat-content">
            <div class="bcwp-chat-header">
                <strong>${escapeHtml(msg.user_name || bcwpData.currentUser.name)}</strong>
                <span class="bcwp-chat-time">just now</span>
            </div>
            <p>${escapeHtml(msg.message)}</p>
        </div>
    `;

    container.appendChild(messageEl);
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

setInterval(loadNewMessages, 3000);

document.getElementById('chat-messages').scrollTop =
    document.getElementById('chat-messages').scrollHeight;
</script>
<script src="<?php echo BCWP_PLUGIN_URL; ?>assets/js/frontend.js"></script>

</body>
</html>
